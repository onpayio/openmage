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

include_once __DIR__ . '/../require.php';

use OnPay\OnPayAPI;
use OnPay\API\PaymentWindow;

class Onpayio_Onpay_Helper_Api extends Mage_Core_Helper_Abstract {
    const ONPAY_PLUGIN_VERSION = '1.0.0';

    public function getPaymentLink(Mage_Sales_Model_Order $order, ?string $method) {
        $paymentWindow = $this->createPaymentWindow($order, $method);
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

    public function captureTransaction(string $transactionId, int $minorAmount) {
        $onpayApi = $this->getOnPayClient();
        return $onpayApi->transaction()->captureTransaction($transactionId, $minorAmount);
    }

    public function refundTransaction(string $transactionId, int $minorAmount) {
        $onpayApi = $this->getOnPayClient();
        return $onpayApi->transaction()->refundTransaction($transactionId, $minorAmount);
    }

    public function cancelTransaction(string $transactionId) {
        $onpayApi = $this->getOnPayClient();
        return $onpayApi->transaction()->cancelTransaction($transactionId);
    }

    private function getOnPayClient(bool $prepareRedirectUri = false) {
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

    private function getReservedOrderId() {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        return $quote->getReservedOrderId();
    }

    protected function createPaymentWindow(Mage_Sales_Model_Order $order, ?string $method) {
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