<?php

class BN_Mail extends \Novut\Tools\Email\Core
{
    protected function defaults()
    {
//        $this->setDefaultEngine('google');
//        $this->disableEngine('smtp');

        $this->setDefaultEngine('smtp');
        $this->disableEngine('google');

    }

}