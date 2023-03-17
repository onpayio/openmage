<?php

use OnPay\API\Util\PaymentMethods\Enums\Methods;

class Onpayio_Onpay_Model_Method_Card extends Onpayio_Onpay_Model_Method_Abstract {
    const METHOD_CODE = 'onpay_card';
    protected $_code = self::METHOD_CODE;
    protected $_formBlockType = 'onpay/method_card';

    protected function getMethod() {
        return Methods::CARD;
    }
}
