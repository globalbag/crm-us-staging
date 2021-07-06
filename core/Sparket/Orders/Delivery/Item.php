<?php


namespace Sparket\Orders\Delivery;

use Novut\Db\Orm;

class Item
{
    use Orm;

    /** @var Delivery $delivery */
    private $delivery;

    /** @var int $WOrderID */
    private $WOrderID;
    /** @var int $WODItemID */
    private $WODItemID;
    /** @var int $WODeliveryID */
    private $WODeliveryID;
    /** @var int $WOItemID */
    private $WOItemID;
    /** @var int $WODItemQty */
    private $WODItemQty;
    /** @var int $Cancelled */
    private $Cancelled;
    /** string $CancelledInfo */
    private $CancelledInfo;

    function __construct(Delivery $delivery, self $item = null)
    {
        $this->setOptions((new Items())->defaultORMOptions());

        $this->delivery = $delivery;

        if(!$this->delivery->getWODeliveryID() || ($this->delivery->getWODeliveryID() && $this->delivery->getCancelled()))
        {
            \BN_Responses::dev("El env&iacute;o no existe.");
        }

        if ($item)
        {
            $this->load($item);

            if($this->delivery->getWODeliveryID() != $this->getWODeliveryID())
            {
                \BN_Responses::dev("El registro no pertenece al env&iacute;o.");
            }
        }
    }

    /**
     * @return int
     */
    public function getWOrderID(): int
    {
        return $this->WOrderID;
    }

    /**
     * @param int $WOrderID
     */
    public function setWOrderID(int $WOrderID)
    {
        $this->WOrderID = $WOrderID;
    }

    /**
     * @return int
     */
    public function getWODItemID(): int
    {
        return $this->WODItemID;
    }

    /**
     * @param int $WODItemID
     */
    public function setWODItemID(int $WODItemID)
    {
        $this->WODItemID = $WODItemID;
    }

    /**
     * @return int
     */
    public function getWODeliveryID(): int
    {
        return $this->WODeliveryID;
    }

    /**
     * @param int $WODeliveryID
     */
    public function setWODeliveryID(int $WODeliveryID)
    {
        $this->WODeliveryID = $WODeliveryID;
    }

    /**
     * @return int
     */
    public function getWOItemID(): int
    {
        return $this->WOItemID;
    }

    /**
     * @param int $WOItemID
     */
    public function setWOItemID(int $WOItemID)
    {
        $this->WOItemID = $WOItemID;
    }

    /**
     * @return int
     */
    public function getWODItemQty(): int
    {
        return $this->WODItemQty;
    }

    /**
     * @param int $WODItemQty
     */
    public function setWODItemQty(int $WODItemQty)
    {
        $this->WODItemQty = $WODItemQty;
    }

    /**
     * @return int
     */
    public function getCancelled(): int
    {
        return $this->Cancelled;
    }

    /**
     * @param int $Cancelled
     */
    public function setCancelled(int $Cancelled)
    {
        $this->Cancelled = $Cancelled;
    }

    /**
     * @return mixed
     */
    public function getCancelledInfo()
    {
        return $this->CancelledInfo;
    }

    /**
     * @param mixed $CancelledInfo
     */
    public function setCancelledInfo($CancelledInfo)
    {
        $this->CancelledInfo = $CancelledInfo;
    }

    public function add_after()
    {
        // Actualizar status del item cantidad de productos entregados



    }



}