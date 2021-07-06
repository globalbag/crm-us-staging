<?php


class inventory_families_product_content extends inventory_families_product
{

    /** @var \App\Products\Brands\FamilyParam $f_param */
    protected $f_param;



    function cmd_content()
    {

        $this->FormID .= "Content";

        $view_data = [];

        $populate = new \Novut\UI\Forms\Populate($this->FormID, $this->product);
        $populate->setValueText('BrandID');
        $populate->setValueText('BFamilyID');
        $populate->setValueText('ProductID');
        $populate->setCodeMirror('ProductContent', null, null, true);
        $js = $populate->getJs(true);


        $this->layout->selectTab('content');
        $this->layout->setWebLib($this->layout->jsLibraries()->codemirror());
        $this->layout->setBrowserTitle("{$this->product->getProductName()} - Contenido");
        $this->layout->render($this->views->load_render('products/product/content', $this->view_data_presets($view_data)) ,"Contenido",$js);
    }

    function cmd_content_save()
    {
        $this->input['ProductContent'] = base64_decode($this->input['ProductContent']);
        $this->input['ProductContent'] = \BN_Coders::utf8_encode($this->input['ProductContent']);

        $product = new App\Products\Product\Product($this->product->getProductID());
        $product->setProductContent($this->input['ProductContent']);
        $product->save();

        $this->response->notification_success("Cambios Aplicados")->render();

    }
}
