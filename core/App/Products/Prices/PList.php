<?php


namespace App\Products\Prices;


use App\Products\Product\Params;
use App\Products\Product\Product;
use Novut\Core\Query;
use Novut\Db\Orm;

class PList
{

    use Orm;

    protected $PListID;
    protected $PListName;
    protected $PListDefault;
    protected $Cancelled;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var bool
     */
    private $_PListDefault;

    /**
     * PList constructor.
     * @param int|mixed $PListID
     */
    function __construct($PListID = null)
    {
        $this->setOptions(PLists::getORMOptions());
        $this->db = Plists::getORMOptions()->getDb();

        if ($PListID && is_array($PListID))
        {
            $this->import($PListID);
        }
        else if ($PListID)
        {
            $this->find($PListID);
        }


    }

    /**
     * @param bool|null $create
     * @return PList
     */
    function loadDefault(bool $create = null)
    {
        $this->find_by_query((new Query())->addQuery(" AND PListDefault = 1 AND Cancelled = 0"));


        if (!$this->getPListID() && $create)
        {
            $total_price_lists = $this->db->Total(PLists::getORMOptions()->getTableName(),  false, false, " AND Cancelled = 0");

            if ($total_price_lists < 1)
            {
                $default_price_list = new PList();
                $default_price_list->setPListName("Default");
                $default_price_list->setPListDefault(true);
                $default_price_list->add();

                return $this->loadDefault();
            }
            else
            {
                $plist = $this->db->TableInfo(PLists::getORMOptions()->getTableName(),  false, false, " AND Cancelled = 0");
                $this->db->Update(PLists::getORMOptions()->getTableName(), ['PListDefault' => 1], 'PListID', $plist['PListID']);
                return $this->loadDefault();
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPListID()
    {
        return $this->PListID;
    }

    /**
     * @param mixed $PListID
     * @return PList
     */
    public function setPListID($PListID)
    {
        $this->PListID = $PListID;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPListName()
    {
        return $this->PListName;
    }

    /**
     * @param mixed $PListName
     * @return PList
     */
    public function setPListName($PListName)
    {
        $this->PListName = $PListName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function isPListDefault()
    {
        return $this->PListDefault ? 1 : 0;
    }

    /**
     * @param bool $PListDefault
     * @return PList
     */
    public function setPListDefault(bool $PListDefault = true)
    {
        $this->PListDefault = $PListDefault ? true : false;
        $this->_PListDefault = $this->PListDefault;
        return $this;
    }


    protected function add_after()
    {
        if ($this->_getCurrentID() && $this->_PListDefault)
        {
            $this->db->Update($this->_options->getTableName(), ['PListDefault' => 0], null, null, " AND PListID != :PListID", ['PListID' => $this->_getCurrentID()]);
        }
    }

    protected function save_after()
    {
        if ($this->getPListID() && $this->isPListDefault())
        {
            $this->db->Update($this->_options->getTableName(), ['PListDefault' => 0], null, null, " AND PListID != :PListID", ['PListID' => $this->getPListID()]);
        }
    }

    protected function cancel_after($id)
    {

        $default_exist = $this->db->TableInfo(Params::getORMOptions()->getTableName(), " AND PListDefault = 1 AND Cancelled = 0");
        if (!$default_exist)
        {
            $first_item = $this->db->TableInfo(Params::getORMOptions()->getTableName(), " AND Cancelled = 0");

            if ($first_item)
            {
                $this->db->Update($this->_options->getTableName(), ['PListDefault' => 1], 'PListID', $first_item['PListID']);
                $this->db->Update($this->_options->getTableName(), ['PListDefault' => 0], null, null, " AND PListID != :PListID", ['PListID' => $first_item['PListID']]);
            }

        }


    }


}