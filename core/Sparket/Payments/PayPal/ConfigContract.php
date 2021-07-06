<?php


namespace Sparket\Payments\PayPal;

abstract class ConfigContract
{
    abstract public function getSecretKey(): string;
    abstract public function getIDKey(): string;
    abstract public function getUrl(): string;
}