<?php


namespace App\Products\Product;


use App\Products\Prices\PList;
use Novut\Db\Orm;
use Novut\Exceptions\Exception;

class Price
{
    use Orm;

    protected $PPriceID;
    protected $ProductID;
    protected $PListID;
    protected $PPricePrice;
    protected $PPriceName;
    protected $PPriceVolMin;
    protected $PPriceVolMax;
    protected $PPriceDiscountLimit;
    protected $PPriceCurrencyID;
    protected $PPriceCurrencyCode;
    protected $PPriceUpdate;
    protected $PPriceUpdateUser;
    protected $Cancelled;

    /** @var Product $product */
    protected $product;

    /** @var PList|null $price_list */
    protected $price_list;

    /**
     * Price constructor.
     * @param Product $product
     * @param PList|null $price_list
     * @param null $PPriceID
     * @throws Exception
     */
    function __construct(Product $product, PList $price_list = null, $PPriceID = null)
    {
        $this->setOptions(Prices::getORMOptions());

        $this->product = $product;
        $this->price_list = $price_list ? : (new PList())->loadDefault();

        if (!$this->product->getProductID())
        {
            throw new Exception("El Producto no existe");
        }
        $this->find($PPriceID);
    }


    /**
     * @return mixed
     */
    public function getPPriceID()
    {
        return $this->PPriceID;
    }

    /**
     * @return mixed
     */
    public function getProductID()
    {
        return $this->ProductID;
    }

    /**
     * @return mixed
     */
    public function getPListID()
    {
        return $this->PListID;
    }

    /**
     * @return mixed
     */
    public function getPPricePrice()
    {
        return $this->PPricePrice;
    }

    /**
     * @param mixed $PPricePrice
     * @return Price
     */
    public function setPPricePrice($PPricePrice)
    {
        $this->PPricePrice = (float) $PPricePrice;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPPriceName()
    {
        return $this->PPriceName;
    }

    /**
     * @param mixed $PPriceName
     * @return Price
     */
    public function setPPriceName(string $PPriceName)
    {
        $this->PPriceName = $PPriceName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPPriceVolMin()
    {
        return $this->PPriceVolMin;
    }

    /**
     * @param mixed $PPriceVolMin
     * @return Price
     */
    public function setPPriceVolMin($PPriceVolMin)
    {
        $this->PPriceVolMin = (float) $PPriceVolMin;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPPriceVolMax()
    {
        return (int) $this->PPriceVolMax;
    }

    /**
     * @param mixed $PPriceVolMax
     * @return Price
     */
    public function setPPriceVolMax($PPriceVolMax)
    {
        $this->PPriceVolMax = (float) $PPriceVolMax;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPPriceCurrencyID()
    {
        return $this->PPriceCurrencyID;
    }

    /**
     * @return mixed
     */
    public function getPPriceCurrencyCode()
    {
        return $this->PPriceCurrencyCode;
    }

    /**
     * @param mixed $PPriceCurrencyID
     * @return Price
     */
    public function setPPriceCurrency($PPriceCurrencyID)
    {
        $this->PPriceCurrencyID = $PPriceCurrencyID;
        $this->PPriceCurrencyCode = \BN_Locale::currency_code($PPriceCurrencyID);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCancelled()
    {
        return $this->Cancelled;
    }

    /**
     * @return mixed
     */
    public function getPPriceUpdate()
    {
        return $this->PPriceUpdate;
    }

    /**
     * @return mixed
     */
    public function getPPriceUpdateUser()
    {
        return $this->PPriceUpdateUser;
    }



    /**
     * @return mixed
     */
    public function getPPriceDiscountLimit()
    {
        return $this->PPriceDiscountLimit;
    }

    /**
     * @param mixed $PPriceDiscountLimit
     * @return Price
     */
    public function setPPriceDiscountLimit($PPriceDiscountLimit)
    {
        $this->PPriceDiscountLimit = (float) $PPriceDiscountLimit;
        return $this;
    }


    protected function add_before()
    {
        $this->ProductID = $this->product->getProductID();
        $this->PListID = $this->price_list->getPListID();
        $this->PPriceUpdate = date('Y-m-d H:i:s');
        $this->PPriceUpdateUser = \BN_Var::$UserInfo['UserID'];
    }

    protected function save_before()
    {
        $this->PPriceUpdate = date('Y-m-d H:i:s');
        $this->PPriceUpdateUser = \BN_Var::$UserInfo['UserID'];
    }

}