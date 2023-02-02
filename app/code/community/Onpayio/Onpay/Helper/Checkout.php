<?php

class Onpayio_Onpay_Helper_Checkout extends Mage_Core_Helper_Abstract {
    public function restoreQuote() {
        $order = $this->getCheckoutSession()->getLastRealOrder();
        if ($order->getId()) {
            $quote = $this->_getQuote($order->getQuoteId());
            if ($quote->getId()) {
                $quote->setIsActive(1)
                    ->setReservedOrderId(null)
                    ->save();
                $this->getCheckoutSession()
                    ->replaceQuote($quote)
                    ->unsLastRealOrderId();
                return true;
            }
        }
        return false;
    }

    public function getCheckoutSession() {
        return Mage::getSingleton('checkout/session');
    }

    protected function _getQuote($quoteId) {
        return Mage::getModel('sales/quote')->load($quoteId);
    }
}