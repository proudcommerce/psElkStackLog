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
class psElkStackLog_search extends psElkStackLog_search_parent
{
    /*
     * workaround for multiple calls
     */
    protected $_sendLogRequest = false;

    /*
     * psElkStackLog core
     */
    public function init()
    {
        $this->psElkStackLog = oxNew("pselkstacklog_core");
        parent::init();
    }

    /**
     * Forms search navigation URLs, executes parent::render() and
     * returns name of template to render search::_sThisTemplate.
     *
     * @return  string  current template file name
     */
    public function render()
    {
        if(oxRegistry::getConfig()->getConfigParam('psElkStackLog_log_search') == true) {
            $this->_psLogElkStack();
        }
        return parent::render();
    }

    /*
     * Log order information to elastic search
     */
    protected function _psLogElkStack()
    {
        if(!$this->_sendLogRequest) {
            $sType = "oxsearch";
            $sSearchParam = oxRegistry::getConfig()->getRequestParameter( 'searchparam', true );
            if(!empty($sSearchParam)) {
                // search request
                $aLog = array(
                    "_oxType" => $sType,
                    "_oxName" => $sSearchParam,
                    "_oxCount" => $this->_iAllArtCnt,
                );
                $this->psElkStackLog->saveToQueue($sType, $aLog);
                unset($aLog);
                $this->_sendLogRequest = true;
            }
        }
    }

}