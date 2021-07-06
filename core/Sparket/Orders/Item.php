<?php

namespace Sparket\Orders;

use Novut\Db\Orm;

class Item
{
    use Orm;

    /** @var Order $order */
    private $order;

    /** @var int $WOItemID */
    private $WOItemID;
    /** @var int $WOrderID */
    private $WOrderID;
    /** @var int $WCartItemID */
    private $WCartItemID;
    /** @var int $ProductID */
    private $ProductID;
    /** @var string $ProductCode */
    private $ProductCode;
    /** @var string $ProductPartNumber */
    private $ProductPartNumber;
    /** @var int $PriceID */
    private $PriceID;
    /** @var double $PriceValue */
    private $PriceValue;
    /** @var string $WOItemName */
    private $WOItemName;
    /** @var int $WOItemQty */
    private $WOItemQty;
    /** @var double $WOItemPriceTaxMXN */
    private $WOItemPriceTaxMXN;
    /** @var double $WOItemPriceTaxUSD */
    private $WOItemPriceTaxUSD;
    /** @var double $WOItemPriceMXN */
    private $WOItemPriceMXN;
    /** @var double $WOItemPriceUSD */
    private $WOItemPriceUSD;
    /** @var double $WOItemAmountMXN */
    private $WOItemAmountMXN;
    /** @var double $WOItemAmountUSD */
    private $WOItemAmountUSD;
    /** @var double $WOItemTaxMXN */
    private $WOItemTaxMXN;
    /** @var double $WOItemTaxUSD */
    private $WOItemTaxUSD;
    /** @var double $WOItemTotalMXN */
    private $WOItemTotalMXN;
    /** @var double $WOItemTotalUSD */
    private $WOItemTotalUSD;
    /** @var double $WOItemAmount */
    private $WOItemAmount;
    /** @var double $WOItemTax */
    private $WOItemTax;
    /** @var double $WOItemTotal */
    private $WOItemTotal;
    /** @var string $WOItemCurrency */
    private $WOItemCurrency;
    /** @var string $WOItemDelivery */
    private $WOItemDelivery;
    /** @var int $WOItemDeliveryItems */
    private $WOItemDeliveryItems;
    /** int $WOItemDeliveryStatus */
    private $WOItemDeliveryStatus;
    /** @var int $Cancelled */
    private $Cancelled;
    /** string $CancelledInfo */
    private $CancelledInfo;

    function __construct(Order $order, self $item = null)
    {
        $this->setOptions(Items::getORMOptions());

        $this->order = $order;

        if(!$this->order->getWOrderID() || ($this->order->getWOrderID() && $this->order->getCancelled()))
        {
            \BN_Responses::dev("La orden no existe.");
        }

        if ($item)
        {
            $this->load($item);

            if($this->order->getWOrderID() != $this->getWOrderID())
            {
                \BN_Responses::dev("El item no pertenece a la orden.");
            }
        }
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
    public function getWCartItemID(): int
    {
        return $this->WCartItemID;
    }

    /**
     * @param int $WCartItemID
     */
    public function setWCartItemID(int $WCartItemID)
    {
        $this->WCartItemID = $WCartItemID;
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
    public function setProductID(int $ProductID)
    {
        $this->ProductID = $ProductID;
    }

    /**
     * @return string
     */
    public function getProductCode(): string
    {
        return $this->ProductCode;
    }

    /**
     * @param string $ProductCode
     */
    public function setProductCode(string $ProductCode)
    {
        $this->ProductCode = $ProductCode;
    }

    /**
     * @return string
     */
    public function getProductPartNumber(): string
    {
        return $this->ProductPartNumber;
    }

    /**
     * @param string $ProductPartNumber
     */
    public function setProductPartNumber(string $ProductPartNumber): void
    {
        $this->ProductPartNumber = $ProductPartNumber;
    }

    /**
     * @return int
     */
    public function getPriceID(): int
    {
        return $this->PriceID;
    }

    /**
     * @param int $PriceID
     */
    public function setPriceID(int $PriceID)
    {
        $this->PriceID = $PriceID;
    }

    /**
     * @return float
     */
    public function getPriceValue(): float
    {
        return $this->PriceValue;
    }

    /**
     * @param float $PriceValue
     */
    public function setPriceValue(float $PriceValue)
    {
        $this->PriceValue = $PriceValue;
    }

    /**
     * @return string
     */
    public function getWOItemName(): string
    {
        return $this->WOItemName;
    }

    /**
     * @param string $WOItemName
     */
    public function setWOItemName(string $WOItemName)
    {
        $this->WOItemName = $WOItemName;
    }

    /**
     * @return int
     */
    public function getWOItemQty(): int
    {
        return $this->WOItemQty;
    }

    /**
     * @param int $WOItemQty
     */
    public function setWOItemQty(int $WOItemQty)
    {
        $this->WOItemQty = $WOItemQty;
    }

    /**
     * @return float
     */
    public function getWOItemPriceTaxMXN(): float
    {
        return $this->WOItemPriceTaxMXN;
    }

    /**
     * @param float $WOItemPriceTaxMXN
     */
    public function setWOItemPriceTaxMXN(float $WOItemPriceTaxMXN)
    {
        $this->WOItemPriceTaxMXN = $WOItemPriceTaxMXN;
    }

    /**
     * @return float
     */
    public function getWOItemPriceMXN(): float
    {
        return $this->WOItemPriceMXN;
    }

    /**
     * @param float $WOItemPriceMXN
     */
    public function setWOItemPriceMXN(float $WOItemPriceMXN)
    {
        $this->WOItemPriceMXN = $WOItemPriceMXN;
    }

    /**
     * @return float
     */
    public function getWOItemAmountMXN(): float
    {
        return $this->WOItemAmountMXN;
    }

    /**
     * @param float $WOItemAmountMXN
     */
    public function setWOItemAmountMXN(float $WOItemAmountMXN)
    {
        $this->WOItemAmountMXN = $WOItemAmountMXN;
    }

    /**
     * @return float
     */
    public function getWOItemTaxMXN(): float
    {
        return $this->WOItemTaxMXN;
    }

    /**
     * @param float $WOItemTaxMXN
     */
    public function setWOItemTaxMXN(float $WOItemTaxMXN)
    {
        $this->WOItemTaxMXN = $WOItemTaxMXN;
    }

    /**
     * @return float
     */
    public function getWOItemTotalMXN(): float
    {
        return $this->WOItemTotalMXN;
    }

    /**
     * @param float $WOItemTotalMXN
     */
    public function setWOItemTotalMXN(float $WOItemTotalMXN)
    {
        $this->WOItemTotalMXN = $WOItemTotalMXN;
    }

    /**
     * @return float
     */
    public function getWOItemPriceTaxUSD(): float
    {
        return $this->WOItemPriceTaxUSD;
    }

    /**
     * @param float $WOItemPriceTaxUSD
     */
    public function setWOItemPriceTaxUSD(float $WOItemPriceTaxUSD)
    {
        $this->WOItemPriceTaxUSD = $WOItemPriceTaxUSD;
    }

    /**
     * @return float
     */
    public function getWOItemPriceUSD(): float
    {
        return $this->WOItemPriceUSD;
    }

    /**
     * @param float $WOItemPriceUSD
     */
    public function setWOItemPriceUSD(float $WOItemPriceUSD)
    {
        $this->WOItemPriceUSD = $WOItemPriceUSD;
    }

    /**
     * @return float
     */
    public function getWOItemAmountUSD(): float
    {
        return $this->WOItemAmountUSD;
    }

    /**
     * @param float $WOItemAmountUSD
     */
    public function setWOItemAmountUSD(float $WOItemAmountUSD)
    {
        $this->WOItemAmountUSD = $WOItemAmountUSD;
    }

    /**
     * @return float
     */
    public function getWOItemTaxUSD(): float
    {
        return $this->WOItemTaxUSD;
    }

    /**
     * @param float $WOItemTaxUSD
     */
    public function setWOItemTaxUSD(float $WOItemTaxUSD)
    {
        $this->WOItemTaxUSD = $WOItemTaxUSD;
    }

    /**
     * @return float
     */
    public function getWOItemTotalUSD(): float
    {
        return $this->WOItemTotalUSD;
    }

    /**
     * @param float $WOItemTotalUSD
     */
    public function setWOItemTotalUSD(float $WOItemTotalUSD)
    {
        $this->WOItemTotalUSD = $WOItemTotalUSD;
    }

    /**
     * @return float
     */
    public function getWOItemAmount(): float
    {
        return $this->WOItemAmount;
    }

    /**
     * @param float $WOItemAmount
     */
    public function setWOItemAmount(float $WOItemAmount)
    {
        $this->WOItemAmount = $WOItemAmount;
    }

    /**
     * @return float
     */
    public function getWOItemTax(): float
    {
        return $this->WOItemTax;
    }

    /**
     * @param float $WOItemTax
     */
    public function setWOItemTax(float $WOItemTax)
    {
        $this->WOItemTax = $WOItemTax;
    }

    /**
     * @return float
     */
    public function getWOItemTotal(): float
    {
        return $this->WOItemTotal;
    }

    /**
     * @param float $WOItemTotal
     */
    public function setWOItemTotal(float $WOItemTotal)
    {
        $this->WOItemTotal = $WOItemTotal;
    }

    /**
     * @return string
     */
    public function getWOItemCurrency(): string
    {
        return $this->WOItemCurrency;
    }

    /**
     * @param string $WOItemCurrency
     */
    public function setWOItemCurrency(string $WOItemCurrency)
    {
        $this->WOItemCurrency = $WOItemCurrency;
    }

    /**
     * @return string
     */
    public function getWOItemDelivery(): ?string
    {
        return $this->WOItemDelivery;
    }

    /**
     * @param string|null $WOItemDelivery
     */
    public function setWOItemDelivery(string $WOItemDelivery)
    {
        $this->WOItemDelivery = $WOItemDelivery;
    }

    /**
     * @return int
     */
    public function getWOItemDeliveryItems(): int
    {
        return $this->WOItemDeliveryItems;
    }

    /**
     * @param int $WOItemDeliveryItems
     */
    public function setWOItemDeliveryItems(int $WOItemDeliveryItems)
    {
        $this->WOItemDeliveryItems = $WOItemDeliveryItems;
    }

    /**
     * @return mixed
     */
    public function getWOItemDeliveryStatus()
    {
        return $this->WOItemDeliveryStatus;
    }

    /**
     * @param mixed $WOItemDeliveryStatus
     */
    public function setWOItemDeliveryStatus($WOItemDeliveryStatus)
    {
        $this->WOItemDeliveryStatus = $WOItemDeliveryStatus;
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
    }
}