<?php

class api_module_web_orders_deployment
{
    use \Novut\API\Core;

    function post_inventory()
    {
        $Order = new \Sparket\Orders\Order();
        $Order->find($this->input['WOrderID']);

        if ($Order->getWOrderID() && !$Order->getWOrderInventoryOrder())
        {
            $Order->actions()->deployInventory();
        }
        else if (!$Order->getWOrderID())
        {
            $this->error('WOrderID', 'Unkown order');
        }

        $this->response([]);

    }
}
(new api_module_web_orders_deployment)->init();