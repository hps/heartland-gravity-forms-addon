
<div class="ginput_complex<?php echo esc_html($class_suffix); ?> ginput_container ginput_container_creditcard hps_secure_ach" id="<?php echo esc_html($field_id); ?>">
  <div id="HPS_secure_ach">
    <div class="ss-shield"></div>
    <!-- account name -->
    <span class="ginput_full<?php echo esc_html($class_suffix); ?>" id="<?php echo esc_html($field_id); ?>_5_container">
      <?php echo esc_html($card_icons); ?>
      <label for="<?php echo esc_html($field_id); ?>_5" id="<?php echo esc_html($field_id); ?>_5_label" <?php echo esc_html($sub_label_class_attribute); ?>>
        <?php echo esc_html($account_name_label); ?>
        <span class="red">*</span>
      </label>

      <input type="text" name="<?php echo esc_html(GF_Field_HPSach::HPS_ACH_CHECK_HOLDER_FIELD_NAME); ?>" id="<?php echo esc_html($field_id); ?>_5"
             placeholder="JOHN DOE" value="<?php echo esc_html($account_name_value); ?>" <?php echo esc_html($account_name_tabindex); ?> <?php echo esc_html($disabled_text); ?>/>
    </span>

    <!-- account number -->
    <span class="ginput_full<?php echo esc_html($class_suffix); ?>" id="<?php echo esc_html($field_id); ?>_6_container" >
      <label for="<?php echo esc_html($field_id); ?>_6" id="<?php echo esc_html($field_id); ?>_6_label" <?php echo esc_html($sub_label_class_attribute); ?>>
        <?php echo esc_html($account_number_label); ?>
        <span class="red">*</span>
      </label>
      
      <input type="tel" name="<?php echo esc_html(GF_Field_HPSach::HPS_ACH_ACCOUNT_FIELD_NAME); ?>" id="<?php echo esc_html($field_id); ?>_6" autocomplete="off"
             placeholder="• • • • • • • • •" value="<?php echo esc_html($account_number_field_input); ?>" <?php echo esc_html($account_number_tabindex); ?> <?php echo esc_html($disabled_text); ?> <?php echo esc_html($onlyDigits); ?>/>
    </span>
    <!-- routing number -->
    <span class="ginput_full<?php echo esc_html($class_suffix); ?> ginput_cardextras" id="<?php echo esc_html($field_id); ?>_7_container">
      <span id="<?php echo esc_html($field_id); ?>_7_accountinfo_left">
        <label for="<?php echo esc_html($field_id); ?>_7" <?php echo esc_html($sub_label_class_attribute); ?>>
          <?php echo esc_html($routing_number_label); ?>
          <span class="red">*</span>
        </label>

        <input type="tel" name="<?php echo esc_html(GF_Field_HPSach::HPS_ACH_ROUTING_FIELD_NAME); ?>" id="<?php echo esc_html($field_id); ?>_7" autocomplete="off"
               placeholder="• • • • • • • • •" value="<?php echo esc_html($routing_number_field_input); ?>" <?php echo esc_html($routing_number_tabindex); ?> <?php echo esc_html($onlyDigits); ?> <?php echo esc_html($disabled_text); ?>/>
      </span>
    </span>
    <!-- account type -->
    <span class="ginput_accountinfo_left<?php echo esc_html($class_suffix); ?>" id="<?php echo esc_html($field_id); ?>_8_accountinfo_left">
      <label for="<?php echo esc_html($field_id); ?>_8" <?php echo esc_html($sub_label_class_attribute); ?>>
        <?php echo esc_html($account_type_label); ?>
        <span class="red">*</span>
      </label>

      <select <?php echo esc_html($disabled_text); ?>  name="<?php echo esc_html(GF_Field_HPSach::HPS_ACH_TYPE_FIELD_NAME); ?>" id="<?php echo esc_html($field_id); ?>_8" <?php echo esc_html($account_type_tabindex); ?>>
        <option value="" disabled<?php echo esc_html($account_type_value) === '' ? ' selected':''?>>Choose One..</option>
        <option value="1"<?php echo esc_html($account_type_value) === '1' ? ' selected':''?>>Checking</option>
        <option value="2"<?php echo esc_html($account_type_value) === '2' ? ' selected':''?>>Savings</option>
      </select>
    </span>
    <!-- check name -->

    <span class="ginput_accountinfo_right<?php echo esc_html($class_suffix); ?>" id="<?php echo esc_html($field_id); ?>_9_accountinfo_right">
      <label for="<?php echo esc_html($field_id); ?>_9" <?php echo esc_html($sub_label_class_attribute); ?>>
        <?php echo esc_html($check_type_label); ?>
        <span class="red">*</span>
      </label>

      <select <?php echo esc_html($disabled_text); ?> name="<?php echo esc_html(GF_Field_HPSach::HPS_ACH_CHECK_FIELD_NAME); ?>" id="<?php echo esc_html($field_id); ?>_9" <?php echo esc_html($check_type_tabindex); ?>>
        <option value="" disabled<?php echo esc_html($check_type_value) === '' ? ' selected':''?>>Choose One..</option>
        <option value="1"<?php echo esc_html($check_type_value) === '1' ? ' selected':''?>>Personal</option>
        <option value="2"<?php echo esc_html($check_type_value) === '2' ? ' selected':''?>>Business</option>
      </select>
    </span>
  </div>
</div>


