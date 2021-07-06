<?php

chdir(dirname(dirname(__DIR__)));



if (file_exists(dirname(getcwd())."/ambar/ambar-muki-fw/"."/lib/app-cli.php"))
{
    require_once dirname(getcwd())."/ambar/ambar-muki-fw/"."/lib/app-cli.php";
}
else
{
    die('No es posible cargar el core.');
}

if (!class_exists('BN_Var'))
{
    die("\napp not found");
}





class Ambar_App extends BN_CLI_App
{


}

class Custom_BN
{


}

class Custom_BN_Load
{


}



$app = new Ambar_App();
$app->bootstrap();