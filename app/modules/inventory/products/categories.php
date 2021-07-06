<?php

$categories = new products_categories();
$categories->init();


class products_categories
{


    /** @var  \novut\DB\BN_PDOMethods|\PDO|\PDOStatement */
    protected $db;

    protected $input;
    protected $FormID;
    protected $BaseUrl;
    protected $document;
    protected $JS;
    protected $CategoryInfo;
    protected $CookieID;
    protected $tables;
    protected $params;


    public function init()
    {

        $this->db = BN::DB();
        $this->input = BN::input();

        // Main Campaign
        $this->BaseUrl      = 'inventory/products/categories/';
        $this->FormID       = 'InventoryProductsCategories';

        $this->params['var_fn'] = 'inventory_products_categories';
        $this->params['var_tp'] = 'categories/';

        $this->params['title']       = "Cat&aacute;logo de Categor&iacute;as";

        $this->tables['categories']     = 'inv_products_categories';
        $this->tables['brands']         = 'inv_products_brands';
        $this->tables['products']         = 'inv_products_products';


        $this->CookieID = md5($this->BaseUrl);
        $this->CookieID = substr($this->CookieID, 0, round(strlen($this->CookieID) / 3));

        $method = BN_Methods::cmd();
        $this->$method();

    }

    function cmd_index()
    {

        $this->document = $this->template("index");
        $DropdownMenuID = BN::random_code(4, 4);

        $ExtraQuery = "";

        // Filters
        list($FilterQuery, $FilterSort, $ExtraJS, $QVal) = $this->categories_filters();
        $this->JS[] = $ExtraJS;

        // Fields

        $FOptions['CategoryName'] 	= array
        (
            'name'=>'Nombre',
            'width'=> '79%',
            'type'=> 'text',
            'sort'=> true,
        );

        $FOptions['Action'] 	= array
        (
            'name'=>'&nbsp;',
            'width'=>'1%',
        );





        $Table = new BN_Table($this->FormID);

        // CreteTable
        $Table->table_header($FOptions);



        // QVal
        $ExtraQuery .= " AND Cancelled = 0 ";
        $sql_offset = $Table->navbar($this->tables['categories'], array('query'=>"  {$ExtraQuery} {$FilterQuery} ", "QVal"=> $QVal), $this->BaseUrl, 50);

        $sth = $this->db->prepare("SELECT * FROM {$this->db->getTable($this->tables['categories'])} WHERE 1 {$ExtraQuery} {$FilterQuery} {$FilterSort} {$sql_offset}");
        $sth->execute($QVal);
        while (($data = $sth->fetch(PDO::FETCH_ASSOC)) != false) {


            $data['Action'] = BN_Layouts::dropdownmenu($DropdownMenuID, 'nav', $data['CategoryID']);


            $Table->row_fill($data);

        }

        $this->tpldata['table'] = $Table->getTable();


        // OptionMenu

        // Dropdown menu
        $this->document .= BN_Layouts::dropdownmenu_items($DropdownMenuID,

            [
                [
                    'label'    =>'Editar',
                    'ico'      =>'fa fa-edit fa-fw',
                    'action'      =>'edit',
                ],
                [
                    'label'    =>'Eliminar',
                    'ico'      =>'fa fa-trash-o fa-fw',
                    'action'      =>'delete',
                ]

            ]

            , $this->params['var_fn']);



        // MainMenu
        $this->index_menu();

        // Populate
        $this->document = $this->populate($this->document);
        $this->document = $this->tplrender($this->document, $this->tpldata);




        // Print
        $BNPrint = new BN_Print;
        $BNPrint->WebLib('bootstrap');
        $BNPrint->WebLib('select2');
        $BNPrint->WebLib('contextmenu');
        $BNPrint->JS($this->JS);
        $BNPrint->render($this->document, "Productos - Configuraci&oacute;n - Categor&iacute;as");

    }

    function categories_filters()
    {

        /**************************************/
        /* Load Filter
         /**************************************/
        $ListFilter = new BN_TableFilter;

        $ListFilter->FormID = $this->FormID;
        $ListFilter->Version = 2;
        $ListFilter->BaseUrl = $this->BaseUrl;

        $ListFilter->CookieID = $this->CookieID;
        $ListFilter->SortDefault['SortID'] = "CategoryName";



        /***********************************
         * render
         ***********************************/
        $ListFilter->FormFields['Text'] = array(

            "CategoryName",
        );

        $ListFilter->FormFields['OptionList2'] = array(


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

            "CategoryName",
        );

        $ListFilter->SortFields['OptionList'] = array(

            //array("Name"=>"ProjectStatus"),
            //array("Name"=>"ProjectPriority"),
            //array("Name"=>"BrandCategory"),
        );


        /***********************************
         * query
         ***********************************/
        $ListFilter->WhereFields[] = array("Name"=>"CategoryName", "Type"=>"like");
//        $ListFilter->WhereFields[] = array("Name"=>"CategoryCode", "Type"=>"like");
//        $ListFilter->WhereFields[] = array("Name"=>"BrandID", "Type"=>"equal");

        /***********************************
         * start
         ***********************************/
        $ListFilter->Start();


        /***********************************
         * end
         ***********************************/
        $ExtraJS .= "$('#flt-BrandCategory').on('select2-open', function() { $('.select2-drop-active').css('min-width', '200px'); });\n";
        $ExtraJS .= $ListFilter->End();


        return array($ListFilter->WhereQuery, $ListFilter->SortQuery, $ExtraJS, $ListFilter->QVal);

    }

    protected function cmd_new()
    {

        $this->FormID .= "New";
        $this->document = $this->template("form");
        $this->document = $this->populate($this->document);
        $ModalID = false;

        $this->JS[] = BN_Forms::setValueText('cmd', "new_add", $this->FormID);

        if ($this->input['CallerForm'])
        {
            $ModalID = BN::random_code();
            $this->JS[] = BN_Forms::jq_after('cmd', html::input('type', 'hidden', 'name', 'CallerForm'), $this->FormID);
            $this->JS[] = BN_Forms::setValueText('CallerForm', $this->input['CallerForm'], $this->FormID);

            $this->JS[] = BN_Forms::jq_after('CallerForm', html::input('type', 'hidden', 'name', 'CallerFormField'), $this->FormID);
            $this->JS[] = BN_Forms::setValueText('CallerFormField', $this->input['CallerFormField'], $this->FormID);

            $this->JS[] = BN_Forms::jq_after('CallerForm', html::input('type', 'hidden', 'name', '__ModalID__', 'value', $ModalID), $this->FormID);

            $this->tpldata['CallerForm'] = $this->input['CallerForm'];
            $this->tpldata['CallerFormField'] = $this->input['CallerFormField'];
        }

        $this->document = $this->tplrender($this->document, $this->tpldata);

        $this->JS[] = BN_Forms::jq_focus('CategoryName', $this->FormID, ($ModalID?$ModalID:true));



        BN_Responses::modal_content($this->document, 'Agregar Categor&iacute;a', $this->JS, $ModalID);
    }

    protected function cmd_new_add()
    {

        $this->FormID .= "New";

        $this->input = BN_Coders::utf8_encode($this->input);

        $this->input['CategoryName'] = BN_Filters::name($this->input['CategoryName']);

        if ($this->db->TableInfo($this->tables['categories'], 'CategoryName', $this->input['CategoryName'], " AND Cancelled = 0 "))
        {
            $ValErrors['CategoryName'] = "El nombre de la marca ya existe.";
        }

        //Validation
        $Validation = array('CategoryName' => 'required');

        BN_Validation::Wizard($this->FormID, $this->input, $Validation, $ValErrors);


        $FieldsName = array(
            "CategoryName",
        );


        foreach ($FieldsName as $ii)
        {
            $DataFields[$ii] = $this->input[$ii];
        }

        $this->db->Insert($this->tables['categories'], $DataFields);
        $CategoryID = $this->db->lastInsertId();

        if ($this->input['CallerForm'])
        {

            $JS[] = BN_Forms::setValueHtml($this->input['CallerFormField'], BN::OptionListEmpty().$this->db->OptionList('inv_products_categories', 'CategoryID', 'CategoryName', ' Cancelled = 0', false, 'CategoryName', 'html').BN::OptionListEmpty().html::option('value', '_new_')->append('[Nuevo]'), $this->input['CallerForm']);
            $JS[] = BN_Forms::setValueSelect2($this->input['CallerFormField'], $CategoryID, $this->input['CallerForm']);
            $JS[] = "BN.WinClose('{$this->input['__ModalID__']}')";

        }
        else

        {
            $JS = 'reload';
        }


        BN_Responses::messageAjax('Registro Agregado', 'success', $JS);

    }

    protected function cmd_edit()
    {

        $this->getCategoryInfo();

        $this->FormID .= "Edit";
        $this->document = $this->template("form");

        $FormFields['text'] = [

            'CategoryID',
            'CategoryName',
        ];


        $FormJS = BN_Forms::populateJS($this->CategoryInfo, $FormFields, $this->FormID);
        $this->JS[] = $FormJS;
        $this->JS[] = BN_Forms::setValueText('cmd', "edit_save", $this->FormID);

        $this->tpldata['CategoryInfo'] = $this->CategoryInfo;

        $this->document = $this->populate($this->document);
        $this->document = $this->tplrender($this->document, $this->tpldata);


        BN_Responses::modal_content($this->document, 'Editar Categor&iacute;a', $this->JS);


    }


    function cmd_edit_save()
    {
        $this->getCategoryInfo();


        $this->input = BN_Coders::utf8_encode($this->input);

        // Validate Name
        $this->input['CategoryName'] = BN_Filters::name($this->input['CategoryName']);
        if ($this->db->TableInfo($this->tables['categories'], 'CategoryName', $this->input['CategoryName'], " AND CategoryID != :CategoryID AND Cancelled = 0 ", ['CategoryID'=>$this->CategoryInfo['CategoryID']]))
        {
            $ValErrors['CategoryName'] = "El nombre de la marca ya existe.";
        }


        // Required Fields
        $Validation['CategoryName']     = 'required';


        BN_Validation::Wizard($this->FormID, $this->input, $Validation, $ValErrors);


        $UpdateFields = [

            "CategoryName",
        ];

        foreach($UpdateFields as $ii)
        {
            $UpdateData[$ii] = $this->input[$ii];
        }

        $this->db->Update($this->tables['categories'], $UpdateData, 'CategoryID', $this->CategoryInfo['CategoryID']);

        BN_Responses::messageAjax('Cambios Aplicados', 'success', 'reload');

    }


    protected function cmd_delete()
    {
        $this->getCategoryInfo();


        $TotalProducts = $this->db->Total($this->tables['products'], 'CategoryID', $this->CategoryInfo['CategoryID'], " AND Cancelled = 0");

        if ($TotalProducts)
        {
            BN_Responses::modalHTML('Existen productos asociados. No es posible eliminar el registro', 'error');
        }



        if (!$this->input['delete'])
        {
            BN_Responses::confirmAjax('¿Deseas eliminar este registro?', false, false, 'delete');
        }

        $UpdateFields['Cancelled'] = 1;
        $this->db->Update($this->tables['categories'], $UpdateFields, 'CategoryID', $this->input['CategoryID'], " AND Cancelled = 0 ");

        BN_Messages::messageAjax('Cambios Aplicados', 'success', 'reload');

    }
/*
    function getBrandList()
    {
        $sth = $this->db->prepare("SELECT * FROM {$this->db->getTable($this->tables['brands'])} WHERE Cancelled = 0 ORDER BY BrandName ");
        $sth->execute();
        while(($data = $sth->fetch(PDO::FETCH_ASSOC)) != false)
        {
            $BrandList[] = $data;
        }

        return $BrandList;
    }

    function getBrandName($BrandID = "")
    {
        if (!$BrandID)
        {
            return false;
        }

        if (!$this->BrandName[$BrandID])
        {
            $this->BrandName[$BrandID] = $this->db->getValue($this->tables['brands'], 'BrandID', $BrandID, " AND Cancelled = 0", false, 'BrandName');
        }

        return $this->BrandName[$BrandID];
    }
*/

    function getCategoryInfo($CategoryID = "", $ignore_error = false)
    {
        $CategoryID = $CategoryID?:$this->input['CategoryID'];

        if (!$CategoryID)
        {
            BN_Messages::messageAjax('El registro no existe.', 'error');
        }

        $this->CategoryInfo = $this->db->TableInfo($this->tables['categories'], 'CategoryID', $CategoryID, " AND Cancelled = 0 ");
        $this->CategoryInfo = BN_Encode::utf8_decode($this->CategoryInfo);
        $this->CallerInfo = $this->CategoryInfo;

        if (!$this->CategoryInfo && $ignore_error == false)
        {
            BN_Messages::messageAjax('El registro no existe.', 'error');
        }

    }

    function index_menu()
    {

        $MainMenu = array();
        $MainMenu['ModuleMenuL'] = novut\Inventory\Products\product::main_menu();
        $MainMenu['ModuleMenuR'] .= BN_Layouts::menu_item("Agregar Categor&iacute;a", 'javascript: void(0);', 'fa-plus', ['onclick', BN_JSHelpers::CMDRoute($this->ModuleUrl, 'new')]);
        $this->tpldata['MainMenu'] = BN_Layouts::setLayout(false, 'topmenu', $MainMenu);

    }

    function populate($document)
    {

        if ($document)
        {
            $TagData['ModuleUrl']         = $this->BaseUrl;
            $TagData['BaseUrl']           = BN_Var::$BaseUrl;
            $TagData['FormID']            = $this->FormID;
            $TagData['CategoryID']           = $this->CategoryInfo['CategoryID'];
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
        $data['CategoryID'] = $this->CategoryInfo['CategoryID'];

        $document = BN::tplrender($document, $data);
        return $document;
    }


}

