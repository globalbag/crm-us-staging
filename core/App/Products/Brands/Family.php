<?php

namespace App\Products\Brands;

use Novut\Core\Query;

class Family
{

    use \Novut\Db\Orm;

    /** @var Brand $_brand */
    protected $_brand;

    protected $BrandID;
    protected $BFamilyID;
    protected $BFamilyName;
    protected $BFamilyCode;
    protected $BFamilyPublished;
    protected $BFamilyQtyRange;
    protected $BFamilyContent;
    protected $BFamilySContent;
    protected $BFamilyParams;
    protected $BFamilyImages;
    protected $RowOrder;
    protected $Cancelled;
    protected $CancelledInfo;


    function __construct(Brand $brand, $family_id = null)
    {
        $this->setOptions(Families::getORMOptions());

        $this->_brand = $brand;

        if ($family_id && is_numeric($family_id))
        {
            $this->find($family_id);
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
    public function getBFamilyID()
    {
        return $this->BFamilyID;
    }

    /**
     * @return mixed
     */
    public function getBFamilyName()
    {
        return $this->BFamilyName;
    }

    /**
     * @param mixed $BFamilyName
     * @return Family
     */
    public function setBFamilyName($BFamilyName)
    {
        $this->BFamilyName = $BFamilyName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBFamilyCode()
    {
        return $this->BFamilyCode;
    }

    /**
     * @param mixed $BFamilyCode
     */
    public function setBFamilyCode($BFamilyCode): void
    {
        $this->BFamilyCode = $BFamilyCode;
    }



    /**
     * @return mixed
     */
    public function getBFamilyImages()
    {
        return $this->BFamilyImages;
    }

    /**
     * @return mixed
     */
    public function getBFamilyContent()
    {
        return $this->BFamilyContent;
    }

    /**
     * @param mixed $BFamilyContent
     * @return Family
     */
    public function setBFamilyContent($BFamilyContent)
    {
        $this->BFamilyContent = $BFamilyContent;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBFamilySContent()
    {
        return $this->BFamilySContent;
    }

    /**
     * @param mixed $BFamilySContent
     * @return Family
     */
    public function setBFamilySContent($BFamilySContent)
    {
        $this->BFamilySContent = $BFamilySContent;
        return $this;
    }




    /**
     * @return mixed
     */
    public function getBFamilyParams()
    {
        return $this->BFamilyParams;
    }




    /**
     * @return mixed
     */
    public function getBFamilyQtyRange()
    {
        return $this->BFamilyQtyRange ? true : false;
    }

    /**
     * @param bool $BFamilyQtyRange
     * @return Family
     */
    public function setBFamilyQtyRange(bool $BFamilyQtyRange)
    {
        $this->BFamilyQtyRange = $BFamilyQtyRange ? 1 : 0;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBFamilyPublished()
    {
        return $this->BFamilyPublished ? true : false;
    }

    /**
     * @param bool $BFamilyPublished
     * @return Family
     */
    public function setBFamilyPublished(bool $BFamilyPublished)
    {
        $this->BFamilyPublished = $BFamilyPublished ? 1 : 0;
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
     * @param mixed $RowOrder
     * @return Family
     */
    public function setRowOrder($RowOrder)
    {
        $this->RowOrder = $RowOrder ? (int) $RowOrder : 0;
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
    public function getCancelledInfo()
    {
        return $this->CancelledInfo;
    }



    function add_before()
    {

        $this->BrandID = $this->_brand->getBrandID();
        $this->RowOrder = $this->RowOrder ? : $this->db->getValue($this->_options->getTableName(), 'BrandID', $this->_brand->getBrandID(), 'RowOrder', " AND Cancelled = 0 Order by RowOrder DESC") + 1;

    }

    private function _get_param($param)
    {
        $_param = new Param($this->_brand);

        if ($param && $param instanceof Param)
        {
            $_param = $param;
        }
        else if ($param && is_numeric($param))
        {
            $_param->find($param);
        }

        return $_param;
    }

    /**
     * @param int|Param $param
     */
    public function addBFamilyParam($param)
    {
        $param = $this->_get_param($param);
        $BParamID = $param->getBParamID();

        if ($BParamID)
        {
            $BFamilyParams = json_decode($this->BFamilyParams, true);
            $BFamilyParams = $BFamilyParams ? : [];

            if (!$BFamilyParams[$BParamID])
            {
                $BFamilyParams[$BParamID] = ['id' => $param->getBParamID(), 'name' => $param->getBParamName(), 'code' => $param->getBParamCode()];
            }

            $this->BFamilyParams = json_encode($BFamilyParams);
        }


    }

    /**
     * @param int|Param $param
     */
    public function removeBFamilyParam($param)
    {

        if ($param && is_numeric($param))
        {
            $BParamID = $param;
        }
        else
        {
            $param = $this->_get_param($param);
            $BParamID = $param->getBParamID();
        }



        if ($BParamID)
        {
            $BFamilyParams = json_decode($this->BFamilyParams, true);
            $BFamilyParams_new = [];

            foreach ($BFamilyParams ? : [] as $key => $value)
            {
                if ($value != $BParamID)
                {
                    $BFamilyParams_new[] = $value;
                }
            }

            $this->BFamilyParams = $BFamilyParams_new ? json_encode($BFamilyParams_new) : "";
        }


    }


    public function updateParams()
    {

    }

    static public function name_exist(Brand $brand, string $family_name, $BFamilyID = null)
    {

        $family = new Family($brand);
        $query = (new Query())
            ->setWhereField('BFamilyName')
            ->setWhereValue($family_name);


        if ($BFamilyID)
        {
            $query->addQuery(" AND BFamilyID != :BFamilyID");
            $query->addParam('BFamilyID', $BFamilyID);
        }

        $query->addQuery(" AND BrandID = :BrandID AND Cancelled != 1");
        $query->addParam('BrandID', $brand->getBrandID());

        $family->find_by_query($query);

        if ($family->getBrandID())
        {
            return $family;
        }
        else
        {
            return null;
        }

    }


    function cancel_before($id)
    {
        if ($this->getBFamilyID())
        {
            // cancel params
            foreach (\App\Products\Brands\FamilyParams::list($this) as $param)
            {
                $param->cancel();
            }
            // cancel images
            foreach (\App\Products\Brands\FamilyImages::list($this) as $image)
            {
                $image->cancel();
            }
        }

    }



}