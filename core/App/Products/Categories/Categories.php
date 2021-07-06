<?php

namespace App\Products\Categories;

use Novut\Core\Query;
use Novut\Db\OrmLists;
use Novut\Db\OrmOptions;

class Categories
{
    /** @var OrmOptions $_options */
    static $_options;

    static function getORMOptions(): OrmOptions
    {


        if (!self::$_options || !self::$_options instanceof OrmOptions)
        {
            self::$_options = new OrmOptions();
            self::$_options->setTableName('inv_products_categories');
            self::$_options->setPrimaryKey('CategoryID');
            self::$_options->setFieldName('CategoryName');
            self::$_options->setCancelled(true, true);
            self::$_options->setClass(Category::class);
        }

        return self::$_options;

    }

    /**
     * @param OrmOptions $options
     */
    static function setORMOptions(OrmOptions $options)
    {
        self::$_options = $options;
    }

    /**
     * @param null $CategoryRef
     * @param Query|null $query
     * @return OrmLists
     */
    static protected function _orm_list($CategoryRef = null, Query $query = null): OrmLists
    {
        $query = $query ? : new Query();

        if ($CategoryRef && is_numeric($CategoryRef))
        {
            $query->addQuery(" AND CategoryRef = :CategoryRef");
            $query->addParam('CategoryRef', $CategoryRef);
        }

        return (new OrmLists(self::getORMOptions()))
            ->setQuery($query)
            ->setName('CategoryName');

    }


    /**
     * @param null $CategoryRef
     * @param Query|null $query
     * @return array
     */
    static function list($CategoryRef = null, Query $query = null): array
    {
        $orm_list = self::_orm_list($query);
        return $orm_list->getList();


    }

    /**
     * @param null $CategoryRef
     * @param Query|null $query
     * @return array
     */
    static function list_simple($CategoryRef = null, Query $query = null): array
    {
        $orm_list = self::_orm_list($CategoryRef, $query);
        return $orm_list->getListSimple();
    }

}