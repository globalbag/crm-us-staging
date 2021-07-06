<?php

namespace Sparket\Orders\Cart;

use Novut\Core\Query;
use Novut\Db\Orm;
use Novut\Db\OrmOptions;
use Sparket\DB\Web;
use Sparket\Orders\ExchangeRate;
use Sparket\Orders\Order;
use Sparket\Orders\Status;
use Sparket\WUsers\Billing;
use Sparket\WUsers\WUser;

class Cart
{
    use Orm;

    /** @var string $DocDate */
    private $DocDate;
    /** @var string $DocInfo */
    private $DocInfo;
    /** @var int $WCartID */
    private $WCartID;
    /** @var int $WCartCode */
    private $WCartCode;
    /** @var int $WUserID */
    private $WUserID;
    /** @var int $GuestID */
    private $GuestID;
    /** @var string $WOrderContactFirstName */
    private $WOrderContactFirstName;
    /** @var string $WOrderContactLastName */
    private $WOrderContactLastName;
    /** @var string $WOrderContactFullName */
    private $WOrderContactFullName;
    /** @var string $WOrderContactEmail */
    private $WOrderContactEmail;
    /** @var string $WOrderContactPhone */
    private $WOrderContactPhone;
    /** @var string $WOrderContactRFC */
    private $WOrderContactRFC;
    /** @var string $WOrderContactLegalName */
    private $WOrderContactLegalName;
    /** @var string $WOrderAddressStreet */
    private $WOrderAddressStreet;
    /** @var string $WOrderAddressNumber */
    private $WOrderAddressNumber;
    /** @var string $WOrderAddressIntNumber */
    private $WOrderAddressIntNumber;
    /** @var string $WOrderAddressNeighborhood */
    private $WOrderAddressNeighborhood;
    /** @var string $WOrderAddressCity */
    private $WOrderAddressCity;
    /** @var int $WOrderAddressStateID */
    private $WOrderAddressStateID;
    /** @var string $WOrderAddressStateName */
    private $WOrderAddressStateName;
    /** @var int $WOrderAddressCountryID */
    private $WOrderAddressCountryID;
    /** @var string $WOrderAddressCountryName */
    private $WOrderAddressCountryName;
    /** @var string $WOrderAddressZipCode */
    private $WOrderAddressZipCode;
    /** @var int $WOrderShippingOfficeID */
    private $WOrderShippingOfficeID;
    /** @var int $WOrderShippingAddressID */
    private $WOrderShippingAddressID;
    /** @var string $WOrderShippingAddress */
    private $WOrderShippingAddress;
    /** @var string $WOrderShippingAddressNeighborhood */
    private $WOrderShippingAddressNeighborhood;
    /** @var string $WOrderShippingAddressCity */
    private $WOrderShippingAddressCity;
    /** @var int $WOrderShippingAddressStateID */
    private $WOrderShippingAddressStateID;
    /** @var string $WOrderShippingAddressStateName */
    private $WOrderShippingAddressStateName;
    /** @var int $WOrderShippingAddressCountryID */
    private $WOrderShippingAddressCountryID;
    /** @var string $WOrderShippingAddressCountryName */
    private $WOrderShippingAddressCountryName;
    /** @var string $WOrderShippingAddressZipCode */
    private $WOrderShippingAddressZipCode;
    /** @var string $WOrderShippingReference */
    private $WOrderShippingReference;
    /** @var string $WOrderShippingContactName */
    private $WOrderShippingContactName;
    /** @var string $WOrderShippingContactPhone */
    private $WOrderShippingContactPhone;
    /** @var double $WOrderShippingAmount */
    private $WOrderShippingAmount;
    /** @var string $WOrderShippingQuoteID */
    private $WOrderShippingQuoteID;
    /** @var string $WOrderShippingLabelID */
    private $WOrderShippingLabelID;
    /** @var string $WOrderShippingCarrierInfo */
    private $WOrderShippingCarrierInfo;
    /** @var int $WOrderTotalItems */
    private $WOrderTotalItems;
    /** @var string $WOrderOptionPayment */
    private $WOrderOptionPayment;
    /** @var string $WOrderCurrency */
    private $WOrderCurrency;
    /** @var double $WOrderTotalTax */
    private $WOrderTotalTax;
    /** @var double $WOrderTotalShipping */
    private $WOrderTotalShipping;
    /** @var double $WOrderTotalAmount */
    private $WOrderTotalAmount;
    /** @var double $WOrderTotalTotal */
    private $WOrderTotalTotal;
    /** @var int $OrderTotalTotal */
    private $WOrderInvoiceCategory;
    /** @var int $WOrderInvoice */
    private $WOrderInvoice;
    /** @var string $ERateDate */
    private $ERateDate;
    /** @var double $ERateValue */
    private $ERateValue;
    /** @var int $Cancelled */
    private $Cancelled;
    /** @var string $CancelledInfo */
    private $CancelledInfo;

    function __construct(Cart $cart = null)
    {
        $options = (new OrmOptions);
        $options->setDb(Web::getDB());
        $options->setTableName('web_orders_cart');
        $options->setPrimaryKey('WCartID');
        $options->setCreationDocDate('DocDate');
        $options->setCancelled(true);

        $this->setOptions($options);

        if ($cart)
        {
            $this->load($cart);
        }
    }

    /**
     * @return string
     */
    public function getDocDate()
    {
        return $this->DocDate;
    }

    /**
     * @param string $DocDate
     */
    public function setDocDate($DocDate)
    {
        $this->DocDate = $DocDate;
    }

    /**
     * @return string
     */
    public function getDocInfo()
    {
        return $this->DocInfo;
    }

    /**
     * @param string $DocInfo
     */
    public function setDocInfo($DocInfo)
    {
        $this->DocInfo = $DocInfo;
    }

    /**
     * @return int
     */
    public function getWCartID()
    {
        return $this->WCartID;
    }

    /**
     * @param int $WCartID
     */
    public function setWCartID($WCartID)
    {
        $this->WCartID = $WCartID;
    }

    /**
     * @return int
     */
    public function getWCartCode()
    {
        return $this->WCartCode;
    }

    /**
     * @param int $WCartCode
     */
    public function setWCartCode($WCartCode)
    {
        $this->WCartCode = $WCartCode;
    }

    /**
     * @return int
     */
    public function getWUserID()
    {
        return $this->WUserID;
    }

    /**
     * @param int $WUserID
     */
    public function setWUserID($WUserID)
    {
        $this->WUserID = $WUserID;
    }

    /**
     * @return int
     */
    public function getGuestID()
    {
        return $this->GuestID;
    }

    /**
     * @param int $GuestID
     */
    public function setGuestID($GuestID)
    {
        $this->GuestID = $GuestID;
    }

    /**
     * @return string
     */
    public function getWOrderContactFirstName()
    {
        return $this->WOrderContactFirstName;
    }

    /**
     * @param string $WOrderContactFirstName
     */
    public function setWOrderContactFirstName($WOrderContactFirstName)
    {
        $this->WOrderContactFirstName = $WOrderContactFirstName;
    }

    /**
     * @return string
     */
    public function getWOrderContactLastName()
    {
        return $this->WOrderContactLastName;
    }

    /**
     * @param string $WOrderContactLastName
     */
    public function setWOrderContactLastName($WOrderContactLastName)
    {
        $this->WOrderContactLastName = $WOrderContactLastName;
    }

    /**
     * @return string
     */
    public function getWOrderContactFullName()
    {
        return $this->WOrderContactFullName;
    }

    /**
     * @param string $WOrderContactFullName
     */
    public function setWOrderContactFullName($WOrderContactFullName)
    {
        $this->WOrderContactFullName = $WOrderContactFullName;
    }

    /**
     * @return string
     */
    public function getWOrderContactEmail()
    {
        return $this->WOrderContactEmail;
    }

    /**
     * @param string $WOrderContactEmail
     */
    public function setWOrderContactEmail($WOrderContactEmail)
    {
        $this->WOrderContactEmail = $WOrderContactEmail;
    }

    /**
     * @return string
     */
    public function getWOrderContactPhone()
    {
        return $this->WOrderContactPhone;
    }

    /**
     * @param string $WOrderContactPhone
     */
    public function setWOrderContactPhone($WOrderContactPhone)
    {
        $this->WOrderContactPhone = $WOrderContactPhone;
    }

    /**
     * @return string
     */
    public function getWOrderContactRFC()
    {
        return $this->WOrderContactRFC;
    }

    /**
     * @param string $WOrderContactRFC
     */
    public function setWOrderContactRFC($WOrderContactRFC)
    {
        $this->WOrderContactRFC = $WOrderContactRFC;
    }

    /**
     * @return string
     */
    public function getWOrderContactLegalName()
    {
        return $this->WOrderContactLegalName;
    }

    /**
     * @param string $WOrderContactLegalName
     */
    public function setWOrderContactLegalName($WOrderContactLegalName)
    {
        $this->WOrderContactLegalName = $WOrderContactLegalName;
    }

    /**
     * @return string
     */
    public function getWOrderAddressStreet()
    {
        return $this->WOrderAddressStreet;
    }

    /**
     * @param string $WOrderAddressStreet
     */
    public function setWOrderAddressStreet($WOrderAddressStreet)
    {
        $this->WOrderAddressStreet = $WOrderAddressStreet;
    }

    /**
     * @return string
     */
    public function getWOrderAddressNumber()
    {
        return $this->WOrderAddressNumber;
    }

    /**
     * @param string $WOrderAddressNumber
     */
    public function setWOrderAddressNumber($WOrderAddressNumber)
    {
        $this->WOrderAddressNumber = $WOrderAddressNumber;
    }

    /**
     * @return string
     */
    public function getWOrderAddressIntNumber()
    {
        return $this->WOrderAddressIntNumber;
    }

    /**
     * @param string $WOrderAddressIntNumber
     */
    public function setWOrderAddressIntNumber($WOrderAddressIntNumber)
    {
        $this->WOrderAddressIntNumber = $WOrderAddressIntNumber;
    }

    /**
     * @return string
     */
    public function getWOrderAddressNeighborhood()
    {
        return $this->WOrderAddressNeighborhood;
    }

    /**
     * @param string $WOrderAddressNeighborhood
     */
    public function setWOrderAddressNeighborhood($WOrderAddressNeighborhood)
    {
        $this->WOrderAddressNeighborhood = $WOrderAddressNeighborhood;
    }

    /**
     * @return string
     */
    public function getWOrderAddressCity()
    {
        return $this->WOrderAddressCity;
    }

    /**
     * @param string $WOrderAddressCity
     */
    public function setWOrderAddressCity($WOrderAddressCity)
    {
        $this->WOrderAddressCity = $WOrderAddressCity;
    }

    /**
     * @return int
     */
    public function getWOrderAddressStateID()
    {
        return $this->WOrderAddressStateID;
    }

    /**
     * @param int $WOrderAddressStateID
     */
    public function setWOrderAddressStateID($WOrderAddressStateID)
    {
        $this->WOrderAddressStateID = $WOrderAddressStateID;
    }

    /**
     * @return string
     */
    public function getWOrderAddressStateName()
    {
        return $this->WOrderAddressStateName;
    }

    /**
     * @param string $WOrderAddressStateName
     */
    public function setWOrderAddressStateName($WOrderAddressStateName)
    {
        $this->WOrderAddressStateName = $WOrderAddressStateName;
    }

    /**
     * @return int
     */
    public function getWOrderAddressCountryID()
    {
        return $this->WOrderAddressCountryID;
    }

    /**
     * @param int $WOrderAddressCountryID
     */
    public function setWOrderAddressCountryID($WOrderAddressCountryID)
    {
        $this->WOrderAddressCountryID = $WOrderAddressCountryID;
    }

    /**
     * @return string
     */
    public function getWOrderAddressCountryName()
    {
        return $this->WOrderAddressCountryName;
    }

    /**
     * @param string $WOrderAddressCountryName
     */
    public function setWOrderAddressCountryName($WOrderAddressCountryName)
    {
        $this->WOrderAddressCountryName = $WOrderAddressCountryName;
    }

    /**
     * @return string
     */
    public function getWOrderAddressZipCode()
    {
        return $this->WOrderAddressZipCode;
    }

    /**
     * @param string $WOrderAddressZipCode
     */
    public function setWOrderAddressZipCode($WOrderAddressZipCode)
    {
        $this->WOrderAddressZipCode = $WOrderAddressZipCode;
    }

    /**
     * @return int
     */
    public function getWOrderShippingAddressID(): ?int
    {
        return $this->WOrderShippingAddressID;
    }

    /**
     * @param int $WOrderShippingAddressID
     */
    public function setWOrderShippingAddressID(int $WOrderShippingAddressID)
    {
        $this->WOrderShippingAddressID = $WOrderShippingAddressID;
    }

    /**
     * @return int
     */
    public function getWOrderShippingOfficeID(): ?int
    {
        return $this->WOrderShippingOfficeID;
    }

    /**
     * @param int $WOrderShippingOfficeID
     */
    public function setWOrderShippingOfficeID(int $WOrderShippingOfficeID): void
    {
        $this->WOrderShippingOfficeID = $WOrderShippingOfficeID;
    }

    /**
     * @return string
     */
    public function getWOrderShippingAddress()
    {
        return $this->WOrderShippingAddress;
    }

    /**
     * @param string $WOrderShippingAddress
     */
    public function setWOrderShippingAddress($WOrderShippingAddress)
    {
        $this->WOrderShippingAddress = $WOrderShippingAddress;
    }

    /**
     * @return string
     */
    public function getWOrderShippingAddressNeighborhood()
    {
        return $this->WOrderShippingAddressNeighborhood;
    }

    /**
     * @param string $WOrderShippingAddressNeighborhood
     */
    public function setWOrderShippingAddressNeighborhood($WOrderShippingAddressNeighborhood)
    {
        $this->WOrderShippingAddressNeighborhood = $WOrderShippingAddressNeighborhood;
    }

    /**
     * @return string
     */
    public function getWOrderShippingAddressCity()
    {
        return $this->WOrderShippingAddressCity;
    }

    /**
     * @param string $WOrderShippingAddressCity
     */
    public function setWOrderShippingAddressCity($WOrderShippingAddressCity)
    {
        $this->WOrderShippingAddressCity = $WOrderShippingAddressCity;
    }

    /**
     * @return int
     */
    public function getWOrderShippingAddressStateID()
    {
        return $this->WOrderShippingAddressStateID;
    }

    /**
     * @param int $WOrderShippingAddressStateID
     */
    public function setWOrderShippingAddressStateID($WOrderShippingAddressStateID)
    {
        $this->WOrderShippingAddressStateID = $WOrderShippingAddressStateID;
    }

    /**
     * @return string
     */
    public function getWOrderShippingAddressStateName()
    {
        return $this->WOrderShippingAddressStateName;
    }

    /**
     * @param string $WOrderShippingAddressStateName
     */
    public function setWOrderShippingAddressStateName($WOrderShippingAddressStateName)
    {
        $this->WOrderShippingAddressStateName = $WOrderShippingAddressStateName;
    }

    /**
     * @return int
     */
    public function getWOrderShippingAddressCountryID()
    {
        return $this->WOrderShippingAddressCountryID;
    }

    /**
     * @param int $WOrderShippingAddressCountryID
     */
    public function setWOrderShippingAddressCountryID($WOrderShippingAddressCountryID)
    {
        $this->WOrderShippingAddressCountryID = $WOrderShippingAddressCountryID;
    }

    /**
     * @return string
     */
    public function getWOrderShippingAddressCountryName()
    {
        return $this->WOrderShippingAddressCountryName;
    }

    /**
     * @param string $WOrderShippingAddressCountryName
     */
    public function setWOrderShippingAddressCountryName($WOrderShippingAddressCountryName)
    {
        $this->WOrderShippingAddressCountryName = $WOrderShippingAddressCountryName;
    }

    /**
     * @return string
     */
    public function getWOrderShippingAddressZipCode()
    {
        return $this->WOrderShippingAddressZipCode;
    }

    /**
     * @param string $WOrderShippingAddressZipCode
     */
    public function setWOrderShippingAddressZipCode($WOrderShippingAddressZipCode)
    {
        $this->WOrderShippingAddressZipCode = $WOrderShippingAddressZipCode;
    }

    /**
     * @return string
     */
    public function getWOrderShippingReference()
    {
        return $this->WOrderShippingReference;
    }

    /**
     * @param string $WOrderShippingReference
     */
    public function setWOrderShippingReference($WOrderShippingReference)
    {
        $this->WOrderShippingReference = $WOrderShippingReference;
    }

    /**
     * @return string
     */
    public function getWOrderShippingContactName()
    {
        return $this->WOrderShippingContactName;
    }

    /**
     * @param string $WOrderShippingContactName
     */
    public function setWOrderShippingContactName($WOrderShippingContactName)
    {
        $this->WOrderShippingContactName = $WOrderShippingContactName;
    }

    /**
     * @return string
     */
    public function getWOrderShippingContactPhone()
    {
        return $this->WOrderShippingContactPhone;
    }

    /**
     * @param string $WOrderShippingContactPhone
     */
    public function setWOrderShippingContactPhone($WOrderShippingContactPhone)
    {
        $this->WOrderShippingContactPhone = $WOrderShippingContactPhone;
    }

    /**
     * @return float
     */
    public function getWOrderShippingAmount()
    {
        return $this->WOrderShippingAmount;
    }

    /**
     * @param float $WOrderShippingAmount
     */
    public function setWOrderShippingAmount($WOrderShippingAmount)
    {
        $this->WOrderShippingAmount = $WOrderShippingAmount;
    }

    /**
     * @return string
     */
    public function getWOrderShippingQuoteID()
    {
        return $this->WOrderShippingQuoteID;
    }

    /**
     * @param string $WOrderShippingQuoteID
     */
    public function setWOrderShippingQuoteID($WOrderShippingQuoteID)
    {
        $this->WOrderShippingQuoteID = $WOrderShippingQuoteID;
    }

    /**
     * @return string
     */
    public function getWOrderShippingLabelID()
    {
        return $this->WOrderShippingLabelID;
    }

    /**
     * @param string $WOrderShippingLabelID
     */
    public function setWOrderShippingLabelID($WOrderShippingLabelID)
    {
        $this->WOrderShippingLabelID = $WOrderShippingLabelID;
    }

    /**
     * @return string
     */
    public function getWOrderShippingCarrierInfo()
    {
        return $this->WOrderShippingCarrierInfo;
    }

    /**
     * @param string $WOrderShippingCarrierInfo
     */
    public function setWOrderShippingCarrierInfo($WOrderShippingCarrierInfo)
    {
        $this->WOrderShippingCarrierInfo = $WOrderShippingCarrierInfo;
    }

    /**
     * @return int
     */
    public function getWOrderTotalItems()
    {
        return $this->WOrderTotalItems;
    }

    /**
     * @param int $WOrderTotalItems
     */
    public function setWOrderTotalItems($WOrderTotalItems)
    {
        $this->WOrderTotalItems = $WOrderTotalItems;
    }

    /**
     * @return string
     */
    public function getWOrderOptionPayment()
    {
        return $this->WOrderOptionPayment;
    }

    /**
     * @param string $WOrderOptionPayment
     */
    public function setWOrderOptionPayment($WOrderOptionPayment)
    {
        $this->WOrderOptionPayment = $WOrderOptionPayment;
    }

    /**
     * @return string
     */
    public function getWOrderCurrency()
    {
        return $this->WOrderCurrency;
    }

    /**
     * @param string $WOrderCurrency
     */
    public function setWOrderCurrency($WOrderCurrency)
    {
        $this->WOrderCurrency = $WOrderCurrency;
    }

    /**
     * @return float
     */
    public function getWOrderTotalTax()
    {
        return $this->WOrderTotalTax;
    }

    /**
     * @param float $WOrderTotalTax
     */
    public function setWOrderTotalTax($WOrderTotalTax)
    {
        $this->WOrderTotalTax = $WOrderTotalTax;
    }

    /**
     * @return float
     */
    public function getWOrderTotalShipping()
    {
        return $this->WOrderTotalShipping;
    }

    /**
     * @param float $WOrderTotalShipping
     */
    public function setWOrderTotalShipping($WOrderTotalShipping)
    {
        $this->WOrderTotalShipping = $WOrderTotalShipping;
    }

    /**
     * @return float
     */
    public function getWOrderTotalAmount()
    {
        return $this->WOrderTotalAmount;
    }

    /**
     * @param float $WOrderTotalAmount
     */
    public function setWOrderTotalAmount($WOrderTotalAmount)
    {
        $this->WOrderTotalAmount = $WOrderTotalAmount;
    }

    /**
     * @return float
     */
    public function getWOrderTotalTotal()
    {
        return $this->WOrderTotalTotal;
    }

    /**
     * @param float $WOrderTotalTotal
     */
    public function setWOrderTotalTotal($WOrderTotalTotal)
    {
        $this->WOrderTotalTotal = $WOrderTotalTotal;
    }

    /**
     * @return int
     */
    public function getWOrderInvoiceCategory()
    {
        return $this->WOrderInvoiceCategory;
    }

    /**
     * @param int $WOrderInvoiceCategory
     */
    public function setWOrderInvoiceCategory($WOrderInvoiceCategory)
    {
        $this->WOrderInvoiceCategory = $WOrderInvoiceCategory;
    }

    /**
     * @return int
     */
    public function getWOrderInvoice()
    {
        return $this->WOrderInvoice;
    }

    /**
     * @param int $WOrderInvoice
     */
    public function setWOrderInvoice($WOrderInvoice)
    {
        $this->WOrderInvoice = $WOrderInvoice;
    }

    /**
     * @return string
     */
    public function getERateDate(): string
    {
        return $this->ERateDate;
    }

    /**
     * @param string $ERateDate
     */
    public function setERateDate(string $ERateDate)
    {
        $this->ERateDate = $ERateDate;
    }

    /**
     * @return float
     */
    public function getERateValue(): float
    {
        return $this->ERateValue;
    }

    /**
     * @param float $ERateValue
     */
    public function setERateValue(float $ERateValue)
    {
        $this->ERateValue = $ERateValue;
    }

    /**
     * @return int
     */
    public function getCancelled()
    {
        return $this->Cancelled;
    }

    /**
     * @param int $Cancelled
     */
    public function setCancelled($Cancelled)
    {
        $this->Cancelled = $Cancelled;
    }

    /**
     * @return string
     */
    public function getCancelledInfo()
    {
        return $this->CancelledInfo;
    }

    /**
     * @param string $CancelledInfo
     */
    public function setCancelledInfo($CancelledInfo)
    {
        $this->CancelledInfo = $CancelledInfo;
    }

    function add_before()
    {
        $this->getDocDate() ? : $this->setDocDate(date('Y-m-d H:i:s'));

        if(\BN_Var::$WUserInfo)
        {
            $this->setWOrderContactFirstName(\BN_Var::$WUserInfo['WUserFirstName']);

            $this->setWOrderContactLastName(\BN_Var::$WUserInfo['WUserLastName']);

            $this->setWOrderContactFullName(\BN_Var::$WUserInfo['WUserFullName']);

            $this->setWOrderContactPhone(\BN_Var::$WUserInfo['WUserPhone']);

            $this->setWOrderContactEmail(\BN_Var::$WUserInfo['WUserEmail']);

            $this->setWUserID(\BN_Var::$WUserInfo['WUserID']);
        }

        $this->setERateDate($this->getDocDate());
        $this->setERateValue(ExchangeRate::getRatePublic($this->getDocDate()));

        $this->setWOrderCurrency("MXN");
    }

    function save_before()
    {
        if(\BN_Var::$WUserInfo)
        {
            $this->setWUserID(\BN_Var::$WUserInfo['WUserID']);
        }
    }

    function save_after()
    {
        $this->update();
    }

    function update()
    {
        $WOrderTotalItems = 0;
        $WOrderTotalTax = 0;
        $WOrderTotalAmount = 0;
        $WOrderTotalTotal = 0;

        $item_list = (new Items($this))->list();

        foreach ($item_list as $item_info)
        {
            $WOrderTotalItems += $item_info['WOItemQty'];
            $WOrderTotalTax += $item_info['WOItemTaxMXN'];
            $WOrderTotalAmount += $item_info['WOItemAmountMXN'];
            $WOrderTotalTotal += $item_info['WOItemTotalMXN'];
        }

        $WOrderTotalTotal = $WOrderTotalTotal + $this->getWOrderTotalShipping();

        $sql_update['WOrderTotalItems'] = $WOrderTotalItems;
        $sql_update['WOrderTotalTax'] = $WOrderTotalTax;
        $sql_update['WOrderTotalAmount'] = $WOrderTotalAmount;
        $sql_update['WOrderTotalTotal'] = $WOrderTotalTotal;

        $this->db->Update($this->_table_name, $sql_update, 'WCartID', $this->getWCartID());
    }

    function addOrder(int $CartID = 0)
    {
        if(!$this->getWCartID())
        {
            $this->find($CartID);
        }

        if(!$this->getWCartID())
        {
            \BN_Responses::dev("El id del carrito no ha sido definido. No es posible continuar.");
        }

        $order = new Order();
        $order->setCallerID($this->getWCartID());
        $order->setCallerModule('cart');
        $order->setWUserID($this->getWUserID());
        $order->setWOrderStatus(Status::pending['value']);
        $order->setWOrderContactFirstName($this->getWOrderContactFirstName());
        $order->setWOrderContactLastName($this->getWOrderContactLastName());
        $order->setWOrderContactFullName($this->getWOrderContactFullName());
        $order->setWOrderContactEmail($this->getWOrderContactEmail());
        $order->setWOrderContactPhone($this->getWOrderContactPhone());
        $order->setWOrderContactRFC($this->getWOrderContactRFC());
        $order->setWOrderContactLegalName($this->getWOrderContactLegalName());
        $order->setWOrderAddressStreet($this->getWOrderAddressStreet());
        $order->setWOrderAddressNumber($this->getWOrderAddressNumber());
        $order->setWOrderAddressIntNumber($this->getWOrderAddressIntNumber());
        $order->setWOrderAddressNeighborhood($this->getWOrderAddressNeighborhood());
        $order->setWOrderAddressCity($this->getWOrderAddressCity());
        $order->setWOrderAddressStateID($this->getWOrderAddressStateID());
        $order->setWOrderAddressStateName($this->getWOrderAddressStateName());
        $order->setWOrderAddressCountryID($this->getWOrderAddressCountryID());
        $order->setWOrderAddressCountryName($this->getWOrderAddressCountryName());
        $order->setWOrderAddressZipCode($this->getWOrderAddressZipCode());

        $order->setWOrderShippingOfficeID($this->getWOrderShippingOfficeID());
        $order->setWOrderShippingAddressID($this->getWOrderShippingAddressID());
        $order->setWOrderShippingAddress($this->getWOrderShippingAddress());

        $order->setWOrderShippingAddressNeighborhood($this->getWOrderShippingAddressNeighborhood());
        $order->setWOrderShippingAddressCity($this->getWOrderShippingAddressCity());
        $order->setWOrderShippingAddressStateID($this->getWOrderShippingAddressStateID());
        $order->setWOrderShippingAddressStateName($this->getWOrderShippingAddressStateName());
        $order->setWOrderShippingAddressCountryID($this->getWOrderShippingAddressCountryID());
        $order->setWOrderShippingAddressCountryName($this->getWOrderShippingAddressCountryName());
        $order->setWOrderShippingAddressZipCode($this->getWOrderShippingAddressZipCode());
        $order->setWOrderShippingReference($this->getWOrderShippingReference());
        $order->setWOrderShippingContactName($this->getWOrderShippingContactName());
        $order->setWOrderShippingContactPhone($this->getWOrderShippingContactPhone());
        $order->setWOrderShippingAmount($this->getWOrderShippingAmount());
        $order->setWOrderShippingQuoteID($this->getWOrderShippingQuoteID());
        $order->setWOrderShippingCarrierInfo($this->getWOrderShippingCarrierInfo());
        $order->setWOrderShippingLabelID($this->getWOrderShippingLabelID());

        $order->setWOrderTotalItems($this->getWOrderTotalItems());
        $order->setWOrderOptionPayment($this->getWOrderOptionPayment());
        $order->setWOrderCurrency($this->getWOrderCurrency());
        $order->setWOrderTotalTax($this->getWOrderTotalTax());
        $order->setWOrderTotalShipping($this->getWOrderTotalShipping());
        $order->setWOrderTotalAmount($this->getWOrderTotalAmount());
        $order->setWOrderTotalTotal($this->getWOrderTotalTotal());
        $order->setWOrderInvoiceCategory($this->getWOrderInvoiceCategory());
        $order->setWOrderInvoice($this->getWOrderInvoice());

        $order->setERateDate($this->getERateDate());
        $order->setERateValue($this->getERateValue());

        $order->add();

        if(!$order->getWOrderID())
        {
            \BN_Responses::dev("No fue posible agregar la orden de compra intente nuevamente.");
        }

        $item_total = 0;
        foreach ((new Items($this))->list() as $data)
        {
            $item_cart = (new Item($this));
            $item_cart->import($data);

            $item = new \Sparket\Orders\Item($order);
            $item->setWCartItemID($item_cart->getWOItemID());
            $item->setProductID($item_cart->getProductID());
            $item->setProductCode($item_cart->getProductCode());
            $item->setProductPartNumber($item_cart->getProductPartNumber());
            $item->setPriceID($item_cart->getPriceID());
            $item->setPriceValue($item_cart->getPriceValue());
            $item->setWOItemName($item_cart->getWOItemName());
            $item->setWOItemQty($item_cart->getWOItemQty());
            $item->setWOItemPriceTaxMXN($item_cart->getWOItemPriceTaxMXN());
            $item->setWOItemPriceTaxUSD($item_cart->getWOItemPriceTaxUSD());
            $item->setWOItemPriceMXN($item_cart->getWOItemPriceMXN());
            $item->setWOItemPriceUSD($item_cart->getWOItemPriceUSD());
            $item->setWOItemAmountMXN($item_cart->getWOItemAmountMXN());
            $item->setWOItemAmountUSD($item_cart->getWOItemAmountUSD());
            $item->setWOItemTaxMXN($item_cart->getWOItemTaxMXN());
            $item->setWOItemTaxUSD($item_cart->getWOItemTaxUSD());
            $item->setWOItemTotalMXN($item_cart->getWOItemTotalMXN());
            $item->setWOItemTotalUSD($item_cart->getWOItemTotalUSD());

            $item->setWOItemAmount($item_cart->getWOItemAmountMXN());
            $item->setWOItemTax($item_cart->getWOItemTaxMXN());
            $item->setWOItemTotal($item_cart->getWOItemTotalMXN());
            $item->setWOItemCurrency($item_cart->getWOItemCurrency());

            $item->add();

            if($item->getWOItemID())
            {
                $item_total++;
            }
        }

        if(!$item_total)
        {
            \BN_Responses::dev("No existen productos en el carrito. No es posible continuar.");
        }

        $wuser = new WUser();
        $wuser->find($this->getWUserID());

        if($wuser->getWUserID())
        {
            $billing = new Billing($wuser);
            $billing->setContactRFC($this->getWOrderContactRFC());
            $billing->setContactLegalName($this->getWOrderContactLegalName());
            $billing->setContactInvoiceCategory($this->getWOrderInvoiceCategory());
            $billing->setAddressStreet($this->getWOrderAddressStreet());
            $billing->setAddressNumber($this->getWOrderAddressNumber());
            $billing->setAddressIntNumber($this->getWOrderAddressIntNumber());
            $billing->setAddressNeighborhood($this->getWOrderAddressNeighborhood());
            $billing->setAddressCity($this->getWOrderAddressCity());
            $billing->setAddressStateID($this->getWOrderAddressStateID());
            $billing->setAddressStateName($this->getWOrderAddressStateName());
            $billing->setAddressCountryID($this->getWOrderAddressCountryID());
            $billing->setAddressCountryName($this->getWOrderAddressCountryName());
            $billing->setAddressZipCode($this->getWOrderAddressZipCode());
            $billing->save();
        }

        return $order->getWOrderID();
    }

}