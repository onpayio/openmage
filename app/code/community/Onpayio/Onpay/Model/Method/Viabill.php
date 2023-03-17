<?php

use OnPay\API\Util\PaymentMethods\Enums\Methods;

class Onpayio_Onpay_Model_Method_Viabill extends Onpayio_Onpay_Model_Method_Abstract {
    const METHOD_CODE = 'onpay_viabill';
    protected $_code = self::METHOD_CODE;
    protected $_formBlockType = 'onpay/method_viabill';

    protected function getMethod() {
        return Methods::VIABILL;
    }
}
