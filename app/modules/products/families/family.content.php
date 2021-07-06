<?php


class inventory_families_family_content extends inventory_families_family
{

    /** @var \App\Products\Brands\FamilyParam $f_param */
    protected $f_param;



    function cmd_content()
    {

        $this->FormID .= "Content";

        $view_data = [];

        $populate = new \Novut\UI\Forms\Populate($this->FormID, $this->family);
        $populate->setValueText('BrandID');
        $populate->setValueText('BFamilyID');
        $populate->setCodeMirror('BFamilyContent', null, null, true);
        $js = $populate->getJs(true);


        $this->layout->selectTab('content');
        $this->layout->setWebLib($this->layout->jsLibraries()->codemirror());
        $this->layout->setBrowserTitle("{$this->family->getBFamilyName()} - Contenido");
        $this->layout->render($this->views->load_render('content/content', $this->view_data_presets($view_data)) ,"Contenido",$js);
    }

    function cmd_content_save()
    {
        $this->input['BFamilyContent'] = base64_decode($this->input['BFamilyContent']);
        $this->input['BFamilyContent'] = \BN_Coders::utf8_encode($this->input['BFamilyContent']);

        $family = new \App\Products\Brands\Family($this->brand, $this->family->getBFamilyID());
        $family->setBFamilyContent($this->input['BFamilyContent']);
        $family->save();

        $this->response->notification_success("Cambios Aplicados")->render();

    }
}
