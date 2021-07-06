<?php


namespace App\Products\Product;


use Novut\Db\Orm;

class Image
{
    use Orm;

    protected $ProductID;
    protected $PImageID;
    protected $PImageDescription;
    protected $PImageDefault;
    protected $PImageUrl;
    protected $PImageBucket;
    protected $RowOrder;

    protected $_product;



    function __construct($product, $PImageID = null)
    {
        $this->setOptions(Images::getORMOptions());
        if ($product && $product instanceof Product)
        {
            $this->_product = $product;
        }
        else
        {
            \BN_Responses::dev("Debes enviar el producto al crear una instancia de " .__CLASS__);
        }

        if ($PImageID)
        {
            $this->find($PImageID);
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
    public function getPImageID()
    {
        return $this->PImageID;
    }

    /**
     * @return mixed
     */
    public function getPImageDescription()
    {
        return $this->PImageDescription;
    }

    /**
     * @return mixed
     */
    public function getPImageDefault()
    {
        return $this->PImageDefault;
    }

    /**
     * @return mixed
     */
    public function getPImageUrl()
    {
        return $this->PImageUrl;
    }

    /**
     * @return mixed
     */
    public function getPImageBucket()
    {
        return $this->PImageBucket;
    }

    /**
     * @return mixed
     */
    public function getRowOrder()
    {
        return $this->RowOrder;
    }

    /**
     * @param string $PImageDescription
     * @return Image
     */
    public function setPImageDescription(string $PImageDescription)
    {
        $this->PImageDescription = $PImageDescription;
        return $this;
    }

    /**
     * @param string $PImageUrl
     * @return Image
     */
    public function setPImageUrl(string $PImageUrl)
    {
        $this->PImageUrl = $PImageUrl;
        return $this;
    }

    /**
     * @param string $PImageBucket
     * @return Image
     */
    public function setPImageBucket(string $PImageBucket)
    {
        $this->PImageBucket = $PImageBucket;
        return $this;
    }


    /**
     * @param bool $PImageDefault
     * * @return Image
     */
    public function setPImageDefault(bool $PImageDefault)
    {
        $this->PImageDefault = $PImageDefault ? 1 : 0;
        return $this;
    }

    function add_before()
    {
        $this->ProductID = $this->_product->getProductID();
        $this->RowOrder = $this->db->getValue($this->_options->getTableName(), 'ProductID', $this->_product->getProductID(), 'RowOrder', " AND Cancelled = 0 Order by RowOrder DESC") + 1;
        $this->PImageDefault = $this->db->getValue($this->_options->getTableName(), 'ProductID', $this->_product->getProductID(), 'PImageDefault', " AND PImageDefault = 1 AND Cancelled = 0") ? 0 : 1;

    }

    function update()
    {
        Images::update($this->_product);
    }


    function cancel_after()
    {
        Images::update($this->_product);
    }

}