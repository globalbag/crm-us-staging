<?php


namespace App\Payments\Stripe;


class Config extends \Sparket\Payments\Stripe\ConfigContract
{
    public function getSecretKey(): string
    {
        $secret = \BN_Var::$Config['Misc']['stripe']['secret'];

        if (!$secret)
        {
            \BN_Responses::dev("Stripe Secret is missing (app.yml|Misc|Stripe|secret");
        }

        return (string) $secret;
    }

    public function getPublicKey(): string
    {
        $public_key = \BN_Var::$Config['Misc']['stripe']['public_key'];

        if (!$public_key)
        {
            \BN_Responses::dev("Stripe Public key is missing (app.yml|Misc|Stripe|public_key");
        }

        return (string) $public_key;
    }

}