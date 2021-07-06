<?php


namespace Sparket\Orders;


use Novut\Core\Caller;
use Novut\Core\Tools\Errors;
use Novut\Inventory\Orders\Type;
use Sparket\DB\Crm;
use Sparket\DB\Web;

class OrderActions
{
    use Errors;

    /** @var Order $order */
    protected $order;


    function __construct(Order $order)
    {
        $this->db = Crm::getDB();
        $this->db_web = Web::getDB();
        $this->order = $order;
    }

    function deployInventory()
    {
        if (!$this->order->getWOrderID())
        {
            return null;
        }
        $config = new \App\Inventory\Config();

        // TODO elegir inventario
        $IWID = $config->defaultWarehouse() ? : $this->db->getValue('inventory_warehouses', false, false, "IWID", " AND Cancelled = 0 Order by IWID");

        $deployDate = date('Y-m-d H:i:s');

        $caller = new Caller();
        $caller->setCallerId($this->order->getWOrderID());
        $caller->setCallerModule('web.orders');

        // create inventory order
        $i_order = new \Novut\Inventory\Orders\Order();
        $i_order->setIWID($IWID);
        $i_order->setCaller($caller);
        $i_order->setIOrderDate($deployDate);
        $i_order->setIOrderType(Type::out['value']);

        $i_order->add();


        if(!$i_order->getIOrderID())
        {
            $this->errorSet('sorders.shipping.iorder', "No fue posible agregar la orden. Intente nuevamente.");
            return false;
        }

        // Add items
        foreach ($this->getItems() as $item)
        {
            $i_order = (new \Novut\Inventory\Orders\Order($i_order));
            $i_order->item()->setProductID($item->getProductID());
            $i_order->item()->setIItemQty($item->getWOItemQty());
            $i_order->item()->add();
        }

        // update inventory status order
        $i_order->actions()->update_status_pending();

        $i_item_list = \Novut\Inventory\Orders\Items\Items::list($i_order);

        if(!$i_item_list)
        {
            $this->errorSet('sorders.shipping.iorder.items', "No es posible solicitar la orden no tiene productos agregados. No es posible continuar.");
            return false;
        }

        foreach ($i_item_list as $i_item)
        {

            foreach (\Novut\Inventory\Stock\Details\Details::list_unlock_date(\Novut\Inventory\Stock\Stocks::info_by_product($i_item->getProductID(), $i_order->getIWID()), $i_item->getIItemQty(), $deployDate) as $i_stock_detail)
            {
                $i_item_detail = (new \Novut\Inventory\Orders\Items\Details\Detail($i_item));
                $i_item_detail->setIIDetailDateExpiration($i_stock_detail->getISDetailDateExpiration() ? : "");
                $i_item_detail->setIIDetailPartNumber($i_stock_detail->getISDetailPartNumber() ? : "");
                $i_item_detail->setIIDetailBatch($i_stock_detail->getISDetailBatch() ? : "");
                $i_item_detail->setIIDetailSerialNumber($i_stock_detail->getISDetailSerialNumber() ? : "");
                $i_item_detail->setISDetailID($i_stock_detail->getISDetailID());

                /**
                 * set detail in item
                 */
                $i_item->setDetail($i_item_detail);
            }

            $i_order->event()->setItem($i_item);
        }

        $i_order->event()->setIEventDate($deployDate);

        $i_order->event()->setIEventDeliveryUserID(user()->getId());
        $i_order->event()->setIEventDeliveryFirstName(\BN_Var::$UserInfo['UserFirstName']);
        $i_order->event()->setIEventDeliveryLastName(\BN_Var::$UserInfo['UserLastName']);
        $i_order->event()->add();

        $i_order->actions()->update_status_finalized();

        $oder = new Order();
        $oder->setWOrderInventoryOrder($i_order->getIOrderID());
        $oder->save($this->order->getWOrderID());

    }

    /**
     * @return Item[]
     */
    function getItems(): array
    {
        return Items::list($this->order);
    }

    function deployInventoryRevert()
    {

    }

    function setAlert($alert_code)
    {
        $alert = new Alerts($this->order);
        $recipients = $alert->recipients();
        $recipients->addEmail($this->order->getWOrderContactEmail(), $this->order->getWOrderContactFirstName(), $this->order->getWOrderContactLastName());

        $validateAlert = array($alert, $alert_code);

        if(!is_callable($validateAlert, false, $name))
        {
            \BN_Responses::dev("Alert code [{$alert_code}] does not exist.");
        }

        $alert->$alert_code($recipients);
    }

}