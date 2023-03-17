<?php

use OnPay\API\Util\PaymentMethods\Enums\Methods;

class Onpayio_Onpay_Model_Method_Paypal extends Onpayio_Onpay_Model_Method_Abstract {
    const METHOD_CODE = 'onpay_paypal';
    protected $_code = self::METHOD_CODE;
    protected $_formBlockType = 'onpay/method_paypal';

    protected function getMethod() {
        return Methods::PAYPAL;
    }
}
