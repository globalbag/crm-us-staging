<?php


namespace App\Products\Product;


use App\Products\Brands\Brand;
use Novut\Core\Query;
use Novut\Db\OrmGroup;
use Novut\Db\OrmOptions;
use Novut\Db\OrmOptionsGroup;

class Params
{
    use OrmGroup;

    static function getORMOptions(): OrmOptions
    {

        if (!self::$_options || !self::$_options instanceof OrmOptions)
        {
            self::$_options = new OrmOptions();
            self::$_options->setTableName('inv_products_params');
            self::$_options->setPrimaryKey('PParamID');
            self::$_options->setPrimaryKey('PParamKey');
            self::$_options->setCancelled(true, false);
            self::$_options->setClass(Param::class);
        }

        return self::$_options;

    }


    static function list(Product $product, Query $query = null, OrmOptionsGroup $options = null)
    {
        if (!$product->getProductID())
        {
            return [];
        }

        $options = $options ? : new OrmOptionsGroup();
        $options->setItemClass(function($data) use ($product)
        {
           $item = new Param($product);
           $item->import($data);
           return $item;
        });
        $options->setKey('PParamKey');
        $options->setValue('PParamValue');

        $query = $query ? : new Query();
        $query->addQuery(" AND ProductID = :ProductID");
        $query->addParam('ProductID', $product->getProductID());

        return self::_list($query, $options);
    }

    /**
     * @param Product $product
     * @param Query|null $query
     * @param OrmOptionsGroup|null $options
     * @return array
     */
    static function listx(Product $product, Query $query = null, OrmOptionsGroup $options = null): array
    {
        $params = [];

        if (!$product->getProductID())
        {
            return [];
        }


        $db = self::getORMOptions()->getDb();
        $query = $query ? : new Query();
        $query->addQuery(" AND ProductID = :ProductID");
        $query->addParam('ProductID', $product->getProductID());
        $query->addQuery(" AND Cancelled = 0");


        foreach ($db->GroupInfo(self::getORMOptions()->getTableName(), false, false, $query->getQuery(), $query->getParams()) as $data)
        {
            $param = new Param($product);
            $param->import($data);

            if ($options && $options->exportList())
            {
                $params[$param->getPParamKey()] = $param->export();
            }
            else if ($options && $options->simpleList())
            {
                $params[$param->getPParamKey()] = $param->getPParamValue();
            }
            else
            {
                $params[$param->getPParamKey()] = $param;
            }


        }

        return $params;
    }

    /**
     * @param Product $product
     * @param array $active_param_keys
     */
    static function purge(Product $product, array $active_param_keys)
    {

        if ($active_param_keys)
        {
            /** @var Param $param */
            foreach (self::list($product) as $param)
            {

                if  ($param->getPParamID() && !in_array($param->getBParamID(), $active_param_keys))
                {
                    $param->cancel();
                }
            }
        }


    }

    /**
     * @param Product $product
     */
    static function cancelAll(Product $product)
    {
        /** @var Param $param */
        foreach (self::list($product) as $param)
        {
            $param->cancel();
        }
    }

    static function sync_data(Product $product)
    {

        // family
        $db = Params::getORMOptions()->getDb();
        $brand = new Brand($product->getBrandID());
        $family = new \App\Products\Brands\Family($brand, $product->getBFamilyID());
        $product_family = new \App\Products\Product\Family($product);
        $product_params_data = Params::list($product);
        $product_data = $product->toolsGetDataRawFull();
        $product_update = [];

        foreach (array_keys($product_data) as $key)
        {
            if (substr($key, 0, 14) == 'ProductCustom_')
            {
                $product_update[$key] = "";
            }
        }

        foreach (\App\Products\Brands\Families::paramCollection($brand, $family) as $param_info)
        {

            /** @var Param $PParam */
            foreach ($product_params_data as $PParam)
            {

                if ($PParam->getBParamID() == $param_info['BParamID'])
                {
                    $product_family->addParam($PParam->getBParamID(), $PParam->getPParamKey(), $PParam->getPParamValue());

                    if (isset($product_data["ProductCustom_{$param_info['BParamCode']}"]))
                    {
                        $product_update["ProductCustom_{$param_info['BParamCode']}"] = $PParam->getPParamValue();
                    }

                }
            }

        }

        $product_family->save();


        // update product
        if ($product_update)
        {
            $db->Update('inv_products_products', $product_update, 'ProductID', $product->getProductID());
        }

    }

}