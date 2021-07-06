<?php

namespace Sparket\Orders\Payments;

use Novut\Core\Query;
use Novut\Db\OrmGroup;
use Novut\Db\OrmOptions;
use Sparket\DB\Web;
use Sparket\Orders\Delivery\Status;
use Sparket\Orders\Order;

class Payments
{
    use OrmGroup;

    function defaultORMOptions()
    {
        return (new OrmOptions())->setDb(Web::getDB())
            ->setTableName('web_orders_payments')
            ->setPrimaryKey('WOPaymentID')
            ->setCreationDoc(true)
            ->setCreationDocUser(true)
            ->setCreationDoc(true, true)

            ->setClass(Payment::class)
            ->setCancelled(true, true);
    }

    /** @var Order $order */
    private $order;

    function __construct(Order $order)
    {
         $this->db = Web::getDB();

        $this->order = $order;

        if(!$this->order->getWOrderID() || ($this->order->getWOrderID() && $this->order->getCancelled()))
        {
            \BN_Responses::dev("La orden no existe.");
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

        $_query->addParam('WOrderID', $this->order->getWOrderID());
        $_query->addQuery(" AND WOrderID = :WOrderID ");

        $_query->addQuery(" AND Cancelled = 0 ");
        $_query->addQuery(" ORDER BY DocDate DESC ");

        return $this->db->GroupInfo('web_orders_payments', false, false, $_query->getQuery(), $_query->getParams(), 'WOPaymentID');
    }

    function info(int $WOPaymentID)
    {
        $_query = new Query();

        $_query->addParam('WOrderID', $this->order->getWOrderID());
        $_query->addQuery(" AND WOrderID = :WOrderID ");

        $_query->addParam('WOPaymentID', $WOPaymentID);
        $_query->addQuery(" AND WOPaymentID = :WOPaymentID ");

        $_query->addQuery(" AND Cancelled = 0 ");

        return $this->db->TableInfo('web_orders_payments', false, false, $_query->getQuery(), $_query->getParams());
    }

    function info_pending()
    {
        $_query = new Query();

        $_query->addParam('WOrderID', $this->order->getWOrderID());
        $_query->addQuery(" AND WOrderID = :WOrderID ");

        $_query->addParam('WOPaymentStatus', Status::pending['value']);
        $_query->addQuery(" AND WOPaymentStatus = :WOPaymentStatus ");

        $_query->addQuery(" AND Cancelled = 0 ");

        return $this->db->TableInfo('web_orders_payments', false, false, $_query->getQuery(), $_query->getParams());
    }

    function info_reject()
    {
        $_query = new Query();

        $_query->addParam('WOrderID', $this->order->getWOrderID());
        $_query->addQuery(" AND WOrderID = :WOrderID ");

        $_query->addParam('WOPaymentStatus', Status::reject['value']);
        $_query->addQuery(" AND WOPaymentStatus = :WOPaymentStatus ");

        $_query->addQuery(" AND Cancelled = 0 ");

        return $this->db->TableInfo('web_orders_payments', false, false, $_query->getQuery(), $_query->getParams());
    }

    function info_success()
    {
        $_query = new Query();

        $_query->addParam('WOrderID', $this->order->getWOrderID());
        $_query->addQuery(" AND WOrderID = :WOrderID ");

        $_query->addParam('WOPaymentStatus', \Sparket\Orders\Payments\Status::success['value']);
        $_query->addQuery(" AND WOPaymentStatus = :WOPaymentStatus ");

        $_query->addQuery(" AND Cancelled = 0 ");

        return $this->db->TableInfo('web_orders_payments', false, false, $_query->getQuery(), $_query->getParams());
    }

    static function updateOrder(Order $order)
    {
        $options = self::getORMOptions();

        $db = $options->getDb();

        $payments_list = (new Payments($order))->list();

        $WOrderPaymentPending = 0;
        $WOrderPaymentStatus = 0;
        $WOrderTotalPayments = 0;
        $WOrderTotalPaymentsDebt = $order->getWOrderTotalTotal();

        foreach ($payments_list as $data)
        {
            $payment = new Payment($order);
            $payment->import($data);

            if($payment->getWOPaymentStatus() == \Sparket\Orders\Payments\Status::pending['value'])
            {
                $WOrderPaymentPending = 1;
            }

            if($payment->getWOPaymentStatus() != \Sparket\Orders\Payments\Status::success['value'])
            {
                continue;
            }

            $WOrderTotalPayments += $payment->getWOPaymentAmount();
            $WOrderTotalPaymentsDebt -= $payment->getWOPaymentAmount();

            $WOrderPaymentStatus = 1;
        }

        $update_data['WOrderPaymentPending'] = $WOrderPaymentPending;
        $update_data['WOrderPaymentStatus'] = $WOrderPaymentStatus;
        $update_data['WOrderTotalPayments'] = $WOrderTotalPayments;
        $update_data['WOrderTotalPaymentsDebt'] = $WOrderTotalPaymentsDebt;

        $db->Update('web_orders_orders', $update_data, 'WOrderID', $order->getWOrderID());
    }
}