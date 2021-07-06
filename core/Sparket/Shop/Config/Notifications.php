<?php

namespace Sparket\Shop\Config;

class Notifications
{
    static function getEmailList()
    {
        return Config::getConfigInfo()['notifications'];
    }

}