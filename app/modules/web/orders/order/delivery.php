<?php


class web_orders_order_delivery extends web_orders_order_shared implements \Novut\Layouts\Layout12\ContentInterface
{
    use \Novut\Layouts\Layout12\Module;

    /** @var Sparket\Orders\Delivery\Delivery $delivery */
    private $delivery;

    function config_custom()
    {
        $this->FormID.= "Delivery";
    }

    function getContent(): \Novut\Layouts\Layout12\Content
    {
        $this->config_load();

        $view_data = [];
        $view_data['delivery_list'] = \Sparket\Orders\Delivery\Deliveries::list($this->order);
        $view_data['status_list'] = \Sparket\Orders\Delivery\Status::getOptionList();
        $view_data['order_status_list'] = \Sparket\Orders\Status::getOptionList();

        $content = $this->getContentCardInstance('delivery', "Shipping");
        $content->setBody($this->views->load_render('delivery.view.twig', $this->view_data_presets($view_data)));


        return $content;
    }

    protected function cmd_new()
    {
        $this->FormID .= "New";

        if($this->order->getWOrderStatus() == \Sparket\Orders\Status::success['value'] || $this->order->getWOrderStatus() == \Sparket\Orders\Status::cancelled['value'])
        {
            BN_Responses::notification_error(_("La orden no puede ser actualizada se encuentra en un status que no es válido para aplicar el cambio.No es posible continuar."));
        }

        foreach (Sparket\Orders\Items::list($this->order) as $item)
        {
            if($item->getWOItemDeliveryStatus() == 2)
            {
                continue;
            }

            $item_list[$item->getWOItemID()] = $item;
        }

        if(!$item_list)
        {
            BN_Responses::notification_error(_("Todos los productos se encuentran asociados a un envío. No es posible continuar."));
        }

        $view_data['item_list'] = $item_list;

        $view_data['ShippingCompanies'] = BN::OptionListHTML('ShippingCompanies');

        $js[] = BN_Forms::setSelect2('WODeliveryCompanyID', $this->FormID);

        responses()->modal(views()->load_render("delivery.form.twig", $this->view_data_presets($view_data)), _("Agregar Envio"), $js)->render();
    }

    protected function cmd_new_add()
    {
        $this->FormID .= "New";

        if($this->order->getWOrderStatus() == \Sparket\Orders\Status::success['value'] || $this->order->getWOrderStatus() == \Sparket\Orders\Status::cancelled['value'])
        {
            BN_Responses::notification_error(_("La orden no puede ser actualizada se encuentra en un status que no es válido para aplicar el cambio.No es posible continuar."));
        }

        $this->input['WODeliveryDate'] = BN_Date::format($this->input['WODeliveryDate'], 'Y-m-d', 'd/m/Y');

        $validation = BN_Forms::validation($this->FormID, $this->input);

        if(!$this->input['WODeliveryDate'])
        {
            $validation->setError('WODeliveryDate', _("La fecha no es válida."));
        }

        $ShippingCompanies = BN::OptionListIndex('ShippingCompanies');

        if(!$ShippingCompanies[$this->input['WODeliveryCompanyID']])
        {
            $validation->setError('WODeliveryCompanyID', _("La paquetería no es válida."));
        }

        $validation->setRequiredField('WODeliveryDate');

        $validation->setRequiredField('WODeliveryCompanyID');

        $validation->setRequiredField('WODeliveryTCode');

        if(!$this->input['WOItem'])
        {
            BN_Responses::notification_error(_("Todos los productos se encuentran asociados a un envío. No es posible continuar."));
        }
        else
        {
            foreach ($this->input['WOItem'] as $WOItemID => $Qty)
            {
                if(!$Qty)
                {
                    continue;
                }

                $item = (new \Sparket\Orders\Item($this->order))->find($WOItemID);

                if(($item->getWOItemQty() - $item->getWOItemDeliveryItems()) < $Qty)
                {
                    $validation->setError("WOItem[{$WOItemID}]Product", _("El producto no cuenta con unidades suficientes (Unidades disponibles: ") . ($item->getWOItemQty() - $item->getWOItemDeliveryItems()) .").");
                }

                $item_list[$item->getWOItemID()] = $item;
                $item_list_qty[$item->getWOItemID()] = $Qty;
            }
        }

        if(!$item_list_qty)
        {
            $validation->setError("WOItemAlert", _("No se ha asociado ningun producto al envío."));
        }

        $validation->validate($this->input);

        $this->input['WODeliveryCompany'] = $ShippingCompanies[$this->input['WODeliveryCompanyID']]['OptionName'];

        $delivery = new Sparket\Orders\Delivery\Delivery($this->order);
        $delivery->setWODeliveryDate($this->input['WODeliveryDate']);
        $delivery->setWODeliveryCompanyID($this->input['WODeliveryCompanyID']);
        $delivery->setWODeliveryCompany($this->input['WODeliveryCompany']);
        $delivery->setWODeliveryTCode($this->input['WODeliveryTCode']);

        foreach ($item_list as $WOItemID => $item)
        {
            $delivery->setWOItem($item, $item_list_qty[$WOItemID]);
        }

        $delivery->add();

        BN_Responses::notification_success(_("Registro Agregado"), "reload");
    }

    protected function cmd_success()
    {
        if($this->order->getWOrderStatus() == \Sparket\Orders\Status::success['value'] || $this->order->getWOrderStatus() == \Sparket\Orders\Status::cancelled['value'])
        {
            BN_Responses::notification_error(_("La orden no puede ser actualizada se encuentra en un status que no es válido para aplicar el cambio.No es posible continuar."));
        }

        $this->getWODeliveryInfo();

        $this->validate_status_payment(_("La orden no ha sido pagada no es posible finalizar el envío."));

        if($this->order->getWOrderStatus() != \Sparket\Orders\Status::success['value'])
        {
            if($this->delivery->getWODeliveryStatus() != \Sparket\Orders\Delivery\Status::pending['value'])
            {
                BN_Responses::notification_error(_("El envío no puede ser finalizado. No es posible continuar."));
            }
        }

        if(!$this->input['success'])
        {
            BN_Responses::confirm_quick(_("¿Estás seguro de finalizar el envío?"), "success");
        }

        (new \Sparket\Orders\Actions())->success_delivery($this->order, $this->delivery);

        BN_Responses::notification_success(_("Envío Finalizado"), "reload");
    }

    protected function cmd_reject()
    {
        if($this->order->getWOrderStatus() == \Sparket\Orders\Status::success['value'] || $this->order->getWOrderStatus() == \Sparket\Orders\Status::cancelled['value'])
        {
            BN_Responses::notification_error(_("La orden no puede ser actualizada se encuentra en un status que no es válido para aplicar el cambio.No es posible continuar."));
        }

        $this->FormID = "Reject";

        $this->getWODeliveryInfo();

        if($this->order->getWOrderStatus() != \Sparket\Orders\Status::success['value'])
        {
            if($this->delivery->getWODeliveryStatus() == \Sparket\Orders\Delivery\Status::reject['value'] )
            {
                BN_Responses::notification_error(_("El envío no puede ser cancelado. No es posible continuar."));
            }
        }

        $view_data['WODeliveryID'] = $this->delivery->getWODeliveryID();

        $content = $this->views->load_render('delivery.form.cancel', $this->view_data_presets($view_data));

        BN_Responses::modal($content, _("Cancelar Envío"));
    }

    protected function cmd_reject_save()
    {
        if($this->order->getWOrderStatus() == \Sparket\Orders\Status::success['value'] || $this->order->getWOrderStatus() == \Sparket\Orders\Status::cancelled['value'])
        {
            BN_Responses::notification_error(_("La orden no puede ser actualizada se encuentra en un status que no es válido para aplicar el cambio.No es posible continuar."));
        }

        $this->FormID = "Reject";

        $this->getWODeliveryInfo();

        if($this->order->getWOrderStatus() != \Sparket\Orders\Status::success['value'])
        {
            if($this->delivery->getWODeliveryStatus() == \Sparket\Orders\Delivery\Status::reject['value'] )
            {
                BN_Responses::notification_error(_("El envío no puede ser cancelado. No es posible continuar."));
            }
        }

        $validation = BN_Forms::validation($this->FormID, $this->input);

        $validation->setRequiredField('CancelComment');

        $validation->validate();

        (new \Sparket\Orders\Actions())->reject_delivery($this->order, $this->delivery, $this->input['CancelComment']);

        BN_Responses::notification_success(_("Envío Cancelado"), "reload");
    }

    protected function cmd_update()
    {
        $view_data = [];
        $this->FormID.= "Update";

        if(!$this->input['WODeliveryID'])
        {
            BN_Responses::notification(_("El envío no existe. No es posible continuar."), "error");
        }

        $this->delivery = new \Sparket\Orders\Delivery\Delivery($this->order);
        $this->delivery->find($this->input['WODeliveryID']);

        $view_data["WOrderID"]     = $this->order->getWOrderID();
        $view_data["WODeliveryID"] = $this->delivery->getWODeliveryID();
        $view_data["FormID"]       = $this->FormID;
        $view_data["cmd"]          = "update_save";
        $status_list               = \Sparket\Orders\Delivery\Status::getOptionListHTMLEn();

        $JS[] = BN_Forms::setValueHtml('WODeliveryStatus', BN::OptionListEmpty().$status_list, $this->FormID);
        $JS[] = BN_Forms::setValueSelect('WODeliveryStatus', $this->delivery->getWODeliveryStatus(), $this->FormID);
        $JS[] = BN_Forms::setSelect2('WODeliveryStatus', $this->FormID);

        $JS[] = BN_Forms::setValueText('WODeliveryTCode', $this->delivery->getWODeliveryTCode(), $this->FormID);

        $content = $this->views->load_render('delivery.update', $this->view_data_presets($view_data));

        BN_Responses::modal($content, _("Detalles del Envío"), $JS);
    }

    protected function cmd_update_save()
    {
        if(!$this->input['WODeliveryID'])
        {
            BN_Responses::notification(_("El envío no existe. No es posible continuar."), "error");
        }

        $Validation["WODeliveryStatus"]  = "required";
        $Validation["WODeliveryTCode"]   = "required";

        BN_Validation::Wizard($this->FormID, $this->input, $Validation, []);


        $this->delivery = new \Sparket\Orders\Delivery\Delivery($this->order);
        $this->delivery->find($this->input['WODeliveryID']);


        if($this->input["WODeliveryTCode"] != $this->delivery->getWODeliveryTCode())
        {
            $this->order->actions()->setAlert("ontheway");
        }

        if($this->input["WODeliveryStatus"] == \Sparket\Orders\Delivery\Status::success["value"])
        {
            $this->order->actions()->setAlert("delivered");
        }

        $this->delivery->setWODeliveryStatus($this->input["WODeliveryStatus"]);
        $this->delivery->setWODeliveryTCode($this->input["WODeliveryTCode"]);
        $this->delivery->setWODeliveryDate(date('Y-m-d H:i:s'));

        $this->delivery->save($this->delivery->getWODeliveryID());


        $this->order->setWOrderShippingReference($this->input["WODeliveryTCode"]);
        $this->order->save($this->order->getWOrderID());


        BN_Responses::notification_success("Cambios Aplicados", 'reload');


    }

    protected function cmd_info()
    {
        $this->FormID = "Info";

        $this->getWODeliveryInfo();

        $delivery_info = $this->delivery->export();

//        $delivery_info['WOItemList'] = BN_Coders::json_decode($this->delivery->getWOItemList());

        foreach (BN_Coders::json_decode($this->delivery->getWOItemList()) as $item)
        {
            $oitem = new \Sparket\Orders\Item($this->order);
            $oitem->find($item);

            $item_info = ($oitem->export());


            $items_d[$item_info["WOItemID"]]["name"] = $item_info["WOItemName"];
            $items_d[$item_info["WOItemID"]]["qty"]   = $item_info["WOItemQty"];
        }

        $delivery_info['WOItemList'] = $items_d;

        $WODeliveryCancelInfo = BN_Coders::json_decode($this->delivery->getWODeliveryCancelInfo());

        $delivery_info['WODeliveryCancelInfo'] = $WODeliveryCancelInfo;

        if($WODeliveryCancelInfo)
        {
            $view_data['reject_username'] = BN_Users::fullname($WODeliveryCancelInfo['UserID']);
        }

        $view_data['delivery_info'] = $delivery_info;

        $view_data['item_list'] = \Sparket\Orders\Items::list_by_delivery($this->order, $this->delivery);

        $view_data['status_list'] = \Sparket\Orders\Delivery\Status::getOptionList();


        $content = $this->views->load_render('delivery.info', $this->view_data_presets($view_data));

        BN_Responses::modal($content, _("Detalles del Envío"));
    }

    private function getWODeliveryInfo()
    {

        if(!$this->input['WODeliveryID'])
        {
            BN_Responses::notification(_("El envío no existe. No es posible continuar."), "error");
        }

        $this->delivery = new \Sparket\Orders\Delivery\Delivery($this->order);
        $this->delivery->find($this->input['WODeliveryID']);

        if(!$this->delivery->getWODeliveryID() || ($this->delivery->getWODeliveryID() && $this->delivery->getCancelled() == true))
        {
            BN_Responses::notification(_("El envio no existe. No es posible continuar."), "error");
        }
    }
}

(new web_orders_order_delivery)->init();