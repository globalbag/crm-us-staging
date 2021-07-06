<?php

namespace Sparket\Orders;

use Intelligy\Leads\Lead;
use Novut\Core\Query;
use Novut\Db\Orm;
use Novut\Db\OrmOptions;
use Sparket\DB\Web;

class Order
{
    use Orm;

    /** @var string $DocDate */
    private $DocDate;
    /** @var string $DocInfo */
    private $DocInfo;
    /** @var int $WOrderID */
    private $WOrderID;
    /** @var int $CallerID */
    private $CallerID;
    /** @var string $CallerModule */
    private $CallerModule;
    /** @var int $WUserID */
    private $WUserID;
    /** @var string $WOrderDate */
    private $WOrderDate;
    /** @var int $WOrderStatus */
    private $WOrderStatus;
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
    /** @var string $WOrderShippingAddressID */
    private $WOrderShippingAddressID;
    /** @var string $WOrderShippingAddress */
    private $WOrderShippingAddress;
    /** @var string $WOrderShippingAddress2 */
    private $WOrderShippingAddress2;
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
    /** @var string $WOrderShippingCarrierInfo */
    private $WOrderShippingCarrierInfo;
    /** @var string $WOrderShippingLabelID */
    private $WOrderShippingLabelID;
    /** @var int $WOrderShippingBlock */
    private $WOrderShippingBlock;
    /** @var int $WOrderTotalItems */
    private $WOrderTotalItems;
    /** @var string $WOrderOptionPayment */
    private $WOrderOptionPayment;
    /** @var string $WOrderCodePayment */
    private $WOrderCodePayment;
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
    /** @var double $WOrderTotalPayments */
    private $WOrderTotalPayments;
    /** @var double $WOrderTotalPaymentsDebt */
    private $WOrderTotalPaymentsDebt;
    /** @var int $WOrderSameParcel */
    private $WOrderSameParcel;
    /** @var string $WOrderParcelInfo */
    private $WOrderParcelInfo;
    /** @var string $WOrderCancelInfo */
    private $WOrderCancelInfo;
    /** @var int $WOrderInvoiceCategory */
    private $WOrderInvoiceCategory;
    /** @var int $WOrderInvoice */
    private $WOrderInvoice;
    /** @var int $WOrderInvoiceStatus */
    private $WOrderInvoiceStatus;
    /** @var int $WOrderPaymentPending */
    private $WOrderPaymentPending;
    /** @var int $WOrderPReceiptStatus */
    private $WOrderPReceiptStatus;
    /** @var int $WOrderPaymentStatus */
    private $WOrderPaymentStatus;
    /** @var int $WOrderDeliveryStatus */
    private $WOrderDeliveryStatus;

    private $WOrderInventoryOrder;
    private $WOrderInventoryOrderDetails;

    /** @var string $ERateDate */
    private $ERateDate;
    /** @var double $ERateValue */
    private $ERateValue;
    /** @var int $Cancelled */
    private $Cancelled;
    /** @var string $CancelledInfo */
    private $CancelledInfo;

    function __construct(Order $order = null)
    {
        $options = (new OrmOptions);
        $options->setDb(Web::getDB());
        $options->setTableName('web_orders_orders');
        $options->setPrimaryKey('WOrderID');
        $options->setCreationDocDate('DocDate');
        $options->setCancelled(true, true);

        $this->setOptions($options);

        if($order)
        {
            $this->load($order);
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
    public function getWOrderID()
    {
        return $this->WOrderID;
    }

    /**
     * @param int $WOrderID
     */
    public function setWOrderID($WOrderID)
    {
        $this->WOrderID = $WOrderID;
    }

    /**
     * @return int
     */
    public function getCallerID()
    {
        return $this->CallerID;
    }

    /**
     * @param int $CallerID
     */
    public function setCallerID($CallerID)
    {
        $this->CallerID = $CallerID;
    }

    /**
     * @return string
     */
    public function getCallerModule()
    {
        return $this->CallerModule;
    }

    /**
     * @param string $CallerModule
     */
    public function setCallerModule($CallerModule)
    {
        $this->CallerModule = $CallerModule;
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
     * @return string
     */
    public function getWOrderDate()
    {
        return $this->WOrderDate;
    }

    /**
     * @param string $WOrderDate
     */
    public function setWOrderDate($WOrderDate)
    {
        $this->WOrderDate = $WOrderDate;
    }

    /**
     * @return int
     */
    public function getWOrderStatus()
    {
        return $this->WOrderStatus;
    }

    /**
     * @param int $WOrderStatus
     */
    public function setWOrderStatus($WOrderStatus)
    {
        $this->WOrderStatus = $WOrderStatus;
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
     * @return string
     */
    public function getWOrderShippingAddressID(): string
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
    public function getWOrderShippingOfficeID(): string
    {
        return $this->WOrderShippingOfficeID;
    }

    /**
     * @param string $WOrderShippingOfficeID
     */
    public function setWOrderShippingOfficeID(string $WOrderShippingOfficeID): void
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
     * @return string
     */
    public function getWOrderShippingAddress2(): string
    {
        return $this->WOrderShippingAddress2;
    }

    /**
     * @param string $WOrderShippingAddress2
     */
    public function setWOrderShippingAddress2(string $WOrderShippingAddress2): void
    {
        $this->WOrderShippingAddress2 = $WOrderShippingAddress2;
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
     * @return int
     */
    public function getWOrderShippingBlock()
    {
        return $this->WOrderShippingBlock;
    }

    /**
     * @param int $WOrderShippingBlock
     */
    public function setWOrderShippingBlock($WOrderShippingBlock)
    {
        $this->WOrderShippingBlock = $WOrderShippingBlock;
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
    public function getWOrderCodePayment()
    {
        return $this->WOrderCodePayment;
    }

    /**
     * @param string $WOrderCodePayment
     */
    public function setWOrderCodePayment($WOrderCodePayment)
    {
        $this->WOrderCodePayment = $WOrderCodePayment;
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
     * @return float
     */
    public function getWOrderTotalPayments()
    {
        return $this->WOrderTotalPayments;
    }

    /**
     * @param float $WOrderTotalPayments
     */
    public function setWOrderTotalPayments($WOrderTotalPayments)
    {
        $this->WOrderTotalPayments = $WOrderTotalPayments;
    }

    /**
     * @return float
     */
    public function getWOrderTotalPaymentsDebt()
    {
        return $this->WOrderTotalPaymentsDebt;
    }

    /**
     * @param float $WOrderTotalPaymentsDebt
     */
    public function setWOrderTotalPaymentsDebt($WOrderTotalPaymentsDebt)
    {
        $this->WOrderTotalPaymentsDebt = $WOrderTotalPaymentsDebt;
    }

    /**
     * @return int
     */
    public function getWOrderSameParcel()
    {
        return $this->WOrderSameParcel;
    }

    /**
     * @param int $WOrderSameParcel
     */
    public function setWOrderSameParcel($WOrderSameParcel)
    {
        $this->WOrderSameParcel = $WOrderSameParcel;
    }

    /**
     * @return string
     */
    public function getWOrderParcelInfo()
    {
        return $this->WOrderParcelInfo;
    }

    /**
     * @param string $WOrderParcelInfo
     */
    public function setWOrderParcelInfo($WOrderParcelInfo)
    {
        $this->WOrderParcelInfo = $WOrderParcelInfo;
    }

    /**
     * @return string
     */
    public function getWOrderCancelInfo(): string
    {
        return $this->WOrderCancelInfo;
    }

    /**
     * @param string $WOrderCancelInfo
     */
    public function setWOrderCancelInfo(string $WOrderCancelInfo): void
    {
        $this->WOrderCancelInfo = $WOrderCancelInfo;
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
     * @return int
     */
    public function getWOrderInvoiceStatus(): int
    {
        return $this->WOrderInvoiceStatus;
    }

    /**
     * @param int $WOrderInvoiceStatus
     */
    public function setWOrderInvoiceStatus(int $WOrderInvoiceStatus): void
    {
        $this->WOrderInvoiceStatus = $WOrderInvoiceStatus;
    }

    /**
     * @return int
     */
    public function getWOrderPaymentPending(): int
    {
        return $this->WOrderPaymentPending;
    }

    /**
     * @param int $WOrderPaymentPending
     */
    public function setWOrderPaymentPending(int $WOrderPaymentPending): void
    {
        $this->WOrderPaymentPending = $WOrderPaymentPending;
    }

    /**
     * @return int
     */
    public function getWOrderPReceiptStatus(): int
    {
        return $this->WOrderPReceiptStatus;
    }

    /**
     * @param int $WOrderPReceiptStatus
     */
    public function setWOrderPReceiptStatus(int $WOrderPReceiptStatus): void
    {
        $this->WOrderPReceiptStatus = $WOrderPReceiptStatus;
    }

    /**
     * @return int
     */
    public function getWOrderPaymentStatus(): int
    {
        return $this->WOrderPaymentStatus;
    }

    /**
     * @param int $WOrderPaymentStatus
     */
    public function setWOrderPaymentStatus(int $WOrderPaymentStatus)
    {
        $this->WOrderPaymentStatus = $WOrderPaymentStatus;
    }

    /**
     * @return int
     */
    public function getWOrderDeliveryStatus(): int
    {
        return $this->WOrderDeliveryStatus;
    }

    /**
     * @param int $WOrderDeliveryStatus
     */
    public function setWOrderDeliveryStatus(int $WOrderDeliveryStatus)
    {
        $this->WOrderDeliveryStatus = $WOrderDeliveryStatus;
    }

    /**
     * @return mixed
     */
    public function getWOrderInventoryOrder()
    {
        return $this->WOrderInventoryOrder;
    }

    /**
     * @param mixed $WOrderInventoryOrder
     */
    public function setWOrderInventoryOrder($WOrderInventoryOrder): void
    {
        $this->WOrderInventoryOrder = $WOrderInventoryOrder;
    }

    /**
     * @return mixed
     */
    public function getWOrderInventoryOrderDetails()
    {
        return $this->WOrderInventoryOrderDetails;
    }

    /**
     * @param mixed $WOrderInventoryOrderDetails
     */
    public function setWOrderInventoryOrderDetails($WOrderInventoryOrderDetails): void
    {
        $this->WOrderInventoryOrderDetails = $WOrderInventoryOrderDetails;
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

        $this->getWOrderDate() ? : $this->setWOrderDate(date('Y-m-d H:i:s'));

    }

    function update()
    {
        if($this->getWOrderStatus() == Status::cancelled['value'])
        {
            return false;
        }

        $order = $this->find($this->getWOrderID());

        $WOrderStatus = Status::pending['value'];

        if($order->getWOrderPaymentStatus())
        {
            $WOrderStatus = Status::payment['value'];
        }

        if($order->getWOrderPaymentStatus() && $order->getWOrderDeliveryStatus() && $order->getWOrderInvoiceStatus())
        {
            $WOrderStatus = Status::success['value'];
        }

        if($order->getWOrderStatus() == $WOrderStatus)
        {
            return false;
        }

        if($WOrderStatus == Status::pending['value'])
        {
            $this->update_status_pending();
        }
        else if($WOrderStatus == Status::payment['value'])
        {
            (new Actions())->payment_order($order);
        }
        else if($WOrderStatus == Status::success['value'])
        {
            (new Actions())->success_order($order);
        }

    }

    function update_status_pending(int $WOrderID = 0)
    {
        if(!$this->getWOrderID())
        {
            $this->find($WOrderID);
        }

        if(!$this->getWOrderID())
        {
            \BN_Responses::dev("No se ha definido el id de la orden.");
        }

        $sql_update['WOrderStatus'] = Status::pending['value'];

        $this->db->Update($this->_table_name, $sql_update, 'WOrderID', $this->getWOrderID());

        $this->find($this->getWOrderID());
    }

    function update_status_payment(int $WOrderID = 0)
    {
        if(!$this->getWOrderID())
        {
            $this->find($WOrderID);
        }

        if(!$this->getWOrderID())
        {
            \BN_Responses::dev("No se ha definido el id de la orden.");
        }

        $sql_update['WOrderStatus'] = Status::payment['value'];

        $this->db->Update($this->_table_name, $sql_update, 'WOrderID', $this->getWOrderID());


        $this->find($this->getWOrderID());
    }

    function update_status_success(int $WOrderID = 0)
    {
        if(!$this->getWOrderID())
        {
            $this->find($WOrderID);
        }

        if(!$this->getWOrderID())
        {
            \BN_Responses::dev("No se ha definido el id de la orden.");
        }

        $sql_update['WOrderStatus'] = Status::success['value'];

        $this->db->Update($this->_table_name, $sql_update, 'WOrderID', $this->getWOrderID());

        $this->find($this->getWOrderID());
    }

    function update_status_cancel(int $WOrderID = 0, string $reason, string $comment = "")
    {
        if(!$this->getWOrderID())
        {
            $this->find($WOrderID);
        }

        if(!$this->getWOrderID())
        {
            \BN_Responses::dev("No se ha definido el id de la orden.");
        }

        $sql_update['WOrderStatus'] = Status::cancelled['value'];

        $WOrderCancelInfo['Date'] = date('Y-m-d H:i:s');
        $WOrderCancelInfo['IP'] = get_ip();
        $WOrderCancelInfo['UserID'] = \BN_Var::$UserInfo['UserID'];
        $WOrderCancelInfo['WUserID'] = \BN_Var::$WUserInfo['WUserID'];
        $WOrderCancelInfo['Reason'] = $reason;
        $WOrderCancelInfo['Comment'] = $comment;

        $WOrderCancelInfo = \BN_Coders::json_encode($WOrderCancelInfo);

        $sql_update['WOrderCancelInfo'] = $WOrderCancelInfo;

        $this->db->Update($this->_table_name, $sql_update, 'WOrderID', $this->getWOrderID());

        $this->find($this->getWOrderID());
    }

    function actions(): OrderActions
    {
        return new OrderActions($this);
    }

}