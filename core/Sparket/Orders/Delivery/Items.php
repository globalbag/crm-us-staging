<?php

namespace Sparket\Orders\Delivery;

use Novut\Core\Query;
use Novut\Db\OrmGroup;
use Novut\Db\OrmOptions;
use Novut\Db\OrmOptionsGroup;
use Sparket\DB\Web;

class Items
{
    use OrmGroup;

    function defaultORMOptions()
    {
        return (new OrmOptions())->setDb(Web::getDB())
            ->setTableName('web_orders_delivery_items')
            ->setPrimaryKey('WODItemID')
            ->setClass(Item::class)
            ->setCancelled(true, true);
    }

    /**
     * @param Delivery $delivery
     * @return Delivery[]|array
     */
    static function list(Delivery $delivery, OrmOptionsGroup $options = null)
    {
        if (!$delivery->getWODeliveryID())
        {
            return [];
        }

        $options = $options ? : new OrmOptionsGroup();
        $options->setItemClass(function($data) use ($delivery)
        {
            $item = new Item($delivery);
            $item->import($data);
            return $item;
        });

        $query = new Query();
        $query->addQuery(" AND WODeliveryID = :WODeliveryID ", 'WODeliveryID', $delivery->getWODeliveryID());
        $query->addQuery(" AND Cancelled = 0 ");

        return self::_list($query, $options);
    }

    static function updateItem(\Sparket\Orders\Item $item)
    {
        $options = self::getORMOptions();

        $db = $options->getDb();

        $query = new Query();

        $query->addSelect(" T1.* ");

        $query->setTable(
            "{$db->getTable('web_orders_delivery_items')} T1 LEFT JOIN {$db->getTable('web_orders_delivery')} T2 ON T1.WODeliveryID = T2 .WODeliveryID"
        );

        $query->addQuery(" AND T1.WOItemID = :WOItemID ", 'WOItemID', $item->getWOItemID());
        $query->addQuery(" AND T1.Cancelled = 0 ");
        $query->addQuery(" AND T2.WODeliveryStatus != :WODeliveryStatus ", 'WODeliveryStatus', Status::reject['value']);
        $query->addQuery(" AND T2.Cancelled = 0 ");

        $WOItemDelivery = array();
        $WOItemDeliveryItems = 0;

        foreach($db->GroupInfo([$query->getTable()], false, false, $query->getQuery(), $query->getParams(), false, $query->getSelect()) as $data)
        {
            $WOItemDeliveryItems += $data['WODItemQty'];

            $WOItemDelivery[$data['WODeliveryID']] = $data['WODeliveryID'];
        }

        $WOItemDelivery = $WOItemDelivery ? \BN_Coders::json_encode($WOItemDelivery) : "";

        // Item sin relación a un envio
        if($WOItemDeliveryItems == 0)
        {
            $WOItemDeliveryStatus = 0;
        }
        // Item con envio pendiendes
        else if ($WOItemDeliveryItems > 0 && $WOItemDeliveryItems < $item->getWOItemQty())
        {
            $WOItemDeliveryStatus = 1;
        }
        // Item con envio finalizado
        else if ($WOItemDeliveryItems > 0 && $WOItemDeliveryItems == $item->getWOItemQty())
        {
            $WOItemDeliveryStatus = 2;
        }

        $sql_update['WOItemDelivery'] = $WOItemDelivery;
        $sql_update['WOItemDeliveryItems'] = $WOItemDeliveryItems;
        $sql_update['WOItemDeliveryStatus'] = $WOItemDeliveryStatus;

        $db->Update('web_orders_items', $sql_update, 'WOItemID', $item->getWOItemID());
    }
}