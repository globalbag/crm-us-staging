<?php


class inventory_families_family_images extends inventory_families_family
{

    /** @var \App\Products\Brands\FamilyImage $family_image */
    protected $family_image;

    /** @var \Novut\Tools\Files\S3Options  $bucket */
    protected $bucket;

    function config_custom()
    {
        parent::config_custom();

        $this->bucket = \App\Products\Config::getPublicBucket();
        if (!$this->bucket->getBucket())
        {
            BN_Responses::alert_error("El bucket no está especificado.");
        }
    }


    function getFImageInfo()
    {
        $this->family_image = new \App\Products\Brands\FamilyImage($this->family, $this->input['BFImageID']);


        if (!$this->family_image->getBFImageID())
        {
            BN_Responses::alert_error('El parámetro de la familia no existe.');
        }
    }


    function cmd_images()
    {

        $view_data = [];
        $js = [];

        $image_list = \App\Products\Brands\FamilyImages::list($this->family);

        $table = new \Novut\Tools\Tables\Table($this->FormID);

        if (sizeof($image_list) >= 2)
        {
            $table->addField('Sort', '&nbsp;', '1%');
        }

        $table->addField('BFImage', '&nbsp;', '80px');
        $table->addField('BFImageDescription', 'Descripción', '98%');
        //$table->addField('Copy', '&nbsp;', '1%');
        $table->addField('BFImageDefault', 'Predeterminada', '1%');
        $table->addField('Actions', '&nbsp;', '1%');


        foreach ($image_list as $image)
        {

            $data = $image->export();

            $data['BFImage'] = "<a href='{$data['BFImageUrl']}' target='_blank' style='display:block; height: 80px; width: 80px; background: url({$data['BFImageUrl']}) center center no-repeat; background-size: cover'></a>";
            $data['BFImageDescription'] = $data['BFImageDescription'] ? : "<em>(Sin descripción)</em>";
            if ($data['BFImageDefault'])
            {
                $data['BFImageDefault'] = "<strong>Si</strong>";
            }
            else{
                $data['BFImageDefault'] = "<a href='#' onclick=\"brand_families_images_default('{$data['BFImageID']}'); return false;\">No</a>";
            }

            $data['BFImageDefault'] = "<div class='text-center'>{$data['BFImageDefault']}</div>";
            //$data['Copy'] = "<input type='text' value='{$data['BFImageUrl']}' id='copy-{$data['BFImageID']}' class='hidden' /><a href='#' onclick=\"BN.tools.copy('{$data['BFImageUrl']}'); return false;\">Copiar</a>";


            if (sizeof($image_list) >= 2)
            {
                $data['Sort'] = "<div class='sort-placeholder'><i class=\"fas fa-sort\" style='cursor: ns-resize'></i> </div>";
                $data['__BN_Attr__']['Sort']['data-sort'] = $image->getBFImageID();

            }

            $data['Actions'] = $table->context_menu()->placeholder($data['BFImageID']);

            $table->addRow($data);
        }

        $table->context_menu()->add_menu("Editar", 'brand_families_images_edit');
        $table->context_menu()->add_menu("Eliminar", 'brand_families_images_delete');
        $view_data['table'] = $table->getTable();
        $view_data['total_items'] = $table->getTotalRows();


        $this->layout->selectTab('images');
        $this->layout->setWebLib('sortable');
        $this->layout->setBrowserTitle("{$this->family->getBFamilyName()} - Imágenes");
        $this->layout->render($this->views->load_render('images/index', $this->view_data_presets($view_data)) ,"Imágenes",$js);
    }

    function cmd_images_sort()
    {
        $items = \BN_Coders::json_decode(base64_decode($this->input['items']));
        if ($items && is_array($items))
        {
            \App\Products\Brands\FamilyImages::sort_items($this->family, $items);
        }

        BN_Responses::routeSuccess();
    }

    function cmd_images_new()
    {

        $this->FormID .= "ImageNew";
        $js = [];
        $view_data = [];

        $populate = new \Novut\UI\Forms\Populate($this->FormID);
        $populate->setValueText('BrandID', $this->brand->getBrandID());
        $populate->setValueText('BFamilyID', $this->family->getBFamilyID());
        $js = $populate->getJs();

        $view_data['cmd'] = "images_new_add";

        $this->response->modal($this->views->load_render('images/form', $this->view_data_presets($view_data)), 'Agregar Imagen', $js)->render();

    }


    function cmd_images_new_add()
    {

        $this->FormID .= "ImageNew";

        $validation = BN_Forms::validation($this->FormID, $this->input);

        if ($_FILES['BFImageFile']['tmp_name'])
        {

            if (!in_array(pathinfo($_FILES['BFImageFile']['name'], PATHINFO_EXTENSION), \App\Products\Config::image_extensions()))
            {
                $validation->setError('BFImageFile', "Formato inválido. <BR>Solo se permiten los siguientes formatos: ". implode(", ", \App\Products\Config::image_extensions()).".");
            }

        }
        else{
            $validation->setRequiredField('BFImageFile');
        }


        $validation->validate();

        $file_name = \BN_Filters::fname($_FILES['BFImageFile']['name']);
        $file_name_final = "images/families/".$this->family->getBFamilyID()."-".BN::random_code(4, 4) . "-" . $file_name;

        // files
        $image_file = new \Novut\Tools\Files\S3($this->bucket);
        $image_file->setAsPublic();
        $image_file->setFileContent(file_get_contents($_FILES['BFImageFile']['tmp_name']));
        $image_file->setFileName($file_name_final);
        $url = $image_file->push();

        if ($url)
        {

            $FamilyImages = new \App\Products\Brands\FamilyImage($this->family);
            $FamilyImages->setBFImageDescription((string) $this->input['BFImageDescription']);
            $FamilyImages->setBFImageBucket($this->bucket->getBucket());
            $FamilyImages->setBFImageUrl($url);
            $FamilyImages->add();

            $this->response->notification_success('Registro Agregado', 'reload')->render();
        }
        else
        {
            $this->response->alert_error('No fue posible agregar la imágen.', 'reload')->render();
        }


    }


    function cmd_images_edit()
    {
        $this->getFImageInfo();

        $this->FormID .= "ImageEdit";
        $js = [];
        $view_data = [];

        $populate = new \Novut\UI\Forms\Populate($this->FormID);
        $populate->setValueText('BrandID', $this->brand->getBrandID());
        $populate->setValueText('BFamilyID', $this->family->getBFamilyID());
        $populate->setValueText('BFImageID', $this->family_image->getBFImageID());
        $populate->setValueText('BFImageDescription', $this->family_image->getBFImageDescription());

        $js = $populate->getJs();

        $view_data['cmd'] = "images_edit_save";

        $this->response->modal($this->views->load_render('images/edit', $this->view_data_presets($view_data)), 'Editar Imagen', $js)->render();

    }


    function cmd_images_edit_save()
    {

        $this->getFImageInfo();

        $this->FormID .= "ImageEdit";

        $validation = BN_Forms::validation($this->FormID, $this->input);
        //$validation->setRequiredField('BFImageFile');

        $validation->validate();

        $this->family_image->setBFImageDescription((string) $this->input['BFImageDescription']);
        $this->family_image->save();

        $this->response->notification_success('Cambios Aplicados', 'reload')->render();

    }


    function cmd_images_delete()
    {

        $this->getFImageInfo();

        if (!$this->input['confirm'])
        {
            \BN_Responses::confirm_quick("¿Deseas eliminar este registro?", 'confirm');
        }

        if ($this->family_image->getBFImageUrl())
        {
            $image_file = new \Novut\Tools\Files\S3($this->bucket);
            $image_file->setBucket($this->family_image->getBFImageBucket());
            $image_file->delete($this->family_image->getBFImageUrl());
        }

        $this->family_image->cancel($this->family_image->getBFImageID());
        $this->response->notification_success('Cambios Aplicados')->reload()->render();

    }


    function cmd_images_default()
    {

        $this->getFImageInfo();

        if (!$this->input['confirm'])
        {
            \BN_Responses::confirm_quick("¿Deseas establecer como predeterminada?", 'confirm');
        }

        \App\Products\Brands\FamilyImages::set_default($this->family, $this->family_image->getBFImageID());


        $this->response->notification_success('Cambios Aplicados')->reload()->render();

    }

}
