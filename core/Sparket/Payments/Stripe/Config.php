<?php


namespace Sparket\Payments\Stripe;


class Config extends ConfigContract
{
    public function getSecretKey(): string
    {
        return $this->localConfig()->getSecretKey();
    }

    public function getPublicKey(): string
    {
        return $this->localConfig()->getPublicKey();
    }

    protected function localConfig(): ConfigContract
    {
        return new \App\Payments\Stripe\Config;
    }

}