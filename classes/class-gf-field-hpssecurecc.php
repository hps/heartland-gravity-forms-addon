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
        );
    }

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