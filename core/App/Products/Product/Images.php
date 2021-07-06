<?php


namespace App\Products\Product;


use Novut\Core\Query;
use Novut\Db\OrmGroup;
use Novut\Db\OrmOptions;
use Novut\Db\OrmOptionsGroup;

class Images
{
    use OrmGroup;

    static function defaultORMOptions(): OrmOptions
    {
        return (new OrmOptions())
            ->setTableName('inv_products_images')
            ->setPrimaryKey('PImageID')
            ->setFieldName('PImageUrl')
            ->setCancelled(true, true)
            ->setClass(Image::class);
        ;
    }

    static public function update(Product $product)
    {
        $image_list = [];
        $image_defaul = "";
        /** @var Image $image */
        foreach (self::list($product) as $image)
        {
            $image_list[] = $image->getPImageUrl();
            if ($image->getPImageDefault())
            {
                $image_defaul = $image->getPImageUrl();
            }
        }

        $update_product['ProductImage'] = $image_defaul;
        $update_product['ProductImages'] = $image_list ? \BN_Coders::json_encode($image_list) : "";

        $db = Products::getORMOptions()->getDb();
        $db->Update(Products::getORMOptions()->getTableName(), $update_product, 'ProductID', $product->getProductID());
    }


    /**
     * @param $product
     * @param Query|null $query
     * @param OrmOptionsGroup|null $options
     * @return array|Image[]
     */
    static function list($product, Query $query = null, OrmOptionsGroup $options = null): array
    {

        $product = $product && $product instanceof Product ? $product : new Product(is_numeric($product) ? $product : null);

        if (!$product instanceof Product || !$product->getProductID())
        {
            return [];
        }

        $options = $options ? : new OrmOptionsGroup();
        $options->setItemClass(function ($data) use ($product)
        {
            $item = new Image($product);
            $item->import($data);
            return $item;
        });

        $query = $query ? : new Query();
        $query->addQuery(" AND ProductID = :ProductID");
        $query->addParam('ProductID', $product->getProductID());
        $query->setOrder('RowOrder', "ASC");

        return self::_list($query, $options);

    }


    static function sort_items(Product $product, $items)
    {
        $options = self::getORMOptions();
        $db = $options->getDb();

        if ($items && is_array($items))
        {
            $order = 0;
            foreach ($items as $PImageID)
            {
                $order++;
                $db->Update($options->getTableName(), ['RowOrder' => $order], 'PImageID', $PImageID, " AND ProductID = :ProductID AND Cancelled = 0", ['ProductID' => $product->getProductID()]);
            }

            self::update($product);

        }
    }

    static function set_default(Product $product, $PImageID)
    {

        if ($PImageID)
        {
            $options = self::getORMOptions();
            $db = $options->getDb();
            $db->Update($options->getTableName(), ['PImageDefault' => 0], 'ProductID', $product->getProductID(), "  AND Cancelled = 0");
            $db->Update($options->getTableName(), ['PImageDefault' => 1], 'PImageID', $PImageID, " AND ProductID = :ProductID AND Cancelled = 0", ['ProductID' => $product->getProductID()]);
            self::update($product);
        }

    }

}