<?php
abstract class Onpayio_Onpay_Block_Method_Abstract extends Mage_Payment_Block_Form {
    abstract protected function getLogo();
    abstract protected function getName();

    public function getMethodLabelAfterHtml() {
        return sprintf('<img class="onpay-method-logo" src="%s" height="20" alt="%s"/>', $this->getSkinUrl('images/onpay/' . $this->getLogo() . '.svg'), $this->getName());
    }
}
