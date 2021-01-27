<?php
GFForms::include_payment_addon_framework();

use GlobalPayments\Api\Entities\EncryptionData;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\PaymentMethods\CreditTrackData;
use GlobalPayments\Api\Services\CreditService;
use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Customer;
use GlobalPayments\Api\Entities\TransactionSummary;
use GlobalPayments\Api\Entities\Enums\AccountType;
use GlobalPayments\Api\Entities\Enums\CheckType;
use GlobalPayments\Api\Entities\Enums\EntryMethod;
use GlobalPayments\Api\Entities\Enums\SecCode;
use GlobalPayments\Api\PaymentMethods\ECheck;
use GlobalPayments\Api\Entities\EcommerceInfo;
use GlobalPayments\Api\ServiceConfigs\AcceptorConfig;
use GlobalPayments\Api\ServiceConfigs\Gateways\TransitConfig;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;


if (!class_exists('GF_Field_HPSach')) {
    include_once 'class-gf-field-hpsach.php';
}
if (!class_exists('GF_Field_HPSCreditCard')) {
    include_once 'class-gf-field-hpssecurecc.php';
}

/**
 * Handles Heartlands Payments with Gravity Forms
 * Class GFSecureSubmit
 */
class GFSecureSubmit extends GFPaymentAddOn
{
    private $processPaymentsFor = array( 'creditcard','hpscreditcard','hpsACH' );
    private $ccFields = array( 'creditcard','hpscreditcard' );

    /**
     * @var bool
     */
    private $isCert = false;

    /**
     * @var bool
     */
    private $isCC = false;

    /**
     * @var bool|\GF_Field_HPSach
     */
    private $isACH = false;

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
    protected $_requires_credit_card = false;

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

    /**
     * @var array
     */
    protected $_capabilities
        = array(
            'gravityforms_securesubmit',
            'gravityforms_securesubmit_uninstall'
        );

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
    public static function get_instance()
    {
        if (self::$_instance == null) {
            self::$_instance = new GFSecureSubmit();
        }

        return self::$_instance;
    }

    /** Add our Secure ACH button to the pricing fields.
     * I found this tutorial very helpful
     * http://wpsmith.net/2011/how-to-create-a-custom-form-field-in-gravity-forms-with-a-terms-of-service-form-field-example/
     *
     * @param $field_groups
     *
     * @return mixed
     */
    public function hps_add_ach_field($field_groups)
    {
        foreach ($field_groups as &$group) {
            if ($group["name"] == "pricing_fields") {
                $group["fields"][] = array(
                    'class' => 'button',
                    // this has to match
                    // \GF_Field_HPSACH::$type
                    'data-type' => 'hpsACH',
                    // the first param here will be the button text
                    // leave the second one as gravityforms
                    'value' => __('Secure ACH', "gravityforms"),
                    "onclick" => "StartAddField('hpsACH');",
                );
                break;
            }
        }

        return $field_groups;
    }

    /** Add our Secure CC button to the pricing fields.
     * I found this tutorial very helpful
     * http://wpsmith.net/2011/how-to-create-a-custom-form-field-in-gravity-forms-with-a-terms-of-service-form-field-example/
     *
     * @param $field_groups
     *
     * @return mixed
     */
    public function hps_add_cc_field($field_groups)
    {
        foreach ($field_groups as &$group) {
            if ($group["name"] == "pricing_fields") {
                $group["fields"][] = array(
                    'class' => 'button',
                    // this has to match
                    // \GF_Field_HPSACH::$type
                    'data-type' => 'hpscreditcard',
                    // the first param here will be the button text
                    // leave the second one as gravityforms
                    'value' => __('Secure CC', "gravityforms"),
                    "onclick" => "StartAddField('hpscreditcard');",
                );
                break;
            }
        }

        return $field_groups;
    }

    public function init()
    {
        parent::init();
        add_action('gform_post_payment_completed', array($this, 'updateAuthorizationEntry'), 10, 2);
        add_filter('gform_replace_merge_tags', array($this, 'replaceMergeTags'), 10, 7);
        add_action('gform_admin_pre_render', array($this, 'addClientSideMergeTags'));

        /*
        * * Sets WP to call \GFSecureSubmit::hps_add_ach_field and build our button
         src: wordpress/wp-includes/plugin.php
        * \GFSecureSubmit::hps_add_ach_field
        */
        add_filter('gform_add_field_buttons', array($this, 'hps_add_ach_field'));
        add_filter('gform_add_field_buttons', array($this, 'hps_add_cc_field'));
        add_action('gform_editor_js_set_default_values', array($this, 'set_defaults'));
    }

    public function set_defaults()
    {
        // this hook is fired in the middle of a JavaScript switch statement,
        // so we need to add a case for our new field types
        ?>
        case "hpsACH" :
            field.label = "Bank Transfer"; //setting the default field label
            break;
        case "hpscreditcard" :
            field.label = "Secure Credit Card"; //setting the default field label
            break;
        <?php
    }

    public function init_ajax()
    {
        parent::init_ajax();
        add_action('wp_ajax_gf_validate_secret_api_key', array($this, 'ajaxValidateSecretApiKey'));
    }

    public function ajaxValidateSecretApiKey()
    {
        $this->includeSecureSubmitSDK();
        $config = $this->getHpsServicesConfig(rgpost('key'));

        $service = new HpsCreditService($config);

        $is_valid = true;

        try {
            $service->get('1');
        } catch (HpsAuthenticationException $e) {
            $is_valid = false;
        } catch (HpsException $e) {
            // Transaction was authenticated, but failed for another reason
        }

        $response = $is_valid
            ? 'valid'
            : 'invalid';
        die($response);
    }

    /**
     * @return array
     */
    public function plugin_settings_fields()
    {
        return array(
            array(
                'title' => __('SecureSubmit API', $this->_slug),
                'fields' => $this->sdkSettingsFields(),
            ),
            array(
                'title' => __('TransIT API', $this->_slug),
                'fields' => $this->sdkTransITSettingsFields(),
            ),
            array(
                'title' => __('Velocity Limits', $this->_slug),
                'fields' => $this->vmcSettingsFields(),
            ),
        );
    }

    /**
     * @return bool|false|string
     */
    public function feed_list_message()
    {
        if ($this->_requires_credit_card && (!$this->has_hps_payment_fields())) {
            return $this->requires_credit_card_message();
        }

        // from GFFeedAddOn::feed_list_message
        if (!$this->can_create_feed()) {
            return $this->configure_addon_message();
        }

        return false;
    }

    /**
     * @return bool
     */
    private function has_hps_payment_fields()
    {
        $fields = GFAPI::get_fields_by_type($this->get_current_form(), $this->processPaymentsFor);
        return empty($fields) ? false : true;
    }

    /**
     * @param $form
     *
     * @return bool
     */
    private function has_ach_field($form)
    {
        return $this->get_ach_field($form) !== false;
    }

    /**
     * @param $form
     *
     * @return bool
     */
    private function has_credit_card_fields($form)
    {
        if (empty($this->isCC)) {
            $fields = GFAPI::get_fields_by_type($form, $this->ccFields);
            $this->isCC = empty($fields) ? false : true;
        }
        return $this->isCC;
    }

    /**
     * @return array
     */
    public function vmcSettingsFields()
    {
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
                'tooltip' => __(
                    'Text entered here will be displayed to your consumer if they exceed the failures within the timeframe.',
                    $this->_slug
                ),
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
                'label' => __(
                    'How long (in minutes) should we keep a tally of recent failures?',
                    $this->_slug
                ),
                'type' => 'text',
                'default_value' => '10',
                'class' => 'small',
            ),
        );
    }

    
    /**
     * @return array
     */
    public function sdkSettingsFields()
    {
        return array(
            array(
                'name' => 'payment_type',
                'label' => __('Payment Type', $this->_slug),
                'type' => 'radio',
                'default_value' => 'securesubmit',
                'tooltip' => __(
                    'Select Payment Type.',
                    $this->_slug
                    ),
                'choices' => array(
                    array(
                        'label' => __('Secure Submit', $this->_slug),
                        'value' => 'securesubmit',
                        'selected' => true,
                    ),
                    array(
                        'label' => __('Trans IT', $this->_slug),
                        'value' => 'transit',
                    ),
                ),
                'horizontal' => true,
            ),
            array(
                'name' => 'hps_sandbox_mode',
                'label' => __('Sandbox Mode', $this->_slug),
                'type' => 'radio',
                'default_value' => 'yes',
                'tooltip' => __(
                    'Is Sandbox Mode',
                    $this->_slug
                    ),
                'choices' => array(
                    array(
                        'label' => __('No', $this->_slug),
                        'value' => 'no'
                    ),
                    array(
                        'label' => __('Yes', $this->_slug),
                        'value' => 'yes',
                        'selected' => true,
                    ),
                ),
                'horizontal' => true,
            ),
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
                'tooltip' => __(
                    'Choose whether you wish to capture funds immediately or authorize payment only.',
                    $this->_slug
                    ),
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
                'tooltip' => __(
                    'Allows a SecureSubmit Feed to override the default payment action (authorize / capture).',
                    $this->_slug
                    ),
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
                'tooltip' => __(
                    'Allows a SecureSubmit Feed to override the default set of API keys.',
                    $this->_slug
                    ),
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
                'name' => 'enable_threedsecure',
                'label' => __('Enable Cardholder Authentication (3DSecure)', $this->_slug),
                'type' => 'radio',
                'default_value' => 'no',
                'tooltip' => __(
                    'This feature requires additional account setup. Please contact your Heartland representative to enable this feature.',
                    $this->_slug
                    ),
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
                'onchange' => "SecureSubmitAdmin.toggleEnableThreeDSecureFields(this.value);",
            ),
            array(
                'name' => 'enable_threedsecure_api_identifier',
                'label' => __('Cardholder Authentication API Identifier', $this->_slug),
                'type' => 'text',
                'class' => 'medium',
            ),
            array(
                'name' => 'enable_threedsecure_org_unit_id',
                'label' => __('Cardholder Authentication Org Unit ID', $this->_slug),
                'type' => 'text',
                'class' => 'medium',
            ),
            array(
                'name' => 'enable_threedsecure_api_key',
                'label' => __('Cardholder Authentication API Key', $this->_slug),
                'type' => 'text',
                'class' => 'medium',
            ),
            array(
                'name' => 'send_email',
                'label' => __('Send Email', $this->_slug),
                'type' => 'radio',
                'default_value' => 'no',
                'tooltip' => __(
                    'Sends email with transaction details independent of GF notification system.',
                    $this->_slug
                    ),
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
    public function sdkTransITSettingsFields()
    {
        return array(
            array(
                'name' => 'transit_sandbox_mode',
                'label' => __('Sandbox Mode', $this->_slug),
                'type' => 'radio',
                'default_value' => 'yes',
                'tooltip' => __(
                    'Is Sandbox Mode',
                    $this->_slug
                    ),
                'choices' => array(
                    array(
                        'label' => __('No', $this->_slug),
                        'value' => 'no'
                    ),
                    array(
                        'label' => __('Yes', $this->_slug),
                        'value' => 'yes',
                        'selected' => true,
                    ),
                ),
                'horizontal' => true,
            ),
            array(
                'name' => 'authorize_or_charge',
                'label' => __('Payment Action', $this->_slug),
                'type' => 'select',
                'default_value' => 'capture',
                'tooltip' => __(
                    'Choose whether you wish to capture funds immediately or authorize payment only.',
                    $this->_slug
                    ),
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
                'name' => 'merchant_id',
                'label' => __('Merchant Id', $this->_slug),
                'type' => 'text',
                'class' => 'medium'
            ),
            array(
                'name' => 'username',
                'label' => __('User Name', $this->_slug),
                'type' => 'text',
                'class' => 'medium'
            ),
            array(
                'name' => 'password',
                'label' => __('Password', $this->_slug),
                'type' => 'text',
                'class' => 'medium'
            ),
            array(
                'name' => 'device_id',
                'label' => __('Device Id', $this->_slug),
                'type' => 'text',
                'class' => 'medium'
            ),
            array(
                'name' => 'developer_id',
                'label' => __('Developer Id', $this->_slug),
                'type' => 'text',
                'class' => 'medium'
            ),
            array(
                'name' => 'transaction_key',
                'label' => __('Transaction Key', $this->_slug),
                'type' => 'text',
                'class' => 'medium'
            ),            
        );
    }
    
    
    /**
     * @return array
     */
    public function feed_settings_fields()
    {
        $default_settings = parent::feed_settings_fields();

        // removes 'Options' checkboxes
        $default_settings = $this->remove_field('options', $default_settings);

        // replace default 'Billing Cycle' to remove useless number select before real choices
        $billingCycle = array(
            'name'    => 'billingCycle',
            'label'   => esc_html__('Billing Cycle', 'gravityforms'),
            'type'     => 'select',
            'choices'  => array(
                array( 'value' => 'WEEKLY', 'label' => esc_html('Weekly', 'gravityforms'), ),
                array( 'value' => 'BIWEEKLY', 'label' => esc_html('Bi-Weekly', 'gravityforms'), ),
                array( 'value' => 'SEMIMONTHLY', 'label' => esc_html('Semi-Monthly', 'gravityforms'), ),
                array( 'value' => 'MONTHLY', 'label' => esc_html('Monthly', 'gravityforms'), ),
                array( 'value' => 'QUARTERLY', 'label' => esc_html('Quarterly', 'gravityforms'), ),
                array( 'value' => 'SEMIANNUALLY', 'label' => esc_html('Semi-Annually', 'gravityforms'), ),
                array( 'value' => 'ANNUALLY', 'label' => esc_html('Annually', 'gravityforms'), ),
            ),
            'tooltip' => '<h6>' . esc_html__('Billing Cycle', 'gravityforms') . '</h6>' . esc_html__('Select your billing cycle. This determines how often the recurring payment should occur.', 'gravityforms'),
        );
        $setupFee = array(
            'name' => 'setupFee',
            'label' => esc_html('Setup Fee', 'gravityforms'),
            'type' => 'setup_fee',
        );
        $trialPeriod = array(
            'name'    => 'trial',
            'label'   => esc_html__('Trial Period', 'gravityforms'),
            'type'    => 'trial',
            'hidden'  => $this->get_setting('setupFee_enabled'),
            'tooltip' => '<h6>' . esc_html__('Trial Period (days)', 'gravityforms') . '</h6>' . esc_html__('Enable a trial period. The user\'s recurring payment will not begin until after this trial period.', 'gravityforms'),
        );

        $default_settings = $this->replace_field('billingCycle', $billingCycle, $default_settings);
        $default_settings = $this->replace_field('setupFee', $setupFee, $default_settings);
        $default_settings = $this->replace_field('trial', $trialPeriod, $default_settings);

        if ($this->getAllowPaymentActionOverride() == 'yes') {
            $authorize_or_charge_field = array(
                'name' => 'authorize_or_charge',
                'label' => __('Payment Action', $this->_slug),
                'type' => 'select',
                'default_value' => 'capture',
                'tooltip' => __(
                    'Choose whether you wish to capture funds immediately or authorize payment only.',
                    $this->_slug
                ),
                'choices' => array(
                     array(
                        'label' => __('Capture', $this->_slug),
                        'value' => 'capture',
                        'selected' => $this->getAuthorizeOrCharge() == 'capture',
                    ),
                    array(
                        'label' => __('Authorize', $this->_slug),
                        'value' => 'authorize',
                        'selected' => $this->getAuthorizeOrCharge() == 'authorize',
                    ),
                ),
            );

            $default_settings = $this->add_field_after('paymentAmount', $authorize_or_charge_field, $default_settings);
            $default_settings = $this->add_field_after('recurringAmount', $authorize_or_charge_field, $default_settings);
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
            $default_settings = $this->add_field_after('recurringAmount', $public_api_key_field, $default_settings);
            $default_settings = $this->add_field_after('recurringAmount', $secret_api_key_field, $default_settings);
        }
        if ($this->getAllowLevelII() == 'yes') {
            $tax_type_field = array(
                'name' => 'mappedFields',
                'label' => esc_html__('Level II Mapping', $this->_slug),
                'type' => 'field_map',
                'field_map' => $this->get_level_ii_fields(),
                'tooltip' => '<h6>' . esc_html__('Map Fields', $this->_slug) . '</h6>'
                            . esc_html__('This is only required if you plan to do Level II Processing.', $this->_slug),
            );

            $default_settings = $this->add_field_after('paymentAmount', $tax_type_field, $default_settings);
        }

        return $default_settings;
    }

    /**
     * Override to modify HTML of 'Trial' option
     */
    public function settings_trial($field, $echo = true)
    {
        //--- Enabled field ---
        $enabled_field = array(
            'name'       => $field['name'] . '_checkbox',
            'type'       => 'checkbox',
            'horizontal' => true,
            'choices'    => array(
                array(
                    'label'    => esc_html__('Enabled', 'gravityforms'),
                    'name'     => $field['name'] . '_enabled',
                    'value'    => '1',
                    'onchange' => $this->set_trial_onchange($field),
                ),
            ),
        );

        $html = $this->settings_checkbox($enabled_field, false);

        //--- Select Product field ---
        $form            = $this->get_current_form();
        $payment_choices = array_merge($this->get_payment_choices($form), array(
            array(
                'label' => esc_html__('Enter an amount', 'gravityforms'),
                'value' => 'enter_amount',
            )
        ));

        $product_field = array(
            'name'     => $field['name'] . '_product',
            'type'     => 'text',
            'class'    => $this->get_setting("{$field['name']}_enabled") ? '' : 'hidden',
        );

        $html .= '&nbsp' . $this->settings_text($product_field, false);

        if ($echo) {
            echo $html;
        }

        return $html;
    }

    /**
     * @return array
     */
    protected function get_level_ii_fields()
    {
        $fields = array(
            array(
                "name" => "customerpo",
                "label" => __("Customer PO", "gravityforms"),
                "required" => false,
            ),
            array(
                "name" => "taxtype",
                "label" => __("Tax Type", "gravityforms"),
                "required" => false,
            ),
            array(
                "name" => "taxamount",
                "label" => __("Tax Amount", "gravityforms"),
                "required" => false,
            ),
        );

        return $fields;
    }

    /**
     * @return array
     */
    public function scripts()
    {
        $this->isCert = (
            false !== strpos(
                (string)trim(
                    $this->get_setting(
                        'public_api_key', '',
                        $this->get_plugin_settings()
                    )
                ), '_cert_')
        );
        $scripts = array(
            array(
                'handle' => 'securesubmit.js',
                'src' => 'https://api2.heartlandportico.com/securesubmit.v1/token/gp-1.6.0/globalpayments.js',
                'version' => $this->_version,
                'deps' => array(),
                'enqueue' => array(
                    array(
                        'admin_page' => array('plugin_settings'),
                        'tab' => array($this->_slug, $this->get_short_title()),
                    ),
                ),
            ),
            array(
                'handle' => 'songbird.js',
                'src' => ( $this->isCert ?
                    'https://includestest.ccdc02.com/cardinalcruise/v1/songbird.js'
                    : 'https://includes.ccdc02.com/cardinalcruise/v1/songbird.js'),
                'version' => $this->_version,
                'deps' => array(),
                'enqueue' => array(
                    array($this, 'hasFeedCallback'),
                ),
            ),
            array(
                'handle' => 'gforms_securesubmit_frontend',
                'src' => $this->get_base_url() . '/../assets/js/securesubmit.js',
                'version' => $this->_version,
                'deps' => array('jquery', 'securesubmit.js'),
                'in_footer' => false,
                'enqueue' => array(
                    array($this, 'hasFeedCallback'),
                ),
            ),
            array(
                'handle' => 'gform_json',
                'src' => GFCommon::get_base_url() . '/js/jquery.json-1.3.js',
                'version' => $this->_version,
                'deps' => array('jquery'),
                'in_footer' => false,
                'enqueue' => array(
                    array($this, 'hasFeedCallback'),
                ),
            ),
            array(
                'handle' => 'gforms_securesubmit_admin',
                'src' => $this->get_base_url() . '/../assets/js/securesubmit-admin.js',
                'version' => $this->_version,
                'deps' => array('jquery'),
                'in_footer' => false,
                'enqueue' => array(
                    array('admin_page' => array('plugin_settings', 'form_editor'), 'tab' => array($this->_slug, $this->get_short_title())),
                ),
                'strings' => array(
                    'spinner' => GFCommon::get_base_url() . '/images/spinner.gif',
                    'validation_error' => __('Error validating this key. Please try again later.', 'gravityforms-securesubmit'),
                ),
            ),
        );

        return array_merge(parent::scripts(), $scripts);
    }

    /**
     * @return array
     */
    public function styles()
    {
        $styles = array(
            array(
                'handle' => 'securesubmit_css',
                'src' => $this->get_base_url() . "/../css/style.css",
                'version' => $this->_version,
                'enqueue' => array(
                    array($this,'hasFeedCallback'),
                ),
            ),
        );

        return array_merge(parent::styles(), $styles);
    }

    public function add_theme_scripts()
    {
        wp_enqueue_style('style', $this->get_base_url() . '/../css/style.css', array(), '1.1', 'all');

        if (is_singular() && comments_open() && get_option('thread_comments')) {
            wp_enqueue_script('comment-reply');
        }
    }

    public function init_frontend()
    {
        add_filter('gform_register_init_scripts', array($this, 'registerInitScripts'), 10, 3);
        add_filter('gform_field_content', array($this, 'addSecureSubmitInputs'), 10, 5);
        parent::init_frontend();
    }

    /**
     * @param $form
     * @param $field_values
     * @param $is_ajax
     */
    public function registerInitScripts($form, $field_values, $is_ajax)
    {
        if (!$this->has_feed($form['id'])) {
            return;
        }

        if (!$this->has_credit_card_fields($form)) {
            return;
        }

        $feeds = GFAPI::get_feeds(null, $form['id']);
        $feed = $feeds[0];
        $pubKey = $this->getPublicApiKey($feed);
        $cc_field = $this->get_credit_card_field($form);

        if ($cc_field === false) {
            $cc_field = $this->get_hpscredit_card_field($form);
        }

        $use_3DSecure = ($this->getEnable3DSecure() === 'yes' ? true : false);
        
        $args = array(
            'apiKey' => $pubKey,
            'formId' => $form['id'],
            'ccFieldId' => $cc_field['id'],
            'ccPage' => rgar($cc_field, 'pageNumber'),
            'isAjax' => $is_ajax,
            'isSecure' => $cc_field['type'] === 'hpscreditcard',
            'isCCA' => $use_3DSecure,
            'isCert' => $this->isCert,
            'pageNo' => rgpost('gform_source_page_number_'.$form['id'].''),
            'baseUrl' => plugins_url('', dirname(__FILE__) . '../'),
            'gatewayConfig' => json_encode($this->setTransItJsScriptsValue($pubKey))            
        );
        
        if ($use_3DSecure) {
            $orderNumber = str_shuffle('abcdefghijklmnopqrstuvwxyz');
            $data = array(
                'jti' => str_shuffle('abcdefghijklmnopqrstuvwxyz'),
                'iat' => time(),
                'iss' => $this->getEnable3DSecureApiIdentifier(),
                'OrgUnitId' => $this->getEnable3DSecureOrgUnitId(),
                'Payload' => array(
                    'OrderDetails' => array(
                        'OrderNumber' => $orderNumber,
                        // Centinel requires amounts in pennies
                        'Amount' => (100 * 0),
                        'CurrencyCode' => '840',
                    ),
                ),
            );

            if (!class_exists('HeartlandJWT')) {
                include_once 'class-heartland-jwt.php';
            }
            $jwt = HeartlandJWT::encode($this->getEnable3DSecureApiKey(), $data);
            $verified = HeartlandJWT::verify($jwt, $this->getEnable3DSecureApiKey());

            $args['ccaData'] = array(
                'jwt' => $jwt,
                'orderNumber' => $orderNumber,
            );
        }

        $script = 'new window.SecureSubmit(' . json_encode($args) . ');';
        GFFormDisplay::add_init_script($form['id'], 'securesubmit', GFFormDisplay::ON_PAGE_RENDER, $script);
    }


    /**
     * @param $content
     * @param $field
     * @param $value
     * @param $lead_id
     * @param $form_id
     *
     * @return string
     */
    public function addSecureSubmitInputs($content, $field, $value, $lead_id, $form_id)
    {
        $type = GFFormsModel::get_input_type($field);
        $secureSubmitFieldFound = in_array($type, $this->processPaymentsFor);
        $hasFeed = $this->has_feed($form_id);

        if (!$secureSubmitFieldFound) {
            return $content;
        }

        if ($this->getSecureSubmitJsResponse()) {
            $content .= '<input type=\'hidden\' name=\'securesubmit_response\' id=\'gf_securesubmit_response\' value=\'' . rgpost('securesubmit_response') . '\' />';
        }

        if (!$hasFeed && $secureSubmitFieldFound) { // Style sheet wont have loaded
            $fieldLabel = $field->label;
            $content = '<span style="color:#ce1025 !important;padding-left:3px;font-size:20px !important;font-weight:700 !important;">Your ['.$fieldLabel.'] seems to be missing a feed. Please check your configuration!!</span>';
        }

        return $content;
    }

    /**
     * @param array $validationResult
     *
     * @return array
     */
    public function maybe_validate($validationResult)
    {
        if (!$this->has_feed($validationResult['form']['id'], true)) {
            return $validationResult;
        }

        foreach ($validationResult['form']['fields'] as $field) {
            $currentPage = GFFormDisplay::get_source_page($validationResult['form']['id']);
            $fieldOnCurrentPage = $currentPage > 0 && $field['pageNumber'] == $currentPage;
            $fieldType = GFFormsModel::get_input_type($field);

            if (!in_array($fieldType, $this->processPaymentsFor) || !$fieldOnCurrentPage) {
                continue;
            }

            if (false == $this->validateACH() && $this->getSecureSubmitJsError() && $this->hasPayment($validationResult)) {
                $field['failed_validation'] = true;
                $field['validation_message'] = "The following error occured: [".$this->getSecureSubmitJsError() . "]";
            } else {
                $field['failed_validation'] = false;
            }

            $validationResult['is_valid'] = !$field['failed_validation'];

            break;
        }

        return parent::maybe_validate($validationResult);
    }

    /**
     * @param $validation_result
     *
     * @return mixed
     */
    public function validation($validation_result)
    {
        if (!rgar($validation_result['form'], 'id', false)) {
            return $validation_result;
        }

        if (!$this->has_feed($validation_result['form']['id'], true)) {
            return $validation_result;
        }

        $this->isACH = false;
        $this->isCC = false;
        foreach ($validation_result['form']['fields'] as $field) {
            $current_page = GFFormDisplay::get_source_page($validation_result['form']['id']);
            $field_on_curent_page = $current_page > 0 && $field['pageNumber'] == $current_page;
            $fieldType = GFFormsModel::get_input_type($field);

            if ($fieldType == 'hpsACH' && $field_on_curent_page) {
                $this->isACH = $field;
            }
            if (in_array($fieldType, $this->ccFields) && $field_on_curent_page) {
                $this->isCC = $field;
                if (false == $this->validateACH() && $this->getSecureSubmitJsError() && $this->hasPayment($validation_result)) {
                    $field['failed_validation'] = true;
                    $field['validation_message'] = $this->getSecureSubmitJsError();
                } else {
                    // override validation in case user has marked field as required allowing securesubmit to handle cc validation
                    $field['failed_validation'] = false;
                }
            }
        }
        // revalidate the validation result
        $validation_result['is_valid'] = true;
        foreach ($validation_result['form']['fields'] as $field) {
            if (in_array($field['type'], $this->processPaymentsFor)
                && false !== $this->isACH
                && false !== $this->isCC
                && false === $this->isCC->failed_validation
            ) {
                continue;
            }

            if ($field['failed_validation']) {
                $validation_result['is_valid'] = false;
                break;
            }
        }

        return parent::validation($validation_result);
    }

    /**
     *
     * @param $feed - Current configured payment feed
     * @param $submission_data - Contains form field data submitted by the user as well as payment information (i.e. payment amount, setup fee, line items, etc...)
     * @param $form - Current form array containing all form settings
     * @param $entry - Current entry array containing entry information (i.e data submitted by users). NOTE: the entry hasn't been saved to the database at this point, so this $entry object does not have the 'ID' property and is only a memory representation of the entry.
     *
     * @return array - Return an $authorization array in the following format:
     * [
     *  'is_authorized' => true|false,
     *  'error_message' => 'Error message',
     *  'transaction_id' => 'XXX',
     *
     *  //If the payment is captured in this method, return a 'captured_payment' array with the following information about the payment
     *  'captured_payment' => ['is_success'=>true|false, 'error_message' => 'error message', 'transaction_id' => 'xxx', 'amount' => 20]
     * ]
     */
    public function authorize($feed, $submission_data, $form, $entry)
    {
        $auth = array(
            'is_authorized' => false,
            'captured_payment' => array('is_success' => false),
        );
        $this->includeSecureSubmitSDK();

        $submission_data = array_merge($submission_data, $this->get_submission_dataACH($feed, $form, $entry));
        $isCCData = $this->getSecureSubmitJsResponse();

        if (empty($isCCData->token_value) && false !== $this->isACH && !empty($submission_data['ach_number'])) {
            $auth = $this->authorizeACH($feed, $submission_data, $form, $entry);
        } elseif (empty($submission_data['ach_number']) && false !== $this->isCC && !empty($isCCData->token_value)) {
            $auth = $this->authorizeCC($feed, $submission_data, $form, $entry);
        } else {
            $failMessage = __('Please check your entries and submit only Credit Card or Bank Transfer');
            $auth = $this->authorization_error($failMessage);
        }

        return $auth;
    }

    /**
     *
     * @param $feed - Current configured payment feed
     * @param $submission_data - Contains form field data submitted by the user as well as payment information (i.e. payment amount, setup fee, line items, etc...)
     * @param $form - Current form array containing all form settings
     * @param $entry - Current entry array containing entry information (i.e data submitted by users). NOTE: the entry hasn't been saved to the database at this point, so this $entry object does not have the 'ID' property and is only a memory representation of the entry.
     *
     * @return array - Return an $authorization array in the following format:
     * [
     *  'is_authorized' => true|false,
     *  'error_message' => 'Error message',
     *  'transaction_id' => 'XXX',
     *
     *  //If the payment is captured in this method, return a 'captured_payment' array with the following information about the payment
     *  'captured_payment' => ['is_success'=>true|false, 'error_message' => 'error message', 'transaction_id' => 'xxx', 'amount' => 20]
     * ]
     */
    private function authorizeACH($feed, $submission_data, $form, $entry)
    {
        $note = null;

        /** Currently saved plugin settings */
        $settings = $this->get_plugin_settings();

        /** This is the message show to the consumer if the rule is flagged */
        $fraud_message = (string)$this->get_setting(
            "fraud_message",
            'Please contact us to complete the transaction.',
            $settings
        );

        /** Maximum number of failures allowed before rule is triggered */
        $fraud_velocity_attempts = (int)$this->get_setting("fraud_velocity_attempts", '3', $settings);

        /** Maximum amount of time in minutes to track failures. If this amount of time elapse between failures then the counter($HeartlandHPS_FailCount) will reset */
        $fraud_velocity_timeout = (int)$this->get_setting("fraud_velocity_timeout", '10', $settings);

        /** Variable name with hash of IP address to identify uniqe transient values         */
        $HPS_VarName = (string)"HeartlandHPS_Velocity_" . md5($this->getRemoteIP());

        /** Running count of failed transactions from the current IP*/
        $HeartlandHPS_FailCount = (int)get_transient($HPS_VarName);

        /** Defaults to true or checks actual settings for this plugin from $settings. If true the following settings are applied:
         *
         * $fraud_message
         *
         * $fraud_velocity_attempts
         *
         * $fraud_velocity_timeout
         *
         */
        $enable_fraud = (bool)($this->get_setting("enable_fraud", 'true', $settings) === 'true');

        try {
            /** @var HpsFluentCheckService $service */
            /** @var HpsCheckResponse $response */
            /** @var HpsCheck $check */
            /** @var string $note displayed message for consumer */

            $check = new ECheck();
            $check->accountNumber = $submission_data['ach_number']; // from form $account_number_field_input
            $check->routingNumber = $submission_data['ach_route'];  // from form $routing_number_field_input
            
            $check->checkHolder = $this->checkHolderData($feed, $submission_data, $entry);
            $check->secCode = SecCode::WEB;
            $check->entryMode = EntryMethod::MANUAL;
            //HpsCheckType::BUSINESS; // drop down choice PERSONAL or BUSINESS $check_type_input
            $check->checkType = $submission_data['ach_check_type'];
            //HpsAccountType::CHECKING; // drop down choice CHECKING or SAVINGS $account_type_input
            $check->accountType = $submission_data['ach_account_type'];
            $config = $this->getHpsServicesConfig($this->getSecretApiKey($feed));
            $address = $this->buildAddress($feed, $submission_data, $entry);

            /**
             * if fraud_velocity_attempts is less than the $HeartlandHPS_FailCount then we know
             * far too many failures have been tried
             */
            if ($enable_fraud && $HeartlandHPS_FailCount >= $fraud_velocity_attempts) {
                sleep(5);
                $issuerResponse = (string)get_transient($HPS_VarName . 'IssuerResponse');
                return $this->authorization_error(wp_sprintf('%s %s', $fraud_message, $issuerResponse));
                //throw new HpsException(wp_sprintf('%s %s', $fraud_message, $issuerResponse));
            }

            $response = $check->charge($submission_data['payment_amount'])
            ->withCurrency('USD')
            ->withAddress($address)
            ->execute();
            do_action('heartland_gravityforms_transaction_success', $form, $entry, $response, null);

            $type = 'Payment';
            $amount_formatted = GFCommon::to_money($submission_data['payment_amount'], GFCommon::get_currency());
            $note = sprintf(
                __('%s has been completed. Amount: %s. Transaction Id: %s.', $this->_slug),
                $type,
                $amount_formatted,
                $response->transactionId
            );

            $auth = array(
                'is_authorized' => true,
                'captured_payment' => array(
                    'is_success' => true,
                    'transaction_id' => $response->transactionId,
                    'amount' => $submission_data['payment_amount'],
                    'payment_method' => 'ACH',
                    'securesubmit_payment_action' => 'checkSale',
                    'note' => $note,
                ),
            );
        } catch (HpsCheckException $e) {
            do_action('heartland_gravityforms_transaction_failure', $form, $entry, $e);
            $err = null;
            if (is_array($e->details)) {
                foreach ($e->details as $error) {
                    if ($error->messageType === 'Error') {
                        $err .= $error->message . "\r\n";
                    }
                }
            } else {
                $err .= $e->details->message . "\r\n";
            }
            // if advanced fraud is enabled, increment the error count
            if ($enable_fraud) {
                if (empty($HeartlandHPS_FailCount)) {
                    $HeartlandHPS_FailCount = 0;
                }

                set_transient(
                    $HPS_VarName,
                    $HeartlandHPS_FailCount + 1,
                    MINUTE_IN_SECONDS * $fraud_velocity_timeout
                );

                if ($HeartlandHPS_FailCount < $fraud_velocity_attempts) {
                    set_transient(
                        $HPS_VarName . 'IssuerResponse',
                        $err,
                        MINUTE_IN_SECONDS * $fraud_velocity_timeout
                    );
                }
            }

            $auth = $this->authorization_error($err);
            $auth['transaction_id'] = (string)$e->transactionId;
        } catch (HpsException $e) {
            do_action('heartland_gravityforms_transaction_failure', $form, $entry, $e);
            // if advanced fraud is enabled, increment the error count
            if ($enable_fraud) {
                if (empty($HeartlandHPS_FailCount)) {
                    $HeartlandHPS_FailCount = 0;
                }

                set_transient(
                    $HPS_VarName,
                    $HeartlandHPS_FailCount + 1,
                    MINUTE_IN_SECONDS * $fraud_velocity_timeout
                );

                if ($HeartlandHPS_FailCount < $fraud_velocity_attempts) {
                    set_transient(
                        $HPS_VarName . 'IssuerResponse',
                        $err,
                        MINUTE_IN_SECONDS * $fraud_velocity_timeout
                    );
                }
            }

            $auth = $this->authorization_error($e->getMessage());
        } catch (Exception $e) {
            do_action('heartland_gravityforms_transaction_failure', $form, $entry, $e);
            $auth = $this->authorization_error($e->getMessage());
        }
        return $auth;
    }

    /**
     * @param $form
     *
     * @return bool|\GF_Field
     */
    private function get_ach_field($form)
    {
        $fields = GFAPI::get_fields_by_type($form, array('ach'));
        return empty($fields) ? false : $fields[0];
    }

    /**
     * @param $form
     *
     * @return bool|\GF_Field
     */
    private function get_hpscredit_card_field($form)
    {
        $fields = GFAPI::get_fields_by_type($form, array('hpscreditcard'));
        return empty($fields) ? false : $fields[0];
    }

    /**
     * @param $form
     *
     * @return bool|\GF_Field
     */
    private function get_address_card_field($feed)
    {
        $form = GFAPI::get_form($feed['form_id']);
        $fields = GFAPI::get_fields_by_type($form, array('address'));
        return empty($fields) ? false : $fields[0];
    }

    /**
     * @param $feed
     * @param $form
     * @param $entry
     *
     * @return mixed
     */
    public function get_submission_dataACH($feed, $form, $entry)
    {
        $this_id = $feed['id'];

        $submission_data = $this->current_submission_data;

        $submission_data['ach_number'] = $this->validateACH();
        $submission_data['ach_route'] = $this->remove_spaces_from_card_number(rgpost(GF_Field_HPSach::HPS_ACH_ROUTING_FIELD_NAME));
        $submission_data['ach_account_type'] = '';
        $submission_data['ach_check_type'] = '';
        $submission_data['ach_check_holder'] = '';

        $accountType = rgpost(GF_Field_HPSach::HPS_ACH_TYPE_FIELD_NAME);
        $checkType = rgpost(GF_Field_HPSach::HPS_ACH_CHECK_FIELD_NAME);
        $accountTypeOptions = array(
            1 => AccountType::CHECKING,
            2 => AccountType::SAVINGS,
        );
        $checkTypeOptions = array(
            1 => CheckType::PERSONAL,
            2 => CheckType::BUSINESS,
        );

        if (key_exists($accountType, $accountTypeOptions) && key_exists($checkType, $checkTypeOptions)) {
            //HpsAccountType::CHECKING; drop down choice CHECKING or SAVINGS $account_type_input
            $submission_data['ach_account_type'] = $accountTypeOptions[ $accountType ];
            //HpsCheckType::BUSINESS; drop down choice PERSONAL or BUSINESS $check_type_input
            $submission_data['ach_check_type'] = $checkTypeOptions[ $checkType ];
        }
        $submission_data['ach_check_holder'] = rgpost(GF_Field_HPSach::HPS_ACH_CHECK_HOLDER_FIELD_NAME);

        return gf_apply_filters(array('gform_submission_data_pre_process_payment', $form['id']), $submission_data, $feed, $form, $entry);
    }

    /**
     * @return mixed|null
     */
    private function validateACH()
    {
        $value = $this->remove_spaces_from_card_number(rgpost(GF_Field_HPSach::HPS_ACH_ACCOUNT_FIELD_NAME));
        $isValid = preg_match('/^[\d]{4,17}$/', $value) === 1;

        return $isValid ? $value : null;
    }

    /**
     *
     * @param $feed - Current configured payment feed
     * @param $submission_data - Contains form field data submitted by the user as well as payment information (i.e. payment amount, setup fee, line items, etc...)
     * @param $form - Current form array containing all form settings
     * @param $entry - Current entry array containing entry information (i.e data submitted by users). NOTE: the entry hasn't been saved to the database at this point, so this $entry object does not have the 'ID' property and is only a memory representation of the entry.
     *
     * @return array - Return an $authorization array in the following format:
     * [
     *  'is_authorized' => true|false,
     *  'error_message' => 'Error message',
     *  'transaction_id' => 'XXX',
     *
     *  //If the payment is captured in this method, return a 'captured_payment' array with the following information about the payment
     *  'captured_payment' => ['is_success'=>true|false, 'error_message' => 'error message', 'transaction_id' => 'xxx', 'amount' => 20]
     * ]
     */
    private function authorizeCC($feed, $submission_data, $form, $entry)
    {
        $this->populateCreditCardLastFour($form);

        /** Currently saved plugin settings */
        $settings = $this->get_plugin_settings();

        /** This is the message show to the consumer if the rule is flagged */
        $fraud_message = (string)$this->get_setting(
            "fraud_message",
            'Please contact us to complete the transaction.',
            $settings
        );

        /** Maximum number of failures allowed before rule is triggered */
        $fraud_velocity_attempts = (int)$this->get_setting("fraud_velocity_attempts", '3', $settings);

        /** Maximum amount of time in minutes to track failures. If this amount of time elapse between failures then the counter($HeartlandHPS_FailCount) will reset */
        $fraud_velocity_timeout = (int)$this->get_setting("fraud_velocity_timeout", '10', $settings);

        /** Variable name with hash of IP address to identify uniqe transient values         */
        $HPS_VarName = (string)"HeartlandHPS_Velocity_" . md5($this->getRemoteIP());

        /** Running count of failed transactions from the current IP*/
        $HeartlandHPS_FailCount = (int)get_transient($HPS_VarName);

        /** Defaults to true or checks actual settings for this plugin from $settings. If true the following settings are applied:
         *
         * $fraud_message
         *
         * $fraud_velocity_attempts
         *
         * $fraud_velocity_timeout
         *
         */
        $enable_fraud = (bool)($this->get_setting("enable_fraud", 'true', $settings) === 'true');

        if ($this->getSecureSubmitJsError()) {
            return $this->authorization_error($this->getSecureSubmitJsError());
        }

        $isAuth = $this->getAuthorizeOrCharge($feed) == 'authorize';

        try {
            $config = $this->getHpsServicesConfig();
            $cardHolder = $this->cardHolderData($feed, $submission_data, $entry);
            $address = $this->buildAddress($feed, $submission_data, $entry);

            /**
             * if fraud_velocity_attempts is less than the $HeartlandHPS_FailCount then we know
             * far too many failures have been tried
             */
            if ($enable_fraud && $HeartlandHPS_FailCount >= $fraud_velocity_attempts) {
                sleep(5);
                $issuerResponse = (string)get_transient($HPS_VarName . 'IssuerResponse');
                return $this->authorization_error(wp_sprintf('%s %s', $fraud_message, $issuerResponse));
                //throw new HpsException(wp_sprintf('%s %s', $fraud_message, $issuerResponse));
            }
            $tokenValue = $this->getSecureSubmitJsResponse();
            $cardHolder->token = ($tokenValue != null
            ? $tokenValue->token_value
            : '');
            /**
             * CardHolder Authentication (3D Secure)
             *
             */
            $secureEcommerce = '';
            if ($this->getEnable3DSecure() === 'yes'
                && false !== ($data = json_decode(stripslashes($submission_data['securesubmit_cca_data'])))
                && isset($data) && isset($data->ActionCode)
                && in_array($data->ActionCode, array('SUCCESS', 'NOACTION'))
            ) {
                $dataSource = '';
                switch ($submission_data['card_type']) {
                case 'visa':
                    $dataSource = 'Visa 3DSecure';
                    break;
                case 'mastercard':
                    $dataSource = 'MasterCard 3DSecure';
                    break;
                case 'discover':
                    $dataSource = 'Discover 3DSecure';
                    break;
                case 'amex':
                    $dataSource = 'AMEX 3DSecure';
                    break;
                }

                $cavv = isset($data->Payment->ExtendedData->CAVV)
                    ? $data->Payment->ExtendedData->CAVV
                    : '';
                $eciFlag = isset($data->Payment->ExtendedData->ECIFlag)
                    ? substr($data->Payment->ExtendedData->ECIFlag, 1)
                    : '';
                $xid = isset($data->Payment->ExtendedData->XID)
                    ? $data->Payment->ExtendedData->XID
                    : '';

                $secureEcommerce = new EcommerceInfo();
                $secureEcommerce->type       = '3DSecure';
                $secureEcommerce->paymentDataSource  = $dataSource;
                $secureEcommerce->cavv       = $cavv;
                $secureEcommerce->eci    = $eciFlag;
                $secureEcommerce->xid        = $xid;
            }
            
            
            $cpcReq = false;
            if ($this->getAllowLevelII() === 'yes') {
                $cpcReq = true;
            }

            $currency = GFCommon::get_currency();
            $transaction = null;
            if ($isAuth) {
                $auth_transaction = $cardHolder->authorize($submission_data['payment_amount'])
                                ->withCurrency($currency)
                                ->withAddress($address)
                                ->withAllowDuplicates(true)
                                ->withCommercialRequest($cpcReq);
                if($secureEcommerce){
                    $transaction = $auth_transaction->withEcommerceInfo($secureEcommerce);
                }
                $transaction = $auth_transaction->execute();
            } else {
                $capt_transaction = $cardHolder->charge($submission_data['payment_amount'])
                                ->withCurrency($currency)
                                ->withAddress($address)
                                ->withAllowDuplicates(true)
                                ->withCommercialRequest($cpcReq);
                if($secureEcommerce){
                    $transaction = $capt_transaction->withEcommerceInfo($secureEcommerce);
                }
                $transaction = $capt_transaction->execute();                                    
            }
            do_action('heartland_gravityforms_transaction_success', $form, $entry, $transaction, $response);
            self::get_instance()->transaction_response = $transaction;

            if ($this->getSendEmail() == 'yes') {
                $this->sendEmail($form, $entry, $transaction, $cardHolder);
            }

            $type = $isAuth
                ? 'Authorization'
                : 'Payment';
            $amount_formatted = GFCommon::to_money($submission_data['payment_amount'], GFCommon::get_currency());
            $note = sprintf(
                __('%s has been completed. Amount: %s. Transaction Id: %s.', $this->_slug),
                $type,
                $amount_formatted,
                $transaction->transactionId
            );
            if ($cpcReq  && $transaction->commercialIndicator == 'B'
                    || $transaction->commercialIndicator == 'R'
                    || $transaction->commercialIndicator == 'S'
            ) {
                $CardHolderPONbr = $this->getLevelIICustomerPO($feed);

                if ($this->getLevelIITaxType($feed) == "SALES_TAX") {
                    $TaxType = HpsTaxType::SALES_TAX;
                } elseif ($this->getLevelIITaxType($feed) == "NOTUSED") {
                    $TaxType = HpsTaxType::NOTUSED;
                } elseif ($this->getLevelIITaxType($feed) == "TAXEXEMPT") {
                    $TaxType = HpsTaxType::TAXEXEMPT;
                }

                $TaxAmt = $this->getLevelIICustomerTaxAmount($feed);

                if (!empty($CardHolderPONbr) && !empty($TaxType) && !empty($TaxAmt)) {
                    $cpcResponse = $service->cpcEdit($transaction->transactionId, $cpcData);
                    $cpcResponse = $response->edit()
                    ->withPoNumber($CardHolderPONbr)
                    ->withTaxType($TaxType)
                    ->withTaxAmount($TaxAmt)
                    ->execute();
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
            do_action('heartland_gravityforms_transaction_failure', $form, $entry, $e);
            // if advanced fraud is enabled, increment the error count
            if ($enable_fraud) {
                if (empty($HeartlandHPS_FailCount)) {
                    $HeartlandHPS_FailCount = 0;
                }

                set_transient(
                    $HPS_VarName,
                    $HeartlandHPS_FailCount + 1,
                    MINUTE_IN_SECONDS * $fraud_velocity_timeout
                );

                if ($HeartlandHPS_FailCount < $fraud_velocity_attempts) {
                    set_transient(
                        $HPS_VarName . 'IssuerResponse',
                        $e->getMessage(),
                        MINUTE_IN_SECONDS * $fraud_velocity_timeout
                    );
                }
            }
            $auth = $this->authorization_error($e->getMessage());
        } catch (Exception $e) {
            do_action('heartland_gravityforms_transaction_failure', $form, $entry, $e);
            $auth = $this->authorization_error($e->getMessage());
        }

        return $auth;
    }

    // Helper functions

    /**
     * @param       $entry
     * @param array $result
     *
     * @return mixed
     */
    public function updateAuthorizationEntry($entry, $result = array())
    {
		print_r($result);die;
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
     * @param      $form
     * @param      $entry
     * @param      $transaction
     * @param null $cardHolder
     */
    protected function sendEmail($form, $entry, $transaction, $cardHolder = null)
    {
        $to = $this->getSendEmailRecipientAddress();
        $subject = 'New Submission: ' . $form['title'];
        $message = sprintf(
            "Form: %s (%d)\r\nEntry ID: %d\r\nTransactionDetails: \r\n%s",
            $form['title'],
            $form['id'],
            $entry['id'],
            print_r($transaction, true)
        );

        if ($cardHolder != null) {
            $message .= "Card Holder Details:\r\n" . print_r($cardHolder, true);
        }

        wp_mail($to, $subject, $message);
    }

    /**
     * @param $feed
     * @param $submission_data
     * @param $entry
     *
     * @return HpsCardHolder|HpsAddress
     */
    private function cardHolderData($feed, $submission_data, $entry)
    {
        $cardHolder = new CreditCardData();
        $firstName = '';
        $lastName = '';
        if ('' === rgar($submission_data, 'card_name')) {
            $submission_data['card_name'] = rgpost('card_name');
        }

        try {
            $name = explode(' ', rgar($submission_data, 'card_name'));
            $firstName = $name[0];
            unset($name[0]);
            $lastName = implode(' ', $name);
        } catch (Exception $ex) {
            $firstName = rgar($submission_data, 'card_name');
        }
        $cardHolder->cardHolderName = $firstName . ' '. $lastName;
        return $cardHolder;
    }

    /**
     * @param $feed
     * @param $submission_data
     * @param $entry
     *
     * @return HpsCheckHolder|HpsAddress
     */
    private function checkHolderData($feed, $submission_data, $entry)
    {
        $checkHolder = new ECheck();
        $checkHolder->checkName = htmlspecialchars(rgar($submission_data, 'ach_check_holder')); //'check holder';

        $firstName = '';
        $lastName = '';
        try {
            $name = explode(' ', rgar($submission_data, 'ach_check_holder'));
            $firstName = $name[0];
            unset($name[0]);
            $lastName = implode(' ', $name);
        } catch (Exception $ex) {
            $firstName = rgar($submission_data, 'ach_check_holder');
        }

        $checkHolder->firstName = htmlspecialchars($firstName);
        $checkHolder->lastName = htmlspecialchars($lastName);
        return $checkHolder;
    }

    /**
     * @param $feed
     * @param $submission_data
     * @param $entry
     *
     * @return \HpsAddress
     */
    private function buildAddress($feed, $submission_data, $entry)
    {
        $isRecurring = isset($feed['meta']['transactionType']) && $feed['meta']['transactionType'] == 'subscription';
        $address = new Address();

        $address->address = rgar($submission_data, 'address')
            . rgar($submission_data, 'address2');
        if (empty($address->address) && in_array('billingInformation_address', $feed['meta'])) {
            $address->address
                = $entry[ $feed['meta']['billingInformation_address'] ] . $entry[ $feed['meta']['billingInformation_address2'] ];
        }

        $address->city = rgar($submission_data, 'city');
        if (empty($address->city) && in_array('billingInformation_city', $feed['meta'])) {
            $address->city = $entry[ $feed['meta']['billingInformation_city'] ];
        }

        $address->state = rgar($submission_data, 'state');
        if (empty($address->state) && in_array('billingInformation_state', $feed['meta'])) {
            $address->state = $entry[ $feed['meta']['billingInformation_state'] ];
        }

        $address->zip = rgar($submission_data, 'zip');
        if (empty($address->zip) && in_array('billingInformation_zip', $feed['meta'])) {
            $address->zip = $entry[ $feed['meta']['billingInformation_zip'] ];
        }

        $address->country = $this->normalizeCountry(rgar($submission_data, 'country'), $isRecurring);
        if (empty($address->country) && in_array('billingInformation_country', $feed['meta'])) {
            $address->country = $this->normalizeCountry($entry[ $feed['meta']['billingInformation_country'] ], $isRecurring);
        }

        return $address;
    }

    /**
     * @param mixed $validation_result
     *
     * @return bool
     */
    public function hasPayment($validation_result)
    {
        $form = $validation_result['form'];
        $entry = GFFormsModel::create_lead($form);
        $feed = $this->get_payment_feed($entry, $form);

        if (!$feed) {
            return false;
        }

        $submission_data = $this->get_submission_data($feed, $form, $entry);

        // Do not process payment if payment amount is 0 or less
        return floatval($submission_data['payment_amount']) > 0;
    }

    /**
     * @param $form
     */
    public function populateCreditCardLastFour($form)
    {
        $cc_field = $this->get_credit_card_field($form);
        $response = $this->getSecureSubmitJsResponse();
        $_POST[ 'input_' . $cc_field['id'] . '_1' ] = 'XXXXXXXXXXXX' . ($response != null
                ? $response->last_four
                : '');
        $_POST[ 'input_' . $cc_field['id'] . '_4' ] = ($response != null
            ? $response->card_type
            : '');
    }

    public function includeSecureSubmitSDK()
    {
        require_once plugin_dir_path(__FILE__) . 'includes/vendor/autoload.php';
        do_action('gform_securesubmit_post_include_api');
    }

    /**
     * @param null $feed
     *
     * @return string
     */
    public function getSecretApiKey($feed = null)
    {
        return $this->getApiKey('secret', $feed);
    }

    /**
     * @param null $feed
     *
     * @return string
     */
    public function getLevelIICustomerPO($feed = null)
    {
        if ($feed != null && isset($feed['meta']["mappedFields_customerpo"])) {
            return (string)$_POST[ 'input_' . $feed["meta"]["mappedFields_customerpo"] ];
        }
        return null;
    }

    /**
     * @param null $feed
     *
     * @return string
     */
    public function getLevelIITaxType($feed = null)
    {
        if ($feed != null && isset($feed['meta']["mappedFields_taxtype"])) {
            return (string)$_POST[ 'input_' . $feed["meta"]["mappedFields_taxtype"] ];
        }
        return null;
    }

    /**
     * @param null $feed
     *
     * @return string
     */
    public function getLevelIICustomerTaxAmount($feed = null)
    {
        if ($feed != null && isset($feed['meta']["mappedFields_taxamount"])) {
            return (string)$_POST[ 'input_' . $feed["meta"]["mappedFields_taxamount"] ];
        }
        return null;
    }

    /**
     * @return string
     */
    public function getAllowLevelII()
    {
        $settings = $this->get_plugin_settings();

        return (string)$this->get_setting('allow_level_ii', 'no', $settings);
    }

    /**
     * @param null $feed
     *
     * @return string
     */
    public function getPublicApiKey($feed = null)
    {
        return $this->getApiKey('public', $feed);
    }

    /**
     * @param string $type
     * @param null   $feed
     *
     * @return string
     */
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

        return (string)trim($this->get_setting("{$type}_api_key", '', $settings));
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function getQueryStringApiKey($type = 'secret')
    {
        return rgget($type);
    }

    /**
     * @param null $feed
     *
     * @return string
     */
    public function getAuthorizeOrCharge($feed = null)
    {
        if ($feed != null && isset($feed['meta']['authorize_or_charge'])) {
            return (string)$feed['meta']['authorize_or_charge'];
        }
        $settings = $this->get_plugin_settings();

        return (string)$this->get_setting('authorize_or_charge', 'charge', $settings);
    }

    /**
     * @return string
     */
    public function getAllowPaymentActionOverride()
    {
        $settings = $this->get_plugin_settings();

        return (string)$this->get_setting('allow_payment_action_override', 'no', $settings);
    }

    /**
     * @return string
     */
    public function getAllowAPIKeysOverride()
    {
        $settings = $this->get_plugin_settings();

        return (string)$this->get_setting('allow_api_keys_override', 'no', $settings);
    }

    /**
     * @return string
     */
    public function getSendEmail()
    {
        $settings = $this->get_plugin_settings();

        return (string)$this->get_setting('send_email', 'no', $settings);
    }

    /**
     * @return string
     */
    public function getEnable3DSecure()
    {
        $settings = $this->get_plugin_settings();

        return (string)$this->get_setting('enable_threedsecure', 'no', $settings);
    }

    /**
     * @return string
     */
    public function getEnable3DSecureApiIdentifier()
    {
        $settings = $this->get_plugin_settings();

        return (string)$this->get_setting('enable_threedsecure_api_identifier', 'no', $settings);
    }

    /**
     * @return string
     */
    public function getEnable3DSecureOrgUnitId()
    {
        $settings = $this->get_plugin_settings();

        return (string)$this->get_setting('enable_threedsecure_org_unit_id', 'no', $settings);
    }

    /**
     * @return string
     */
    public function getEnable3DSecureApiKey()
    {
        $settings = $this->get_plugin_settings();

        return (string)$this->get_setting('enable_threedsecure_api_key', 'no', $settings);
    }

    /**
     * @return string
     */
    public function getSendEmailRecipientAddress()
    {
        $settings = $this->get_plugin_settings();

        return (string)$this->get_setting('send_email_recipient_address', '', $settings);
    }

    /**
     * @param $form
     *
     * @return bool
     */
    public function hasFeedCallback($form)
    {
        return $form && $this->has_feed($form['id']);
    }

    /**
     * @return array|mixed|object
     */
    public function getSecureSubmitJsResponse()
    {
        return json_decode(rgpost('securesubmit_response'));
    }

    /**
     * @return bool
     */
    public function getSecureSubmitJsError()
    {
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
    public function isFieldOnValidPage($field, $parent)
    {
        $form = $this->get_current_form();

        $mapped_field_id = $this->get_setting($field['name']);
        $mapped_field = GFFormsModel::get_field($form, $mapped_field_id);
        $mapped_field_page = rgar($mapped_field, 'pageNumber');

        $cc_field = $this->get_credit_card_field($form);
        $cc_page = rgar($cc_field, 'pageNumber');

        if ($mapped_field_page > $cc_page) {
            $this->set_field_error(
                $field,
                __('The selected field needs to be on the same page as the Credit Card field or a previous page.', $this->_slug)
            );
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
     *
     * @return mixed
     */
    public function replaceMergeTags($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format)
    {
        $mergeTags = array(
            'transactionId' => '{securesubmit_transaction_id}',
            'authorizationCode' => '{securesubmit_authorization_code}',
        );

        $gFormsKey = array('transactionId' => 'transaction_id',);

        foreach ($mergeTags as $key => $mergeTag) {
            // added for GF 1.9.x
            if (strpos($text, $mergeTag) === false || empty($entry) || empty($form)) {
                return $text;
            }

            $value = '';
            if (class_exists('GFSecureSubmit') && isset(GFSecureSubmit::get_instance()->transaction_response)) {
                $value = GFSecureSubmit::get_instance()->transaction_response->$key;
            }

            if (isset($gFormsKey[ $key ]) && empty($value)) {
                $value = rgar($entry, $gFormsKey[ $key ]);
            }

            $text = str_replace($mergeTag, $value, $text);
        }

        return $text;
    }

    /**
     * @param $form
     *
     * @return mixed
     */
    public function addClientSideMergeTags($form)
    {
        include plugin_dir_path(__FILE__) . '../templates/client-side-merge-tags.php';

        return $form;
    }

    /**Attempts to get real ip even if there is a proxy chain
     *
     * @return string
     */
    private function getRemoteIP()
    {
        $remoteIP = $_SERVER['REMOTE_ADDR'];
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
            $remoteIPArray = array_values(
                array_filter(
                    explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])
                )
            );
            $remoteIP = end($remoteIPArray);
        }

        return $remoteIP;
    }

    /**
     * Gets the payment validation result.
     *
     * @since  Unknown
     * @access public
     *
     * @used-by GFPaymentAddOn::validation()
     *
     * @param array $validationResult    Contains the form validation results.
     * @param array $authorizationResult Contains the form authorization results.
     *
     * @return array The validation result for the credit card field.
     */
    public function get_validation_result($validationResult, $authorizationResult)
    {
        $ach_page = 0;
        foreach ($validationResult['form']['fields'] as $field) {
            if ($field->type == 'hpsACH' || $field->type == 'hpscreditcard') {
                $field->failed_validation  = true;
                $field->validation_message = $authorizationResult['error_message'];
                $ach_page                  = $field->pageNumber;
                break;
            }
        }
        $validationResult['credit_card_page'] = $ach_page;
        $validationResult['is_valid']         = false;
        return parent::get_validation_result($validationResult, $authorizationResult);
    }

    // # HPS SUBSCRIPTION FUNCTIONS ---------------------------------------------------------------------------------------

    /**
     * Subscribe the user to a HPS Pay plan. This process works like so:
     *
     * 1 - Get existing plan or create new plan (plan ID generated by feed name, id and recurring amount).
     * 2 - Create new customer.
     * 3 - Create new subscription by subscribing customer to plan.
     *
     * @since  Unknown
     * @access public
     *
     * @uses   GFSecureSubmit::includeSecureSubmitSDK()
     * @uses   GFSecureSubmit::getSecureSubmitJsError()
     * @uses   GFPaymentAddOn::authorization_error()
     * @uses   GFAddOn::log_debug()
     * @uses   HpsInputValidation::checkAmount
     * @uses   \rgars
     * @uses   \GFSecureSubmit::getPayPlanService
     * @uses   \GFSecureSubmit::create_customer
     * @uses   \HpsPayPlanService::addCustomer
     * @uses   \GFSecureSubmit::createPaymentMethod
     * @uses   \HpsPayPlanService::addPaymentMethod
     * @uses   \GFSecureSubmit::create_plan
     * @uses   \HpsPayPlanService::addSchedule
     * @uses   \GFSecureSubmit::processRecurring
     *
     * @param array $feed            The feed object currently being processed.
     * @param array $submission_data The customer and transaction data.
     * @param array $form            The form object currently being processed.
     * @param array $entry           The entry object currently being processed.
     *
     * @return array Subscription details if successful. Contains error message if failed.
     */
    public function subscribe($feed, $submission_data, $form, $entry)
    {
        /** @var array $subscribResult */
        // Include HPS API library.
        $this->includeSecureSubmitSDK();
        $userError = 'Your subscription could not be created. Please try again or contact customer service. ';

        // If there was an error when retrieving the HPS.js token, return an authorization error.
        if ($this->getSecureSubmitJsError()) {
            $this->log_debug(__METHOD__ . '(): Tokenization error: ' . $this->getSecureSubmitJsError());

            return $this->authorization_error($userError . $this->getSecureSubmitJsError());
        }

        // make sure we arent trying to submit ACH
        if ('' !== rgpost(GF_Field_HPSach::HPS_ACH_CHECK_HOLDER_FIELD_NAME)) {
            $this->log_debug(__METHOD__ . '(): Incorrect submission: ' . $this->getSecureSubmitJsError());

            return $this->authorization_error($userError . 'Currently ACH is not supported for subscriptions');
        }

        // Prepare payment amount and trial period data.
        $payment_amount = HpsInputValidation::checkAmount(rgar($submission_data, 'payment_amount'));
        $setupFeeEnabled = rgar($feed['meta'], 'setupFee_enabled');
        $setupFeeField = rgar($feed['meta'], 'setupFee_product');
        $setupFeePaymentAmount = HpsInputValidation::checkAmount(rgar($submission_data, 'setup_fee'));
        $trialEnabled = rgars($feed, 'meta/trial_enabled');
        $trial_period_days = $trialEnabled ? rgars($feed, 'meta/trial_product') : null;
        $currency = rgar($entry, 'currency');

        $payPlanCustomer = null;
        $payPlanPaymentMethod = null;
        $planSchedule = null;

        try {
            $payPlanService = $this->getPayPlanService($this->getSecretApiKey($feed));

            // while it could be ACH here for the Payplan Customer record it is the same difference
            // Prepare customer metadata.
            $customer = $this->create_customer($feed, $submission_data, $entry);

            $this->log_debug(__METHOD__ . '(): Create customer.');
            /** @var HpsPayPlanCustomer $payPlanCustomer */
            $payPlanCustomer = $payPlanService->addCustomer($customer);

            if (null === $payPlanCustomer->customerKey) {
                $this->log_debug(__METHOD__ . '(): Could not create Pay Plan Customer');

                return $this->authorization_error($userError);
            }

            $this->log_debug(__METHOD__ . '(): Create payment method.');
            $paymentMethod = $this->createPaymentMethod($payPlanCustomer);
            /** @var HpsPayPlanPaymentMethod $payPlanPaymentMethod */
            $payPlanPaymentMethod = $payPlanService->addPaymentMethod($paymentMethod);

            if (null === $payPlanPaymentMethod->paymentMethodKey) {
                $this->log_debug(__METHOD__ . '(): Could not create Pay Plan payment method');

                return $this->authorization_error($userError);
            }

            // Get HPS plan for feed.
            $this->log_debug(__METHOD__ . '(): Create Schedule.');
            /** @var HpsPayPlanSchedule $plan */
            $plan = $this->create_plan(
                $payPlanPaymentMethod,
                $feed,
                $payment_amount,
                $trial_period_days,
                $currency
            );

            // If error was returned when retrieving plan, return plan.

            /** @var HpsPayPlanSchedule $planSchedule */
            $this->log_debug(__METHOD__ . '(): Add Schedule.');
            $planSchedule = $payPlanService->addSchedule($plan);

            // Create the plan unless there is no key.
            if (null === $planSchedule->scheduleKey) {
                $this->log_debug(__METHOD__ . '(): Could not create Pay Plan Schedule');

                return $this->authorization_error($userError);
            }

            // If a setup fee is required, add an invoice item.
            if ($setupFeePaymentAmount) {
                $this->log_debug(__METHOD__ . '(): Processing one time setup fee');
                $payment_amount += $setupFeePaymentAmount;
            } // if

            if (!$trialEnabled) {
                /** @var HpsAuthorization $response */
                /** @noinspection PhpParamsInspection */
                $response = $this->processRecurring(
                    $payment_amount,
                    $feed,
                    $payPlanPaymentMethod,
                    $planSchedule
                );
                do_action('heartland_gravityforms_transaction_success', $form, $entry, $response, $this->getSecureSubmitJsResponse());
            }

            $subscribResult = array(
                'is_success' => true,
                'subscription_id' => $planSchedule->scheduleKey,
                'customer_id' => $customer->customerKey,
                'amount' => $payment_amount,
            ); // array
        } catch (Exception $e) {
            do_action('heartland_gravityforms_transaction_failure', $form, $entry, $e);
            $this->rollbackPayPlanResources($payPlanService, $payPlanCustomer, $payPlanPaymentMethod, $planSchedule);
            // Return authorization error.
            return $this->authorization_error($userError . $e->getMessage());
        }

        if (!isset($subscribResult)) {
            $this->rollbackPayPlanResources($payPlanService, $payPlanCustomer, $payPlanPaymentMethod, $planSchedule);
            $this->log_debug(__METHOD__ . '(): Unknown error ');
            return $this->authorization_error($userError);
        } // if

        // Return subscription data.
        return $subscribResult;
    }

    /**
     * Update entry meta with subscription data
     *
     * @param array $authorization   Contains the result of the subscribe() function.
     * @param array $feed            The feed object currently being processed.
     * @param array $submission_data The customer and transaction data.
     * @param array $form            The form object currently being processed.
     * @param array $entry           The entry object currently being processed.
     *
     * @return array
     */
    public function process_subscription(
        $authorization,
        $feed,
        $submission_data,
        $form,
        $entry
    ) {
        gform_update_meta($entry['id'], 'hps_payplan_subscription_id', $authorization['subscription']['subscription_id']);
        return parent::process_subscription(
            $authorization,
            $feed,
            $submission_data,
            $form,
            $entry
        );
    }

    /**
     * Handle subscription cancellation through entry detail page
     *
     * @param array $entry The entry object currently being processed
     * @param array $feed  The feed object associated with the `$entry`
     *
     * @return bool
     */
    public function cancel($entry, $feed)
    {
        $this->includeSecureSubmitSDK();

        try {
            $scheduleKey = gform_get_meta($entry['id'], 'hps_payplan_subscription_id');
            $service = $this->getPayPlanService($this->getSecretApiKey($feed));
            $subscription = $service->getSchedule($scheduleKey);
            // set schedule to inactive
            $subscription->scheduleStatus = HpsPayPlanScheduleStatus::INACTIVE;
            $service->editSchedule($subscription);
            return true;
        } catch (HpsException $e) {
            return false;
        }
    }

    protected function rollbackPayPlanResources($service, $customer, $paymentMethod, $schedule)
    {
        if ($service === null) {
            return;
        }

        if ($schedule !== null) {
            $service->deleteSchedule($schedule);
        }

        if ($paymentMethod !== null) {
            $service->deletePaymentMethod($paymentMethod);
        }

        if ($customer !== null) {
            $service->deleteCustomer($customer);
        }
    }

    // # HPS HELPER FUNCTIONS ---------------------------------------------------------------------------------------

    /**
     * @param $payment_amount
     * @param $feed
     * @param $payPlanPaymentMethod
     * @param $planSchedule
     *
     * @return array|\HpsBuilderAbstract|\HpsReportTransactionDetails|\HpsReportTransactionSummary|\HpsTransaction|mixed|null
     */
    private function processRecurring($payment_amount, $feed, $payPlanPaymentMethod, $planSchedule)
    {
        static $creditService = null;

        if (null === $creditService) {
            $creditService = new HpsFluentCreditService($this->getHpsServicesConfig($this->getSecretApiKey($feed)));
        }
        
        $details = new HpsTransactionDetails();
        $details->customerId = $payPlanPaymentMethod->customerIdentifier;

        return $creditService
            ->recurring()
            ->withAmount($payment_amount)
            ->withPaymentMethodKey($payPlanPaymentMethod->paymentMethodKey)
            ->withSchedule($planSchedule->scheduleKey)
            ->withDetails($details)
            ->execute();
    }

    /**
     * Create and return a HPS customer with the specified properties.
     *
     * @since    Unknown
     * @access   private
     *
     * @used-by  GFSecureSubmit::subscribe()
     * @uses     GFAddOn::log_debug()
     * @uses     \GFSecureSubmit::CardHolderData
     * @uses     \GFSecureSubmit::getIdentifier
     *
     * @param array $feed  The feed currently being processed.
     * @param array $submission_data
     * @param array $entry The entry currently being processed.
     *
     * @return \HpsPayPlanCustomer The HPS customer object.
     * @internal param array $customer_meta The customer properties.
     * @internal param array $form The form which created the current entry.
     *
     */
    private function create_customer($feed, $submission_data, $entry)
    {
        $acctHolder = $this->cardHolderData($feed, $submission_data, $entry);
        $meta = $this->get_address_card_field($feed);
        //'United States' 'Canada'

        /** @noinspection PhpUndefinedFieldInspection */
        $acctHolder->address->country = $this->normalizeCountry($acctHolder->address->country, true);

        // Convert states names to abbreviations
        $acctHolder->address->state = $this->normalizeState($acctHolder->address->state);

        // Log the customer to be created.
        $this->log_debug(__METHOD__ . '(): Customer meta to be created => ' . print_r($acctHolder, 1));

        /** @var string $modifier This value helps semi uniqely identify the customer */
        $modifier = $this->getSecureSubmitJsResponse()->last_four . $this->getSecureSubmitJsResponse()->card_type;

        $customer = new HpsPayPlanCustomer();
        $customer->customerIdentifier = $this->getIdentifier($modifier . $acctHolder->firstName . $acctHolder->lastName);
        $customer->firstName = $acctHolder->firstName;
        $customer->lastName = $acctHolder->lastName;
        $customer->primaryEmail = rgar($submission_data, 'email');
        $customer->customerStatus = HpsPayPlanCustomerStatus::ACTIVE;
        $customer->addressLine1 = $acctHolder->address->address;
        $customer->city = $acctHolder->address->city;
        $customer->stateProvince = $acctHolder->address->state;
        $customer->zipPostalCode = $acctHolder->address->zip;
        /** @noinspection PhpUndefinedFieldInspection */
        $customer->country = $acctHolder->address->country;

        return $customer;
    }

    /**
     * Retrieve a specific customer from HPS.
     *
     * @since    Unknown
     * @access   protected
     *
     * @used-by  GFSecureSubmit::subscribe()
     * @uses     GFAddOn::log_debug()
     * @uses     HpsPayPlanService::getCustomer()
     * @uses     \GFSecureSubmit::getSecureSubmitJsResponse
     * @uses     \GFSecureSubmit::getIdentifier
     *
     * @param array              $submission_data
     * @param HpsPayPlanCustomer $customer
     *
     * @return bool|\HpsPayPlanPaymentMethod Contains customer data if available. Otherwise, false.
     *
     * @internal param \HpsPayPlanService $payPlanService
     */
    private function createPaymentMethod($customer)
    {
        $paymentMethod = null;
        $acct = $this->getSecureSubmitJsResponse()->token_value;

        if (!empty($acct)) {
            $paymentMethod = new HpsPayPlanPaymentMethod();
            $paymentMethod->paymentMethodIdentifier = $this->getIdentifier('Credit' . $acct);
            $paymentMethod->nameOnAccount = $customer->firstName . ' ' . $customer->lastName;
            /** @noinspection PhpUndefinedFieldInspection */
            $paymentMethod->firstName = $customer->firstName;
            $paymentMethod->lastName = $customer->lastName;
            $paymentMethod->country = $customer->country;
            $paymentMethod->zipPostalCode = $customer->zipPostalCode;
            $paymentMethod->customerKey = $customer->customerKey;
            $paymentMethod->paymentMethodType = HpsPayPlanPaymentMethodType::CREDIT_CARD;
            $paymentMethod->paymentToken = $acct;
        }

        return $paymentMethod;
    }

    /**
     * Create and return a HPS plan with the specified properties.
     *
     * @since   Unknwon
     * @access  public
     *
     * @used-by GFSecureSubmit::subscribe()
     * @uses    \GFSecureSubmit::getIdentifier
     * @uses    \GFSecureSubmit::getPayPlanService
     * @uses    \GFSecureSubmit::getSecretApiKey
     * @uses    HpsInputValidation::checkAmount
     * @uses    \GFSecureSubmit::validPayPlanCycle
     * @uses    HpsPayPlanSchedule
     * @uses    HpsPayPlanAmount
     * @uses    GFAddOn::log_debug()
     *
     * @param HpsPayPlanPaymentMethod    $plan           The plan ID.
     * @param array     $feed              The feed currently being processed.
     * @param float|int $payment_amount    The recurring amount.
     * @param string    $customerKey       The Custyomer ID used by HPS.
     * @param string    $paymentMethodKey  The PaymentID used by HPS.
     * @param int       $trial_period_days The number of days the trial should last.
     *
     * @return array|HpsPayPlanSchedule The plan object.
     */
    private function create_plan(
        $plan,
        $feed,
        $payment_amount,
        $trial_period_days = 0
    ) {
        // Log the plan to be created.
        $this->log_debug(__METHOD__ . '(): Plan to be created => ' . print_r(func_get_args(), 1));
        //(HpsPayPlanService $service, $customerKey, $paymentMethodKey, $amount)
        $schedule = new HpsPayPlanSchedule();
        $schedule->scheduleIdentifier = $this->getIdentifier($feed['meta']['feedName'] . $plan->paymentMethodKey);
        $schedule->customerKey = $plan->customerKey;
        $schedule->scheduleStatus = HpsPayPlanScheduleStatus::ACTIVE;
        $schedule->paymentMethodKey = $plan->paymentMethodKey;
        $schedule->subtotalAmount = new HpsPayPlanAmount(HpsInputValidation::checkAmount($payment_amount) * 100);
        $schedule->totalAmount = new HpsPayPlanAmount(HpsInputValidation::checkAmount($payment_amount));
        $schedule->frequency = $this->validPayPlanCycle($feed);

        /*Conditional; Required if Frequency is Monthly, Bi-Monthly, Quarterly, Semi-Annually, or Semi-Monthly.*/
        if (!in_array($schedule->frequency, array(HpsPayPlanScheduleFrequency::WEEKLY,HpsPayPlanScheduleFrequency::BIWEEKLY, HpsPayPlanScheduleFrequency::ANNUALLY))) {
            $schedule->processingDateInfo = date("d", strtotime(date('d-m-Y')));
        }

        $schedule->startDate = $this->getStartDateInfo($schedule->frequency, $trial_period_days);
        $numberOfPayments = $feed['meta']['recurringTimes'] === '0'
            ? HpsPayPlanScheduleDuration::ONGOING
            : HpsPayPlanScheduleDuration::LIMITED_NUMBER;
        $schedule->duration = $numberOfPayments;
        $schedule->reprocessingCount = 1;

        if ($numberOfPayments !== HpsPayPlanScheduleDuration::ONGOING) {
            $schedule->numberOfPayments = intval($feed['meta']['recurringTimes']);

            if ($trial_period_days != null && $trial_period_days != 0) {
                $schedule->numberOfPayments = $schedule->numberOfPayments - 1;
            }
        }

        return $schedule;
    }

    /** Takes subscription billing cycle and returns a valid payplan cycle
     *
     * @used-by \GFSecureSubmit::create_plan
     * @uses    HpsPayPlanScheduleFrequency
     * @uses    GFAddOn::log_debug()
     *
     * @param array $feed
     *
     * @return null|string
     * @throws \HpsArgumentException
     */
    private function validPayPlanCycle($feed)
    {
        $this->log_debug(__METHOD__ . '(): Plan to be created => ' . print_r($feed, 1));

        $this->includeSecureSubmitSDK();
        $oClass = new ReflectionClass('HpsPayPlanScheduleFrequency');
        $array = $oClass->getConstants();
        $cycle = rgar($array, $feed['meta']['billingCycle']);
        if (null == $cycle) {
            $this->log_debug(__METHOD__ . '(): Billing Cycle Error => ' . print_r($feed, 1));
            throw new HpsArgumentException(
                'Invalid period for subscription. Please check settings and try again',
                HpsExceptionCodes::INVALID_CONFIGURATION
            );
        }
        $this->log_debug(__METHOD__ . '(): Billing Cycle Calculated => ' . $cycle);

        return $cycle;
    }

    /**
     * @param string $frequency
     * @param int $trial_period_days
     *
     * @return bool|string
     * @throws \HpsArgumentException
     */
    private function getStartDateInfo($frequency, $trial_period_days)
    {
        if ($trial_period_days*1 !== 0) {
            $period = date('mdY', strtotime('+' . ($trial_period_days * 1) . ' days'));
        } else {
            switch ($frequency) {
                case HpsPayPlanScheduleFrequency::WEEKLY:
                    $period = date('mdY', strtotime('+1 week'));
                    break;
                case HpsPayPlanScheduleFrequency::BIWEEKLY:
                    $period = date('mdY', strtotime('+2 week'));
                    break;
                case HpsPayPlanScheduleFrequency::SEMIMONTHLY:
                    $period = 'Last';
                    break;
                case HpsPayPlanScheduleFrequency::MONTHLY:
                    $period = date('mdY', strtotime('+1 month'));
                    break;
                case HpsPayPlanScheduleFrequency::QUARTERLY:
                    $period = date('mdY', strtotime('+3 month'));
                    break;
                case HpsPayPlanScheduleFrequency::SEMIANNUALLY:
                    $period = date('mdY', strtotime('+6 month'));
                    break;
                case HpsPayPlanScheduleFrequency::ANNUALLY:
                    $period = date('mdY', strtotime('+1 year'));
                    break;
                default:
                    $this->log_debug(__METHOD__ . '(): Billing Cycle Error => ' . print_r($frequency, 1));
                    throw new HpsArgumentException(
                        'Invalid period for subscription. Please check settings and try again',
                        HpsExceptionCodes::INVALID_CONFIGURATION
                    );
            }
        }

        return $period;
    }

    /**
     * @param string $id
     *
     * @return string
     */
    private function getIdentifier($id)
    {
        $identifierBase = '%s-%s' . substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 10);

        return substr(sprintf($identifierBase, date('Ymd'), $id), 0, 50);
    }

    /**
     * @param string $key
     *
     * @return \HpsServicesConfig|null
     */
    private function getHpsServicesConfig($key = null)
    {
        static $config = null;
        if (empty($config)) {
            $settings = $this->get_plugin_settings();
            
            $paymentType = (string)trim($this->get_setting("payment_type", '', $settings));
            
            $is_sandbox_mode = false;
            if($paymentType === 'transit'){
                $is_sandbox_mode = (string)trim($this->get_setting('transit_sandbox_mode', '', $settings));
                $config = new TransitConfig();
                $config->merchantId = (string)trim($this->get_setting('merchant_id', '', $settings));
                $config->username = (string)trim($this->get_setting('username', '', $settings));
                $config->password = (string)trim($this->get_setting('password', '', $settings));
                $config->deviceId = (string)trim($this->get_setting('device_id', '', $settings));
                $config->transactionKey = (string)trim($this->get_setting('transaction_key', '', $settings));
                $config->developerId = (string)trim($this->get_setting('developer_id', '', $settings));                
                $config->acceptorConfig = new AcceptorConfig();                    
            } else {
                $config = new PorticoConfig();
                $config->secretApiKey = $key;
                $is_sandbox_mode = (string)trim($this->get_setting('hps_sandbox_mode', '', $settings));
            }
            
            $config->environment = ($is_sandbox_mode === 'yes') ? 'TEST' : 'PRODUCTION';            
            ServicesContainer::configureService($config);
        }
        return $config;
    }
    
    private function setTransItJsScriptsValue($publicKey = null){
        $config = $this->getHpsServicesConfig(null);
        $gatewayConfig = [
            'gatewayProvider' => strtolower($config->gatewayProvider),
            'env' => $config->environment
        ];
        if($gatewayConfig['gatewayProvider'] === 'transit'){
            //create new manifest for tokenization and return config details
            $provider = ServicesContainer::instance()->getClient('default');
            $manifest = $provider->createManifest();
            
            $gatewayConfig['deviceId'] = $config->deviceId;
            $gatewayConfig['manifest'] = $manifest;
        } else {
            $gatewayConfig['publicKey'] = $publicKey;
        }
        
        return $gatewayConfig;
    }

    /**
     * @param string $key
     *
     * @return \HpsPayPlanService|null
     */
    private function getPayPlanService($key)
    {
        static $service = null;
        if (empty($service)) {
            $service = new HpsPayPlanService($this->getHpsServicesConfig($key));
        }

        return $service;
    }

    /**
     * @return array
     */
    /** @noinspection PhpMissingParentCallCommonInspection */
    public function supported_billing_intervals()
    {
        //authorize.net does not use years or weeks, override framework function

        $this->includeSecureSubmitSDK();
        $oClass = new ReflectionClass('HpsPayPlanScheduleFrequency');
        $array = $oClass->getConstants();
        $billing_cycles = array();
        foreach ($array as $const => $value) {
            $billing_cycles[$const] = array('label' => esc_html__($value, 'gravityforms'), 'min' => 1, 'max' => 1);
        }

        return $billing_cycles;
    }
    /**
     * If custom meta data has been configured on the feed retrieve the mapped field values.
     *
     * @since   Unknown
     * @access  public
     *
     * @used-by GFSecureSubmit::authorize_product()
     * @used-by GFSecureSubmit::capture()
     * @used-by GFSecureSubmit::process_subscription()
     * @used-by GFSecureSubmit::subscribe()
     * @uses    GFAddOn::get_field_value()
     *
     * @param array $feed  The feed object currently being processed.
     * @param array $entry The entry object currently being processed.
     * @param array $form  The form object currently being processed.
     *
     * @return array The HPS meta data.
     */
    private function get_hps_meta_data($feed, $entry, $form)
    {

        // Initialize metadata array.
        $metadata = array();

        // Find feed metadata.
        $custom_meta = rgars($feed, 'meta/metaData');

        if (is_array($custom_meta)) {
            // Loop through custom meta and add to metadata for HPS.
            foreach ($custom_meta as $meta) {
                // If custom key or value are empty, skip meta.
                if (empty($meta['custom_key']) || empty($meta['value'])) {
                    continue;
                }

                // Make the key available to the gform_HPS_field_value filter.
                $this->_current_meta_key = $meta['custom_key'];

                // Get field value for meta key.
                $field_value = $this->get_field_value($form, $entry, $meta['value']);

                if (!empty($field_value)) {
                    // Trim to 500 characters, per HPS requirement.
                    $field_value = substr($field_value, 0, 500);

                    // Add to metadata array.
                    $metadata[$meta['custom_key']] = $field_value;
                }
            }

            // Clear the key in case get_field_value() and gform_HPS_field_value are used elsewhere.
            $this->_current_meta_key = '';
        }

        return $metadata;
    }

    protected function normalizeCountry($country, $isRecurring = false)
    {
        switch (strtolower($country)) {
            case null:
            case '':
            case 'us':
            case 'usa':
            case 'united states':
            case 'united states of america':
                return 'USA';
            case 'ca':
            case 'can':
            case 'cana':
            case 'cgg':
            case 'canada':
                return 'CAN';
            default:
                if ($isRecurring) {
                    throw new Exception(sprintf('Country "%s" is currently not supported', $country));
                }
                return null;
        }
    }

    protected function normalizeState($state)
    {
        $na_state_abbreviations  = array(
            // United States
            'ALABAMA' => 'AL',
            'ALASKA' => 'AK',
            'ARIZONA' => 'AZ',
            'ARKANSAS' => 'AR',
            'CALIFORNIA' => 'CA',
            'COLORADO' => 'CO',
            'CONNECTICUT' => 'CT',
            'DELAWARE' => 'DE',
            'DISTRICT OF COLUMBIA' => 'DC',
            'FLORIDA' => 'FL',
            'GEORGIA' => 'GA',
            'HAWAII' => 'HI',
            'IDAHO' => 'ID',
            'ILLINOIS' => 'IL',
            'INDIANA' => 'IN',
            'IOWA' => 'IA',
            'KANSAS' => 'KS',
            'KENTUCKY' => 'KY',
            'LOUISIANA' => 'LA',
            'MAINE' => 'ME',
            'MARYLAND' => 'MD',
            'MASSACHUSETTS' => 'MA',
            'MICHIGAN' => 'MI',
            'MINNESOTA' => 'MN',
            'MISSISSIPPI' => 'MS',
            'MISSOURI' => 'MO',
            'MONTANA' => 'MT',
            'NEBRASKA' => 'NE',
            'NEVADA' => 'NV',
            'NEW HAMPSHIRE' => 'NH',
            'NEW JERSEY' => 'NJ',
            'NEW MEXICO' => 'NM',
            'NEW YORK' => 'NY',
            'NORTH CAROLINA' => 'NC',
            'NORTH DAKOTA' => 'ND',
            'OHIO' => 'OH',
            'OKLAHOMA' => 'OK',
            'OREGON' => 'OR',
            'PENNSYLVANIA' => 'PA',
            'RHODE ISLAND' => 'RI',
            'SOUTH CAROLINA' => 'SC',
            'SOUTH DAKOTA' => 'SD',
            'TENNESSEE' => 'TN',
            'TEXAS' => 'TX',
            'UTAH' => 'UT',
            'VERMONT' => 'VT',
            'VIRGINIA' => 'VA',
            'WASHINGTON' => 'WA',
            'WEST VIRGINIA' => 'WV',
            'WISCONSIN' => 'WI',
            'WYOMING' => 'WY',
            'ARMED FORCES AMERICAS' => 'AA',
            'ARMED FORCES EUROPE' => 'AE',
            'ARMED FORCES PACIFIC' => 'AP',
            // Canada
            'ALBERTA' => 'AB',
            'BRITISH COLUMBIA' => 'BC',
            'MANITOBA' => 'MB',
            'NEW BRUNSWICK' => 'NB',
            'NEWFOUNDLAND AND LABRADOR' => 'NL',
            'NORTHWEST TERRITORIES' => 'NT',
            'NOVA SCOTIA' => 'NS',
            'NUNAVUT' => 'NU',
            'ONTARIO' => 'ON',
            'PRINCE EDWARD ISLAND' => 'PE',
            'QUEBEC' => 'QC',
            'SASKATCHEWAN' => 'SK',
            'YUKON' => 'YT',
        );

        $state_uc = strtoupper($state);

        if (!empty($na_state_abbreviations[$state_uc])) {
            return $na_state_abbreviations[$state_uc];
        }

        if (in_array($state_uc, $na_state_abbreviations, true)) {
            return $state_uc;
        }

        throw new Exception(sprintf('State/Province "%s" is currently not supported', $state));
    }
}
