<?php

use Novut\Tools\Notifications\Notification;
use Sparket\DB\Crm;

class web_orders_order extends web_orders_order_shared
{

    function config_custom()
    {



        print_r($this->order->export()); exit;


        $alert = new \Sparket\Orders\Alerts($this->order);
//        $alert->purchase($recipients);
//        $alert->ontheway($recipients);
        $alert->warehouse_request();

        exit;




//        exit;

    }

    function cmd_index()
    {
        if($this->order->getWOrderStatus() != \Sparket\Orders\Status::success['value'])
        {
            if($this->order->getWOrderStatus() == \Sparket\Orders\Status::cancelled['value'])
            {
                $this->layout->setLayoutOptions(
                    [
                        $this->layout->option_list()->setOptions([
                            $this->layout->option()->setLabel('Abrir Orden')->setOnclick(BN_JSHelpers::CMDRoute(false, ['cmd' => 'cancel_undo', 'WOrderID' => $this->order->getWOrderID()])),
                        ])->setIcon('fa-cog')
                    ]
                );
            }
            else{
                $this->layout->setLayoutOptions(
                    [
                        $this->layout->option_list()->setOptions([
                            $this->layout->option()->setLabel('Cancelar Orden')->setOnclick(BN_JSHelpers::CMDRoute(false, ['cmd' => 'cancel', 'WOrderID' => $this->order->getWOrderID()])),
                        ])->setIcon('fa-cog')
                    ]
                );
            }
        }


        //$this->layout->addLayoutAction('Hola')->setUrl("http://127.0.0.1/");
        //$this->layout->addLayoutNavBarLeft('Opcion 1', 'o1')->setOnclick("console.log('click!!!');");

        $this->layout->addPanelPrimary('customer')->setContent((new web_orders_order_customer())->getContent());
        $this->layout->addPanelPrimary('items')->setContent((new web_orders_order_items())->getContent());
        $this->layout->addPanelPrimary('payments')->setContent((new web_orders_order_payments())->getContent());
        $this->layout->addPanelSecondary('resume')->setContent((new web_orders_order_resume())->getContent());
        $this->layout->addPanelSecondary('invoices')->setContent((new web_orders_order_invoices())->getContent());
        //$this->layout->addPanelSecondary('preceipts')->setContent((new web_orders_order_preceipts())->getContent());
        $this->layout->addPanelSecondary('delivery')->setContent((new web_orders_order_delivery())->getContent());
        // $this->layout->addPanelSecondary('followup')->setContent((new web_orders_order_followup())->getContent());

        $this->layout->setWebLib('select2');
        $this->layout->setWebLib('datetimepicker');

        $this->layout->addLayoutBreadcrumbs("Website");
        $this->layout->addLayoutBreadcrumbs("Orders")->setUrl("web/orders/");
        $this->layout->addLayoutBreadcrumbs("Orden {$this->order->getWOrderID()}");

        $this->layout->render("Orden #{$this->order->getWOrderID()}");
    }

    protected function cmd_cancel()
    {

        if($this->order->getWOrderStatus() == \Sparket\Orders\Status::success['value'] || $this->order->getWOrderStatus() == \Sparket\Orders\Status::cancelled['value'])
        {
            BN_Responses::notification_error("La orden no puede ser actualizada se encuentra en un status que no es v&aacute;lido para aplicar el cambio.No es posible continuar.");
        }

        $this->FormID .= "Cancel";

//        $shipments = \Sparket\Orders\Delivery\Deliveries::list($this->order);
//
//        foreach ($shipments as $shipment)
//        {
//            if($shipment->getWODeliveryStatus() == \Sparket\Orders\Delivery\Status::success["value"])
//            {
//                BN_Responses::error("El envío se ha completado, no es posible cancelar esta orden.");
//            }
//        }



        $payments = (new \Sparket\Orders\Payments\Payments($this->order))->list();

        foreach ($payments as $payment)
        {
            if($payment["WOPaymentStatus"] == \Sparket\Orders\Payments\Status::success["value"])
            {
                BN_Responses::error("El pago se ha completado, no es posible cancelar esta orden.");
            }
        }

        $view_data = [];

        $view_data['WOrderCancelReasons'] = BN::OptionListHTML('WOrdersCancelReasons');

        $js[] = BN_Forms::setSelect2('CancelReason', $this->FormID);

        $content = $this->views->load_render('index.cancel', $this->view_data_presets($view_data));

        BN_Responses::modal($content, "Cancelar Orden", $js);
    }

    protected function cmd_cancel_save()
    {

        if($this->order->getWOrderStatus() == \Sparket\Orders\Status::success['value'] || $this->order->getWOrderStatus() == \Sparket\Orders\Status::cancelled['value'])
        {
            BN_Responses::notification_error("La orden no puede ser actualizada se encuentra en un status que no es v&aacute;lido para aplicar el cambio.No es posible continuar.");
        }



        echo "jjjjjj"; exit;
        $this->FormID .= "Cancel";

        $validation = BN_Forms::validation($this->FormID, $this->input);

        if(!$this->input['CancelReason'])
        {
            $validation->setRequiredField('CancelReason');
        }
        else
        {
            $option_info = BN::OptionListOInfo('WOrdersCancelReasons', $this->input['CancelReason']);

            if(!$option_info)
            {
                $validation->setError('CancelReason', "El motivo no existe.");
            }
        }

        $validation->validate();

        (new \Sparket\Orders\Actions())->cancel_order($this->order,  $option_info['OptionName'],  $this->input['CancelComment']);

        BN_Responses::notification_success("Orden Cancelada", "reload");
    }

    protected function cmd_cancel_undo()
    {
        if (!$this->input['confirm'])
        {
            responses()->confirm_simple("¿Deseas abrir la orden?", 'confirm');
        }

        (new \Sparket\Orders\Actions())->cancel_order_undo($this->order);

        BN_Responses::notification_success("Cambios Aplicados", "reload");
    }

}
(new web_orders_order)->init();