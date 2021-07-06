<?php


namespace App\Products\Brands;


use Novut\Db\Orm;

class FamilyParam
{
    use Orm;

    protected $BFamilyID;
    protected $BFParamID;
    protected $BrandID;
    protected $BParamID;
    protected $RowOrder;


    function __construct($family, $BFParamID = null)
    {
        $this->setOptions(FamilyParams::getORMOptions());
        if ($family && $family instanceof Family)
        {
            $this->family = $family;
        }
        else
        {
            \BN_Responses::dev("Debes enviar la familia al crear una instancia de " .__CLASS__);
        }

        if ($BFParamID)
        {
            $this->find($BFParamID);
        }
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
    public function getBFParamID()
    {
        return $this->BFParamID;
    }

    /**
     * @return mixed
     */
    public function getRowOrder()
    {
        return $this->RowOrder;
    }

    function add($param)
    {
        if ($param && $param instanceof Param)
        {
            if (!$param->getBParamID())
            {
                \BN_Responses::dev("El parametro debe existir.");
            }
        }
        else
        {
            \BN_Responses::dev("El parametro debe ser una instancia de ".Param::class);
        }

        $this->BParamID = $param->getBParamID();
        $this->BrandID = $param->getBrandID();
        $this->BFamilyID = $this->family->getBFamilyID();
        $db = FamilyParams::getORMOptions()->getDb();
        $this->RowOrder = $db->getValue(FamilyParams::getORMOptions()->getTableName(), 'BFamilyID', $this->family->getBFamilyID(), 'RowOrder', " AND Cancelled = 0 Order by RowOrder DESC") + 1;

        $this->_add();
    }


    function update()
    {
        FamilyParams::update($this->family);
    }

    function cancel_after()
    {
        FamilyParams::update($this->family);
    }

}