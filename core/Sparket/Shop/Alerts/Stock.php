<?php


namespace Sparket\Shop\Alerts;

use Novut\Controllers\ErrorsSimple;
use Novut\Core\Query;
use Novut\Db\OrmOptions;
use Sparket\DB\Web;
use Sparket\Tools\Alerts\Alert;

class Stock
{
    use ErrorsSimple;

    /** @var \Sparket\DB\BN_PDOMethods|\Sparket\DB\BN_PDOStatement|\PDO|\PDOStatement $db */
    private $db;

    /** @var int $ProductID */
    private $ProductID;

    /** @var string $ContactName */
    private $ContactName;

    /** @var string $ContactEmail */
    private $ContactEmail;

    function __construct()
    {
        $this->db = Web::getDB();
    }

    /**
     * @return int
     */
    public function getProductID(): int
    {
        return $this->ProductID;
    }

    /**
     * @param int $ProductID
     */
    public function setProductID(int $ProductID): void
    {
        $this->ProductID = $ProductID;
    }

    /**
     * @return string
     */
    public function getContactName(): string
    {
        return $this->ContactName;
    }

    /**
     * @param string $ContactName
     */
    public function setContactName(string $ContactName): void
    {
        $this->ContactName = $ContactName;
    }

    /**
     * @return string
     */
    public function getContactEmail(): string
    {
        return $this->ContactEmail;
    }

    /**
     * @param string $ContactEmail
     */
    public function setContactEmail(string $ContactEmail): void
    {
        $this->ContactEmail = $ContactEmail;
    }

    public function add()
    {
        if(!$this->getContactName())
        {
            $this->error_set("El nombre del remitente no ha sido definido.");
        }

        if(!$this->getContactEmail())
        {
            $this->error_set("El correo del remitente no ha sido definido.");
        }

        if(!$this->getProductID())
        {
            $this->error_set("El id del producto no ha sido definido.");
        }

        $ProductInfo = $this->db->TableInfo('products', 'ProductID', $this->getProductID(), " AND Cancelled = 0 ");

        if(!$ProductInfo)
        {
            $this->error_set("El producto no existe.");
        }

        // validate exist same alert pending

        $caller_id = $ProductInfo['ProductID'];
        $caller_module = "shop.products";

        $query = new Query();
        $query->addParam('ContactEmail', $this->getContactEmail());
        $query->addQuery(" AND ContactEmail = :ContactEmail ");

        $query->addParam('CallerID', $caller_id);
        $query->addQuery(" AND CallerID = :CallerID ");

        $query->addParam('CallerModule', $caller_module);
        $query->addQuery(" AND CallerModule = :CallerModule ");

        $query->addQuery(" AND AlertStatus = 1 ");

        $alert = new Alert();
        $alert_info = $alert->find_by_query($query, true);

        if($alert_info)
        {
            return true;
        }

        $alert = new Alert;
        $alert->setContactName($this->getContactName());
        $alert->setContactEmail($this->getContactEmail());
        $alert->setAlertSubject("Producto Disponible - {$ProductInfo['ProductName']}");
        $alert->setAlertMessage("Hola <b>{$this->getContactName()}</b>! <br> Tenemos el gusto de notificarle que el producto <b>{$ProductInfo['ProductName']}</b> ya cuenta con existencias disponibles.");

        $alert->setCallerID($this->getProductID());
        $alert->setCallerModule('shop.products');
        $alert->add();
    }


}