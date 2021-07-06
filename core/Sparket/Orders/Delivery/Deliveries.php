<?php

namespace Sparket\Orders\Delivery;

use Novut\Core\Query;
use Novut\Db\OrmGroup;
use Novut\Db\OrmOptions;
use Novut\Db\OrmOptionsGroup;
use Sparket\DB\Web;
use Sparket\Orders\Order;

class Deliveries
{
    use OrmGroup;

    function defaultORMOptions()
    {
        return (new OrmOptions())->setDb(Web::getDB())
            ->setTableName('web_orders_delivery')
            ->setPrimaryKey('WODeliveryID')
            ->setFieldName('WODeliveryTCode')
            ->setCreationDoc(true)
            ->setCreationDocUser(true)
            ->setClass(Delivery::class)
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
            $delivery = new Delivery($order);
            $delivery->import($data);
            return $delivery;
        });

        $query = new Query();
        $query->addQuery(" AND WOrderID = :WOrderID ", 'WOrderID', $order->getWOrderID());
        $query->addQuery(" AND Cancelled = 0 ");
        $query->setOrder('DocDate', 'DESC');

        return self::_list($query, $options);
    }

    static function find_by_Code(Order $order, string $TCode)
    {
        $query = new Query();
        $query->setWhereField('WOrderID');
        $query->setWhereValue($order->getWOrderID());
        $query->addQuery(" AND WODeliveryTCode = :WODeliveryTCode AND Cancelled = 0 ", 'WODeliveryTCode', $TCode);

        $delivery = new Delivery($order);
        return $delivery->find_by_query($query);
    }

    static function updateOrder(Order $order)
    {
        $options = self::getORMOptions();

        $db = $options->getDb();

        $delivery_list = Deliveries::list($order);

        $WOrderDeliveryStatus = 0;

        if($delivery_list)
        {
            foreach ($delivery_list as $delivery)
            {
                if($delivery->getWODeliveryStatus() == Status::reject['value'])
                {
                    continue;
                }

                if($delivery->getWODeliveryStatus() == Status::success['value'])
                {
                    $WOrderDeliveryStatus = 1;
                }
                else
                {
                    $WOrderDeliveryStatus = 0;

                    break;
                }
            }
        }

        // items without delivery id
        if($WOrderDeliveryStatus && $db->getValue('web_orders_items', 'WOrderID', $order->getWOrderID(), 'WOItemID', " AND WOItemDeliveryStatus != 2 AND Cancelled = 0 "))
        {
            $WOrderDeliveryStatus = 0;
        }

        $sql_update['WOrderDeliveryStatus'] = $WOrderDeliveryStatus;

        $db->Update('web_orders_orders', $sql_update, 'WOrderID', $order->getWOrderID());
    }

}