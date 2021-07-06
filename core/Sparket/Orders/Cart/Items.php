<?php

namespace Sparket\Orders\Cart;

use Novut\Core\Query;
use Sparket\DB\Web;

class Items
{
    /** @var Cart $cart */
    private $cart;

    function __construct(Cart $cart)
    {
        $this->db = Web::getDB();

        $this->cart = $cart;

        if(!$this->cart->getWCartID() || ($this->cart->getWCartID() && $this->cart->getCancelled()))
        {
            \BN_Responses::dev("El carrito no existe.");
        }

    }

    function list(Query $query = null)
    {
        $_query = new Query();

        if($query)
        {
            $_query->setParams($query->getParams());
            $_query->setQuery($query->getQuery());
        }

        \BN_Load::model('productos');

        $_query->setSelect(" T1.*, T2.ProductDetailsDocs, T2.ProductCategories, T2.BrandID, T2.CategoryID, T2.ProductPath, T2.ProductDescription, T2.ProductPartNumber, T2.ProductImageDefault, T2.ProductDetailsImages ");
        $_query->setTable(" {$this->db->getTable('web_orders_cart_items')} T1 LEFT JOIN {$this->db->getTable('products')} T2 ON T1.ProductID = T2.ProductID ");

        $_query->addParam('WCartID', $this->cart->getWCartID());
        $_query->addQuery(" AND T1.WCartID = :WCartID ");

        $_query->addQuery(" AND T1.Cancelled = 0 ");

        foreach ($this->db->GroupInfo([$_query->getTable()], false, false, $_query->getQuery(), $_query->getParams(), 'ProductID', $_query->getSelect()) as $data)
        {
            $data = \BNModel\ProductProduct::productparse($data);

            $item_list[$data['WOItemID']] = $data;
        }

        return $item_list;
    }

    function info(int $WOItemID)
    {
        $_query = new Query();
        $_query->setSelect(" T1.*, T2.ProductPath, T2.ProductDescription, T2.ProductPartNumber, T2.ProductImageDefault, T2.ProductDetailsImages ");
        $_query->setTable(" {$this->db->getTable('web_orders_cart_items')} T1 LEFT JOIN {$this->db->getTable('products')} T2 ON T1.ProductID = T2.ProductID ");

        $_query->addParam('WCartID', $this->cart->getWCartID());
        $_query->addQuery(" AND T1.WCartID = :WCartID ");

        $_query->addParam('WOItemID', $WOItemID);
        $_query->addQuery(" AND T1.WOItemID = :WOItemID ");

        $_query->addQuery(" AND T1.Cancelled = 0 ");

        $item_info = $this->db->TableInfo([$_query->getTable()], false, false, $_query->getQuery(), $_query->getParams(), $_query->getSelect());

        if(!$item_info)
        {
            return false;
        }

        $item_info['ProductDetailsImages'] = $item_info['ProductDetailsImages'] ? \BN_Coders::json_decode($item_info['ProductDetailsImages']) : "";

        return $item_info;
    }

    function info_by_product(int $ProductID)
    {
        $query = new Query();
        $query->addParam('WCartID', $this->cart->getWCartID());
        $query->addQuery(" AND WCartID = :WCartID ");

        $query->addParam('ProductID', $ProductID);
        $query->addQuery(" AND ProductID = :ProductID ");

        $query->addQuery(" AND Cancelled = 0 ");

        return $this->db->TableInfo('web_orders_cart_items', false, false, $query->getQuery(), $query->getParams());
    }

}