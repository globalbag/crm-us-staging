<?php

$plists = new products_plists();
$plists->init();


class products_plists
{

    protected $input;
    protected $FormID;
    protected $BaseUrl;
    protected $document;
    protected $JS;
    protected $PListInfo;
    protected $CookieID;
    protected $tables;
    protected $params;


    public function init()
    {

        $this->db = BN::DB();
        $this->input = BN::input();

        // Main Campaign
        $this->BaseUrl      = 'inventory/products/plists/';
        $this->FormID       = 'InventoryProductsPLists';

        $this->params['var_fn'] = 'inventory_products_plists';
        $this->params['var_tp'] = 'plists/';

        $this->params['title']       = "Listas de Precios";

        $this->tables['plists'] = 'inv_products_plists';
        $this->tables['prices'] = 'inv_products_prices';


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

        $FOptions['PListName'] 	= array
        (
            'name'=>'Nombre',
            'width'=>'99%',
        );

        $FOptions['PListDefault'] 	= array
        (
            'name'=>'Default',
            'width'=>'1%',
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

        $sth = $this->db->prepare("SELECT * FROM {$this->db->getTable($this->tables['plists'])} WHERE 1 AND Cancelled = 0 Order by PListName");
        $sth->execute();
        while (($data = $sth->fetch(PDO::FETCH_ASSOC)) != false) {

            $data['Action'] = html::a('href', '#', 'onclick', 'return false;', 'class', "{$this->params['var_fn']}Menu", "rel", $data['PListID'])->append(html::i('class', 'fa fa-navicon'));
            $data['Action'] = BN_Layouts::dropdownmenu($DropdownMenuID, 'nav', $data['PListID']);
            $data['PListDefault'] = $data['PListDefault']?"Si":"No";

            //$data['Url'] = $this->BaseUrl."?PListID={$data['PListID']}";
            //$data['PListName'] = html::a('href', $data['Url'])->append($data['PListName']);

            $Table->row_fill($data);

        }

        $this->tpldata['table'] = $Table->getTable();


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
        $BNPrint->render($this->document, "Productos - Configuraci&oacute;n - Listas de Precios");

    }


    protected function cmd_new()
    {

        $this->FormID .= "New";
        $this->document = $this->template("form");
        $this->document = $this->populate($this->document);

        $this->JS[] = BN_Forms::setValueText('cmd', "new_add", $this->FormID);

        BN_Responses::modal_content($this->document, 'Agregar Lista', $this->JS);
    }

    protected function cmd_new_add()
    {

        $this->FormID .= "New";

        $this->input = BN_Coders::utf8_encode($this->input);

        $this->input['PListName'] = BN_Filters::name($this->input['PListName']);

        if ($this->db->TableInfo($this->tables['plists'], 'PListName', $this->input['PListName'], " AND Cancelled = 0 "))
        {
            $ValErrors['PListName'] = "El nombre de la marca ya existe.";
        }

        //Validation
        $Validation = array('PListName' => 'required');

        BN_Validation::Wizard($this->FormID, $this->input, $Validation, $ValErrors);


        $FieldsName = array(
            "PListName",
            "PListDefault",
        );


        foreach ($FieldsName as $ii)
        {
            $DataFields[$ii] = $this->input[$ii];
        }

        $this->db->Insert($this->tables['plists'], $DataFields);
        $PListID = $this->db->lastInsertId();

        if ($this->input['PListDefault'])
        {
            $this->db->Update($this->tables['plists'], ['PListDefault'=>0], false, false, " AND PListID != :PListID AND Cancelled = 0", ['PListID'=>$PListID]);
        }

        BN_Responses::messageAjax('Registro Agregado', 'success', 'reload');

    }

    protected function cmd_edit()
    {

        $this->getPListInfo();

        $this->FormID .= "Edit";
        $this->document = $this->template("form");

        $FormFields['text'] = [

            'PListID',
            'PListName',
        ];

        $FormFields['checkbox'] = [

            'PListDefault',
        ];


        $FormJS = BN_Forms::populateJS($this->PListInfo, $FormFields, $this->FormID);
        $this->JS[] = $FormJS;
        $this->JS[] = BN_Forms::setValueText('cmd', "edit_save", $this->FormID);

        $this->tpldata['PListInfo'] = $this->PListInfo;

        $this->document = $this->populate($this->document);
        $this->document = $this->tplrender($this->document, $this->tpldata);


        BN_Responses::modal_content($this->document, 'Editar Lista', $this->JS);


    }


    function cmd_edit_save()
    {
        $this->getPListInfo();

        $this->input = BN_Coders::utf8_encode($this->input);


        // Validate Name
        $this->input['PListName'] = BN_Filters::name($this->input['PListName']);
        if ($this->db->TableInfo($this->tables['plists'], 'PListName', $this->input['PListName'], " AND PListID != :PListID AND Cancelled = 0 ", ['PListID'=>$this->PListInfo['PListID']]))
        {
            $ValErrors['PListName'] = "El nombre de la marca ya existe.";
        }


        // Required Fields
        $Validation['PListName']     = 'required';


        BN_Validation::Wizard($this->FormID, $this->input, $Validation, $ValErrors);


        $UpdateFields = [

            "PListName",
            "PListDefault",
        ];

        foreach($UpdateFields as $ii)
        {
            $UpdateData[$ii] = $this->input[$ii];
        }

        $this->db->Update($this->tables['plists'], $UpdateData, 'PListID', $this->PListInfo['PListID']);

        if ($this->input['PListDefault'])
        {
            $this->db->Update($this->tables['plists'], ['PListDefault'=>0], false, false, " AND PListID != :PListID AND Cancelled = 0", ['PListID'=>$this->input['PListID']]);
        }

        BN_Responses::messageAjax('Cambios Aplicados', 'success', 'reload');

    }


    protected function cmd_delete()
    {
        $this->getPListInfo();



        $TotalProducts = $this->db->Total($this->tables['prices'], 'PListID', $this->PListInfo['PListID'], " AND Cancelled = 0");

        if ($TotalProducts)
        {
            BN_Responses::modalHTML('Existen productos asociados. No es posible eliminar el registro', 'error');
        }

        if (!$this->input['delete'])
        {
            BN_Responses::confirmAjax('¿Deseas eliminar este registro?', false, false, 'delete');
        }



        $UpdateFields['Cancelled'] = 1;
        $this->db->Update($this->tables['plists'], $UpdateFields, 'PListID', $this->input['PListID'], " AND Cancelled = 0 ");

        BN_Messages::messageAjax('Cambios Aplicados', 'success', 'reload');

    }


    function getPListInfo($PListID = "", $ignore_error = false)
    {
        $PListID = $PListID?:$this->input['PListID'];

        if (!$PListID)
        {
            BN_Messages::messageAjax('El registro no existe.', 'error');
        }

        $this->PListInfo = $this->db->TableInfo($this->tables['plists'], 'PListID', $PListID, " AND Cancelled = 0 ");
        $this->PListInfo = BN_Encode::utf8_decode($this->PListInfo);
        $this->CallerInfo = $this->PListInfo;

        if (!$this->PListInfo && $ignore_error == false)
        {
            BN_Messages::messageAjax('El registro no existe.', 'error');
        }

    }

    function index_menu()
    {

        $MainMenu = array();
        $MainMenu['ModuleMenuL'] = novut\Inventory\Products\product::main_menu();
        $MainMenu['ModuleMenuR'] .= BN_Layouts::menu_item("Agregar Lista", 'javascript: void(0);', 'fa-plus', ['onclick', BN_JSHelpers::CMDRoute($this->ModuleUrl, 'new')]);
        $this->tpldata['MainMenu'] = BN_Layouts::setLayout(false, 'topmenu', $MainMenu);

    }

    function populate($document)
    {

        if ($document)
        {
            $TagData['ModuleUrl']         = $this->BaseUrl;
            $TagData['BaseUrl']           = BN_Var::$BaseUrl;
            $TagData['FormID']            = $this->FormID;
            $TagData['PListID']           = $this->PListInfo['PListID'];
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
        $data['PListID'] = $this->PListInfo['PListID'];

        $document = BN::tplrender($document, $data);
        return $document;
    }


}

