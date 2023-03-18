<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Helloworld extends Module
{
    private $_html;

    // Language ISO codes you want to use in fixtures
    private $langDefault = array('pl', 'en');

    // Default configuration array (multi-language fields)
    private $configurationDefault = array(
        'HELLOWORLD_TITLE' =>
            array(
                'defaults' => array(
                    'pl' => 'Cześć X13',
                    'en' => 'Hello X13',
                ),
                'type' => 'text',
                'label' => 'Title',
                'lang' => true,
            ),
        'HELLOWORLD_DESC' =>
            array(
                'defaults' => array(
                    'pl' => '',
                    'en' => '',
                ),
                'type' => 'text',
                'label' => 'Description',
                'lang' => true,
            ),
        'HELLOWORLD_URL' =>
            array(
                'defaults' => array(
                    'pl' => 'cześć',
                    'en' => 'hello-world',
                ),
                'type' => 'text',
                'label' => 'URL (slug)',
                'lang' => true,
                'required' => false,
            ),
    );

    public function __construct()
    {
        $this->name = 'helloworld';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Kamil';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Hello World!');
        $this->description = $this->l('Lorem ipsum dolor sit amet...');
        $this->ps_versions_compliancy = ['min' => '1.6.0.0', 'max' => _PS_VERSION_];
        $this->_html = '';
    }

    public function install()
    {
        return parent::install() && $this->installFixtures() && $this->registerHook('moduleRoutes');
    }

    public function hookModuleRoutes($params)
    {
        $routes = array();
        $languages = $this->getLanguagesByIso();

        if (count($languages) == 0) {
            return $routes;
        }

        foreach ($languages as $iso => $idLang) {
            $value = Configuration::get('HELLOWORLD_URL', $idLang);

            $routes['module-helloworld-page-' . $iso] = array(
                'controller' => 'page',
                'rule' => $value,
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => $this->name,
                )
            );
        }

        return $routes;
    }

    private function getLanguagesByIso()
    {
        $data = array();

        if (!$this->isDefinedArray($this->langDefault)) {
            return $data;
        }

        foreach ($this->langDefault as $iso) {
            $id = (int)Language::getIdByIso($iso);

            if ($id > 0) {
                $data[$iso] = $id;
            }
        }

        return $data;
    }

    private function installFixtures()
    {
        if (!$this->isDefinedArray($this->configurationDefault)) {
            return false;
        }

        $languages = $this->getLanguagesByIso();

        foreach ($this->configurationDefault as $configurationField => $values) {
            $languageValues = array();

            foreach ($values['defaults'] as $iso => $value) {
                if (isset($languages[$iso])) {
                    $languageValues[$languages[$iso]] = $value;
                }
            }

            Configuration::updateValue($configurationField, $languageValues);
        }

        return true;
    }

    private function isDefinedArray()
    {
        if (!isset($this->configurationDefault)) {
            return false;
        }

        if (!is_array($this->configurationDefault)) {
            return false;
        }

        if (count($this->configurationDefault) == 0) {
            return false;
        }

        return true;
    }

    private function getTranslatedText($text)
    {
        $translation = array(
            'Title' => $this->l('Title'),
            'Description' => $this->l('Description'),
            'URL (slug)' => $this->l('URL (slug)'),
        );

        return isset($translation[$text]) ? $translation[$text] : $text;
    }

    public function renderForm()
    {
        $inputs = array();
        $i = 0;

        foreach ($this->configurationDefault as $key => $values) {
            $inputs[$i]['name'] = $key;
            $inputs[$i]['type'] = $values['type'];
            $inputs[$i]['label'] = $this->getTranslatedText($values['label']);

            if (isset($values['lang'])) {
                $inputs[$i]['lang'] = $values['lang'];
            }

            if (isset($values['required'])) {
                $inputs[$i]['required'] = $values['required'];
            }
            $i++;
        }

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => $inputs,
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?
            Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit' . ucfirst($this->name);
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        $arrayKeys = array_keys($this->configurationDefault);
        $array = array();
        $languages = $this->getLanguagesByIso();

        foreach ($arrayKeys as $key) {
            foreach ($languages as $idLang) {
                $array[$key][$idLang] = Configuration::get($key, $idLang);
            }
        }

        return $array;
    }

    protected function postProcess()
    {
        $languages = $this->getLanguagesByIso();

        foreach ($this->getConfigFieldsValues() as $field => $values) {
            $valueLang = array();

            foreach ($languages as $idLang) {
                $valueLang[$idLang] = Tools::getValue($field . '_' . $idLang);
            }

            Configuration::updateValue($field, $valueLang);
        }
    }

    public function getContent()
    {
        if (Tools::isSubmit('submit' . ucfirst($this->name))) {
            $this->postProcess();
            $this->_html .= $this->displayConfirmation($this->l('Settings updated.'));
        }

        $this->_html .= $this->renderForm();

        return $this->_html;
    }
}