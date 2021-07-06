<?php

namespace App\Products\Brands;

use Novut\Core\Query;

class Param
{

    use \Novut\Db\Orm;

    /** @var Brand $_brand */
    protected $_brand;

    protected $BrandID;
    protected $BParamID;
    protected $BParamName;
    protected $BParamComment;
    protected $BParamCode;
    protected $BParamType;
    protected $BParamDefault;
    protected $RowOrder;
    protected $BParamData;
    protected $Cancelled;
    protected $CancelledInfo;


    function __construct(Brand $brand, $param_id = null)
    {
        $this->setOptions(Params::getORMOptions());

        $this->_brand = $brand;

        if ($param_id && is_numeric($param_id))
        {
            $this->find($param_id);
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
    public function getBParamID()
    {
        return $this->BParamID;
    }

    /**
     * @return mixed
     */
    public function getBParamName()
    {
        return $this->BParamName;
    }

    /**
     * @param mixed $BParamName
     * @return Param
     */
    public function setBParamName($BParamName)
    {
        $this->BParamName = $BParamName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBParamComment()
    {
        return $this->BParamComment;
    }

    /**
     * @param mixed $BParamName
     * @return Param
     */
    public function setBParamComment($BParamComment)
    {
        $this->BParamComment = $BParamComment;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBParamCode()
    {
        return $this->BParamCode;
    }

    /**
     * @param mixed $BParamCode
     * @return Param
     */
    public function setBParamCode($BParamCode)
    {
        $this->BParamCode = $BParamCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBParamType()
    {
        return $this->BParamType;
    }

    /**
     * @param $type
     * @return Param
     */
    public function setBParamType($type)
    {
        $this->BParamType = Params::getTypes()[$type] ? $type : 'select';
        return $this;
    }

    /**
     * @return Param
     */
    public function setBParamTypeSelect()
    {
        $this->BParamType = 'select';
        return $this;
    }

    /**
     * @return Param
     */
    public function setBParamTypeText()
    {
        $this->BParamType = 'text';
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBParamDefault()
    {
        return $this->BParamDefault;
    }

    /**
     * @param mixed $BParamDefault
     * @return Param
     */
    public function setBParamDefault($BParamDefault)
    {
        $this->BParamDefault = $BParamDefault ? true : false;
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
     * @return Param
     */
    public function setRowOrder($RowOrder)
    {
        $this->RowOrder = $RowOrder;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBParamData()
    {
        return $this->BParamData;
    }

    /**
     * @param mixed $BParamData
     * @return Param
     */
    public function setBParamData($BParamData)
    {
        $this->BParamData = $BParamData;
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
    }

    public function addBParamData(string $key, string $value)
    {
        $BParamData = json_decode($this->BParamData, true);

        if (!isset($BParamData[$key]))
        {
            $BParamData[$key] = $value;
        }

        $this->BParamData = json_encode($BParamData);

    }

    public function removeBParamData(string $key)
    {

        $BParamData = json_decode($this->BParamData, true);

        if (isset($BParamData[$key]))
        {
            unset($BParamData[$key]);
        }

        $this->BParamData = sizeof($BParamData) > 0 ? json_encode($BParamData) : "";
    }


    public function resetBParamData()
    {

        $this->BParamData = "";

    }


    static public function name_exist(Brand $brand, string $param_name, $BParamID = null)
    {

        $param = new Param($brand);
        $query = (new Query())
            ->setWhereField('BParamName')
            ->setWhereValue($param_name);


        if ($BParamID)
        {
            $query->addQuery(" AND BParamID != :BParamID");
            $query->addParam('BParamID', $BParamID);
        }

        $query->addQuery(" AND BrandID = :BrandID AND Cancelled != 1");
        $query->addParam('BrandID', $brand->getBrandID());

        $param->find_by_query($query);

        if ($param->getBrandID())
        {
            return $param;
        }
        else
        {
            return null;
        }

    }



}