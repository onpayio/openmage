<?php
class Onpayio_Onpay_Model_System_Config_Source_PaymentWindowDesign {
    public function toOptionArray() {
        $designs = [];
        foreach(Mage::helper('onpay/api')->getWindowDesigns()->paymentWindowDesigns as $design) {
            $designs[] = [
                'value' => $design->name,
                'label' => $design->name
            ];
        }
        return $designs;
    }
}