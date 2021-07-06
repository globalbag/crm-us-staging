<?php


namespace Sparket\Orders\Cfdi;


use Novut\Db\OrmGroup;
use Novut\Db\OrmOptions;
use Sparket\DB\Web;

class Cfdis
{
    use OrmGroup;

    function defaultORMOptions()
    {
        return (new OrmOptions())->setDb(Web::getDB())
            ->setTableName('web_orders_cfdi')
            ->setPrimaryKey('WOCFDIID')
            ->setFieldName('WOCFDICode')
            ->setCreationDoc(true)
            ->setCreationDocUser(true)
            ->setClass(XCfdi::class)
            ->setCancelled(true, true);
    }
}