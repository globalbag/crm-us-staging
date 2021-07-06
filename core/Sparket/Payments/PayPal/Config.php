<?php


namespace Sparket\Payments\PayPal;


class Config extends ConfigContract
{
    public function getSecretKey(): string
    {
        return $this->localConfig()->getSecretKey();
    }

    public function getIDKey(): string
    {
        return $this->localConfig()->getIDKey();
    }

    public function getUrl(): string
    {
        return $this->localConfig()->getUrl();
    }

    protected function localConfig(): ConfigContract
    {
        return new \App\Payments\Paypal\Config;
    }

}