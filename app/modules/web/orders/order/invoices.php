<?php


use Novut\Db\OrmOptionsGroup;

class web_orders_order_invoices extends web_orders_order_shared implements \Novut\Layouts\Layout12\ContentInterface
{

    use \Novut\Layouts\Layout12\Module;

    /** @var \Sparket\Orders\Cfdi\Invoice $invoice */
    protected $invoice;

    function getContent(): \Novut\Layouts\Layout12\Content
    {
        $this->config_load();

        $view_data = [];

        $content = $this->getContentCardInstance('invoices', "Invoices");
        $content->setBody($this->views->load_render('invoices.view.twig', $this->view_data_presets($view_data)));


        return $content;
    }

    function cmd_pdf_preview()
    {

        $this->view_data["WOrderInfo"]                = $this->order->export();
        $this->view_data["WOrderInfo"]['WOrderItems'] = \Sparket\Orders\Items::list($this->order, (new \Novut\Db\OrmOptionsGroup())->setExport());
        $this->view_data["WOrderInfo"]["Payment"] = $this->load_payment();

//        $content = $this->views->load_render('invoice.pdf', $this->view_data_presets($this->view_data));
        $content = $this->views->render(\Novut\Tools\Templates::getTemplateContent('order.invoices'), $this->view_data_presets($this->view_data));

        $dompdf = new Dompdf\Dompdf;
        $dompdf->loadHtml($content);


        // Render the HTML as PDF
        $dompdf->render();


        $file_object = new \Novut\Tools\Files\FileObject;
        $file_object->setContent($dompdf->output(), "order_{$this->order->getWOrderID()}_".\BN::random_code_human(4).".pdf");

        if ($this->input['download'])
        {
            \BN_FileH::download_js($file_object);
        }
        else
        {
            \BN_FileH::pdf_display($file_object);
        }

    }

    private function load_payment()
    {
        $stripe_config = new Sparket\Payments\Stripe\Config;
        \Stripe\Stripe::setApiKey($stripe_config->getSecretKey());

        $db = \Sparket\DB\Web::getDB();
        $data = $db->TableInfo("web_orders_payments", 'WOrderID', $this->WOrderID, " AND Cancelled = 0");

        if($data["WOPaymentMethod"] == "stripe")
        {
            $payment_gateway = \Stripe\PaymentIntent::update($data["WOPaymentStripeID"], [])->toArray();
            $charges         = reset($payment_gateway["charges"]["data"])["payment_method_details"];

            if($charges["card"])
            {
                $data["Info"]["type"]     = "Card";
                $data["Info"]["brand"]    = $charges["card"]["brand"];
                $data["Info"]["details"]  = "**** ".$charges["card"]["last4"];
                $data["Info"]["id"]       = $data["WOPaymentStripeID"];
            }
        }

        elseif ($data["WOPaymentMethod"] == "paypal")
        {
            $payment_gateway = (new \Paypal\Order())->get_info($data["WOPaymentPayPalID"]);

            if($payment_gateway)
            {
                $data["Info"]["details"]     = "Paypal";
                $data["Info"]["id"]     = $data["WOPaymentPayPalID"];
            }
        }

//        print_r($data); exit;

        return $data;

    }



}

(new web_orders_order_invoices)->init();