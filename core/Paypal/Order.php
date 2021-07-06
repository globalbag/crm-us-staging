<?php

namespace Paypal;

class Order
{
    function get_info($id)
    {
        $response = (new Api())->curlWrap("v2/checkout/orders/{$id}", false);

        if(!$response['success'])
        {
            \BN_Responses::notification("La orden no existe.", "error");
        }

        return $response['data'];
    }

    function get_payment($id)
    {
        $response = (new Api())->curlWrap("v2/payments/captures/{$id}", false);

        if(!$response['success'])
        {
            \BN_Responses::notification("El pago no existe.", "error");
        }

        return $response['data'];
    }

}