<?php

namespace Sparket\Orders\Notifications;

use Sparket\BN_Load;
use Sparket\Orders\Cfdi\Invoice;
use Sparket\Orders\Cfdi\PReceipt;
use Sparket\Orders\Delivery\Delivery;
use Sparket\Orders\Item;
use Sparket\Orders\Items;
use Sparket\Orders\Order;
use Sparket\Orders\Payments\Payment;
use Sparket\Shop\Config\Banks;

class Notifications
{
    /** @var array $param_data */
    private static $param_data;

    /** @var Order $order */
    private $order;

    function __construct(Order $order)
    {
        $this->order = $order;

        if(!$order->getWOrderID())
        {
            \BN_Responses::dev("La orden no existe. No es posible continuar.");
        }
    }

    protected function getList(string $mail_list)
    {
        if (self::$param_data === null)
        {
            self::$param_data = \Sparket\Shop\Config\Notifications::getEmailList();
        }
        
        return self::$param_data[$mail_list];
    }

    protected function get_emails(string $mail_list)
    {
        $emails = [];

        foreach ($this->getList($mail_list) as $email)
        {
            if(!\BN_Validation::email($email))
            {
                continue;
            }
            
            $emails[] = $email;
        }

        return $emails;
    }
    
    protected function sendEmail(string $email_id, string $email_subject, string $email_body, array $view_data = array())
    {
        if(\BN_Validation::email($email_id))
        {
            $email_list[] = $email_id;
        }
        else
        {
            $email_list  = $this->get_emails($email_id);
        }

        if(!$email_list)
        {
            return false;
        }

        $email_to = "";

        foreach($email_list as $ii)
        {
            if(!$email_to)
            {
                $email_to = $ii;

                continue;
            }

            $email_cc[] = $ii;
        }

        if(!$email_body)
        {
            return false;
        }

        $view_data['WOrderInfo'] = $view_data['WOrderInfo'] ? : $this->order->export();

        \Sparket\Tools\Email\Email::email_notification($email_subject, $email_body, $view_data, $email_to, $email_cc ? ['Cc' => $email_cc] : []);
    }

    /**********************************************
     Orders
     **********************************************/
    function add_order_user(array $email_list = [])
    {
        $WOrderInfo = $this->order->export();

        $email_subject = "Orden de Compra #{$this->order->getWOrderID()}";

        /****/
        $WOrderAddress = "{$this->order->getWOrderAddressStreet()} ";

        if($this->order->getWOrderAddressIntNumber())
        {
            $WOrderAddress .= "{$this->order->getWOrderAddressNumber()} ";
            $WOrderAddress .= "{$this->order->getWOrderAddressIntNumber()}, ";
        }
        else
        {
            $WOrderAddress .= "{$this->order->getWOrderAddressNumber()}, ";
        }

        $WOrderAddress .= "{$this->order->getWOrderAddressNeighborhood()}, ";
        $WOrderAddress .= "{$this->order->getWOrderAddressCity()}, ";
        $WOrderAddress .= "{$this->order->getWOrderAddressStateName()}, ";
        $WOrderAddress .= "{$this->order->getWOrderAddressCountryName()}, ";
        $WOrderAddress .= "{$this->order->getWOrderAddressZipCode()}.";

        $WOrderInfo['WOrderAddress'] = $WOrderAddress;

        $WOrderShippingAddress = "{$this->order->getWOrderShippingAddress()} ";

        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressNeighborhood()}, ";
        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressCity()}, ";
        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressStateName()}, ";
        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressCountryName()}, ";
        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressZipCode()}.";

        $WOrderInfo['WOrderShippingAddress'] = $WOrderShippingAddress;
        /*******/

        $view_data['payment_method'] = \Sparket\Orders\Payments\Method::getOptionInfo($this->order->getWOrderOptionPayment());
        $view_data['invoice_category'] = \Sparket\Tools\SAT\Invoice\Category::getOptionInfo($this->order->getWOrderInvoiceCategory());

        $view_data['WOItemList'] = \Sparket\Orders\Items::list($this->order);

        $view_data['CRMBaseUrl'] = \BN::param('CRMBaseUrl');

        $email_body = \BN_Load::file(__DIR__ . '/templates/add.order.user.twig');

        $this->sendEmail('new', $email_subject, $email_body, $view_data);
    }

    function add_order_wuser()
    {
        $WOrderInfo = $this->order->export();

        $email_subject = "Orden de Compra #{$this->order->getWOrderID()}";

        $view_data['payment_method'] = \Sparket\Orders\Payments\Method::getOptionInfo($this->order->getWOrderOptionPayment());
        $view_data['invoice_category'] = \Sparket\Tools\SAT\Invoice\Category::getOptionInfo($this->order->getWOrderInvoiceCategory());

        $view_data['WOrderID'] = $this->order->getWOrderID();

        $WOrderAddress = "{$this->order->getWOrderAddressStreet()} ";

        if($this->order->getWOrderAddressIntNumber())
        {
            $WOrderAddress .= "{$this->order->getWOrderAddressNumber()}, ";
            $WOrderAddress .= "{$this->order->getWOrderAddressIntNumber()}, ";
        }
        else
        {
            $WOrderAddress .= "{$this->order->getWOrderAddressNumber()}, ";
        }

        $WOrderAddress .= "{$this->order->getWOrderAddressNeighborhood()}, ";
        $WOrderAddress .= "{$this->order->getWOrderAddressCity()}, ";
        $WOrderAddress .= "{$this->order->getWOrderAddressStateName()}, ";
        $WOrderAddress .= "{$this->order->getWOrderAddressCountryName()}, ";
        $WOrderAddress .= "{$this->order->getWOrderAddressZipCode()}.";

        $WOrderInfo['WOrderAddress'] = $WOrderAddress;

        $WOrderShippingAddress = "{$this->order->getWOrderShippingAddress()} ";

        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressNeighborhood()}, ";
        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressCity()}, ";
        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressStateName()}, ";
        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressCountryName()}, ";
        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressZipCode()}.";

        $WOrderInfo['WOrderShippingAddress'] = $WOrderShippingAddress;

        $item_description  = "";

        $QVal['WOrderID'] = $WOrderInfo['WOrderID'];

        /** @var Item $item */
        foreach (\Sparket\Orders\Items::list($this->order) as $item)
        {
            $item_description .= $item->getWOItemQty();
            $item_description .= " " . $item->getWOItemName();
            $item_description .= ', ';
        }

        $item_description = rtrim($item_description, ', ');
        $item_description .= '.';

        $WOrderInfo['item_description'] = $item_description;

        $view_data['WOrderInfo'] = $WOrderInfo;

        $view_data['bank_account_list'] = Banks::getBankList();

        $email_body = \BN_Load::file(__DIR__ . '/templates/add.order.wuser.twig');

        $this->sendEmail($this->order->getWOrderContactEmail(), $email_subject, $email_body, $view_data);
    }

    function cancel_order_wuser()
    {
        $email_subject = "Orden de Compra #{$this->order->getWOrderID()} - Cancelada";

        $WOrderInfo = $this->order->export();

        $WOrderInfo['WOrderCancelInfo'] = \BN_Coders::json_decode($WOrderInfo['WOrderCancelInfo']);

        $view_data['WOrderID'] = $this->order->getWOrderID();

        $view_data['WOrderInfo'] = $WOrderInfo;

        $email_body = \BN_Load::file(__DIR__ . '/templates/cancel.order.wuser.twig');

        $this->sendEmail($this->order->getWOrderContactEmail(), $email_subject, $email_body, $view_data);
    }


    /**********************************************
     Payments
     **********************************************/
    function payment_pending_user(Payment $payment, array $email_list = [])
    {
              if(!$payment->getWOPaymentID())
        {
            return false;
        }

        $email_subject = "Orden de Compra #{$this->order->getWOrderID()} - Pago Agregado";

        $view_data['payment_method'] = \Sparket\Orders\Payments\Method::getOptionInfo($this->order->getWOrderOptionPayment());

        $view_data['WOPaymentInfo'] = $payment->export();;

        $email_body = \BN_Load::file(__DIR__ . '/templates/pending.payment.user.twig');

        $this->sendEmail('payment_new', $email_subject, $email_body, $view_data);
    }

    function payment_pending_wuser(Payment $payment)
    {
        if(!$payment->getWOPaymentID())
        {
            return false;
        }

        $email_subject = "Orden de Compra #{$this->order->getWOrderID()} - Pago Agregado";

        $view_data['payment_method'] = \Sparket\Orders\Payments\Method::getOptionInfo($this->order->getWOrderOptionPayment());

        $view_data['WOPaymentInfo'] = $payment->export();;

        $email_body = \BN_Load::file(__DIR__ . '/templates/pending.payment.wuser.twig');

        $this->sendEmail($this->order->getWOrderContactEmail(), $email_subject, $email_body, $view_data);
    }

    function payment_success_user(Payment $payment, array $email_list = [])
    {
         if(!$payment->getWOPaymentID())
        {
            return false;
        }

        $email_subject = "Orden de Compra #{$this->order->getWOrderID()} - Pago Aprobado";

        $view_data['payment_method'] = \Sparket\Orders\Payments\Method::getOptionInfo($this->order->getWOrderOptionPayment());

        $view_data['WOPaymentInfo'] = $payment->export();;
      
        $email_body = \BN_Load::file(__DIR__ . '/templates/success.payment.user.twig');

        $this->sendEmail('payment_success', $email_subject, $email_body, $view_data);
    }

    function payment_success_wuser(Payment $payment)
    {
        if(!$payment->getWOPaymentID())
        {
            return false;
        }

        $email_subject = "Orden de Compra #{$this->order->getWOrderID()} - Pago Aprobado";

        $view_data['payment_method'] = \Sparket\Orders\Payments\Method::getOptionInfo($this->order->getWOrderOptionPayment());

        $view_data['WOPaymentInfo'] = $payment->export();;

        $email_body = \BN_Load::file(__DIR__ . '/templates/success.payment.wuser.twig');

        $this->sendEmail($this->order->getWOrderContactEmail(), $email_subject, $email_body, $view_data);
    }

    function payment_reject_user(Payment $payment)
    {
        if(!$payment->getWOPaymentID())
        {
            return false;
        }

//        $email_subject = "Orden de Compra #{$this->order->getWOrderID()} - Pago Rechazado";
//
//        $view_data['payment_method'] = \Sparket\Orders\Payments\Method::getOptionInfo($this->order->getWOrderOptionPayment());
//
//        $view_data['WOPaymentInfo'] = $payment->export();
//
//        $view_data['CancelInfo'] = \BN_Coders::json_decode($payment->getWOPaymentCancelInfo());
//
//        $email_body = \BN_Load::file(__DIR__ . '/templates/reject.payment.user.twig');
//
//        $this->sendEmail('payment_reject', $email_subject, $email_body, $view_data);
    }

    function payment_reject_wuser(Payment $payment)
    {
        if(!$payment->getWOPaymentID())
        {
            return false;
        }
        
//        $email_subject = "Orden de Compra #{$this->order->getWOrderID()} - Pago Rechazado";
//
//        $view_data['payment_method'] = \Sparket\Orders\Payments\Method::getOptionInfo($this->order->getWOrderOptionPayment());
//
//        $view_data['WOPaymentInfo'] = $payment->export();
//        $view_data['CancelInfo'] = \BN_Coders::json_decode($payment->getWOPaymentCancelInfo());
//
//        $email_body = \BN_Load::file(__DIR__ . '/templates/reject.payment.wuser.twig');
//
//        $this->sendEmail($this->order->getWOrderContactEmail(), $email_subject, $email_body, $view_data);
    }

    /**********************************************
     Delivery
     **********************************************/
    function add_delivery_user(Delivery $delivery, array $email_list = [])
    {
        if (!$delivery->getWODeliveryID()) 
        {
            return false;
        }
       
        $WOrderInfo = $this->order->export();

        $email_subject = "Orden de Compra #{$this->order->getWOrderID()} - Envio Agregado";
        
        $WOrderShippingAddress = "{$this->order->getWOrderShippingAddress()} ";

        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressNeighborhood()}, ";
        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressCity()}, ";
        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressStateName()}, ";
        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressCountryName()}, ";
        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressZipCode()}.";

        $WOrderInfo['WOrderShippingAddress'] = $WOrderShippingAddress;
        /*******/

        $view_data['WOrderInfo'] = $WOrderInfo;
        $view_data['WODeliveryInfo'] = $delivery->export();
        $view_data['WOItemList'] = Items::list_by_delivery($this->order, $delivery);

        $email_body = \BN_Load::file(__DIR__ . '/templates/add.delivery.user.twig');

        $this->sendEmail('new_delivery', $email_subject, $email_body, $view_data);
    }

    function add_delivery_wuser(Delivery $delivery)
    {
        if (!$delivery->getWODeliveryID())
        {
            return false;
        }

        $WOrderInfo = $this->order->export();

        $email_subject = "Orden de Compra #{$this->order->getWOrderID()} - Envio Agregado";

        $view_data['WOrderID'] = $this->order->getWOrderID();

        $WOrderShippingAddress = "{$this->order->getWOrderShippingAddress()} ";

        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressNeighborhood()}, ";
        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressCity()}, ";
        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressStateName()}, ";
        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressCountryName()}, ";
        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressZipCode()}.";

        $WOrderInfo['WOrderShippingAddress'] = $WOrderShippingAddress;

        $view_data['WOrderInfo'] = $WOrderInfo;
        $view_data['WODeliveryInfo'] = $delivery->export();
        $view_data['WOItemList'] = Items::list_by_delivery($this->order, $delivery);
        
        $email_body = \BN_Load::file(__DIR__ . '/templates/add.delivery.wuser.twig');

        $this->sendEmail($this->order->getWOrderContactEmail(), $email_subject, $email_body, $view_data);
    }

    function reject_delivery_wuser(Delivery $delivery)
    {

        if (!$delivery->getWODeliveryID())
        {
            return false;
        }

        $WOrderInfo = $this->order->export();

        $email_subject = "Orden de Compra #{$this->order->getWOrderID()} - Envio Rechazado";

        $view_data['WOrderID'] = $this->order->getWOrderID();

        $WOrderShippingAddress = "{$this->order->getWOrderShippingAddress()} ";

        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressNeighborhood()}, ";
        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressCity()}, ";
        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressStateName()}, ";
        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressCountryName()}, ";
        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressZipCode()}.";

        $WOrderInfo['WOrderShippingAddress'] = $WOrderShippingAddress;

        $view_data['WOrderInfo'] = $WOrderInfo;
        $view_data['WODeliveryInfo'] = $delivery->export();
        $view_data['WOItemList'] = Items::list_by_delivery($this->order, $delivery);

        $email_body = \BN_Load::file(__DIR__ . '/templates/reject.delivery.wuser.twig');

        $this->sendEmail($this->order->getWOrderContactEmail(), $email_subject, $email_body, $view_data);
    }

    function success_delivery_wuser(Delivery $delivery)
    {
        if (!$delivery->getWODeliveryID())
        {
            return false;
        }

        $WOrderInfo = $this->order->export();

        $email_subject = "Orden de Compra #{$this->order->getWOrderID()} - Envio Finalizado";
        
        $view_data['WOrderID'] = $this->order->getWOrderID();

        $WOrderShippingAddress = "{$this->order->getWOrderShippingAddress()} ";

        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressNeighborhood()}, ";
        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressCity()}, ";
        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressStateName()}, ";
        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressCountryName()}, ";
        $WOrderShippingAddress .= "{$this->order->getWOrderShippingAddressZipCode()}.";

        $WOrderInfo['WOrderShippingAddress'] = $WOrderShippingAddress;

        $view_data['WOrderInfo'] = $WOrderInfo;
        $view_data['WODeliveryInfo'] = $delivery->export();
        $view_data['WOItemList'] = Items::list_by_delivery($this->order, $delivery);

        $email_body = \BN_Load::file(__DIR__ . '/templates/success.delivery.wuser.twig');

        $this->sendEmail($this->order->getWOrderContactEmail(), $email_subject, $email_body, $view_data);
    }

    function add_invoice_wuser(Invoice $invoice)
    {
        if(!$invoice->getWOCFDIID())
        {
            return false;
        }

        $email_subject = "Orden de Compra #{$this->order->getWOrderID()} - Factura Agregada";

        $view_data['WOCFDIInfo'] = $invoice->export();;
        $view_data['email_subject'] = $email_subject;

        $email_body = \BN_Load::file(__DIR__ . '/templates/add.invoice.wuser.twig');

        $this->sendEmail($this->order->getWOrderContactEmail(), $email_subject, $email_body, $view_data);
    }

    function add_preceipt_wuser(PReceipt $preceipt)
    {
        if(!$preceipt->getWOCFDIID())
        {
            return false;
        }

        $email_subject = "Orden de Compra #{$this->order->getWOrderID()} - Comprobante de Pago Agregado";

        $view_data['WOCFDIInfo'] = $preceipt->export();;
        $view_data['email_subject'] = $email_subject;

        $email_body = \BN_Load::file(__DIR__ . '/templates/add.preceipt.wuser.twig');

        $this->sendEmail($this->order->getWOrderContactEmail(), $email_subject, $email_body, $view_data);
    }


}