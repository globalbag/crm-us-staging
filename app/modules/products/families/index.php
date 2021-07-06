<?php

class inventory_families extends nv_products_shared
{
    /** @var \Novut\Core\Endpoint $endpoint */
    protected $endpoint;

    /** @var \App\Products\Brands\Brand  $brand */
    protected $brand;

    /** @var \App\Products\Brands\Family $family */
    protected $family;


    function config_custom()
    {

        $this->ModuleUrl    .= 'families/';
        $this->FormID       .= 'Families';

    }

    function view_data_presets_custom($data)
    {
        $data = $data ? $data : [];

        $data['BFamilyID'] = $this->family ? $this->family->getBFamilyID() : "";

        return $data;
    }

    function cmd_index()
    {

        $query = new \Novut\Core\Query();
        $query->setQuery(" AND Cancelled = 0");
        $query->setOrder("RowOrder");

        $ProductMenuID = $this->FormID."Menu";
        $table = new \Novut\UI\Tables\Table($this->FormID);
        $table->setField($table->field('BFamilyLink', 'ID', '1%'));
        $table->setField($table->field('BFamilyName', 'Name', '80%'));
        $table->setField($table->field('BrandName', 'Brand', '10%'));
        $table->setField($table->field('BFamilyCode', 'Code', '10%'));
        $table->setField($table->field('BFamilyPublished', 'Published', '1%'));



        $sth = $this->db->prepare("SELECT * FROM {$this->db->getTable('inv_products_brands_family')}  WHERE 1 {$query->getQueryFull()} ");
        $sth->execute($query->getParams());
        while (($data = $sth->fetch(PDO::FETCH_ASSOC)) != false) {

            // dropdown menu

            $data['BFamilyLink'] = "<a href=\"{$this->ModuleUrl}family/?BFamilyID={$data['BFamilyID']}&BrandID={$data['BrandID']}\">{$data['BFamilyID']}</a>";
            $data['BFamilyName'] = "<a href=\"{$this->ModuleUrl}family/?BFamilyID={$data['BFamilyID']}&BrandID={$data['BrandID']}\">{$data['BFamilyName']}</a>";

            $data['BFamilyPublished'] = $data['BFamilyPublished']?"Yes":"No";
            $data['BrandName'] = $data['BrandID'] ? \App\Products\Brands\Brands::name($data['BrandID']):"";

            $table->row_fill($data);

        }

        $view_data['table'] = $table->getTotalRows() ? $table->getTable() : "";

        $this->layout->setLayoutAction($this->layout->link()->setLabel('Agregar')->setRoute(null, ['cmd' => 'new']));

        $this->layout->render($this->views->load_render('index', $this->view_data_presets($view_data)), "Families");


    }

    function filters_config_custom()
    {

        // import grid data
        $this->GridFilter->importFields($this->config['FieldsInfo'], $this->config['ViewInfo']);

        // Sort
        $this->GridFilter->setSortDefault('ProductName');

        // Queries
        //$this->GridFilter->setQuery('fi');

        // Url
        $this->GridFilter->setUrl($this->ModuleUrl, ['view' =>$this->input['view']]);

    }

    function products_filters()
    {

        /**************************************/
        /* Load Filter
         /**************************************/
        $ListFilter = new BN_TableFilter;

        $ListFilter->FormID = $this->FormID;
        $ListFilter->Version = 2;
        $ListFilter->BaseUrl = $this->BaseUrl;

        $ListFilter->CookieID = $this->CookieID;
        $ListFilter->SortDefault['SortID'] = "ProductName";



        /***********************************
         * render
         ***********************************/
        $ListFilter->FormFields['Text'] = array(

            "ProductCode",
            "ProductName",

        );

        $ListFilter->FormFields['OptionList2'] = array(

            "BrandID",
            "CategoryID",

        );


        $ListFilter->FormLists = array(

        );

        //Brands Cat
        /*
        $BrandList = $this->getBrandList();

        $BrandListHTML = html::option('value', "")->append("-");

        foreach($BrandList as $BrandInfo)
        {
            $BrandListHTML .= html::option('value', $BrandInfo['BrandID'])->append($BrandInfo['BrandName']);
        }

        unset($BrandList);

        $BrandList = base64_encode($BrandListHTML);
        $ExtraJS = "$('#flt-BrandID').html($.base64.decode('{$BrandList}'));";
        */
        /***********************************
         * sort
         ***********************************/
        $ListFilter->SortFields['Text'] = array(

            "ProductCode",
            "ProductName",
        );

        $ListFilter->SortFields['OptionList'] = array(

            //array("Name"=>"ProjectStatus"),
            //array("Name"=>"ProjectPriority"),
            //array("Name"=>"BrandProduct"),
        );


        /***********************************
         * query
         ***********************************/
        $ListFilter->WhereFields[] = array("Name"=>"ProductName", "Type"=>"like");
        $ListFilter->WhereFields[] = array("Name"=>"ProductCode", "Type"=>"like");
        $ListFilter->WhereFields[] = array("Name"=>"BrandID");
        $ListFilter->WhereFields[] = array("Name"=>"CategoryID");

        /***********************************
         * start
         ***********************************/
        $ListFilter->Start();


        /***********************************
         * end
         ***********************************/
        $sth = $this->db->prepare("SELECT * FROM {$this->db->getTable($this->tables['brands'])} WHERE 1 AND Cancelled = 0 Order by BrandName");
        $sth->execute();
        while (($data = $sth->fetch(PDO::FETCH_ASSOC)) != false) {

            $BrandList[$data['BrandID']] = $data['BrandName'];
           // {}
        }

        $sth = $this->db->prepare("SELECT * FROM {$this->db->getTable($this->tables['categories'])} WHERE 1 AND Cancelled = 0 Order by CategoryName");
        $sth->execute();
        while (($data = $sth->fetch(PDO::FETCH_ASSOC)) != false) {

            $CategoryList[$data['CategoryID']] = $data['CategoryName'];
            // {}
        }
        $ExtraJS .= BN_Forms::setSelectContent('flt-CategoryID', $CategoryList, ['filter'=>1], $this->FormID);
        $ExtraJS .= BN_Forms::setSelectContent('flt-BrandID', $BrandList, ['filter'=>1], $this->FormID);
        $ExtraJS .= "$('#flt-BrandProduct').on('select2-open', function() { $('.select2-drop-active').css('min-width', '200px'); });\n";
        $ExtraJS .= $ListFilter->End();


        return array($ListFilter->WhereQuery, $ListFilter->SortQuery, $ExtraJS, $ListFilter->QVal);

    }



    function cmd_new()
    {
        $this->FormID .= "New";
        $js = [];
        $view_data = [];
        $view_data['param_list'] =  \App\Products\Brands\Params::list($this->brand, null, (new \Novut\Db\OrmOptionsGroup())->setExport());

        /** @var \App\Products\Brands\Brand $brand */
        foreach (\App\Products\Brands\Brands::list() as $brand)
        {
            $brand_list[$brand->getBrandID()] = $brand->getBrandName();
        }

        $brand_list = BN_Forms::option_empty().BN_Forms::option_list_render($brand_list);
        $populate = new \Novut\UI\Forms\Populate($this->FormID);
        $populate->setSelect2('BrandID', $brand_list);
        $js = $populate->getJs();

        $view_data['cmd'] = "new_add";
        $view_data['FormUrl'] = $this->ModuleUrlRoot . "families/";

        $this->response->modal($this->views->load_render('form', $this->view_data_presets($view_data)), 'Agregar Familia', $js)->render();
    }

    function cmd_new_add()
    {
        $this->FormID .= "New";

        $this->getBrandInfo();
        $this->input['BFamilyCode'] = \BN_Filters::code($this->input['BFamilyCode']);

        $validation = BN_Forms::validation($this->FormID, $this->input);
        $validation->setRequiredField('BFamilyName');
        $validation->fieldValidation('BFamilyName', $this->input['BFamilyName'], function ($name)
        {
            return \App\Products\Brands\Families::name_exist($this->brand, trim($name)) ? false : true;
        }, "El nombre ya existe");

        $validation->fieldValidation('BFamilyCode', $this->input['BFamilyCode'], function ($code)
        {
            return \App\Products\Brands\Families::code_exist($this->brand, trim($code)) ? false : true;
        }, "El alias ya existe");


        $validation->validate();

        $family = new \App\Products\Brands\Family($this->brand);
        $family->setBFamilyName($this->input['BFamilyName']);
        $family->setBFamilyCode($this->input['BFamilyCode']);
        $family->setBFamilyPublished($this->input['BFamilyPublished'] ? true : false);
        $family->setBFamilyQtyRange($this->input['BFamilyQtyRange'] ? true : false);

        $family->add();

        $this->response->notification_success('Registro Agregado', 'reload')->render();

    }


    function getProductInfo($ProductID = "", $ignore_error = false)
    {
        $ProductID = $ProductID?:$this->input['ProductID'];

        if (!$ProductID)
        {
            BN_Messages::messageAjax('El registro no existe.', 'error');
        }

        $this->ProductInfo = $this->db->TableInfo($this->tables['products'], 'ProductID', $ProductID, " AND Cancelled = 0 ");
        $this->ProductInfo = BN_Encode::utf8_decode($this->ProductInfo);
        $this->CallerInfo = $this->ProductInfo;

        if (!$this->ProductInfo && $ignore_error == false)
        {
            BN_Messages::messageAjax('El registro no existe.', 'error');
        }

        $this->product = new \App\Products\Product\Product();

        if ($this->ProductInfo)
        {
            $this->product->import($this->ProductInfo);
        }


    }

    function index_menu()
    {

        $MainMenu = array();
        $MainMenu['ModuleMenuL'] = novut\Inventory\Products\product::main_menu();
        $MainMenu['ModuleMenuR'] .= BN_Layouts::menu_item("Agregar Producto", 'javascript: void(0);', 'fa-plus', ['onclick', BN_JSHelpers::CMDRoute($this->ModuleUrl, 'new')]);
        $MainMenu['ModuleMenuR'] .= BN_Layouts::menu_item("Importar", 'javascript: void(0);', 'fa-upload', ['onclick', BN_JSHelpers::CMDRoute($this->ModuleUrl."import/")]);
        $this->tpldata['MainMenu'] = BN_Layouts::setLayout(false, 'topmenu', $MainMenu);

    }

    function populate($document)
    {

        if ($document)
        {
            $TagData['ModuleUrl']         = \BN_Request::getPathInfo()."/";
            $TagData['BaseUrl']           = BN_Var::$BaseUrl;
            $TagData['FormID']            = $this->FormID;
            $TagData['ProductID']           = $this->ProductInfo['ProductID'];
            $TagData['ModuleCookieID']    = $this->CookieID;
            $TagData['CookieID']          = $this->CookieID;

            $document = str_replace("bn_generic_module_function", $this->params['var_fn'], $document);

            foreach(array_keys($TagData) as $ii)
            {
                $document = str_replace(array("--{$ii}--", '{{ '.$ii.' }}', '{{'.$ii.'}}'), $TagData[$ii], $document);
            }

        }



        return $document;
    }

    function template($templateID = "")
    {
        return BN_Load::template(($this->params['var_tp']?$this->params['var_tp']."/":"").$templateID);
    }

    function tplrender($document, $data)
    {
        $data['ModuleUrl'] = $this->BaseUrl;
        $data['FormID'] = $this->FormID;
        $data['ProductID'] = $this->ProductInfo['ProductID'];

        $document = BN::tplrender($document, $data);
        return $document;
    }



}

(new inventory_families)->init();