<?php

namespace Sparket\Orders\Cfdi;

use Aws\S3\S3Client;
use novut\tools\aws_s3;

class Cfdi
{
    //bn_invoices

    // Fecha
    // Tipo de documento
    // Url del xml
    // Url de pdf

    /** Guardar en s3 */
    /** realizar descarga */

    function get_file()
    {

//        (new \Sparket\Orders\Cfdi\Cfdi())->get_file();

        $aws_s3 = new aws_s3();
        $aws_s3->setBucketDir('test');
        $aws_s3->setFileName('sample.pdf');
        $aws_s3->pull();


//        $aws_s3->pull_url();


    }


}