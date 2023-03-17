<?php

use OnPay\API\Util\PaymentMethods\Enums\Methods;

class Onpayio_Onpay_Model_Method_Anyday extends Onpayio_Onpay_Model_Method_Abstract {
    const METHOD_CODE = 'onpay_anyday';
    protected $_code = self::METHOD_CODE;
    protected $_formBlockType = 'onpay/method_anyday';

    protected function getMethod() {
        return Methods::ANYDAY;
    }
}
