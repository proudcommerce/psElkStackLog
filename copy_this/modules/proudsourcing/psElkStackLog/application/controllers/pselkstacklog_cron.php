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
class psElkStackLog_cron extends oxUBase
{
    /*
 * psElkStackLog core
 */
    public function init()
    {
        $this->psElkStackLog = oxNew("pselkstacklog_core");
        parent::init();
    }

    /*
     * Reads log queue
     */
    public function readQueue()
    {
        // allowed to read the queue?
        if($this->_checkAuthentication()) {
            $this->psElkStackLog->readQueue();
        } else {
            echo "<pre>Authentication failed</pre>";
        }
        exit;
    }

    /*
     * Checks authentication for queue reading
     */
    protected function _checkAuthentication()
    {
        if(oxRegistry::getConfig()->getRequestParameter('key') == oxRegistry::getConfig()->getConfigParam('psElkStackLog_hash')) {
            return true;
        }
        return false;
    }
}