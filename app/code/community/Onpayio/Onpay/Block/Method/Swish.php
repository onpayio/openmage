<?php
class Onpayio_Onpay_Block_Method_Swish extends Onpayio_Onpay_Block_Method_Abstract {
    protected function getLogo() {
        return 'swish';
    }
    protected function getName() {
        return __('Swish');
    }
}
