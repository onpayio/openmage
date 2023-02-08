<?php
class Onpayio_Onpay_PaymentController extends Mage_Core_Controller_Front_Action {
    const ONPAY_PENDING_STATE = 'pending';
    const ONPAY_PROCESSING_STATE = 'processing';

    public function cancelAction() {
        $session = $this->getCheckoutSession();
        if ($session->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
            if ($order->getId()) {
                $order->cancel()->save();
            }
            Mage::helper('onpay/checkout')->restoreQuote();
        }
        $this->_redirect('checkout/cart');
    }

    public function successAction() {
        $validQuery = Mage::helper('onpay/api')->isReturnQueryValid($this->getRequest()->getParams());
        // Check if query was valid
        if ($validQuery) {
            $onpayUuid = $this->getRequest()->getParam('onpay_uuid');
            $onpayReference = $this->getRequest()->getParam('onpay_reference');
            if ($onpayReference !== null && $onpayUuid !== null) {
                // Get order from reference provided by OnPay
                $quote = Mage::getModel('sales/quote')->getCollection()->addFieldToFilter('reserved_order_id', $onpayReference)->getFirstItem();
                // Check if order is existing, and make it inactive if so
                if ($quote->getId()) {
                    $quote->setIsActive(false)->save();
                }
                // Redirect to generic success page
                $this->_redirect('checkout/onepage/success');
                return;
            }
        }
        // An error occured, restore cart and redirect to payment failure page
        Mage::helper('onpay/checkout')->restoreQuote();
        $this->_redirect('checkout/onepage/failure');
    }

    public function callbackAction() {
        $validQuery = Mage::helper('onpay/api')->isReturnQueryValid($this->getRequest()->getParams());

        $onpayUuid = $this->getRequest()->getParam('onpay_uuid');
        $onpayReference = $this->getRequest()->getParam('onpay_reference');
        $onpayErrorCode = $this->getRequest()->getParam('onpay_errorcode');

        // Check if query was valid and error code indicates success
        if (!$validQuery || null === $onpayReference || null === $onpayUuid || '0' !== $onpayErrorCode) {
            $this->jsonResponse('Invalid values', true, 400);
            return;
        }

        // Get quote by reserved order id with onpay reference value
        $quote = Mage::getModel('sales/quote')->getCollection()->addFieldToFilter('reserved_order_id', $onpayReference)->getFirstItem();

        // Check if quote exists
        if (null === $quote->getId()) {
            $this->jsonResponse('Order not found', true, 400);
            return;
        }

        // Get order
        $order = Mage::getModel('sales/order')->loadByAttribute('quote_id', $quote->getId())->loadByAttribute('increment_id', $onpayReference);
        
        // If order is not in pending state, no need to do anything with the order
        if ($order->getStatus() !== self::ONPAY_PENDING_STATE) {
            $this->jsonResponse('Order processed');
            return;
        }

        $payment = $order->getPayment();
        $payment->setLastTransId($onpayUuid);
        $payment->setTransactionId($onpayUuid);
        $payment->setIsTransactionClosed(false);
        $payment->setAdditionalInformation("OnpayUUID", $onpayUuid);

        if (null !== $this->getRequest()->getParam('onpay_3dsecure')) {
            $payment->setCcSecureVerify($this->getRequest()->getParam('onpay_3dsecure'));
        }
        
        if (null !== $this->getRequest()->getParam('onpay_cardtype')) {
            $payment->setCcType($this->getRequest()->getParam('onpay_cardtype'));
        }

        if (null !== $this->getRequest()->getParam('onpay_cardmask')) {
            $payment->setCcLast4(substr($this->getRequest()->getParam('onpay_cardmask'), -4));
            $payment->setCcNumberEnc($this->getRequest()->getParam('onpay_cardmask'));
        }

        if (null !== $this->getRequest()->getParam('onpay_expiry_month')) {
            $payment->setCcExpMonth($this->getRequest()->getParam('onpay_expiry_month'));
        }

        if (null !== $this->getRequest()->getParam('onpay_expiry_year')) {
            $payment->setCcExpYear($this->getRequest()->getParam('onpay_expiry_year'));
        }

        $transactionComment = __('OnPay - Transaction Authorized.');
        $transaction = $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH, null, false, $transactionComment);
        $payment->setAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $this->getRequest()->getParams());

        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, self::ONPAY_PROCESSING_STATE);

        $payment->save();
        $order->save();
        
        $this->jsonResponse('Order validated');
        return;
    }

    protected function getCheckoutSession() {
        $session = Mage::helper('onpay/checkout')->getCheckoutSession();
        $session->setQuoteId($session->getOnpayPaymentQuoteId(true));
        return $session;
    }

    protected function jsonResponse($message, $error = false, $responseCode = 200) {
        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json');
        $response = [];
        if (!$error) {
            $response = ['success' => $message, 'error' => false];
        } else {
            $response = ['error' => $message];
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }
}
