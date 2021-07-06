<?php


class nv_products_brands_brand extends nv_products_shared
{

    use \novut\layout_10;

    /** @var \App\Products\Brands\Brand $brand */
    protected $brand;

    function config_custom()
    {
        $this->ModuleUrl .= "brands/brand/";

        $this->getBrandInfo();


        $this->layout->setLayoutOptions(
            [

                $this->layout->option_list()->setOptions([
                    $this->layout->option()
                        ->setLabel('Editar Marca')
                        ->setRoute(null, ['BrandID' => $this->brand->getBrandID(),'cmd' => 'edit']),
                    $this->layout->option()
                        ->setLabel('Eliminar Marca')
                        ->setRoute(null, ['BrandID' => $this->brand->getBrandID(),'cmd' => 'delete'])
                ])->setLabel('Herramientas')

            ]
        );

        $this->layout->setSidebar($this->views->load_render('brand/sidebar',$this->view_data_presets()));

        $this->layout->setTab($this->layout->tab('gdata', 'Datos Generales')->setUrlMaker(null, 'gdata', ['BrandID' => $this->brand->getBrandID()]));
        $this->layout->setTab($this->layout->tab('params', 'Par&aacute;metros')->setUrlMaker(null, 'params', ['BrandID' => $this->brand->getBrandID()]));
        $this->layout->setTab($this->layout->tab('families', 'Familias')->setUrlMaker(null, 'families', ['BrandID' => $this->brand->getBrandID()]));

        $this->layout->setSharedContent($this->views->load_render('brand/common',$this->view_data_presets()));

    }

    function view_data_presets_custom(array $data = null)
    {
        $data['BrandInfo'] = $this->brand->export();
        $data['BrandID'] = $this->brand->getBrandID();



        return $data;
    }

    function getBrandInfo()
    {
        $this->brand = new \App\Products\Brands\Brand($this->input['BrandID']);

        if (!$this->brand->getBrandID())
        {
            BN_Responses::alert_error('La marca no existe.');
        }
    }

    function cmd_index()
    {

        \BN_Responses::redirect_http($this->ModuleUrl, ['cmd' => 'gdata', 'BrandID' => $this->brand->getBrandID()]);
    }


    protected function cmd_edit()
    {
        $this->FormID .= "Edit";

        $this->getBrandInfo();

        $js = [];
        $view_data = [];
        $view_data['cmd'] = 'edit_save';


        $populate = new \Novut\UI\Forms\Populate($this->FormID);
        $populate->setMultipleValueText(['BrandID', 'BrandName', 'BrandCode'], $this->brand->export());

        $js[] = $populate->getJs();

        BN_Responses::modal($this->views->load_render('brand/form', $this->view_data_presets($view_data)), "Editar Marca", $js);

    }


    function cmd_edit_save()
    {
        $this->FormID .= "Edit";
        $this->getBrandInfo();

        $validation = \BN_Forms::validation($this->FormID);
        $validation->setRequiredField('BrandName');
        $validation->setRequiredField('BrandCode');



        if ($this->input['BrandName'] && \App\Products\Brands\Brands::name_exist($this->input['BrandName'], $this->brand->getBrandID()))
        {
            $validation->setError('BrandName', 'El nombre ya existe');
        }

        if ($this->input['BrandCode'] && \App\Products\Brands\Brands::name_exist($this->input['BrandCode'], $this->brand->getBrandID()))
        {
            $validation->setError('BrandCode', 'El alias ya existe');
        }

        $validation->validate($this->input);

        $brand = new \App\Products\Brands\Brand($this->brand->getBrandID());
        $brand->setBrandName($this->input['BrandName']);
        $brand->setBrandCode($this->input['BrandCode']);
        $brand->save();

        BN_Responses::notification_success("Cambios Aplicados", 'reload');


    }

    function cmd_delete()
    {

    }



}
(new nv_products_brands_brand)->init();