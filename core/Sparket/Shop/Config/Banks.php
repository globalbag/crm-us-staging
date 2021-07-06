<?php

namespace Sparket\Shop\Config;

class Banks
{
    static function getBankList()
    {
        return Config::getConfigInfo()['bank_accounts'];
    }

}