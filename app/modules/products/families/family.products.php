<?php


class inventory_families_family_products extends inventory_families_family
{

    /** @var \App\Products\Brands\FamilyProduct $f_product */
    protected $f_product;

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

    private function getProductName($BProductID)
    {
        if (!isset($this->product_names[$BProductID]))
        {
            $this->product_names[$BProductID] = \App\Products\Brands\Products::getName($this->brand, $BProductID);
        }

        return $this->product_names[$BProductID];
    }


    function getFProductInfo()
    {
        $this->f_product = new \App\Products\Brands\FamilyProduct($this->family, $this->input['BFProductID']);


        if (!$this->f_product->getBFProductID())
        {
            BN_Responses::alert_error('El parámetro de la familia no existe.');
        }
    }


    function cmd_products()
    {

        $view_data = [];
        $param_list = [];
        $js = [];

        $product_list = \App\Products\Brands\FamilyProducts::list($this->family, (new \Novut\Core\Query())->setOrder("ProductCustom_gauge ASC, LENGTH(ProductCustom_width), ProductCustom_width ASC, LENGTH(ProductCustom_length), ProductCustom_length", "ASC"));

        foreach(\App\Products\Brands\Families::paramCollection($this->brand, $this->family) as $param_info)
        {
            $param_list[$param_info['BParamID']] = $param_info;
        }

        $table = new \Novut\Tools\Tables\Table($this->FormID);

        if (sizeof($product_list) >= 2)
        {
            $table->addField('Sort', '&nbsp;', '1%');
        }

        $table->addField('ProductImage', '&nbsp;', '1%');
        $table->addField('ProductIDx', 'ID', '1%');
        $table->addField('ProductCode', 'SKU', '5%');
        $table->addField('ProductName', 'Nombre', '50%');
        $table->addField('ProductQtyPP', 'CxE', '1%')->setTitle('Cantidad por Empaque');
        $table->addField('ProductStock', 'Stock', '1%');

        foreach ($param_list as $param_info)
        {
            $table->addField("Param_{$param_info['BParamID']}", $param_info['BParamName'], ceil(40 / sizeof($param_list)));

        }

        $table->addField('Actions', '&nbsp;', '1%');


        foreach ($product_list as $product)
        {
            $data = $product->export();

            foreach(\BN_Coders::json_decode($data['BFamilyData']) as $param_info)
            {
                $data["Param_{$param_info['id']}"] = $param_info['value'] . ($param_list[$param_info['id']]['BParamComment'] ? " {$param_list[$param_info['id']]['BParamComment']}" : "");

            }


            // images
            if ($data['BFProductImage'])
            {
                $data['BFProductImage'] = "<a href='{$data['BFProductUrl']}' target='_blank' style='display:block; height: 80px; width: 80px; background: url({$data['BFProductUrl']}) center center no-repeat; background-size: cover'></a>";
            }
            else
            {
                $data['BFProductImage'] = "&nbsp;";
            }

            if ($data['BFProductDefault'])
            {
                $data['BFProductDefault'] = "<strong>Si</strong>";
            }
            else{
                $data['BFProductDefault'] = "<a href='#' onclick=\"brand_families_products_default('{$data['BFProductID']}'); return false;\">No</a>";
            }
            $data['ProductUrl'] = "{$this->ModuleUrlRoot}families/product/?ProductID={$data['ProductID']}&BrandID={$data['BrandID']}&BFamilyID={$data['BFamilyID']}";
            $data['ProductIDx'] = "<a href=\"{$data['ProductUrl']}\" >{$data['ProductID']}</a>";
            $data['ProductCode'] = "<a href=\"{$data['ProductUrl']}\" >{$data['ProductCode']}</a>";
            //$data['ProductName'] = "<a href=\"{$data['ProductUrl']}\" >{$data['ProductName']}</a>";

            $data['ProductCode'] = "<div class='nowrap'>{$data['ProductCode']}</div>";

            $table->addRow($data);
        }

        $view_data['table'] = $table->getTable();


        $this->layout->addLayoutOption('Actualizar Inventario')->setRoute(false, ['cmd' => 'products_invsync', 'BrandID' => $this->input['BrandID'], 'BFamilyID' => $this->input['BFamilyID']]);
        $this->layout->selectTab('products');
        $this->layout->setWebLib('sortable');
        $this->layout->setBrowserTitle("{$this->family->getBFamilyName()} - Productos");
        $this->layout->render($this->views->load_render('products/index', $this->view_data_presets($view_data)) ,"Productos",$js);
    }

    function cmd_products_sort()
    {
        $items = \BN_Coders::json_decode(base64_decode($this->input['items']));
        if ($items && is_array($items))
        {
            \App\Products\Brands\FamilyProducts::sort_items($this->family, $items);
        }

        BN_Responses::routeSuccess();
    }

    function cmd_products_new()
    {

        $this->FormID .= "ProductNew";
        $js = [];
        $view_data = [];

        $populate = new \Novut\UI\Forms\Populate($this->FormID);
        $populate->setValueText('BrandID', $this->brand->getBrandID());
        $populate->setValueText('BFamilyID', $this->family->getBFamilyID());
        $populate->setValueText('ProductQtyPP', 1);
        $js = $populate->getJs();

        $view_data['params'] = \App\Products\Brands\Families::paramCollection($this->brand, $this->family);
        $view_data['cmd'] = "products_new_add";
        $view_data['FormUrl'] = $this->ModuleUrlRoot . "families/family/";


        //print_r([\App\Products\Brands\Families::paramCollection($this->brand, $this->family)]); exit;
        $this->response->modal($this->views->load_render('products/form', $this->view_data_presets($view_data)), 'Agregar Producto', $js)->render();

    }


    function cmd_products_new_add()
    {

        $this->FormID .= "ProductNew";

        $validation = BN_Forms::validation($this->FormID, $this->input);
        $validation->setRequiredField('ProductName');
        $validation->setRequiredField('ProductCode');
        $validation->setRequiredField('ProductQtyPP');


        $validation->fieldValidation('ProductName', $this->input['ProductName'], function ($ProductName)
        {

            $product = \App\Products\Product\Products::name_exist(trim($ProductName));
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
            $product = \App\Products\Product\Products::code_exist(trim($ProductCode));
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

        $product = new App\Products\Product\Product();
        $product->setBrand($this->brand);
        $product->setProductName($this->input['ProductName']);
        $product->setProductCode($this->input['ProductCode']);
        $product->setProductDescription($this->input['ProductDescription']);
        $product->setProductQtyPP($this->input['ProductQtyPP']);
        $ProductID = $product->add();
        $product = new App\Products\Product\Product($ProductID);


        // family
        $family = new \App\Products\Product\Family($product);
        $family->setBFamilyID($this->family->getBFamilyID());
        foreach (\App\Products\Brands\Families::paramCollection($this->brand, $this->family) as $param_info)
        {
            $family->addParam($param_info['BParamID'], $param_info['BParamCode'], $this->input['PFamilyData'][$param_info['BParamID']]);
        }

        $family->save();


        // update product
        $product->update();

        $this->response->notification_success('Registro Agregado', 'reload')->render();

    }

    function cmd_products_invsync()
    {
        $invConfig = new \App\Inventory\Config;
        $total = 0;
        foreach (\App\Products\Brands\FamilyProducts::list($this->family, (new \Novut\Core\Query())->setOrder("ProductCustom_gauge ASC, LENGTH(ProductCustom_width), ProductCustom_width ASC, LENGTH(ProductCustom_length), ProductCustom_length", "ASC")) as $product)
        {
            $total++;

            // contabilizar todos los warehouses
            $InventoryInfo = $this->db->TableInfo('inventory_stock', 'ProductID', $product->getProductID(), " AND Cancelled = 0");
            $ProductStock = 0;
            if ($InventoryInfo)
            {
                $ProductStock = $InventoryInfo['IStockQty'];
            }

            $this->db->Update('inv_products_products', ['ProductStock' => $ProductStock], 'ProductID', $product->getProductID());

        }

        responses()->alert_success("Datos Actualizados ({$total})")->reload()->render();
    }

}
