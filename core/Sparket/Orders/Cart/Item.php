<?php

namespace Sparket\Orders\Cart;

use Novut\Core\Query;
use Novut\Db\Orm;
use Novut\Db\OrmOptions;
use Sparket\DB\Web;
use Sparket\Orders\Taxes;

class Item
{
    use Orm {add as parent_add; }

    /** @var Cart $cart */
    private $cart;

    /** @var int $WOItemID */
    private $WOItemID;
    /** @var int $WOItemGUID */
    private $WOItemGUID;
    /** @var int CartID */
    private $WCartID;
    /** @var int ProductID */
    private $ProductID;
    /** @var string ProductCode */
    private $ProductCode;
    /** @var string ProductPartNumber */
    private $ProductPartNumber;
    /** @var int PriceID */
    private $PriceID;
    /** @var double PriceValue */
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
    /** @var int $WODeliveryID */
    private $WODeliveryID;
    /** @var int $Cancelled */
    private $Cancelled;
    /** @var string $CancelledInfo */
    private $CancelledInfo;

    /** @var bool $_disable_update */
    private $_disable_update = false;

    function __construct(Cart $cart, self $item = null)
    {
        $options = (new OrmOptions);
        $options->setDb(Web::getDB());
        $options->setTableName('web_orders_cart_items');
        $options->setPrimaryKey('WOItemID');
        $options->setCancelled(true);

        $this->setOptions($options);

        $this->cart = $cart;

        if(!$this->cart->getWCartID() || ($this->cart->getWCartID() && $this->cart->getCancelled()))
        {
            \BN_Responses::dev("El carrito no existe.");
        }

        if ($item)
        {
            $this->load($item);

            if($this->cart->getWCartID() != $this->getWCartID())
            {
                \BN_Responses::dev("El item no pertenece al carrito.");
            }
        }
    }

    /**
     * @return int
     */
    public function getWOItemID()
    {
        return $this->WOItemID;
    }

    /**
     * @param int $WOItemID
     */
    public function setWOItemID($WOItemID)
    {
        $this->WOItemID = $WOItemID;
    }

    /**
     * @return int
     */
    public function getWOItemGUID()
    {
        return $this->WOItemGUID;
    }

    /**
     * @param int $WOItemGUID
     */
    public function setWOItemGUID($WOItemGUID)
    {
        $this->WOItemGUID = $WOItemGUID;
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
    public function getProductID()
    {
        return $this->ProductID;
    }

    /**
     * @param int $ProductID
     */
    public function setProductID($ProductID)
    {
        $this->ProductID = $ProductID;
    }

    /**
     * @return string
     */
    public function getProductCode()
    {
        return $this->ProductCode;
    }

    /**
     * @param string $ProductCode
     */
    public function setProductCode($ProductCode)
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
    public function getPriceID()
    {
        return $this->PriceID;
    }

    /**
     * @param int $PriceID
     */
    public function setPriceID($PriceID)
    {
        $this->PriceID = $PriceID;
    }

    /**
     * @return float
     */
    public function getPriceValue()
    {
        return $this->PriceValue;
    }

    /**
     * @param float $PriceValue
     */
    public function setPriceValue($PriceValue)
    {
        $this->PriceValue = $PriceValue;
    }

    /**
     * @return string
     */
    public function getWOItemName()
    {
        return $this->WOItemName;
    }

    /**
     * @param string $WOItemName
     */
    public function setWOItemName($WOItemName)
    {
        $this->WOItemName = $WOItemName;
    }

    /**
     * @return int
     */
    public function getWOItemQty()
    {
        return $this->WOItemQty;
    }

    /**
     * @param int $WOItemQty
     */
    public function setWOItemQty($WOItemQty)
    {
        $this->WOItemQty = $WOItemQty;
    }

    /**
     * @return float
     */
    public function getWOItemPriceTaxMXN()
    {
        return $this->WOItemPriceTaxMXN;
    }

    /**
     * @param float $WOItemPriceTaxMXN
     */
    public function setWOItemPriceTaxMXN($WOItemPriceTaxMXN)
    {
        $this->WOItemPriceTaxMXN = $WOItemPriceTaxMXN;
    }

    /**
     * @return float
     */
    public function getWOItemPriceMXN()
    {
        return $this->WOItemPriceMXN;
    }

    /**
     * @param float $WOItemPriceMXN
     */
    public function setWOItemPriceMXN($WOItemPriceMXN)
    {
        $this->WOItemPriceMXN = $WOItemPriceMXN;
    }

    /**
     * @return float
     */
    public function getWOItemAmountMXN()
    {
        return $this->WOItemAmountMXN;
    }

    /**
     * @param float $WOItemAmountMXN
     */
    public function setWOItemAmountMXN($WOItemAmountMXN)
    {
        $this->WOItemAmountMXN = $WOItemAmountMXN;
    }

    /**
     * @return float
     */
    public function getWOItemTaxMXN()
    {
        return $this->WOItemTaxMXN;
    }

    /**
     * @param float $WOItemTaxMXN
     */
    public function setWOItemTaxMXN($WOItemTaxMXN)
    {
        $this->WOItemTaxMXN = $WOItemTaxMXN;
    }

    /**
     * @return float
     */
    public function getWOItemTotalMXN()
    {
        return $this->WOItemTotalMXN;
    }

    /**
     * @param float $WOItemTotalMXN
     */
    public function setWOItemTotalMXN($WOItemTotalMXN)
    {
        $this->WOItemTotalMXN = $WOItemTotalMXN;
    }

    /**
     * @return int
     */
    public function getWODeliveryID()
    {
        return $this->WODeliveryID;
    }

    /**
     * @param int $WODeliveryID
     */
    public function setWODeliveryID($WODeliveryID)
    {
        $this->WODeliveryID = $WODeliveryID;
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

    /**
     * @param bool $disable_update
     */
    public function setDisableUpdate(bool $disable_update = true)
    {
        $this->_disable_update = $disable_update;
    }

    function add_before()
    {
        $query = new Query();

        $query->addParam('ProductID', $this->getProductID());
        $query->addQuery(" AND ProductID = :ProductID ");

        $query->addQuery(" AND Cancelled = 0 ");

        $product_info = $this->db->TableInfo('products', false, false, $query->getQuery(), $query->getParams());

        if(!$product_info)
        {
            \BN_Responses::dev("El id del producto no existe. Intente nuevamente.");
        }

        $this->setWCartID($this->cart->getWCartID());
        $this->setProductID($product_info['ProductID']);
        $this->setProductCode($product_info['ProductCode']);
        $this->setProductPartNumber($product_info['ProductPartNumber']);

        $this->setWOItemName($product_info['ProductName']);
        $this->setWOItemQty(1);

        // USD
        $this->setWOItemPriceUSD($product_info['ProductPrice']);
        $this->setWOItemPriceTaxUSD(( $this->getWOItemPriceUSD() * (new Taxes())->getIVA()));

        $this->setWOItemAmountUSD($this->getWOItemPriceUSD() * $this->getWOItemQty());
        $this->setWOItemTaxUSD($this->getWOItemPriceTaxUSD() * $this->getWOItemQty());
        $this->setWOItemTotalUSD($this->getWOItemAmountUSD() + $this->getWOItemTaxUSD());

        // MXN
        $this->setWOItemPriceMXN($this->getWOItemPriceUSD() * $this->cart->getERateValue());
        $this->setWOItemPriceTaxMXN(( $this->getWOItemPriceMXN() * (new Taxes())->getIVA()));

        $this->setWOItemAmountMXN($this->getWOItemPriceMXN() * $this->getWOItemQty());
        $this->setWOItemTaxMXN($this->getWOItemPriceTaxMXN() * $this->getWOItemQty());
        $this->setWOItemTotalMXN($this->getWOItemAmountMXN() + $this->getWOItemTaxMXN());

        $this->setWOItemAmount($this->getWOItemAmountMXN());
        $this->setWOItemTax($this->getWOItemTaxMXN());
        $this->setWOItemTotal($this->getWOItemTotalMXN());
        $this->setWOItemCurrency("MXN");
    }

    function add()
    {
        if(!$this->getProductID())
        {
            \BN_Responses::dev("No fue definido el id del producto. Intente nuevamente.");
        }

        $item_info = (new Items($this->cart))->info_by_product($this->getProductID());

        if($item_info)
        {
            $this->import($item_info);
            $this->setWOItemQty(($this->getWOItemQty() + 1));
            $this->save();
        }
        else
        {
            $this->parent_add();
        }
    }

    function add_after()
    {

    }

    function save_before()
    {

    }

    function save_after()
    {
        $this->update();
    }

    function cancel_after()
    {
        $this->cart->update();
    }

    function update()
    {
        if($this->_disable_update)
        {
            return false;
        }

        // USD
        $WOItemAmountUSD = $this->getWOItemPriceUSD() * $this->getWOItemQty();
        $WOItemTaxUSD = $this->getWOItemPriceTaxUSD() * $this->getWOItemQty();
        $WOItemTotalUSD = $WOItemAmountUSD + $WOItemTaxUSD;

        $sql_update['WOItemAmountUSD'] = $WOItemAmountUSD;
        $sql_update['WOItemTaxUSD'] = $WOItemTaxUSD;
        $sql_update['WOItemTotalUSD'] = $WOItemTotalUSD;
        
        // MXN
        $WOItemAmountMXN = $this->getWOItemPriceMXN() * $this->getWOItemQty();
        $WOItemTaxMXN = $this->getWOItemPriceTaxMXN() * $this->getWOItemQty();
        $WOItemTotalMXN = $WOItemAmountMXN + $WOItemTaxMXN;

        $sql_update['WOItemAmountMXN'] = $WOItemAmountMXN;
        $sql_update['WOItemTaxMXN'] = $WOItemTaxMXN;
        $sql_update['WOItemTotalMXN'] = $WOItemTotalMXN;

        $this->db->Update($this->_table_name, $sql_update, 'WOItemID', $this->getWOItemID());

        $this->cart->update();
    }

}