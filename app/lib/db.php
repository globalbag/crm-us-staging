<?php

use Novut\Db\BN_PDOMethods;
use Novut\Db\BN_PDOStatement;

class BN_DB_WEB extends \Novut\DB\Singleton
{
    /** @var BN_PDOMethods|BN_PDOStatement|\PDO|\PDOStatement $_instance */
    protected static $_instance;
    protected static $prefix;
    protected static $encode_type;

    /**
     * @return BN_PDOMethods|BN_PDOStatement|\PDO|\PDOStatement
     */
    public static function getInstance()
    {
        if (self::$_instance === NULL) {
            $dbconfig = BN_Var::$Database['connections']['web'];
            self::$encode_type = $dbconfig['charset'] ?: "utf8";
            self::$prefix = $dbconfig['prefix'] ?: "bn_";

            if (!$dbconfig['host'] || !$dbconfig['database'] || !$dbconfig['username']) {
                throw new InvalidArgumentException('You must supply connection options on first run');
            }
            self::$_instance = new self(
                "mysql:host={$dbconfig['host']};port={$dbconfig['port']};dbname={$dbconfig['database']}",
                $dbconfig['username'],
                $dbconfig['password'],
                [
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . self::$encode_type,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_STATEMENT_CLASS => [
                        '\Novut\Db\BN_PDOStatement', []
                    ]
                ]);
        }
        return self::$_instance;
    }
}