<?php


namespace App\Products\Prices;


use Novut\Core\Query;
use Novut\Db\OrmGroup;
use Novut\Db\OrmOptions;
use Novut\Db\OrmOptionsGroup;

class PLists
{

    use OrmGroup;

    static function getORMOptions(): OrmOptions
    {

        if (!self::$_options || !self::$_options instanceof OrmOptions)
        {
            self::$_options = new OrmOptions();
            self::$_options->setTableName('inv_products_plists');
            self::$_options->setPrimaryKey('PListID');
            self::$_options->setFieldName('PListName');
            self::$_options->setCancelled(true, false);
            self::$_options->setClass(PList::class);
        }

        return self::$_options;

    }


    /**
     * @param $id
     * @return PList
     */
    static function find($id): PList
    {
        return new PList($id);
    }

    static function list(Query $query = null, OrmOptionsGroup $options = null): array
    {
        $options = $options ? : new OrmOptionsGroup();
        $options->setItemClass(function ($data)
        {
            return new PList($data);
        });

        $_query = $query;
        $query = $query ? : new Query();

        if (!$query->getOrder())
        {
            $query->addQuery(" ORDER BY PListDefault DESC, PListName ASC");
        }

        $price_lists = self::_list($query, $options);

        if (!$price_lists && !$_query)
        {
            $db = self::getORMOptions()->getDb();

            $total_price_lists = $db->Total(self::getORMOptions()->getTableName(),  false, false, " AND Cancelled = 0");

            if ($total_price_lists < 1)
            {
                $default_price_list = new PList();
                $default_price_list->setPListName("Default");
                $default_price_list->setPListDefault(true);
                $default_price_list->add();

                return self::list($_query, $options);

            }

        }

        return $price_lists;

    }

    /**
     * @param Query|null $query
     * @param OrmOptionsGroup|null $options
     * @return array
     */
    static function list_(Query $query = null, OrmOptionsGroup $options = null): array
    {

        $price_lists = [];

        $db = self::getORMOptions()->getDb();
        $_query = $query;
        $query = $query ? : new Query();

        if (!$query->getOrder())
        {
            $query->addQuery(" ORDER BY PListDefault DESC, PListName ASC");
        }

        foreach ($db->GroupInfo(self::getORMOptions()->getTableName(), false, false, $query->getQuery(), $query->getParams()) as $data)
        {
            $price_list = new PList($data);

            if ($options && $options->exportList())
            {
                $price_lists[$price_list->getPListID()] = $price_list->export();
            }
            else if ($options && $options->simpleList())
            {
                $price_lists[$price_list->getPListID()] = $price_list->getPListName();
            }
            else
            {
                $price_lists[$price_list->getPListID()] = $price_list;
            }


        }

        if (!$price_lists && !$_query)
        {
            $total_price_lists = $db->Total(self::getORMOptions()->getTableName(),  false, false, " AND Cancelled = 0");

            if ($total_price_lists < 1)
            {
                $default_price_list = new PList();
                $default_price_list->setPListName("Default");
                $default_price_list->setPListDefault(true);
                $default_price_list->add();

                return self::list($_query, $options);

            }

        }

        return $price_lists;

    }

}