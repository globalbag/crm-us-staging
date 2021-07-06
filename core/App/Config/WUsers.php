<?php


namespace App\Config;


class WUsers extends \Novut\Config\WUsers
{
    function getTable(): string
    {
        return "web_users";
    }

    function getDb()
    {
        return \BN_DB_WEB::getInstance();
    }

}