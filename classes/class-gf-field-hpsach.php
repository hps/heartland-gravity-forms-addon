<?php

if (!class_exists('GFForms')) {
    die();
}

/**
 * Class GF_Field_HPSACH
 */
class GF_Field_HPSach extends GF_Field {
    /**ACH element names*/
    const HPS_ACH_CHECK_HOLDER_FIELD_NAME     = 'hps_ach_check_holder';
    const HPS_ACH_ACCOUNT_FIELD_NAME          = 'hps_ach_account';
    const HPS_ACH_ROUTING_FIELD_NAME          = 'hps_ach_routing';
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
        $this->failed_validation = false;
        if (empty($account_name) ) {
            $this->failed_validation = true;
            $this->validation_message = empty($this->errorMessage) ? esc_html__('Please enter your account holder name. All feilds required.',
                'gravityforms') : $this->errorMessage;
        } elseif (empty($account_number) ) {
            $this->failed_validation = true;
            $this->validation_message = empty($this->errorMessage) ? esc_html__('Please enter your account number. All feilds required.',
                'gravityforms') : $this->errorMessage;
        } elseif (empty($routing_number) ) {
            $this->failed_validation = true;
            $this->validation_message = empty($this->errorMessage) ? esc_html__('Please enter your rounting number. All feilds required.',
                'gravityforms') : $this->errorMessage;
        } elseif (empty($account_type) ) {
            $this->failed_validation = true;
            $this->validation_message = empty($this->errorMessage) ? esc_html__('Please select an account type. All feilds required.',
                'gravityforms') : $this->errorMessage;
        } elseif (empty($check_type)) {
            $this->failed_validation = true;
            $this->validation_message = empty($this->errorMessage) ? esc_html__('Please select the type of checkgit commit. All feilds required.',
                'gravityforms') : $this->errorMessage;
        } else {

            if (!filter_var($account_name, FILTER_SANITIZE_STRING)) {
                $this->failed_validation = true;
                $this->validation_message = esc_html__("Please enter your account holder name and avoid special characters.",
                    'gravityforms');
            } elseif (!filter_var($account_number, FILTER_VALIDATE_INT, array('min_range' => 1000, 'default' => 0))) {
                $this->failed_validation = true;
                $this->validation_message = esc_html__('Invalid account number.', 'gravityforms');
            } elseif (!filter_var($routing_number, FILTER_VALIDATE_INT,
                array('min_range' => 99999999, 'max_range' => 999999999, 'default' => 0))
            ) {
                $this->failed_validation = true;
                $this->validation_message = esc_html__('Invalid routing number must be exactly 9 digits.',
                    'gravityforms');
            } elseif ($account_type !== '2' && $account_type !== '1') {
                $this->failed_validation = true;
                $this->validation_message = esc_html__('Please select the type of account.', 'gravityforms');
            } elseif ($check_type !== '2' && $check_type !== '1') {
                $this->failed_validation = true;
                $this->validation_message = esc_html__('Please select the check type.', 'gravityforms');
            }
        }
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


        $autocomplete = RGFormsModel::is_html5_enabled() ? "autocomplete='off'" : '';


        $action = !($is_entry_detail || $is_form_editor) ? "gformMatchCard(\"{$field_id}_1\");" : '';


        $onlyDigits = !is_admin() ? " title='" . esc_attr__('Only digits are allowed', 'gravityforms') . "'" : '';


//customer name
        $account_name_field_input = GFFormsModel::get_input($this, GF_Field_HPSach::HPS_ACH_CHECK_HOLDER_FIELD_NAME);
        $account_name_label = rgar($account_name_field_input, 'customLabel') != '' ? $account_name_field_input['customLabel'] : esc_html__('Account holder Name', 'gravityforms');
        $account_name_label = gf_apply_filters(array('gform_card_name', $form_id), $account_name_label, $form_id);

//account number
        $account_number_field_input = GFFormsModel::get_input($this, GF_Field_HPSach::HPS_ACH_ACCOUNT_FIELD_NAME);
        $account_number_label = rgar($account_number_field_input, 'customLabel') != '' ? $account_number_field_input['customLabel'] : esc_html__('Account Number', 'gravityforms');
        $account_number_label = gf_apply_filters(array('gform_card_number', $form_id), $account_number_label, $form_id);

//routing number
        $routing_number_field_input = GFFormsModel::get_input($this, GF_Field_HPSach::HPS_ACH_ROUTING_FIELD_NAME);
        $routing_number_label = rgar($routing_number_field_input, 'customLabel') != '' ? $routing_number_field_input['customLabel'] : esc_html__('Routing Number', 'gravityforms');
        $routing_number_label = gf_apply_filters(array('gform_card_number', $form_id), $routing_number_label, $form_id);

//account type
        $account_type_input = GFFormsModel::get_input($this, GF_Field_HPSach::HPS_ACH_TYPE_FIELD_NAME);
        $account_type_label = rgar($account_type_input, 'customLabel') != '' ? $account_type_input['customLabel'] : esc_html__('Account Type', 'gravityforms');
        $account_type_label = gf_apply_filters(array('gform_card_expiration', $form_id), $account_type_label, $form_id);

//check type
        $check_type_input = GFFormsModel::get_input($this, GF_Field_HPSach::HPS_ACH_CHECK_FIELD_NAME);
        $check_type_label = rgar($check_type_input, 'customLabel') != '' ? $check_type_input['customLabel'] : esc_html__('Check Type', 'gravityforms');
        $check_type_label = gf_apply_filters(array('gform_card_expiration', $form_id), $check_type_label, $form_id);


        if (!isset($class_suffix)) {$class_suffix = '';}
        if (!isset($field_id)) {$field_id = '';}
        if (!isset($card_icons)) {$card_icons = '';}
        if (!isset($sub_label_class_attribute)) {$sub_label_class_attribute = '';}
        if (!isset($account_name_label)) {$account_name_label = '';}
        if (!isset($disabled_text)) {$disabled_text = '';}
        if (!isset($account_number_label)) {$account_number_label = '';}
        if (!isset($routing_number_label)) {$routing_number_label = '';}
        if (!isset($account_type_label)) {$account_type_label = '';}
        if (!isset($check_type_label)) {$check_type_label = '';}

        $account_name_value = rgpost(GF_Field_HPSach::HPS_ACH_CHECK_HOLDER_FIELD_NAME);
        $account_type_value = rgpost(GF_Field_HPSach::HPS_ACH_TYPE_FIELD_NAME);
        $check_type_value = rgpost(GF_Field_HPSach::HPS_ACH_CHECK_FIELD_NAME);
        ob_start();
        include dirname(__FILE__) . "/../templates/ach-payment-fields.php";
        $ss_ach_output = ob_get_clean();

        return $ss_ach_output;
    }

    /**
     * @return string
     */
    public function get_field_label_class() {
        return 'gfield_label gfield_label_before_complex';
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
            $account_number = trim(rgget(GF_Field_HPSach::HPS_ACH_ACCOUNT_FIELD_NAME, $value));
            $routing_number = trim(rgget(GF_Field_HPSach::HPS_ACH_ROUTING_FIELD_NAME, $value));
            $account_type = trim(rgget(GF_Field_HPSach::HPS_ACH_TYPE_FIELD_NAME, $value));
            $check_type = trim(rgget(GF_Field_HPSach::HPS_ACH_CHECK_FIELD_NAME, $value));
            $separator = $format == 'html' ? '<br/>' : "\n";

            return empty($account_number) ? '' : $check_type . $separator . $account_number;
        }
        else {
            return '';
        }
    }

}

GF_Fields::register(new GF_Field_HPSach());
