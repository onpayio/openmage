<?php

include_once __DIR__ . '/../require.php';

use OnPay\OnPayAPI;
use OnPay\API\PaymentWindow;

class Onpayio_Onpay_Helper_Api extends Mage_Core_Helper_Abstract {
    const ONPAY_PLUGIN_VERSION = '0.0.1';

    public function getPaymentLink(?string $method) {
        $paymentWindow = $this->createPaymentWindow($method);
        $onpayApi = $this->getOnPayClient();
        $payment = $onpayApi->payment()->createNewPayment($paymentWindow);
        return $payment->getPaymentWindowLink();
    }

    public function getWindowDesigns() {
        $onpayApi = $this->getOnPayClient();
        return $onpayApi->gateway()->getPaymentWindowDesigns();
    }

    private function getOnPayClient() {
        $accessToken = Mage::getStoreConfig('payment/onpay/apikey');
        $tokenStorage = new \OnPay\StaticToken($accessToken);
        $onPayAPI = new OnPayAPI($tokenStorage, [
            'client_id' => 'Static token',
        ]);
        return $onPayAPI;
    }

    private function getOrder() {
        $quoteId = Mage::getSingleton('checkout/session')->getQuoteId();
        return Mage::getModel("sales/order")->load($quoteId);
    }

    protected function createPaymentWindow(?string $method) {
        $order = $this->getOrder();

        $payment = $order->getPayment();
        if (null !== $payment->getLastTransId()) {
            //Cannot redirect: This order is already processed
            return null;
        }

        $paymentWindow = new PaymentWindow();
        $testmode = 1;
        if ($this->getSetting('testmode') === '0') {
            $testmode = 0;
        }
        $paymentWindow->setTestMode($testmode);
        $paymentWindow->setPlatform('openmage', self::ONPAY_PLUGIN_VERSION, Mage::getVersion());
        $paymentWindow->setWebsite(Mage::getUrl());

        if ($method !== null) {
            $paymentWindow->setMethod($method);
        }

        $minorAmount = Mage::helper('onpay/currency')->majorToMinor($order->getGrandTotal(), $order->getOrderCurrencyCode(), '.');

        $paymentWindow->setCurrency($order->getOrderCurrencyCode());
        $paymentWindow->setAmount($minorAmount);
        $paymentWindow->setReference($order->getIncrementId());

        $paymentWindow->setDesign($this->getSetting('windowdesign'));
        $paymentWindow->setLanguage($this->getSetting('windowlanguage'));

        $paymentWindow->setDeclineUrl(Mage::getUrl('onpay/payment/cancel'));

        return $paymentWindow;
    }

    private function getSetting($setting, $entity = 'onpay') {
        return Mage::getStoreConfig('payment/' . $entity . '/' . $setting);
    }
}