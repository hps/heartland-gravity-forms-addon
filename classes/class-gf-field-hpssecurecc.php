<?php

if ( ! class_exists( 'GFForms' ) ) {
    die();
}

/**
 * Class GF_Field_HPSCreditCard
 */
class GF_Field_HPSCreditCard extends GF_Field
{

    /**
     *@var string
     */
    public $type = 'hpscreditcard';
    protected $_slug = 'gravityforms-securesubmit';


    /**
     * Returns the class names of the settings which should be available on the field in the form editor.
     *
     * @return array
     */
    function get_form_editor_field_settings() {
        return array(
            'label_setting',
        );/*
        return array(
            'credit_card_style_setting',
            'error_message_setting',
            'label_setting',
            'label_placement_setting',
            'sub_labels_setting',
            'sub_label_placement_setting',
            'label_placement_setting',
            'rules_setting',
            'description_setting',
            'css_class_setting',
            'credit_card_setting',
            'input_placeholders_setting',
        );*/
    }

    /**
     * Retrieve the field value on submission.
     *
     * @param array $field_values The dynamic population parameter names with their corresponding values to be populated.
     * @param bool|true $get_from_post_global_var Whether to get the value from the $_POST array as opposed to $field_values.
     *
     * @return array|string
     */
    public function get_value_submission( $field_values, $get_from_post_global_var = true ) {

        if ( $get_from_post_global_var ) {
            $value[ $this->id . '.1' ] = $this->get_input_value_submission( 'input_' . $this->id . '_1', rgar( $this->inputs[0], 'name' ), $field_values, true );
            $value[ $this->id . '.2' ] = $this->get_input_value_submission( 'input_' . $this->id . '_2', rgar( $this->inputs[1], 'name' ), $field_values, true );
            $value[ $this->id . '.3' ] = $this->get_input_value_submission( 'input_' . $this->id . '_3', rgar( $this->inputs[3], 'name' ), $field_values, true );
            $value[ $this->id . '.4' ] = $this->get_input_value_submission( 'input_' . $this->id . '_4', rgar( $this->inputs[4], 'name' ), $field_values, true );
            $value[ $this->id . '.5' ] = $this->get_input_value_submission( 'input_' . $this->id . '_5', rgar( $this->inputs[5], 'name' ), $field_values, true );
        } else {
            $value = $this->get_input_value_submission( 'input_' . $this->id, $this->inputName, $field_values, $get_from_post_global_var );
        }

        return $value;
    }
    /**
     * Validates a token was retreived and if not displays a meaningful error
     *
     * Return the result (bool) by setting $this->failed_validation.
     * Return the validation message (string) by setting $this->validation_message.
     *
     * @param string|array $value The field value from get_value_submission().
     * @param array $form The Form Object currently being processed.
     */
    public function validate($value, $form ) {

        /** @var array $ssTokenData
         * Possible structures
         * Success
        $ssTokenData = array(
            'token' => array(
                'token_value' => '',
                'token_type' => '',
                'token_expire' => '',
                'card' => array('number' => '',),
            ),
        );
         * Failure
        $ssTokenData = array(
            'error' => array(
                'type' => '',
                'message' => '',
                'code' => '',
                'param' => '',
            ),
        );
        */
        $ssTokenData = json_decode(rgpost('securesubmit_response'), true);
        $this->failed_validation = false;
        if (is_array(rgget('error', $ssTokenData)) && '' !== rgget('message', $ssTokenData['error'])) {
            $this->failed_validation  = true;
            $this->validation_message = esc_html__( __("The following error occured [%s]", rgget('message', $ssTokenData['error'])), 'gravityforms' );
        }
        else{ // expect that token data is available
            $token_value = rgget('token_value' ,$ssTokenData );
            $token_type = rgget('token_type' ,$ssTokenData );
            $token_expire = rgget('token_expire' ,$ssTokenData );
            $number = rgget('number' ,rgget('card' ,$ssTokenData ) );
            if ( $this->isRequired && ( empty( $token_value ) || empty( $token_type ) || empty( $token_expire ) || empty( $number ) ) ) {
                $this->failed_validation  = true;
                $this->validation_message = empty( $this->errorMessage ) ? esc_html__( 'Please enter your credit card information.', 'gravityforms' ) : $this->errorMessage;
            }
        }
    }
/*
    private function getVarArraybuilder($array){
        $r = array();
        if(is_array($array)){
            foreach($array as $key=>$element){
                $this->getVarArray($key, $element);
            }
        }
        else{
            $r = __("%s",$array);
        }

        return $r;
    }
    private function getVarArray($key, $element){
        return is_array($element) ? $this->getVarArraybuilder($element) : rgget($key, $element);
    }
*/
    /**
     * Returns the field inner markup.
     *
     * @param array $form The Form Object currently being processed.
     * @param string|array $value The field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
     * @param null|array $entry Null or the Entry Object currently being edited.
     *
     * @return string
     */
    public function get_field_input($form, $value = '', $entry = null)
    {
        $is_entry_detail = $this->is_entry_detail();
        $is_form_editor  = $this->is_form_editor();

        $disabled_text = $is_form_editor ? ",disabled: 'disabled'" : '';

        $form_id  = $form['id'];
        $id       = intval( $this->id );
        $field_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";
        $form_id  = ( $is_entry_detail || $is_form_editor ) && empty( $form_id ) ? rgget( 'id' ) : $form_id;

        $disabled_text = $is_form_editor ? ",disabled: 'disabled'" : '';

        $settings = get_option( 'gravityformsaddon_' . $this->_slug . '_settings' );;
        $baseURL = plugins_url( '', dirname(__FILE__) . '../' );
        $pubKey = (string)trim(rgar($settings, "public_api_key"));
        ob_start();
        include dirname(__FILE__) . "/../templates/cc-payment-fields.php";
        $ss_cc_output = ob_get_clean();
        return $ss_cc_output;
    }
}
GF_Fields::register( new GF_Field_HPSCreditCard() );