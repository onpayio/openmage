<?php

include_once __DIR__ . '/../require.php';

use OnPay\TokenStorageInterface;

class Onpayio_Onpay_Model_TokenStorage implements TokenStorageInterface {
    const CONFIG_PATH = 'payment/onpay/oauth2_token';
    /**
     * @return string|null
     */
    public function getToken() {
        return Mage::getStoreConfig(self::CONFIG_PATH);
    }

    /**
     * @param  $token
     * @return void
     */
    public function saveToken($token) {
        $save = Mage::getConfig()->saveConfig(self::CONFIG_PATH, $token);
        Mage::getConfig()->cleanCache();
        return $save;
    }
}
