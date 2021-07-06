<?php

namespace Paypal;

use Novut\Core\Url;
use Sparket\Payments\PayPal\Config;

class Api
{
    /** @var array $config_base  */
    private $config_base;
    
    /** @var string $config_mode  */
    private $config_mode;
    
    /** @var Config $config_paypal  */
    private $config_paypal;
    
    function __construct()
    {
        $this->config_paypal =  new Config();


        if (!$this->config_paypal)
        {
            \BN_Responses::dev( 'Unkwonw API PayPal config mode.');
        }
    }

    private function api_response(int $http_code = 0, array $response = [])
    {
        if($response['error'])
        {
            $error_info['message'] = $response['error_description'];
        }

        if(!$http_code)
        {
            $error_info = $error_info ? : ['message' => 'No response from api.'];

            return ['error' => true, 'error_info' => $error_info, 'http_code' => $http_code];
        }

        switch ($http_code)
        {
            case 400:

                $error_info = $error_info ? : ['message' => 'INVALID_REQUEST. Request is not well-formed, syntactically incorrect, or violates schema.'];

                return ['error' => true, 'error_info' => $error_info, 'http_code' => $http_code];
                break;

            case 401:

                $error_info = $error_info ? : ['message' => 'AUTHENTICATION_FAILURE. Authentication failed due to invalid authentication credentials.'];

                return ['error' => true, 'error_info' => $error_info, 'http_code' => $http_code];
                break;

            case 403:

                $error_info = $error_info ? : ['message' => 'NOT_AUTHORIZED. Authorization failed due to insufficient permissions.'];

                return ['error' => true, 'error_info' => $error_info, 'http_code' => $http_code];
                break;

            case 404:

                $error_info = $error_info ? : ['message' => 'RESOURCE_NOT_FOUND. The specified resource does not exist.'];

                return ['error' => true, 'error_info' => $error_info, 'http_code' => $http_code];
                break;

            case 405:

                $error_info = $error_info ? : ['message' => 'METHOD_NOT_SUPPORTED. The server does not implement the requested HTTP method.'];

                return ['error' => true, 'error_info' => $error_info, 'http_code' => $http_code];
                break;

            case 406:
                $error_info = $error_info ? : ['message' => 'MEDIA_TYPE_NOT_ACCEPTABLE. The server does not implement the media type that would be acceptable to the client.'];

                return ['error' => true, 'error_info' => $error_info, 'http_code' => $http_code];
                break;

            case 415:

                $error_info = $error_info ? : ['message' => 'UNSUPPORTED_MEDIA_TYPE. The server does not support the request payload’s media type.'];

                return ['error' => true, 'error_info' => $error_info, 'http_code' => $http_code];
                break;

            case 422:

                $error_info = $error_info ? : ['message' => 'UNPROCESSABLE_ENTITY. The API cannot complete the requested action, or the request action is semantically incorrect or fails business validation.'];

                return ['error' => true, 'error_info' => $error_info, 'http_code' => $http_code];
                break;

            case 429:

                $error_info = $error_info ? : ['message' => 'RATE_LIMIT_REACHED. Too many requests. Blocked due to rate limiting.'];

                return ['error' => true, 'error_info' => $error_info, 'http_code' => $http_code];
                break;

            case 500:

                $error_info = $error_info ? : ['message' => 'INTERNAL_SERVER_ERROR. An internal server error has occurred.'];

                return ['error' => true, 'error_info' => $error_info, 'http_code' => $http_code];
                break;

            case 503:

                $error_info = $error_info ? : ['message' => 'SERVICE_UNAVAILABLE. Service Unavailable.'];

                return ['error' => true, 'error_info' => $error_info, 'http_code' => $http_code];
                break;

            default:
                return ['success' => true, 'data' => $response];
                break;
        }

    }

    private function getCurlToken()
    {
//        $token_expires = $this->config_paypal['token_expires'];
//        $token_code = $this->config_paypal['token_code'];

//        if($token_expires)
//        {
//            $expires_time = strtotime($token_expires);
//            $current_time = strtotime(date('Y-m-d H:i:s'));
//
//            if($expires_time > $current_time && $token_expires)
//            {
//                return $token_code;
//            }
//        }

        if (!$this->config_paypal->getIDKey() || !$this->config_paypal->getSecretKey())
        {
            \BN_Responses::dev( 'Unkwonw API PayPal credentials.');
        }

        $url = trim($this->config_paypal->getUrl(), "/") . "/v1/oauth2/token";

        $data = "grant_type=client_credentials";

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->config_paypal->getIDKey()}:{$this->config_paypal->getSecretKey()}");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        // ejecuta solicitud
        $output = curl_exec($ch);

        // obtiene respuesta
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        // parse a respuesta
        $headers = substr($output, 0, $header_size);
        $response = substr($output, $header_size);

        curl_close($ch);

        $response = \BN_Coders::json_decode($response);

        $response = $this->api_response($http_code, $response);

        if($response['error'])
        {
            \BN_Responses::dev($response['error_info']['message']);
        }

        $token_code = $response['data']['access_token'];

        $expires_seconds = $response['data']['expires_in'];

        $token_expires = date("Y-m-d H:i:s", time() + ($expires_seconds - 100));

//        $this->config_paypal['token_code'] = $token_code;
//        $this->config_paypal['token_expires'] = $token_expires;

        $this->config_base[$this->config_mode] = $this->config_paypal;

        \BN::param_update('PayPalAPIConfig', $this->config_base, 'json');

        return $token_code;
    }

    function curlWrap($url, $data = [], $action = 'GET')
    {
        $token_code = $this->getCurlToken();

        if (!$this->config_paypal->getIDKey() || !$this->config_paypal->getSecretKey() || !$token_code)
        {
            \BN_Responses::dev( 'Unkwonw API PayPal credentials.');
        }

        $url = trim($this->config_paypal->getUrl(), "/") . "/" . $url;
        
        if (is_array($data))
        {
            $data = \BN_Coders::json_encode($data);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        if ($data && \BN_Coders::json_decode($data) && ($action == 'GET' || $action == 'DELETE'))
        {
            $input_params = "";
            
            foreach (\BN_Coders::json_decode($data) as $key => $value) 
            {
                $input_params .= $key . '=' . $value . '&';
            }

            $input_params = trim($input_params, '&');

            curl_setopt($ch, CURLOPT_URL, $url . '?' . $input_params);
        } 
        else 
        {
            curl_setopt($ch, CURLOPT_URL, $url);
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $header = [];

        switch ($action)
        {
            case "POST":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                $header = array('Content-type: application/json', "Content-Length: " . strlen($data));
                break;
            case "GET":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                break;
            case "PUT":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                $header = array('Content-type: application/json', "Content-Length: " . strlen($data));
                break;
            case "DELETE":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            default:
                break;
        }

        array_push($header, "Authorization: Bearer {$token_code}");

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $output = curl_exec($ch);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $response = \BN_Coders::json_decode($output);

        $response = $this->api_response($http_code, $response);

        return $response;
    }

}