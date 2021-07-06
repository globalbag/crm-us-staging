<?php

namespace Sparket\Shipping;

use Novut\Core\Query;
use Novut\Db\OrmGroup;
use Novut\Db\OrmOptions;
use Sparket\DB\Web;

class Postals
{
    use OrmGroup;

    function defaultORMOptions()
    {
        return (new OrmOptions())->setDb(Web::getDB())
            ->setTableName('web_shipping_postal')
            ->setPrimaryKey('PostalID')
            ->setClass(Postal::class)
            ->setCancelled(true, true);
    }

    static function find_by_code(string $code)
    {
        $query = new Query();
        $query->addParam('PostalCode', $code);
        $query->addQuery(" AND PostalCode = :PostalCode ");
        $query->addQuery(" AND Cancelled = 0 ");

        $postal = new Postal();
        return $postal->find_by_query($query);

    }
}