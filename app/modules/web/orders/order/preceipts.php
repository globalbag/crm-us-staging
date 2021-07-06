<?php


use Novut\Db\OrmOptionsGroup;

class web_orders_order_preceipts extends web_orders_order_shared implements \Novut\Layouts\Layout12\ContentInterface
{
    use \Novut\Layouts\Layout12\Module;


    /** @var \Sparket\Orders\Cfdi\PReceipt preceipt */
    protected $preceipt;

    function getContent(): \Novut\Layouts\Layout12\Content
    {
        $this->config_load();
        $view_data = [];
        $view_data['preceipts'] = \Sparket\Orders\Cfdi\PReceipts::list($this->order, (new OrmOptionsGroup)->setExport());
        $view_data['order_status_list'] = \Sparket\Orders\Status::getOptionList();


        $content = $this->getContentCardInstance('preceipts', "Complementos de Pago");
        $content->setBody($this->views->load_render('preceipts.view.twig', $this->view_data_presets($view_data)));

        if($this->order->getWOrderStatus() != \Sparket\Orders\Status::cancelled['value'])
        {
            if (sizeof($view_data['preceipts']) > 0)
            {
                $content->newCardTopAction()->setTitle("Add")->setOnclick("web_order_preceipt_new();");
            }
            else
            {
                $content->newCardTopAction()->setTitle("Add")->setOnclick("web_order_preceipt_new();")->setIcon('fa-plus');
            }
        }

        return $content;
    }

    function getPReceipt()
    {
        $this->preceipt = new \Sparket\Orders\Cfdi\PReceipt($this->order, $this->input['WOCFDIID']);

        if (!$this->preceipt || !$this->preceipt->getWOCFDIID())
        {
            responses()->alert_error('La factura no existe')->render();
        }
    }


    function cmd_new()
    {
        if($this->order->getWOrderStatus() == \Sparket\Orders\Status::success['value'] || $this->order->getWOrderStatus() == \Sparket\Orders\Status::cancelled['value'])
        {
            BN_Responses::notification_error("La orden no puede ser actualizada se encuentra en un status que no es v&aacute;lido para aplicar el cambio.No es posible continuar.");
        }

        $this->FormID .= "New";
        $view_data = [];
        $js = [];

        responses()->modal(views()->load_render("preceipts.new.twig", $this->view_data_presets($view_data)), "Agregar Complemento de Pago", $js)->render();
    }

    function cmd_new_add()
    {
        if($this->order->getWOrderStatus() == \Sparket\Orders\Status::success['value'] || $this->order->getWOrderStatus() == \Sparket\Orders\Status::cancelled['value'])
        {
            BN_Responses::notification_error("La orden no puede ser actualizada se encuentra en un status que no es v&aacute;lido para aplicar el cambio.No es posible continuar.");
        }

        $this->FormID .= "New";
        $validation = \BN_Forms::validation($this->FormID, $this->input);
        $validation->setRequiredField('PReceiptXML', function () use ($validation)
        {

            if ($_FILES['PReceiptXML'] && isset($_FILES['PReceiptXML']['tmp_name'])  && $_FILES['PReceiptXML']['tmp_name'] && file_exists($_FILES['PReceiptXML']['tmp_name']))
            {
                $xml_data = Sparket\Orders\Cfdi\Tools::parseXML(file_get_contents($_FILES['PReceiptXML']['tmp_name']));
                if (!$xml_data || !isset($xml_data['Comprobante']) || !$xml_data['Comprobante'] || !$xml_data['Comprobante']['Complemento']['TimbreFiscalDigital']['UUID'])
                {
                    return new \Novut\Tools\Forms\ValidationError('PReceiptXML', "El xml es inválido.");
                }

                // tipo documento
                if ($xml_data['Comprobante']['TipoDeComprobante'] != 'P')
                {
                    return new \Novut\Tools\Forms\ValidationError('PReceiptXML', "El documento xml debe ser un complemento de pago.");
                }
                //

                // cfdi exist
                $preceipt = \Sparket\Orders\Cfdi\PReceipts::find_by_UUID($this->order, $xml_data['Comprobante']['Complemento']['TimbreFiscalDigital']['UUID']);
                if ($preceipt && $preceipt->getWOCFDIID())
                {
                    return new \Novut\Tools\Forms\ValidationError('PReceiptXML', "El folio {$preceipt->getWOCFDICode()} ya existe en esta orden.");
                }

                // rfc
                if (strtoupper($xml_data['Comprobante']['Receptor']['Rfc']) != strtolower($this->order->getWOrderContactRFC()))
                {
                    if (!$this->input['confirm-receptor'])
                    {
                        $validation->confirm("El receptor del CFDI no coincide con el de la orden web. ¿Deseas continuar?", "confirm-receptor");
                    }
                }



                return true;
            }
            else
            {
                return new \Novut\Tools\Forms\ValidationError('PReceiptXML', "Debes ingresar el archivo xml.");
            }

        });
        $validation->setRequiredField('PReceiptPDF', function()
        {
            if (!$_FILES['PReceiptPDF'] || ($_FILES['PReceiptPDF'] && !file_exists($_FILES['PReceiptPDF']['tmp_name'])))
            {
                return new \Novut\Tools\Forms\ValidationError('PReceiptPDF', "El archivo pdf no existe.");
            }
        });

        if(file_exists($_FILES['PReceiptXML']['tmp_name']))
        {
            $this->input['PReceiptXML'] = true;
        }
        if(file_exists($_FILES['PReceiptPDF']['tmp_name']))
        {
            $this->input['PReceiptPDF'] = true;
        }

        $validation->validate($this->input);

        $pdf_url = "";
        $xml_data = Sparket\Orders\Cfdi\Tools::parseXML(file_get_contents($_FILES['PReceiptXML']['tmp_name']));
        $xml_file = new \Novut\Tools\Files\S3;
        $xml_file->setFileContent(file_get_contents($_FILES['PReceiptXML']['tmp_name']));
        $xml_file->setFileName("web/orders/".$xml_data['Comprobante']['Complemento']['TimbreFiscalDigital']['UUID'].".xml");
        $xml_url = $xml_file->push();


        if (!$xml_url)
        {
            responses()->alert_error("No fue posible agregar el xml.")->render();
        }

        if ($_FILES['PReceiptPDF'] && isset($_FILES['PReceiptPDF']['tmp_name'])  && $_FILES['PReceiptPDF']['tmp_name'] && file_exists($_FILES['PReceiptPDF']['tmp_name']))
        {

            $pdf_file = new \Novut\Tools\Files\S3;
            $pdf_file->setFileContent(file_get_contents($_FILES['PReceiptPDF']['tmp_name']));
            $pdf_file->setFileName("web/orders/".$xml_data['Comprobante']['Complemento']['TimbreFiscalDigital']['UUID'].".pdf");
            $pdf_url = $pdf_file->push();
        }

        $preceipt = new \Sparket\Orders\Cfdi\PReceipt($this->order);
        $preceipt->add(file_get_contents($_FILES['PReceiptXML']['tmp_name']), $xml_url, $pdf_url);


        $preceipt->find($preceipt->getWOCFDIID());

        (new \Sparket\Orders\Notifications\Notifications($this->order))->add_preceipt_wuser($preceipt);

        responses()->notification_success("Complemento Agregado", $this->getContent()->getJs())->closeModal()->render();
    }

    function cmd_delete()
    {
        if($this->order->getWOrderStatus() == \Sparket\Orders\Status::success['value'] || $this->order->getWOrderStatus() == \Sparket\Orders\Status::cancelled['value'])
        {
            BN_Responses::notification_error("La orden no puede ser actualizada se encuentra en un status que no es v&aacute;lido para aplicar el cambio.No es posible continuar.");
        }

        $this->getPReceipt();

        if (!$this->input['confirm'])
        {
            responses()->confirm_simple("¿Deseas remover esta factura?", 'confirm');
        }

        $this->preceipt->cancel();

        responses()->notification_success("Complemento removido.", $this->getContent()->getJs())->render();
    }

    function cmd_download()
    {
        $this->getPReceipt();
        $url = null;

        if ($this->input['type'] == 'xml')
        {
            $url = $this->preceipt->getWOCFDIXML();
        }
        else if ($this->input['type'] == 'pdf')
        {
            $url = $this->preceipt->getWOCFDIPDF();
        }

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

    function cmd_view()
    {
        $this->getPReceipt();
        $url = $this->preceipt->getWOCFDIPDF();

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
        \BN_FileH::pdf_display((new \Novut\Tools\Files\FileObject)->setContent($file_content, basename($url)));
    }


}

(new web_orders_order_preceipts)->init();