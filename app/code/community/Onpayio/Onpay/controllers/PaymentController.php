<?php
class Onpayio_Onpay_PaymentController extends Mage_Core_Controller_Front_Action {
    public function cancelAction() {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getOnpayPaymentQuoteId(true));
        if ($session->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
            if ($order->getId()) {
                $order->cancel()->save();
            }
            Mage::helper('onpay/checkout')->restoreQuote();
        }
        $this->_redirect('checkout/cart');
    }
}
