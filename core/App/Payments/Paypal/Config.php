<?php


namespace App\Payments\Paypal;


class Config extends \Sparket\Payments\Paypal\ConfigContract
{
    public function getSecretKey(): string
    {
        $secret = \BN_Var::$Config['Misc']['paypal']['client_secret'];

        if (!$secret)
        {
            \BN_Responses::dev("PayPal secret key is missing (app.yml|misc|paypal|client_secret");
        }

        return (string) $secret;
    }

    public function getIDKey(): string
    {
        $id_key = \BN_Var::$Config['Misc']['paypal']["client_id"];

        if (!$id_key)
        {
            \BN_Responses::dev("PayPal id key is missing (app.yml|misc|paypal|client_id");
        }

        return (string) $id_key;
    }

    public function getUrl(): string
    {
        $public_key = \BN_Var::$Config['Misc']['paypal']['url'];

        if (!$public_key)
        {
            \BN_Responses::dev("PayPal url is missing (app.yml|misc|paypal|url");
        }

        return (string) $public_key;
    }

}