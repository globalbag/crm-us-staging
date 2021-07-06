<?php

$products = new products_products();
$products->init();


class products_products extends inv_products
{

    protected $params;
    protected $catalogs;
    protected $tables;
    protected $InvoiceParams;
    protected $ProductInfo;
    /** @var \Novut\Core\Endpoint $endpoint */
    protected $endpoint;
    /** @var \App\Products\Product\Product $product */
    protected $product;

    function config_custom()
    {


        // Main Campaign
        $this->BaseUrl      = 'inventory/products/';
        $this->ModuleUrl    = $this->BaseUrl;
        $this->FormID       = 'InventoryProductsProducts';

        $this->params['var_fn'] = 'inventory_products_products';
        $this->params['var_tp'] = 'products/';

        $this->params['title']       = "Cat&aacute;logo de Productos";

        $this->tables['products']       = 'inv_products_products';
        $this->tables['categories']     = 'inv_products_categories';
        $this->tables['brands']         = 'inv_products_brands';
        $this->tables['plist']          = 'inv_products_plist';

        $this->catalogs['Taxes']['iva-yes'] = "IVA - Tasa 16%";
        $this->catalogs['Taxes']['iva-no'] = "IVA - Tasa 0%";
        $this->catalogs['Taxes']['na'] = "No Aplica";

        $this->InvoiceParams = \BN::param('InvoiceParams', 'json');

    }

    function view_data_presets_custom($data)
    {
        $data = $data ? $data : [];

        $data['ProductID'] = $this->product->getProductID();

        return $data;
    }

    function cmd_index()
    {
        $valid_views = ['enabled', 'disabled'];

        if (!in_array($this->input['view'], $valid_views))
        {
            $this->input['view'] = 'enabled';
        }


        $this->document = $this->template("index");

        $ProductMenuID = $this->FormID."Menu";

        // grid
        $this->GridFilter->setGridID($this->FormID);


        $FOptions = $this->GridFilter->export_table();

        $FOptions['Action'] = [
            'name'  => '&nbsp;',
            'width' => '1%',
        ];


        $ExtraQuery = "";
        $ExtraQuery .= " AND Cancelled = 0 ";
        $SQLParams = [];

        if ($this->input['view'] == 'enabled')
        {
            $ExtraQuery .= " AND ProductDisabled = 0 ";

        }
        else if ($this->input['view'] == 'disabled')
        {
            $ExtraQuery .= " AND ProductDisabled = 1 ";
        }


        // Table
        $Table = new BN_Table();
        $Table->setFormID($this->FormID);
        $Table->setModuleUrl($this->ModuleUrl);
        $Table->setModuleUrlExtra(['view' => $this->input['view']]);
        $Table->import_grid($FOptions);
        $Table->setHeader();


        $ExtraQuery .= " ";
        $sql_offset = $Table->navbar($this->tables['products'], array('query'=>"  {$ExtraQuery} {$this->GridFilter->getSQL()} ", "QVal"=> $this->GridFilter->getSQLParams($SQLParams)), $this->ModuleUrl, 50);



        $sth = $this->db->prepare("SELECT * FROM {$this->db->getTable($this->tables['products'])}  WHERE 1 {$ExtraQuery} {$this->GridFilter->getSQL()} {$this->GridFilter->getSort()} {$sql_offset}");
        $sth->execute($this->GridFilter->getSQLParams($SQLParams));
        while (($data = $sth->fetch(PDO::FETCH_ASSOC)) != false) {

            // dropdown menu
            $data['Action'] = BN_Layouts::dropdownmenu($ProductMenuID, 'nav', $data['ProductID']);
            //$data['ProductName'] = html::a('href', '#')->append($data['ProductName']);
            $data['ProductDisabled'] = $data['ProductDisabled']?"Si":"No";
            $data['BrandID'] = $data['BrandID']?novut\Inventory\Products\brands::name($data['BrandID']):"";
            $data['CategoryID'] = $data['CategoryID']?novut\Inventory\Products\categories::name($data['CategoryID']):"";


            //$data['TaxID'] = $data['TaxID']?(novut\Accounting\taxes::name($data['TaxID'])):"";


            $data['__BN_Attr__']['PPricePrice']['align'] = 'right';
            //$data['__BN_Attr__']['PPriceDiscountLimit']['align'] = 'right';
            $data['__BN_Attr__']['PPriceCurrencyCode']['align'] = 'center';

            $Table->row_fill($data);

        }

        $this->setTPLData('table', $Table->getTable());

        $this->JS[] = $this->GridFilter->getJS(true);


        // Dropdown menu
        $this->document .= BN_Layouts::dropdownmenu_items($ProductMenuID, [
            ['label' => 'Editar', 'action' => 'edit'],
            ['separator' => true],
            ['label' => 'Galer&iacute;a', 'action' => 'gallery'],
            ['label' => 'Precios', 'action' => 'prices'],
            ['label' => 'Documentos', 'action' => 'documents'],
            ['separator' => true],
            ['label' => 'Eliminar', 'action' => 'delete']
        ], $this->params['var_fn']);

        // MainMenu
        $this->index_menu();


        $this->setTPLData('input', $this->input);

        // Populate
        $this->document = $this->populate($this->document);
        $this->document = $this->tplrender($this->document, $this->tpldata);




        // Print
        $BNPrint = new BN_Print;
        $BNPrint->WebLib('bootstrap');
        $BNPrint->WebLib('select2');
        $BNPrint->WebLib('contextmenu');
        $BNPrint->JS($this->JS);
        $BNPrint->render($this->document, "Cat&aacute;logo de Productos");

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

    protected function cmd_new()
    {

        $this->FormID .= "New";


        $this->JS[] = BN_Forms::setValueText('cmd', "new_add", $this->FormID);

        // currency
        // $this->JS[] = BN_Forms::setValueHtml('PPriceCurrencyID', BN_Locale::currency_olist(), $this->FormID);
        // $this->JS[] = BN_Forms::setSelect2('PPriceCurrencyID', $this->FormID);
        // $this->JS[] = BN_Forms::setValueSelect2('PPriceCurrencyID', BN_Locale::currency_id('MXN'), $this->FormID);

        // brand
        $this->JS[] = BN_Forms::setValueHtml('BrandID', BN::OptionListEmpty().$this->db->OptionList('inv_products_brands', 'BrandID', 'BrandName', ' Cancelled = 0', false, 'BrandName', 'html'), $this->FormID);
        $this->JS[] = BN_Forms::setSelect2('BrandID', $this->FormID);
        $this->JS[] = BN_Forms::setSelect2('BFamilyID', $this->FormID);

        // category
        $this->JS[] = BN_Forms::setValueHtml('CategoryID', BN::OptionListEmpty().$this->db->OptionList('inv_products_categories', 'CategoryID', 'CategoryName', ' Cancelled = 0', false, 'CategoryName', 'html').BN::OptionListEmpty().html::option('value', '_new_')->append('[Nuevo]'), $this->FormID);
        $this->JS[] = BN_Forms::setSelect2('CategoryID', $this->FormID);

        // tax
        $this->JS[] = BN_Forms::setValueHtml('TaxID', BN::OptionListEmpty().$this->db->OptionList('accounting_taxes', 'TaxID', 'TaxName', ' Cancelled = 0', false, 'TaxName', 'html'), $this->FormID);
        $this->JS[] = BN_Forms::setSelect2('TaxID', $this->FormID);
        $this->JS[] = BN_Forms::setValueSelect2('TaxID', 1, $this->FormID);

        // tex v2
        $this->JS[] = BN_Forms::setValueHtml('SATTaxRef', BN::OptionListEmpty().BN::OptionListHTMLRender($this->catalogs['Taxes']), $this->FormID);
        $this->JS[] = BN_Forms::setSelect2('SATTaxRef', $this->FormID);
        $this->JS[] = BN_Forms::setValueSelect2('SATTaxRef', 'iva-yes', $this->FormID);



        // Producto SAT de Configuración


        $SATProductInfo = \novut\accounting\sat::getOptionInfo('ProductList', $this->InvoiceParams['InvoiceProduct'] ? : '01010101');
        if ($SATProductInfo)
        {
            $this->JS[] = BN_Forms::setDataSelect2('SATProductID', $SATProductInfo['OptionID'], "{$SATProductInfo['OptionName']} ({$SATProductInfo['OptionValue']})", $this->FormID);
        }


        $SATUnitInfo = \novut\accounting\sat::getOptionInfo('Unidades', $this->InvoiceParams['InvoiceUnit'] ? : 'H87');
        if ($SATUnitInfo)
        {
            $this->JS[] = BN_Forms::setDataSelect2('SATUnitID', $SATUnitInfo['OptionID'], "{$SATUnitInfo['OptionName']} ({$SATUnitInfo['OptionValue']})", $this->FormID);
        }




        $this->document = $this->template("form");
        $this->document = $this->populate($this->document);
        $this->document = $this->tplrender($this->document, $this->tpldata);

        BN_Responses::modal_content($this->document, 'Agregar Producto', $this->JS);
    }

    protected function cmd_new_add()
    {

        $this->FormID .= "New";

        $this->input = BN_Coders::utf8_encode($this->input);

        $this->input['ProductName'] = $this->formatName($this->input['ProductName']);

        if ($this->db->TableInfo($this->tables['products'], 'ProductName', $this->input['ProductName'], " AND Cancelled = 0 "))
        {
            $ValErrors['ProductName'] = "El nombre del producto ya existe.";
        }

        if ($this->input['ProductCode'] && $this->db->TableInfo($this->tables['products'], 'ProductCode', $this->input['ProductCode'], " AND Cancelled = 0 "))
        {
            $ValErrors['ProductCode'] = "El SKU ya existe.";
        }

        if ($this->input['ProductPart'] && $this->db->TableInfo($this->tables['products'], 'ProductPart', $this->input['ProductPart'], " AND Cancelled = 0 "))
        {
            $ValErrors['ProductPart'] = "El No. de parte ya existe.";
        }

        $this->input['ProductQTYTier_Min'] = (int) $this->input['ProductQTYTier_Min'];
        $this->input['ProductQTYTier_Max'] = (int) $this->input['ProductQTYTier_Max'];

        if ($this->input['ProductQTYTier_Min'] > 0 && $this->input['ProductQTYTier_Max'] < 1 || $this->input['ProductQTYTier_Min'] < 1 && $this->input['ProductQTYTier_Max'] > 0 || ($this->input['ProductQTYTier_Min'] > 0 && $this->input['ProductQTYTier_Min'] == $this->input['ProductQTYTier_Max']) || ($this->input['ProductQTYTier_Max'] > 0 && $this->input['ProductQTYTier_Min'] > $this->input['ProductQTYTier_Max']))
        {
            if ($this->input['ProductQTYTier_Min'] < 1)
            {
                $ValErrors['ProductQTYTier_Min'] = "Valor Inv&aacute;lido";
            }
            else if ($this->input['ProductQTYTier_Max'] < 1)
            {
                $ValErrors['ProductQTYTier_Max'] = "Valor Inv&aacute;lido";
            }
            else if ($this->input['ProductQTYTier_Min'] > $this->input['ProductQTYTier_Max'])
            {
                $ValErrors['ProductQTYTier_Max'] = "Valor Inv&aacute;lido";
            }
            else
            {
                $ValErrors['ProductQTYTier_Max'] = "Valor Duplicado";
            }
        }

        //Validation
        $Validation = array('ProductName' => 'required');

        BN_Validation::Wizard($this->FormID, $this->input, $Validation, $ValErrors);


        if ($this->input['BrandID'])
        {
            $BrandInfo = $this->db->TableInfo('inv_products_brands', 'BrandID', $this->input['BrandID']);
            $this->input['BrandName'] = $BrandInfo['BrandName'];
        }


        if ($this->input['CategoryID'])
        {
            $CategoryInfo = $this->db->TableInfo('inv_products_categories', 'CategoryID', $this->input['CategoryID']);
            if ($CategoryInfo)
            {
                $this->input['CategoryPath'] = $CategoryInfo['CategoryName'];
                //$this->input['CategoryPath'][$CategoryInfo['CategoryID']] = $CategoryInfo['CategoryName'];
                //$this->input['CategoryPath'] = BN_Encode::json_encode($this->input['CategoryPath']);
            }

        }

        // Producto SAT de Configuración
        BN_Load::model('accounting');
        if ($this->input['SATProductID']){

            $SATProductInfo = \novut\accounting\sat::getOptionInfo('ProductList', $this->input['SATProductID']);

            $this->input['SATProductCode'] = $SATProductInfo['OptionValue'];
            $this->input['SATProductName'] = $SATProductInfo['OptionName'];

        }

        if ($this->input['SATUnitID']){

            $SATUnitInfo = \novut\accounting\sat::getOptionInfo('Unidades', $this->input['SATUnitID']);

            $this->input['SATUnitCode'] = $SATUnitInfo['OptionValue'];
            $this->input['SATUnitName'] = $SATUnitInfo['OptionName'];

        }


        if ($this->input['SATTaxRef'] != 'na')
        {
            $SATTaxID = \novut\accounting\sat::getOptionID('Impuestos', false, 'IVA');
            $SATTaxInfo = \novut\accounting\sat::getOptionInfo('Impuestos', $SATTaxID);

            if ($this->input['SATTaxRef'] == 'iva-yes' && $SATTaxInfo)
            {
                $SATTaxInfo['OptionValue2'] = $this->db->getValue('accounting_taxes', 'TaxName', 'IVA', 'TaxValue', " AND Cancelled = 0 ");
            }

            $this->input['SATTaxInfo'][$SATTaxID]['id'] = $SATTaxID;
            $this->input['SATTaxInfo'][$SATTaxID]['code'] = $SATTaxInfo['OptionValue'];
            $this->input['SATTaxInfo'][$SATTaxID]['value'] = $SATTaxInfo['OptionValue2'];
            $this->input['SATTaxInfo'][$SATTaxID]['name'] = $SATTaxInfo['OptionName'];
        }

        $this->input['SATTaxInfo']  = \BN_Coders::json_encode($this->input['SATTaxInfo']);


        if ($this->input['ProductQTYTier_Min'] > 0 && $this->input['ProductQTYTier_Max'] > 0)
        {
            $this->input['ProductQTYTier'] = "{$this->input['ProductQTYTier_Min']}-{$this->input['ProductQTYTier_Max']}";
        }

        $FieldsName = array(

            'ProductCode',
            'ProductPart',
            'ProductName',
            'ProductDescription',
            'BrandID',
            'BrandName',
            'CategoryID',
            'CategoryPath',
            'ProductDisabled',
            'TaxID',

            'ProductQtyPP',

            'SATProductID',
            'SATProductCode',
            'SATProductName',

            'SATUnitID',
            'SATUnitName',
            'SATUnitCode',

            'SATTaxRef',
            'SATTaxInfo',

            'ProductQTYTier',

        );


        foreach ($FieldsName as $ii)
        {
            $DataFields[$ii] = $this->input[$ii];
        }

        $this->db->Insert($this->tables['products'], $DataFields);
        $ProductID = $this->db->lastInsertId();

        // Price
        //$this->price_new_add($ProductID, false, $this->input['PPricePrice'], $this->input['PPriceDiscountLimit'], $this->input['PPriceCurrencyID'], true);

        $product = new \App\Products\Product\Product($ProductID);

        // family
        $family = new \App\Products\Product\Family($product);
        $family->setBFamilyID($this->input['BFamilyID']);
        foreach ($this->input['PFamilyData'] as $key => $value)
        {
            $family->addParam($key, $value);
        }
        $family->save();

        BN_Responses::messageAjax('Registro Agregado', 'success', 'reload');

    }

    protected function cmd_edit()
    {

        $this->getProductInfo();

        $this->FormID .= "Edit";
        $this->document = $this->template("form");

        list($this->ProductInfo['ProductQTYTier_Min'], $this->ProductInfo['ProductQTYTier_Max']) = explode("-", $this->ProductInfo['ProductQTYTier']);
        $this->ProductInfo['ProductQtyPP'] = (int) $this->ProductInfo['ProductQtyPP'] > 0 ? $this->ProductInfo['ProductQtyPP'] : "";

        $FormFields['text'] = [

            'ProductID',
            'ProductCode',
            'ProductPart',
            'ProductName',
            'ProductDescription',

            'ProductQtyPP',

            //'PPricePrice',
            //'PPriceDiscountLimit',

            'ProductQTYTier_Min',
            'ProductQTYTier_Max',

        ];

        $FormFields['select2'] = [

            'BrandID',
            'CategoryID',
            'TaxID',
        ];
        $FormFields['checkbox'] = [

            'ProductDisabled',
        ];


        $FormJS = BN_Forms::populateJS($this->ProductInfo, $FormFields, $this->FormID);
        $this->JS[] = BN_Forms::setValueText('cmd', "edit_save", $this->FormID);

        //$this->JS[] = BN_Forms::setValueHtml('PPriceCurrencyID', BN_Locale::currency_olist(), $this->FormID);
        //$this->JS[] = BN_Forms::setSelect2('PPriceCurrencyID', $this->FormID);
        //$this->ProductInfo['PPriceCurrencyID'] =  $this->ProductInfo['PPriceCurrencyID'] ? : ( $this->ProductInfo['PPriceCurrencyCode'] ? BN_Locale::currency_id($this->ProductInfo['PPriceCurrencyCode']) : "");
        //$this->JS[] = BN_Forms::setValueSelect2('PPriceCurrencyID', $this->ProductInfo['PPriceCurrencyID'], $this->FormID);


        $this->JS[] = BN_Forms::setValueHtml('BrandID', BN::OptionListEmpty().$this->db->OptionList('inv_products_brands', 'BrandID', 'BrandName', ' Cancelled = 0', false, 'BrandName', 'html'), $this->FormID);
        $this->JS[] = BN_Forms::setSelect2('BrandID', $this->FormID);


        $this->JS[] = BN_Forms::setValueHtml('CategoryID', BN::OptionListEmpty().$this->db->OptionList('inv_products_categories', 'CategoryID', 'CategoryName', ' Cancelled = 0', false, 'CategoryName', 'html').BN::OptionListEmpty().html::option('value', '_new_')->append('[Nuevo]'), $this->FormID);
        $this->JS[] = BN_Forms::setSelect2('CategoryID', $this->FormID);

        $this->JS[] = BN_Forms::setValueHtml('TaxID', BN::OptionListEmpty().$this->db->OptionList('accounting_taxes', 'TaxID', 'TaxName', ' Cancelled = 0', false, 'TaxName', 'html'), $this->FormID);
        $this->JS[] = BN_Forms::setSelect2('TaxID', $this->FormID);

        $SATProductInfo = \novut\accounting\sat::getOptionInfo('ProductList', $this->ProductInfo['SATProductID']);
        if ($SATProductInfo)
        {
            $this->JS[] = BN_Forms::setDataSelect2('SATProductID', $SATProductInfo['OptionID'], "{$SATProductInfo['OptionName']} ({$SATProductInfo['OptionValue']})", $this->FormID);
        }


        $SATUnitInfo = \novut\accounting\sat::getOptionInfo('Unidades', $this->ProductInfo['SATUnitID']);
        if ($SATUnitInfo)
        {
            $this->JS[] = BN_Forms::setDataSelect2('SATUnitID', $SATUnitInfo['OptionID'], "{$SATUnitInfo['OptionName']} ({$SATUnitInfo['OptionValue']})", $this->FormID);
        }




        $this->ProductInfo['SATTaxInfo'] = BN_Coders::json_decode($this->ProductInfo['SATTaxInfo']);
        $this->ProductInfo['SATTaxRef'] = "";


        foreach ($this->ProductInfo['SATTaxInfo'] as $TaxInfo)
        {
            if ($this->ProductInfo['SATTaxRef'])
            {
                break;
            }

            if ($TaxInfo['code'] == '002' && $TaxInfo['value'] > 0)
            {
                $this->ProductInfo['SATTaxRef'] = 'iva-yes';
            }
            else if ($TaxInfo['code'] == '002' && (float) $TaxInfo['value'] == 0)
            {
                $this->ProductInfo['SATTaxRef'] = 'iva-no';
            }
            else
            {
                $this->ProductInfo['SATTaxRef'] = 'na';
            }

        }


        $this->JS[] = BN_Forms::setValueHtml('SATTaxRef', BN::OptionListEmpty().BN::OptionListHTMLRender($this->catalogs['Taxes']), $this->FormID);
        $this->JS[] = BN_Forms::setSelect2('SATTaxRef', $this->FormID);
        $this->JS[] = BN_Forms::setValueSelect2('SATTaxRef', $this->ProductInfo['SATTaxRef'], $this->FormID);



        $this->JS[] = $FormJS;

        if ($this->ProductInfo['BrandID'])
        {
            $brand_data['BrandID'] = $this->ProductInfo['BrandID'];
            $brand_data['BFamilyID'] = $this->ProductInfo['BFamilyID'];
            $brand_data['BFamilyData'] = $this->ProductInfo['BFamilyData'] && is_string($this->ProductInfo['BFamilyData']) ? \BN_Coders::json_decode($this->ProductInfo['BFamilyData']) : (is_array($this->ProductInfo['BFamilyData']) ? $this->ProductInfo['BFamilyData'] : null);
            $this->JS[] = "select_brand({$this->ProductInfo['BrandID']}, ". BN_JSHelpers::json_value($brand_data).");";
        }


        $this->tpldata['ProductInfo'] = $this->ProductInfo;

        $this->document = $this->populate($this->document);
        $this->document = $this->tplrender($this->document, $this->tpldata);


        BN_Responses::modal($this->document, 'Editar Producto', $this->JS, BN_Responses::modal_options()->setWidth(800));


    }


    function cmd_edit_save()
    {
        $this->getProductInfo();

        $this->FormID .= "Edit";

        $this->input = BN_Coders::utf8_encode($this->input);

        // Validate Name
        $this->input['ProductName'] = $this->formatName($this->input['ProductName']);
        if ($this->db->TableInfo($this->tables['products'], 'ProductName', $this->input['ProductName'], " AND ProductID != :ProductID AND Cancelled = 0 ", ['ProductID'=>$this->ProductInfo['ProductID']]))
        {
            $ValErrors['ProductName'] = "El nombre del producto ya existe.";
        }

        if ($this->input['ProductCode'] && $this->db->TableInfo($this->tables['products'], 'ProductCode', $this->input['ProductCode'], " AND ProductID != :ProductID AND Cancelled = 0 ", ['ProductID'=>$this->ProductInfo['ProductID']]))
        {
            $ValErrors['v'] = "El SKU ya existe.";
        }

        if ($this->input['ProductPart'] && $this->db->TableInfo($this->tables['products'], 'ProductPart', $this->input['ProductPart'], " AND ProductID != :ProductID AND Cancelled = 0 ", ['ProductID'=>$this->ProductInfo['ProductID']]))
        {
            $ValErrors['ProductPart'] = "El N&uacute;mero de Parte ya existe.";
        }

        $this->input['ProductQTYTier_Min'] = (int) $this->input['ProductQTYTier_Min'];
        $this->input['ProductQTYTier_Max'] = (int) $this->input['ProductQTYTier_Max'];

        if ($this->input['ProductQTYTier_Min'] > 0 && $this->input['ProductQTYTier_Max'] < 1 || $this->input['ProductQTYTier_Min'] < 1 && $this->input['ProductQTYTier_Max'] > 0 || ($this->input['ProductQTYTier_Min'] > 0 && $this->input['ProductQTYTier_Min'] == $this->input['ProductQTYTier_Max']) || ($this->input['ProductQTYTier_Max'] > 0 && $this->input['ProductQTYTier_Min'] > $this->input['ProductQTYTier_Max']))
        {
            if ($this->input['ProductQTYTier_Min'] < 1)
            {
                $ValErrors['ProductQTYTier_Min'] = "Valor Inv&aacute;lido";
            }
            else if ($this->input['ProductQTYTier_Max'] < 1)
            {
                $ValErrors['ProductQTYTier_Max'] = "Valor Inv&aacute;lido";
            }
            else if ($this->input['ProductQTYTier_Min'] > $this->input['ProductQTYTier_Max'])
            {
                $ValErrors['ProductQTYTier_Max'] = "Valor Inv&aacute;lido";
            }
            else
            {
                $ValErrors['ProductQTYTier_Max'] = "Valor Duplicado";
            }
        }

        // Required Fields
        $Validation['ProductName']     = 'required';


        BN_Validation::Wizard($this->FormID, $this->input, $Validation, $ValErrors);


        if ($this->input['BrandID'])
        {
            $BrandInfo = $this->db->TableInfo('inv_products_brands', 'BrandID', $this->input['BrandID']);
            $this->input['BrandName'] = $BrandInfo['BrandName'];
        }


        if ($this->input['CategoryID'])
        {
            $CategoryInfo = $this->db->TableInfo('inv_products_categories', 'CategoryID', $this->input['CategoryID']);
            if ($CategoryInfo)
            {
                $this->input['CategoryPath'] = $CategoryInfo['CategoryName'];
                //$this->input['CategoryPath'][$CategoryInfo['CategoryID']] = $CategoryInfo['CategoryName'];
                //$this->input['CategoryPath'] = BN_Encode::json_encode($this->input['CategoryPath']);
            }

        }



        // Producto SAT de Configuración
        if ($this->input['SATProductID']){

            $SATProductInfo = \novut\accounting\sat::getOptionInfo('ProductList', $this->input['SATProductID']);

            $this->input['SATProductCode'] = $SATProductInfo['OptionValue'];
            $this->input['SATProductName'] = $SATProductInfo['OptionName'];

        }

        if ($this->input['SATUnitID']){

            $SATUnitInfo = \novut\accounting\sat::getOptionInfo('Unidades', $this->input['SATUnitID']);

            $this->input['SATUnitCode'] = $SATUnitInfo['OptionValue'];
            $this->input['SATUnitName'] = $SATUnitInfo['OptionName'];

        }

        // tax v2
        if ($this->input['SATTaxRef'] != 'na')
        {
            $SATTaxID = \novut\accounting\sat::getOptionID('Impuestos', false, 'IVA');
            $SATTaxInfo = \novut\accounting\sat::getOptionInfo('Impuestos', $SATTaxID);

            if ($this->input['SATTaxRef'] == 'iva-yes' && $SATTaxInfo)
            {
                $SATTaxInfo['OptionValue2'] = $this->db->getValue('accounting_taxes', 'TaxName', 'IVA', 'TaxValue', " AND Cancelled = 0 ");
            }

            $this->input['SATTaxInfo'][$SATTaxID]['id'] = $SATTaxID;
            $this->input['SATTaxInfo'][$SATTaxID]['code'] = $SATTaxInfo['OptionValue'];
            $this->input['SATTaxInfo'][$SATTaxID]['value'] = $SATTaxInfo['OptionValue2'];
            $this->input['SATTaxInfo'][$SATTaxID]['name'] = $SATTaxInfo['OptionName'];
        }

        $this->input['SATTaxInfo']  = \BN_Coders::json_encode($this->input['SATTaxInfo']);


        if ($this->input['ProductQTYTier_Min'] > 0 && $this->input['ProductQTYTier_Max'] > 0)
        {
            $this->input['ProductQTYTier'] = "{$this->input['ProductQTYTier_Min']}-{$this->input['ProductQTYTier_Max']}";
        }


        $UpdateFields = [

            'ProductCode',
            'ProductPart',
            'ProductName',
            'ProductDescription',
            'BrandID',
            'BrandName',
            'CategoryID',
            'CategoryPath',
            'ProductDisabled',

            'ProductQtyPP',

            'TaxID',

            'SATProductID',
            'SATProductCode',
            'SATProductName',

            'SATUnitID',
            'SATUnitName',
            'SATUnitCode',

            'SATTaxRef',
            'SATTaxInfo',

            'ProductQTYTier',

        ];

        foreach($UpdateFields as $ii)
        {
            $UpdateData[$ii] = $this->input[$ii];
        }

        $this->db->Update($this->tables['products'], $UpdateData, 'ProductID', $this->ProductInfo['ProductID']);
        $product = new \App\Products\Product\Product($this->ProductInfo['ProductID']);

        // Price
        //$this->price_edit_save($this->ProductInfo['PPriceID'], $this->ProductInfo['PListID'], $this->input['PPricePrice'], $this->input['PPriceDiscountLimit'], $this->input['PPriceCurrencyID'], $this->ProductInfo['ProductID']);




        // family
        $family = new \App\Products\Product\Family($product);
        $family->setBFamilyID($this->input['BFamilyID']);
        foreach ($this->input['PFamilyData'] as $key => $value)
        {
            $family->addParam($key, $value);
        }
        $family->save();

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

    protected function cmd_select_brand()
    {
        $response = [];
        $brand = new \App\Products\Brands\Brand($this->input['BrandID']);

        $response['FormID'] = $this->input['FormID'];
        $response['BrandID'] = $brand->getBrandID();

        $response['families'] = \Novut\UI\Forms\Helpers::optionEmpty();
        if ($brand->getBrandID())
        {
            $response['families'] .= \Novut\UI\Forms\Helpers::option_list_render(\App\Products\Brands\Families::list_simple($brand));
        }

        BN_Responses::routeData($response);


    }

    protected function cmd_select_family()
    {
        $response = [];
        $brand = new \App\Products\Brands\Brand($this->input['BrandID']);
        $family = new \App\Products\Brands\Family($brand, $this->input['BFamilyID']);

        $response['FormID'] = $this->input['FormID'];
        $response['BrandID'] = $brand->getBrandID();
        $response['BFamilyID'] = $family->getBFamilyID();
        $response['options'] = $this->views->load_render('products/params', ['params' => \App\Products\Brands\Families::paramCollection($brand, $family)]);

        BN_Responses::routeData($response);


    }

    protected function cmd_sat_products()
    {

        $this->input['q'] = BN_Encode::utf8_decode($this->input['q']);

        $QVal['q'] = "%{$this->input['q']}%";
        $QVal['OptionGroupCode'] = "ProductList";

        $resultJson = [];

        // Products
        $sth = $this->db->prepare("SELECT * FROM {$this->db->getTable('accounting_sat_options_items')} WHERE OptionGroupCode = :OptionGroupCode AND (OptionName LIKE :q OR OptionValue LIKE :q) AND Cancelled = 0 ORDER BY OptionName ");
        $sth->execute($QVal);

        while(($data = $sth->fetch(PDO::FETCH_ASSOC)) != false)
        {
            $resultJson[] = array('id' => $data['OptionID'], "label" => "{$data['OptionName']} ({$data['OptionValue']})");
        }

        BN_Print::JSON($resultJson);
    }


    protected function cmd_sat_units()
    {

        $this->input['q'] = BN_Encode::utf8_decode($this->input['q']);

        $QVal['q'] = "%{$this->input['q']}%";
        $QVal['OptionGroupCode'] = "Unidades";

        $resultJson = [];

        // Products
        $sth = $this->db->prepare("SELECT * FROM {$this->db->getTable('accounting_sat_options_items')} WHERE OptionGroupCode = :OptionGroupCode AND (OptionName LIKE :q  OR OptionValue LIKE :q) AND Cancelled = 0 ORDER BY OptionName ");
        $sth->execute($QVal);

        while(($data = $sth->fetch(PDO::FETCH_ASSOC)) != false)
        {
            $resultJson[] = array('id' => $data['OptionID'], "label" => "{$data['OptionName']} ({$data['OptionValue']})");
        }

        BN_Print::JSON($resultJson);
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

