<?php


class inventory_families_product_prices extends inventory_families_product
{

    /** @var \App\Products\Product\Price $product_price */
    protected $product_price;

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


    function getPriceInfo()
    {
        $PPriceID = $PPriceID? : $this->input['PPriceID'];


        $this->product_price = $PPriceID ? new \App\Products\Product\Price($this->product, null, $PPriceID) : null;

        if (!$this->product_price || !$this->product_price->getPPriceID())
        {
            BN_Messages::messageAjax('El registro no existe.', 'error');
        }

    }


    function cmd_prices()
    {

        $view_data = [];
        $js = [];

        $table = new \Novut\Tools\Tables\Table($this->FormID);
        $table->setClass('table');

        $table->addField('PPriceVolMin', 'Min', '20%');
        $table->addField('PPriceVolMax', 'Max', '20%');
        $table->addField('PPricePrice', 'Precio', '30%');
        $table->addField('PPriceCurrencyCode', 'Moneda', '20%');
        $table->addField('action', '', '1%');


        /** @var \App\Products\Product\Price $price */
        foreach (\App\Products\Product\Prices::list($this->product, (new \App\Products\Prices\PList())->loadDefault(true), (new \Novut\Core\Query())->setOrder('PPriceVolMin', 'ASC')) as $price)
        {
            $data = $price->export();
            $data['PPriceVolMin'] = \BN_Format::number_currency_enduser($data['PPriceVolMin']);
            $data['PPriceVolMax'] = \BN_Format::number_currency_enduser($data['PPriceVolMax']);
            $data['PPricePrice'] = \BN_Format::number_currency_enduser($data['PPricePrice']);
            $data['action'] = $table->context_menu()->getPlaceholder('PPriceID', $data['PPriceID'], ['ProductID' => $data['ProductID']]);
            $table->addRow($data);
        }

        $table->context_menu()->add_menu('Editar', $table->context_menu()->action('prices_edit', $this->base_url_params_product()));
        $table->context_menu()->add_menu('Eliminar', $table->context_menu()->action('prices_delete', $this->base_url_params_product()));

        $view_data['table'] = $table->getTable();

        $this->layout->selectTab('prices');
        $this->layout->setBrowserTitle("{$this->product->getProductName()} - Precios");
        $this->layout->render($this->views->load_render('products/product/prices/index', $this->view_data_presets($view_data)) ,"Precios",$js);
    }


    function cmd_prices_new()
    {

        $this->FormID .= "ProceNew";
        $js = [];
        $view_data = [];

        $populate = new \Novut\UI\Forms\Populate($this->FormID);
        $populate->setValueText('BrandID', $this->product->getBrandID());
        $populate->setValueText('BFamilyID', $this->product->getBFamilyID());
        $populate->setValueText('ProductID', $this->product->getProductID());
        $populate->setValueHtml('PPriceCurrencyID', \BN_Currency::getCurrencyList());
        $populate->setSelect2('PPriceCurrencyID');
        
        $js = $populate->getJs();

        $view_data['cmd'] = "prices_new_add";

        $this->response->modal($this->views->load_render('products/product/prices/form', $this->view_data_presets($view_data)), 'Agregar Precio', $js)->render();

    }


    function cmd_prices_new_add()
    {

        $this->FormID .= "PriceNew";


        $validation = \BN_Forms::validation($this->FormID);
        $validation->setRequiredField('PPriceVolMin');
        $validation->setRequiredField('PPricePrice');
        $validation->setRequiredField('PPriceCurrencyID');

        foreach (['PPriceVolMin', 'PPriceVolMax', 'PPricePrice'] as $ii)
        {
            $this->input[$ii] = \BN_Format::number_decimal_raw($this->input[$ii]);
        }

        $validation->validate($this->input);


        try {

            $price = new \App\Products\Product\Price($this->product, null);
            $price->setPPriceVolMin($this->input['PPriceVolMin']);
            $price->setPPriceVolMax($this->input['PPriceVolMax']);
            $price->setPPricePrice($this->input['PPricePrice']);
            $price->setPPriceCurrency(\BN_Currency::getCurrencyIDbyCode($this->input['PPriceCurrencyID']));
            $price->add();

        } catch (\Novut\Exceptions\Exception $e) {
            $e->display();
        }

        \App\Products\Product\Prices::syncDefault($this->product);

        $this->response->notification_success('Registro Agregado', 'reload')->render();


    }

    function cmd_prices_edit()
    {
        $this->getPriceInfo();

        $product_price = $this->product_price->export();
        $js = [];
        $view_data = [];
        $view_data['cmd']  = "prices_edit_save";
        $this->FormID .= "PriceEdit";

        $populate = new \Novut\UI\Forms\Populate($this->FormID, $this->product_price);

        $populate->setValueText('BrandID', $this->product->getBrandID());
        $populate->setValueText('BFamilyID', $this->product->getBFamilyID());
        $populate->setValueText('ProductID', $this->product->getProductID());

        $populate->setValueText('PPriceID');
        $populate->setValueText('PPriceVolMin');
        $populate->setValueText('PPriceVolMax');
        $populate->setValueText('PPricePrice');

        $populate->setSelect2('PPriceCurrencyID', \BN_Forms::option_empty().\BN_Currency::getCurrencyList());
        $populate->setValueSelect2('PPriceCurrencyID', $this->product_price->getPPriceCurrencyCode());


        $js[] = $populate->getJs();
        $this->response->modal(
            $this->views->load_render('products/product/prices/form', $this->view_data_presets($view_data)),
            "Editar Precio",
            $js
        )->render();

    }

    function cmd_prices_edit_save()
    {
        $this->getPriceInfo();
        $this->FormID .= "PriceEdit";

        $validation = \BN_Forms::validation($this->FormID);
        $validation->setRequiredField('PPriceVolMin');
        $validation->setRequiredField('PPricePrice');
        $validation->setRequiredField('PPriceCurrencyID');

        foreach (['PPriceVolMin', 'PPriceVolMax', 'PPricePrice'] as $ii)
        {
            $this->input[$ii] = \BN_Format::number_decimal_raw($this->input[$ii]);
        }

        $validation->validate($this->input);


        try {

            $price = new \App\Products\Product\Price($this->product, null, $this->product_price->getPPriceID());
            $price->setPPriceVolMin($this->input['PPriceVolMin']);
            $price->setPPriceVolMax($this->input['PPriceVolMax']);
            $price->setPPricePrice($this->input['PPricePrice']);
            $price->setPPriceCurrency(\BN_Currency::getCurrencyIDbyCode($this->input['PPriceCurrencyID']));
            $price->save();

        } catch (\Novut\Exceptions\Exception $e) {
            $e->display();
        }

        \App\Products\Product\Prices::syncDefault($this->product);


        $this->response->notification_success("Cambios Aplicados")->reload()->render();
    }

    function cmd_prices_delete()
    {
        $this->getPriceInfo();

        if (!$this->input['confirm'])
        {
            $this->response->confirm_simple("&iquest;Deseas eliminar este registro?", 'confirm');
        }

        $price = new \App\Products\Product\Price($this->product, null, $this->product_price->getPPriceID());
        $price->cancel();

        \App\Products\Product\Prices::syncDefault($this->product);

        $this->response->notification_success("Cambios Aplicados")->reload()->render();
    }


}
