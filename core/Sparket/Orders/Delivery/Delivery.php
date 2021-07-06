<?php


namespace Sparket\Orders\Delivery;


use Novut\Db\Orm;
use Novut\Db\OrmOptions;
use Sparket\Orders\Order;
use Sparket\Orders\Payments\Status;

class Delivery
{
    use Orm;

    /** @var Order $order */
    private $order;

    /** @var string $DocDate */
    private $DocDate;
    /** @var int $DocUserID */
    private $DocUserID;
    /** @var int $WOrderID */
    private $WOrderID;
    /** @var int $WODeliveryID */
    private $WODeliveryID;
    /** @var string $WODeliveryDate */
    private $WODeliveryDate;
    /** @var string $WODeliveryCompanyID */
    private $WODeliveryCompanyID;
    /** @var string $WODeliveryCompany */
    private $WODeliveryCompany;
    /** @var string $WODeliveryTCode */
    private $WODeliveryTCode;
    /** @var int $WODeliveryStatus */
    private $WODeliveryStatus;
    /** @var string $WODeliveryCancelInfo */
    private $WODeliveryCancelInfo;
    /** @var string $WOItemList */
    private $WOItemList;
    /** @var int $Cancelled */
    private $Cancelled;
    /** @var string $CancelledInfo */
    private $CancelledInfo;



    function __construct(Order $order, self $delivery = null)
    {
        $this->setOptions((new Deliveries)->defaultORMOptions());

        $this->order = $order;

        if(!$this->order->getWOrderID() || ($this->order->getWOrderID() && $this->order->getCancelled()))
        {
            \BN_Responses::dev("La orden no existe.");
        }

        if ($delivery)
        {
            $this->load($delivery);

            if($this->order->getWOrderID() != $this->getWOrderID())
            {
                \BN_Responses::dev("El registro no pertenece a la orden.");
            }
        }
    }

    /**
     * @return string
     */
    public function getDocDate(): string
    {
        return $this->DocDate;
    }

    /**
     * @param string $DocDate
     */
    public function setDocDate(string $DocDate)
    {
        $this->DocDate = $DocDate;
    }

    /**
     * @return int
     */
    public function getDocUserID(): int
    {
        return $this->DocUserID;
    }

    /**
     * @param int $DocUserID
     */
    public function setDocUserID(int $DocUserID)
    {
        $this->DocUserID = $DocUserID;
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
     * @return string
     */
    public function getWODeliveryDate(): ?string
    {
        return $this->WODeliveryDate;
    }

    /**
     * @param string $WODeliveryDate
     */
    public function setWODeliveryDate(string $WODeliveryDate)
    {
        $this->WODeliveryDate = $WODeliveryDate;
    }

    /**
     * @return string
     */
    public function getWODeliveryCompanyID(): string
    {
        return $this->WODeliveryCompanyID;
    }

    /**
     * @param string $WODeliveryCompanyID
     */
    public function setWODeliveryCompanyID(string $WODeliveryCompanyID)
    {
        $this->WODeliveryCompanyID = $WODeliveryCompanyID;
    }

    /**
     * @return string
     */
    public function getWODeliveryCompany(): string
    {
        return $this->WODeliveryCompany;
    }

    /**
     * @param string $WODeliveryCompany
     */
    public function setWODeliveryCompany(string $WODeliveryCompany)
    {
        $this->WODeliveryCompany = $WODeliveryCompany;
    }

    /**
     * @return string
     */
    public function getWODeliveryTCode(): string
    {
        return $this->WODeliveryTCode;
    }

    /**
     * @param string $WODeliveryTCode
     */
    public function setWODeliveryTCode(string $WODeliveryTCode)
    {
        $this->WODeliveryTCode = $WODeliveryTCode;
    }

    /**
     * @return int
     */
    public function getWODeliveryStatus(): int
    {
        return $this->WODeliveryStatus;
    }

    /**
     * @param int $WODeliveryStatus
     */
    public function setWODeliveryStatus(int $WODeliveryStatus)
    {
        $this->WODeliveryStatus = $WODeliveryStatus;
    }

    /**
     * @return string
     */
    public function getWODeliveryCancelInfo(): string
    {
        return $this->WODeliveryCancelInfo;
    }

    /**
     * @param string $WODeliveryCancelInfo
     */
    public function setWODeliveryCancelInfo(string $WODeliveryCancelInfo): void
    {
        $this->WODeliveryCancelInfo = $WODeliveryCancelInfo;
    }

    /**
     * @return int
     */
    public function getCancelled(): ?int
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
     * @return string
     */
    public function getCancelledInfo(): string
    {
        return $this->CancelledInfo;
    }

    /**
     * @param string $CancelledInfo
     */
    public function setCancelledInfo(string $CancelledInfo)
    {
        $this->CancelledInfo = $CancelledInfo;
    }

    /**
     * @return string|null|array
     */
    function getWOItemList()
    {
        return $this->WOItemList;
    }

    /**
     * @param string $WOItemList
     */
    private function setWOItemList(string $WOItemList = "")
    {
        $this->WOItemList = $WOItemList;
    }

    function add_before()
    {
        $this->setWOrderID($this->order->getWOrderID());

        if(!$this->getWOItemList())
        {
            \BN_Responses::dev("No hay sido definidos items.");
        }

        $this->setWOItemList(\BN_Coders::json_encode($this->getWOItemList()));

        if(!$this->getWODeliveryDate())
        {
            $this->setWODeliveryDate(date('Y-m-d H:i:s'));
        }

        $this->setWODeliveryStatus(Status::pending['value']);
    }

    function add_after()
    {
        if(!$this->getWODeliveryID())
        {
            \BN_Responses::dev("No fue posible agregar el registro.");
        }

        $delivery = $this->find($this->getWODeliveryID());

        foreach (\BN_Coders::json_decode($this->getWOItemList()) as $WOItemID => $WOItemQty)
        {
            $item = new \Sparket\Orders\Item($this->order);
            $item->find($WOItemID);

            $item_delivery = (new Item($delivery));
            $item_delivery->setWOrderID($this->order->getWOrderID());
            $item_delivery->setWODeliveryID($this->getWODeliveryID());
            $item_delivery->setWOItemID($item->getWOItemID());
            $item_delivery->setWODItemQty($WOItemQty);
            $item_delivery->add();

            Items::updateItem($item);
        }

        Deliveries::updateOrder($this->order);

        $this->order->update();
    }

    function save_after()
    {
        Deliveries::updateOrder($this->order);

        $this->order->update();
    }

    function update_status_success(int $WODeliveryID = 0)
    {
        if(!$this->getWODeliveryID())
        {
            $this->find($WODeliveryID);
        }

        if(!$this->getWODeliveryID())
        {
            \BN_Responses::dev("No se ha definido el id del env&iacute;o.");
        }

        $sql_update['WODeliveryStatus'] = Status::success['value'];

        $this->db->Update($this->_table_name, $sql_update, 'WODeliveryID', $this->getWODeliveryID());

        Deliveries::updateOrder($this->order);

        $this->order->update();
    }

    function update_status_reject(int $WODeliveryID = 0, string $comment = "")
    {
        if(!$this->getWODeliveryID())
        {
            $this->find($WODeliveryID);
        }

        if(!$this->getWODeliveryID())
        {
            \BN_Responses::dev("No se ha definido el id del env&iacute;o.");
        }

        $sql_update['WODeliveryStatus'] = \Sparket\Orders\Delivery\Status::reject['value'];

        $WODeliveryCancelInfo['Date'] = date('Y-m-d H:i:s');
        $WODeliveryCancelInfo['IP'] = get_ip();
        $WODeliveryCancelInfo['UserID'] = \BN_Var::$UserInfo['UserID'];
        $WODeliveryCancelInfo['WUserID'] = \BN_Var::$WUserInfo['WUserID'];
        $WODeliveryCancelInfo['Comment'] = $comment;

        $WODeliveryCancelInfo = \BN_Coders::json_encode($WODeliveryCancelInfo);

        $sql_update['WODeliveryCancelInfo'] = $WODeliveryCancelInfo;

        $this->db->Update($this->_table_name, $sql_update, 'WODeliveryID', $this->getWODeliveryID());

        foreach (\BN_Coders::json_decode($this->getWOItemList()) as $WOItemID => $WOItemID)
        {
            $item = (new \Sparket\Orders\Item($this->order))->find($WOItemID);

            $WOItemDelivery = \BN_Coders::json_decode($item->getWOItemDelivery());

            unset($WOItemDelivery[$this->getWODeliveryID()]);

            $WOItemDelivery = $WOItemDelivery ? \BN_Coders::json_encode($WOItemDelivery) : "";

            $item->setWOItemDelivery($WOItemDelivery);

            $item->save($item->getWOItemID());

            Items::updateItem($item);
        }

        Deliveries::updateOrder($this->order);

        $this->order->update();

    }

    function setWOItem(\Sparket\Orders\Item $item, int $item_qty = 0)
    {
        if(!$item_qty)
        {
            \BN_Responses::dev("La cantidad tiene que ser mayor a 0.");
        }

        if(!$item->getWOItemID() || ($item->getWOItemID() && $item->getCancelled()))
        {
            \BN_Responses::dev("El item no existe.");
        }

        if(($item->getWOItemDeliveryItems() + $item_qty) >  $item->getWOItemQty())
        {
            \BN_Responses::dev("La cantidad excede el n&uacute;mero de items disponibles.");
        }

        $this->WOItemList[$item->getWOItemID()] = $item_qty;
    }
}