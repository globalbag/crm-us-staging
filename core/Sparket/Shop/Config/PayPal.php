<?php

namespace Sparket\Shop\Config;

class PayPal
{

    static function getSandboxInfo()
    {
        return Config::getConfigInfo()['paypal']['sandbox'];
    }

    static function getLiveInfo()
    {
        return Config::getConfigInfo()['paypal']['live'];
    }

    static function getPayPalInfo()
    {
        $PaypalInfo = Config::getConfigInfo()['paypal'];

        print_r($PaypalInfo); exit;

        return $PaypalInfo[$PaypalInfo['mode']];
    }
}