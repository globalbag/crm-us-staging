<?php


namespace Sparket\DB;


class Crm
{
    static protected $db;

    static function getDB()
    {
        return self::$db ? : \BN::DB();
    }

    static function setDB($db)
    {
        self::$db = $db;
    }
}