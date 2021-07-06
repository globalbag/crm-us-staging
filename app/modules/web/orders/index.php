<?php

class web_orders extends web_orders_shared
{
    use \Novut\Core\Controller\Filters;

    function config_custom()
    {
        $this->FormID = "WOrders";
        $this->endpoint->addParam('tab', $this->input['tab']);
        $this->config_filters_default();
    }


    function config_filters_default()
    {

        $filter = new \Novut\Tools\Filters\Filters('default');
        $filter->setEndpoint($this->endpoint);
        $filter->setNavBarID($this->FormID);
        $filter->addText('WOrderIDLink', 'ID')->setDbFieldId('WOrderID');
        $filter->addDate('WOrderDate', 'Fecha');
        $filter->addText('WOrderContactFullName', 'Nombre')->setMatchLike();
        $filter->addText('WOrderContactEmail', 'Email')->setMatchLike();
        $filter->addText('WOrderContactLegalName', 'Compa&ntilde;&iacute;a')->setMatchLike();
        $filter->addSelectMethod('WOrderStatus', 'Status', function() {

            $list = [];
            foreach (\Sparket\Orders\Status::getOptionList() as $data)
            {
                $list[$data['value']] = $data['name'];
            }
            return $list;
        });

        $this->tools_filters_register($filter);

    }

    function cmd_index()
    {
        $this->layout->setLayoutNavBarRight($this->layout->option()->setLabel('Crear Orden')->setOnclick(BN_JSHelpers::CMDRoute($this->ModuleUrl, ['cmd' => 'new'])));


        $view_data = [];
        $js = [];

        list($TabQuery, $TabID) = $this->index_tabs(true);
        $Query = new \Novut\Core\Query();
        $Query->setQuery(" AND Cancelled = 0");
        $Query->append($TabQuery);

        $this->tools_filters_get()->setEndpoint($this->endpoint);
        $filter_client = new \Novut\Tools\Filters\Engine\Client($this->tools_filters_get()->setEndpoint($this->endpoint));
        $this->layout->setLayoutFilters($filter_client->getToolbar());


        $table = new \Novut\Tools\Tables\Table($this->FormID);
        $table->setFilter($this->tools_filters_get());
        $table->setEndpoint($this->endpoint);

        $table->addField('WOrderIDLink', 'ID')->setFilter()->setSort();
        $table->addField('WOrderDate', 'Fecha')->setFilter()->setSort();;
        $table->addField('WOrderContactFullName', 'Nombre')->setFilter()->setSort();;
        $table->addField('WOrderContactLegalName', 'Compa&ntilde;&iacute;a')->setFilter()->setSort();;
        $table->addField('WOrderTotalAmount', 'Monto');
        $table->addField('WOrderTotalTotal', 'Total');


        if ($TabID == 'all')
        {
            $table->addField('WOrderStatus', 'Status')->setFilter()->setSort();;
        }


        $table->sort()->setDefaultSort('WOrderDate', 'DESC');

        $table->navbar()->setTableId('web_orders_orders');
        $table->navbar()->setEndpoint($this->endpoint);
        $table->navbar()->setQuery($Query);
        $table->navbar()->setTake(20);
        $table->navbar()->setFilter($table->filter());
        $table->navbar()->setDB($this->db_web);

        //
        $Query->append($filter_client->getQuery());

        foreach ($this->db_web->GroupInfo('web_orders_orders', false, false, "{$Query->getQuery()} {$table->getQuery()}", $Query->getParams(), 'WOrderID') as $data)
        {
            $data['WOrderIDLink'] = "<strong><a href='{$this->endpoint->makeUrl($this->ModuleUrl."order/", ['WOrderID' => $data['WOrderID']])}'><i class=\"fa fa-file\"></i>&nbsp;{$data['WOrderID']}</a></strong>";

            $data['WOrderContactFullName'] = "<div>{$data['WOrderContactFullName']}</div><div><em>{$data['WOrderContactEmail']}</em></div>";
            $data['WOrderDate'] = \BN_Date::sql_format_from($data['WOrderDate']);
            $data['WOrderStatus'] = \Sparket\Orders\Status::getOptionName($data['WOrderStatus'], true);


            $data["WOrderTotalTotal"]  = " $ {$data["WOrderTotalTotal"]}";
            $data["WOrderTotalAmount"] = " $ {$data["WOrderTotalAmount"]}";

            $table->addRow($data);
        }

        $view_data['table'] = $table->getTable();

        $this->layout->setLayoutOptions(
            [
                $this->layout->option_list()->setOptions([
                    $this->layout->option()->setLabel('Configuraci&oacute;n de la Tienda')->setOnclick(BN_JSHelpers::CMDRoute('admin/config/custom/', ['cmd' => 'shop_shop'])),
                ])->setIcon('fa-cog')
            ]
        );

        $this->layout->addLayoutBreadcrumbs("Sitio Web");
        $this->layout->addLayoutBreadcrumbs("&Oacute;rdenes");

        $this->layout->render($this->views->load_render('index', $this->view_data_presets($view_data)), "&Oacute;rdenes", $js);

    }

    function cmd_new()
    {
        $this->FormID .= "New";

        // get cards

        $this->JS[] = BN_Forms::setValueHtml('CardID', BN::OptionListEmpty().$this->db->OptionList('cards', 'CardID', 'CardName', ' Cancelled = 0', false, 'CardName', 'html'), $this->FormID);
        $this->JS[] = BN_Forms::setSelect2('CardID', $this->FormID);

        $this->JS[] = BN_Forms::setValueHtml('ProductID[]', BN::OptionListEmpty().$this->db->OptionList('inv_products_products', 'ProductID', 'ProductName', ' Cancelled = 0', false, 'ProductName', 'html'), $this->FormID);
        $this->JS[] = BN_Forms::setSelect2('ProductID[]', $this->FormID);

        $view_data["cmd"] = "new_add";

        responses()->modal(views()->load_render("form.twig", $this->view_data_presets($view_data)), "Crear Orden", $this->JS)->render();

    }

    function cmd_new_add()
    {
        $this->FormID .= "New";

        $ContactID = isset($this->input["ContactID"]) ? $this->input["ContactID"] : 10;
        $CardID    = isset($this->input["CardID"]) ? $this->input["CardID"] : 0;
        $Products = isset($this->input["ProductID"]) ? $this->input["ProductID"] : [];

        if($ContactID)
        {
            $Contact = new \Novut\Cards\Contact();
            $Contact->find($ContactID);
        }



    }

    function cmd_get_contacts()
    {
        $CardID = $this->input["CardID"];

        $contacts = $this->db->GroupInfo("cards_contacts", "CardID", $CardID);



        if($contacts)
        {
            $this->JS[] = BN_Forms::setValueHtml('ContactID', BN::OptionListEmpty().$this->db->OptionList('cards_contacts', 'ContactID', 'ContactFullName', ' CardID = '.$CardID, false, 'ContactFullName', 'html'), $this->FormID);
            $this->JS[] = BN_Forms::setSelect2('ContactID', $this->FormID);
        }
        else
        {
            $this->JS[] = BN_Responses::alert("No hay contactos para este cliente.", "error");
        }


        responses()->js($this->JS);

    }

    function index_tabs($render_layout = true): array
    {
        $Query = new \Novut\Core\Query();

        $tab_items['pending'] = [
            'id' => 'pending',
            'label' => 'Pendientes'
        ];

        $tab_items['payment.pending'] = [
            'id' => 'payment.pending',
            'label' => 'Aprobaci&oacute;n de Pago Pendiente'
        ];

        $tab_items['done'] = [
            'id' => 'done',
            'label' => 'Finalizadas'
        ];

        $tab_items['cancelled'] = [
            'id' => 'cancelled',
            'label' => 'Canceladas'
        ];

        $tab_items['all'] = [
            'id' => 'all',
            'label' => 'Todas'
        ];

        $current_tab = $this->input['tab'] && isset($tab_items[$this->input['tab']]) ? $this->input['tab'] : "pending";
        $this->input['tab'] = $current_tab;

        if ($render_layout === true)
        {
            foreach ($tab_items as $tab_info)
            {
                $endpoint = $this->endpoint;
                $endpoint->addParam('tab', $tab_info['id']);
                $this->layout->setLayoutTab($this->layout->tab($tab_info['id'], $tab_info['label'])->setUrl($endpoint->getUrlFull()));
            }

            $this->layout->setLayoutTabActive($current_tab);
        }

        if ($current_tab == 'pending')
        {
            $Query->addQuery(" AND WOrderStatus != :WOrderStatus", 'WOrderStatus', Sparket\Orders\Status::success['value']);
            $Query->addQuery(" AND WOrderPaymentPending = 0 ");
        }
        else if ($current_tab == 'payment.pending')
        {
            $Query->addQuery(" AND WOrderStatus = :WOrderStatus", 'WOrderStatus', Sparket\Orders\Status::pending['value']);
            $Query->addQuery(" AND WOrderPaymentPending = 1");
        }
        else if ($current_tab == 'done')
        {
            $Query->addQuery(" AND WOrderStatus = :WOrderStatus", 'WOrderStatus', Sparket\Orders\Status::success['value']);
        }
        else if ($current_tab == 'cancelled')
        {
            $Query->addQuery(" AND WOrderStatus = :WOrderStatus", 'WOrderStatus', Sparket\Orders\Status::cancelled['value']);
        }

        $this->endpoint->addParam('tab', $current_tab);

        return [$Query, $current_tab];
    }

}
(new web_orders)->init();