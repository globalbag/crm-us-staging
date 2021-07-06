<?php

namespace Sparket\Tools\Address;

use Sparket\DB\Crm;

class Countries
{
    /** @var bool|\PDO|\PDOStatement|\Sparket\DB\BN_PDOMethods|\Sparket\DB\BN_PDOStatement $db  */
    private $db;

    function __construct()
    {
        $this->db = Crm::getDB();
    }

    function list_html()
    {
        return $this->db->OptionList('system_db_countries', 'CountryID', 'CountryName', " 1 ", false, 'CountryName', 'html');
    }

    function name(int $CountryID)
    {
        return $this->db->getValue('system_db_countries', 'CountryID', $CountryID, 'CountryName');
    }


}