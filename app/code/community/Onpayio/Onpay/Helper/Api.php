<?php

include_once __DIR__ . '/../require.php';

use OnPay\OnPayAPI;
use OnPay\API\PaymentWindow;

class Onpayio_Onpay_Helper_Api extends Mage_Core_Helper_Abstract {

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
        $paymentWindow->setGatewayId('test');
        $paymentWindow->setSecret('test');
        $paymentWindow->setTestMode(1); // TODO: Set Testmode value from settings
        $paymentWindow->setPlatform('OpenMage', '', ''); // TODO: Set plugin and openmage version
        $paymentWindow->setWebsite('https://placeholder.onpay.io/'); // TODO: Set website value

        if ($method !== null) {
            $paymentWindow->setMethod($method);
        }

        $minorAmount = Mage::helper('onpay/currency')->majorToMinor($order->getGrandTotal(), $order->getOrderCurrencyCode(), '.');

        $paymentWindow->setCurrency($order->getOrderCurrencyCode());
        $paymentWindow->setAmount($minorAmount);
        $paymentWindow->setReference($order->getIncrementId());

        return $paymentWindow;
    }
}