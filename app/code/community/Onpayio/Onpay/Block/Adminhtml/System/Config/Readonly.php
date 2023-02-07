<?php

class Onpayio_Onpay_Block_Adminhtml_System_Config_Readonly extends Mage_Adminhtml_Block_System_Config_Form_Field {
    protected function _getElementHtml($element) {
        $element->setReadonly('readonly');
        return parent::_getElementHtml($element);
    }
}
