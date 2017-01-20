<?php

  $wrapper = "<div id='HPS_secure_ach'>";

$hps_ach_check_holder_field_name = GF_Field_HPSach::HPS_ACH_CHECK_HOLDER_FIELD_NAME;
$hps_ach_account_field_name = GF_Field_HPSach::HPS_ACH_ACCOUNT_FIELD_NAME;
$hps_ach_routing_field_name = GF_Field_HPSach::HPS_ACH_ROUTING_FIELD_NAME;
$hps_ach_type_field_name = GF_Field_HPSach::HPS_ACH_TYPE_FIELD_NAME;
$hps_ach_check_field_name = GF_Field_HPSach::HPS_ACH_CHECK_FIELD_NAME;
    $account_name_field =
        "  <span class='ginput_full{$class_suffix}' id='{$field_id}_5_container'>{$card_icons}

              <label for='{$field_id}_5' id='{$field_id}_5_label' {$sub_label_class_attribute}>{$account_name_label}<span class='red'>*</span></label>

                  <input type='text' name='{$hps_ach_check_holder_field_name}' id='{$field_id}_5' placeholder='JON DOE' value='{$account_name_field_input}' {$disabled_text}'/>

          </span>";


     $account_number_field	=
          "  <span class='ginput_full{$class_suffix}' id='{$field_id}_6_container' >

                <label for='{$field_id}_6' id='{$field_id}_6_label' {$sub_label_class_attribute}>{$account_number_label}<span class='red'>*</span></label>

                  <input type='tel' name='{$hps_ach_account_field_name}' class='' id='{$field_id}_6' placeholder='000000000' value='{$account_number_field_input}' {$disabled_text}/>

            </span>";

     $routing_number_field 	=
           "  <span class='ginput_full{$class_suffix} ginput_cardextras' id='{$field_id}_7_container'>

                  <span class='' id='{$field_id}_7_accountinfo_left'>

                        <label for='{$field_id}_7' {$sub_label_class_attribute}>{$routing_number_label}<span class='red'>*</span></label>

                        <input type='tel' name='{$hps_ach_routing_field_name}' id='routing_number' placeholder='000000000' value='{$routing_number_field_input}' {$disabled_text}/>

                    </span>

              </span>";

     $account_type_field =
            "  <span class='ginput_accountinfo_left{$class_suffix}' id='{$field_id}_8_accountinfo_left'>


                        <label for='{$field_id}_8' {$sub_label_class_attribute}>$account_type_label<span class='red'>*</span></label>

                            <select {$disabled_text}  name='{$hps_ach_type_field_name}' class=''
                            id='{$field_id}_8'>
                              <option value='' disabled selected>Choose One..</option>
                              <option value='0'>Checking</option>
                              <option value='1'>Savings</option>
                            </select>


               </span>";

    $check_type_field =
            "  <span class='ginput_accountinfo_right{$class_suffix}' id='{$field_id}_9_accountinfo_right'>


                          <label for='{$field_id}_9' {$sub_label_class_attribute}>$check_type_label<span class='red'>*</span></label>

                                <select {$disabled_text} name='{$hps_ach_check_field_name}' class=''
                                id='{$field_id}_9'>
                                  <option value='' disabled selected>Choose One..</option>
                                  <option value='0'>Personal</option>
                                  <option value='1'>Business</option>
                                </select>


                </span>";

  $wrapper_close = "</div>";

echo  "<div class='ginput_complex{$class_suffix} ginput_container ginput_container_creditcard' id='{$field_id}'>" .$wrapper . $account_name_field . $account_number_field . $routing_number_field . $account_type_field . $check_type_field . $wrapper_close . ' </div>';
