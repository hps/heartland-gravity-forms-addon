
<div class="ginput_complex<?php echo $class_suffix; ?> ginput_container ginput_container_creditcard hps_secure_ach" id="<?php echo $field_id; ?>">
  <div id="HPS_secure_ach">
    <div class="ss-shield"></div>
    <!-- account name -->
    <span class="ginput_full<?php echo $class_suffix; ?>" id="<?php echo $field_id; ?>_5_container">
      <?php echo $card_icons; ?>
      <label for="<?php echo $field_id; ?>_5" id="<?php echo $field_id; ?>_5_label" <?php echo $sub_label_class_attribute; ?>>
        <?php echo $account_name_label; ?>
        <span class="red">*</span>
      </label>

      <input type="text" name="<?php echo GF_Field_HPSach::HPS_ACH_CHECK_HOLDER_FIELD_NAME; ?>" id="<?php echo $field_id; ?>_5"
             placeholder="JON DOE" value="<?php echo $account_name_field_input; ?>" <?php echo $disabled_text; ?>/>
    </span>
    <!-- account number -->
    <span class="ginput_full<?php echo $class_suffix; ?>" id="<?php echo $field_id; ?>_6_container" >
      <label for="<?php echo $field_id; ?>_6" id="<?php echo $field_id; ?>_6_label" <?php echo $sub_label_class_attribute; ?>>
        <?php echo $account_number_label; ?>
        <span class="red">*</span>
      </label>
      
      <input type="tel" name="<?php echo GF_Field_HPSach::HPS_ACH_ACCOUNT_FIELD_NAME; ?>" id="<?php echo $field_id; ?>_6"
             placeholder="• • • • • • • • •" value="<?php echo $account_number_field_input; ?>" <?php echo $disabled_text; ?> <?php echo $onlyDigits; ?>/>
    </span>
    <!-- routing number -->
    <span class="ginput_full<?php echo $class_suffix; ?> ginput_cardextras" id="<?php echo $field_id; ?>_7_container">
      <span id="<?php echo $field_id; ?>_7_accountinfo_left">
        <label for="<?php echo $field_id; ?>_7" <?php echo $sub_label_class_attribute; ?>>
          <?php echo $routing_number_label; ?>
          <span class="red">*</span>
        </label>
        
        <input type="tel" name="<?php echo GF_Field_HPSach::HPS_ACH_ROUTING_FIELD_NAME; ?>" id="<?php echo $field_id; ?>_7"
               placeholder="• • • • • • • • •" value="<?php echo $routing_number_field_input; ?>" <?php echo $onlyDigits; ?> <?php echo $disabled_text; ?>/>
      </span>
    </span>
    <!-- account type -->
    <span class="ginput_accountinfo_left<?php echo $class_suffix; ?>" id="<?php echo $field_id; ?>_8_accountinfo_left">
      <label for="<?php echo $field_id; ?>_8" <?php echo $sub_label_class_attribute; ?>>
        <?php echo $account_type_label; ?>
        <span class="red">*</span>
      </label>

      <select <?php echo $disabled_text; ?>  name="<?php echo GF_Field_HPSach::HPS_ACH_TYPE_FIELD_NAME; ?>" id="<?php echo $field_id; ?>_8">
        <option value="" disabled selected>Choose One..</option>
        <option value="1">Checking</option>
        <option value="2">Savings</option>
      </select>
    </span>
    <!-- check name -->
    <span class="ginput_accountinfo_right<?php echo $class_suffix; ?>" id="<?php echo $field_id; ?>_9_accountinfo_right">
      <label for="<?php echo $field_id; ?>_9" <?php echo $sub_label_class_attribute; ?>>
        <?php echo $check_type_label; ?>
        <span class="red">*</span>
      </label>

      <select <?php echo $disabled_text; ?> name="<?php echo GF_Field_HPSach::HPS_ACH_CHECK_FIELD_NAME; ?>" id="<?php echo $field_id; ?>_9">
        <option value="" disabled selected>Choose One..</option>
        <option value="1">Personal</option>
        <option value="2">Business</option>
      </select>
    </span>
  </div>
</div>


