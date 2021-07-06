<?php

namespace Sparket\Orders;

class Status
{
    const pending = [
        'value' => 10,
        'name' => 'Pending',
        'color' => '#808B96',
        'key' => 'pending',
    ];

    const payment = [
        'value' => 20,
        'name' => 'Paid',
        'color' => '#2980B9',
        'key' => 'payment',
    ];

    const success = [
        'value' => 30,
        'name' => 'Delivered',
        'color' => '#27AE60',
        'key' => 'success',
    ];

    const cancelled = [
        'value' => 40,
        'name' => 'Canceled',
        'color' => '#ff0000',
        'key' => 'cancelled',
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

        $OptionKey = array_search($OptionID, array_column($OptionList, 'value', 'key'));

        return $OptionList[$OptionKey];
    }

    static function getOptionName($OptionID)
    {
        return self::getOptionInfo($OptionID)['name'];
    }
}