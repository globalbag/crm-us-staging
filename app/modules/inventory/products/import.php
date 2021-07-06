<?php

$products = new products_import();
$products->init();


class products_import extends \novut\Inventory\Products\product
{

    /** @var  \novut\DB\BN_PDOMethods|\PDO|\PDOStatement */
    protected $db;

    protected $input;
    protected $FormID;
    protected $BaseUrl;
    protected $document;
    protected $JS;
    protected $ProductInfo;
    protected $CookieID;
    protected $tables;
    protected $params;
    protected $ImportFields;


    public function init()
    {

        $this->db = BN::DB();
        $this->input = BN::input();

        // Main Campaign
        $this->BaseUrl      = 'inventory/products/import/';
        $this->FormID       = 'InventoryProductsImport';

        $this->params['var_fn'] = 'inventory_products_products';
        $this->params['var_tp'] = 'import/';

        $this->tables['products']       = 'inv_products_products';
        $this->tables['plist']          = 'inv_products_plist';

        $this->params['Config'] = BN::yaml(__DIR__."/import.yml");
        foreach($this->params['Config']['ImportFields'] as $FieldInfo)
        {
            $this->ImportFields[$FieldInfo['id']] = $FieldInfo;
            $this->ImportFields[$FieldInfo['id']]['field'] = $FieldInfo['field']?:$FieldInfo['id'];

        }
        $this->ImportFields = BN_Coders::utf8_decode($this->ImportFields);


        $this->CookieID = md5($this->BaseUrl);
        $this->CookieID = substr($this->CookieID, 0, round(strlen($this->CookieID) / 3));


        $this->tables['products']       = 'inv_products_products';
        $this->tables['categories']     = 'inv_products_categories';
        $this->tables['brands']         = 'inv_products_brands';
        $this->tables['plist']          = 'inv_products_plist';

        $method = BN_Methods::cmd();
        $this->$method();

    }

    function cmd_index()
    {



        $this->FormID .= "Request";
        $this->document = $this->template("request");
        $this->document = $this->populate($this->document);

        BN_Responses::modal_content($this->document, 'Importar Productos', $this->JS);

    }

    function cmd_layout()
    {


        $csv = new parseCSV();

        foreach($this->ImportFields as $FieldInfo)
        {
            $CSVDataLine[] = $FieldInfo['name'];
        }


        $CSVData[] = $CSVDataLine;

        $csv->output('import_products.csv', $CSVData);
        exit;
    }

    function cmd_associate()
    {



        $this->FormID .= "Request";

        $RandCode = BN::random_code();


        $this->input['ImportFile'] = $_FILES['ImportFile']['tmp_name'];
        $Validation['ImportFile'] = 'required';

        // Ext.
        $ValidExtensions[] = "csv";
        $ValidExtensions[] = "txt";
        $FileExt = strtolower(end(explode('.', $_FILES['ImportFile']['name'], PATHINFO_EXTENSION)));

        if ($this->input['ImportFile'] && !in_array($FileExt, $ValidExtensions))
        {
            $ValErrors['ImportFile'] = "El formato de archivo es incorrecto.";
        }
        else if($this->input['ImportFile'])
        {

            $mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv');
            if(in_array($_FILES['ImportFile']['type'],$mimes))
            {

                $csvFile = new Keboola\Csv\CsvFile($_FILES['ImportFile']['tmp_name']);

                foreach($csvFile as $row)
                {
                    $Lines[] = $row;
                }

                if (count($Lines) < 2)
                {
                    $ValErrors['ImportFile'] = "Archivo no contiene registros.";
                }
                else
                {
                    $FilePath = "{$RandCode}.csv";
                    BN_FileH::copy_file($_FILES['ImportFile']['tmp_name'], $FilePath, BN_Var::$Paths['temp']);
                }


            } else {

                $ValErrors['ImportFile'] = "El formato de archivo es incorrecto";
            }

        }


        BN_Validation::Wizard($this->FormID, $this->input, $Validation, $ValErrors);


        foreach($this->ImportFields as $FieldInfo)
        {
            $this->JS[] = BN_Forms::setSelect2("ImportFieldID-{$FieldInfo['id']}", $this->FormID);
        }

        $col = 0;
        foreach($Lines[0] as $ColName)
        {
            $col++;
            $ImportFields[$col]['id'] = $col;
            $ImportFields[$col]['name'] = $ColName;
        }


        $tpldata['FieldList'] = $this->ImportFields;
        $tpldata['FieldList'] = $this->ImportFields;
        $tpldata['ImportFields'] = $ImportFields;
        $tpldata['ImportID'] = $RandCode;


        $this->document = $this->template("associate");
        $this->document = $this->populate($this->document);
        $this->document = BN::tplrender($this->document, $tpldata);

        BN_Responses::modal_content($this->document, 'Asociar Campos', $this->JS);

    }

    function cmd_import()
    {
        $this->FormID .= "Request";

        foreach(array_keys($this->input) as $ii)
        {
            if (substr($ii, 0, 14) == 'ImportFieldID-')
            {
                $this->input['Fields'][substr($ii, 14)] = $this->input[$ii];
            }
        }

        if (!$this->input['ImportID'] || !$this->input['Fields'])
        {
            BN_Responses::messageAjax('La solicitud no existe.', 'error');
        }

        // Required Field
        foreach($this->ImportFields as $ImportFieldInfo)
        {
            if ($ImportFieldInfo['required'] && !$this->input['Fields'][$ImportFieldInfo['id']])
            {
                $Validation['ImportFieldID-'.$ImportFieldInfo['id']] = 'required';
            }

            $FieldNames[$ImportFieldInfo['id']] = $ImportFieldInfo['name'];
        }

        BN_Validation::Wizard($this->FormID, $this->input, $Validation);


        // parse CSV
        $this->parse_csv($this->input['ImportID'], $this->input['Fields']);

        // ValidateData

        foreach ($this->ImportData as $id => $data) {



            foreach($this->ImportFields as $ImportFieldInfo)
            {
                if ($ImportFieldInfo['required'] && !$data[$ImportFieldInfo['id']])
                {
                    $ErrorList[] = ['field' => $ImportFieldInfo['id'], 'type'=>'required', 'line'=>$data['_line_']];
                }
            }



            // BrandID
            if ($data['BrandID'])
            {

                    $BrandInfo = $this->db->TableInfo($this->tables['brands'], false, false, " AND (BrandID = :BrandID OR BrandName = :BrandID) AND Cancelled = 0 ", ['BrandID'=>$data['BrandID']]);

                    if ($BrandInfo)
                    {
                        $this->ImportData[$id]['BrandID'] = $BrandInfo['BrandID'];
                    }
                    else
                    {
                        $ErrorList[] = ['field' => 'BrandID', 'type'=>'unknown_brand', 'line'=>$data['_line_']];
                    }

            }



            // CategoryID
            if ($data['CategoryID'])
            {

                $CategoryInfo = $this->db->TableInfo($this->tables['categories'], false, false, " AND (CategoryID = :CategoryID OR CategoryName = :CategoryID) AND Cancelled = 0 ", ['CategoryID'=>$data['CategoryID']]);
                if ($CategoryInfo)
                {
                    $this->ImportData[$id]['CategoryID'] = $CategoryInfo['CategoryID'];
                }
                else
                {
                    $ErrorList[] = ['field' => 'CategoryID', 'type'=>'unknown_category', 'line'=>$data['_line_']];
                }

            }


            // ProductCode
            if ($data['ProductCode'])
            {
                if (!BN_Var::$temp['ImportVal']['ProductCode'][$data['ProductCode']])
                {
                    $ProductInfo = $this->db->TableInfo($this->tables['products'], false, false, " AND ProductCode = :ProductCode AND Cancelled = 0 ", ['ProductCode'=>$data['ProductCode']]);

                    if ($ProductInfo)
                    {
                        BN_Var::$temp['ImportVal']['ProductCode'][$data['ProductCode']] = 2;
                    }
                    else
                    {
                        BN_Var::$temp['ImportVal']['ProductCode'][$data['ProductCode']] = 1;
                    }

                }

                if (BN_Var::$temp['ImportVal']['ProductCode'][$data['ProductCode']] != 1)
                {
                    $ErrorList[] = ['field' => 'ProductCode', 'type'=>'pcode_exist', 'line'=>$data['_line_']];
                }
            }
            // ProductName

            if (!BN_Var::$temp['ImportVal']['ProductName'][$data['ProductName']])
            {
                $ProductInfo = $this->db->TableInfo($this->tables['products'], false, false, " AND ProductName = :ProductName AND Cancelled = 0 ", ['ProductName'=>$data['ProductName']]);

                if ($ProductInfo)
                {
                    BN_Var::$temp['ImportVal']['ProductName'][$data['ProductName']] = 2;
                }
                else
                {
                    BN_Var::$temp['ImportVal']['ProductName'][$data['ProductName']] = 1;
                }

            }

            if (BN_Var::$temp['ImportVal']['ProductName'][$data['ProductName']] != 1)
            {
                $ErrorList[] = ['field' => 'ProductName', 'type'=>'pname_exist', 'line'=>$data['_line_']];
            }

            /*
            if ($data['PPricePrice'])
            {
                $data['PPricePrice'] = preg_replace("/[^0-9.0]/", "", $ph_number);
            }
            */

        }



        if (count($ErrorList) > 0) {


            $tpldata['FieldNames'] = $FieldNames;
            $tpldata['ErrorList'] = $ErrorList;
            $tpldata['Alerts'] = BN_Coders::utf8_decode($this->params['Config']['ImportAlerts']);
            $this->document = $this->template("error");
            $this->document = $this->populate($this->document);
            $this->document = BN::tplrender($this->document, $tpldata);

            BN_Responses::modal_content($this->document, 'Error en Importaci&oacute;n', $this->JS);
        }

        $products = 0;
        foreach ($this->ImportData as $id => $data) {

            $this->insert_product($data);
            $products++;

        }


        // JSON JS
        $JS[] = "BN.WinClose()";
        $JS[] = BN_Responses::modalAjax("Se agregaron {$products} productos.", 'info', 'reload', false, false, true);
        BN_Print::JSON_JS($JS);

    }


    function insert_product($input = "")
    {

        $input['PPriceCurrency'] = 'USD';
        $input['TaxID'] = '1';


        foreach(['PPricePrice', 'PPriceDiscountLimit'] as $ii)
        {
            $input[$ii] = preg_replace("/[^0-9.0]/", "", $input[$ii]);
        }

        $FieldsName = array(

            'ProductCode',
            'ProductName',
            'ProductDescription',
            'BrandID',
            'CategoryID',
            'ProductDisabled',
            'TaxID',
        );


        foreach ($FieldsName as $ii)
        {
            $DataFields[$ii] = $input[$ii];
        }

        $this->db->Insert($this->tables['products'], $DataFields);
        $ProductID = $this->db->lastInsertId();

        // Price
        $PPriceInfo = $this->price_new_add($ProductID, false, $input['PPricePrice'], $input['PPriceDiscountLimit'], $input['PPriceCurrency']);
        $UpdatePPrice['PPriceID'] = $PPriceInfo['PPriceID'];
        $UpdatePPrice['PListID'] = $PPriceInfo['PListID'];
        $UpdatePPrice['PPricePrice'] = $PPriceInfo['PPricePrice'];
        $UpdatePPrice['PPriceDiscountLimit'] = $PPriceInfo['PPriceDiscountLimit'];
        $UpdatePPrice['PPriceCurrency'] = $PPriceInfo['PPriceCurrency'];
        $this->db->Update($this->tables['products'], $UpdatePPrice, 'ProductID', $ProductID, " AND Cancelled = 0");

    }

    function parse_csv($ImportID = "", $FieldMap = [])
    {

        if (count(implode("\n", $ImportID) == 1))
        {
            $ImportContent = BN_FileH::open_file(BN_Var::$Paths['temp']."/{$ImportID}.csv");
        }
        else
        {
            $ImportContent = $ImportID;
        }


        $csv = new parseCSV();
        $csv->heading = false;
        $csv->parse($ImportContent);


        foreach($FieldMap as $Field => $Value)
        {
            $MapField[$Field] = $Value -1;
        }

        unset($csv->data[0]);

        foreach($this->ImportFields as $FieldInfo)
        {
            $this->ImportFields[$FieldInfo['id']]['col'] = $MapField[$FieldInfo['id']];
        }




        $lines = 0;
        foreach($csv->data as $LineInfo)
        {
            $lines++;

            foreach($this->ImportFields as $FieldInfo)
            {
                $this->ImportData[$lines][$FieldInfo['field']] = trim($LineInfo[$FieldInfo['col']]);

            }

            $this->ImportData[$lines]['ProductDescription'] = str_replace('\n', "\n", $this->ImportData[$lines]['ProductDescription']);
            $this->ImportData[$lines]['_line_'] = $lines + 1;

        }

        return $this->ImportData;


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

            "ProductName",
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
//        $ListFilter->WhereFields[] = array("Name"=>"ProductCode", "Type"=>"like");
//        $ListFilter->WhereFields[] = array("Name"=>"BrandID", "Type"=>"equal");

        /***********************************
         * start
         ***********************************/
        $ListFilter->Start();


        /***********************************
         * end
         ***********************************/
        $ExtraJS .= "$('#flt-BrandProduct').on('select2-open', function() { $('.select2-drop-active').css('min-width', '200px'); });\n";
        $ExtraJS .= $ListFilter->End();


        return array($ListFilter->WhereQuery, $ListFilter->SortQuery, $ExtraJS, $ListFilter->QVal);

    }

    protected function cmd_new()
    {

        $this->FormID .= "New";
        $this->document = $this->template("form");
        $this->document = $this->populate($this->document);

        $this->JS[] = BN_Forms::setValueText('cmd', "new_add", $this->FormID);
        $this->JS[] = BN_Forms::setValueHtml('PPriceCurrency', BN_Locale::currency_olist(), $this->FormID);
        $this->JS[] = BN_Forms::setSelect2('PPriceCurrency', $this->FormID);

        $this->JS[] = BN_Forms::setValueHtml('BrandID', BN::OptionListEmpty().$this->db->OptionList('inv_products_brands', 'BrandID', 'BrandName', ' Cancelled = 0', false, 'BrandName', 'html'), $this->FormID);
        $this->JS[] = BN_Forms::setSelect2('BrandID', $this->FormID);

        $this->JS[] = BN_Forms::setValueHtml('CategoryID', BN::OptionListEmpty().$this->db->OptionList('inv_products_categories', 'CategoryID', 'CategoryName', ' Cancelled = 0', false, 'CategoryName', 'html').BN::OptionListEmpty().html::option('value', '_new_')->append('[Nuevo]'), $this->FormID);
        $this->JS[] = BN_Forms::setSelect2('CategoryID', $this->FormID);

        $this->JS[] = BN_Forms::setValueHtml('TaxID', BN::OptionListEmpty().$this->db->OptionList('accounting_taxes', 'TaxID', 'TaxName', ' Cancelled = 0', false, 'TaxName', 'html'), $this->FormID);
        $this->JS[] = BN_Forms::setSelect2('TaxID', $this->FormID);



        BN_Responses::modal_content($this->document, 'Agregar Producto', $this->JS);
    }

    protected function cmd_new_add()
    {

        $this->FormID .= "New";

        $this->input = BN_Coders::utf8_encode($this->input);

        $this->input['ProductName'] = BN_Filters::name($this->input['ProductName']);

        if ($this->db->TableInfo($this->tables['products'], 'ProductName', $this->input['ProductName'], " AND Cancelled = 0 "))
        {
            $ValErrors['ProductName'] = "El nombre del producto ya existe.";
        }
        if ($this->input['ProductCode'] && $this->db->TableInfo($this->tables['products'], 'ProductCode', $this->input['ProductCode'], " AND Cancelled = 0 "))
        {
            $ValErrors['ProductCode'] = "El SKU ya existe.";
        }

        //Validation
        $Validation = array('ProductName' => 'required');

        BN_Validation::Wizard($this->FormID, $this->input, $Validation, $ValErrors);




        $FieldsName = array(

            'ProductCode',
            'ProductName',
            'ProductDescription',
            'BrandID',
            'CategoryID',
            'ProductDisabled',
            'TaxID',
        );


        foreach ($FieldsName as $ii)
        {
            $DataFields[$ii] = $this->input[$ii];
        }

        $this->db->Insert($this->tables['products'], $DataFields);
        $ProductID = $this->db->lastInsertId();

        // Price
        $PPriceInfo = $this->price_new_add($ProductID, false, $this->input['PPricePrice'], $this->input['PPriceDiscountLimit'], $this->input['PPriceCurrency']);
        $UpdatePPrice['PPriceID'] = $PPriceInfo['PPriceID'];
        $UpdatePPrice['PListID'] = $PPriceInfo['PListID'];
        $UpdatePPrice['PPricePrice'] = $PPriceInfo['PPricePrice'];
        $UpdatePPrice['PPriceDiscountLimit'] = $PPriceInfo['PPriceDiscountLimit'];
        $UpdatePPrice['PPriceCurrency'] = $PPriceInfo['PPriceCurrency'];
        $this->db->Update($this->tables['products'], $UpdatePPrice, 'ProductID', $ProductID, " AND Cancelled = 0");

        BN_Responses::messageAjax('Registro Agregado', 'success', 'reload');

    }

    protected function cmd_edit()
    {

        $this->getProductInfo();

        $this->FormID .= "Edit";
        $this->document = $this->template("form");

        $FormFields['text'] = [

            'ProductID',
            'ProductCode',
            'ProductName',
            'ProductDescription',

            'PPricePrice',
            'PPriceDiscountLimit',

        ];

        $FormFields['select2'] = [

            'PPriceCurrency',
            'BrandID',
            'CategoryID',
            'TaxID',
        ];
        $FormFields['checkbox'] = [

            'ProductDisabled',
        ];


        $FormJS = BN_Forms::populateJS($this->ProductInfo, $FormFields, $this->FormID);
        $this->JS[] = BN_Forms::setValueText('cmd', "edit_save", $this->FormID);

        $this->JS[] = BN_Forms::setValueHtml('PPriceCurrency', BN_Locale::currency_olist(), $this->FormID);
        $this->JS[] = BN_Forms::setSelect2('PPriceCurrency', $this->FormID);

        $this->JS[] = BN_Forms::setValueHtml('BrandID', BN::OptionListEmpty().$this->db->OptionList('inv_products_brands', 'BrandID', 'BrandName', ' Cancelled = 0', false, 'BrandName', 'html'), $this->FormID);
        $this->JS[] = BN_Forms::setSelect2('BrandID', $this->FormID);

        $this->JS[] = BN_Forms::setValueHtml('CategoryID', BN::OptionListEmpty().$this->db->OptionList('inv_products_categories', 'CategoryID', 'CategoryName', ' Cancelled = 0', false, 'CategoryName', 'html').BN::OptionListEmpty().html::option('value', '_new_')->append('[Nuevo]'), $this->FormID);
        $this->JS[] = BN_Forms::setSelect2('CategoryID', $this->FormID);

        $this->JS[] = BN_Forms::setValueHtml('TaxID', BN::OptionListEmpty().$this->db->OptionList('accounting_taxes', 'TaxID', 'TaxName', ' Cancelled = 0', false, 'TaxName', 'html'), $this->FormID);
        $this->JS[] = BN_Forms::setSelect2('TaxID', $this->FormID);


        $this->JS[] = $FormJS;

        $this->tpldata['ProductInfo'] = $this->ProductInfo;

        $this->document = $this->populate($this->document);
        $this->document = $this->tplrender($this->document, $this->tpldata);


        BN_Responses::modal_content($this->document, 'Editar Producto', $this->JS);


    }


    function cmd_edit_save()
    {
        $this->getProductInfo();


        $this->input = BN_Coders::utf8_encode($this->input);

        // Validate Name
        $this->input['ProductName'] = BN_Filters::name($this->input['ProductName']);
        if ($this->db->TableInfo($this->tables['products'], 'ProductName', $this->input['ProductName'], " AND ProductID != :ProductID AND Cancelled = 0 ", ['ProductID'=>$this->ProductInfo['ProductID']]))
        {
            $ValErrors['ProductName'] = "El nombre del producto ya existe.";
        }

        if ($this->input['ProductCode'] && $this->db->TableInfo($this->tables['products'], 'ProductCode', $this->input['ProductCode'], " AND ProductID != :ProductID AND Cancelled = 0 ", ['ProductID'=>$this->ProductInfo['ProductID']]))
        {
            $ValErrors['ProductName'] = "El SKU ya existe.";
        }



        // Required Fields
        $Validation['ProductName']     = 'required';


        BN_Validation::Wizard($this->FormID, $this->input, $Validation, $ValErrors);


        $UpdateFields = [

            'ProductCode',
            'ProductName',
            'ProductDescription',
            'BrandID',
            'CategoryID',
            'ProductDisabled',
            'TaxID',

        ];

        foreach($UpdateFields as $ii)
        {
            $UpdateData[$ii] = $this->input[$ii];
        }

        $this->db->Update($this->tables['products'], $UpdateData, 'ProductID', $this->ProductInfo['ProductID']);

        // Price
        $PPriceInfo = $this->price_edit_save($this->ProductInfo['PPriceID'], $this->ProductInfo['PListID'], $this->input['PPricePrice'], $this->input['PPriceDiscountLimit'], $this->input['PPriceCurrency']);
        $UpdatePPrice['PListID'] = $PPriceInfo['PListID'];
        $UpdatePPrice['PPricePrice'] = $PPriceInfo['PPricePrice'];
        $UpdatePPrice['PPriceDiscountLimit'] = $PPriceInfo['PPriceDiscountLimit'];
        $UpdatePPrice['PPriceCurrency'] = $PPriceInfo['PPriceCurrency'];
        $this->db->Update($this->tables['products'], $UpdatePPrice, 'ProductID', $this->ProductInfo['ProductID'], " AND Cancelled = 0");


        BN_Responses::messageAjax('Cambios Aplicados', 'success', 'reload');

    }


    protected function cmd_delete()
    {
        $this->getProductInfo();

        if (!$this->input['delete'])
        {
            BN_Responses::confirmAjax('¿Deseas eliminar este registro?', false, false, 'delete');
        }

        $UpdateFields['Cancelled'] = 1;
        $this->db->Update($this->tables['products'], $UpdateFields, 'ProductID', $this->input['ProductID'], " AND Cancelled = 0 ");

        BN_Messages::messageAjax('Cambios Aplicados', 'success', 'reload');

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

    }

    function index_menu()
    {

        $MainMenu = array();
        $MainMenu['ModuleMenuL'] = novut\Inventory\Products\product::main_menu();
        $MainMenu['ModuleMenuR'] .= BN_Layouts::menu_item("Agregar Producto", 'javascript: void(0);', 'fa-plus', ['onclick', BN_JSHelpers::CMDRoute($this->ModuleUrl, 'new')]);
        $this->tpldata['MainMenu'] = BN_Layouts::setLayout(false, 'topmenu', $MainMenu);

    }

    function populate($document)
    {

        if ($document)
        {
            $TagData['ModuleUrl']         = $this->BaseUrl;
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

