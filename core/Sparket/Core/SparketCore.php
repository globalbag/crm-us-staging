<?php


namespace Sparket\Core;

use Novut\Db\BN_PDOMethods;
use Novut\Db\BN_PDOStatement;
use Sparket\DB\Web;

trait SparketCore
{
    /** @var BN_PDOMethods|BN_PDOStatement|\PDO|\PDOStatement $db_web */
    protected $db_web;

    protected function getWebDB()
    {
        return Web::getDB();
    }

}

