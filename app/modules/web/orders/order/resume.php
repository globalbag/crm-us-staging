<?php


class web_orders_order_resume extends web_orders_order_shared implements \Novut\Layouts\Layout12\ContentInterface
{
    use \Novut\Layouts\Layout12\Module;

    function getContent(): \Novut\Layouts\Layout12\Content
    {
        $this->config_load();
        $view_data = [];

        $view_data['status_info'] = \Sparket\Orders\Status::getOptionInfo($this->order->getWOrderStatus());
        $view_data['status_list'] = \Sparket\Orders\Status::getOptionList();
        $view_data['WOrderCancelInfo'] = \Novut\Components\Coders\Json::decode($this->order->getWOrderCancelInfo());

        $content = $this->getContentCardInstance('resume', "Orden #{$this->order->getWOrderID()}");
        $content->setBody($this->views->load_render('resume.view.twig', $this->view_data_presets($view_data)));

        return $content;
    }

}
(new web_orders_order_customer)->init();