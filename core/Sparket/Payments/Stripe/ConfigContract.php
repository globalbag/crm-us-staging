<?php


namespace Sparket\Payments\Stripe;


abstract class ConfigContract
{
    abstract public function getSecretKey(): string;
    abstract public function getPublicKey(): string;
}