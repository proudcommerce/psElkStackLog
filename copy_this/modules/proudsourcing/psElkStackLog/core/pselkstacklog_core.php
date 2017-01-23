<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @copyright (c) Proud Sourcing GmbH | 2017
 * @link www.proudcommerce.com
 * @package psElkStackLog
 * @version 1.2.0
 **/
class psElkStackLog_core extends oxSuperCfg
{
    /*
     * Saves log in formation in queue table
     */
    public function saveToQueue($sType, $sData)
    {
        $oEntry = oxnew("oxbase");
        $oEntry->init("pselkstacklog_queue");
        $oEntry->pselkstacklog_queue__oxtype = new oxField($sType);
        $oEntry->pselkstacklog_queue__oxdata = new oxField(serialize($sData));
        $oEntry->save();
    }

    /*
     * Reads log queue and prepares curl request
     */
    public function readQueue()
    {
        $iLimit = 99999;
        if($iGetLimit = oxRegistry::getConfig()->getRequestParameter('limit') > 0) {
            $iLimit = $iGetLimit;
        }
        $sSql = 'SELECT oxid, oxdata FROM pselkstacklog_queue WHERE oxstatus = 0 ORDER BY oxtimestamp ASC LIMIT '.$iLimit;
        $rs = oxDb::getDb(ADODB_FETCH_ASSOC)->execute($sSql);
        if ($rs != false && $rs->recordCount() > 0) {
            while (!$rs->EOF) {
                echo "<pre><b>".$rs->fields["oxid"]."</b><br>".$rs->fields["oxdata"]."</pre>";
                $aData = unserialize($rs->fields["oxdata"]);
                $this->sendCurlRequest($aData);
                oxDb::getDb(ADODB_FETCH_ASSOC)->execute('UPDATE pselkstacklog_queue SET oxstatus = 1 WHERE oxid = '.oxDb::getDb(ADODB_FETCH_ASSOC)->quote( $rs->fields["oxid"] ));
                sleep(1);
                $rs->moveNext();
            }
        }
    }

    /*
     * Sends curl request to elastic search
     */
    public function sendCurlRequest($aLog)
    {
        $aLog["message"] = "psElkStackLog";
        $aLog["source"] = rtrim(preg_replace("(^https?://)", "", oxRegistry::getConfig()->getConfigParam('sShopURL')), '/\\');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, oxRegistry::getConfig()->getConfigParam('psElkStackLog_logurl'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($aLog));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen(json_encode($aLog)),)
        );
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}