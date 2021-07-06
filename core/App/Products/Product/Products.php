<?php

namespace App\Products\Product;


use App\Products\Brands\Brand;
use App\Products\Brands\Family;
use Novut\Core\Query;
use Novut\Db\OrmGroup;
use Novut\Db\OrmOptions;

class Products
{
    use OrmGroup;


    static function getORMOptions(): OrmOptions
    {

        if (!self::$_options || !self::$_options instanceof OrmOptions)
        {
            self::$_options = new OrmOptions();
            self::$_options->setTableName('inv_products_products');
            self::$_options->setPrimaryKey('ProductID');
            self::$_options->setFieldName('ProductName');
            self::$_options->setCancelled(true, false);
            self::$_options->setClass(Product::class);
        }

        return self::$_options;

    }


    /**
     * @param string $name
     * @param null $ProductID
     * @return Product|null
     */
    static public function name_exist(string $name, $ProductID = null)
    {

        $product = new Product();
        $query = new Query();

        $query->setWhereField('ProductName');
        $query->setWhereValue($name);

        if ($ProductID)
        {
            $query->addQuery(" AND ProductID != :ProductID");
            $query->addParam('ProductID', $ProductID);
        }

        $query->addQuery(" AND Cancelled != 1");

        $product->find_by_query($query);

        if ($product->getProductID())
        {
            return $product;
        }
        else
        {
            return null;
        }

    }

    /**
     * @param string $name
     * @param null $ProductID
     * @return Product|null
     */
    static public function code_exist(string $code, $ProductID = null)
    {

        $product = new Product();
        $query = new Query();

        $query->setWhereField('ProductCode');
        $query->setWhereValue($code);

        if ($ProductID)
        {
            $query->addQuery(" AND ProductID != :ProductID");
            $query->addParam('ProductID', $ProductID);
        }

        $query->addQuery(" AND Cancelled != 1");

        $product->find_by_query($query);

        if ($product->getProductID())
        {
            return $product;
        }
        else
        {
            return null;
        }

    }




}