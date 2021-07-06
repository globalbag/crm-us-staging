<?php

namespace Sparket\Shop\Config;

class Orders
{

    static function getExpirationDays()
    {
        return Config::getConfigInfo()['expiration_days'];
    }

    static function getShippingCost()
    {
        return Config::getConfigInfo()['shipping_cost'];
    }

}