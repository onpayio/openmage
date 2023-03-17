<?php
class Onpayio_Onpay_Block_Method_Viabill extends Onpayio_Onpay_Block_Method_Abstract {
    protected function getLogo() {
        return 'viabill';
    }
    protected function getName() {
        return __('ViaBill');
    }
}
