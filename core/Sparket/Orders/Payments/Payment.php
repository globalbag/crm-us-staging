<?php


namespace Sparket\Orders\Payments;

use Novut\Core\Query;
use Novut\Db\Orm;
use Novut\Db\OrmOptions;
use Sparket\Orders\Order;
use Sparket\Tools\Files\Files;

class Payment
{
    use Orm;

    /** @var Order $order */
    protected $order;

    /** @var array $_file */
    protected $_file;

    /** @var string $DocDate */
    protected $DocDate;
    /** @var int $DocUserID */
    protected $DocUserID;
    /** @var int $WOPaymentID */
    protected $WOPaymentID;
    /** @var int $WOrderID */
    protected $WOrderID;
    /** @var string $WOPaymentDate */
    protected $WOPaymentDate;
    /** @var double $WOPaymentAmount */
    protected $WOPaymentAmount;
    /** @var string $WOPaymentMethod */
    protected $WOPaymentMethod;
    /** @var string $WOPaymentCurrency */
    protected $WOPaymentCurrency;
    /** @var string $WOPaymentPayuMerchantID */
    protected $WOPaymentPayuMerchantID;
    /** @var string $WOPaymentPayuStatePol */
    protected $WOPaymentPayuStatePol;
    /** @var string $WOPaymentPayPalPayerID */
    protected $WOPaymentPayPalPayerID;
    /** @var string $WOPaymentPayPalID */
    protected $WOPaymentPayPalID;
    /** @var string $WOPaymentPayPalStatus */
    protected $WOPaymentPayPalStatus;
    /** @var int $WOPaymentStatus */
    protected $WOPaymentStatus;
    /** @var string $WOPaymentSuccessInfo */
    protected $WOPaymentSuccessInfo;
    /** @var string $WOPaymentCancelInfo */
    protected $WOPaymentCancelInfo;
    /** @var string $WOPaymentFile */
    protected $WOPaymentFile;

    /** @var string $WOPaymentStripeID */
    protected $WOPaymentStripeID;

    /** @var int $Cancelled */
    protected $Cancelled;
    /** @var string $CancelledInfo */
    protected $CancelledInfo;

    function __construct(Order $order, self $payment = null)
    {

        $this->setOptions(Payments::getORMOptions());

        $this->order = $order;

        if(!$this->order->getWOrderID() || ($this->order->getWOrderID() && $this->order->getCancelled()))
        {
            \BN_Responses::dev("La orden no existe.");
        }

        if ($payment)
        {
            $this->load($payment);

            if($this->order->getWOrderID() != $this->getWOrderID())
            {
                \BN_Responses::dev("El pago no pertenece a la orden.");
            }
        }
    }

    /**
     * @param array $files
     */
    public function setFile(array $files)
    {
        $this->_file = $files;
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
    public function setDocDate(string $DocDate): void
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
    public function setDocUserID(int $DocUserID): void
    {
        $this->DocUserID = $DocUserID;
    }

    /**
     * @return int
     */
    public function getWOPaymentID(): ?int
    {
        return $this->WOPaymentID;
    }

    /**
     * @param int $WOPaymentID
     */
    public function setWOPaymentID(int $WOPaymentID)
    {
        $this->WOPaymentID = $WOPaymentID;
    }

    /**
     * @return int
     */
    public function getWOrderID(): ?int
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
     * @return string
     */
    public function getWOPaymentDate(): ?string
    {
        return $this->WOPaymentDate;
    }

    /**
     * @param string $WOPaymentDate
     */
    public function setWOPaymentDate(string $WOPaymentDate)
    {
        $this->WOPaymentDate = $WOPaymentDate;
    }

    /**
     * @return float
     */
    public function getWOPaymentAmount(): ?float
    {
        return $this->WOPaymentAmount;
    }

    /**
     * @param float $WOPaymentAmount
     */
    public function setWOPaymentAmount(float $WOPaymentAmount)
    {
        $this->WOPaymentAmount = $WOPaymentAmount;
    }

    /**
     * @return string
     */
    public function getWOPaymentMethod(): ?string
    {
        return $this->WOPaymentMethod;
    }

    /**
     * @param string $WOPaymentMethod
     */
    public function setWOPaymentMethod(string $WOPaymentMethod)
    {
        $this->WOPaymentMethod = $WOPaymentMethod;
    }

    /**
     * @return string
     */
    public function getWOPaymentCurrency(): ?string
    {
        return $this->WOPaymentCurrency;
    }

    /**
     * @param string $WOPaymentCurrency
     */
    public function setWOPaymentCurrency(string $WOPaymentCurrency)
    {
        $this->WOPaymentCurrency = $WOPaymentCurrency;
    }

    /**
     * @return string
     */
    public function getWOPaymentPayuMerchantID(): ?string
    {
        return $this->WOPaymentPayuMerchantID;
    }

    /**
     * @param string $WOPaymentPayuMerchantID
     */
    public function setWOPaymentPayuMerchantID(string $WOPaymentPayuMerchantID)
    {
        $this->WOPaymentPayuMerchantID = $WOPaymentPayuMerchantID;
    }

    /**
     * @return string
     */
    public function getWOPaymentPayuStatePol(): ?string
    {
        return $this->WOPaymentPayuStatePol;
    }

    /**
     * @param string $WOPaymentPayuStatePol
     */
    public function setWOPaymentPayuStatePol(string $WOPaymentPayuStatePol)
    {
        $this->WOPaymentPayuStatePol = $WOPaymentPayuStatePol;
    }

    /**
     * @return string
     */
    public function getWOPaymentPayPalPayerID(): ?string
    {
        return $this->WOPaymentPayPalPayerID;
    }

    /**
     * @param string $WOPaymentPayPalPayerID
     */
    public function setWOPaymentPayPalPayerID(string $WOPaymentPayPalPayerID)
    {
        $this->WOPaymentPayPalPayerID = $WOPaymentPayPalPayerID;
    }

    /**
     * @return string
     */
    public function getWOPaymentPayPalStatus(): ?string
    {
        return $this->WOPaymentPayPalStatus;
    }

    /**
     * @param string $WOPaymentPayPalStatus
     */
    public function setWOPaymentPayPalStatus(string $WOPaymentPayPalStatus)
    {
        $this->WOPaymentPayPalStatus = $WOPaymentPayPalStatus;
    }

    /**
     * @return int
     */
    public function getWOPaymentStatus(): ?int
    {
        return $this->WOPaymentStatus;
    }

    /**
     * @param int $WOPaymentStatus
     */
    public function setWOPaymentStatus(int $WOPaymentStatus)
    {
        $this->WOPaymentStatus = $WOPaymentStatus;
    }

    /**
     * @return string
     */
    public function getWOPaymentPayPalID(): ?string
    {
        return $this->WOPaymentPayPalID;
    }

    /**
     * @param string $WOPaymentPayPalID
     */
    public function setWOPaymentPayPalID(string $WOPaymentPayPalID)
    {
        $this->WOPaymentPayPalID = $WOPaymentPayPalID;
    }

    /**
     * @return string
     */
    public function getWOPaymentSuccessInfo(): string
    {
        return $this->WOPaymentSuccessInfo;
    }

    /**
     * @param string $WOPaymentSuccessInfo
     */
    public function setWOPaymentSuccessInfo(string $WOPaymentSuccessInfo)
    {
        $this->WOPaymentSuccessInfo = $WOPaymentSuccessInfo;
    }

    /**
     * @return mixed
     */
    public function getWOPaymentCancelInfo()
    {
        return $this->WOPaymentCancelInfo;
    }

    /**
     * @param mixed $WOPaymentCancelInfo
     */
    public function setWOPaymentCancelInfo($WOPaymentCancelInfo)
    {
        $this->WOPaymentCancelInfo = $WOPaymentCancelInfo;
    }

    /**
     * @return string
     */
    public function getWOPaymentFile(): string
    {
        return $this->WOPaymentFile;
    }

    /**
     * @param string $WOPaymentFile
     */
    public function setWOPaymentFile(string $WOPaymentFile): void
    {
        $this->WOPaymentFile = $WOPaymentFile;
    }

    /**
     * @return string
     */
    public function getWOPaymentStripeID(): string
    {
        return $this->WOPaymentStripeID;
    }

    /**
     * @param string $WOPaymentStripeID
     */
    public function setWOPaymentStripeID(string $WOPaymentStripeID): void
    {
        $this->WOPaymentStripeID = $WOPaymentStripeID;
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

    function add_before()
    {
        $this->setWOrderID($this->order->getWOrderID());

        $this->getWOPaymentStatus() ? : $this->setWOPaymentStatus(Status::pending['value']);

        if(!$this->getWOPaymentMethod())
        {
            \BN_Responses::dev("El m&eacute;todo de pago no ha sido definido.");
        }

        $this->setWOPaymentCurrency($this->order->getWOrderCurrency());
    }

    function add_after()
    {
        if(!$this->getWOPaymentID())
        {
            return false;
        }

        Payments::updateOrder($this->order);

        $this->order->update();
    }

    function save_after()
    {
        Payments::updateOrder($this->order);

        $this->order->update();
    }

    function cancel_after()
    {
        $query = new \Novut\Core\Query;
        $query->addParam('CallerID', $this->getWOPaymentID());
        $query->addQuery(" AND CallerID = :CallerID ");
        $query->addParam('CallerModule', 'worders.payments');
        $query->addQuery(" AND CallerModule = :CallerModule ");
        $query->addQuery(" AND Cancelled = 0 ");

        Payments::updateOrder($this->order);

        $this->order->update();
    }

    function update_status_success(int $WOPaymentID = 0)
    {
        if(!$this->getWOPaymentID())
        {
            $this->find($WOPaymentID);
        }

        if(!$this->getWOPaymentID())
        {
            \BN_Responses::dev("No se ha definido el id del pago.");
        }

        $sql_update['WOPaymentStatus'] = Status::success['value'];

        $WOPaymentSuccessInfo['Date'] = date('Y-m-d H:i:s');
        $WOPaymentSuccessInfo['IP'] = get_ip();
        $WOPaymentSuccessInfo['UserID'] = \BN_Var::$UserInfo['UserID'];
        $WOPaymentSuccessInfo['WUserID'] = \BN_Var::$WUserInfo['WUserID'];

        $WOPaymentSuccessInfo = \BN_Coders::json_encode($WOPaymentSuccessInfo);

        $sql_update['WOPaymentSuccessInfo'] = $WOPaymentSuccessInfo;

        $this->db->Update($this->_table_name, $sql_update, $this->getPrimaryKey(), $this->getWOPaymentID());

        Payments::updateOrder($this->order);

        $this->order->update();
    }

    function update_status_reject(int $WOPaymentID = 0, string $comment = "")
    {
        if(!$this->getWOPaymentID())
        {
            $this->find($WOPaymentID);
        }

        if(!$this->getWOPaymentID())
        {
            \BN_Responses::dev("No se ha definido el id del pago.");
        }

        $this->setWOPaymentStatus(Status::reject['value']);

        $WOPaymentCancelInfo['Date'] = date('Y-m-d H:i:s');
        $WOPaymentCancelInfo['IP'] = get_ip();
        $WOPaymentCancelInfo['UserID'] = \BN_Var::$UserInfo['UserID'];
        $WOPaymentCancelInfo['WUserID'] = \BN_Var::$WUserInfo['WUserID'];
        $WOPaymentCancelInfo['Comment'] = $comment;

        $WOPaymentCancelInfo = \BN_Coders::json_encode($WOPaymentCancelInfo);

        $this->setWOPaymentCancelInfo($WOPaymentCancelInfo);

        $this->save($this->getWOPaymentID());
    }

}