<?php


namespace App\Products\Brands;


use Novut\Core\Query;
use Novut\Db\OrmGroup;
use Novut\Db\OrmOptionsGroup;
use Novut\Db\OrmOptions;

class Params
{

    use OrmGroup;

    static protected $types =
        [
            'select' => 'Select',
            'text' => 'Text',
        ];

    static function getORMOptions(): OrmOptions
    {


        if (!self::$_options || !self::$_options instanceof OrmOptions) {
            self::$_options = new OrmOptions();
            self::$_options->setTableName('inv_products_brands_params');
            self::$_options->setPrimaryKey('BParamID');
            self::$_options->setFieldName('BParamName');
            self::$_options->setCancelled(true, true);
            self::$_options->setDb(\BN::DB());
            self::$_options->setClass(Param::class);
        }

        return self::$_options;

    }

    /**
     * @param Brand $brand
     * @param $id
     * @return Param
     */
    static function find(Brand $brand, $id): Param
    {
        return new Param($brand, $id);
    }

    static public function name_exist(Brand $brand, string $name, $BParamID = null)
    {

        $param = new Param($brand);
        $query = new Query();

        $query->setWhereField('BParamName');
        $query->setWhereValue($name);

        $query->addQuery(" AND BrandID = :BrandID");
        $query->addParam('BrandID', $brand->getBrandID());


        if ($BParamID)
        {
            $query->addQuery(" AND BParamID != :BParamID");
            $query->addParam('BParamID', $BParamID);
        }

        $query->addQuery(" AND Cancelled != 1");

        $param->find_by_query($query);

        if ($param->getBrandID())
        {
            return $param;
        }
        else
        {
            return null;
        }

    }

    static public function code_exist(Brand $brand, string $code, $BParamID = null)
    {

        $param = new Param($brand);
        $query = new Query();

        $query->setWhereField('BParamCode');
        $query->setWhereValue($code);

        $query->addQuery(" AND BrandID = :BrandID");
        $query->addParam('BrandID', $brand->getBrandID());


        if ($BParamID)
        {
            $query->addQuery(" AND BParamID != :BParamID");
            $query->addParam('BParamID', $BParamID);
        }

        $query->addQuery(" AND Cancelled != 1");

        $param->find_by_query($query);

        if ($param->getBrandID())
        {
            return $param;
        }
        else
        {
            return null;
        }

    }

    static function list($brand, Query $query = null, OrmOptionsGroup $options = null): array
    {

        $brand = $brand && $brand instanceof Brand ? $brand : new Brand(is_numeric($brand) ? $brand : null);

        if (!$brand instanceof Brand || !$brand->getBrandID())
        {
            return [];
        }

        $options = $options ? : new OrmOptionsGroup();
        $options->setItemClass(function ($data) use ($brand)
        {
            $item = new Param($brand);
            $item->import($data);
            return $item;
        });

        $query = $query ? : new Query();
        $query->addQuery(" AND BrandID = :BrandID");
        $query->addParam('BrandID', $brand->getBrandID());
        $query->setOrder('RowOrder', "DESC");

        return self::_list($query, $options);

    }

    static function list_($brand, Query $query = null, OrmOptionsGroup $options = null): array
    {
        $params = [];

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
            $query->setOrder("BParamName");
            $query->setOrderOrientationASC();
        }

        foreach ($db->GroupInfo(self::getORMOptions()->getTableName(), false, false, $query->getQueryFull(), $query->getParams()) as $data)
        {
            $param = new Param($brand);
            $param->import($data);

            if ($options && $options->exportList())
            {
                $params[$param->getBParamID()] = $param->export();
            }
            else if ($options && $options->simpleList())
            {
                $params[$param->getBParamID()] = $param->getBParamName();
            }
            else
            {
                $params[$param->getBParamID()] = $param;
            }


        }



        return $params;

    }

    static function listSimple($brand, Query $query = null): array
    {
        return self::list($brand,$query, (new OrmOptionsGroup())->setSimple());
    }

    static function listAssoc($brand, Query $query = null): array
    {
        return self::list($brand,$query, (new OrmOptionsGroup())->setExport());
    }

    static function getTypes()
    {
        return self::$types ? : [];
    }

    static function valid_json()
    {

    }

    static function getName(Brand $brand, $BParamID)
    {
        $db = self::getORMOptions()->getDb();
        return $db->getValue(self::getORMOptions()->getTableName(), "BrandID", $brand->getBrandID(), 'BParamName', " AND BParamID = :BParamID AND Cancelled = 0 ",  ['BParamID' => $BParamID]);

    }

}