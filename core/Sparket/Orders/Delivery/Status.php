<?php

namespace Sparket\Orders\Delivery;

class Status
{
    const pending = [
        'value' => 10,
        'name' => 'Pendiente',
        'name_en' => 'Pending',
        'color' => '#f0ad4e',
        'key' => 'pending',
    ];

    const ontheway =
        [
            'value' => 15,
            'name' => 'En camino',
            'name_en' => 'On the way',
            'color' => '#0275d8',
            'key' => 'ontheway',
        ];

    const success = [
        'value' => 20,
        'name' => 'Finalizado',
        'name_en' => 'Delivered',
        'color' => '#5cb85c',
        'key' => 'success',
    ];

    const reject = [
        'value' => 30,
        'name' => 'Cancelado',
        'name_en' => 'Cancelled',
        'color' => '#ff0000',
        'key' => 'reject',
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

    static function getOptionListHTMLEn()
    {
        return \BN_Forms::option_list_render(self::getOptionList(), ['id' => 'value', 'name' => 'name_en']);
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