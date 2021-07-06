<?php


namespace Sparket\Orders\Cfdi;


use Novut\Core\Query;
use Novut\Db\OrmOptionsGroup;
use Sparket\Orders\Order;

class PReceipts extends Cfdis
{

    /**
     * @param Order $order
     * @return PReceipt[]|array
     */
    static function list(Order $order)
    {

        if (!$order->getWOrderID())
        {
            return [];
        }

        $options = new OrmOptionsGroup();
        $options->setItemClass(function($data) use ($order)
        {
            $item = new PReceipt($order);
            $item->import($data);
            return $item;
        });

        $query = new Query();
        $query->addQuery(" AND WOrderID = :WOrderID", 'WOrderID', $order->getWOrderID());
        $query->addQuery(" AND WOCFDIType = :WOCFDIType", 'WOCFDIType', 'preceipt');

        return self::_list($query, $options);

    }

    static function find_by_UUID(Order $order, string $UUID)
    {
        $query = new Query();
        $query->setWhereField('WOrderID');
        $query->setWhereValue($order->getWOrderID());
        $query->addQuery(' AND Cancelled = 0 AND WOCFDIFFiscal = :WOCFDIFFiscal', 'WOCFDIFFiscal', $UUID);

        $Cfdi = new PReceipt($order);
        return $Cfdi->find_by_query($query);

    }

    static function updateOrder(Order $order)
    {
        $options = PReceipts::getORMOptions();
        $db = $options->getDb();
        $update_data['WOrderPReceiptStatus'] = sizeof(self::list($order)) > 0 ? 1 : 0;
        $db->Update('web_orders_orders', $update_data, 'WOrderID', $order->getWOrderID());
    }

}