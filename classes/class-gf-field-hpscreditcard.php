<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}




/**
 * Class GF_Field_HPSCreditCard
 */
class GF_Field_HPSCreditCard extends GF_Field {

	/**
	 * @var string
     */
	public $type = 'hpscreditcard';

	/**
	 * @return string
     */
	public function get_form_editor_field_title() {
		return esc_attr__( 'Secure Credit Card', 'gravityforms' );
	}

	/**
	 * @return array
     */
	function get_form_editor_field_settings() {
		return array(
			'conditional_logic_field_setting',
			'force_ssl_field_setting',
			'credit_card_style_setting',
			'error_message_setting',
			'label_setting',
			'label_placement_setting',
			'sub_labels_setting',
			'sub_label_placement_setting',
			'label_placement_setting',
			'admin_label_setting',
			'rules_setting',
			'description_setting',
			'css_class_setting',
			'credit_card_setting',
			'input_placeholders_setting',
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
	 * @param array $form
     */
	public function validate($value, $form ) {
		$card_number     = rgpost( 'input_' . $this->id . '_1' );
		$expiration_date = rgpost( 'input_' . $this->id . '_2' );
		$security_code   = rgpost( 'input_' . $this->id . '_3' );

		if ( $this->isRequired && ( empty( $card_number ) || empty( $security_code ) || empty( $expiration_date[0] ) || empty( $expiration_date[1] ) ) ) {
			$this->failed_validation  = true;
			$this->validation_message = empty( $this->errorMessage ) ? esc_html__( 'Please enter your credit card information.', 'gravityforms' ) : $this->errorMessage;
		} elseif ( ! empty( $card_number ) ) {
			$card_type     = GFCommon::get_card_type( $card_number );

			if ( empty( $security_code ) ) {
				$this->failed_validation  = true;
				$this->validation_message = esc_html__( "Please enter your card's security code.", 'gravityforms' );
			} elseif ( ! $card_type ) {
				$this->failed_validation  = true;
				$this->validation_message = esc_html__( 'Invalid credit card number.', 'gravityforms' );
			} elseif ( ! $this->is_card_supported( $card_type['slug'] ) ) {
				$this->failed_validation  = true;
				$this->validation_message = $card_type['name'] . ' ' . esc_html__( 'is not supported. Please enter one of the supported credit cards.', 'gravityforms' );
			}
		}
	}

	/**
	 * @param $card_slug
	 * @return bool
     */
	public function is_card_supported($card_slug ) {
		$supported_cards = $this->creditCards;
		$default_cards   = array( 'amex', 'discover', 'mastercard', 'visa' );

		if ( ! empty( $supported_cards ) && in_array( $card_slug, $supported_cards ) ) {
			return true;
		} elseif ( empty( $supported_cards ) && in_array( $card_slug, $default_cards ) ) {
			return true;
		}

		return false;

	}

	/**
	 * @param array $field_values
	 * @param bool $get_from_post_global_var
	 * @return array|string
     */
	public function get_value_submission($field_values, $get_from_post_global_var = true ) {

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
	 * @param array $form
	 * @param string $value
	 * @param null $entry
	 * @return string
     */
	public function get_field_input($form, $value = '', $entry = null ) {
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();

		$form_id  = $form['id'];
		$id       = intval( $this->id );
		$field_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";
		$form_id  = ( $is_entry_detail || $is_form_editor ) && empty( $form_id ) ? rgget( 'id' ) : $form_id;

		$disabled_text = $is_form_editor ? "disabled='disabled'" : '';
		$class_suffix  = $is_entry_detail ? '_admin' : '';




		$card_name        = '';
		$card_number      = '';
		$expiration_date = '';
		$security_code    = '';
		$autocomplete     = RGFormsModel::is_html5_enabled() ? "autocomplete='off'" : '';

		if ( is_array( $value ) ) {
			$card_number     = esc_attr( rgget( $this->id . '.1', $value ) );
			$card_name       = esc_attr( rgget( $this->id . '.5', $value ) );
			$expiration_date = rgget( $this->id . '.2', $value );
			if ( ! is_array( $expiration_date ) && ! empty( $expiration_date ) ) {
				$expiration_date = explode( '/', $expiration_date );
			}

			if ( is_array( $expiration_date ) && count( $expiration_date ) == 2 ) {
				$expiration_month = $expiration_date[0];
				$expiration_year  = $expiration_date[1];
			}

			$security_code = esc_attr( rgget( $this->id . '.3', $value ) );
		}

		// $action = ! ( $is_entry_detail || $is_form_editor ) ? "gformMatchCard(\"{$field_id}_1\");" : '';

		$onchange = "onchange='{$action}'";
		$onkeyup  = "onkeyup='{$action}'";

		$card_icons = '';
		$cards      = GFCommon::get_card_types();
		$card_style = $this->creditCardStyle ? $this->creditCardStyle : 'style1';


		$card_icons .= "<div class='gform_card_icon'>{$card['name']}</div>";
		$payment_methods = apply_filters( 'gform_payment_methods', array(), $this, $form_id );
		$payment_options = '';
		if ( is_array( $payment_methods ) ) {
		  foreach ( $payment_methods as $payment_method ) {
		    $checked = rgpost( 'gform_payment_method' ) == $payment_method['key'] ? "checked='checked'" : '';
		    $payment_options .= "<div class='gform_payment_option gform_payment_{$payment_method['key']}'><input type='radio' name='gform_payment_method' value='{$payment_method['key']}' id='gform_payment_method_{$payment_method['key']}' onclick='gformToggleCreditCard();' onkeypress='gformToggleCreditCard();' {$checked}/> {$payment_method['label']}</div>";
		  }
		}
		$checked           = rgpost( 'gform_payment_method' ) == 'creditcard' || rgempty( 'gform_payment_method' ) ? "checked='checked'" : '';
		$card_radio_button = empty( $payment_options ) ? '' : "<input type='radio' name='gform_payment_method' id='gform_payment_method_creditcard' value='creditcard' onclick='gformToggleCreditCard();' onkeypress='gformToggleCreditCard();' {$checked}/>";
		$card_icons        = "{$payment_options}<div class='gform_card_icon_container gform_card_icon_{$card_style}'>{$card_radio_button}{$card_icons}</div>";

		//card name fields
		  $tabindex              = $this->get_tabindex();
		  $card_name_field_input = GFFormsModel::get_input( $this, $this->id . '.5' );
		  $card_name_label       = rgar( $card_name_field_input, 'customLabel' ) != '' ? $card_name_field_input['customLabel'] : esc_html__( 'Cardholder Name', 'gravityforms' );
		  $card_name_label       = gf_apply_filters( array( 'gform_card_name', $form_id ), $card_name_label, $form_id );
		  $card_name_placeholder = $this->get_input_placeholder_attribute( $card_name_field_input );
		  $card_name_field = "<span class='ginput_full{$class_suffix}' id='{$field_id}_5_container'>
		                      {$card_icons}
		                                          <label for='{$field_id}_5' id='{$field_id}_5_label' {$sub_label_class_attribute}>{$card_name_label}</label>
		                                          <input type='text' name='input_{$id}.5' id='{$field_id}_5' placeholder='CARDHOLDER NAME' value='{$card_name}' />
		                                      </span>";


		//card number fields
		$tabindex                = $this->get_tabindex();
		$card_number_field_input = GFFormsModel::get_input( $this, $this->id . '.1' );
		$html5_output            = ! is_admin() && GFFormsModel::is_html5_enabled() ? "pattern='[0-9]*' title='" . esc_attr__( 'Only digits are allowed', 'gravityforms' ) . "'" : '';
		$card_number_label       = rgar( $card_number_field_input, 'customLabel' ) != '' ? $card_number_field_input['customLabel'] : esc_html__( 'Card Number', 'gravityforms' );
		$card_number_label       = gf_apply_filters( array( 'gform_card_number', $form_id ), $card_number_label, $form_id );
		$card_field =
		                            "<span class='ginput_full{$class_suffix}' id='{$field_id}_1_container' >
		                                <label for='{$field_id}_1' id='{$field_id}_1_label' {$sub_label_class_attribute}>{$card_number_label}</label>
		                                <input type='text' name='input_{$id}.1' class='card_type_icon' id='cc_number' placeholder='CARD NUMBER' value='{$card_number}' />
		                             </span>";

		//expiration date field
		$expiration_month_tab_index   = $this->get_tabindex();
		$expiration_year_tab_index    = $this->get_tabindex();
		$expiration_month_input       = GFFormsModel::get_input( $this, $this->id . '.2_month' );
		//$expiration_month_placeholder = $this->get_input_placeholder_value( $expiration_month_input );
		$expiration_year_input        = GFFormsModel::get_input( $this, $this->id . '.2_year' );
		//$expiration_year_placeholder  = $this->get_input_placeholder_value( $expiration_year_input );
		$expiration_months            = $this->get_expiration_months( $expiration_month );
		$expiration_years             = $this->get_expiration_years( $expiration_year );
		$expiration_label             = rgar( $expiration_month_input, 'customLabel' ) != '' ? $expiration_month_input['customLabel'] : esc_html__( 'Expiration Date', 'gravityforms' );
		$expiration_label             = gf_apply_filters( array( 'gform_card_expiration', $form_id ), $expiration_label, $form_id );
		$expiration_field =
		                                      "<span class='ginput_full{$class_suffix} ginput_cardextras' id='{$field_id}_2_container'>
		                                          <span class='ginput_cardinfo_left{$class_suffix}' id='{$field_id}_2_cardinfo_left'>
		                                            <span class='ginput_card_expiration_container ginput_card_field'>
		                                                <label for='{$field_id}_2_month' {$sub_label_class_attribute}>{$expiration_label}</label>
		                                                <input type='text' name='input_{$id}.2' id='_exp_date' placeholder='MM / YYYY' value='{$expiration_month} {$expiration_year} {$expiration_field}' />
		                                            </span>
		                                        </span>";

		//security code field
		$tabindex                  = $this->get_tabindex();
		$security_code_field_input = GFFormsModel::get_input( $this, $this->id . '.3' );
		$security_code_label       = rgar( $security_code_field_input, 'customLabel' ) != '' ? $security_code_field_input['customLabel'] : esc_html__( 'Security Code', 'gravityforms' );
		$security_code_label       = gf_apply_filters( array( 'gform_card_security_code', $form_id ), $security_code_label, $form_id );
		$html5_output              = GFFormsModel::is_html5_enabled() ? "pattern='[0-9]*' title='" . esc_attr__( 'Only digits are allowed', 'gravityforms' ) . "'" : '';
		$security_code_placeholder = $this->get_input_placeholder_attribute( $security_code_field_input );
		$security_field =

		                                  "<span class='ginput_cardinfo_right{$class_suffix} cvv-field' id='{$field_id}_2_cardinfo_right'>
		                                        <span class='ginput_card_expiration_container ginput_card_field'>
		                                            <label for='{$field_id}_3' {$sub_label_class_attribute}>$security_code_label</label>
		                                            <input type='text' name='input_{$id}.3' id='_cvv_number' placeholder='CVV' {$tabindex} {$disabled_text} class='ginput_card_security_code ginput_card_security_code_icon' value='{$security_code}' {$autocomplete} {$html5_output} {$security_code_placeholder} />
		                                      </span>
		                                    </span>";


		return "<div class='ginput_complex{$class_suffix} ginput_container ginput_container_creditcard' id='{$field_id}'>" . $card_name_field . $card_field . $expiration_field . $security_field . ' </div>';

		}

	/**
	 * @return string
     */
	public function get_field_label_class(){
		return 'gfield_label gfield_label_before_complex';
	}

	/**
	 * @param $selected_month
	 * @param $placeholder
	 * @return string
     */
	private function get_expiration_months($selected_month, $placeholder ) {
		if ( empty( $placeholder ) ) {
			$placeholder = esc_html__( 'Month', 'gravityforms' );
		}
		$str = "<option value=''>{$placeholder}</option>";
		for ( $i = 1; $i < 13; $i ++ ) {
			$selected = intval( $selected_month ) == $i ? "selected='selected'" : '';
			$month    = str_pad( $i, 2, '0', STR_PAD_LEFT );
			$str .= "<option value='{$i}' {$selected}>{$month}</option>";
		}

		return $str;
	}

	/**
	 * @param $selected_year
	 * @param $placeholder
	 * @return string
     */
	private function get_expiration_years($selected_year, $placeholder ) {
		if ( empty( $placeholder ) ) {
			$placeholder = esc_html__( 'Year', 'gravityforms' );
		}
		$str  = "<option value=''>{$placeholder}</option>";
		$year = intval( date( 'Y' ) );
		for ( $i = $year; $i < ( $year + 20 ); $i ++ ) {
			$selected = intval( $selected_year ) == $i ? "selected='selected'" : '';
			$str .= "<option value='{$i}' {$selected}>{$i}</option>";
		}

		return $str;
	}

	/**
	 * @param array|string $value
	 * @param string $currency
	 * @param bool $use_text
	 * @param string $format
	 * @param string $media
	 * @return string
     */
	public function get_value_entry_detail($value, $currency = '', $use_text = false, $format = 'html', $media = 'screen' ) {

		if ( is_array( $value ) ) {
			$card_number = trim( rgget( $this->id . '.1', $value ) );
			$card_type   = trim( rgget( $this->id . '.4', $value ) );
			$separator   = $format == 'html' ? '<br/>' : "\n";

			return empty( $card_number ) ? '' : $card_type . $separator . $card_number;
		} else {
			return '';
		}
	}

	/**
	 * @param array $form
	 * @return string
     */
	public function get_form_inline_script_on_page_render($form ) {

		$field_id = "input_{$form['id']}_{$this->id}";

		if ( $this->forceSSL && ! GFCommon::is_ssl() && ! GFCommon::is_preview() ) {
			$script = "document.location.href='" . esc_js( RGFormsModel::get_current_page_url( true ) ) . "';";
		} else {
			$script = ""; // "jQuery(document).ready(function(){ { gformMatchCard(\"{$field_id}_1\"); } } );";
		}

		$card_rules = $this->get_credit_card_rules();
		$script     = "if(!window['gf_cc_rules']){window['gf_cc_rules'] = new Array(); } window['gf_cc_rules'] = " . GFCommon::json_encode( $card_rules ) . "; $script";

		return $script;
	}

	/**
	 * @return array
     */
	public function get_credit_card_rules() {

		$cards = GFCommon::get_card_types();
		//$supported_cards = //TODO: Only include enabled cards
		$rules = array();

		foreach ( $cards as $card ) {
			$prefixes = explode( ',', $card['prefixes'] );
			foreach ( $prefixes as $prefix ) {
				$rules[ $card['slug'] ][] = $prefix;
			}
		}

		return $rules;
	}

	/**
	 * @return array
     */
	public function get_entry_inputs() {
		$inputs = array();
		// only store month and card number input values
		foreach ( $this->inputs as $input ) {
			if ( in_array( $input['id'], array( $this->id . '.1', $this->id . '.4' ) ) ) {
				$inputs[] = $input;
			}
		}

		return $inputs;
	}

	/**
	 * @param string $value
	 * @param array $form
	 * @param string $input_name
	 * @param int $lead_id
	 * @param array $lead
	 * @return string
     */
	public function get_value_save_entry($value, $form, $input_name, $lead_id, $lead ) {

		//saving last 4 digits of credit card
		list( $input_token, $field_id_token, $input_id ) = rgexplode( '_', $input_name, 3 );
		if ( $input_id == '1' ) {
			$value              = str_replace( ' ', '', $value );
			$card_number_length = strlen( $value );
			$value              = substr( $value, - 4, 4 );
			$value              = str_pad( $value, $card_number_length, 'X', STR_PAD_LEFT );
		} elseif ( $input_id == '4' ) {

			$value = rgpost( "input_{$field_id_token}_4" );

			if ( ! $value ) {
				$card_number = rgpost( "input_{$field_id_token}_1" );
				$card_type   = GFCommon::get_card_type( $card_number );
				$value       = $card_type ? $card_type['name'] : '';
			}
		} else {
			$value = '';
		}

		return $this->sanitize_entry_value( $value, $form['id'] );
	}

	/**
	 * GF1.8 and earlier used 5 inputs (1 input for the expiration date); GF1.9 changed to 6 inputs (the expiration month and year now separate); upgrade those fields still using the older configuration.
	 */
	public function maybe_upgrade_inputs() {
		$inputs    = $this->inputs;
		$exp_input = $inputs[1];
		$exp_id    = $this->id . '.2';

		if ( count( $inputs ) == 5 && $exp_input['id'] == $exp_id ) {
			$new_inputs = array(
				array(
					'id'           => $exp_id . '_month',
					'label'        => esc_html__( 'Expiration Month', 'gravityforms' ),
					'defaultLabel' => $exp_input['label']
				),
				array(
					'id'    => $exp_id . '_year',
					'label' => esc_html__( 'Expiration Year', 'gravityforms' ),
				)
			);

			array_splice( $inputs, 1, 1, $new_inputs );
			$this->inputs = $inputs;
		}
	}
}

GF_Fields::register( new GF_Field_HPSCreditCard() );
