<?php

use OnPay\API\Util\PaymentMethods\Enums\Methods;

class Onpayio_Onpay_Model_Method_Swish extends Onpayio_Onpay_Model_Method_Abstract {
    const METHOD_CODE = 'onpay_swish';
    protected $_code = self::METHOD_CODE;
    protected $_formBlockType = 'onpay/method_swish';

    protected function getMethod() {
        return Methods::SWISH;
    }
}
