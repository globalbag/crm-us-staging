<?php


namespace Sparket\Orders\Cfdi;

use Novut\Core\Query;
use Novut\Db\OrmOptionsGroup;
use Sparket\Orders\Order;

class Invoices extends Cfdis
{

    /**
     * @param Order $order
     * @return Invoice[]|array
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
            $item = new Invoice($order);
            $item->import($data);
            return $item;
        });

        $query = new Query();
        $query->addQuery(" AND WOrderID = :WOrderID", 'WOrderID', $order->getWOrderID());
        $query->addQuery(" AND WOCFDIType = :WOCFDIType", 'WOCFDIType', 'invoice');

        return self::_list($query, $options);

    }

    static function find_by_UUID(Order $order, string $UUID)
    {
        $query = new Query();
        $query->setWhereField('WOrderID');
        $query->setWhereValue($order->getWOrderID());
        $query->addQuery(' AND Cancelled = 0 AND WOCFDIFFiscal = :WOCFDIFFiscal', 'WOCFDIFFiscal', $UUID);

        $Cfdi = new Invoice($order);
        return $Cfdi->find_by_query($query);

    }

    static function updateOrder(Order $order)
    {
        $options = Invoices::getORMOptions();
        $db = $options->getDb();
        $update_data['WOrderInvoiceStatus'] = sizeof(self::list($order)) > 0 ? 1 : 0;
        $db->Update('web_orders_orders', $update_data, 'WOrderID', $order->getWOrderID());
    }
}