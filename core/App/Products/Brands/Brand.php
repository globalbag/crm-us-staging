<?php

namespace App\Products\Brands;

use Novut\Core\Query;

class Brand
{
    protected $BrandID;
    protected $BrandName;
    protected $BrandCode;
    protected $RowOrder;
    protected $Cancelled;
    protected $CancelledInfo;

    use \Novut\Db\Orm;

    function __construct($brand_id = null)
    {
        $this->setOptions(Brands::getORMOptions());

        if ($brand_id && is_numeric($brand_id))
        {
            $this->find($brand_id);
        }
        else if ($brand_id && is_string($brand_id))
        {
            $this->find_by_code($brand_id);
        }

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
    public function getBrandCode()
    {
        return $this->BrandCode;
    }

    /**
     * @param mixed $BrandCode
     * @return Brand
     *
     */
    public function setBrandCode($BrandCode)
    {
        $this->BrandCode = $BrandCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBrandName()
    {
        return $this->BrandName;
    }

    /**
     * @param mixed $BrandName
     * @return Brand
     */
    public function setBrandName($BrandName)
    {
        $this->BrandName = $BrandName;
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

    public function find_by_code(string $alias)
    {
        $query = new Query();
        $query->setWhereField('BrandCode');
        $query->setWhereValue($alias);
        $query->addQuery(" AND Cancelled != 1");
        return $this->find_by_query($query);
    }


}