<?php

namespace Sparket\Orders;

use Novut\Core\Query;
use Novut\Db\OrmGroup;
use Novut\Db\OrmOptions;
use Novut\Db\OrmOptionsGroup;
use Sparket\DB\Web;
use Sparket\Orders\Delivery\Delivery;

class Items
{
    use OrmGroup;

    function defaultORMOptions()
    {
        return (new OrmOptions())->setDb(Web::getDB())
            ->setTableName('web_orders_items')
            ->setPrimaryKey('WOItemID')
            ->setClass(Item::class)
            ->setCancelled(true, true);
    }

    /**
     * @param Order $order
     * @return Delivery[]|array
     */
    static function list(Order $order, OrmOptionsGroup $options = null)
    {

        if (!$order->getWOrderID())
        {
            return [];
        }

        $options = $options ? : new OrmOptionsGroup();
        $options->setItemClass(function($data) use ($order)
        {
            $item = new Item($order);
            $item->import($data);
            return $item;
        });

        $query = new Query();
        $query->addQuery(" AND WOrderID = :WOrderID ", 'WOrderID', $order->getWOrderID());
        $query->addQuery(" AND Cancelled = 0 ");

        return self::_list($query, $options);
    }

    /**
     * @param Order $order
     * @return Delivery[]|array
     */
    static function list_by_delivery(Order $order, Delivery $delivery)
    {
        if (!$order->getWOrderID())
        {
            return [];
        }

        $options = new OrmOptionsGroup();
        $options->setItemClass(function($data) use ($order)
        {
            $item = new Item($order);
            $item->import($data);
            return $item;
        });

        $query = new Query();
        $query->setWhereField('WODeliveryID');
        $query->setWhereValue($delivery->getWODeliveryID());
        $query->addQuery(" AND WOrderID = :WOrderID ", 'WOrderID', $order->getWOrderID());
        $query->addQuery(" AND Cancelled = 0 ");

        return self::_list($query, $options);
    }


}