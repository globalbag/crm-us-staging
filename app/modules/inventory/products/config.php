<?php

class inventory_products_config
{
    use \novut\core;

    public function cmd_config()
    {
        $this->FormID .= "ProductConfig";

        $js = [];
        $config = $this->getParam();

        $js[] = BN_Forms::setValueText('bucket', $config['bucket'], $this->FormID);
        $js[] = BN_Forms::setValueText('region', $config['region'], $this->FormID);
        $js[] = BN_Forms::setValueText('key', $config['key'], $this->FormID);
        $js[] = BN_Forms::setValueText('secret', $config['secret'], $this->FormID);

        BN_Responses::modal($this->views->load_render('config/config', $this->view_data_presets()), "Configuraci&oacute;n", $js);
    }

    public function cmd_config_set()
    {

        $this->FormID .= "ProductConfig";
        // Validate Form

        $Validation['bucket']           = 'required';
        $Validation['region']           = 'required';

        BN_Validation::Wizard($this->FormID, $this->input, $Validation);


        $this->setConfig($this->input);

        BN_Responses::notification_success("Cambios Aplicados", 'reload');
    }


    function setConfig($ConfigSet)
    {
        $ProductConfig = $this->getParam();

        $ProductConfig['bucket']         = $ConfigSet['bucket'];
        $ProductConfig['region']          = $ConfigSet['region'];
        $ProductConfig['key']             = $ConfigSet['key'];
        $ProductConfig['secret']          = $ConfigSet['secret'];

        $this->setParam($ProductConfig);
    }

    private function setParam($ParamContent)
    {
        $ParamContent = BN_Coders::json_encode($ParamContent);
        BN::param_update('WebProductsConfig', $ParamContent, 'json', true);
    }


    function getParam()
    {
        $param =  BN::param('WebProductsConfig');
        $param = BN_Coders::json_decode($param);

        return $param;
    }
}
(new inventory_products_config)->init();