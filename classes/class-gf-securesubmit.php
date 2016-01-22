<?php

GFForms::include_payment_addon_framework();

class GFSecureSubmit extends GFPaymentAddOn
{
    protected $_version = GF_SECURESUBMIT_VERSION;

    protected $_min_gravityforms_version = '1.9.1.1';
    protected $_slug = 'gravityforms-securesubmit';
    protected $_path = 'gravityforms-securesubmit/gravityforms-securesubmit.php';
    protected $_full_path = __FILE__;
    protected $_title = 'Gravity Forms SecureSubmit Add-On';
    protected $_short_title = 'SecureSubmit';
    protected $_requires_credit_card = true;
    protected $_supports_callbacks = true;
    protected $_enable_rg_autoupgrade = true;

    // Permissions
    protected $_capabilities_settings_page = 'gravityforms_securesubmit';
    protected $_capabilities_form_settings = 'gravityforms_securesubmit';
    protected $_capabilities_uninstall = 'gravityforms_securesubmit_uninstall';

    //Members plugin integration
    protected $_capabilities = array('gravityforms_securesubmit', 'gravityforms_securesubmit_uninstall');

    private static $_instance = null;
    public $transaction_response = null;

    public static function get_instance()
    {
        if (self::$_instance == null) {
            self::$_instance = new GFSecureSubmit();
        }

        return self::$_instance;
    }

    public function init()
    {
        parent::init();
        add_action('gform_post_payment_completed', array($this, 'updateAuthorizationEntry'), 10, 2);
        add_filter('gform_replace_merge_tags', array($this, 'replaceMergeTags'), 10, 7);
        add_action('gform_admin_pre_render', array($this, 'addClientSideMergeTags'));
    }

    public function init_ajax()
    {
        parent::init_ajax();
        add_action('wp_ajax_gf_validate_secret_api_key', array($this, 'ajaxValidateSecretApiKey'));
    }

    public function ajaxValidateSecretApiKey()
    {
        $this->includeSecureSubmitSDK();
        $config = new HpsServicesConfig();
        $config->secretApiKey = rgpost('key');
        $config->developerId = '002914';
        $config->versionNumber = '1916';

        $service = new HpsCreditService($config);

        $is_valid = true;

        try {
            $service->get('1');
        } catch (HpsAuthenticationException $e) {
            $is_valid = false;
        } catch (HpsException $e) {
            // Transaction was authenticated, but failed for another reason
        }

        $response = $is_valid ? 'valid' : 'invalid';
        die($response);
    }

    public function plugin_settings_fields()
    {
        return array(
            array(
                'title'  => __('SecureSubmit API', 'gravityforms-securesubmit'),
                'fields' => $this->sdkSettingsFields()
            ),
        );
    }

    public function feed_list_message()
    {
        $message = parent::feed_list_message();
        if ($message !== false) {
            return $message;
        }

        return false;
    }

    public function sdkSettingsFields()
    {
        return array(
            array(
                'name'     => 'public_api_key',
                'label'    => __('Public Key', 'gravityforms-securesubmit'),
                'type'     => 'text',
                'class'    => 'medium',
                'onchange' => "SecureSubmitAdmin.validateKey('public_api_key', this.value);",
            ),
            array(
                'name'     => 'secret_api_key',
                'label'    => __('Secret Key', 'gravityforms-securesubmit'),
                'type'     => 'text',
                'class'    => 'medium',
                'onchange' => "SecureSubmitAdmin.validateKey('secret_api_key', this.value);",
            ),
            array(
                'name'          => 'authorize_or_charge',
                'label'         => __('Payment Action', 'gravityforms-securesubmit'),
                'type'          => 'select',
                'default_value' => 'capture',
                'tooltip'       => __('Choose whether you wish to capture funds immediately or authorize payment only.', 'gravityforms-securesubmit'),
                'choices'       => array(
                    array(
                        'label'    => __('Capture', 'gravityforms-securesubmit'),
                        'value'    => 'capture',
                        'selected' => true,
                    ),
                    array(
                        'label' => __('Authorize', 'gravityforms-securesubmit'),
                        'value' => 'authorize',
                    ),
                ),
            ),
            array(
                'name'          => 'allow_payment_action_override',
                'label'         => __('Allow Payment Action Override', 'gravityforms-securesubmit'),
                'type'          => 'radio',
                'default_value' => 'no',
                'tooltip'       => __('Allows a SecureSubmit Feed to override the default payment action (authorize / capture).', 'gravityforms-securesubmit'),
                'choices'       => array(
                    array(
                        'label'    => __('No', 'gravityforms-securesubmit'),
                        'value'    => 'no',
                        'selected' => true,
                    ),
                    array(
                        'label' => __('Yes', 'gravityforms-securesubmit'),
                        'value' => 'yes',
                    ),
                ),
                'horizontal'    => true,
            ),
            array(
                'name'          => 'allow_level_ii',
                'label'         => __('Allow Level II Processing', 'gravityforms-securesubmit'),
                'type'          => 'radio',
                'default_value' => 'no',
                'tooltip'       => __('If you need Level II Processing, enable this field.' , 'gravityforms-securesubmit'),
                'choices'       => array(
                    array(
                        'label'    => __('No', 'gravityforms-securesubmit'),
                        'value'    => 'no',
                        'selected' => true,
                    ),
                    array(
                        'label' => __('Yes', 'gravityforms-securesubmit'),
                        'value' => 'yes',
                    ),
                ),
                'horizontal'    => true,
            ),
            array(
                'name'          => 'allow_api_keys_override',
                'label'         => __('Allow API Keys Override', 'gravityforms-securesubmit'),
                'type'          => 'radio',
                'default_value' => 'no',
                'tooltip'       => __('Allows a SecureSubmit Feed to override the default set of API keys.', 'gravityforms-securesubmit'),
                'choices'       => array(
                    array(
                        'label'    => __('No', 'gravityforms-securesubmit'),
                        'value'    => 'no',
                        'selected' => true,
                    ),
                    array(
                        'label' => __('Yes', 'gravityforms-securesubmit'),
                        'value' => 'yes',
                    ),
                ),
                'horizontal'    => true,
            ),
            array(
                'name'          => 'send_email',
                'label'         => __('Send Email', 'gravityforms-securesubmit'),
                'type'          => 'radio',
                'default_value' => 'no',
                'tooltip'       => __('Sends email with transaction details independent of GF notification system.', 'gravityforms-securesubmit'),
                'choices'       => array(
                    array(
                        'label'    => __('No', 'gravityforms-securesubmit'),
                        'value'    => 'no',
                        'selected' => true,
                    ),
                    array(
                        'label' => __('Yes', 'gravityforms-securesubmit'),
                        'value' => 'yes',
                    ),
                ),
                'horizontal'    => true,
                'onchange' => "SecureSubmitAdmin.toggleSendEmailFields(this.value);",
            ),
            array(
                'name'     => 'send_email_recipient_address',
                'label'    => __('Email Recipient', 'gravityforms-securesubmit'),
                'type'     => 'text',
                'class'    => 'medium',
            ),
            array(
                'label' => 'hidden',
                'name'  => 'public_api_key_is_valid',
                'type'  => 'hidden',
            ),
            array(
                'label' => 'hidden',
                'name'  => 'secret_api_key_is_valid',
                'type'  => 'hidden',
            ),
        );
    }

    public function feed_settings_fields()
    {
        $default_settings = parent::feed_settings_fields();

        if ($this->getAllowPaymentActionOverride() == 'yes') {
            $authorize_or_charge_field = array(
                'name'          => 'authorize_or_charge',
                'label'         => __('Payment Action', 'gravityforms-securesubmit'),
                'type'          => 'select',
                'default_value' => 'capture',
                'tooltip'       => __('Choose whether you wish to capture funds immediately or authorize payment only.', 'gravityforms-securesubmit'),
                'choices'       => array(
                    array(
                        'label'    => __('Capture', 'gravityforms-securesubmit'),
                        'value'    => 'capture',
                    ),
                    array(
                        'label' => __('Authorize', 'gravityforms-securesubmit'),
                        'value' => 'authorize',
                    ),
                ),
            );
            if ($this->getAuthorizeOrCharge() == 'capture') {
                $authorize_or_charge_field['choices'][0]['selected'] = true;
            } else {
                $authorize_or_charge_field['choices'][1]['selected'] = true;
            }
            $default_settings = $this->add_field_after('paymentAmount', $authorize_or_charge_field, $default_settings);
        }
        if ($this->getAllowAPIKeysOverride() == 'yes') {
            $public_api_key_field = array(
                'name'     => 'public_api_key',
                'label'    => __('Public Key', 'gravityforms-securesubmit'),
                'type'     => 'text',
                'class'    => 'medium',
                'onchange' => "SecureSubmitAdmin.validateKey('public_api_key', this.value);",
            );
            $secret_api_key_field = array(
                'name'     => 'secret_api_key',
                'label'    => __('Secret Key', 'gravityforms-securesubmit'),
                'type'     => 'text',
                'class'    => 'medium',
                'onchange' => "SecureSubmitAdmin.validateKey('secret_api_key', this.value);",
            );
            $default_settings = $this->add_field_after('paymentAmount', $public_api_key_field, $default_settings);
            $default_settings = $this->add_field_after('paymentAmount', $secret_api_key_field, $default_settings);
        }

        if ($this->getAllowLevelII() == 'yes') {
            $tax_type_field = array(
                'name'      => 'mappedFields',
                'label'     => esc_html__( 'Level II Mapping', 'gravityforms-securesubmit' ),
                'type'      => 'field_map',
                'field_map' => $this->get_level_ii_fields(),
                'tooltip'   => '<h6>' . esc_html__( 'Map Fields', 'gravityforms-securesubmit' ) . '</h6>' . esc_html__( 'This is only required if you plan to do Level II Processing.', 'gravityforms-securesubmit' ),
            );

            $default_settings = $this->add_field_after('paymentAmount', $tax_type_field, $default_settings);
        }

        return $default_settings;
    }

    protected function get_level_ii_fields() {
        $fields = array(
            array("name" => "customerpo", "label" => __("Customer PO", "gravityforms"), "required" => false),
            array("name" => "taxtype", "label" => __("Tax Type", "gravityforms"), "required" => false),
            array("name" => "taxamount", "label" => __("Tax Amount", "gravityforms"), "required" => false),
        );
        return $fields;
    }


    public function scripts()
    {
        $scripts = array(
            array(
                'handle'  => 'securesubmit.js',
                'src'       => 'https://api.heartlandportico.com/SecureSubmit.v1/token/2.1/securesubmit.js',
                'version' => $this->_version,
                'deps'    => array(),
                'enqueue' => array(
                    array(
                        'admin_page' => array('plugin_settings'),
                        'tab'        => array($this->_slug, $this->get_short_title())
                    ),
                )
            ),
            array(
                'handle'    => 'gforms_securesubmit_frontend',
                'src'       => $this->get_base_url() . '/../assets/js/securesubmit.js',
                'version'   => $this->_version,
                'deps'      => array('jquery', 'securesubmit.js'),
                'in_footer' => false,
                'enqueue'   => array(
                    array($this, 'hasFeedCallback'),
                )
            ),
            array(
                'handle'    => 'gform_json',
                'src'       => GFCommon::get_base_url() . '/js/jquery.json-1.3.js',
                'version'   => $this->_version,
                'deps'      => array('jquery'),
                'in_footer' => false,
                'enqueue'   => array(
                    array($this, 'hasFeedCallback'),
                )
            ),
            array(
                'handle'    => 'gforms_securesubmit_admin',
                'src'       => $this->get_base_url() . '/../assets/js/securesubmit-admin.js',
                'version'   => $this->_version,
                'deps'      => array('jquery'),
                'in_footer' => false,
                'enqueue'   => array(
                    array('admin_page' => array('plugin_settings'), 'tab' => array($this->_slug, $this->get_short_title())),
                ),
                'strings'   => array(
                    'spinner'          => GFCommon::get_base_url() . '/images/spinner.gif',
                    'validation_error' => __('Error validating this key. Please try again later.', 'gravityforms-securesubmit'),

                )
            ),
        );

        return array_merge(parent::scripts(), $scripts);
    }

    public function init_frontend()
    {
        add_filter('gform_register_init_scripts', array($this, 'registerInitScripts'), 10, 3);
        add_filter('gform_field_content', array($this, 'addSecureSubmitInputs'), 10, 5);

        parent::init_frontend();
    }

    public function registerInitScripts($form, $field_values, $is_ajax)
    {
        if (!$this->has_feed($form['id'])) {
            return;
        }

        $feeds = GFAPI::get_feeds(null, $form['id']);
        $feed = $feeds[0];

        $cc_field = $this->get_credit_card_field($form);

        $args = array(
            'apiKey'     => $this->getPublicApiKey($feed),
            'formId'     => $form['id'],
            'ccFieldId'  => $cc_field['id'],
            'ccPage'     => rgar($cc_field, 'pageNumber'),
            'isAjax'     => $is_ajax,
        );

        $script = 'new SecureSubmit(' . json_encode($args) . ');';
        GFFormDisplay::add_init_script($form['id'], 'securesubmit', GFFormDisplay::ON_PAGE_RENDER, $script);
    }

    public function addSecureSubmitInputs($content, $field, $value, $lead_id, $form_id)
    {
        if (! $this->has_feed($form_id) || GFFormsModel::get_input_type($field) != 'creditcard') {
            return $content;
        }

        if ($this->getSecureSubmitJsResponse()) {
            $content .= '<input type=\'hidden\' name=\'securesubmit_response\' id=\'gf_securesubmit_response\' value=\'' . rgpost('securesubmit_response') . '\' />';
        }

        return $content;
    }

    public function maybe_validate($validationResult)
    {
        if (!$this->has_feed($validationResult['form']['id'], true)) {
            return $validationResult;
        }

        foreach ($validationResult['form']['fields'] as $field) {
            $currentPage        = GFFormDisplay::get_source_page($validationResult['form']['id']);
            $fieldOnCurrentPage = $currentPage > 0 && $field['pageNumber'] == $currentPage;

            if (GFFormsModel::get_input_type($field) != 'creditcard' || !$fieldOnCurrentPage) {
                continue;
            }

            if ($this->getSecureSubmitJsError() && $this->hasPayment($validationResult)) {
                $field['failed_validation']  = true;
                $field['validation_message'] = $this->getSecureSubmitJsError();
            } else {
                $field['failed_validation'] = false;
            }

            break;
        }

        $validationResult['is_valid'] = true;
        return parent::maybe_validate($validationResult);
    }

    public function validation($validation_result)
    {
        if (!$this->has_feed($validation_result['form']['id'], true)) {
            return $validation_result;
        }

        foreach ($validation_result['form']['fields'] as $field) {
            $current_page         = GFFormDisplay::get_source_page($validation_result['form']['id']);
            $field_on_curent_page = $current_page > 0 && $field['pageNumber'] == $current_page;

            if (GFFormsModel::get_input_type($field) != 'creditcard' || ! $field_on_curent_page) {
                continue;
            }

            if ($this->getSecureSubmitJsError() && $this->hasPayment($validation_result)) {
                $field['failed_validation']  = true;
                $field['validation_message'] = $this->getSecureSubmitJsError();
            } else {
                // override validation in case user has marked field as required allowing securesubmit to handle cc validation
                $field['failed_validation'] = false;
            }

            // only one cc field per form, break once we've found it
            break;
        }

        // revalidate the validation result
        $validation_result['is_valid'] = true;

        foreach ($validation_result['form']['fields'] as $field) {
            if ($field['failed_validation']) {
                $validation_result['is_valid'] = false;
                break;
            }
        }

        return parent::validation($validation_result);
    }

    public function authorize($feed, $submission_data, $form, $entry)
    {
        $this->populateCreditCardLastFour($form);
        $this->includeSecureSubmitSDK();
        if ($this->getSecureSubmitJsError()) {
            return $this->authorization_error($this->getSecureSubmitJsError());
        }

        $isAuth = $this->getAuthorizeOrCharge($feed) == 'authorize';
        $config = new HpsServicesConfig();
        $config->secretApiKey = $this->getSecretApiKey($feed);
        $config->developerId = '002914';
        $config->versionNumber = '1916';

        $service = new HpsCreditService($config);

        $cardHolder = $this->buildCardHolder($feed, $submission_data, $entry);

        try {
            $response = $this->getSecureSubmitJsResponse();
            $token = new HpsTokenData();
            $token->tokenValue = ($response != null ? $response->token_value : '');

            $transaction = null;
            if ($isAuth) {
                if ($this->getAllowLevelII()) {
                    $transaction = $service->authorize($submission_data['payment_amount'], GFCommon::get_currency(), $token, $cardHolder, false, null, null, false, true);
                } else {
                    $transaction = $service->authorize($submission_data['payment_amount'], GFCommon::get_currency(), $token, $cardHolder);
                }
            } else {
                if ($this->getAllowLevelII()) {
                    $transaction = $service->charge($submission_data['payment_amount'], GFCommon::get_currency(), $token, $cardHolder, false, null, null, false, true, null);
                } else {
                    $transaction = $service->charge($submission_data['payment_amount'], GFCommon::get_currency(), $token, $cardHolder);
                }
            }
            self::get_instance()->transaction_response = $transaction;

            if ($this->getSendEmail() == 'yes') {
                $this->sendEmail($form, $entry, $transaction, $cardHolder);
            }

            $type = $isAuth ? 'Authorization' : 'Payment';
            $amount_formatted = GFCommon::to_money($submission_data['payment_amount'], GFCommon::get_currency());
            $note = sprintf(__('%s has been completed. Amount: %s. Transaction Id: %s.', 'gravityforms-securesubmit'), $type, $amount_formatted, $transaction->transactionId);

            if (
                $this->getAllowLevelII()
                && (
                    $transaction->cpcIndicator == 'B' || 
                    $transaction->cpcIndicator == 'R' || 
                    $transaction->cpcIndicator == 'S')
                ) {

                $cpcData = new HpsCPCData();
                $cpcData->CardHolderPONbr = $this->getLevelIICustomerPO($feed);

                if ($this->getLevelIITaxType($feed) == "SALES_TAX") {
                    $cpcData->TaxType = HpsTaxType::SALES_TAX;
                } else if ($this->getLevelIITaxType($feed) == "NOTUSED") {
                    $cpcData->TaxType = HpsTaxType::NOTUSED;
                } else if ($this->getLevelIITaxType($feed) == "TAXEXEMPT") {
                    $cpcData->TaxType = HpsTaxType::TAXEXEMPT;
                }

                $cpcData->TaxAmt = $this->getLevelIICustomerTaxAmount($feed);

                if (!empty($cpcData->CardHolderPONbr) && !empty($cpcData->TaxType) && !empty($cpcData->TaxAmt)) {
                    $cpcResponse = $service->cpcEdit($transaction->transactionId, $cpcData);
                    $note .= sprintf(__(' CPC Response Code: %s', 'gravityforms-securesubmit'), $cpcResponse->responseCode);
                }
            }


            if ($isAuth) {
                $note .= sprintf(__(' Authorization Code: %s', 'gravityforms-securesubmit'), $transaction->authorizationCode);
            }

            $auth = array(
                'is_authorized' => true,
                'captured_payment' => array(
                    'is_success'                  => true,
                    'transaction_id'              => $transaction->transactionId,
                    'amount'                      => $submission_data['payment_amount'],
                    'payment_method'              => $response->card_type,
                    'securesubmit_payment_action' => $this->getAuthorizeOrCharge($feed),
                    'note'                        => $note,
                ),
            );
        } catch (HpsException $e) {
            $auth = $this->authorization_error($e->getMessage());
        }

        return $auth;
    }

    // Helper functions

    public function updateAuthorizationEntry($entry, $result = array())
    {
        if (isset($result['securesubmit_payment_action'])
            && $result['securesubmit_payment_action'] == 'authorize'
            && isset($result['is_success'])
            && $result['is_success']) {
            $entry['payment_status'] = __('Authorized', 'gravityforms-securesubmit');
            GFAPI::update_entry($entry);
        }

        return $entry;
    }

    protected function sendEmail($form, $entry, $transaction, $cardHolder = null)
    {
        $to = $this->getSendEmailRecipientAddress();
        $subject = 'New Submission: ' . $form['title'];
        $message = 'Form: ' . $form['title'] . ' (' . $form['id'] . ")\r\n"
                 . 'Entry ID: ' . $entry['id'] . "\r\n"
                 . "Transaction Details:\r\n"
                 . print_r($transaction, true);

        if ($cardHolder != null) {
            $message .= "Card Holder Details:\r\n"
                      . print_r($cardHolder, true);
        }

        wp_mail($to, $subject, $message);
    }

    protected function buildCardHolder($feed, $submission_data, $entry)
    {
        $firstName = '';
        $lastName = '';

        try {
            $name = explode(' ', $submission_data['card_name']);
            $firstName = $name[0];
            unset($name[0]);
            $lastName = implode(' ', $name);
        } catch (Exception $ex) {
            $firstName = $submission_data['card_name'];
        }

        $address = new HpsAddress();
        $address->address  = $entry[$feed['meta']['billingInformation_address']]
                           . $entry[$feed['meta']['billingInformation_address2']];
        $address->city     = $entry[$feed['meta']['billingInformation_city']];
        $address->state    = $entry[$feed['meta']['billingInformation_state']];
        $address->zip      = $entry[$feed['meta']['billingInformation_zip']];
        $address->country  = $entry[$feed['meta']['billingInformation_country']];

        $cardHolder = new HpsCardHolder();
        $cardHolder->firstName = $firstName;
        $cardHolder->lastName = $lastName;
        $cardHolder->address = $address;

        return $cardHolder;
    }

    public function hasPayment($validation_result)
    {
        $form = $validation_result['form'];
        $entry = GFFormsModel::create_lead($form);
        $feed  = $this->get_payment_feed($entry, $form);

        if (!$feed) {
            return false;
        }

        $submission_data = $this->get_submission_data($feed, $form, $entry);

        //Do not process payment if payment amount is 0 or less
        return floatval($submission_data['payment_amount']) > 0;
    }

    public function populateCreditCardLastFour($form)
    {
        $cc_field = $this->get_credit_card_field($form);
        $response = $this->getSecureSubmitJsResponse();
        $_POST['input_' . $cc_field['id'] . '_1'] = 'XXXXXXXXXXXX' . ($response != null ? $response->last_four : '');
        $_POST['input_' . $cc_field['id'] . '_4'] = ($response != null ? $response->card_type : '');
    }

    public function includeSecureSubmitSDK()
    {
        require_once plugin_dir_path(__FILE__) . 'includes/Hps.php';
        do_action('gform_securesubmit_post_include_api');
    }

    public function getSecretApiKey($feed = null)
    {
        return $this->getApiKey('secret', $feed);
    }

    public function getLevelIICustomerPO($feed = null) 
    {
        if ($feed != null && isset($feed['meta']["mappedFields_customerpo"])) {
            return (string)$_POST['input_' . $feed["meta"]["mappedFields_customerpo"]];
        }
    }

    public function getLevelIITaxType($feed = null) 
    {
        if ($feed != null && isset($feed['meta']["mappedFields_taxtype"])) {
            return (string)$_POST['input_' . $feed["meta"]["mappedFields_taxtype"]];
        }
    }

    public function getLevelIICustomerTaxAmount($feed = null) 
    {
        if ($feed != null && isset($feed['meta']["mappedFields_taxamount"])) {
            return (string)$_POST['input_' . $feed["meta"]["mappedFields_taxamount"]];
        }
    }

    public function getAllowLevelII()
    {
        $settings = $this->get_plugin_settings();
        return (string)$this->get_setting('allow_level_ii', 'no', $settings);
    }

    public function getPublicApiKey($feed = null)
    {
        return $this->getApiKey('public', $feed);
    }

    public function getApiKey($type = 'secret', $feed = null)
    {
        // user needs admin privileges for this
        $api_key = $this->getQueryStringApiKey($type);
        if ($api_key && current_user_can('update_core')) {
            return $api_key;
        }

        if ($feed != null && isset($feed['meta']["{$type}_api_key"])) {
            return (string)$feed['meta']["{$type}_api_key"];
        }
        $settings = $this->get_plugin_settings();
        return (string)$this->get_setting("{$type}_api_key", '', $settings);
    }

    public function getQueryStringApiKey($type = 'secret')
    {
        return rgget($type);
    }

    public function getAuthorizeOrCharge($feed = null)
    {
        if ($feed != null && isset($feed['meta']['authorize_or_charge'])) {
            return (string)$feed['meta']['authorize_or_charge'];
        }
        $settings = $this->get_plugin_settings();
        return (string)$this->get_setting('authorize_or_charge', 'charge', $settings);
    }

    public function getAllowPaymentActionOverride()
    {
        $settings = $this->get_plugin_settings();
        return (string)$this->get_setting('allow_payment_action_override', 'no', $settings);
    }

    public function getAllowAPIKeysOverride()
    {
        $settings = $this->get_plugin_settings();
        return (string)$this->get_setting('allow_api_keys_override', 'no', $settings);
    }

    public function getSendEmail()
    {
        $settings = $this->get_plugin_settings();
        return (string)$this->get_setting('send_email', 'no', $settings);
    }

    public function getSendEmailRecipientAddress()
    {
        $settings = $this->get_plugin_settings();
        return (string)$this->get_setting('send_email_recipient_address', '', $settings);
    }

    public function hasFeedCallback($form)
    {
        return $form && $this->has_feed($form['id']);
    }

    public function getSecureSubmitJsResponse()
    {
        return json_decode(rgpost('securesubmit_response'));
    }

    public function getSecureSubmitJsError()
    {
        $response = $this->getSecureSubmitJsResponse();

        if (isset($response->error)) {
            return $response->error->message;
        }

        return false;
    }

    public function isFieldOnValidPage($field, $parent)
    {
        $form = $this->get_current_form();

        $mapped_field_id   = $this->get_setting($field['name']);
        $mapped_field      = GFFormsModel::get_field($form, $mapped_field_id);
        $mapped_field_page = rgar($mapped_field, 'pageNumber');

        $cc_field = $this->get_credit_card_field($form);
        $cc_page  = rgar($cc_field, 'pageNumber');

        if ($mapped_field_page > $cc_page) {
            $this->set_field_error($field, __('The selected field needs to be on the same page as the Credit Card field or a previous page.', 'gravityforms-securesubmit'));
        }
    }

    public function replaceMergeTags($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format)
    {
        $mergeTags = array(
            'transactionId'     => '{securesubmit_transaction_id}',
            'authorizationCode' => '{securesubmit_authorization_code}'
        );

        $gFormsKey = array(
            'transactionId' => 'transaction_id',
        );

        foreach ($mergeTags as $key => $mergeTag) {
            // added for GF 1.9.x
            if (strpos($text, $mergeTag) === false || empty($entry) || empty($form)) {
                return $text;
            }

            $value = '';
            if (class_exists('GFSecureSubmit') && isset(GFSecureSubmit::get_instance()->transaction_response)) {
                $value = GFSecureSubmit::get_instance()->transaction_response->$key;
            }

            if (isset($gFormsKey[$key]) && empty($value)) {
                $value = rgar($entry, $gFormsKey[$key]);
            }

            $text = str_replace($mergeTag, $value, $text);
        }
        return $text;
    }

    public function addClientSideMergeTags($form)
    {
        include plugin_dir_path(__FILE__) . '../templates/client-side-merge-tags.php';
        return $form;
    }
}
