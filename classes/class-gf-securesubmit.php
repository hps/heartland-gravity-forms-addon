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

    public static function get_instance()
    {
        if (self::$_instance == null) {
            self::$_instance = new GFSecureSubmit();
        }

        return self::$_instance;
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

    public function scripts()
    {
        $scripts = array(
            array(
                'handle'  => 'securesubmit.js',
                'src'       => $this->get_base_url() . '/../assets/js/secure.submit-1.0.2.js',
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

        $cc_field = $this->get_credit_card_field($form);

        $args = array(
            'apiKey'     => $this->getPublicApiKey(),
            'formId'     => $form['id'],
            'ccFieldId'  => $cc_field['id'],
            'ccPage'     => rgar($cc_field, 'pageNumber'),
            'isAjax'     => $is_ajax,
            'settings' => json_encode($this->get_plugin_settings()),
            'send_email' => $this->getSendEmail(),
            'authorize_or_charge' => $this->getAuthorizeOrCharge(),
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
        return array('is_authorized' => true);
    }

    public function capture($auth, $feed, $submission_data, $form, $entry)
    {
        $config = new HpsServicesConfig();
        $config->secretApiKey = $this->getSecretApiKey();
        $config->developerId = '002914';
        $config->versionNumber = '1916';

        $service = new HpsCreditService($config);

        $cardHolder = $this->buildCardHolder($feed, $submission_data, $entry);

        try {
            $response = $this->getSecureSubmitJsResponse();
            $token = new HpsTokenData();
            $token->tokenValue = $response->token_value;

            $transaction = null;
            if ($this->getAuthorizeOrCharge() == 'authorize') {
                $transaction = $service->authorize($submission_data['payment_amount'], GFCommon::get_currency(), $token, $cardHolder);
            } else {
                $transaction = $service->charge($submission_data['payment_amount'], GFCommon::get_currency(), $token, $cardHolder);
            }

            if ($this->getSendEmail() == 'yes') {
                $this->sendEmail($form, $entry, $transaction, $cardHolder);
            }

            $payment = array(
                'is_success'     => true,
                'transaction_id' => $transaction->transactionId,
                'amount'         => $submission_data['payment_amount'],
                'payment_method' => $response->card_type,
            );
        } catch (SecureSubmit_Error $e) {
            $payment = array(
                'is_success'    => false,
                'error_message' => $e->getMessage()
            );
        }

        return $payment;
    }

    // Helper functions

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
        $name = explode(' ', $submission_data['card_name']);
        $firstName = $name[0];
        unset($name[0]);
        $lastName = implode(' ', $name);

        $address = new HpsAddress();
        $address->address1 = $entry[$feed['meta']['billingInformation_address']];
        $address->address2 = $entry[$feed['meta']['billingInformation_address2']];
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
        $_POST['input_' . $cc_field['id'] . '_1'] = 'XXXXXXXXXXXX' . $response->last_four;
        $_POST['input_' . $cc_field['id'] . '_4'] = $response->card_type;
    }

    public function includeSecureSubmitSDK()
    {
        require_once $this->get_base_path() . '/../gravityforms-securesubmit/classes/includes/Hps.php';
        do_action('gform_securesubmit_post_include_api');
    }

    public function getSecretApiKey()
    {
        return $this->getApiKey('secret');
    }

    public function getPublicApiKey()
    {
        return $this->getApiKey('public');
    }

    public function getApiKey($type = 'secret')
    {
        // user needs admin privileges for this
        $api_key = $this->getQueryStringApiKey($type);
        if ($api_key && current_user_can('update_core')) {
            return $api_key;
        }

        $settings = $this->get_plugin_settings();
        return (string)$this->get_setting("{$type}_api_key", '', $settings);
    }

    public function getQueryStringApiKey($type = 'secret')
    {
        return rgget($type);
    }

    public function getAuthorizeOrCharge()
    {
        $settings = $this->get_plugin_settings();
        return (string)$this->get_setting('authorize_or_charge', 'charge', $settings);
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
}
