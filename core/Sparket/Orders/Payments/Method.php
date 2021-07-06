<?php

namespace Sparket\Orders\Payments;

class Method
{
    const paypal = [
        'value' => 'paypal',
        'name' => 'PayPal',
    ];

    const deposit = [
        'value' => 'deposit',
        'name' => 'Deposito Bancario',
    ];

    static function getOptionList()
    {
        $oClass = new \ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }

    static function getOptionListHTML()
    {
        return \BN_Forms::option_list_render(self::getOptionList(), ['id' => 'value', 'name' => 'name']);
    }

    static function getOptionInfo($OptionID)
    {
        $OptionList = self::getOptionList();

        $OptionKey = array_search($OptionID, array_column($OptionList, 'value', 'value'));

        return $OptionList[$OptionKey];
    }

    static function getOptionName($OptionID)
    {
        return self::getOptionInfo($OptionID)['name'];
    }
}