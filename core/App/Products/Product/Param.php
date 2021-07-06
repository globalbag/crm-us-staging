<?php


namespace App\Products\Product;


use Novut\Db\Orm;

class Param
{

    use Orm;

    /** @var Product $product */
    protected $product;

    protected $PParamID;
    protected $ProductID;
    protected $PParamKey;
    protected $BParamID;
    protected $BFamilyID;
    protected $PParamValue;

    function __construct(Product $product, $p_param_id = null)
    {
        $this->setOptions(Params::getORMOptions());
        $this->db = Params::getORMOptions()->getDb();

        $this->product = $product;

        if ($p_param_id)
        {
            $this->find($p_param_id);
        }

        if (!$this->product->getProductID())
        {
            \BN_Responses::dev("El producto no existe.");
        }


    }


    /**
     * @return int
     */
    public function getPParamID()
    {
        return (int) $this->PParamID;
    }

    /**
     * @param int $PParamID
     * @return Param
     */
    public function setPParamID($PParamID)
    {
        $this->PParamID = (int) $PParamID;
        return $this;
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
    public function getBFamilyID()
    {
        return $this->BFamilyID;
    }



    /**
     * @return int
     */
    public function getProductID()
    {
        return (int) $this->ProductID;
    }

    /**
     * @param int $ProductID
     * @return Param
     */
    public function setProductID($ProductID)
    {
        $this->ProductID = (int) $ProductID;
        return $this;
    }

    /**
     * @return string
     */
    public function getPParamKey()
    {
        return (string) $this->PParamKey;
    }

    /**
     * @param string $PParamKey
     * @return Param
     */
    public function setPParamKey(string $PParamKey)
    {
        $this->PParamKey = $PParamKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getPParamValue()
    {
        return (string) $this->PParamValue;
    }

    /**
     * @param string $PParamValue
     * @return Param
     */
    public function setPParamValue($PParamValue)
    {
        $this->PParamValue = (string) $PParamValue;
        return $this;
    }


    /**
     * @param $BParamID
     * @param string $PParamKey
     * @param string|mixed $PParamValue
     * @return mixed
     */
    function add($BParamID, string $PParamKey, string $PParamValue)
    {
        $data_insert['ProductID'] = $this->product->getProductID();
        $data_insert['BFamilyID'] = $this->product->getBFamilyID();
        $data_insert['BParamID'] = $BParamID;
        $data_insert['PParamKey'] = $PParamKey;
        $data_insert['PParamValue'] = (string) $PParamValue;

        $on_duplicate_keys['ProductID'] = $this->product->getProductID();
        $on_duplicate_keys['PParamKey'] = $PParamKey;
        $on_duplicate_keys['Cancelled'] = 0;

        $update_fields = ["BParamID", "BFamilyID", "PParamValue"];

        return $this->db->InsertOrUpdate(Params::getORMOptions()->getTableName(), $data_insert, 'PParamID', $on_duplicate_keys, $update_fields);

    }

    function save($id = null)
    {
        \BN_Responses::dev("Disabled method");
    }

    function cancel(string $PParamKey = null)
    {

        if ($PParamKey)
        {
            $this->db->Cancelled(Params::getORMOptions()->getTableName(), 'ProductID', $this->product->getProductID(), " AND PParamKey = :PParamKey", ['PParamKey' => $PParamKey], false);
        }
        else if ($this->PParamID)
        {
            $this->db->Cancelled(Params::getORMOptions()->getTableName(), 'ProductID', $this->product->getProductID(), " AND PParamID = :PParamID", ['PParamID' => $this->PParamID], false);
        }


    }


}