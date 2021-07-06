<?php

$home = new home_index;
$home->cmd_index();

class home_index
{

    function cmd_index()
    {

        $document = "";

        $BNPrint = new BN_Print();
        $BNPrint->WebLib('calendar');
        $BNPrint->render($document);

    }

}