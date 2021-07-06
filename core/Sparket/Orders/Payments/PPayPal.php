<?php


namespace Sparket\Orders\Payments;

use Novut\Core\Query;
use Novut\Db\Orm;
use Novut\Db\OrmOptions;
use Sparket\Orders\Order;

class PPayPal extends Payment
{

    function add_before()
    {
        $this->setWOrderID($this->order->getWOrderID());

        $this->getWOPaymentStatus() ? : $this->setWOPaymentStatus(Status::pending['value']);

        $this->setWOPaymentMethod(Method::paypal['value']);

        $this->setWOPaymentCurrency($this->order->getWOrderCurrency());
    }

}