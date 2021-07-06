<?php


namespace Sparket\DB;


class Web
{
    static protected $db;

    static function getDB()
    {
        return self::$db ? : \BN_DB_WEB::getInstance();
    }

    static function setDB($db)
    {
        self::$db = $db;
    }
}