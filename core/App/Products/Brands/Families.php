<?php


namespace App\Products\Brands;


use Novut\Core\Query;
use Novut\Db\OrmGroup;
use Novut\Db\OrmLists;
use Novut\Db\OrmOptionsGroup;
use Novut\Db\OrmOptions;

class Families
{

    use OrmGroup;

    static function getORMOptions(): OrmOptions
    {

        if (!self::$_options || !self::$_options instanceof OrmOptions)
        {
            self::$_options = new OrmOptions();
            self::$_options->setTableName('inv_products_brands_family');
            self::$_options->setPrimaryKey('BFamilyID');
            self::$_options->setFieldName('BFamilyName');
            self::$_options->setCancelled(true, true);
            self::$_options->setClass(Family::class);
        }

        return self::$_options;

    }

    /**
     * @param Brand $brand
     * @param $id
     * @return Family
     */
    static function find(Brand $brand, $id): Family
    {
        return new Family($brand, $id);
    }

    /**
     * @param Brand $brand
     * @param string $name
     * @param null $BFamilyID
     * @return Family|null
     */
    static public function name_exist(Brand $brand, string $name, $BFamilyID = null)
    {

        $family = new Family($brand);
        $query = new Query();

        $query->setWhereField('BFamilyName');
        $query->setWhereValue($name);

        $query->addQuery(" AND BrandID = :BrandID");
        $query->addParam('BrandID', $brand->getBrandID());


        if ($BFamilyID)
        {
            $query->addQuery(" AND BFamilyID != :BFamilyID");
            $query->addParam('BFamilyID', $BFamilyID);
        }

        $query->addQuery(" AND Cancelled != 1");

        $family->find_by_query($query);

        if ($family->getBrandID())
        {
            return $family;
        }
        else
        {
            return null;
        }

    }

    /**
     * @param Brand $brand
     * @param string $code
     * @param null $BFamilyID
     * @return Family|null
     */
    static public function code_exist(Brand $brand, string $code, $BFamilyID = null)
    {

        $family = new Family($brand);
        $query = new Query();

        $query->setWhereField('BFamilyCode');
        $query->setWhereValue($code);

        $query->addQuery(" AND BrandID = :BrandID");
        $query->addParam('BrandID', $brand->getBrandID());


        if ($BFamilyID)
        {
            $query->addQuery(" AND BFamilyID != :BFamilyID");
            $query->addParam('BFamilyID', $BFamilyID);
        }

        $query->addQuery(" AND Cancelled != 1");

        $family->find_by_query($query);

        if ($family->getBrandID())
        {
            return $family;
        }
        else
        {
            return null;
        }

    }

    static function list(Brand $brand, Query $query = null, OrmOptionsGroup $options = null): array
    {
        $brand = $brand && $brand instanceof Brand ? $brand : new Brand(is_numeric($brand) ? $brand : null);
        \BN_Var::$temp['aaa'] = true;

        if (!$brand instanceof Brand || !$brand->getBrandID())
        {
            return [];
        }

        $options = $options ? : new OrmOptionsGroup();
        $options->setItemClass(function($data) use ($brand)
        {

            $item = new Family($brand);
            $item->import($data);
            return $item;

        });

        $query = $query ? : new Query();
        $query->addQuery(" AND BrandID = :BrandID");
        $query->addParam('BrandID', $brand->getBrandID());

        return self::_list($query, $options);

    }

    /**
     * @param Brand $brand
     * @param Query|null $query
     * @param OrmOptionsGroup|null $options
     * @return array
     */
    static function list_(Brand $brand, Query $query = null, OrmOptionsGroup $options = null): array
    {

        $families = [];

        $brand = $brand && $brand instanceof Brand ? $brand : new Brand(is_numeric($brand) ? $brand : null);

        if (!$brand instanceof Brand || !$brand->getBrandID())
        {
            return [];
        }

        $db = self::getORMOptions()->getDb();
        $query = $query ? : new Query();
        $query->addQuery(" AND BrandID = :BrandID");
        $query->addParam('BrandID', $brand->getBrandID());
        $query->addQuery(" AND Cancelled = 0");

        if (!$query->getOrder())
        {
            $query->setOrder("BFamilyName");
            $query->setOrderOrientationASC();
        }

        foreach ($db->GroupInfo(self::getORMOptions()->getTableName(), false, false, $query->getQueryFull(), $query->getParams()) as $data)
        {
            $family = new Family($brand);
            $family->import($data);

            if ($options && $options->exportList())
            {
                $families[$family->getBFamilyID()] = $family->export();
            }
            else if ($options && $options->simpleList())
            {
                $families[$family->getBFamilyID()] = $family->getBFamilyName();
            }
            else
            {
                $families[$family->getBFamilyID()] = $family;
            }


        }

        return $families;


    }

    /**
     * @param Brand $brand
     * @param Query|null $query
     * @return array
     */
    static function list_simple(Brand $brand, Query $query = null): array
    {

        return self::list($brand, $query, (new OrmOptionsGroup())->setSimple());
    }

    /**
     * @param Brand $brand
     * @param Query|null $query
     * @return array
     */
    static function list_export(Brand $brand, Query $query = null): array
    {

        return self::list($brand, $query, (new OrmOptionsGroup())->setExport());
    }

    /**
     * @param Brand $brand
     * @param Family $family
     * @return array
     */
    static function paramCollection(Brand $brand, Family $family): array
    {
        $params = [];

        foreach (\BN_Coders::json_decode($family->getBFamilyParams()) as $BParamInfo)
        {
            $param_details = \App\Products\Brands\Params::find($brand, $BParamInfo['id'])->export();
            if ($param_details['BParamData'])
            {
                $param_details['BParamData'] = \BN_Coders::json_decode($param_details['BParamData']);
            }
            $params[$param_details['BParamID']] = $param_details;
        }

        return $params;
    }

}