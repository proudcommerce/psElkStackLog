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
class psElkStackLog_thankyou extends psElkStackLog_thankyou_parent
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
     * First checks for basket - if no such object available -
     * redirects to start page. Otherwise - executes parent::render()
     * and returns name of template to render thankyou::_sThisTemplate.
     *
     * @return  string  current template file name
     */
    public function render()
    {
        if(oxRegistry::getConfig()->getConfigParam('psElkStackLog_log_order') == true) {
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
            $sType = "oxorder";
            $oOrder = oxNew("oxorder");
            $oOrder->load($this->_oBasket->getOrderId());
            $oPayment = oxNew("oxpayment");
            $oPayment->load($oOrder->oxorder__oxpaymenttype->value);

            // order request
            $aLog = array(
                "_oxType" => $sType,
                "_oxOrderNr" => $oOrder->oxorder__oxordernr->value,
                "_oxOrderDate" => $oOrder->oxorder__oxorderdate->value,
                "_oxCountry" => $oOrder->oxorder__oxbillcountry->value,
                "_oxCity" => $oOrder->oxorder__oxbillcity->value,
                "_oxPayment" => $oPayment->oxpayments__oxdesc->value,
                "_oxCustomer" => $oOrder->oxorder__oxbilllname->value.", ".$oOrder->oxorder__oxbillfname->value,
                "_oxOrderAmount" => $oOrder->oxorder__oxtotalbrutsum->value,
            );
            $this->psElkStackLog->saveToQueue($sType, $aLog);

            // get orderarticles
            $sType = "oxorderarticle";
            $aBasketContents = $this->_oBasket->getContents();
            while ( list( $sItemKey, $oBasketItem ) = each( $aBasketContents ) ) {
                unset($aLog);
                $oArticle = $oBasketItem->getArticle(false);

                // orderarticle request
                $aLog = array(
                    "_oxType" => $sType,
                    "_oxOrderId" => $oOrder->oxorder__oxordernr->value,
                    "_oxArtnum" => $oArticle->oxarticles__oxartnum->value,
                    "_oxId" => $oArticle->oxarticles__oxid->value,
                    "_oxAmount" => $oBasketItem->getAmount(),
                    "_oxTitle" => $oBasketItem->getTitle(),
                    "_oxDate" => $oOrder->oxorder__oxorderdate->value,
                    "_oxManufacturer" => $this->_psGetManufacturerTitle($oArticle)
                );
                $this->psElkStackLog->saveToQueue($sType, $aLog);
                unset($aLog);

                // if more then one amount per article
                for($count = 1; $count < $oBasketItem->getAmount(); $count++) {
                    $aLog = array(
                        "_oxType" => $sType,
                        "_oxOrderId" => $oOrder->oxorder__oxordernr->value,
                        "_oxArtnum" => $oArticle->oxarticles__oxartnum->value,
                        "_oxId" => $oArticle->oxarticles__oxid->value,
                        "_oxTitle" => $oBasketItem->getTitle(),
                        "_oxDate" => $oOrder->oxorder__oxorderdate->value
                    );
                    $this->psElkStackLog->saveToQueue($sType, $aLog);
                    unset($aLog);
                }
            }
            $this->_sendLogRequest = true;
        }
    }

    /*
     * Get manufacturer title by article oxid
     */
    protected function _psGetManufacturerTitle($oArticle)
    {
        if($this->getConfig()->getVersion() >= "4.8.1") {
            return $oArticle->getManufacturer()->getTitle();
        } else {
            $sSql = "SELECT oxtitle FROM oxmanufacturers WHERE oxid = ".oxDb::getDb()->quote( $oArticle->oxarticles__oxmanufacturerid->value );
            return oxDb::getDb()->getOne($sSql);
        }
    }

}