<?php

class HelloworldPageModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $display_column_right = false;

    public function initContent()
    {
        parent::initContent();

        $idLang = Context::getContext()->language->id;

        if ($this->is16orOlder()) {
            $template = 'page_1_6_older.tpl';
        } else {
            $template = 'module:helloworld/views/templates/front/page.tpl';
        }

        $this->context->smarty->assign(array(
            'helloworld_title' => Configuration::get('HELLOWORLD_TITLE', $idLang),
            'helloworld_desc' => Configuration::get('HELLOWORLD_DESC', $idLang),
        ));

        $this->setTemplate($template);
    }

    private function is16orOlder()
    {
        return version_compare(_PS_VERSION_, '1.7.0.0', '<');
    }
}