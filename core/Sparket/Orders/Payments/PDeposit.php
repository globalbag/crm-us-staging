<?php


namespace Sparket\Orders\Payments;

use Novut\Core\Query;
use Novut\Db\Orm;
use Novut\Db\OrmOptions;
use Sparket\Orders\Order;
use Sparket\Tools\Files\Files;

class PDeposit extends Payment
{

    function add_before()
    {
        $this->setWOrderID($this->order->getWOrderID());

        $this->setWOPaymentStatus(Status::pending['value']);

        $this->setWOPaymentMethod(Method::deposit['value']);

        $this->setWOPaymentCurrency($this->order->getWOrderCurrency());

        if(!file_exists($this->_file['tmp_name']))
        {
            \BN_Responses::dev("El archivo no ha sido definido.");
        }
    }

    function add_after()
    {
        if(!$this->getWOPaymentID())
        {
            \BN_Responses::dev("No fue posible agregar el pago intente nuevamente.");
        }

        // files Name
        $file_ext = pathinfo($this->_file['name'], PATHINFO_EXTENSION);

        $file = new \Novut\Tools\Files\S3;
        $file->setFileContent(file_get_contents($this->_file['tmp_name']));
        $file->setFileName("web/orders/payments/comprobante_de_pago_{$this->getWOPaymentID()}.{$file_ext}");

        $file_url = $file->push();

        $sql_update['WOPaymentFile'] = $file_url;

        $this->db->Update($this->getTableName(), $sql_update, $this->getPrimaryKey(), $this->getWOPaymentID());

        parent::add_after();
    }

    function cancel_after()
    {
        parent::cancel_after();
    }

}