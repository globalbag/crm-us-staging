<?php

namespace Sparket\Shipping;

use Novut\Core\Query;
use Novut\Db\OrmGroup;
use Novut\Db\OrmOptions;
use Novut\Db\OrmOptionsGroup;
use Sparket\DB\Web;
use Sparket\Orders\Cfdi\Invoice;
use Sparket\Orders\Order;

class Offices
{
    use OrmGroup;

    function defaultORMOptions()
    {
        return (new OrmOptions())->setDb(Web::getDB())
            ->setTableName('web_shipping_offices')
            ->setPrimaryKey('OfficeID')
            ->setClass(Office::class)
            ->setCancelled(true, true);
    }

    /**
     * @param Order $order
     * @return Invoice[]|array
     */
    static function list(OrmOptionsGroup $options = null)
    {
        $options = $options ? : new OrmOptionsGroup();
        $options->setItemClass(function($data)
        {
            $office = new Office();
            $office->import($data);
            return $office;
        });

        $query = new Query();
        $query->addQuery(" AND Cancelled = 0 ");
        $query->setOrder('OfficeName', 'ASC');

        return self::_list($query, $options);
    }

}