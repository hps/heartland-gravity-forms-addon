<?php

if (!class_exists('GFForms')) {
    die();
}

/**
 * Class GF_Field_HPSACH
 */
class GF_Field_HPSach extends GF_Field {
    /**ACH element names*/
    const HPS_ACH_CHECK_HOLDER_FIELD_NAME     = 'ach_check_holder';
    const HPS_ACH_ACCOUNT_FIELD_NAME          = 'hps_account';
    const HPS_ACH_ROUTING_FIELD_NAME          = 'hps_routing';
    const HPS_ACH_TYPE_FIELD_NAME             = 'hps_ach_type';
    const HPS_ACH_CHECK_FIELD_NAME            = 'hps_ach_check';
    /**
     * @var string
     */
    public $type = 'hpsACH';

    /**
     * @return string
     */
    public function get_form_editor_field_title() {

        return esc_attr__('Secure ACH', 'gravityforms');
    }

    /**
     * @return array
     */
    function get_form_editor_field_settings() {
        return array(
            'force_ssl_field_setting',
            'error_message_setting',
            'label_setting',
            'admin_label_setting',
            'rules_setting',
            'css_class_setting',
        );
    }

    /**
     * @return array
     */
    public function get_form_editor_button() {
        return array(); // this button is conditionally added in the form detail page
    }

    /**
     * @param array|string $value
     * @param array        $form
     */
    public function validate($value, $form) {
        $account_name = rgpost(GF_Field_HPSach::HPS_ACH_CHECK_HOLDER_FIELD_NAME);
        $account_number = rgpost(GF_Field_HPSach::HPS_ACH_ACCOUNT_FIELD_NAME);
        $routing_number = rgpost(GF_Field_HPSach::HPS_ACH_ROUTING_FIELD_NAME);
        $account_type = rgpost(GF_Field_HPSach::HPS_ACH_TYPE_FIELD_NAME);
        $check_type = rgpost(GF_Field_HPSach::HPS_ACH_CHECK_FIELD_NAME);

        if ($this->isRequired && (empty($account_name) || empty($account_number) || empty($routing_number) || empty($account_type))) {
            $this->failed_validation = true;
            $this->validation_message = empty($this->errorMessage) ? esc_html__('Please enter your account information.', 'gravityforms') : $this->errorMessage;
        }
        elseif (!empty($card_number)) {
            $card_type = GFCommon::get_card_type($card_number);

            if (empty($security_code)) {
                $this->failed_validation = true;
                $this->validation_message = esc_html__("Please enter your card's security code.", 'gravityforms');
            }
            elseif (!$card_type) {
                $this->failed_validation = true;
                $this->validation_message = esc_html__('Invalid credit card number.', 'gravityforms');
            }
            elseif (!$this->is_card_supported($card_type['slug'])) {
                $this->failed_validation = true;
                $this->validation_message = $card_type['name'] . ' ' . esc_html__('is not supported. Please enter one of the supported credit cards.', 'gravityforms');
            }
        }
    }

    /**
     * @param $card_slug
     *
     * @return bool
     */
    public function is_card_supported($card_slug) {
        $supported_cards = $this->creditCards;
        $default_cards = array('amex', 'discover', 'mastercard', 'visa');

        if (!empty($supported_cards) && in_array($card_slug, $supported_cards)) {
            return true;
        }
        elseif (empty($supported_cards) && in_array($card_slug, $default_cards)) {
            return true;
        }

        return false;

    }


    /**
     * @param array  $form
     * @param string $value
     * @param null   $entry
     *
     * @return string
     */
    public function get_field_input($form, $value = '', $entry = null) {
        $is_entry_detail = $this->is_entry_detail();
        $is_form_editor = $this->is_form_editor();

        $form_id = $form['id'];
        $id = intval($this->id);
        $field_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";
        $form_id = ($is_entry_detail || $is_form_editor) && empty($form_id) ? rgget('id') : $form_id;

        $disabled_text = $is_form_editor ? "disabled='disabled'" : '';
        $class_suffix = $is_entry_detail ? '_admin' : '';


        $form_sub_label_placement = rgar($form, 'subLabelPlacement');
        $field_sub_label_placement = $this->subLabelPlacement;
        $is_sub_label_above = $field_sub_label_placement == 'above' || (empty($field_sub_label_placement) && $form_sub_label_placement == 'above');
        $sub_label_class_attribute = $field_sub_label_placement == 'hidden_label' ? "class='hidden_sub_label screen-reader-text'" : '';


        $account_name = '';
        $account_number = '';
        $routing_number = '';
        $account_type = '';
        $autocomplete = RGFormsModel::is_html5_enabled() ? "autocomplete='off'" : '';

        if (is_array($value)) {
            $account_number = esc_attr(rgget(GF_Field_HPSach::HPS_ACH_ACCOUNT_FIELD_NAME, $value));
            $account_name = esc_attr(rgget(GF_Field_HPSach::HPS_ACH_CHECK_HOLDER_FIELD_NAME, $value));
            $account_type = rgget(GF_Field_HPSach::HPS_ACH_TYPE_FIELD_NAME, $value);

            if (!empty($expiration_date) && !is_array($expiration_date)) {
                $expiration_date = explode('/', $expiration_date);
            }

            if (is_array($expiration_date) && count($expiration_date) == 2) {
                $expiration_month = $expiration_date[0];
                $expiration_year = $expiration_date[1];
            }

            $routing_number = esc_attr(rgget('hps_routing', $value));
        }

        $action = !($is_entry_detail || $is_form_editor) ? "gformMatchCard(\"{$field_id}_1\");" : '';

        $onchange = "onchange='{$action}'";
        $onkeyup = "onkeyup='{$action}'";

        $card_icons = '';
        $cards = GFCommon::get_card_types();
        $card_style = $this->creditCardStyle ? $this->creditCardStyle : 'style1';


        $payment_methods = apply_filters('gform_payment_methods', array(), $this, $form_id);
        $payment_options = '';
        if (is_array($payment_methods)) {
            foreach ($payment_methods as $payment_method) {
                $checked = rgpost('gform_payment_method') == $payment_method['key'] ? "checked='checked'" : '';
                $payment_options .= "<div class='gform_payment_option gform_payment_{$payment_method['key']}'><input type='radio' name='gform_payment_method' value='{$payment_method['key']}' id='gform_payment_method_{$payment_method['key']}' onclick='gformToggleCreditCard();' onkeypress='gformToggleCreditCard();' {$checked}/> {$payment_method['label']}</div>";
            }
        }
        $checked = rgpost('gform_payment_method') == 'creditcard' || rgempty('gform_payment_method') ? "checked='checked'" : '';
        $card_radio_button = empty($payment_options) ? '' : "<input type='radio' name='gform_payment_method' id='gform_payment_method_creditcard' value='creditcard' onclick='gformToggleCreditCard();' onkeypress='gformToggleCreditCard();' {$checked}/>";
        $card_icons = "{$payment_options}<div class='gform_card_icon_container gform_card_icon_{$card_style}'>{$card_radio_button}{$card_icons}</div>";


        /*
                    ach_check_holder
                hps_account
                hps_routing
                hps_ach_type
                hps_ach_check*/

//customer name
        $account_name_field_input = GFFormsModel::get_input($this, GF_Field_HPSach::HPS_ACH_CHECK_HOLDER_FIELD_NAME);
        $account_name_label = rgar($account_name_field_input, 'customLabel') != '' ? $account_name_field_input['customLabel'] : esc_html__('Account holder Name', 'gravityforms');
        $account_name_label = gf_apply_filters(array('gform_card_name', $form_id), $account_name_label, $form_id);

//account number
        $account_number_field_input = GFFormsModel::get_input($this, GF_Field_HPSach::HPS_ACH_ACCOUNT_FIELD_NAME);
        $html5_output = !is_admin() && GFFormsModel::is_html5_enabled() ? "pattern='[0-9]*' title='" . esc_attr__('Only digits are allowed', 'gravityforms') . "'" : '';
        $account_number_label = rgar($account_number_field_input, 'customLabel') != '' ? $account_number_field_input['customLabel'] : esc_html__('Account Number', 'gravityforms');
        $account_number_label = gf_apply_filters(array('gform_card_number', $form_id), $account_number_label, $form_id);

//routing number
        $routing_number_field_input = GFFormsModel::get_input($this, GF_Field_HPSach::HPS_ACH_ROUTING_FIELD_NAME);
        $html5_output = !is_admin() && GFFormsModel::is_html5_enabled() ? "pattern='[0-9]*' title='" . esc_attr__('Only digits are allowed', 'gravityforms') . "'" : '';
        $routing_number_label = rgar($routing_number_field_input, 'customLabel') != '' ? $routing_number_field_input['customLabel'] : esc_html__('Routing Number', 'gravityforms');
        $routing_number_label = gf_apply_filters(array('gform_card_number', $form_id), $routing_number_label, $form_id);

//account type
        $account_type_input = GFFormsModel::get_input($this, GF_Field_HPSach::HPS_ACH_TYPE_FIELD_NAME);
        $html5_output = !is_admin() && GFFormsModel::is_html5_enabled() ? "pattern='[0-9]*' title='" . esc_attr__('', 'gravityforms') . "'" : '';
        $account_type_label = rgar($account_type_input, 'customLabel') != '' ? $account_type_input['customLabel'] : esc_html__('Account Type', 'gravityforms');
        $account_type_label = gf_apply_filters(array('gform_card_expiration', $form_id), $account_type_label, $form_id);

//check type
        $check_type_input = GFFormsModel::get_input($this, GF_Field_HPSach::HPS_ACH_CHECK_FIELD_NAME);
        $html5_output = !is_admin() && GFFormsModel::is_html5_enabled() ? "pattern='[0-9]*' title='" . esc_attr__('', 'gravityforms') . "'" : '';
        $check_type_label = rgar($check_type_input, 'customLabel') != '' ? $check_type_input['customLabel'] : esc_html__('Check Type', 'gravityforms');
        $check_type_label = gf_apply_filters(array('gform_card_expiration', $form_id), $check_type_label, $form_id);


        if (!isset($class_suffix)) {$class_suffix = '';}
        if (!isset($field_id)) {$field_id = '';}
        if (!isset($card_icons)) {$card_icons = '';}
        if (!isset($sub_label_class_attribute)) {$sub_label_class_attribute = '';}
        if (!isset($account_name_label)) {$account_name_label = '';}
        if (!isset($account_name)) {$account_name = '';}
        if (!isset($disabled_text)) {$disabled_text = '';}
        if (!isset($account_number_label)) {$account_number_label = '';}
        if (!isset($routing_number_label)) {$routing_number_label = '';}
        if (!isset($account_type_label)) {$account_type_label = '';}
        if (!isset($check_type_label)) {$check_type_label = '';}
        ob_start();
        include dirname(__FILE__) . "/../templates/ach-payment-fields.php";
        $ss_cc_output = ob_get_clean();

        return $ss_cc_output;
    }

    /**
     * @return string
     */
    public function get_field_label_class() {
        return 'gfield_label gfield_label_before_complex';
    }

    /**
     * @param $selected_month
     * @param $placeholder
     *
     * @return string
     */
    private function get_expiration_months($selected_month, $placeholder) {
        if (empty($placeholder)) {
            $placeholder = esc_html__('Month', 'gravityforms');
        }
        $str = "<option value=''>{$placeholder}</option>";
        for ($i = 1; $i < 13; $i++) {
            $selected = intval($selected_month) == $i ? "selected='selected'" : '';
            $month = str_pad($i, 2, '0', STR_PAD_LEFT);
            $str .= "<option value='{$i}' {$selected}>{$month}</option>";
        }

        return $str;
    }

    /**
     * @param $selected_year
     * @param $placeholder
     *
     * @return string
     */
    private function get_expiration_years($selected_year, $placeholder) {
        if (empty($placeholder)) {
            $placeholder = esc_html__('Year', 'gravityforms');
        }
        $str = "<option value=''>{$placeholder}</option>";
        $year = intval(date('Y'));
        for ($i = $year; $i < ($year + 20); $i++) {
            $selected = intval($selected_year) == $i ? "selected='selected'" : '';
            $str .= "<option value='{$i}' {$selected}>{$i}</option>";
        }

        return $str;
    }

    /**
     * @param array|string $value
     * @param string       $currency
     * @param bool         $use_text
     * @param string       $format
     * @param string       $media
     *
     * @return string
     */
    public function get_value_entry_detail($value, $currency = '', $use_text = false, $format = 'html', $media = 'screen') {

        if (is_array($value)) {
            /*
                        ach_check_holder
                    hps_account
                    hps_routing
                    hps_ach_type
                    hps_ach_check*/
            $account_number = trim(rgget('ach_check_holder', $value));
            $routing_number = trim(rgget('hps_routing', $value));
            $account_type = trim(rgget(GF_Field_HPSach::HPS_ACH_TYPE_FIELD_NAME, $value));
            $check_type = trim(rgget(GF_Field_HPSach::HPS_ACH_CHECK_FIELD_NAME, $value));
            $separator = $format == 'html' ? '<br/>' : "\n";

            return empty($account_number) ? '' : $check_type . $separator . $account_number;
        }
        else {
            return '';
        }
    }

    /**
     * @param array $form
     *
     * @return string
     */
    public function get_form_inline_script_on_page_render($form) {

        $field_id = "input_{$form['id']}_{$this->id}";

        if ($this->forceSSL && !GFCommon::is_ssl() && !GFCommon::is_preview()) {
            $script = "document.location.href='" . esc_js(RGFormsModel::get_current_page_url(true)) . "';";
        }
        else {
            $script = ""; // "jQuery(document).ready(function(){ { gformMatchCard(\"{$field_id}_1\"); } } );";
        }

        $card_rules = $this->get_credit_card_rules();
        $script = "if(!window['gf_cc_rules']){window['gf_cc_rules'] = new Array(); } window['gf_cc_rules'] = " . GFCommon::json_encode($card_rules) . "; $script";

        return $script;
    }

    /**
     * @return array
     */
    public function get_credit_card_rules() {

        $cards = GFCommon::get_card_types();
        //$supported_cards = //TODO: Only include enabled cards
        $rules = array();

        foreach ($cards as $card) {
            $prefixes = explode(',', $card['prefixes']);
            foreach ($prefixes as $prefix) {
                $rules[ $card['slug'] ][] = $prefix;
            }
        }

        return $rules;
    }


    /**
     * @param string $value
     * @param array  $form
     * @param string $input_name
     * @param int    $lead_id
     * @param array  $lead
     *
     * @return string
     */
    public function get_value_save_entry($value, $form, $input_name, $lead_id, $lead) {

        //saving last 4 digits of credit card
        list($input_token, $field_id_token, $input_id) = rgexplode('_', $input_name, 3);
        if ($input_id == '1') {
            $value = str_replace(' ', '', $value);
            $card_number_length = strlen($value);
            $value = substr($value, -4, 4);
            $value = str_pad($value, $card_number_length, 'X', STR_PAD_LEFT);
        }
        elseif ($input_id == '4') {

            $value = rgpost("input_{$field_id_token}_4");

            if (!$value) {
                $card_number = rgpost("input_{$field_id_token}_1");
                $card_type = GFCommon::get_card_type($card_number);
                $value = $card_type ? $card_type['name'] : '';
            }
        }
        else {
            $value = '';
        }

        return $this->sanitize_entry_value($value, $form['id']);
    }

    /**
     * GF1.8 and earlier used 5 inputs (1 input for the expiration date); GF1.9 changed to 6 inputs (the expiration
     * month and year now separate); upgrade those fields still using the older configuration.
     */
    public function maybe_upgrade_inputs() {
        $inputs = $this->inputs;
        $exp_input = $inputs[1];
        $exp_id = $this->id . '.2';

        if (count($inputs) == 5 && $exp_input['id'] == $exp_id) {
            $new_inputs = array(
                array(
                    'id'           => $exp_id . '_month',
                    'label'        => esc_html__('Expiration Month', 'gravityforms'),
                    'defaultLabel' => $exp_input['label'],
                ),
                array(
                    'id'    => $exp_id . '_year',
                    'label' => esc_html__('Expiration Year', 'gravityforms'),
                ),
            );

            array_splice($inputs, 1, 1, $new_inputs);
            $this->inputs = $inputs;
        }
    }
}

GF_Fields::register(new GF_Field_HPSach());
