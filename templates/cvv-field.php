<?php echo=

"<span class='ginput_cardinfo_right{$class_suffix} cvv-field' id='{$field_id}_2_cardinfo_right'>

      <span class='ginput_card_expiration_container ginput_card_field'>

          <label for='{$field_id}_3' {$sub_label_class_attribute}>$security_code_label</label>

          <input type='text'  tabindex=6 name='input_{$id}.3' id='_cvv_number' placeholder='CVV' {$tabindex} {$disabled_text} class='ginput_card_security_code ginput_card_security_code_icon' value='{$security_code}' {$autocomplete} {$html5_output} {$security_code_placeholder} />

    </span>

  </span>";

  ?>
