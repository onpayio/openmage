<?php
class Onpayio_Onpay_Block_Method_Card extends Onpayio_Onpay_Block_Method_Abstract {
    protected function getLogo() {
        return 'generic';
    }
    protected function getName() {
        return __('Credit card');
    }
}
