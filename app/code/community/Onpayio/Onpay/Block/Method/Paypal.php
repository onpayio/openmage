<?php
class Onpayio_Onpay_Block_Method_Paypal extends Onpayio_Onpay_Block_Method_Abstract {
    protected function getLogo() {
        return 'paypal';
    }
    protected function getName() {
        return __('PayPal');
    }
}
