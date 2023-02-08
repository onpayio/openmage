<?php

use OnPay\API\Util\PaymentMethods\Enums\Methods;

class Onpayio_Onpay_Model_Method_Vipps extends Onpayio_Onpay_Model_Method_Abstract {
    const METHOD_CODE = 'onpay_vipps';
    protected $_code = self::METHOD_CODE;

    protected function getMethod() {
        return Methods::VIPPS;
    }
}
