<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class ACHelper extends Module
{
    public function __construct()
    {
        $this->name = 'achelper';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Hacc';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Analytics Cookies Helper');
        $this->description = $this->l('Analytics cookies consent help plugin.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    public function install()
    {
        return parent::install() &&
        $this->registerHook('displayHeader') &&
        $this->registerHook('displayBeforeBodyClosingTag') &&
        Configuration::updateValue('ACHelper_GMeasureID', '') &&
        Configuration::updateValue('ACHelper_GA_URL_Passthrough', '') &&
        Configuration::updateValue('ACHelper_GA_Ads_Data_Redaction', '') &&
        Configuration::updateValue('ACHelper_TopLevelDomain', '') &&
        Configuration::updateValue('ACHelper_lgcookieslawID', '');
    }

    public function uninstall()
    {
        Configuration::deleteByName('ACHelper_GMeasureID');
        Configuration::deleteByName('ACHelper_GA_URL_Passthrough');
        Configuration::deleteByName('ACHelper_GA_Ads_Data_Redaction');
        Configuration::deleteByName('ACHelper_TopLevelDomain');
        Configuration::deleteByName('ACHelper_lgcookieslawID');
        return parent::uninstall();
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submit'.$this->name)) {
            $this->postProcess();
            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }
        return $output.$this->displayForm();
    }

    public function displayForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Top Level Domain'),
                        'class' => 't',
                        'desc' => $this->l('We need those things to delete cookies for our user.'),
                        'name' => 'ACHelper_TopLevelDomain'
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Google Measurement ID'),
                        'class' => 't',
                        'name' => 'ACHelper_GMeasureID'
                    ),
                    array(
                        'type' => 'checkbox',
                        'name' => 'ACHelper',
                        'desc'    => $this->l('When a user arrives at your website from an ad, ad information can '.
                                            'be attached to the URL or stored in cookies to enhance conversion tracking; '.
                                            'if storing data is restricted, using URL passthrough allows for improved ad click '.
                                            'and analytics measurement without cookies.'),
                        'class' => 't',
                        'values' => array(
                            'query' => array(
                                array(
                                    'id_option' => 'GA_URL_Passthrough',
                                    'name' => $this->l('Google Analytics GCLID and DCLID URL Passthrough'),
                                ),
                            ),
                            'id' => 'id_option',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'checkbox',
                        'name' => 'ACHelper',
                        'desc'    => $this->l('When ads_data_redaction is enabled and ad_storage is denied, '.
                                            'identifiers in Google Ads and Floodlight tag requests will be redacted, '.
                                            'and these requests will use a cookieless domain.'),
                        'class' => 't',
                        'values' => array(
                            'query' => array(
                                array(
                                    'id_option' => 'GA_Ads_Data_Redaction',
                                    'name' => $this->l('Google Analytics Ads Data Redaction'),
                                ),
                            ),
                            'id' => 'id_option',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('lgcookieslaw plugin purpose ID'),
                        'class' => 't',
                        'name' => 'ACHelper_lgcookieslawID'
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save')
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->module = $this;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG')
                : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit'.$this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->fields_value['ACHelper_TopLevelDomain'] = Configuration::get('ACHelper_TopLevelDomain');
        $helper->fields_value['ACHelper_GMeasureID'] = Configuration::get('ACHelper_GMeasureID');
        $helper->fields_value['ACHelper_GA_URL_Passthrough'] = Configuration::get('ACHelper_GA_URL_Passthrough');
        $helper->fields_value['ACHelper_GA_Ads_Data_Redaction'] = Configuration::get('ACHelper_GA_Ads_Data_Redaction');
        $helper->fields_value['ACHelper_lgcookieslawID'] = Configuration::get('ACHelper_lgcookieslawID');
        return $helper->generateForm(array($fields_form));
    }

    protected function postProcess()
    {
        Configuration::updateValue('ACHelper_TopLevelDomain', Tools::getValue('ACHelper_TopLevelDomain'));
        Configuration::updateValue('ACHelper_GMeasureID', Tools::getValue('ACHelper_GMeasureID'));
        Configuration::updateValue('ACHelper_GA_URL_Passthrough', Tools::getValue('ACHelper_GA_URL_Passthrough'));
        Configuration::updateValue('ACHelper_GA_Ads_Data_Redaction', Tools::getValue('ACHelper_GA_Ads_Data_Redaction'));
        Configuration::updateValue('ACHelper_lgcookieslawID', Tools::getValue('ACHelper_lgcookieslawID'));
    }

    public function hookDisplayHeader($params)
    {
        $templateFile = 'module:'.$this->name. '/views/templates/hook/header.tpl';
        $cacheId = $this->getCacheId();
        if (!$this->isCached($templateFile, $cacheId)) {
            $this->context->smarty->assign(array(
                'GA_URL_Passthrough' => Configuration::get('ACHelper_GA_URL_Passthrough') == 'on',
                'GA_Ads_Data_Redaction' => Configuration::get('ACHelper_GA_Ads_Data_Redaction') == 'on',
            ));
        }
        return $this->context->smarty->fetch($templateFile, $cacheId);
    }
    
    public function hookDisplayBeforeBodyClosingTag($params)
    {
        $templateFile = 'module:'.$this->name. '/views/templates/hook/before-body-closing-tag.tpl';
        $cacheId = $this->getCacheId();
        if (!$this->isCached($templateFile, $cacheId)) {
            $this->context->smarty->assign(array(
                'TopLevelDomain' => Configuration::get('ACHelper_TopLevelDomain'),
                'GMeasureID' => Configuration::get('ACHelper_GMeasureID'),
                'lgcookieslawID' => Configuration::get('ACHelper_lgcookieslawID'),
            ));
        }
        return $this->context->smarty->fetch($templateFile, $cacheId);
    }

}
