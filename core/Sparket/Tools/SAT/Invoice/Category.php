<?php

namespace Sparket\Tools\SAT\Invoice;

class Category
{
    /** @var array $category_list  */
    public $category_list = [

        'G01' => [
            'value' => 'G01',
            'name' => 'Adquisición de mercancias (G01)',
        ],
        'G03' => [
            'value' => 'G03',
            'name' => 'Gastos en general (G03)',
        ],
        'I08' => [
            'value' => 'I08',
            'name' => 'Otra maquinaria y equipo (I08)',
        ],
        'P01' => [
            'value' => 'P01',
            'name' => 'Por definir (P01)',
        ]

    ];

    static function getOptionList()
    {
        $obj = new self;

        return $obj->category_list;
    }

    static function getOptionListHTML()
    {
        return \BN_Forms::option_list_render(self::getOptionList(), ['id' => 'value', 'name' => 'name']);
    }

    static function getOptionInfo($OptionID)
    {
        $OptionList = self::getOptionList();

        return $OptionList[$OptionID];
    }

    static function getOptionName($OptionID)
    {
        return self::getOptionInfo($OptionID)['name'];
    }
}