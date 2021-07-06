<?php


class inventory_families_family extends nv_products_shared
{

    use \novut\layout_10;

    /** @var \App\Products\Brands\Brand $brand */
    protected $brand;


    /** @var \App\Products\Brands\Family $family */
    protected $family;

    function config_custom()
    {
        $this->ModuleUrl .= "families/family/";

        $this->getBrandInfo();
        $this->getFamilyInfo();

        $this->layout->addLayoutBreadcrumbs($this->family->getBFamilyName())->setUrl($this->ModuleUrlRoot."families/family/?" . http_build_query($this->base_url_params()));

        $this->layout->setLayoutOptions(
            [

                $this->layout->option_list()->setOptions([
                    $this->layout->option()
                        ->setLabel('Editar Familia')
                        ->setRoute($this->ModuleUrlRoot."families/family/", ['BrandID' => $this->brand->getBrandID(), 'BFamilyID' => $this->family->getBFamilyID(), 'cmd' => 'edit']),
                    $this->layout->option()
                        ->setLabel('Eliminar Familia')
                        ->setRoute($this->ModuleUrlRoot."families/family/", ['BrandID' => $this->brand->getBrandID(), 'BFamilyID' => $this->family->getBFamilyID(), 'cmd' => 'delete'])
                ])->setLabel('Herramientas')



            ]
        );

        $this->layout->setSidebar($this->views->load_render('sidebar',$this->view_data_presets()));

        $this->layout->setTab($this->layout->tab('products', 'Productos')->setUrlMaker(null, 'products', $this->base_url_params()));
        $this->layout->setTab($this->layout->tab('params', 'Par&aacute;metros')->setUrlMaker(null, 'params', $this->base_url_params()));
        $this->layout->setTab($this->layout->tab('scontent', 'Descripción Corta')->setUrlMaker(null, 'scontent', $this->base_url_params()));
        $this->layout->setTab($this->layout->tab('content', 'Descripción Larga')->setUrlMaker(null, 'content', $this->base_url_params()));
        $this->layout->setTab($this->layout->tab('images', 'Imágenes')->setUrlMaker(null, 'images', $this->base_url_params()));

        $this->layout->setSharedContent($this->views->load_render('common',$this->view_data_presets()));

    }

    function view_data_presets_custom(array $data = null)
    {
        $data['BrandID'] = $this->brand->getBrandID();
        $data['BrandInfo'] = $this->brand->export();

        $data['BFamilyID'] = $this->family->getBFamilyID();
        $data['BFamilyInfo'] = $this->family->export();

        return $data;
    }



    function cmd_index()
    {

        \BN_Responses::redirect_http($this->ModuleUrl, ['cmd' => 'products', 'BrandID' => $this->brand->getBrandID(), 'BFamilyID' => $this->family->getBFamilyID()]);
    }


    function cmd_edit()
    {
        $this->FormID .= "Edit";

        $view_data = [];
        $FamilyParams = [];

        $populate = new \Novut\UI\Forms\Populate($this->FormID, $this->family);


        $populate->setValueText('BrandID');
        $populate->setValueText('BFamilyID');
        $populate->setValueText('BFamilyName');
        $populate->setValueText('BFamilyCode');
        $populate->setValueText('RowOrder');
        $populate->setValueCheckbox('BFamilyPublished');


        $js = $populate->getJs();
        $view_data['cmd'] = "edit_save";
        $view_data['FormUrl'] = $this->ModuleUrlRoot . "families/family/";

        $this->response->modal($this->views->load_render('form', $this->view_data_presets($view_data)), 'Editar Familia', $js)->render();
    }



    function cmd_edit_save()
    {
        $this->FormID .= "Edit";

        $this->input['BFamilyCode'] = \BN_Filters::code($this->input['BFamilyCode']);

        $validation = BN_Forms::validation($this->FormID, $this->input);
        $validation->setRequiredField('BFamilyName');
        $validation->setRequiredField('BFamilyCode');

        $validation->fieldValidation('BFamilyName',  $this->input['BFamilyName'],function ($name)
        {
            return \App\Products\Brands\Families::name_exist($this->brand, trim($name), $this->family->getBFamilyID()) ? false : true;
        }, "El nombre ya existe");

        $validation->fieldValidation('BFamilyCode',  $this->input['BFamilyCode'],function ($code)
        {
            return \App\Products\Brands\Families::code_exist($this->brand, trim($code), $this->family->getBFamilyID()) ? false : true;
        }, "El nombre ya existe");

        $validation->validate();


        $family = new \App\Products\Brands\Family($this->brand, $this->family->getBFamilyID());
        $family->setBFamilyName($this->input['BFamilyName']);
        $family->setBFamilyCode($this->input['BFamilyCode']);
        $family->setBFamilyPublished($this->input['BFamilyPublished'] ? true : false);
        $family->setRowOrder((int) $this->input['RowOrder']);
//        $family->setBFamilyQtyRange($this->input['BFamilyQtyRange'] ? true : false);
        $family->save();

        $this->response->notification_success('Cambios aplicados')->closeModal()->render();

    }



    function cmd_delete()
    {

        $this->getBrandInfo();
        $this->getFamilyInfo();

        $products = \App\Products\Brands\FamilyProducts::list($this->family);

        if (sizeof($products) >= 1)
        {
            $this->response->alert_error("No es posible eliminar la familia. Existen productos.")->render();
        }

        if (!$this->input['confirm'])
        {
            $this->response->confirm_simple("&iquest;Deseas eliminar este registro?", 'confirm');
        }



        $family = new \App\Products\Brands\Family($this->brand, $this->family->getBFamilyID());
        $family->cancel();

        $this->response->notification_success('Registro Eliminado')->redirect($this->ModuleUrlRoot."families/")->render();

    }




}
(new inventory_families_family)->init();