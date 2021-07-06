<?php


namespace App\Products;


use Novut\Tools\Files\S3Options;

class Config
{
    /**
     * @return S3Options
     */
    static function getPublicBucket()
    {
        $data =  \BN::param('AWSConfig', 'json');
        $bucket_name = $data['public_bucket'] ? : $data['default_bucket'];

        return new S3Options((string) $bucket_name);

    }

    /**
     * @return S3Options
     */
    static function getPrivateBucket()
    {

        $data =  \BN::param('AWSConfig', 'json');
        $bucket_name = $data['private_bucket'] ? : $data['default_bucket'];

        return new S3Options((string) $bucket_name);

    }
    
    static function image_extensions()
    {
        return [
            'jpg',
            'png',
            'jpeg',
            'webp'
        ];
    }
}