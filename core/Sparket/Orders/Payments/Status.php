<?php

namespace Sparket\Orders\Payments;

class Status
{
    const pending = [
        'value' => 10,
        'name' => 'Pendiente',
        'name_en' => 'Pending',
        'color' => '#808B96',
        'key' => 'pending',
    ];

    const success = [
        'value' => 20,
        'name' => 'Aprobado',
        'name_en' => 'Approved',
        'color' => '#27AE60',
        'key' => 'success',
    ];

    const reject = [
        'value' => 30,
        'name' => 'Cancelado',
        "name_en" => 'Cancelled',
        'color' => '#ff0000',
        'key' => 'reject',
    ];

    const cancel = [
        'value' => 40,
        'name' => 'Cancelado',
        "name_en" => 'Cancelled',
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

//        print_r($OptionList);
//        print_r([$OptionKey]);
//        print_r([$OptionList[$OptionKey]]);


        return $OptionList[$OptionKey];
    }

    static function getOptionName($OptionID)
    {
        return self::getOptionInfo($OptionID)['name'];
    }

}