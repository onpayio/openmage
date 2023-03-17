<?php
class Onpayio_Onpay_Block_Method_Mpo extends Onpayio_Onpay_Block_Method_Abstract {
    protected function getLogo() {
        return 'mobilepay';
    }
    protected function getName() {
        return __('MobilePay');
    }
}
