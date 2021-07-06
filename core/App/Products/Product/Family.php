<?php


namespace App\Products\Product;


use App\Products\Brands\Brand;
use Novut\Db\Orm;

class Family
{

    use Orm;

    protected $ProductID;
    protected $BFamilyID;
    protected $BFamilyData;
    protected $_data;


    /** @var Product|int $product */
    protected $product;
    /**
     * Family constructor.
     * @param Product|int $product
     */
    function __construct(Product $product)
    {

        $this->setOptions(Products::getORMOptions());
        $this->product = $product;

        if (!$this->product->getProductID())
        {
            \BN_Responses::dev("El producto no existe. [{$product}]");
        }

        $this->import($product->export_raw());
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
    public function getBFamilyID()
    {
        return $this->BFamilyID;
    }

    /**
     * @param mixed $BFamilyID
     * @return Family
     */
    public function setBFamilyID($BFamilyID)
    {
        $this->BFamilyID = $BFamilyID;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBFamilyData()
    {
        return $this->BFamilyData;
    }

    /**
     * @param $BParamID
     * @param string $BParamCode
     * @param $value
     */
    function addParam($BParamID, string $BParamCode, $value)
    {
        $this->_data[$BParamID]['BParamID'] = $BParamID;
        $this->_data[$BParamID]['BParamCode'] = $BParamCode;
        $this->_data[$BParamID]['Value'] = $value;
    }

    protected function save_before()
    {

        $valid_items = [];
        $this->BFamilyData = [];
        $data = [];

        foreach ($this->_data as $param_info)
        {
            $valid_items[] = $param_info['BParamID'];
            (new Param($this->product))->add($param_info['BParamID'], $param_info['BParamCode'], $param_info['Value']);


            $data[$param_info['BParamID']]['id'] = $param_info['BParamID'];
            $data[$param_info['BParamID']]['code'] = $param_info['BParamCode'];
            $data[$param_info['BParamID']]['value'] = $param_info['Value'];
        }

        Params::purge($this->product, $valid_items);


        $this->BFamilyData = $data ? \BN_Coders::json_encode($data) : "";

    }

    function add($id = null)
    {
        \BN_Responses::dev("Disabled method.");
    }

    function cancel($id = null)
    {
        \BN_Responses::dev("Disabled method.");
    }




}