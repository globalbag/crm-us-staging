<?php

namespace novut\Inventory\Products;



class product
{
    protected $QuoteInfo, $QuoteItemInfo;

    function __construct()
    {
        \BN_Load::model('accounting');
    }



    static function main_menu($group = "")
    {
        $group = $group?:"default";
        $MainMenu = "";




        if ($group == 'default')
        {

            $MainMenu .= \BN_Layouts::menu_item("Productos", 'inventory/products/');
            $MainMenu .= \BN_Layouts::menu_item("Familias", 'inventory/families/');

            $MainMenu .= \BN_Layouts::menu_group('Configuraci&oacute;n', 'fa-cog',

                [
                    ['Marcas',  	            'products/brands/'],
                    ['Categor&iacute;as',  	'inventory/products/categories'],
                    ['Listas de Precios',  	    'inventory/products/plists/'],
                    ['Configuraci&oacute;n',  	    '#', '', ['onclick' => \BN_JSHelpers::route(\BN_Var::$BaseUrl.'inventory/products/config/', ['cmd'=>'config'])]],
                ]
            );



        }

        return $MainMenu;



    }

    function price_new_add($ProductID = "", $PListID = "", $PPricePrice = "", $PPriceDiscountLimit = "", $PPriceCurrencyID = "", $UpdateProduct = false)
    {
        if (!$PListID)
        {
            $PListInfo = $this->db->TableInfo('inv_products_plists', 'PListDefault', 1,  " AND Cancelled = 0");
            $PListID = $PListInfo['PListID'];
        }

        $InsertPrice['ProductID'] = $ProductID;
        $InsertPrice['PListID'] = $PListID;
        $InsertPrice['PPricePrice'] = \BN_Format::number_decimal($PPricePrice);
        $InsertPrice['PPriceDiscountLimit'] = \BN_Format::number_decimal($PPriceDiscountLimit);
        $InsertPrice['PPriceCurrencyID'] = $PPriceCurrencyID;
        $InsertPrice['PPriceCurrencyCode'] = \BN_Locale::currency_code($PPriceCurrencyID);

        $this->db->Insert('inv_products_prices', $InsertPrice);

        $PriceInfo = $InsertPrice;
        $PriceInfo['PPriceID'] = $this->db->LastInsertId();

        if ($UpdateProduct && (!$PListInfo || $PListInfo['PListDefault']))
        {
            $UpdatePPrice['PPriceID'] = $PriceInfo['PPriceID'];
            $UpdatePPrice['PListID'] = $PriceInfo['PListID'];
            $UpdatePPrice['PPricePrice'] = $PriceInfo['PPricePrice'];
            //$UpdatePPrice['PPriceDiscountLimit'] = $PriceInfo['PPriceDiscountLimit'];
            $UpdatePPrice['PPriceCurrencyID'] = $PriceInfo['PPriceCurrencyID'];
            $UpdatePPrice['PPriceCurrencyCode'] = $PriceInfo['PPriceCurrencyCode'];
            $this->db->Update('inv_products_products', $UpdatePPrice, 'ProductID', $ProductID, " AND Cancelled = 0");
        }

        return $PriceInfo;


    }

    function price_edit_save($PPriceID = "", $PListID = "", $PPricePrice = "", $PPriceDiscountLimit = "", $PPriceCurrencyID = "", $ProductID = "")
    {
        if (!$PListID)
        {
            $PListInfo = $this->db->TableInfo('inv_products_plists', 'PListDefault', 1,  " AND Cancelled = 0");
            $PListID = $PListInfo['PListID'];
        }

        if (!$PPriceID)
        {
            $InsertPrice['ProductID'] = $ProductID;
            $InsertPrice['PListID'] = $PListID;
            $InsertPrice['PPriceCurrencyID'] = $PPriceCurrencyID;
            $InsertPrice['PPriceCurrencyCode'] = \BN_Locale::currency_code($PPriceCurrencyID);

            $this->db->Insert('inv_products_prices', $InsertPrice);
            $PPriceID = $this->db->LastInsertId();

        }


        $UpdatePPrice['PListID'] = $PListID;
        $UpdatePPrice['PPricePrice'] = \BN_Format::number_decimal($PPricePrice);
        $UpdatePPrice['PPriceDiscountLimit'] = \BN_Format::number_decimal($PPriceDiscountLimit);
        $UpdatePPrice['PPriceCurrencyID'] = $PPriceCurrencyID;
        $UpdatePPrice['PPriceCurrencyCode'] = \BN_Locale::currency_code($PPriceCurrencyID);

        $this->db->Update('inv_products_prices', $UpdatePPrice, 'ProductID', $PPriceID, " AND Cancelled = 0");


        if ($ProductID)
        {
            $UpdatePPrice['PPriceID'] = $PPriceID;
            $UpdatePPrice['PListID'] = $UpdatePPrice['PListID'];
            $UpdatePPrice['PPricePrice'] = $UpdatePPrice['PPricePrice'];
            //$UpdatePPrice['PPriceDiscountLimit'] = $UpdatePPrice['PPriceDiscountLimit'];
            $UpdatePPrice['PPriceCurrencyID'] = $UpdatePPrice['PPriceCurrencyID'];
            $UpdatePPrice['PPriceCurrencyCode'] = $UpdatePPrice['PPriceCurrencyCode'];

            $this->db->Update('inv_products_products', $UpdatePPrice, 'ProductID', $ProductID, " AND Cancelled = 0");
        }


        return $UpdatePPrice;


    }

    function formatName($text = "")
    {
        $text = trim($text);

        $text = preg_replace('/[^\d\s\p{L}\x26\-\.,()]/u','',$text);

        return $text;
    }

}

class brands
{

    static function name($BrandID = "")
    {
        $db = \BN::DB();
        if (!\BN_Var::$temp['inv.prod.brands']['BrandName'][$BrandID])
        {
            $BrandInfo = $db->TableInfo('inv_products_brands', 'BrandID', $BrandID);
            \BN_Var::$temp['inv.prod.brands']['BrandName'][$BrandID] = $BrandInfo['BrandName'];
        }

        return \BN_Var::$temp['inv.prod.brands']['BrandName'][$BrandID];
    }
}

class categories
{

    static function name($CategoryID = "")
    {
        $db = \BN::DB();
        if (!\BN_Var::$temp['inv.prod.categories']['CategoryName'][$CategoryID])
        {
            $CategoryInfo = $db->TableInfo('inv_products_categories', 'CategoryID', $CategoryID);
            \BN_Var::$temp['inv.prod.categories']['CategoryName'][$CategoryID] = $CategoryInfo['CategoryName'];
        }

        return \BN_Var::$temp['inv.prod.categories']['CategoryName'][$CategoryID];
    }
}