<?php

$brands = new brands_brands();
$brands->init();


class brands_brands
{

    protected $input;
    protected $FormID;
    protected $BaseUrl;
    protected $document;
    protected $JS;
    protected $BrandInfo;
    protected $CookieID;
    protected $tables;
    protected $params;


    public function init()
    {

        $this->db = BN::DB();
        $this->input = BN::input();

        // Main Campaign
        $this->BaseUrl      = 'inventory/products/brands/';
        $this->FormID       = 'InventoryProductsBrands';

        $this->params['var_fn'] = 'inventory_products_brands';
        $this->params['var_tp'] = 'brands/';

        $this->params['title']       = "Cat&aacute;logo de Marcas";

        $this->tables['brands'] = 'inv_products_brands';
        $this->tables['products'] = 'inv_products_products';


        $this->CookieID = md5($this->BaseUrl);
        $this->CookieID = substr($this->CookieID, 0, round(strlen($this->CookieID) / 3));

        $method = BN_Methods::cmd();
        $this->$method();

    }

    function cmd_index()
    {

        $this->document = $this->template("index");
        $DropdownMenuID = BN::random_code(4, 4);


       // Fields

        $FOptions['BrandName'] 	= array
        (
            'name'=>'Nombre',
            'width'=>'99%',
        );


        $FOptions['Action'] 	= array
        (
            'name'=>'&nbsp;',
            'width'=>'1%',
        );





        $Table = new BN_Table($this->FormID);

        // CreteTable
        $Table->table_header($FOptions);


        // Offset

        $sth = $this->db->prepare("SELECT * FROM {$this->db->getTable($this->tables['brands'])} WHERE 1 AND Cancelled = 0 Order by BrandName");
        $sth->execute();
        while (($data = $sth->fetch(PDO::FETCH_ASSOC)) != false) {

            $data['Action'] = BN_Layouts::dropdownmenu($DropdownMenuID, 'nav', $data['BrandID']);

            $Table->row_fill($data);
        }

        $this->tpldata['table'] = $Table->getTable();


        // Dropdown menu
        $this->document .= BN_Layouts::dropdownmenu_items($DropdownMenuID, [
                ['label' => 'Editar', 'ico' => 'fa fa-edit fa-fw', 'action' => 'edit'],
                ['label' => 'Eliminar', 'ico' => 'fa fa-trash-o fa-fw', 'action' => 'delete']
            ], $this->params['var_fn']);

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
        $BNPrint->render($this->document, "Productos - Configuraci&oacute;n - Marcas");

    }


    protected function cmd_new()
    {

        $this->FormID .= "New";
        $this->document = $this->template("form");
        $this->document = $this->populate($this->document);

        $this->JS[] = BN_Forms::setValueText('cmd', "new_add", $this->FormID);

        BN_Responses::modal_content($this->document, 'Agregar Marca', $this->JS);
    }

    protected function cmd_new_add()
    {

        $this->FormID .= "New";

        $this->input = BN_Coders::utf8_encode($this->input);

        $this->input['BrandName'] = BN_Filters::name($this->input['BrandName']);

        if ($this->db->TableInfo($this->tables['brands'], 'BrandName', $this->input['BrandName'], " AND Cancelled = 0 "))
        {
            $ValErrors['BrandName'] = "El nombre de la marca ya existe.";
        }

        //Validation
        $Validation = array('BrandName' => 'required');

        BN_Validation::Wizard($this->FormID, $this->input, $Validation, $ValErrors);


        $FieldsName = array(
            "BrandName",
        );


        foreach ($FieldsName as $ii)
        {
            $DataFields[$ii] = $this->input[$ii];
        }

        $this->db->Insert($this->tables['brands'], $DataFields);

        BN_Responses::messageAjax('Registro Agregado', 'success', 'reload');

    }

    protected function cmd_edit()
    {

        $this->getBrandInfo();

        $this->FormID .= "Edit";
        $this->document = $this->template("form");

        $FormFields['text'] = [

            'BrandID',
            'BrandName',
        ];


        $FormJS = BN_Forms::populateJS($this->BrandInfo, $FormFields, $this->FormID);
        $this->JS[] = $FormJS;
        $this->JS[] = BN_Forms::setValueText('cmd', "edit_save", $this->FormID);

        $this->tpldata['BrandInfo'] = $this->BrandInfo;

        $this->document = $this->populate($this->document);
        $this->document = $this->tplrender($this->document, $this->tpldata);


        BN_Responses::modal_content($this->document, 'Editar Marca', $this->JS);


    }


    function cmd_edit_save()
    {
        $this->getBrandInfo();

        $this->input = BN_Coders::utf8_encode($this->input);


        // Validate Name
        $this->input['BrandName'] = BN_Filters::name($this->input['BrandName']);
        if ($this->db->TableInfo($this->tables['brands'], 'BrandName', $this->input['BrandName'], " AND BrandID != :BrandID AND Cancelled = 0 ", ['BrandID'=>$this->BrandInfo['BrandID']]))
        {
            $ValErrors['BrandName'] = "El nombre de la marca ya existe.";
        }


        // Required Fields
        $Validation['BrandName']     = 'required';


        BN_Validation::Wizard($this->FormID, $this->input, $Validation, $ValErrors);


        $UpdateFields = [

            "BrandName",
        ];

        foreach($UpdateFields as $ii)
        {
            $UpdateData[$ii] = $this->input[$ii];
        }

        $this->db->Update($this->tables['brands'], $UpdateData, 'BrandID', $this->BrandInfo['BrandID']);

        BN_Responses::messageAjax('Cambios Aplicados', 'success', 'reload');

    }


    protected function cmd_delete()
    {
        $this->getBrandInfo();

        $TotalProducts = $this->db->Total($this->tables['products'], 'BrandID', $this->BrandInfo['BrandID'], " AND Cancelled = 0");

        if ($TotalProducts)
        {
            BN_Responses::modalHTML('Existen productos asociados. No es posible eliminar el registro', 'error');
        }


        if (!$this->input['delete'])
        {
            BN_Responses::confirmAjax('¿Deseas eliminar este registro?', false, false, 'delete');
        }

        $UpdateFields['Cancelled'] = 1;
        $this->db->Update($this->tables['brands'], $UpdateFields, 'BrandID', $this->input['BrandID'], " AND Cancelled = 0 ");

        BN_Messages::messageAjax('Cambios Aplicados', 'success', 'reload');

    }


    function getBrandInfo($BrandID = "", $ignore_error = false)
    {
        $BrandID = $BrandID?:$this->input['BrandID'];

        if (!$BrandID)
        {
            BN_Messages::messageAjax('El registro no existe.', 'error');
        }

        $this->BrandInfo = $this->db->TableInfo($this->tables['brands'], 'BrandID', $BrandID, " AND Cancelled = 0 ");
        $this->BrandInfo = BN_Encode::utf8_decode($this->BrandInfo);
        $this->CallerInfo = $this->BrandInfo;

        if (!$this->BrandInfo && $ignore_error == false)
        {
            BN_Messages::messageAjax('El registro no existe.', 'error');
        }

    }

    function index_menu()
    {

        $MainMenu = array();
        $MainMenu['ModuleMenuL'] = novut\Inventory\Products\product::main_menu();
        $MainMenu['ModuleMenuR'] .= BN_Layouts::menu_item("Agregar Marca", 'javascript: void(0);', 'fa-plus', ['onclick', BN_JSHelpers::CMDRoute($this->ModuleUrl, 'new')]);
        $this->tpldata['MainMenu'] = BN_Layouts::setLayout(false, 'topmenu', $MainMenu);

    }

    function populate($document)
    {

        if ($document)
        {
            $TagData['ModuleUrl']         = $this->BaseUrl;
            $TagData['BaseUrl']           = BN_Var::$BaseUrl;
            $TagData['FormID']            = $this->FormID;
            $TagData['BrandID']           = $this->BrandInfo['BrandID'];
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
        $data['BrandID'] = $this->BrandInfo['BrandID'];

        $document = BN::tplrender($document, $data);
        return $document;
    }


}

