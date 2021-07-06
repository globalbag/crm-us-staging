<?php

class web_orders_shared
{

    use \Novut\Controllers\Core;
    use \Sparket\Core\SparketCore;


    function config_common()
    {
        $this->db_web = $this->getWebDB();

        $this->ModuleUrlRoot = "web/orders/";
    }

}