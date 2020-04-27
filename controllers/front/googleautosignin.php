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

require_once dirname(__FILE__).'/../../libraries/vendor/autoload.php';

class PhautosigninGoogleautosigninModuleFrontController extends ModuleFrontController
{

    public $php_self;

    public function displayAjax()
    {
        $errors = array();
        $success = array();
        $relaodPage = 0;
        $googleUserData = array();
        $array_result = array();

        $action = Tools::getValue('action');

        if ($action == 'ph-auto-signin') {
            $credential = Tools::getValue('credential');

            $clientId = Configuration::get('PHAUTOSIGNIN_CLIENTID');
            $clientSecret = Configuration::get('PHAUTOSIGNIN_CLIENTSECRET');
            //$activeModule = (bool)Configuration::get('PHAUTOSIGNIN_ACTIVATE');

            if ($clientId !== null and $clientSecret !== null) {
                $client = new Google_Client([
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                ]); // Specify the CLIENT_ID of the app that accesses the backend

                $payload = $client->verifyIdToken($credential);

                if ($payload) {
                    $useid = $payload['sub'];
                    $firstName = $payload['given_name'];
                    $lastName = $payload['family_name'];
                    $email = $payload['email'];
                    $emailVerified = $payload['email_verified'];

                    $googleUserData[] = $payload;

                    // Create a new account silently
                    //Check if customer is already logged
                    if ($this->context->customer->isLogged()) {
                        /**
                         * TOODO: if customer is logged we return null
                         */
                        $success[] = $this->l('Customer is already logged.', 'phautosignin');
                    } else {
                        if (!($email = trim($email)) || !Validate::isEmail($email)) {
                            $errors[] = $this->l('Invalid email address', 'phautosignin');
                        }
                        /*if (!($firstname = trim(Tools::getValue('first_name'))) || !Validate::isName($firstname)) {
                            $errors[] = $this->l('Invalid first name', 'phautosignin');
                        }
                        if (!($lastname = trim(Tools::getValue('last_name'))) || !Validate::isName($lastname)) {
                            $errors[] = $this->l('Invalid last name', 'phautosignin');
                        }*/

                        if (!count($errors)) {
                            // IF customer exist but is not logged
                            // If customer exist we sign in the customer
                            if (Customer::customerExists($email, true, true)) {
                                Hook::exec('actionAuthenticationBefore');

                                $customer = new Customer();
                                $authentication = $customer->getByEmail($email, null, true);

                                if (isset($authentication->active) && !$authentication->active) {
                                    $errors[] = $this->l(
                                        'Your account isn\'t available at this time, please contact us',
                                        'phautosignin'
                                    );
                                } elseif (!$authentication || !$customer->id || $customer->is_guest) {
                                    $errors[] = $this->l('Authentication failed.', 'phautosignin');
                                } else {
                                    // update cookie to login the customer
                                    $this->context->updateCustomer($customer);

                                    Hook::exec('actionAuthentication', array('customer' => $this->context->customer));

                                    // Login information have changed, so we check if the cart rules still apply
                                    CartRule::autoRemoveFromCart($this->context);
                                    CartRule::autoAddToCart($this->context);
                                    $success[] = $this->l('You have successfully logged in', 'phautosignin');
                                    $relaodPage = 1;
                                }
                            } else {
                                // ELSE the customer does not exist so we create a new account
                                $hookResult = array_reduce(
                                    Hook::exec('actionSubmitAccountBefore', array(), null, true),
                                    function ($carry, $item) {
                                        return $carry && $item;
                                    },
                                    true
                                );

                                //Create a new customer account
                                $customer = new Customer();
                                $customer->firstname = $firstName;
                                $customer->lastname = $lastName;
                                $customer->email = $email;
                                $password = Tools::passwdGen();
                                $customer->passwd = $this->get('hashing')->hash($password, _COOKIE_KEY_);

                                if ($hookResult && $customer->save()) {
                                    $this->context->updateCustomer($customer);
                                    $this->context->cart->update();
                                    //send mail new account
                                    if (!$customer->is_guest && Configuration::get('PS_CUSTOMER_CREATION_EMAIL')) {
                                        Mail::Send(
                                            $this->context->language->id,
                                            'account_social',
                                            $this->l('Welcome!', 'phautosignin'),
                                            array(
                                                '{firstname}' => $customer->firstname,
                                                '{lastname}' => $customer->lastname,
                                                '{email}' => $customer->email,
                                                '{password}' => $password
                                            ),
                                            $customer->email,
                                            $customer->firstname . ' ' . $customer->lastname,
                                            null,
                                            null,
                                            null,
                                            null,
                                            _PS_MODULE_DIR_ . $this->module->name . '/mails/'
                                        );
                                    }

                                    Hook::exec('actionCustomerAccountAdd', array('newCustomer' => $customer));
                                    $success[] = $this->l(
                                        'You have successfully created a new account',
                                        'phautosignin'
                                    );
                                    $relaodPage = 1;

                                    $googleUserData = [
                                        'email' => $email,
                                        'name' => $firstName
                                    ];
                                } else {
                                    $errors[] = $this->l(
                                        'An error occurred while creating the new account.',
                                        'phautosignin'
                                    );
                                }
                            }
                        }
                    }
                } else {
                    // Invalid ID token
                    $errors[] = $this->l('Invalid ID token', 'phautosignin');
                }
            }
        }

        if (!(bool)Configuration::get('PHAUTOSIGNIN_RELOADPAGEAFTERLOGIN')) {
            $relaodPage =  0;
        }

        $array_result['success'] = $success;
        $array_result['errors'] = $errors;
        $array_result['googleUserData'] =  $googleUserData;
        $array_result['reloadPage'] =  $relaodPage;
        die(Tools::jsonEncode($array_result));
    }

    /**
    * @see FrontController::initContent()
    */
    public function initContent()
    {
       // parent::initContent();
    }
}
