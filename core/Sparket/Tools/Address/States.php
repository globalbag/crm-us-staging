<?php

namespace Sparket\Tools\Address;

use Novut\Core\Query;
use Sparket\DB\Crm;

class States
{
    /** @var bool|\PDO|\PDOStatement|\Sparket\DB\BN_PDOMethods|\Sparket\DB\BN_PDOStatement $db  */
    private $db;

    function __construct()
    {
        $this->db = Crm::getDB();
    }

    function list_html(int $CountryID)
    {
        $query = new Query();
        $query->addParam('CountryID', $CountryID);
        $query->addQuery(" 1 AND CountryID = :CountryID ");

        return $this->db->OptionList('system_db_states', 'StateID', 'StateName', $query->getQuery(), $query->getParams(), 'StateName', 'html');
    }

    function name(int $StateID)
    {
        return $this->db->getValue('system_db_states', 'StateID', $StateID, 'StateName');
    }


}