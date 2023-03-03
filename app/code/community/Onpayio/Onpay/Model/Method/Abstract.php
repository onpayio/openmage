<?php

abstract class Onpayio_Onpay_Model_Method_Abstract extends Mage_Payment_Model_Method_Abstract {
    protected $_isInitializeNeeded = true;
    protected $_canCapture = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_canCancel = true;
    protected $_canUseInternal = false;
    protected $_canUseForMultishipping = false;

    abstract protected function getMethod();

    public function getOrderPlaceRedirectUrl() {
        $paymentInfo = $this->getInfoInstance();
        $quoteId = $paymentInfo->getQuote()->getId();
        $order = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('quote_id', $quoteId)->getFirstItem();
        return Mage::helper('onpay/api')->getPaymentLink($order, $this->getMethod());
    }

    public function canUseForCurrency($currencyCode) {
        return Mage::helper('onpay/currency')->isCurrencySupported($currencyCode, $this->getMethod());
    }

    public function capture(Varien_Object $payment, $amount) {
        $info = $payment->getAdditionalInformation();
        $order = $payment->getOrder();
        if (array_key_exists('OnpayUUID', $info)) {
            $minorAmount = Mage::helper('onpay/currency')->majorToMinor($amount, $order->getOrderCurrencyCode(), '.');
            Mage::helper('onpay/api')->captureTransaction($info['OnpayUUID'], $minorAmount);
        } else {
            Mage::throwException('OnPay - Payment is not authorized. Unable to capture.');
        }
        return $this;
    }

    public function refund(Varien_Object $payment, $amount) {
        $info = $payment->getAdditionalInformation();
        $order = $payment->getOrder();
        if (array_key_exists('OnpayUUID', $info)) {
            $minorAmount = Mage::helper('onpay/currency')->majorToMinor($amount, $order->getOrderCurrencyCode(), '.');
            Mage::helper('onpay/api')->refundTransaction($info['OnpayUUID'], $minorAmount);
        } else {
            Mage::throwException('OnPay - Payment is not authorized. Unable to refund');
        }
        return $this;
    }

    public function void(Varien_Object $payment) {
        $info = $payment->getAdditionalInformation();
        if (array_key_exists('OnpayUUID', $info)) {
            Mage::helper('onpay/api')->cancelTransaction($info['OnpayUUID']);
        } else {
            Mage::throwException('OnPay - Payment is not authorized. Unable to cancel');
        }
        return $this;
    }

    public function cancel(Varien_Object $payment) {
        return $this->void($payment);
    }
}
