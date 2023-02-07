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
        if ($onpayApi->isAuthorized()) {
            return $onpayApi->gateway()->getPaymentWindowDesigns();
        }
        return [];
    }

    public function isReturnQueryValid(array $query) {
        $paymentWindow = new \OnPay\API\PaymentWindow();
        $paymentWindow->setSecret($this->getSetting('secret'));
        return $paymentWindow->validatePayment($query);
    }

    public function getAuthUrl() {
        $onpayApi = $this->getOnPayClient(true);
        return $onpayApi->authorize();
    }

    public function finishAuthSetup(array $query) {
        $onpayApi = $this->getOnPayClient(true);
        if(array_key_exists('code', $query) && !$onpayApi->isAuthorized()) {
            // We're not authorized with the API, and we have a 'code' value at hand. 
            // Let's authorize, and save the gatewayID and secret accordingly.
            $onpayApi->finishAuthorize($query['code']);
            if ($onpayApi->isAuthorized()) {
                $this->setSetting('gateway', $onpayApi->gateway()->getInformation()->gatewayId);
                $this->setSetting('secret', $onpayApi->gateway()->getPaymentWindowIntegrationSettings()->secret);
                return true;
            }
        }
        return false;
    }

    public function detachAuth() {
        $this->deleteSetting('gateway');
        $this->deleteSetting('secret');
        $this->deleteSetting('oauth2_token');
    }

    
    public function isConnected() {
        $onpayApi = $this->getOnPayClient();
        return $onpayApi->isAuthorized();
    }

    private function getOnPayClient($prepareRedirectUri = false) {
        $accessToken = Mage::getStoreConfig('payment/onpay/apikey');
        $tokenStorage = Mage::getModel('onpay/TokenStorage');

        $params = [
            'client_id' => 'OnPay OpenMage',
            'redirect_uri' => ''
        ];
        if ($prepareRedirectUri) {
            $params['redirect_uri'] = Mage::helper('adminhtml')->getUrl('adminhtml/onpayAuth/return', ['_secure' => true]);
        }
        $onPayAPI = new OnPayAPI($tokenStorage, $params);
        
        return $onPayAPI;
    }

    private function getOrder() {
        $quoteId = Mage::getSingleton('checkout/session')->getQuoteId();
        return Mage::getModel("sales/order")->load($quoteId);
    }

    private function getReservedOrderId() {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        return $quote->getReservedOrderId();
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

        $paymentWindow->setReference($this->getReservedOrderId());

        $paymentWindow->setDesign($this->getSetting('windowdesign'));
        $paymentWindow->setLanguage($this->getSetting('windowlanguage'));

        $paymentWindow->setDeclineUrl(Mage::getUrl('onpay/payment/cancel'));
        $paymentWindow->setAcceptUrl(Mage::getUrl('onpay/payment/success'));
        $paymentWindow->setCallbackUrl(Mage::getUrl('onpay/payment/callback'));

        return $paymentWindow;
    }

    private function getSetting($setting, $entity = 'onpay') {
        return Mage::getStoreConfig('payment/' . $entity . '/' . $setting);
    }

    private function setSetting($setting, $value, $entity = 'onpay') {
        return Mage::getConfig()->saveConfig('payment/' . $entity . '/' . $setting, $value);
    }

    private function deleteSetting($setting, $entity = 'onpay') {
        return Mage::getConfig()->deleteConfig('payment/' . $entity . '/' . $setting);
    }
}