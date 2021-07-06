<?php


class inventory_families_family_params extends inventory_families_family
{

    /** @var \App\Products\Brands\FamilyParam $f_param */
    protected $f_param;


    private function getParamName($BParamID)
    {
        if (!isset($this->param_names[$BParamID]))
        {
            $this->param_names[$BParamID] = \App\Products\Brands\Params::getName($this->brand, $BParamID);
        }

        return $this->param_names[$BParamID];
    }


    function getFParamInfo()
    {
        $this->f_param = new \App\Products\Brands\FamilyParam($this->family, $this->input['BFParamID']);


        if (!$this->f_param->getBFParamID())
        {
            BN_Responses::alert_error('El parámetro de la familia no existe.');
        }
    }


    function cmd_params()
    {

        $view_data = [];
        $js = [];

        $param_list = \App\Products\Brands\FamilyParams::list($this->family);

        $table = new \Novut\Tools\Tables\Table($this->FormID);

        if (sizeof($param_list) >= 2)
        {
            $table->addField('Sort', '&nbsp;', '1%');
        }

        $table->addField('BParamName', 'Nombre', '98%');
        $table->addField('Actions', '&nbsp;', '1%');


        foreach ($param_list as $param)
        {

            $data = $param->export();

            $param_details = new \App\Products\Brands\Param($this->brand, $param->getBParamID());
            $data['BParamName'] = $param_details->getBParamName();
            $data['BParamCode'] = $param_details->getBParamCode();

            if (sizeof($param_list) >= 2)
            {
                $data['Sort'] = "<div class='sort-placeholder'><i class=\"fas fa-sort\" style='cursor: ns-resize'></i> </div>";
                $data['__BN_Attr__']['Sort']['data-sort'] = $param->getBFParamID();

            }

            $data['Actions'] = $table->context_menu()->placeholder($data['BFParamID']);

            $table->addRow($data);
        }

        $table->context_menu()->add_menu("Eliminar", 'brand_families_params_delete');
        $view_data['table'] = $table->getTable();
        $view_data['total_items'] = $table->getTotalRows();


        $this->layout->selectTab('params');
        $this->layout->setWebLib('sortable');
        $this->layout->setBrowserTitle("{$this->family->getBFamilyName()} - Parámetros");
        $this->layout->render($this->views->load_render('params/index', $this->view_data_presets($view_data)) ,"Parámetros",$js);
    }

    function cmd_params_sort()
    {
        $items = \BN_Coders::json_decode(base64_decode($this->input['items']));
        if ($items && is_array($items))
        {
            \App\Products\Brands\FamilyParams::sort_items($this->family, $items);
        }

        BN_Responses::routeSuccess();
    }

    function cmd_params_new()
    {

        $this->FormID .= "ParamNew";
        $js = [];
        $view_data = [];

        foreach (\App\Products\Brands\FamilyParams::list($this->family, null, (new \Novut\Db\OrmOptionsGroup)->setExport()) as $fparam_info)
        {
            $b_param_exist[$fparam_info['BParamID']] = $fparam_info['BParamID'];
        }

        foreach (\App\Products\Brands\Params::list($this->brand, null, (new \Novut\Db\OrmOptionsGroup())->setExport()) as $param_info)
        {
            if (!$b_param_exist[$param_info['BParamID']])
            {
                $param_list[$param_info['BParamID']] = $param_info['BParamName'];
            }

        }

        if (!$param_list)
        {
            BN_Responses::alert_error("No hay parámetros sin asignar.");
        }

        $populate = new \Novut\UI\Forms\Populate($this->FormID);
        $populate->setSelect2('BParamID', BN_Forms::option_empty().BN_Forms::option_list_render($param_list));
        $populate->setValueText('BrandID', $this->brand->getBrandID());
        $populate->setValueText('BFamilyID', $this->family->getBFamilyID());
        $js = $populate->getJs();

        $view_data['cmd'] = "params_new_add";

        $this->response->modal($this->views->load_render('params/form', $this->view_data_presets($view_data)), 'Agregar Parámetro', $js)->render();

    }


    function cmd_params_new_add()
    {

        $this->FormID .= "ParamNew";

        $validation = BN_Forms::validation($this->FormID, $this->input);
        $validation->setRequiredField('BParamID');

        $param = new \App\Products\Brands\Param($this->brand, $this->input['BParamID']);

        $validation->fieldValidation('BParamID', $this->input['BParamID'], function ($BParamID) use ($param)
        {
            return $param->getBParamID() ? true : false;
        }, "El parámetro no existe");

        $validation->fieldValidation('BParamID', $this->input['BParamID'], function ($BParamID)
        {
            return \App\Products\Brands\FamilyParams::param_exist($this->family, $BParamID) ? false : true;
        }, "El parámetro ya existe");


        $validation->validate();

        $FamilyParams = new \App\Products\Brands\FamilyParam($this->family);
        $FamilyParams->add($param);

        $this->family->updateParams();

        $this->response->notification_success('Registro Agregado', 'reload')->render();

    }


    function cmd_params_delete()
    {

        $this->getFParamInfo();

        if (!$this->input['confirm'])
        {
            \BN_Responses::confirm_quick("¿Deseas eliminar este registro?", 'confirm');
        }

        $this->f_param->cancel($this->f_param->getBFParamID());

        $this->response->notification_success('Cambios Aplicados')->reload()->render();

    }

}
