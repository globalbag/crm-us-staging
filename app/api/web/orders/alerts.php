<?php

use Sparket\Orders\Alerts;
use Sparket\Orders\Order;

class api_module_web_orders_alerts_index extends api_module_web_orders_shared
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

        if(!$this->input["WOrderID"])
        {
            $this->error("Missing Field WOrderID","Unknown WOrderID.");
        }


        $this->order->find($this->input["WOrderID"]);

        if(!$this->order->getWOrderID())
        {
            $this->error("Missing Field WOrderID","WOrderID does not exist.");
        }

        if(!$this->input["AlertCode"])
        {
            $this->error("Missing Field AlertCode","Unknown AlertCode.");
        }

        $this->alert   = new \Sparket\Orders\Alerts($this->order);
        $validateAlert = array($this->alert, $this->input["AlertCode"]);

        if(!is_callable($validateAlert, false, $name))
        {
            $this->error("Unknown Alert Code ","Alert {$this->input['AlertCode']} does not exist.");
        }

        $alert_code = $this->input["AlertCode"];

        $recipients = $this->alert->recipients();

        if(!$this->order->getWOrderContactEmail())
        {
            $this->error("Order hasn't ContactEmail", "Order hasn't ContactEmail");
        }


        $recipients->addEmail($this->order->getWOrderContactEmail(), $this->order->getWOrderContactFirstName(), $this->order->getWOrderContactLastName());
        $this->alert->$alert_code($recipients);


        $data["sent"] = "Sent Alert";

        $this->response($data);

    }
}

(new api_module_web_orders_alerts_index())->init();