<?php


namespace App\Config;


class Email extends \Novut\Config\Email
{
    function getEngines(): array
    {
        $engines['sendgrid'] = \Novut\Tools\Email\Gateway\SendGrid::class;
        return $engines;
    }
}