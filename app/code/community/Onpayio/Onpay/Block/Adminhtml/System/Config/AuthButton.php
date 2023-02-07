<?php

class Onpayio_Onpay_Block_Adminhtml_System_Config_AuthButton extends Mage_Adminhtml_Block_System_Config_Form_Field {

    public function render($element){
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    protected function _getElementHtml($element){
        $url = Mage::helper('onpay/api')->getAuthUrl();
        $text = __('Setup with login through OnPay');

        if (Mage::helper('onpay/api')->isConnected()) {
            $url =  Mage::helper('adminhtml')->getUrl('adminhtml/onpayAuth/detachAuth', ['_secure' => true]);
            $text = __('Disconnect from OnPay');
        }
        
        return '<button onclick="window.location.href=\'' . $url . '\'; return false;">' . $text . '</button>';
    }
}
