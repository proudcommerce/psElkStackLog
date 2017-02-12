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
 * @version 1.3.0
 **/
class psElkStackLog_oxorder extends psElkStackLog_oxorder_parent
{
    /*
     * workaround for multiple calls
     */
    protected $_sendLogRequest = false;

    /**
     * Order checking, processing and saving method.
     * Before saving performed checking if order is still not executed (checks in
     * database oxorder table for order with know ID), if yes - returns error code 3,
     * if not - loads payment data, assigns all info from basket to new oxorder object
     * and saves full order with error status. Then executes payment. On failure -
     * deletes order and returns error code 2. On success - saves order (oxorder::save()),
     * removes article from wishlist (oxorder::_updateWishlist()), updates voucher data
     * (oxorder::_markVouchers()). Finally sends order confirmation email to customer
     * (oxemail::SendOrderEMailToUser()) and shop owner (oxemail::SendOrderEMailToOwner()).
     * If this is order recalculation, skipping payment execution, marking vouchers as used
     * and sending order by email to shop owner and user
     * Mailing status (1 if OK, 0 on error) is returned.
     *
     * @param oxBasket $oBasket              Shopping basket object
     * @param object   $oUser                Current user object
     * @param bool     $blRecalculatingOrder Order recalculation
     *
     * @return integer
     */
    public function finalizeOrder( oxBasket $oBasket, $oUser, $blRecalculatingOrder = false )
    {
        $mReturn = parent::finalizeOrder( $oBasket, $oUser, $blRecalculatingOrder );

        if(oxRegistry::getConfig()->getConfigParam('psElkStackLog_log_order') == true) {
            $this->_psLogElkStack($oBasket);
        }

        return $mReturn;
    }

    /*
     * Log order information to elastic search
     */
    protected function _psLogElkStack($oBasket)
    {
        if(!$this->_sendLogRequest) {
            $oPsElkStackLog = oxNew("pselkstacklog_core");
            $sType = "oxorder";
            $oOrder = oxNew("oxorder");
            $oOrder->load($oBasket->getOrderId());
            $oPayment = oxNew("oxpayment");
            $oPayment->load($oOrder->oxorder__oxpaymenttype->value);

            if(!$this->_stopLogging($oOrder->oxorder__oxbillemail->value)) {
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
                    "_oxOrderSource"=> $this->_getOrderSource($oOrder),
                );
                $oPsElkStackLog->saveToQueue($sType, $aLog);

                // get orderarticles
                $sType = "oxorderarticle";
                $aBasketContents = $oBasket->getContents();
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
                    $oPsElkStackLog->saveToQueue($sType, $aLog);
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
                        $oPsElkStackLog->saveToQueue($sType, $aLog);
                        unset($aLog);
                    }
                }
                $this->_sendLogRequest = true;
            }
        }
    }

    /*
     * Get order source (multichannel)
     */
    protected function _getOrderSource($oOrder)
    {
        if($oOrder->oxorder__oxpaymenttype->value == "emamazon") {
            // egate amazon modul
            return "Amazon";
        } elseif(strstr($oOrder->oxorder__oxremark->value, "eBay")) {
            // itratos ebay modul
            return "eBay";
        }
        return "Shop";
    }

    /*
     * Get manufacturer title by article oxid
     */
    protected function _psGetManufacturerTitle($oArticle)
    {
        $sSql = "SELECT oxtitle FROM oxmanufacturers WHERE oxid = ".oxDb::getDb()->quote( $oArticle->oxarticles__oxmanufacturerid->value );
        return oxDb::getDb()->getOne($sSql);
    }

    /*
     * Dont log this request?!
     */
    protected function _stopLogging($sName)
    {
        $sStopName = str_replace(" ", "", oxRegistry::getConfig()->getShopConfVar('psElkStackLog_log_search_stop'));
        $aStopName = explode(",", $sStopName);
        if (is_array($aStopName)) {
            if (in_array($sName, $aStopName)) {
                return true;
            }
        }
        return false;
    }

}