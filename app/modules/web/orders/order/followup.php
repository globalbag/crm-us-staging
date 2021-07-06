<?php


class web_orders_order_followup extends web_orders_order_shared implements \Novut\Layouts\Layout12\ContentInterface
{

    use \Novut\Layouts\Layout12\Module;

    function getContent(): \Novut\Layouts\Layout12\Content
    {
        $this->config_load();

        return $this->getContentCardInstance('followup', "Followup");
    }
}
(new web_orders_order_followup)->init();