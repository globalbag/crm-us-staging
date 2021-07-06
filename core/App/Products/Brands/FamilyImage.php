<?php


namespace App\Products\Brands;


use Novut\Db\Orm;

class FamilyImage
{
    use Orm;

    protected $BFamilyID;
    protected $BrandID;
    protected $BFImageID;
    protected $BFImageDescription;
    protected $BFImageDefault;
    protected $BFImageUrl;
    protected $BFImageBucket;
    protected $RowOrder;

    /** @var Family $_family */
    protected $_family;



    function __construct($family, $BFImageID = null)
    {
        $this->setOptions(FamilyImages::getORMOptions());
        if ($family && $family instanceof Family)
        {
            $this->_family = $family;
        }
        else
        {
            \BN_Responses::dev("Debes enviar la familia al crear una instancia de " .__CLASS__);
        }

        if ($BFImageID)
        {
            $this->find($BFImageID);
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
    public function getBFImageID()
    {
        return $this->BFImageID;
    }

    /**
     * @return mixed
     */
    public function getBFImageDescription()
    {
        return $this->BFImageDescription;
    }

    /**
     * @return mixed
     */
    public function getBFImageDefault()
    {
        return $this->BFImageDefault;
    }

    /**
     * @return mixed
     */
    public function getBFImageUrl()
    {
        return $this->BFImageUrl;
    }

    /**
     * @return mixed
     */
    public function getBFImageBucket()
    {
        return $this->BFImageBucket;
    }

    /**
     * @return mixed
     */
    public function getRowOrder()
    {
        return $this->RowOrder;
    }

    /**
     * @param string $BFImageDescription
     * @return FamilyImage
     */
    public function setBFImageDescription(string $BFImageDescription)
    {
        $this->BFImageDescription = $BFImageDescription;
        return $this;
    }

    /**
     * @param string $BFImageUrl
     * @return FamilyImage
     */
    public function setBFImageUrl(string $BFImageUrl)
    {
        $this->BFImageUrl = $BFImageUrl;
        return $this;
    }

    /**
     * @param string $BFImageBucket
     * @return FamilyImage
     */
    public function setBFImageBucket(string $BFImageBucket)
    {
        $this->BFImageBucket = $BFImageBucket;
        return $this;
    }


    /**
     * @param bool $BFImageDefault
     * * @return FamilyImage
     */
    public function setBFImageDefault(bool $BFImageDefault)
    {
        $this->BFImageDefault = $BFImageDefault ? 1 : 0;
        return $this;
    }

    function add_before()
    {
        $db  = FamilyImages::getORMOptions()->getDb();
        $this->RowOrder = $db->getValue(FamilyImages::getORMOptions()->getTableName(), 'BFamilyID', $this->_family->getBFamilyID(), 'RowOrder', " AND Cancelled = 0 Order by RowOrder DESC") + 1;
        $this->BFImageDefault = $db->getValue(FamilyImages::getORMOptions()->getTableName(), 'BFamilyID', $this->_family->getBFamilyID(), 'BFImageDefault', " AND BFImageDefault = 1 AND Cancelled = 0") ? 0 : 1;
        $this->BFamilyID = $this->_family->getBFamilyID();
        $this->BrandID = $this->_family->getBrandID();
    }

    function update()
    {
        FamilyImages::update($this->_family);
    }


    function cancel_after()
    {
        FamilyImages::update($this->_family);
    }

}