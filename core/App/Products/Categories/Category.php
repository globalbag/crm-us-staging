<?php

namespace App\Products\Categories;

use Novut\Db\Orm;

class Category
{

    use Orm;

    protected $CategoryID;
    protected $CategoryName;
    protected $CategoryRef;
    protected $ProductType;
    protected $RowOrder;
    protected $Cancelled;
    protected $CancelledInfo;

   function __construct($CategoryID = null)
   {
       $this->setOptions(Categories::getORMOptions());

       if ($CategoryID && is_numeric($CategoryID))
       {
           $this->find($CategoryID);
       }
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
    public function getCategoryName()
    {
        return $this->CategoryName;
    }

    /**
     * @param mixed $CategoryName
     * @return Category
     */
    public function setCategoryName($CategoryName)
    {
        $this->CategoryName = $CategoryName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCategoryRef()
    {
        return $this->CategoryRef;
    }

    /**
     * @param mixed $CategoryRef
     * @return Category
     */
    public function setCategoryRef($CategoryRef)
    {
        $this->CategoryRef = $CategoryRef;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductType()
    {
        return $this->ProductType;
    }

    /**
     * @param mixed $ProductType
     * @return Category
     */
    public function setProductType($ProductType)
    {
        $this->ProductType = $ProductType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRowOrder()
    {
        return $this->RowOrder;
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
    public function getCancelledInfo()
    {
        return $this->CancelledInfo;
    }


}