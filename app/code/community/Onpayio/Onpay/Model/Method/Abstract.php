<?php

abstract class Onpayio_Onpay_Model_Method_Abstract extends Mage_Payment_Model_Method_Abstract {
    protected $_isInitializeNeeded = true;
    protected $_canUseInternal = false;
    protected $_canUseForMultishipping = false;

    abstract protected function getMethod();

    public function getOrderPlaceRedirectUrl() {
        return Mage::helper('onpay/api')->getPaymentLink($this->getMethod());
    }
}
