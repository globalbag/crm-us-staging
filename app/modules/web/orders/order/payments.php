<?php


class web_orders_order_payments extends web_orders_order_shared implements \Novut\Layouts\Layout12\ContentInterface
{
    use \Novut\Layouts\Layout12\Module;

    /** @var Sparket\Orders\Payments\Payment $payment */
    private $payment;

    function config_custom()
    {
        $this->FormID.= "Payment";

        // Payment
         $stripe_config = new Sparket\Payments\Stripe\Config;

        \Stripe\Stripe::setApiKey($stripe_config->getSecretKey());
    }

    function getContent(): \Novut\Layouts\Layout12\Content
    {
        $this->config_load();

        $payment_button = 1;

        foreach ((new \Sparket\Orders\Payments\Payments($this->order))->list() as $data)
        {
            if($data["WOPaymentMethod"] == "stripe")
            {
                $payment_gateway = \Stripe\PaymentIntent::update($data["WOPaymentStripeID"], [])->toArray();
                $charges         = reset($payment_gateway["charges"]["data"])["payment_method_details"];

                if($charges["card"])
                {
                    $data["PaymentMethodInfo"]["type"]     = "Card";
                    $data["PaymentMethodInfo"]["brand"]    = $charges["card"]["brand"];
                    $data["PaymentMethodInfo"]["details"]  = "**** ".$charges["card"]["last4"];
                    $data["PaymentMethodInfo"]["id"]       = $data["WOPaymentStripeID"];
                }
            }

            elseif ($data["WOPaymentMethod"] == "paypal")
            {
                $payment_gateway = (new \Paypal\Order())->get_info($data["WOPaymentPayPalID"]);

                if($payment_gateway)
                {
                    $data["PaymentMethodInfo"]["details"]     = "Paypal";
                    $data["PaymentMethodInfo"]["id"]     = $data["WOPaymentPayPalID"];
                }
            }


            if($data['WOPaymentMethod'] == 'deposit')
            {
                 if($data['WOPaymentStatus'] == \Sparket\Orders\Payments\Status::pending['value'] || $data['WOPaymentStatus'] == \Sparket\Orders\Payments\Status::success['value'])
                {
                    $payment_button = 0;
                }
            }


            $payment_list[] = $data;
        }

        $view_data['payment_list'] = $payment_list;

//        print_r($payment_list); exit;

        $view_data['method_list'] = \Sparket\Orders\Payments\Method::getOptionList();
        $view_data['status_list'] = \Sparket\Orders\Payments\Status::getOptionList();
        $view_data['order_status_list'] = \Sparket\Orders\Status::getOptionList();


        $content = $this->getContentCardInstance('payments', 'Payments');
        $content->setBody($this->views->load_render('payments.view.twig', $this->view_data_presets($view_data)));

//        if($this->order->getWOrderStatus() != \Sparket\Orders\Status::cancelled['value'] && $payment_button)
//        {
//            $content->newCardTopAction()->setTitle("Agregar")->setOnclick("web_order_payment_new();")->setIcon('fa-plus');
//
//        }


        return $content;
    }

    protected function cmd_new()
    {
        if($this->order->getWOrderStatus() == \Sparket\Orders\Status::success['value'] || $this->order->getWOrderStatus() == \Sparket\Orders\Status::cancelled['value'])
        {
            BN_Responses::notification_error("La orden no puede ser actualizada se encuentra en un status que no es v&aacute;lido para aplicar el cambio.No es posible continuar.");
        }

        $this->validate_status_pending();

        if((new Sparket\Orders\Payments\Payments($this->order))->info_pending())
        {
            BN_Responses::notification_error("Existe un pago pendiente. No es posible continuar.",  "reload");
        }

        if((new Sparket\Orders\Payments\Payments($this->order))->info_success())
        {
            BN_Responses::notification_error("Existe un pago finalizado. No es posible continuar.", "reload");
        }

        $this->FormID .= "New";

        $js[] = BN_Forms::setValueText('WOPaymentAmount', $this->order->getWOrderTotalPaymentsDebt(), $this->FormID);
        $js[] = BN_Forms::setValueText('WOPaymentDate', date('d/m/Y'), $this->FormID);

        $content = $this->views->load_render('payments.form', $this->view_data_presets([]));

        BN_Responses::modal($content, "Agregar Recibo de Pago", $js);
    }

    protected function cmd_new_add()
    {
        if($this->order->getWOrderStatus() == \Sparket\Orders\Status::success['value'] || $this->order->getWOrderStatus() == \Sparket\Orders\Status::cancelled['value'])
        {
            BN_Responses::notification_error("La orden no puede ser actualizada se encuentra en un status que no es v&aacute;lido para aplicar el cambio.No es posible continuar.");
        }

        $this->validate_status_pending();

        if((new Sparket\Orders\Payments\Payments($this->order))->info_pending())
        {
            BN_Responses::notification_error("Existe un pago pendiente. No es posible continuar.",  "reload");
        }

        if((new Sparket\Orders\Payments\Payments($this->order))->info_success())
        {
            BN_Responses::notification_error("Existe un pago finalizado. No es posible continuar.", "reload");
        }

        $this->FormID .= "New";

        $validation = BN_Forms::validation($this->FormID, $this->input);

        $this->input['WOPaymentDate'] = BN_Date::format($this->input['WOPaymentDate'], 'Y-m-d', 'd/m/Y');

        if(!$this->input['WOPaymentDate'])
        {
            $validation->setError('WOPaymentDate', "La fecha no es v&aacute;lida.");
        }

        if(!is_numeric($this->input['WOPaymentAmount']))
        {
            $validation->setError('WOPaymentAmount', "El valor del monto debe ser n&uacute;merico.");
        }

        if(!($this->input['WOPaymentAmount'] > 0))
        {
            $validation->setError('WOPaymentAmount', "El valor del monto debe ser mayor a 0.");
        }

        $this->input['WOPaymentAmount'] = $this->order->getWOrderTotalPaymentsDebt();

        if($_FILES['FileFile']['tmp_name'])
        {
            $ext_list = [
                'png',
                'jpg',
                'jpeg',
                'pdf',
                'gif',
            ];

            // files Name
            $file_ext = pathinfo($_FILES['FileFile']['name'], PATHINFO_EXTENSION);

            $file_ext = strtolower($file_ext);

            if(!in_array($file_ext, $ext_list))
            {
                $validation->setError('FileFile', "La extensi&oacute;n del archivo no es v&aacute;lido.");
            }
        }
        else
        {
            $validation->setRequiredField('FileFile');
        }

        $validation->setRequiredField('WOPaymentAmount');
        $validation->setRequiredField('WOPaymentDate');

        $validation->validate();

        $WOPaymentID = (new \Sparket\Orders\Actions())->add_payment_deposit($this->order, $this->input['WOPaymentDate'], $_FILES['FileFile']);

        if(!$WOPaymentID)
        {
            BN_Responses::notification_error("No fue posible agregar el pago intente nuevamente.");
        }

        BN_Responses::notification_success("Pago Agregado", "reload");
    }

    protected function cmd_reject()
    {
        if($this->order->getWOrderStatus() == \Sparket\Orders\Status::success['value'] || $this->order->getWOrderStatus() == \Sparket\Orders\Status::cancelled['value'])
        {
            BN_Responses::notification_error("La orden no puede ser actualizada se encuentra en un status que no es v&aacute;lido para aplicar el cambio.No es posible continuar.");
        }

        $this->FormID = "Reject";

        $this->getWOPaymentInfo();

        if($this->order->getWOrderStatus() != \Sparket\Orders\Status::success['value'] && $this->order->getWOrderStatus() != \Sparket\Orders\Status::payment['value'])
        {
            $this->validate_status_pending();

            if($this->payment->getWOPaymentStatus() != Sparket\Orders\Payments\Status::pending['value'] && $this->payment->getWOPaymentStatus() != Sparket\Orders\Payments\Status::success['value'])
            {
                BN_Responses::notification_error("El pago no puede ser rechazado. No es posible continuar.");
            }
        }

        $view_data['cmd']         = "reject_save";
        $view_data['WOPaymentID'] = $this->payment->getWOPaymentID();


        $content = $this->views->load_render('payments.cancel', $this->view_data_presets($view_data));

        BN_Responses::modal($content, "Cancelar Pago");
    }

    protected function cmd_reject_save()
    {
        if($this->order->getWOrderStatus() == \Sparket\Orders\Status::success['value'] || $this->order->getWOrderStatus() == \Sparket\Orders\Status::cancelled['value'])
        {
            BN_Responses::notification_error("La orden no puede ser actualizada se encuentra en un status que no es v&aacute;lido para aplicar el cambio.No es posible continuar.");
        }

        $this->FormID = "Reject";

        $this->getWOPaymentInfo();

        if($this->order->getWOrderStatus() != \Sparket\Orders\Status::success['value'] && $this->order->getWOrderStatus() != \Sparket\Orders\Status::payment['value'])
        {
            $this->validate_status_pending();

            if($this->payment->getWOPaymentStatus() != Sparket\Orders\Payments\Status::pending['value'] && $this->payment->getWOPaymentStatus() != Sparket\Orders\Payments\Status::success['value'])
            {
                BN_Responses::notification_error("El pago no puede ser rechazado. No es posible continuar.");
            }
        }

        $validation = BN_Forms::validation($this->FormID, $this->input);

        $validation->setRequiredField('CancelComment');

        $validation->validate();

        (new \Sparket\Orders\Actions())->reject_payment($this->order, $this->payment, $this->input['CancelComment']);

        BN_Responses::notification_success("Pago Cancelado", "reload");
    }

    protected function cmd_success()
    {
        if($this->order->getWOrderStatus() == \Sparket\Orders\Status::success['value'] || $this->order->getWOrderStatus() == \Sparket\Orders\Status::cancelled['value'])
        {
            BN_Responses::notification_error("La orden no puede ser actualizada se encuentra en un status que no es v&aacute;lido para aplicar el cambio.No es posible continuar.");
        }

        $this->getWOPaymentInfo();

        if($this->payment->getWOPaymentStatus() != Sparket\Orders\Payments\Status::pending['value'])
        {
            BN_Responses::notification_error("El pago no puede ser aprobado. No es posible continuar.");
        }

        if(!$this->input['success'])
        {
            BN_Responses::confirm_quick("¿Est&aacute;s seguro de confirmar el pago?", "success");
        }

        (new \Sparket\Orders\Actions())->success_payment($this->order, $this->payment);

        BN_Responses::notification_success("Pago Confirmado", "reload");
    }

    protected function cmd_delete()
    {
        if($this->order->getWOrderStatus() == \Sparket\Orders\Status::success['value'] || $this->order->getWOrderStatus() == \Sparket\Orders\Status::cancelled['value'])
        {
            BN_Responses::notification_error("La orden no puede ser actualizada se encuentra en un status que no es v&aacute;lido para aplicar el cambio.No es posible continuar.");
        }

        $this->getWOPaymentInfo();

        if($this->order->getWOrderStatus() == \Sparket\Orders\Status::cancelled['value'])
        {
            BN_Responses::notification_error("La orden se encuentra cancelada no es v&aacute;lido para aplicar el cambio. No es posible continuar.", "reload");
        }

        if($this->payment->getWOPaymentStatus() != Sparket\Orders\Payments\Status::pending['value'])
        {
            BN_Responses::notification_error("El pago no puede ser eliminado. No es posible continuar.");
        }

        if (!$this->input['confirm'])
        {
            responses()->confirm_simple("¿Deseas remover este pago?", 'confirm');
        }

        $this->payment->cancel();

        responses()->notification_success("Pago Eliminado", $this->getContent()->getJs())->render();
    }

    protected function cmd_cancel()
    {
        $this->getWOPaymentInfo();

        if($this->order->getWOrderStatus() == \Sparket\Orders\Status::success['value'] || $this->order->getWOrderStatus() == \Sparket\Orders\Status::cancelled['value'])
        {
            BN_Responses::notification_error("La orden no puede ser actualizada se encuentra en un status que no es v&aacute;lido para aplicar el cambio.No es posible continuar.");
        }

        $content = $this->views->load_render('payments.cancel', $this->view_data_presets($view_data));

        $view_data['cmd']         = "cancel_save";
        $view_data['WOPaymentID'] = $this->payment->getWOPaymentID();

        $content = $this->views->load_render('payments.cancel', $this->view_data_presets($view_data));

        BN_Responses::modal($content, "Cancelar Pago");

//        $this->payment->setWOPaymentStatus(\Sparket\Orders\Payments\Status::cancel["value"]);
//        $this->payment->save($this->payment->getWOPaymentID());
//
//        BN_Responses::notification_success("Cambios Aplicados", "reload");
    }

    protected function cmd_cancel_save()
    {

        print_r($this->input); exit;

    }

    protected function cmd_info()
    {
        $this->FormID = "Info";

        $this->getWOPaymentInfo();

        $payment_info = $this->payment->export();

        $WOPaymentCancelInfo = BN_Coders::json_decode($this->payment->getWOPaymentCancelInfo());

        $payment_info['WOPaymentCancelInfo'] = $WOPaymentCancelInfo;

        if($WOPaymentCancelInfo)
        {
            $view_data['reject_username'] = BN_Users::fullname($WOPaymentCancelInfo['UserID']);
        }

        $payment_details = [];

        if($payment_info["WOPaymentMethod"] == "stripe")
        {
            $payment_gateway = \Stripe\PaymentIntent::update($payment_info["WOPaymentStripeID"], [])->toArray();

            foreach ($payment_gateway["charges"]["data"] as $charge_id => $charge)
            {
                if(isset($charge["payment_method_details"]["card"]))
                {
                    $month = "";

                    if(strlen($charge["payment_method_details"]["card"]["exp_month"]) == 1)
                    {
                        $month.= "0".$charge["payment_method_details"]["card"]["exp_month"];
                    }

                    $payment_details["card"][$charge_id]["brand"]       = $charge["payment_method_details"]["card"]["brand"];
                    $payment_details["card"][$charge_id]["exp_month"]   = $month;
                    $payment_details["card"][$charge_id]["exp_year"]    = $charge["payment_method_details"]["card"]["exp_year"];
                    $payment_details["card"][$charge_id]["last4"]       = $charge["payment_method_details"]["card"]["last4"];
                    $payment_details["card"][$charge_id]["type"]        = $charge["payment_method_details"]["card"]["funding"];
                }
            }
        }

        elseif ($payment_info["WOPaymentMethod"] == "paypal")
        {
            $payment_gateway = (new \Paypal\Order())->get_info($payment_info["WOPaymentPayPalID"]);

            if($payment_gateway)
            {
                $payment_details["details"]        = "Paypal";
                $payment_details["payer"]["name"]  = implode(' ', $payment_gateway["payer"]["name"]);
                $payment_details["payer"]["email"] = $payment_gateway["payer"]["email_address"];
            }

        }

        $view_data['payment_info']    = $payment_info;
        $view_data['payment_details'] = $payment_details;

//        print_r($view_data); exit;


        $view_data['status_list'] = \Sparket\Orders\Payments\Status::getOptionList();
        $view_data['method_info'] = \Sparket\Orders\Payments\Method::getOptionInfo($this->payment->getWOPaymentMethod());

        $content = $this->views->load_render('payment.info', $this->view_data_presets($view_data));

        BN_Responses::modal($content, "Detalles del Pago");
    }

    protected function cmd_download()
    {
        $this->getWOPaymentInfo();

        $url = $this->payment->getWOPaymentFile();

        if (!$url)
        {
            responses()->alert_error("El archivo no existe")->render();
        }

        $file_content = (new \Novut\Tools\Files\S3())->getContent($url);

        if (!$file_content)
        {
            responses()->alert_error("El archivo no existe")->render();
        }

        // download
        \BN_FileH::download_js((new \Novut\Tools\Files\FileObject)->setContent($file_content, basename($url)));
    }

    protected function cmd_view()
    {
        $this->getWOPaymentInfo();

        $url = $this->payment->getWOPaymentFile();

        if (!$url)
        {
            responses()->alert_error("El archivo no existe")->render();
        }

        $file_content = (new \Novut\Tools\Files\S3())->getContent($url);

        if (!$file_content)
        {
            responses()->alert_error("El archivo no existe")->render();
        }

        $file_ext = pathinfo($url, PATHINFO_EXTENSION);

        $image_list = [
            'png',
            'jpg',
            'jpeg',
            'gif',
        ];

        if(in_array($file_ext, $image_list))
        {
            BN_FileH::image_display((new \Novut\Tools\Files\FileObject)->setContent($file_content, basename($url)));
        }
        else
        {
            // download
            \BN_FileH::pdf_display((new \Novut\Tools\Files\FileObject)->setContent($file_content, basename($url)));
        }

    }

    private function getWOPaymentInfo()
    {
        if(!$this->input['WOPaymentID'])
        {
            BN_Responses::notification("El pago no existe. No es posible continuar.", "error");
        }

        $this->payment = new Sparket\Orders\Payments\Payment($this->order);
        $this->payment->find($this->input['WOPaymentID']);

        if(!$this->payment->getWOPaymentID() || ($this->payment->getWOPaymentID() && $this->payment->getCancelled()))
        {
            BN_Responses::notification("El pago no existe. No es posible continuar.", "error");
        }
    }

}
(new web_orders_order_payments)->init();