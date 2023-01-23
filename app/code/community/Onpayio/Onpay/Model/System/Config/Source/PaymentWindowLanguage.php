<?php
class Onpayio_Onpay_Model_System_Config_Source_PaymentWindowLanguage {
    public function toOptionArray() {
        return [
            ['value' => 'da', 'label' => __('Danish')],
            ['value' => 'nl', 'label' => __('Dutch')],
            ['value' => 'en', 'label' => __('English')],
            ['value' => 'fr', 'label' => __('French')],
            ['value' => 'de', 'label' => __('German')],
            ['value' => 'it', 'label' => __('Italian')],
            ['value' => 'no', 'label' => __('Norwegian')],
            ['value' => 'es', 'label' => __('Spanish')],
            ['value' => 'se', 'label' => __('Swedish')],
        ];
    }
}