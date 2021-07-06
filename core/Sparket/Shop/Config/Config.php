<?php


namespace Sparket\Shop\Config;

use Sparket\DB\Crm;

class Config
{
    static function getConfigInfo()
    {
        $code = str_replace('\\', '.', __NAMESPACE__);

        if(!\BN_Var::$temp[$code])
        {
            \BN_Var::$temp[$code] = \BN::param('ShopConfig', 'json', false, Crm::getDB());
        }

        return \BN_Var::$temp[$code];
    }

}