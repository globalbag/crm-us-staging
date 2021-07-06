<?php

namespace Sparket\Tools\Address;

use Novut\Core\Query;
use Sparket\DB\Crm;

class Postal
{
    /** @var bool|\PDO|\PDOStatement|\Sparket\DB\BN_PDOMethods|\Sparket\DB\BN_PDOStatement $db  */
    private $db;

    function __construct()
    {
        $this->db = Crm::getDB();
    }

    function info(int $PostalID)
    {
        $query = new Query();
        $query->addParam('PostalID', $PostalID);
        $query->addQuery(" AND PostalID = :PostalID ");

        return $this->db->TableInfo('system_db_states_zip', false, false, $query->getQuery(), $query->getParams());
    }

    function info_by_code($PostalCode, int $CountryID, string $PostalNeighborhood = "")
    {
        if ($PostalCode && $CountryID)
        {
            $query = new Query();

            $query->addParam('PostalCode', $PostalCode);
            $query->addQuery(" AND PostalCode = :PostalCode ");

            $query->addParam('CountryID', $CountryID);
            $query->addQuery(" AND CountryID = :CountryID ");

            if($PostalNeighborhood)
            {
                $query->addParam('PostalNeighborhood', $PostalNeighborhood);
                $query->addQuery(" AND PostalNeighborhood = :PostalNeighborhood ");
            }

            return $this->db->TableInfo('system_db_states_zip', false, false, $query->getQuery(), $query->getParams());
        }

        return [];
    }

    function list_by_code($PostalCode, int $CountryID)
    {
        if ($PostalCode)
        {
            $query = new Query();

            $query->addParam('PostalCode', $PostalCode);
            $query->addQuery(" AND PostalCode = :PostalCode ");

            $query->addParam('CountryID', $CountryID);
            $query->addQuery(" AND CountryID = :CountryID ");

            return $this->db->GroupInfo('system_db_states_zip', false, false, $query->getQuery(), $query->getParams(), 'PostalID');
        }

        return [];

    }

    function list_html_by_code($PostalCode, int $CountryID)
    {
        $neighborhood_list = [];

        foreach ($this->list_by_code($PostalCode, $CountryID) as $PostalInfo)
        {
            $neighborhood_list[$PostalInfo['PostalNeighborhood']] = $PostalInfo['PostalNeighborhood'];
        }

        return ( $neighborhood_list ? \BN::OptionListHTMLRender($neighborhood_list) : "" );
    }

}