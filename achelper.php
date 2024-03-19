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
        Configuration::updateValue('ACHelper_TopLevelDomain', '') &&
        Configuration::updateValue('ACHelper_lgcookieslawID', '');
    }

    public function uninstall()
    {
        Configuration::deleteByName('ACHelper_GMeasureID');
        Configuration::deleteByName('ACHelper_TopLevelDomain');
        Configuration::deleteByName('ACHelper_lgcookieslawID');
        return parent::uninstall();
    }

    public function hookDisplayHeader($params)
    {
        return "<script type=\"text/javascript\">
                    //Setup Consent for Matomo
                    var _paq = window._paq = window._paq || [];
                    _paq.push(['requireCookieConsent']);
                    
                    //Setup Consent for Google Analytics
                    window.dataLayer = window.dataLayer || [];
                    function gtag(){dataLayer.push(arguments);}
                    gtag('consent', 'default', {
                        'ad_storage': 'denied',
                        'ad_user_data': 'denied',
                        'ad_personalization': 'denied',
                        'analytics_storage': 'denied'
                    });

                    //Activate tracking code
                    function allConsentGranted() {
                        //Activate Matomo with cookies
                        _paq.push(['rememberCookieConsentGiven']);

                        //Activate Google Analytics
                        gtag('consent', 'update', {
                            'ad_user_data': 'granted',
                            'ad_personalization': 'granted',
                            'ad_storage': 'granted',
                            'analytics_storage': 'granted'
                        });
                    }
                </script>";
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
        $helper->fields_value['ACHelper_lgcookieslawID'] = Configuration::get('ACHelper_lgcookieslawID');
        return $helper->generateForm(array($fields_form));
    }

    protected function postProcess()
    {
        Configuration::updateValue('ACHelper_TopLevelDomain', Tools::getValue('ACHelper_TopLevelDomain'));
        Configuration::updateValue('ACHelper_GMeasureID', Tools::getValue('ACHelper_GMeasureID'));
        Configuration::updateValue('ACHelper_lgcookieslawID', Tools::getValue('ACHelper_lgcookieslawID'));
    }
    
    public function hookDisplayBeforeBodyClosingTag($params)
    {
        return "<script type=\"text/javascript\">
                    _paq.push(['trackPageView']);

                    function ACHelperDeleteCookie(name, domain, path) {
                        let cookieString = name + \"=; expires=Thu, 01 Jan 1970 00:00:00 GMT\";
                        if (domain) {
                            cookieString += \"; domain=\" + domain;
                        }
                        if (path) {
                            cookieString += \"; path=\" + path;
                        }
                        document.cookie = cookieString;
                    }

                    if (typeof lgcookieslaw_cookie_values === 'object') {
                        if (lgcookieslaw_cookie_values === null) {
                            //Confirm Cookies consent status when the user first enters the page (lgcookieslaw_cookie_values is null at this time)

                            // Select the element that controls the consent for Analytics Cookies
                            const cookiesConsentToggle = document.getElementById('lgcookieslaw_slider_".Configuration::get('ACHelper_lgcookieslawID')."');

                            // Define a function to handle changes in the consent toggle for cookies
                            const onConsentToggleChange = function(mutationsList, observer) {
                                for (let mutation of mutationsList) {
                                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                                        const currentClass = cookiesConsentToggle.className;
                                        // Check if the class attribute matches our target state
                                        if (currentClass.includes('lgcookieslaw-slider') && currentClass.includes('lgcookieslaw-slider-checked')) {
                                            allConsentGranted();
                                        }
                                        consentObserver.disconnect();
                                    }
                                }
                            }

                            // Instantiate MutationObserver with a callback to monitor attribute changes
                            const consentObserver = new MutationObserver(onConsentToggleChange);

                            // Start observing the class attribute changes of the cookies consent toggle
                            consentObserver.observe(cookiesConsentToggle, { attributes: true });
                        }
                        else {
                            if (lgcookieslaw_cookie_values.lgcookieslaw_purpose_".Configuration::get('ACHelper_lgcookieslawID')." === true) {
                                allConsentGranted();
                            }
                            else {
                                _paq.push(['forgetCookieConsentGiven']);
                                ACHelperDeleteCookie('_ga_".Configuration::get('ACHelper_GMeasureID')."', '.".Configuration::get('ACHelper_TopLevelDomain')."', '/');
                                ACHelperDeleteCookie('_ga', '.".Configuration::get('ACHelper_TopLevelDomain')."', '/');
                            }
                        }
                    }
                </script>";
    }

}
