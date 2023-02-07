<?php

class Onpayio_Onpay_Adminhtml_OnpayAuthController extends Mage_Adminhtml_Controller_Action {
    public function returnAction() {
        Mage::helper('onpay/api')->finishAuthSetup($this->getRequest()->getParams());
        $this->_redirect('adminhtml/system_config/edit/section/payment');
    }

    public function detachAuthAction() {
        Mage::helper('onpay/api')->detachAuth();
        $this->_redirect('adminhtml/system_config/edit/section/payment');
    }
}
