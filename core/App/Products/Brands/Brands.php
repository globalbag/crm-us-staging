<?php


namespace App\Products\Brands;


use Novut\Core\Query;
use Novut\Db\OrmGroup;
use Novut\Db\OrmLists;
use Novut\Db\OrmOptionsGroup;
use Novut\Db\OrmOptions;

class Brands
{

    use OrmGroup;

    static function getORMOptions(): OrmOptions
    {


        if (!self::$_options || !self::$_options instanceof OrmOptions)
        {
            self::$_options = new OrmOptions();
            self::$_options->setTableName('inv_products_brands');
            self::$_options->setPrimaryKey('BrandID');
            self::$_options->setCancelled(true, true);
            self::$_options->setClass(Brand::class);
            self::$_options->setFieldName('BrandName');
        }

        return self::$_options;

    }

    static public function code_exist(string $code, $BrandID = null)
    {

        $brand = new Brand();
        $query = new Query();

        $query->setWhereField('BrandCode');
        $query->setWhereValue($code);


        if ($BrandID)
        {
            $query->addQuery(" AND BrandID != :BrandID");
            $query->addParam('BrandID', $BrandID);
        }

        $query->addQuery(" AND Cancelled != 1");

        $brand->find_by_query($query);

        if ($brand->getBrandID())
        {
            return $brand;
        }
        else
        {
            return null;
        }

    }

    static public function name($BrandID)
    {
        if (!isset(\BN_Var::$temp['BrandName'][$BrandID]))
        {
            \BN_Var::$temp['BrandName'][$BrandID] = (new Brand($BrandID))->getBrandName();
        }

        return \BN_Var::$temp['BrandName'][$BrandID];

    }

    static public function name_exist(string $name, $BrandID = null)
    {

        $brand = new Brand();
        $query = new Query();

        $query->setWhereField('BrandName');
        $query->setWhereValue($name);


        if ($BrandID)
        {
            $query->addQuery(" AND BrandID != :BrandID");
            $query->addParam('BrandID', $BrandID);
        }

        $query->addQuery(" AND Cancelled != 1");

        $brand->find_by_query($query);

        if ($brand->getBrandID())
        {
            return $brand;
        }
        else
        {
            return null;
        }

    }

    /**
     * @param Query|null $query
     * @param OrmOptionsGroup|null $options
     * @return array
     */
    static function list(Query $query = null, OrmOptionsGroup $options = null): array
    {

        $brands = [];

        $db = self::getORMOptions()->getDb();
        $query = $query ? : new Query();

        if (!$query->getOrder())
        {
            $query->setOrder("BrandName");
            $query->setOrderOrientationASC();
        }

        foreach ($db->GroupInfo(self::getORMOptions()->getTableName(), false, false, $query->getQueryFull(), $query->getParams()) as $data)
        {
            $brand = new Brand();
            $brand->import($data);

            if ($options && $options->exportList())
            {
                $brands[$brand->getBrandID()] = $brand->export();
            }
            else if ($options && $options->simpleList())
            {
                $brands[$brand->getBrandID()] = $brand->getBrandName();
            }
            else
            {
                $brands[$brand->getBrandID()] = $brand;
            }


        }

        return $brands;

    }

    /**
     * @param Query|null $query
     * @return array
     */
    static function list_simple(Query $query = null): array
    {
        $orm_list = self::_orm_list($query);
        return $orm_list->getListSimple('BrandName');
    }


}