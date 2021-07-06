<?php


class inventory_families_product_images extends inventory_families_product
{

    /** @var \App\Products\Product\Image $product_image */
    protected $product_image;

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


    function getPImageInfo()
    {
        $this->product_image = new \App\Products\Product\Image($this->product, $this->input['PImageID']);


        if (!$this->product_image->getPImageID())
        {
            BN_Responses::alert_error('El parámetro de la familia no existe.');
        }
    }


    function cmd_images()
    {

        $view_data = [];
        $js = [];

        $image_list = \App\Products\Product\Images::list($this->product);

        $table = new \Novut\Tools\Tables\Table($this->FormID);
        $table->setClass('table');

        if (sizeof($image_list) >= 2)
        {
            $table->addField('Sort', '&nbsp;', '1%');
        }

        $table->addField('PImage', '&nbsp;', '80px');
        $table->addField('PImageDescription', 'Descripción', '98%');
        //$table->addField('Copy', '&nbsp;', '1%');
        $table->addField('PImageDefault', 'Predeterminada', '1%');
        $table->addField('Actions', '&nbsp;', '1%');


        foreach ($image_list as $image)
        {

            $data = $image->export();

            $data['PImage'] = "<a href='{$data['PImageUrl']}' target='_blank' style='display:block; height: 80px; width: 80px; background: url({$data['PImageUrl']}) center center no-repeat; background-size: cover'></a>";
            $data['PImageDescription'] = $data['PImageDescription'] ? : "<em>(Sin descripción)</em>";
            if ($data['PImageDefault'])
            {
                $data['PImageDefault'] = "<strong>Si</strong>";
            }
            else{
                $data['PImageDefault'] = "<a href='#' onclick=\"brand_families_products_images_default('{$data['PImageID']}'); return false;\">No</a>";
            }

            $data['PImageDefault'] = "<div class='text-center'>{$data['PImageDefault']}</div>";
            //$data['Copy'] = "<input type='text' value='{$data['PImageUrl']}' id='copy-{$data['PImageID']}' class='hidden' /><a href='#' onclick=\"BN.tools.copy('{$data['PImageUrl']}'); return false;\">Copiar</a>";


            if (sizeof($image_list) >= 2)
            {
                $data['Sort'] = "<div class='sort-placeholder'><i class=\"fas fa-sort\" style='cursor: ns-resize'></i> </div>";
                $data['__BN_Attr__']['Sort']['data-sort'] = $image->getPImageID();

            }

            $data['Actions'] = $table->context_menu()->placeholder($data['PImageID']);

            $table->addRow($data);
        }

        $table->context_menu()->add_menu("Editar", 'brand_families_products_images_edit');
        $table->context_menu()->add_menu("Eliminar", 'brand_families_products_images_delete');
        $view_data['table'] = $table->getTable();
        $view_data['total_items'] = $table->getTotalRows();


        $this->layout->selectTab('images');
        $this->layout->setWebLib('sortable');
        $this->layout->setBrowserTitle("{$this->product->getProductName()} - Imágenes");
        $this->layout->render($this->views->load_render('products/product/images/index', $this->view_data_presets($view_data)) ,"Imágenes",$js);
    }

    function cmd_images_sort()
    {
        $items = \BN_Coders::json_decode(base64_decode($this->input['items']));
        if ($items && is_array($items))
        {
            \App\Products\Product\Images::sort_items($this->product, $items);
        }

        BN_Responses::routeSuccess();
    }

    function cmd_images_new()
    {

        $this->FormID .= "ImageNew";
        $js = [];
        $view_data = [];

        $populate = new \Novut\UI\Forms\Populate($this->FormID);
        $populate->setValueText('BrandID', $this->product->getBrandID());
        $populate->setValueText('BFamilyID', $this->product->getBFamilyID());
        $populate->setValueText('ProductID', $this->product->getProductID());
        $js = $populate->getJs();

        $view_data['cmd'] = "images_new_add";

        $this->response->modal($this->views->load_render('products/product/images/form', $this->view_data_presets($view_data)), 'Agregar Imagen', $js)->render();

    }


    function cmd_images_new_add()
    {

        $this->FormID .= "ImageNew";

        $validation = BN_Forms::validation($this->FormID, $this->input);

        if ($_FILES['PImageFile']['tmp_name'])
        {

            if (!in_array(pathinfo($_FILES['PImageFile']['name'], PATHINFO_EXTENSION), \App\Products\Config::image_extensions()))
            {
                $validation->setError('PImageFile', "Formato inválido. <BR>Solo se permiten los siguientes formatos: ". implode(", ", \App\Products\Config::image_extensions()).".");
            }

        }
        else{
            $validation->setRequiredField('PImageFile');
        }


        $validation->validate();

        $file_name = \BN_Filters::fname($_FILES['PImageFile']['name']);
        $file_name_final = "images/products/".$this->product->getProductID()."-".BN::random_code(4, 4) . "-" . $file_name;

        // files
        $image_file = new \Novut\Tools\Files\S3($this->bucket);
        $image_file->setAsPublic();
        $image_file->setFileContent(file_get_contents($_FILES['PImageFile']['tmp_name']));
        $image_file->setFileName($file_name_final);
        $url = $image_file->push();

        if ($url)
        {

            $image = new \App\Products\Product\Image($this->product);
            $image->setPImageDescription((string) $this->input['PImageDescription']);
            $image->setPImageBucket($this->bucket->getBucket());
            $image->setPImageUrl($url);
            $image->add();

            $this->response->notification_success('Registro Agregado', 'reload')->render();
        }
        else
        {
            $this->response->alert_error('No fue posible agregar la imágen.', 'reload')->render();
        }


    }


    function cmd_images_edit()
    {
        $this->getPImageInfo();

        $this->FormID .= "ImageEdit";
        $js = [];
        $view_data = [];

        $populate = new \Novut\UI\Forms\Populate($this->FormID);
        $populate->setValueText('BrandID', $this->product->getBrandID());
        $populate->setValueText('BFamilyID', $this->product->getBFamilyID());
        $populate->setValueText('ProductID', $this->product->getProductID());
        $populate->setValueText('PImageID', $this->product_image->getPImageID());
        $populate->setValueText('PImageDescription', $this->product_image->getPImageDescription());

        $js = $populate->getJs();

        $view_data['cmd'] = "images_edit_save";

        $this->response->modal($this->views->load_render('products/product/images/edit', $this->view_data_presets($view_data)), 'Editar Imagen', $js)->render();

    }


    function cmd_images_edit_save()
    {

        $this->getPImageInfo();

        $this->FormID .= "ImageEdit";

        $validation = BN_Forms::validation($this->FormID, $this->input);
        //$validation->setRequiredField('PImageFile');

        $validation->validate();

        $this->product_image->setPImageDescription((string) $this->input['PImageDescription']);
        $this->product_image->save();

        $this->response->notification_success('Cambios Aplicados', 'reload')->render();

    }


    function cmd_images_delete()
    {

        $this->getPImageInfo();

        if (!$this->input['confirm'])
        {
            \BN_Responses::confirm_quick("¿Deseas eliminar este registro?", 'confirm');
        }

        if ($this->product_image->getPImageUrl())
        {
            $image_file = new \Novut\Tools\Files\S3($this->bucket);
            $image_file->setBucket($this->product_image->getPImageBucket());
            $image_file->delete($this->product_image->getPImageUrl());
        }

        $this->product_image->cancel($this->product_image->getPImageID());
        $this->response->notification_success('Cambios Aplicados')->reload()->render();

    }


    function cmd_images_default()
    {

        $this->getPImageInfo();

        if (!$this->input['confirm'])
        {
            \BN_Responses::confirm_quick("¿Deseas establecer como predeterminada?", 'confirm');
        }

        \App\Products\Product\Images::set_default($this->product, $this->product_image->getPImageID());


        $this->response->notification_success('Cambios Aplicados')->reload()->render();

    }

}
