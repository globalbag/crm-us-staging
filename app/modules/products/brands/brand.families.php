<?php


class nv_products_brands_brand_families extends nv_products_brands_brand
{

    /** @var \App\Products\Brands\Family $bfamily */
    private $bfamily;

    private $param_names;

    function config_custom()
    {
        parent::config_custom(); // TODO: Change the autogenerated stub

        $this->FormID .= "Families";

    }


    function getBFamilyInfo($BFamilyID = null)
    {
        if (!$this->brand)
        {
            $this->getBrandInfo();
        }


        $this->bfamily = new \App\Products\Brands\Family($this->brand, $BFamilyID ? : $this->input['BFamilyID']);

        if (!$this->bfamily->getBFamilyID())
        {
            BN_Responses::alert_error('El par&aacute;metro no existe.');
        }
    }

    function cmd_families()
    {


        $view_data = [];
        $js = [];


        $table = new \Novut\Tools\Tables\Table($this->FormID);
//        $table = new novut\ui\table($this->FormID);
        $table->addField('BFamilyName', 'Nombre', '70%');
        $table->addField('BFamilyParamsNames', 'Par&aacute;metros', '30%');

        foreach (\App\Products\Brands\Families::list($this->brand) as $data)
        {
            $data = $data->export();

            if ($data['BFamilyParams'])
            {
                $data['BFamilyParamsNames'] = [];
                foreach (\BN_Coders::json_decode($data['BFamilyParams']) as $BParamID)
                {
                    $data['BFamilyParamsNames'][] = $this->getParamName($BParamID);
                }
                $data['BFamilyParamsNames'] = implode(", ", $data['BFamilyParamsNames']);
            }
            $data['BFamilyName'] = "<a href=\"#\" onclick=\"".BN_JSHelpers::CMDRoute('', ['cmd' => 'families_edit', 'BrandID' => $data['BrandID'], 'BFamilyID' => $data['BFamilyID']])."; return false;\">{$data['BFamilyName']}</a>";
            //$data['BFamilyType'] = \App\Products\Brands\Families::getTypes()[$data['BFamilyType']];
            //$data['Action'] = $table->context_menu()->placeholder($data['BFamilyID']);
            $table->addRow($data);
        }

        //$table->context_menu()->add_menu("Editar", 'brand_param_edit');
        //$table->context_menu()->add_menu("Eliminar", 'brand_param_delete');
        $view_data['table'] = $table->getTable();


        $this->layout->selectTab('families');
        $this->layout->setWebLib('codemirror');
        $this->layout->render($this->views->load_render('brand/families/index', $this->view_data_presets($view_data)) ,"Par&aacute;metros",$js);

    }

    function cmd_families_new()
    {
        $this->FormID .= "New";
        $js = [];
        $view_data = [];
        $view_data['param_list'] =  \App\Products\Brands\Params::list($this->brand, null, (new \Novut\Db\OrmOptionsGroup())->setExport());

        $js[] = BN_Forms::setValueText('BrandID', $this->brand->getBrandID(), $this->FormID);
        $view_data['cmd'] = "families_new_add";

        $this->response->modal($this->views->load_render('brand/families/form', $this->view_data_presets($view_data)), 'Agregar Familia', $js)->setWidth(800)->render();
    }

    function cmd_families_new_add()
    {
        $this->FormID .= "New";

        $validation = BN_Forms::validation($this->FormID, $this->input);
        $validation->setRequiredField('BFamilyName');
        $validation->fieldValidation('BFamilyName', $this->input['BFamilyName'], function ($name)
        {
            return \App\Products\Brands\Families::name_exist($this->brand, trim($name)) ? false : true;
        }, "El nombre ya existe");


        $validation->validate();

        // images
        $image_url = $this->addImage();


        $family = new \App\Products\Brands\Family($this->brand);
        $family->setBFamilyName($this->input['BFamilyName']);
        foreach ($this->input['BFamilyParams'] as $BParamID)
        {
            $family->addBFamilyParam($BParamID);
        }


        // Images
        $family->removeAllBFamilyImages();
        $images = $this->input["images"];
        if($image_url)
        {
            $images[count($images)] = $image_url;
        }

        $images = json_encode($images);
        $family->setBFamilyImages($images);

        $family->add();

        $this->response->notification_success('Registro Agregado', 'reload')->render();

    }

    function cmd_families_edit()
    {
        $this->FormID .= "Edit";
        $this->getBFamilyInfo();

        $js = [];
        $view_data = [];
        $FamilyParams = [];

        $view_data['param_list'] =  \App\Products\Brands\Params::list($this->brand, null, (new \Novut\Db\OrmOptionsGroup())->setExport());
        $view_data['images_list'] =  json_decode($this->bfamily->getBFamilyImages(), true);

        $js[] = BN_Forms::setValueText('BrandID', $this->brand->getBrandID(), $this->FormID);
        $js[] = BN_Forms::setValueText('BFamilyID', $this->bfamily->getBFamilyID(), $this->FormID);

        $js[] = BN_Forms::setValueText('BFamilyName', $this->bfamily->getBFamilyName(), $this->FormID);

        $FamilyParams = $this->bfamily->getBFamilyParams() ? ( is_string($this->bfamily->getBFamilyParams()) ? \BN_Coders::json_decode($this->bfamily->getBFamilyParams()) : $this->bfamily->getBFamilyParams() ) : [];

        if ($FamilyParams)
        {
            foreach ($FamilyParams as $BParamID)
            {
                $js[] = \BN_Forms::setValueCheckbox("BFamilyParams_" . $BParamID, true);
            }
        }



        $view_data['cmd'] = "families_edit_save";
        $view_data['BFamilyID'] = $this->bfamily->getBFamilyID();

        $this->response->modal($this->views->load_render('brand/families/form', $this->view_data_presets($view_data)), 'Editar Familia', $js)->setWidth(800)->render();
    }



    function cmd_families_edit_save()
    {
        $this->FormID .= "Edit";
        $this->getBFamilyInfo();

        $validation = BN_Forms::validation($this->FormID, $this->input);
        $validation->setRequiredField('BFamilyName');
        $validation->fieldValidation('BFamilyName',  $this->input['BFamilyData'],function ($name)
        {
            return \App\Products\Brands\Families::name_exist($this->brand, trim($name), $this->bfamily->getBFamilyID()) ? false : true;
        }, "El nombre ya existe");


        // if a new picture is added
        $image_url = $this->addImage();

        // params

        $param = new \App\Products\Brands\Family($this->brand, $this->bfamily->getBFamilyID());
        $param->setBFamilyName($this->input['BFamilyName']);
        //$param->setBFamilyImages();

        $param->removeAllBFamilyParam();
        foreach ($this->input['BFamilyParams'] as $BParamID)
        {
            $param->addBFamilyParam($BParamID);
        }

        // Images
        $param->removeAllBFamilyImages();
        $images = $this->input["images"];
        if($image_url)
        {
            $images[count($images)] = $image_url;
        }

        $images = json_encode($images);
        $param->setBFamilyImages($images);


        $param->save();

        BN_Responses::notification_success("Cambios Aplicados", 'BN.WinClose()');

    }

    function cmd_families_delete()
    {
        $this->getBFamilyInfo();

        if (!$this->input['confirm'])
        {
            $this->response->confirm_simple("&iquest;Deseas eliminar este registro?", 'confirm');
        }

        $param = new \App\Products\Brands\Family($this->brand, $this->bfamily->getBFamilyID());
        $param->cancel();

        $this->response->notification_success('Cambios Aplicados', 'reload')->render();

    }

    private function data_input_decode()
    {
        return trim(base64_decode($this->input['BFamilyData']));
    }


    private function getParamName($BParamID)
    {
        if (!isset($this->param_names[$BParamID]))
        {
            $this->param_names[$BParamID] = \App\Products\Brands\Params::getName($this->brand, $BParamID);
        }

        return $this->param_names[$BParamID];
    }

    private function addImage()
    {
        if($_FILES["BFamilyImages"]["name"])
        {
            $ext = ["jpg", "png", "jpeg", "JPG", "PNG", "JPEG"];

            $type = explode('/', $_FILES["BFamilyImages"]["type"]);

            if(!in_array(end($type), $ext))
            {
                $this->response->alert_error('Formatos v&aacute;lidos : '.implode(', ', $ext). ".")->render();
            }

            // files
            $image_file = new \Novut\Tools\Files\S3;
            $image_file->setBucket('Test');
            $image_file->setAsPublic();
            $image_file->setFileContent(file_get_contents($_FILES['BFamilyImages']['tmp_name']));
            $image_file->setFileName("brand/families/images/".$this->input["BFamilyName"]."_".date('YmdHis'));
            $url = $image_file->push();

            // todo remove simular una url, despues remover
            if(!$url)
            {
                //$pdf_url = "https://www.invesa.com/wp-content/themes/invesa-template/html5blank-stable/img/prod.jpg";
                //$pdf_url = "https://datanyze-logos.s3.amazonaws.com/technologies/1e232db07d42776be901941da57c4c3813f7db4e.png";
                //$pdf_url = "https://cdn.freebiesupply.com/logos/large/2x/phpstorm-1-logo-black-and-white.png";
                //$pdf_url = "https://financesonline.com/uploads/2019/08/phpstorm_logo1.png";
                //$pdf_url = "https://www.pngkey.com/png/full/258-2582799_notepad-phpstorm-logo.png";
                //$pdf_url = "https://iconape.com/wp-content/files/hv/89485/png/phpstorm.png";

                // navicat
                //$pdf_url = "https://seekvectorlogo.com/wp-content/uploads/2017/12/oracle-vector-logo.png";
                $url = "https://www.kindpng.com/picc/m/0-3498_oracle-cloud-icon-png-transparent-png.png";

            }

            return $url;
        }
    }


}
