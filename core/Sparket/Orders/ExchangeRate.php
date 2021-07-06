<?php


namespace Sparket\Orders;


use Sparket\DB\Crm;

class ExchangeRate
{
    static function getRatePublic(string $date = null)
    {
        $db = Crm::getDB();

        $date = $date ? : date('Y-m-d');
        $data = $db->TableInfo('system_exchange_rates', 'ERateFrom', 'MXN', " AND ERateTo = :ERateTo AND ERateValuePublic > 0 AND ERateDate <= :ERateDate Order by ERateDate DESC", ['ERateTo' => 'USD', 'ERateDate' => $date]);

        if ($data)
        {
            return $data['ERateValuePublic'];
        }

        return null;
    }

}