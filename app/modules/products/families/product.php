<?php


class inventory_families_product extends nv_products_shared
{

    use \novut\layout_10;

    /** @var \App\Products\Brands\Brand $brand */
    protected $brand;


    /** @var \App\Products\Brands\Family $family */
    protected $family;

    /** @var \App\Products\Product\Product $product */
    protected $product;


    function config_custom()
    {
        $this->ModuleUrl .= "families/product/";

        $this->getBrandInfo();
        $this->getFamilyInfo();
        $this->getProductInfo();

        $this->layout->addLayoutBreadcrumbs($this->family->getBFamilyName())->setUrl($this->ModuleUrlRoot."families/family/?" . http_build_query($this->base_url_params()));
        $this->layout->addLayoutBreadcrumbs($this->product->getProductName())->setUrl($this->ModuleUrlRoot."families/product/?" . http_build_query($this->base_url_params_product()));

        $this->layout->setLayoutOptions(
            [

                $this->layout->option_list()->setOptions([
                    $this->layout->option()
                        ->setLabel('Editar Producto')
                        ->setRoute($this->ModuleUrlRoot."families/product/", $this->base_url_params_product(['cmd' => 'edit'])),
                    $this->layout->option()
                        ->setLabel('Eliminar Producto')
                        ->setRoute($this->ModuleUrlRoot."families/product/", $this->base_url_params_product(['cmd' => 'delete']))
                ])->setLabel('Herramientas')

            ]
        );

        $this->layout->setSidebar($this->sidebar_content());

        $this->layout->setTab($this->layout->tab('prices', 'Precios')->setUrlMaker(null, 'prices', $this->base_url_params_product()));
        $this->layout->setTab($this->layout->tab('content', 'Contenido')->setUrlMaker(null, 'content', $this->base_url_params_product()));
        $this->layout->setTab($this->layout->tab('images', 'Imágenes')->setUrlMaker(null, 'images', $this->base_url_params_product()));

        $this->layout->setSharedContent($this->views->load_render('products/product/common',$this->view_data_presets()));

    }


    protected function sidebar_content()
    {

        $param_list = [];
        $product_params = \BN_Coders::json_decode($this->product->getBFamilyData());

        foreach(\App\Products\Brands\Families::paramCollection($this->brand, $this->family) as $param_info)
        {
            $param_list[$param_info['BParamCode']] = ['name' => $param_info['BParamName'], 'value' => $product_params[$param_info['BParamID']]['value'] . ($param_info['BParamComment'] ? " {$param_info['BParamComment']}" : "")];
        }

        //print_r([$param_list]); exit;

        $view_data['ProductInfo'] = $this->product->export();
        $view_data['param_list'] = $param_list;

        return $this->views->load_render('products/sidebar',$this->view_data_presets($view_data));
    }




    function getProductInfo()
    {
        $this->product = new App\Products\Product\Product();
        $this->product->find_by_query((new \Novut\Core\Query())->setWhere('ProductID', $this->input['ProductID'])->addQuery(" AND BFamilyID = :BFamilyID", 'BFamilyID', $this->family->getBFamilyID()));


        if (!$this->product->getProductID())
        {
            BN_Responses::alert_error('El producto no existe.');
        }
    }

    function base_url_params_product($data = null)
    {
        $data = $data && is_array($data) ? $data : [];

        $data['BrandID'] = $this->brand->getBrandID();
        $data['BFamilyID'] = $this->family->getBFamilyID();
        $data['ProductID'] = $this->product->getProductID();

        return $data;
    }

    function view_data_presets_custom(array $data = null)
    {
        $data['BrandID'] = $this->brand->getBrandID();
        $data['BrandInfo'] = $this->brand->export();

        $data['BFamilyID'] = $this->family->getBFamilyID();
        $data['BFamilyInfo'] = $this->family->export();

        if ($this->product)
        {
            $data['ProductID'] = $this->product->getProductID();
            $data['ProductInfo'] = $this->product->export();
        }

        return $data;
    }



    function cmd_index()
    {
        //\App\Products\Product\Params::sync_data($this->product);

        \BN_Responses::redirect_http($this->ModuleUrl, ['cmd' => 'prices', 'ProductID' => $this->product->getProductID(), 'BrandID' => $this->brand->getBrandID(), 'BFamilyID' => $this->family->getBFamilyID()]);
    }



    function cmd_edit()
    {
        $this->FormID .= "ProductEdit";
        $js = [];
        $view_data = [];

        $params = \App\Products\Brands\Families::paramCollection($this->brand, $this->family);
        $product_params = \BN_Coders::json_decode($this->product->getBFamilyData());

        $populate = new \Novut\UI\Forms\Populate($this->FormID, $this->product);
        $populate->setValueText('BrandID');
        $populate->setValueText('BFamilyID');
        $populate->setValueText('ProductID');
        $populate->setValueText('ProductName');
        $populate->setValueText('ProductCode');
        $populate->setValueText('ProductDescription');
        $populate->setValueText('ProductQtyPP');

        foreach ($params as $param_info)
        {
            if ($param_info['BParamType'] == 'select')
            {
                $populate->setValueSelect2("#PFamilyData_{$param_info['BParamID']}", $product_params[$param_info['BParamID']]['Value']);
            }
        }

        $populate->setValueText('ProductName');

        $js = $populate->getJs();


        $view_data['params'] = $params;
        $view_data['cmd'] = "edit_save";

        $this->response->modal($this->views->load_render('products/form', $this->view_data_presets($view_data)), 'Editar Producto', $js)->render();

    }


    function cmd_edit_save()
    {


        $this->FormID .= "ProductEdit";

        $validation = BN_Forms::validation($this->FormID, $this->input);

        $validation->setRequiredField('ProductName');
        $validation->setRequiredField('ProductCode');
        $validation->setRequiredField('ProductQtyPP');


        $validation->fieldValidation('ProductName', $this->input['ProductName'], function ($ProductName)
        {

            $product = \App\Products\Product\Products::name_exist(trim($ProductName), $this->product->getProductID());
            if ($product && $product->getProductID())
            {
                return (new \Novut\Tools\Forms\ValidationError('ProductName', "El nombre ya existe. (ID: {$product->getProductID()})"));
            }
            else
            {
                return true;
            }

        });

        $validation->fieldValidation('ProductCode', $this->input['ProductCode'], function ($ProductCode)
        {
            $product = \App\Products\Product\Products::code_exist(trim($ProductCode), $this->product->getProductID());
            if ($product && $product->getProductID())
            {
                return (new \Novut\Tools\Forms\ValidationError('ProductCode', "El SKU ya existe. (ID: {$product->getProductID()})"));
            }
            else
            {
                return true;
            }

        });


        $validation->validate();
        $product = new App\Products\Product\Product($this->product->getProductID());
        $product->setProductName($this->input['ProductName']);
        $product->setProductCode($this->input['ProductCode']);
        $product->setProductDescription($this->input['ProductDescription']);
        $product->setProductQtyPP($this->input['ProductQtyPP']);
        $product->save();

        // family
        $family = new \App\Products\Product\Family($product);
        foreach (\App\Products\Brands\Families::paramCollection($this->brand, $this->family) as $param_info)
        {
            $family->addParam($param_info['BParamID'], $param_info['BParamCode'], $this->input['PFamilyData'][$param_info['BParamID']]);
        }


        $family->save();

        // update product
        $product->update();

        $this->response->notification_success('Cambios Aplicados', 'reload')->render();

    }


    function cmd_delete()
    {

        if (!$this->input['confirm'])
        {
            \BN_Responses::confirm_quick("¿Deseas eliminar este registro?", 'confirm');
        }

        $product_images = \App\Products\Product\Images::list($this->product);
        $product_prices = \App\Products\Product\Prices::list($this->product);

        if ($product_images && sizeof($product_images) > 0)
        {
            /** @var \App\Products\Product\Image $image */
            foreach ($product_images as $image)
            {
                $image->cancel($image->getPImageID());
            }
        }


        if ($product_prices && sizeof($product_prices) > 0)
        {
            /** @var \App\Products\Product\Price $price */
            foreach ($product_prices as $price)
            {
                $price->cancel($price->getPPriceID());
            }
        }

        $this->product->cancel($this->product->getProductID());
        $this->response->notification_success('Cambios Aplicados')->redirect(BN_Request::getBaseUrl()."products/families/family/?cmd=products&BrandID={$this->product->getBrandID()}&BFamilyID={$this->product->getBFamilyID()}")->render();

    }



}
(new inventory_families_product)->init();