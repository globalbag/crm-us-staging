<?php


use App\Products\Brands\Brand;

class nv_products_brands extends nv_products_shared
{

    function config_custom()
    {
        $this->ModuleUrl .= "brands/";

    }

    function cmd_index()
    {
        $table = new \novut\ui\table($this->FormID);
        $table->setField($table->field('BrandID', 'ID', '5%'));
        $table->setField($table->field('BrandName', 'Nombre', '94%'));


        /** @var Brand $data_raw */
        foreach (\App\Products\Brands\Brands::list() as $data_raw)
        {
            $data = $data_raw->export();
            $data['_BrandID'] = $data['BrandID'];

            $data['BrandID'] = "<div class='text-center'><a href=\"{$this->ModuleUrl}brand/?BrandID={$data['_BrandID']}\">{$data['_BrandID']}</a></div>";
            $table->row_fill($data);
        }


        $view_data['table'] = $table->getTable();

        $this->layout->setLayoutAction($this->layout->link()->setLabel('Agregar')->setRoute(null, ['cmd' => 'new']));

        $this->layout->render($this->views->load_render('index', $this->view_data_presets($view_data)), "Cat&aacute;logo de Marcas");

    }


    function cmd_new()
    {
        $this->FormID .= "New";
        $view_data = [];

        $view_data['cmd'] = 'new_add';
        \BN_Responses::modal($this->views->load_render('brand/form', $this->view_data_presets($view_data)), "Agregar Marca");

    }

    function cmd_new_add()
    {
        $this->FormID .= "New";
        $validation = \BN_Forms::validation($this->FormID);
        $validation->setRequiredField('BrandName');
        $validation->setRequiredField('BrandCode');


        if ($this->input['BrandName'] && \App\Products\Brands\Brands::name_exist($this->input['BrandName']))
        {
            $validation->setError('BrandName', 'El nombre ya existe');
        }

        if ($this->input['BrandCode'] && \App\Products\Brands\Brands::name_exist($this->input['BrandCode']))
        {
            $validation->setError('BrandCode', 'El alias ya existe');
        }

        $validation->validate($this->input);

        $brand = new Brand();
        $brand->setBrandName($this->input['BrandName']);
        $brand->setBrandCode($this->input['BrandCode']);
        $brand->add();

        BN_Responses::notification_success("Registro Agregado", 'reload');

    }



}
(new nv_products_brands)->init();