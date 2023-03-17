<?php
class Onpayio_Onpay_Block_Method_Vipps extends Onpayio_Onpay_Block_Method_Abstract {
    protected function getLogo() {
        return 'vipps';
    }
    protected function getName() {
        return __('Vipps');
    }
}
