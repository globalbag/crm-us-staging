<?php


class web_orders_order_items extends web_orders_order_shared implements \Novut\Layouts\Layout12\ContentInterface
{
    use \Novut\Layouts\Layout12\Module;

    function getContent(): \Novut\Layouts\Layout12\Content
    {
        $this->config_load();
        $view_data = [];

        $view_data['items'] = \Sparket\Orders\Items::list($this->order, (new \Novut\Db\OrmOptionsGroup())->setExport());

        $content  =$this->getContentCardInstance('items', 'Products') ;
        $content->setBody($this->views->load_render('items.view.twig', $this->view_data_presets($view_data)));

        return $content;
    }
}
(new web_orders_order_items)->init();