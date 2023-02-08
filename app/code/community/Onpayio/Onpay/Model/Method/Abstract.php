<?php

abstract class Onpayio_Onpay_Model_Method_Abstract extends Mage_Payment_Model_Method_Abstract {
    protected $_isInitializeNeeded = true;
    protected $_canCapture = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canUseInternal = false;
    protected $_canUseForMultishipping = false;

    abstract protected function getMethod();

    public function getOrderPlaceRedirectUrl() {
        return Mage::helper('onpay/api')->getPaymentLink($this->getMethod());
    }

    public function canUseForCurrency($currencyCode) {
        return Mage::helper('onpay/currency')->isCurrencySupported($currencyCode, $this->getMethod());
    }
}
