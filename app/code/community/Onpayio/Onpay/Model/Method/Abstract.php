<?php
/**
 * MIT License
 *
 * Copyright (c) 2023 OnPay.io
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

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
