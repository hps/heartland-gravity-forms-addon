<?php

	$card_name_field =
    "  <span class='ginput_full{$class_suffix}' id='{$field_id}_5_container'>{$card_icons}

          <label for='{$field_id}_5' id='{$field_id}_5_label' {$sub_label_class_attribute}>{$card_name_label}</label>

              <input type='text' tabindex=3 name='input_{$id}.5' id='{$field_id}_5' placeholder='CARDHOLDER NAME' value='{$card_name}'/>

      </span>";

	$card_field =
      "  <span class='ginput_full{$class_suffix}' id='{$field_id}_1_container' >

            <label for='{$field_id}_1' id='{$field_id}_1_label' {$sub_label_class_attribute}>{$card_number_label}</label>

              <input type='text' tabindex=4 name='input_{$id}.1' class='card_type_icon' id='cc_number' placeholder='CARD NUMBER' value='{$card_number}'/>

        </span>";

  $expiration_field =
       "  <span class='ginput_full{$class_suffix} ginput_cardextras' id='{$field_id}_2_container'>

              <span class='ginput_cardinfo_left{$class_suffix}' id='{$field_id}_2_cardinfo_left'>

                <span class='ginput_card_expiration_container ginput_card_field'>

                    <label for='{$field_id}_2_month' {$sub_label_class_attribute}>{$expiration_label}</label>

                    <input type='text'   tabindex=5 name='input_{$id}.2' id='_exp_date' placeholder='MM / YYYY' value='{$expiration_field}'/>

                </span>

          </span>";


    $security_field =
        "  <span class='ginput_cardinfo_right{$class_suffix} cvv-field' id='{$field_id}_2_cardinfo_right'>

              <span class='ginput_card_expiration_container ginput_card_field'>

                    <label for='{$field_id}_3' {$sub_label_class_attribute}>$security_code_label</label>

                          <input type='text'  tabindex=6 name='input_{$id}.3' id='_cvv_number' placeholder='CVV' {$tabindex} {$disabled_text} class='ginput_card_security_code ginput_card_security_code_icon' value='{$security_code}' {$autocomplete} {$html5_output} {$security_code_placeholder} />

              </span>

           </span>";

echo  "<div class='ginput_complex{$class_suffix} ginput_container ginput_container_creditcard' id='{$field_id}'>" . $card_name_field . $card_field . $expiration_field . $security_field . ' </div>';
