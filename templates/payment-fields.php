$get_template_part('templates/payment-fields.php');

<?php

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
$expiration_month_placeholder = $this->get_input_placeholder_value( $expiration_month_input );
$expiration_year_input        = GFFormsModel::get_input( $this, $this->id . '.2_year' );
$expiration_year_placeholder  = $this->get_input_placeholder_value( $expiration_year_input );
$expiration_months            = $this->get_expiration_months( $expiration_month, $expiration_month_placeholder );
$expiration_years             = $this->get_expiration_years( $expiration_year, $expiration_year_placeholder );
$expiration_label             = rgar( $expiration_month_input, 'customLabel' ) != '' ? $expiration_month_input['customLabel'] : esc_html__( 'Expiration Date', 'gravityforms' );
$expiration_label             = gf_apply_filters( array( 'gform_card_expiration', $form_id ), $expiration_label, $form_id );
$expiration_field             =
                                      "<span class='ginput_full{$class_suffix} ginput_cardextras' id='{$field_id}_2_container'>
                                          <span class='ginput_cardinfo_left{$class_suffix}' id='{$field_id}_2_cardinfo_left'>
                                            <span class='ginput_card_expiration_container ginput_card_field'>
                                                <label for='{$field_id}_2_month' {$sub_label_class_attribute}>{$expiration_label}</label>
                                                <input type='text' name='input_{$id}.2' id='_exp_date' placeholder='MM / YYYY' value='{$expiration_field}' />

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
