<?php


namespace App\Products\Product;


use App\Products\Prices\PList;
use Novut\Core\Query;
use Novut\Db\OrmGroup;
use Novut\Db\OrmOptions;
use Novut\Db\OrmOptionsGroup;

class Prices
{
    use OrmGroup;

    static function getORMOptions(): OrmOptions
    {

        if (!self::$_options || !self::$_options instanceof OrmOptions)
        {
            self::$_options = new OrmOptions();
            self::$_options->setTableName('inv_products_prices');
            self::$_options->setPrimaryKey('PPriceID');
            self::$_options->setFieldName('PPriceName');
            self::$_options->setCancelled(true, false);
            self::$_options->setClass(Price::class);
        }

        return self::$_options;

    }

    static function getDefault(Product $product, PList $price_list = null)
    {
        $db = self::getORMOptions()->getDb();
        $price_list = $price_list ? : (new PList())->loadDefault(true);
        $price_data = $db->TableInfo(self::getORMOptions()->getTableName(), 'ProductID', $product->getProductID(), " AND PListID = :PListID AND Cancelled = 0 ORDER BY PPriceVolMin DESC", ['PListID' => $price_list->getPListID()]);

        if ($price_data)
        {
            try {
                $price = new Price($product, $price_list);
                $price->import($price_data);
                return $price;
            }
            catch (\Novut\Exceptions\Exception $e)
            {
                \BN_Responses::dev($e->getMessage());
            }
            return null;

        }
        else
        {
            return null;
        }
    }

    static function syncDefault(Product $product, PList $price_list = null)
    {
        $price = self::getDefault($product, $price_list);

        if ($product->getProductID() && $price)
        {
            $product->setPrice($price);
            $product->save();
        }

    }

    /**
     * @param Product $product
     * @param PList|null $price_list
     * @param Query|null $query
     * @param OrmOptionsGroup|null $options
     * @return array|Price[]
     */
    static function list(Product $product, PList $price_list = null, Query $query = null, OrmOptionsGroup $options = null)
    {
        if (!$product->getProductID())
        {
            return [];
        }

        $price_list = $price_list ? : (new PList())->loadDefault();

        $options = $options ? : new OrmOptionsGroup();
        $options->setItemClass(function($data) use ($product, $price_list)
        {
            $item = new Price($product, $price_list);
            $item->import($data);
            return $item;
        });

        $query = $query ? : new Query();
        $query->addQuery(" AND ProductID = :ProductID");
        $query->addParam('ProductID', $product->getProductID());

        $query->addQuery(" AND PListID = :PListID");
        $query->addParam('PListID', $price_list->getPListID());

        return self::_list($query, $options);
    }

}