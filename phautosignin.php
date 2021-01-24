<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    PrestaHeroes <info@prestaheroes.com>
 *  @copyright 2020 Heroic Business Solutions LLC
 *  @license   LICENSE.txt
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class Phautosignin extends Module implements WidgetInterface
{
    protected $config_form = false;
    public $config;

    public function __construct()
    {
        $this->name = 'phautosignin';
        $this->tab = 'front_office_features';
        $this->version = '1.0.2';
        $this->author = 'Prestaheroes';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Google One Tap Sign In');
        $this->description = $this->l('Sign in automatically visitors when they visit website');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module ?');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('PHAUTOSIGNIN_ACTIVATE', false);
        Configuration::updateValue('PHAUTOSIGNIN_CLIENTID', null);
        Configuration::updateValue('PHAUTOSIGNIN_CLIENTSECRET', null);
        Configuration::updateValue('PHAUTOSIGNIN_AUTOSIGNINSILENTLY', false);
        Configuration::updateValue('PHAUTOSIGNIN_PAGE_LIST', 'cart');
        Configuration::updateValue('PHAUTOSIGNIN_RELOADPAGEAFTERLOGIN', true);
        Configuration::updateValue('PHAUTOSIGNIN_CANCELONTAPOUTSIDE', true);
        Configuration::updateValue('PHAUTOSIGNIN_POSITION', 1);

        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayBeforeBodyClosingTag') &&
            $this->registerHook('actionFrontControllerSetVariables') &&
            $this->registerHook('actionFrontControllerSetMedia') &&
            $this->registerHook('actionCustomerLogoutAfter');
    }

    public function uninstall()
    {
        Configuration::deleteByName('PHAUTOSIGNIN_ACTIVATE');
//       /* Configuration::deleteByName('PHAUTOSIGNIN_CLIENTID');
//        Configuration::deleteByName('PHAUTOSIGNIN_CLIENTSECRET');*/
        Configuration::deleteByName('PHAUTOSIGNIN_AUTOSIGNINSILENTLY');
        Configuration::deleteByName('PHAUTOSIGNIN_PAGE_LIST');
        Configuration::deleteByName('PHAUTOSIGNIN_RELOADPAGEAFTERLOGIN');
        Configuration::deleteByName('PHAUTOSIGNIN_CANCELONTAPOUTSIDE');
        Configuration::deleteByName('PHAUTOSIGNIN_POSITION');

        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitPhautosigninModule')) == true) {
            $this->postProcess();
            $this->context->smarty->assign(
                array(
                    'message'=> 1
                )
            );
        }
        if ((Tools::isSubmit('submitPhautosigninModuleIP'))) {
            $this->saveIpProcess();
        }


        $this->context->smarty->assign(
            array(
                'module_dir'=>$this->_path,
                "currentIndexPH"=> $this->context->link->getAdminLink('AdminModules', false)
                    .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name
                    .'&token='.Tools::getAdminTokenLite('AdminModules'),

                'ph_cssPath' => $this->_path . '/views/css/front.css'
            )
        );
        $msgInfo = $this->context->smarty->fetch($this->local_path.'views/templates/admin/msg-info.tpl');

        return $msgInfo.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitPhautosigninModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $pages = array(
            array(
                'id' => 'allpages',
                'name' => 'Activate In All pages',
                'value' => 'all'
            ),
        );
        $idposition = array(
            array(
                'idposition' => 1,
                'name' => $this->l('Bottom right')
            ),
            array(
                'idposition' => 2,
                'name' => $this->l('Top right')
            ),
            array(
                'idposition' => 3,
                'name' => $this->l('Top left')
            ),
            array(
                'idposition' => 4,
                'name' => $this->l('Bottom left')
            ),
        );

        $controllers = Dispatcher::getControllers(_PS_FRONT_CONTROLLER_DIR_);
        ksort($controllers);

        foreach ($controllers as $key => $value) {
            $pages[] = array(
                'id' => $key,
                'name' => $key,
                'value' => $value
            );
        }

        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'tabs' => array(
                    'configuration' => $this->l('Configuration'),
                    'pageSettings' => $this->l('Page Setting'),
                    'documentation' => $this->l('Documentation'),
                ),
                'input' => array(
                   /* array(
                        'type' => 'switch',
                        'label' => $this->l('Enable module'),
                        'name' => 'PHAUTOSIGNIN_ACTIVATE',
                        'is_bool' => true,
                        'desc' => $this->l('Activate or Deactivate module'),
                        'hint' => '',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),*/
                    array(
                        'type' => 'text',
                        'tab' => 'configuration',
                        'label' => $this->l('Client ID key'),
                        'name' => 'PHAUTOSIGNIN_CLIENTID',
                        'is_bool' => true,
                        'hint' => $this->l('Web application-type client ID from Google Console'),
                        'desc' => '',
                    ),
                    array(
                        'type' => 'text',
                        'tab' => 'configuration',
                        'label' => $this->l('Client Secret key'),
                        'name' => 'PHAUTOSIGNIN_CLIENTSECRET',
                        'hint' => $this->l('Web application-type client secret key from Google Console'),
                    ),
                     array(
                        'type' => 'switch',
                        'tab' => 'configuration',
                        'label' => $this->l('Auto sign in silently'),
                        'name' => 'PHAUTOSIGNIN_AUTOSIGNINSILENTLY',
                        'is_bool' => true,
                        'desc' => '',
                         'hint' => $this->l(
                             'When enabled visitor will be sign in automatically.
                              When disabled a popup will be display and customer 
                              will be ask to choose an account to use for sign in.'
                         ),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'tab' => 'configuration',
                        'label' => $this->l('Reload page after login'),
                        'name' => 'PHAUTOSIGNIN_RELOADPAGEAFTERLOGIN',
                        'is_bool' => true,
                        'desc' => '',
                        'hint' => $this->l('Reload the current page after the user is logged'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'tab' => 'configuration',
                        'label' => $this->l('Cancel on tap outside'),
                        'name' => 'PHAUTOSIGNIN_CANCELONTAPOUTSIDE',
                        'is_bool' => true,
                        'desc' => '',
                        'hint' => $this->l('Cancel or not One Tap request if the user clicks outside of the prompt'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'tab' => 'configuration',
                        'label' => $this->l('Position:'),
                        'name' => 'PHAUTOSIGNIN_POSITION',
                        'hint' => $this->l(
                            'Choose one of 4 positions of sign in prompt.'
                        ),
                        'options' => array(
                        'query' => $idposition,
                        'id' => 'idposition',
                        'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'tab' => 'pageSettings',
                        'class' => 'chosen',
                        'label' => $this->l('Page into:'),
                        'name' => 'PHAUTOSIGNIN_PAGE_LIST[]',
                        'hint' => $this->l(
                            'Choose one or more page to activate the auto sign in.'
                        ),
                        'multiple' => true,
                        'options' => array(
                            'query' => $pages,
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'html',
                        'tab' => 'documentation',
                        'name' => 'html_documentation',
                        'html_content' => $this->displayDocumentation(),
                        'class' => 'col-lg-12'
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    public function displayDocumentation()
    {
        return $this->context->smarty->fetch($this->local_path.'views/templates/admin/documentation.tpl');
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'PHAUTOSIGNIN_ACTIVATE' => Configuration::get('PHAUTOSIGNIN_ACTIVATE', true),
            'PHAUTOSIGNIN_CLIENTID' => Configuration::get('PHAUTOSIGNIN_CLIENTID', true),
            'PHAUTOSIGNIN_CLIENTSECRET' => Configuration::get('PHAUTOSIGNIN_CLIENTSECRET', true),
            'PHAUTOSIGNIN_AUTOSIGNINSILENTLY' => Configuration::get('PHAUTOSIGNIN_AUTOSIGNINSILENTLY', true),
            'PHAUTOSIGNIN_PAGE_LIST[]' => explode(';', Configuration::get('PHAUTOSIGNIN_PAGE_LIST')),
            'PHAUTOSIGNIN_RELOADPAGEAFTERLOGIN' => Configuration::get('PHAUTOSIGNIN_RELOADPAGEAFTERLOGIN', true),
            'PHAUTOSIGNIN_CANCELONTAPOUTSIDE' => Configuration::get('PHAUTOSIGNIN_CANCELONTAPOUTSIDE', true),
            'PHAUTOSIGNIN_POSITION' => (int) Configuration::get('PHAUTOSIGNIN_POSITION')
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        foreach (array_keys($form_values) as $key) {
            if ($key == 'PHAUTOSIGNIN_PAGE_LIST[]') {
                $ph_page_list_val = (Tools::getValue('PHAUTOSIGNIN_PAGE_LIST')) ?
                    implode(';', Tools::getValue('PHAUTOSIGNIN_PAGE_LIST')) :
                    null;
                Configuration::updateValue(
                    'PHAUTOSIGNIN_PAGE_LIST',
                    $ph_page_list_val
                );
            } else {
                Configuration::updateValue($key, Tools::getValue($key));
            }
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookActionFrontControllerSetMedia()
    {
        // It the module is activated
        if ((bool)Configuration::get('PHAUTOSIGNIN_AUTOSIGNINSILENTLY') && !Context::getContext()->cookie->isLogged()) {
//        if ((bool)Configuration::get('PHAUTOSIGNIN_AUTOSIGNINSILENTLY') && !$this->context->customer->isLogged()) {
            $this->context->controller->registerJavascript(
                'phautosignin-gaccount',
                'https://accounts.google.com/gsi/client',
                [
                    'server' => 'remote',
                    'position' => 'bottom',
                    'priority' => 20,
                ]
            );
            $this->context->controller->registerJavascript(
                'phautosignin-javascript',
                $this->_path.'views/js/front.js',
                [
                    'position' => 'bottom',
                    'priority' => 1000,
                ]
            );
        }
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        $this->context->smarty->assign($this->getWidgetVariables($hookName, $configuration));
        return null;
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        //return array();
        return [
            'hookName' => $hookName,
            'configuration' => $configuration
        ];
    }

    public function hookDisplayBeforeBodyClosingTag()
    {
        if (Context::getContext()->cookie->isLogged()) {
//        if ($this->context->customer->isLogged()) {
            return;
        }

        $isGoogleAutoSignInAvailable = false;
        $activatedPages = explode(';', Configuration::get('PHAUTOSIGNIN_PAGE_LIST'));
        $controllers = array_values($activatedPages);
        $position = (int) Configuration::get('PHAUTOSIGNIN_POSITION');
        $cancelOnTapOutside = (Configuration::get('PHAUTOSIGNIN_CANCELONTAPOUTSIDE')) ? 'true' : 'false';

        // Enable the auto signin on the selected pages
        if (in_array(Tools::getValue("controller"), $controllers)
            || in_array('allpages', $controllers)
        ) {
            $isGoogleAutoSignInAvailable = true;
        }

        $clientId = Configuration::get('PHAUTOSIGNIN_CLIENTID');
        $clientSecret = Configuration::get('PHAUTOSIGNIN_CLIENTSECRET');

        if (!Tools::isEmpty($clientId) && !Tools::isEmpty($clientSecret)) {
            $clientId = Configuration::get('PHAUTOSIGNIN_CLIENTID');
        } else {
            $clientId = 0;
        }

        $this->context->smarty->assign(
            array(
                'phSocialLoginUrl' => $this->context->link->getModuleLink($this->name, 'googleautosignin'),
                'isGoogleAutoSignInAvailable' => $isGoogleAutoSignInAvailable,
                'phCustomerIsLogged' => (int)Context::getContext()->cookie->isLogged(),
//                'phCustomerIsLogged' => (int)$this->context->customer->isLogged(),
                'clientId' => $clientId,
                'position' => $position,
                'cancelOnTapOutside' => $cancelOnTapOutside,
                'phAutoSigninSilently' => (Configuration::get('PHAUTOSIGNIN_AUTOSIGNINSILENTLY')) ? 'true' : 'false'
            )
        );
        return $this->fetch('module:phautosignin/views/templates/hook/phautosignin.tpl');
    }
}
