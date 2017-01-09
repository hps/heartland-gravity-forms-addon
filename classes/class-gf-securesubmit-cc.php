<?php

GFForms::include_payment_addon_framework();
include_once 'class-gf-field-hpscreditcard.php';
include_once 'class-gf-field-hpsach.php';

/**
 * Handles Heartlands Payments with Gravity Forms
 * Class GFSecureSubmit
 */
class GFSecureSubmit extends GFPaymentAddOn {
    /**
     * @var string
     */
    protected $_version = GF_SECURESUBMIT_VERSION;

    /**
     * @var string
     */
    protected $_min_gravityforms_version = '1.9.1.1';
    /**
     * @var string
     */
    protected $_slug = 'gravityforms-securesubmit';
    /**
     * @var string
     */
    protected $_path = 'gravityforms-securesubmit/gravityforms-securesubmit.php';
    /**
     * @var string
     */
    protected $_full_path = __FILE__;
    /**
     * @var string
     */
    protected $_title = 'Gravity Forms SecureSubmit Add-On';
    /**
     * @var string
     */
    protected $_short_title = 'SecureSubmit';
    /**
     * @var bool
     */
    protected $_requires_credit_card = true;
    /**
     * @var bool
     */
    protected $_supports_callbacks = true;
    /**
     * @var bool
     */
    protected $_enable_rg_autoupgrade = true;

    // Permissions
    /**
     * @var string
     */
    protected $_capabilities_settings_page = 'gravityforms_securesubmit';
    /**
     * @var string
     */
    protected $_capabilities_form_settings = 'gravityforms_securesubmit';
    /**
     * @var string
     */
    protected $_capabilities_uninstall = 'gravityforms_securesubmit_uninstall';

    //Members plugin integration
    /**
     * @var array
     */
    protected $_capabilities = array('gravityforms_securesubmit', 'gravityforms_securesubmit_uninstall');

    /**
     * @var null
     */
    private static $_instance = null;
    /**
     * @var null
     */
    public $transaction_response = null;

    /**
     * @return GFSecureSubmit|null
     */
    public static function get_instance() {
        if (self::$_instance == null) {
            self::$_instance = new GFSecureSubmit();
        }

        return self::$_instance;
    }

    /** Add our Secure CC button to the pricing fields.
     * I found this tutorial very helpful
     * http://wpsmith.net/2011/how-to-create-a-custom-form-field-in-gravity-forms-with-a-terms-of-service-form-field-example/
     * @param $field_groups
     * @return mixed
     */
    public function hps_add_cc_field($field_groups ) {
        foreach( $field_groups as &$group ){
            if( $group["name"] == "pricing_fields" ){
                $group["fields"][] = array(
                    'class'     => 'button',
                    // this has to match
                    // \GF_Field_HPSCreditCard::$type
                    'data-type' => 'hpscreditcard',
                    // the first param here will be the button text
                    // leave the second one as gravityforms
                    'value'     => __('Secure CC', "gravityforms"),
                );
                break;
            }
        }
        return $field_groups;
    }
    public function hps_add_ach_field($field_groups ) {
        foreach( $field_groups as &$group ){
            if( $group["name"] == "pricing_fields" ){
                $group["fields"][] = array(
                    'class'     => 'button',
                    // this has to match
                    // \GF_Field_HPSACH::$type
                    'data-type' => 'hpsACH',
                    // the first param here will be the button text
                    // leave the second one as gravityforms
                    'value'     => __('Secure ACH', "gravityforms"),
                );
                break;
            }
        }
        return $field_groups;
    }

    /**
     *
     */
    public function init() {
        parent::init();
        add_action('gform_post_payment_completed', array($this, 'updateAuthorizationEntry'), 10, 2);
        add_filter('gform_replace_merge_tags', array($this, 'replaceMergeTags'), 10, 7);
        add_action('gform_admin_pre_render', array($this, 'addClientSideMergeTags'));

        /* Sets WP to call \GFSecureSubmit::hps_add_cc_field and build our button
        src: wordpress/wp-includes/plugin.php
         * \GFSecureSubmit::hps_add_cc_field
         * */
        add_filter('gform_add_field_buttons', array($this, 'hps_add_cc_field') );
        add_filter('gform_add_field_buttons', array($this, 'hps_add_ach_field') );

    }

    /**
     *
     */
    public function init_ajax() {
        parent::init_ajax();
        add_action('wp_ajax_gf_validate_secret_api_key', array($this, 'ajaxValidateSecretApiKey'));
    }

    /**
     *
     */
    public function ajaxValidateSecretApiKey() {
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

    /**
     * @return array
     */
    public function plugin_settings_fields() {
        return array(
            array(
                'title' => __('SecureSubmit API', $this->_slug),
                'fields' => $this->sdkSettingsFields()
            ),
            array(
                'title' => __('Velocity Limits', $this->_slug),
                'fields' => $this->vmcSettingsFields()
            ),
        );
    }

    /**
     * @return bool|false|string
     */
    public function feed_list_message() {
        $message = parent::feed_list_message();
        if ($message !== false) {
            return $message;
        }

        return false;
    }

    /**
     * @return array
     */
    public function vmcSettingsFields() {
        return array(
            array(
                'name' => 'enable_fraud',
                'label' => __('Velocity Settings', $this->_slug),
                'type' => 'select',
                'default_value' => 'Enabled',
                'tooltip' => __('Choose whether you wish to limit failed attempts', $this->_slug),
                'choices' => array(
                    array(
                        'label' => __('Enabled', $this->_slug),
                        'value' => 'true',
                        'selected' => true,
                    ),
                    array(
                        'label' => __('Disabled', $this->_slug),
                        'value' => 'false',
                    ),
                ),
            ),
            array(
                'name' => 'fraud_message',
                'label' => __('Displayed Message', $this->_slug),
                'type' => 'text',
                'tooltip' => __('Text entered here will be displayed to your consumer if they exceed the failures within the timeframe.', $this->_slug),
                'default_value' => 'Please contact us to complete the transaction.',
                'class' => 'medium',
            ),
            array(
                'name' => 'fraud_velocity_attempts',
                'label' => __('How many failed attempts before blocking?', $this->_slug),
                'type' => 'text',
                'default_value' => '3',
                'class' => 'small',
            ),
            array(
                'name' => 'fraud_velocity_timeout',
                'label' => __('How long (in minutes) should we keep a tally of recent failures?', $this->_slug),
                'type' => 'text',
                'default_value' => '10',
                'class' => 'small',
            ),
        );
    }

    /**
     * @return array
     */
    public function sdkSettingsFields() {
        return array(
            array(
                'name' => 'public_api_key',
                'label' => __('Public Key', $this->_slug),
                'type' => 'text',
                'class' => 'medium',
                'onchange' => "SecureSubmitAdmin.validateKey('public_api_key', this.value);",
            ),
            array(
                'name' => 'secret_api_key',
                'label' => __('Secret Key', $this->_slug),
                'type' => 'text',
                'class' => 'medium',
                'onchange' => "SecureSubmitAdmin.validateKey('secret_api_key', this.value);",
            ),
            array(
                'name' => 'authorize_or_charge',
                'label' => __('Payment Action', $this->_slug),
                'type' => 'select',
                'default_value' => 'capture',
                'tooltip' => __('Choose whether you wish to capture funds immediately or authorize payment only.', $this->_slug),
                'choices' => array(
                    array(
                        'label' => __('Capture', $this->_slug),
                        'value' => 'capture',
                        'selected' => true,
                    ),
                    array(
                        'label' => __('Authorize', $this->_slug),
                        'value' => 'authorize',
                    ),
                ),
            ),
            array(
                'name' => 'allow_payment_action_override',
                'label' => __('Allow Payment Action Override', $this->_slug),
                'type' => 'radio',
                'default_value' => 'no',
                'tooltip' => __('Allows a SecureSubmit Feed to override the default payment action (authorize / capture).', $this->_slug),
                'choices' => array(
                    array(
                        'label' => __('No', $this->_slug),
                        'value' => 'no',
                        'selected' => true,
                    ),
                    array(
                        'label' => __('Yes', $this->_slug),
                        'value' => 'yes',
                    ),
                ),
                'horizontal' => true,
            ),
            array(
                'name' => 'allow_level_ii',
                'label' => __('Allow Level II Processing', $this->_slug),
                'type' => 'radio',
                'default_value' => 'no',
                'tooltip' => __('If you need Level II Processing, enable this field.', $this->_slug),
                'choices' => array(
                    array(
                        'label' => __('No', $this->_slug),
                        'value' => 'no',
                        'selected' => true,
                    ),
                    array(
                        'label' => __('Yes', $this->_slug),
                        'value' => 'yes',
                    ),
                ),
                'horizontal' => true,
            ),
            array(
                'name' => 'allow_api_keys_override',
                'label' => __('Allow API Keys Override', $this->_slug),
                'type' => 'radio',
                'default_value' => 'no',
                'tooltip' => __('Allows a SecureSubmit Feed to override the default set of API keys.', $this->_slug),
                'choices' => array(
                    array(
                        'label' => __('No', $this->_slug),
                        'value' => 'no',
                        'selected' => true,
                    ),
                    array(
                        'label' => __('Yes', $this->_slug),
                        'value' => 'yes',
                    ),
                ),
                'horizontal' => true,
            ),
            array(
                'name' => 'send_email',
                'label' => __('Send Email', $this->_slug),
                'type' => 'radio',
                'default_value' => 'no',
                'tooltip' => __('Sends email with transaction details independent of GF notification system.', $this->_slug),
                'choices' => array(
                    array(
                        'label' => __('No', $this->_slug),
                        'value' => 'no',
                        'selected' => true,
                    ),
                    array(
                        'label' => __('Yes', $this->_slug),
                        'value' => 'yes',
                    ),
                ),
                'horizontal' => true,
                'onchange' => "SecureSubmitAdmin.toggleSendEmailFields(this.value);",
            ),
            array(
                'name' => 'send_email_recipient_address',
                'label' => __('Email Recipient', $this->_slug),
                'type' => 'text',
                'class' => 'medium',
            ),
            array(
                'label' => 'hidden',
                'name' => 'public_api_key_is_valid',
                'type' => 'hidden',
            ),
            array(
                'label' => 'hidden',
                'name' => 'secret_api_key_is_valid',
                'type' => 'hidden',
            ),
        );
    }

    /**
     * @return array
     */
    public function feed_settings_fields() {
        $default_settings = parent::feed_settings_fields();

        if ($this->getAllowPaymentActionOverride() == 'yes') {
            $authorize_or_charge_field = array(
                'name' => 'authorize_or_charge',
                'label' => __('Payment Action', $this->_slug),
                'type' => 'select',
                'default_value' => 'capture',
                'tooltip' => __('Choose whether you wish to capture funds immediately or authorize payment only.', $this->_slug),
                'choices' => array(
                    array(
                        'label' => __('Capture', $this->_slug),
                        'value' => 'capture',
                    ),
                    array(
                        'label' => __('Authorize', $this->_slug),
                        'value' => 'authorize',
                    ),
                ),
            );
            if ($this->getAuthorizeOrCharge() == 'capture') {
                $authorize_or_charge_field['choices'][0]['selected'] = true;
            }
            else {
                $authorize_or_charge_field['choices'][1]['selected'] = true;
            }
            $default_settings = $this->add_field_after('paymentAmount', $authorize_or_charge_field, $default_settings);
        }
        if ($this->getAllowAPIKeysOverride() == 'yes') {
            $public_api_key_field = array(
                'name' => 'public_api_key',
                'label' => __('Public Key', $this->_slug),
                'type' => 'text',
                'class' => 'medium',
                'onchange' => "SecureSubmitAdmin.validateKey('public_api_key', this.value);",
            );
            $secret_api_key_field = array(
                'name' => 'secret_api_key',
                'label' => __('Secret Key', $this->_slug),
                'type' => 'text',
                'class' => 'medium',
                'onchange' => "SecureSubmitAdmin.validateKey('secret_api_key', this.value);",
            );
            $default_settings = $this->add_field_after('paymentAmount', $public_api_key_field, $default_settings);
            $default_settings = $this->add_field_after('paymentAmount', $secret_api_key_field, $default_settings);
        }

        if ($this->getAllowLevelII() == 'yes') {
            $tax_type_field = array(
                'name' => 'mappedFields',
                'label' => esc_html__('Level II Mapping', $this->_slug),
                'type' => 'field_map',
                'field_map' => $this->get_level_ii_fields(),
                'tooltip' => '<h6>' . esc_html__('Map Fields', $this->_slug) . '</h6>' . esc_html__('This is only required if you plan to do Level II Processing.', $this->_slug),
            );

            $default_settings = $this->add_field_after('paymentAmount', $tax_type_field, $default_settings);
        }

        return $default_settings;
    }

    /**
     * @return array
     */
    protected function get_level_ii_fields() {
        $fields = array(
            array("name" => "customerpo", "label" => __("Customer PO", "gravityforms"), "required" => false),
            array("name" => "taxtype", "label" => __("Tax Type", "gravityforms"), "required" => false),
            array("name" => "taxamount", "label" => __("Tax Amount", "gravityforms"), "required" => false),
        );
        return $fields;
    }

    /**
     * @return array
     */
    public function scripts() {
        $scripts = array(

            array(
                'handle' => 'gforms_securesubmit_frontend',
                'src' => $this->get_base_url() . '/../assets/js/securesubmit-ach.js',
                'version' => $this->_version,
                'deps' => array('jquery', 'securesubmit.js'),
                'in_footer' => false,
                'enqueue' => array(
                    array($this, 'hasFeedCallback'),
                )
            ),
            array(
                'handle' => 'gform_json',
                'src' => GFCommon::get_base_url() . '/js/jquery.json-1.3.js',
                'version' => $this->_version,
                'deps' => array('jquery'),
                'in_footer' => false,
                'enqueue' => array(
                    array($this, 'hasFeedCallback'),
                )
            ),
            array(
                'handle' => 'gforms_securesubmit_admin',
                'src' => $this->get_base_url() . '/../assets/js/securesubmit-admin.js',
                'version' => $this->_version,
                'deps' => array('jquery'),
                'in_footer' => false,
                'enqueue' => array(
                    array('admin_page' => array('plugin_settings'), 'tab' => array($this->_slug, $this->get_short_title())),
                ),
                'strings' => array(
                    'spinner' => GFCommon::get_base_url() . '/images/spinner.gif',
                    'validation_error' => __('Error validating this key. Please try again later.', $this->_slug),

                )
            ),
        );

        return array_merge(parent::scripts(), $scripts);


    }

    public function styles() {
        $styles = array(
            array(
                'handle'  => 'securesubmit_css',
                'src'     => $this->get_base_url() . "/../css/style.css",
                'version' => $this->_version,
                'enqueue' => array(
                    array($this, 'hasFeedCallback'),
                ),
            ),
        );
        return array_merge(parent::styles(), $styles);
    }

    public function add_theme_scripts() {


      wp_enqueue_style( 'style', $this->get_base_url() . '/../css/style.css', array(), '1.1', 'all');



        if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
          wp_enqueue_script( 'comment-reply' );
        }
    }


    /**
     *
     */



    public function init_frontend() {
        add_filter('gform_register_init_scripts', array($this, 'registerInitScripts'), 10, 3);
        add_filter('gform_field_content', array($this, 'addSecureSubmitInputs'), 10, 5);
        parent::init_frontend();

    }

    /**
     * @param $form
     * @param $field_values
     * @param $is_ajax
     */
    public function registerInitScripts($form, $field_values, $is_ajax) {
        if (!$this->has_feed($form['id'])) {
            return;
        }

        $feeds = GFAPI::get_feeds(null, $form['id']);
        $feed = $feeds[0];

        $cc_field = $this->get_credit_card_field($form);

        $args = array(
            'apiKey' => $this->getPublicApiKey($feed),
            'formId' => $form['id'],
            'ccFieldId' => $cc_field['id'],
            'ccPage' => rgar($cc_field, 'pageNumber'),
            'isAjax' => $is_ajax,
        );

        $script = 'new SecureSubmit(' . json_encode($args) . ');';
        GFFormDisplay::add_init_script($form['id'], 'securesubmit', GFFormDisplay::ON_PAGE_RENDER, $script);
    }

    /**
     * @param $content
     * @param $field
     * @param $value
     * @param $lead_id
     * @param $form_id
     * @return string
     */
    public function addSecureSubmitInputs($content, $field, $value, $lead_id, $form_id) {
        if (!$this->has_feed($form_id) || GFFormsModel::get_input_type($field) != 'creditcard') {
            return $content;
        }

        if ($this->getSecureSubmitJsResponse()) {
            $content .= '<input type=\'hidden\' name=\'securesubmit_response\' id=\'gf_securesubmit_response\' value=\'' . rgpost('securesubmit_response') . '\' />';
        }

        return $content;
    }

    /**
     * @param array $validationResult
     * @return array
     */
    public function maybe_validate($validationResult) {
        if (!$this->has_feed($validationResult['form']['id'], true)) {
            return $validationResult;
        }

        foreach ($validationResult['form']['fields'] as $field) {
            $currentPage = GFFormDisplay::get_source_page($validationResult['form']['id']);
            $fieldOnCurrentPage = $currentPage > 0 && $field['pageNumber'] == $currentPage;

            if (GFFormsModel::get_input_type($field) != 'creditcard' || !$fieldOnCurrentPage) {
                continue;
            }

            if ($this->getSecureSubmitJsError() && $this->hasPayment($validationResult)) {
                $field['failed_validation'] = true;
                $field['validation_message'] = $this->getSecureSubmitJsError();
            }
            else {
                $field['failed_validation'] = false;
            }

            break;
        }

        $validationResult['is_valid'] = true;
        return parent::maybe_validate($validationResult);
    }

    /**
     * @param $validation_result
     * @return mixed
     */
    public function validation($validation_result) {
        if (!$this->has_feed($validation_result['form']['id'], true)) {
            return $validation_result;
        }

        foreach ($validation_result['form']['fields'] as $field) {
            $current_page = GFFormDisplay::get_source_page($validation_result['form']['id']);
            $field_on_curent_page = $current_page > 0 && $field['pageNumber'] == $current_page;

            if (GFFormsModel::get_input_type($field) != 'creditcard' || !$field_on_curent_page) {
                continue;
            }

            if ($this->getSecureSubmitJsError() && $this->hasPayment($validation_result)) {
                $field['failed_validation'] = true;
                $field['validation_message'] = $this->getSecureSubmitJsError();
            }
            else {
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

    /** Authorise or capture a card. Overrides \GFPaymentAddOn::authorize
     * @param $feed
     * @param $submission_data
     * @param $form
     * @param $entry
     * @return array
     * @throws HpsException
     */
    public function authorize($feed, $submission_data, $form, $entry) {
        $this->populateCreditCardLastFour($form);
        $this->includeSecureSubmitSDK();

        /** Currently saved plugin settings */
        $settings                   = $this->get_plugin_settings();

        /** This is the message show to the consumer if the rule is flagged */
        $fraud_message              = (string)  $this->get_setting("fraud_message", 'Please contact us to complete the transaction.', $settings);

        /** Maximum number of failures allowed before rule is triggered */
        $fraud_velocity_attempts    = (int)     $this->get_setting("fraud_velocity_attempts", '3', $settings);

        /** Maximum amount of time in minutes to track failures. If this amount of time elapse between failures then the counter($HeartlandHPS_FailCount) will reset */
        $fraud_velocity_timeout     = (int)     $this->get_setting("fraud_velocity_timeout", '10', $settings);

        /** Variable name with hash of IP address to identify uniqe transient values         */
        $HPS_VarName                = (string)  "HeartlandHPS_Velocity_" . md5($this->getRemoteIP());

        /** Running count of failed transactions from the current IP*/
        $HeartlandHPS_FailCount     = (int)     get_transient($HPS_VarName);

        /** Defaults to true or checks actual settings for this plugin from $settings. If true the following settings are applied:

         $fraud_message
         *
         $fraud_velocity_attempts
         *
         $fraud_velocity_timeout
         *
         */
        $enable_fraud               = (bool)    ($this->get_setting("enable_fraud", 'true', $settings) === 'true');

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

            /**
             * if fraud_velocity_attempts is less than the $HeartlandHPS_FailCount then we know
             * far too many failures have been tried
             *//*
            if ($enable_fraud && $HeartlandHPS_FailCount >= $fraud_velocity_attempts) {
                sleep(5);
                $issuerResponse = (string)get_transient($HPS_VarName . 'IssuerResponse');
                throw new HpsException(wp_sprintf('%s %s', $fraud_message, $issuerResponse));
            }*/
            $response = $this->getSecureSubmitJsResponse();
            $token = new HpsTokenData();
            $token->tokenValue = ($response != null ? $response->token_value : '');

            $transaction = null;
            if ($isAuth) {
                if ($this->getAllowLevelII()) {
                    $transaction = $service->authorize($submission_data['payment_amount'], GFCommon::get_currency(), $token, $cardHolder, false, null, null, false, true);
                }
                else {
                    $transaction = $service->authorize($submission_data['payment_amount'], GFCommon::get_currency(), $token, $cardHolder);
                }
            }
            else {
                if ($this->getAllowLevelII()) {
                    $transaction = $service->charge($submission_data['payment_amount'], GFCommon::get_currency(), $token, $cardHolder, false, null, null, false, true, null);
                }
                else {
                    $transaction = $service->charge($submission_data['payment_amount'], GFCommon::get_currency(), $token, $cardHolder);
                }
            }
            self::get_instance()->transaction_response = $transaction;

            if ($this->getSendEmail() == 'yes') {
                $this->sendEmail($form, $entry, $transaction, $cardHolder);
            }

            $type = $isAuth ? 'Authorization' : 'Payment';
            $amount_formatted = GFCommon::to_money($submission_data['payment_amount'], GFCommon::get_currency());
            $note = sprintf(__('%s has been completed. Amount: %s. Transaction Id: %s.', $this->_slug), $type, $amount_formatted, $transaction->transactionId);

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
                }
                else if ($this->getLevelIITaxType($feed) == "NOTUSED") {
                    $cpcData->TaxType = HpsTaxType::NOTUSED;
                }
                else if ($this->getLevelIITaxType($feed) == "TAXEXEMPT") {
                    $cpcData->TaxType = HpsTaxType::TAXEXEMPT;
                }

                $cpcData->TaxAmt = $this->getLevelIICustomerTaxAmount($feed);

                if (!empty($cpcData->CardHolderPONbr) && !empty($cpcData->TaxType) && !empty($cpcData->TaxAmt)) {
                    $cpcResponse = $service->cpcEdit($transaction->transactionId, $cpcData);
                    $note .= sprintf(__(' CPC Response Code: %s', $this->_slug), $cpcResponse->responseCode);
                }
            }


            if ($isAuth) {
                $note .= sprintf(__(' Authorization Code: %s', $this->_slug), $transaction->authorizationCode);
            }

            $auth = array(
                'is_authorized' => true,
                'captured_payment' => array(
                    'is_success' => true,
                    'transaction_id' => $transaction->transactionId,
                    'amount' => $submission_data['payment_amount'],
                    'payment_method' => $response->card_type,
                    'securesubmit_payment_action' => $this->getAuthorizeOrCharge($feed),
                    'note' => $note,
                ),
            );
        } catch (HpsException $e) {

            // if advanced fraud is enabled, increment the error count
            if ($enable_fraud) {
                if(empty($HeartlandHPS_FailCount)){$HeartlandHPS_FailCount = 0;}
                set_transient($HPS_VarName, $HeartlandHPS_FailCount + 1, MINUTE_IN_SECONDS * $fraud_velocity_timeout);
                if ($HeartlandHPS_FailCount < $fraud_velocity_attempts) {
                    set_transient($HPS_VarName . 'IssuerResponse', $e->getMessage(), MINUTE_IN_SECONDS * $fraud_velocity_timeout);
                }
            }
            $auth = $this->authorization_error( $HeartlandHPS_FailCount . $e->getMessage());
        }

        return $auth;
    }

    // Helper functions

    /**
     * @param $entry
     * @param array $result
     * @return mixed
     */
    public function updateAuthorizationEntry($entry, $result = array()) {
        if (isset($result['securesubmit_payment_action'])
            && $result['securesubmit_payment_action'] == 'authorize'
            && isset($result['is_success'])
            && $result['is_success']
        ) {
            $entry['payment_status'] = __('Authorized', $this->_slug);
            GFAPI::update_entry($entry);
        }

        return $entry;
    }

    /**
     * @param $form
     * @param $entry
     * @param $transaction
     * @param null $cardHolder
     */
    protected function sendEmail($form, $entry, $transaction, $cardHolder = null) {
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

    /**
     * @param $feed
     * @param $submission_data
     * @param $entry
     * @return HpsCardHolder
     */
    protected function buildCardHolder($feed, $submission_data, $entry) {
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
        $address->address = $entry[$feed['meta']['billingInformation_address']]
            . $entry[$feed['meta']['billingInformation_address2']];
        $address->city = $entry[$feed['meta']['billingInformation_city']];
        $address->state = $entry[$feed['meta']['billingInformation_state']];
        $address->zip = $entry[$feed['meta']['billingInformation_zip']];
        $address->country = $entry[$feed['meta']['billingInformation_country']];

        $cardHolder = new HpsCardHolder();
        $cardHolder->firstName = $firstName;
        $cardHolder->lastName = $lastName;
        $cardHolder->address = $address;

        return $cardHolder;
    }

    /**
     * @param $validation_result
     * @return bool
     */
    public function hasPayment($validation_result) {
        $form = $validation_result['form'];
        $entry = GFFormsModel::create_lead($form);
        $feed = $this->get_payment_feed($entry, $form);

        if (!$feed) {
            return false;
        }

        $submission_data = $this->get_submission_data($feed, $form, $entry);

        //Do not process payment if payment amount is 0 or less
        return floatval($submission_data['payment_amount']) > 0;
    }

    /**
     * @param $form
     */
    public function populateCreditCardLastFour($form) {
        $cc_field = $this->get_credit_card_field($form);
        $response = $this->getSecureSubmitJsResponse();
        $_POST['input_' . $cc_field['id'] . '_1'] = 'XXXXXXXXXXXX' . ($response != null ? $response->last_four : '');
        $_POST['input_' . $cc_field['id'] . '_4'] = ($response != null ? $response->card_type : '');
    }

    /**
     *
     */
    public function includeSecureSubmitSDK() {
        require_once plugin_dir_path(__FILE__) . 'includes/Hps.php';
        do_action('gform_securesubmit_post_include_api');
    }

    /**
     * @param null $feed
     * @return string
     */
    public function getSecretApiKey($feed = null) {
        return $this->getApiKey('secret', $feed);
    }

    /**
     * @param null $feed
     * @return string
     */
    public function getLevelIICustomerPO($feed = null) {
        if ($feed != null && isset($feed['meta']["mappedFields_customerpo"])) {
            return (string)$_POST['input_' . $feed["meta"]["mappedFields_customerpo"]];
        }
    }

    /**
     * @param null $feed
     * @return string
     */
    public function getLevelIITaxType($feed = null) {
        if ($feed != null && isset($feed['meta']["mappedFields_taxtype"])) {
            return (string)$_POST['input_' . $feed["meta"]["mappedFields_taxtype"]];
        }
    }

    /**
     * @param null $feed
     * @return string
     */
    public function getLevelIICustomerTaxAmount($feed = null) {
        if ($feed != null && isset($feed['meta']["mappedFields_taxamount"])) {
            return (string)$_POST['input_' . $feed["meta"]["mappedFields_taxamount"]];
        }
    }

    /**
     * @return string
     */
    public function getAllowLevelII() {
        $settings = $this->get_plugin_settings();
        return (string)$this->get_setting('allow_level_ii', 'no', $settings);
    }

    /**
     * @param null $feed
     * @return string
     */
    public function getPublicApiKey($feed = null) {
        return $this->getApiKey('public', $feed);
    }

    /**
     * @param string $type
     * @param null $feed
     * @return string
     */
    public function getApiKey($type = 'secret', $feed = null) {
        // user needs admin privileges for this
        $api_key = $this->getQueryStringApiKey($type);
        if ($api_key && current_user_can('update_core')) {
            return $api_key;
        }

        if ($feed != null && isset($feed['meta']["{$type}_api_key"])) {
            return (string)$feed['meta']["{$type}_api_key"];
        }
        $settings = $this->get_plugin_settings();
        return (string)trim($this->get_setting("{$type}_api_key", '', $settings));
    }

    /**
     * @param string $type
     * @return string
     */
    public function getQueryStringApiKey($type = 'secret') {
        return rgget($type);
    }

    /**
     * @param null $feed
     * @return string
     */
    public function getAuthorizeOrCharge($feed = null) {
        if ($feed != null && isset($feed['meta']['authorize_or_charge'])) {
            return (string)$feed['meta']['authorize_or_charge'];
        }
        $settings = $this->get_plugin_settings();
        return (string)$this->get_setting('authorize_or_charge', 'charge', $settings);
    }

    /**
     * @return string
     */
    public function getAllowPaymentActionOverride() {
        $settings = $this->get_plugin_settings();
        return (string)$this->get_setting('allow_payment_action_override', 'no', $settings);
    }

    /**
     * @return string
     */
    public function getAllowAPIKeysOverride() {
        $settings = $this->get_plugin_settings();
        return (string)$this->get_setting('allow_api_keys_override', 'no', $settings);
    }

    /**
     * @return string
     */
    public function getSendEmail() {
        $settings = $this->get_plugin_settings();
        return (string)$this->get_setting('send_email', 'no', $settings);
    }

    /**
     * @return string
     */
    public function getSendEmailRecipientAddress() {
        $settings = $this->get_plugin_settings();
        return (string)$this->get_setting('send_email_recipient_address', '', $settings);
    }

    /**
     * @param $form
     * @return bool
     */
    public function hasFeedCallback($form) {
        return $form && $this->has_feed($form['id']);
    }

    /**
     * @return array|mixed|object
     */
    public function getSecureSubmitJsResponse() {
        return json_decode(rgpost('securesubmit_response'));
    }

    /**
     * @return bool
     */
    public function getSecureSubmitJsError() {
        $response = $this->getSecureSubmitJsResponse();

        if (isset($response->error)) {
            return $response->error->message;
        }

        return false;
    }

    /**
     * @param $field
     * @param $parent
     */
    public function isFieldOnValidPage($field, $parent) {
        $form = $this->get_current_form();

        $mapped_field_id = $this->get_setting($field['name']);
        $mapped_field = GFFormsModel::get_field($form, $mapped_field_id);
        $mapped_field_page = rgar($mapped_field, 'pageNumber');

        $cc_field = $this->get_credit_card_field($form);
        $cc_page = rgar($cc_field, 'pageNumber');

        if ($mapped_field_page > $cc_page) {
            $this->set_field_error($field, __('The selected field needs to be on the same page as the Credit Card field or a previous page.', $this->_slug));
        }
    }

    /**
     * @param $text
     * @param $form
     * @param $entry
     * @param $url_encode
     * @param $esc_html
     * @param $nl2br
     * @param $format
     * @return mixed
     */
    public function replaceMergeTags($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format) {
        $mergeTags = array(
            'transactionId' => '{securesubmit_transaction_id}',
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

    /**
     * @param $form
     * @return mixed
     */
    public function addClientSideMergeTags($form) {
        include plugin_dir_path(__FILE__) . '../templates/client-side-merge-tags.php';
        return $form;
    }

    /**Attempts to get real ip even if there is a proxy chain
     * @return string
     */
    private function getRemoteIP() {
        $remoteIP = $_SERVER['REMOTE_ADDR'];
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
            $remoteIPArray = array_values(
                array_filter(
                    explode(
                        ',',
                        $_SERVER['HTTP_X_FORWARDED_FOR']
                    )
                )
            );
            $remoteIP = end($remoteIPArray);
        }
        return $remoteIP;
    }

}
