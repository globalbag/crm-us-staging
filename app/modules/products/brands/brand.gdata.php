<?php


class nv_products_brands_brand_gdata extends nv_products_brands_brand
{


    function cmd_gdata()
    {

        $view_data = [];
        $js = [];

        $this->layout->selectTab('gdata');
        $this->layout->render($this->views->load_render('brand/gdata', $this->view_data_presets($view_data)) ,"Datos Generales",$js);

    }


}
