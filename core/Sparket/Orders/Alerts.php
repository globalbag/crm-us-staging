<?php


namespace Sparket\Orders;


use Novut\CMS\Templates;
use Novut\Tools\Notifications\Notification;
use Novut\Tools\Notifications\Send\Send;
use Novut\Tools\Notifications\Recipients;
use Sparket\DB\Crm;

class Alerts
{

    /** @var Order $order */
    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    function purchase(Recipients $recipients = null)
    {
        $notification = $this->getInstance('orders.alerts.purchase', 'orders/alerts/enduser/purchase', $recipients);
        $notification->send();
    }

    function ontheway(Recipients $recipients = null)
    {
        $notification = $this->getInstance('orders.alerts.ontheway', 'orders/alerts/enduser/ontheway', $recipients);
        $notification->send();
    }

    function delivered(Recipients $recipients = null)
    {
        $notification = $this->getInstance('orders.alerts.delivered', 'orders/alerts/enduser/delivered', $recipients);
        $notification->send();
    }

    function cancelled(Recipients $recipients = null)
    {
        $notification = $this->getInstance('orders.alerts.cancelled', 'orders/alerts/enduser/cancelled', $recipients);
        $notification->send();
    }

    function warehouse_request(Recipients $recipients = null)
    {
        $notification = $this->getInstance('orders.alerts.warehouse.request', 'orders/alerts/operations/warehouse', $recipients);
        $notification->send();
    }

    protected function getInstance(string $code, string $message_path = null, Recipients $recipients = null): Send
    {
        $message = new \Novut\Tools\Notifications\Send\Message();
        $message->importNotification($code);

        if (!$message->isValid())
        {
            $message = new \Novut\Tools\Notifications\Send\MessageCustom();
            $message->importFromString(views()->load($message_path, false, false));
        }

        $message->setBodyTemplate(true);

        $items = \Sparket\Orders\Items::list($this->order, (new \Novut\Db\OrmOptionsGroup())->setExport());

        $send = new Send($message);
        $send->addParam('Order', $this->order->export());
        $send->addParam('Items', $items);


       $web_base_url = \BN_Var::$Config['Misc']['web']['url'];

       if(!$web_base_url)
       {
           \BN_Responses::dev("Web Url not found in app.yml Misc|web|url");
       }

       $send->addParam('Url', $web_base_url."my-account/orders/order?WOrderID=".$this->order->getWOrderID());

        if ($recipients)
        {
            $send->addRecipients($recipients);
        }

        return $send;

    }

    public function recipients(): Recipients
    {
        return new Recipients;
    }

}