<?php


namespace Sparket\Orders;


use Sparket\Orders\Cart\Cart;
use Sparket\Orders\Delivery\Delivery;
use Sparket\Orders\Notifications\Notifications;
use Sparket\Orders\Payments\Payment;
use Sparket\Orders\Payments\PDeposit;
use Sparket\Orders\Payments\PPayPal;

class Actions
{

    function add_order(Cart $cart)
    {
        // Create Order from Cart
        $WOrderID = $cart->addOrder();

        if(!$WOrderID)
        {
            \BN_Responses::notification("No fue posible agregar la orden de compra intente nuevamente.", "error");
        }

        $order = new \Sparket\Orders\Order();
        $order->find($WOrderID);

        // Email User
        (new Notifications($order))->add_order_user();

        // Email Web User
        (new Notifications($order))->add_order_wuser();

        return $WOrderID;
    }

    function add_payment_deposit(Order $order, $date = "", array $file = [])
    {
        // Create Order from Cart
        $WOrderID = $order->getWOrderID();

        if(!$WOrderID)
        {
            \BN_Responses::notification("No ha sido definida la orden de compra intente nuevamente.", "error");
        }

        $date = $date ? : date('Y-m-d H:i:s');

        $payment = new PDeposit($order);
        $payment->setFile($file);
        $payment->setWOPaymentMethod(\Sparket\Orders\Payments\Method::deposit['value']);
        $payment->setWOPaymentDate($date);
        $payment->setWOPaymentAmount($order->getWOrderTotalTotal());

        $WOPaymentID = $payment->add();

        if(!$WOPaymentID)
        {
            \BN_Responses::notification("No fue posible agregar el pago intente nuevamente.", "error");
        }

        $payment->find($WOPaymentID);

        if(\BN_Var::$WUserInfo['WUserID'])
        {
            (new Notifications($order))->payment_pending_user($payment);
        }

        if(\BN_Var::$UserInfo['UserID'])
        {
            (new Notifications($order))->payment_pending_wuser($payment);
        }

        return $WOPaymentID;
    }

    function add_payment_paypal(Order $order, $paypal_id = "")
    {
        // Create Order from Cart
        $WOrderID = $order->getWOrderID();

        if(!$WOrderID)
        {
            \BN_Responses::notification("No ha sido definida la orden de compra intente nuevamente.", "error");
        }

        if(is_string($paypal_id))
        {
            $paypal_payment = (new \Paypal\Order())->get_info($paypal_id);
        }
        else if(is_array($paypal_id) && $paypal_id['id'])
        {
            $paypal_payment = $paypal_id;
        }

        if(!$paypal_payment)
        {
            \BN_Responses::notification("No ha sido definida la referencia de pago de paypal intente nuevamente.", "error");
        }


        $paypal_purchase_units = reset($paypal_payment['purchase_units']);

        // Validar que el pago sea de la misma orden
        if($order->getWOrderID() != $paypal_purchase_units['custom_id'])
        {
            return false;
        }

        $payment = new PPayPal($order);

        $payment->setWOPaymentDate(date('Y-m-d H:i:s', strtotime($paypal_payment['update_time'])));
        $payment->setWOPaymentAmount($paypal_purchase_units['amount']['value']);
        $payment->setWOPaymentPayPalID($paypal_payment['id']);
        $payment->setWOPaymentPayPalStatus($paypal_payment['status']);
        $payment->setWOPaymentPayPalPayerID($paypal_payment['payer']['payer_id']);
        $payment->setWOPaymentCurrency($paypal_purchase_units['amount']['currency_code']);

        if($paypal_payment['status'] == 'COMPLETED')
        {
            $payment->setWOPaymentStatus(\Sparket\Orders\Payments\Status::success['value']);
        }
        else if($paypal_payment['status'] == 'VOIDED')
        {
            $payment->setWOPaymentStatus(\Sparket\Orders\Payments\Status::reject['value']);
        }
        else
        {
            $payment->setWOPaymentStatus(\Sparket\Orders\Payments\Status::pending['value']);
        }

        $WOPaymentID = $payment->add();

        if(!$WOPaymentID)
        {
            \BN_Responses::notification("No fue posible agregar el pago intente nuevamente.", "error");
        }

        $payment->find($WOPaymentID);

        if($paypal_payment['status'] == 'COMPLETED')
        {
            (new Notifications($order))->payment_success_user($payment);

            (new Notifications($order))->payment_success_wuser($payment);
        }
        else if($paypal_payment['status'] == 'VOIDED')
        {
            (new Notifications($order))->payment_reject_user($payment);

            (new Notifications($order))->payment_reject_wuser($payment);
        }
        else
        {
            (new Notifications($order))->payment_pending_user($payment);

            (new Notifications($order))->payment_pending_wuser($payment);
        }

        return $WOPaymentID;
    }

    function success_payment(Order $order, Payment $payment)
    {
        if(!$order->getWOrderID())
        {
            return false;
        }

        if(!$payment->getWOPaymentID())
        {
            return false;
        }

        $payment->update_status_success($payment->getWOPaymentID());

        (new Notifications($order))->payment_success_user($payment);

        (new Notifications($order))->payment_success_wuser($payment);
    }

    function reject_payment(Order $order, Payment $payment, string $comment)
    {
        if(!$order->getWOrderID())
        {
            return false;
        }

        if(!$payment->getWOPaymentID())
        {
            return false;
        }

        $payment->update_status_reject($payment->getWOPaymentID(), $comment);

        if(\BN_Var::$WUserInfo['WUserID'])
        {
            (new Notifications($order))->payment_reject_user($payment);
        }

        if(\BN_Var::$UserInfo['UserID'])
        {
            (new Notifications($order))->payment_reject_wuser($payment);
        }

    }

    function success_delivery(Order $order, Delivery $delivery)
    {
        $WODeliveryID = $delivery->getWODeliveryID();

        if(!$WODeliveryID)
        {
            \BN_Responses::notification("El env&iacute;o no ha sido definido intente nuevamente.", "error");
        }

        $delivery->update_status_success($delivery->getWODeliveryID());

        // TODO notificacion?
//        (new Notifications())->success_delivery_wuser($order, $delivery);
    }

    function reject_delivery(Order $order, Delivery $delivery, string $comment)
    {
        $WODeliveryID = $delivery->getWODeliveryID();

        if(!$WODeliveryID)
        {
            \BN_Responses::notification("El env&iacute;o no ha sido definido intente nuevamente.", "error");
        }

        $delivery->update_status_reject($delivery->getWODeliveryID(), $comment);

        // TODO notificacion?
//        (new Notifications())->reject_delivery_wuser($order, $delivery);
    }

    function payment_order(Order $order)
    {
        $WOrderID = $order->getWOrderID();

        if(!$WOrderID)
        {
            \BN_Responses::notification("No ha sido definida la orden de compra intente nuevamente.", "error");
        }

        $order->update_status_payment();

        // TODO notificacion?
//        (new Notifications())->payment_order_wuser($order, $delivery);
    }

    function success_order(Order $order)
    {
        $WOrderID = $order->getWOrderID();

        if(!$WOrderID)
        {
            \BN_Responses::notification("No ha sido definida la orden de compra intente nuevamente.", "error");
        }

        $order->update_status_success();

        // TODO notificacion?
//        (new Notifications())->success_order_wuser($order, $delivery);
    }

    function cancel_order(Order $order, string $reason, string $comment = "")
    {
        $WOrderID = $order->getWOrderID();

        if(!$WOrderID)
        {
            \BN_Responses::notification("No ha sido definida la orden de compra intente nuevamente.", "error");
        }

        $order->update_status_cancel($order->getWOrderID(), $reason, $comment);

//        (new Notifications($order))->cancel_order_wuser();
    }

    function cancel_order_undo(Order $order)
    {
        $WOrderID = $order->getWOrderID();

        if(!$WOrderID)
        {
            \BN_Responses::notification("No ha sido definida la orden de compra intente nuevamente.", "error");
        }

        if($order->getWOrderPaymentStatus())
        {
            $order->update_status_payment($order->getWOrderID());
        }
        else
        {
            $order->update_status_pending($order->getWOrderID());
        }
    }
}