<?php


namespace Sparket\Orders;


class Tools
{
    static function RFCValidation(string $RFC): bool
    {
        return $RFC && preg_match('/^([A-ZÑ\x26]{3,4}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])([A-Z]|[0-9]){2}([A]|[0-9]){1})?$/', strtoupper($RFC)) ? true : false;
    }
}