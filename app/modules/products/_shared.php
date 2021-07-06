<?php

class nv_products_shared
{

    use \novut\core;

    function config_common()
    {
        $this->ModuleUrlRoot = \BN_Request::getBaseUrl()."products/";
        $this->ModuleUrl = \BN_Request::getBaseUrl()."products/";

        $this->layout->addLayoutBreadcrumbs("Familias")->setUrl($this->ModuleUrlRoot."families/");

    }

    function getBrandInfo()
    {
        $this->brand = new \App\Products\Brands\Brand($this->input['BrandID']);

        if (!$this->brand->getBrandID())
        {
            BN_Responses::alert_error('La marca no existe.');
        }
    }



    function getFamilyInfo()
    {
        $this->family = new \App\Products\Brands\Family($this->brand, $this->input['BFamilyID']);


        if (!$this->family->getBFamilyID())
        {
            BN_Responses::alert_error('La familia no existe.');
        }
    }

    private function data_input_decode()
    {
        return trim(base64_decode($this->input['BFamilyData']));
    }



    function base_url_params($data = null)
    {
        $data = $data && is_array($data) ? $data : [];

        $data['BrandID'] = $this->brand->getBrandID();
        $data['BFamilyID'] = $this->family->getBFamilyID();

        return $data;
    }
}
