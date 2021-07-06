<?php


namespace App\Products\Product;


use App\Products\Brands\Brand;
use App\Products\Category;
use Novut\Db\Orm;

class Product
{

    use Orm;

    protected $ProductID;
    protected $ProductCode;
    protected $ProductSKU;
    protected $ProductPart;
    protected $ProductName;
    protected $ProductDescription;

    protected $BrandID;
    protected $BrandName;

    protected $BFamilyID;
    protected $BFamilyData;

    protected $CategoryID;
    protected $CategoryPath;
    protected $ProductDisabled;
    protected $PPriceID;
    protected $PPricePrice;
    protected $PPriceCurrencyCode;
    protected $PPriceCurrencyID;

    protected $ProductQtyPP;
    protected $ProductContent;
    protected $ProductStock;

    protected $Cancelled;

    function __construct($ProductID = null)
    {
        $this->setOptions(Products::getORMOptions());

        if ($ProductID)
        {
            $this->find($ProductID);
        }
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
    public function getProductCode()
    {
        return $this->ProductCode;
    }

    /**
     * @param mixed $ProductCode
     * @return Product
     */
    public function setProductCode($ProductCode)
    {
        $this->ProductCode = $ProductCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductSKU()
    {
        return $this->ProductSKU;
    }

    /**
     * @param mixed $ProductSKU
     * @return Product
     */
    public function setProductSKU($ProductSKU)
    {
        $this->ProductSKU = $ProductSKU;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductPart()
    {
        return $this->ProductPart;
    }

    /**
     * @param mixed $ProductPart
     * @return Product
     */
    public function setProductPart($ProductPart)
    {
        $this->ProductPart = $ProductPart;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductName()
    {
        return $this->ProductName;
    }

    /**
     * @param mixed $ProductName
     * @return Product
     */
    public function setProductName($ProductName)
    {
        $this->ProductName = $ProductName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductDescription()
    {
        return $this->ProductDescription;
    }

    /**
     * @param mixed $ProductDescription
     * @return Product
     */
    public function setProductDescription($ProductDescription)
    {
        $this->ProductDescription = $ProductDescription;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductQtyPP()
    {
        return (int) $this->ProductQtyPP;
    }

    /**
     * @param mixed $ProductQtyPP
     * @return Product
     */
    public function setProductQtyPP($ProductQtyPP)
    {
        $this->ProductQtyPP = (int) $ProductQtyPP;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductContent()
    {
        return $this->ProductContent;
    }

    /**
     * @param mixed $ProductContent
     * @return Product
     */
    public function setProductContent($ProductContent)
    {
        $this->ProductContent = $ProductContent;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductStock()
    {
        return $this->ProductStock;
    }

    /**
     * @param mixed $ProductStock
     * @return Product
     */
    public function setProductStock($ProductStock)
    {
        $this->ProductStock = $ProductStock;
        return $this;
    }



    /**
     * @return mixed
     */
    public function getBFamilyID()
    {
        return $this->BFamilyID;
    }

    /**
     * @return mixed
     */
    public function getBFamilyData()
    {
        return $this->BFamilyData;
    }



    /**
     * @return mixed
     */
    public function getBrandID()
    {
        return $this->BrandID;
    }

    /**
     * @return mixed
     */
    public function getBrandName()
    {
        return $this->BrandName;
    }

    /**
     * @param Brand $brand
     * @return Product
     */
    public function setBrand(Brand $brand)
    {
        $this->BrandID = $brand->getBrandID();
        $this->BrandName = $brand->getBrandName();
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCategoryID()
    {
        return $this->CategoryID;
    }

    /**
     * @return mixed
     */
    public function getCategoryPath()
    {
        return $this->CategoryPath;
    }

    /**
     * @param Category $category
     * @return Product
     */
    public function setCategory(Category $category)
    {
        $this->CategoryID = $category->getCategoryID();
        $this->CategoryPath = $category->getCategoryPath();
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductDisabled()
    {
        return $this->ProductDisabled;
    }

    /**
     * @param bool $disabled
     * @return Product
     */
    public function setProductDisabled(bool $disabled)
    {
        $this->ProductDisabled = $disabled ? 1 : 0;
        return $this;
    }

    /**
     * @return int
     */
    public function getPPriceID()
    {
        return $this->PPriceID;
    }

    /**
     * @return float
     */
    public function getPPricePrice()
    {
        return $this->PPricePrice;
    }

    /**
     * @return string
     */
    public function getPPriceCurrencyCode()
    {
        return $this->PPriceCurrencyCode;
    }

    /**
     * @return int
     */
    public function getPPriceCurrencyID()
    {
        return $this->PPriceCurrencyID;
    }



    public function setPrice(Price $price)
    {
        $this->PPriceID = $price->getPPriceID();
        $this->PPricePrice = $price->getPPricePrice();
        $this->PPriceCurrencyID = $price->getPPriceCurrencyID();
        $this->PPriceCurrencyCode = $price->getPPriceCurrencyCode();
    }

    /**
     * @return mixed
     */
    public function getCancelled()
    {
        return $this->Cancelled;
    }

    public function export_raw()
    {
        return $this->_export_raw();
    }



}