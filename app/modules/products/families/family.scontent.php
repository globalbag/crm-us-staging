<?php


class inventory_families_family_scontent extends inventory_families_family
{

    /** @var \App\Products\Brands\FamilyParam $f_param */
    protected $f_param;



    function cmd_scontent()
    {

        $this->FormID .= "Content";

        $view_data = [];

        $populate = new \Novut\UI\Forms\Populate($this->FormID, $this->family);
        $populate->setValueText('BrandID');
        $populate->setValueText('BFamilyID');
        $populate->setCodeMirror('BFamilySContent', null, null, true);
        $js = $populate->getJs(true);


        $this->layout->selectTab('scontent');
        $this->layout->setWebLib($this->layout->jsLibraries()->codemirror());
        $this->layout->setBrowserTitle("{$this->family->getBFamilyName()} - Contenido");
        $this->layout->render($this->views->load_render('content/scontent', $this->view_data_presets($view_data)) ,"Contenido",$js);
    }

    function cmd_scontent_save()
    {
        $this->input['BFamilySContent'] = base64_decode($this->input['BFamilySContent']);
        $this->input['BFamilySContent'] = \BN_Coders::utf8_encode($this->input['BFamilySContent']);

        $family = new \App\Products\Brands\Family($this->brand, $this->family->getBFamilyID());
        $family->setBFamilySContent($this->input['BFamilySContent']);
        $family->save();

        $this->response->notification_success("Cambios Aplicados")->render();

    }
}
