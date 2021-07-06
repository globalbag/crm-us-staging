<?php

use Sparket\Orders\Alerts;
use Sparket\Orders\Order;

class api_module_web_orders_stock extends api_module_web_orders_shared
{

    /** @var Alerts $alert */
    protected $alert;

    /** @var Order $order */
    protected $order;

    function config_custom()
    {
        $this->order = new Order();
    }


    function post_index()
    {
        $this->setRequired('ProductID', 'Unknown Product');
        $this->setRequired('WUserID', 'Unknown WUser');

        $this->error_display();

        $alert = new \App\Inventory\Stock\Alert($this->input['ProductID']);

        $alert->add($this->input['WUserID'], (string) $this->input['IP']);
        $this->success();

    }

    function get_index()
    {
        $this->setRequired('WUserID', 'Unknown WUser');
        $this->error_display();

        $alerts = new \App\Inventory\Stock\Alert(0);
        $this->response(['products' => $alerts->userSubscriptions($this->input['WUserID'])]);
    }

    function post_delete()
    {
        $this->setRequired('ProductID', 'Unknown Product');
        $this->setRequired('WUserID', 'Unknown WUser');
        $alerts = new \App\Inventory\Stock\Alert($this->input['ProductID']);
        $alerts->delete($this->input['WUserID']);
        $this->response();
    }
}

(new api_module_web_orders_stock())->init();