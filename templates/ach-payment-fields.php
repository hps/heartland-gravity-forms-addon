<?php

  $wrapper = "<div id='HPS_secure_ach'>";

    $account_name_field =
        "  <span class='ginput_full{$class_suffix}' id='{$field_id}_5_container'>{$card_icons}

              <label for='{$field_id}_5' id='{$field_id}_5_label' {$sub_label_class_attribute}>{$account_name_label}</label>

                  <input type='text' name='input_{$id}.5' id='{$field_id}_5' placeholder='JOHN DOE' value='{$account_name}' {$disabled_text}'/>

          </span>";

     $account_number_field	=
          "  <span class='ginput_full{$class_suffix}' id='{$field_id}_6_container' >

                <label for='{$field_id}_6' id='{$field_id}_6_label' {$sub_label_class_attribute}>{$account_number_label}</label>

                  <input type='tel' name='input_{$id}.6' class='' id='{$field_id}_6' placeholder='000000000' value='{$account_number_field}' {$disabled_text}/>

            </span>";

     $routing_number_field 	=
           "  <span class='ginput_full{$class_suffix} ginput_cardextras' id='{$field_id}_7_container'>

                  <span class='' id='{$field_id}_7_accountinfo_left'>

                        <label for='{$field_id}_7' {$sub_label_class_attribute}>{$routing_number_label}</label>

                        <input type='tel' name='input_{$id}.7' id='routing_number' placeholder='000000000' value='{$routing_number_field}' {$disabled_text}/>

                    </span>

              </span>";

     $account_type_field =
            "  <span class='ginput_accountinfo_left{$class_suffix}' id='{$field_id}_8_accountinfo_left'>


                        <label for='{$field_id}_8' {$sub_label_class_attribute}>$account_type_label</label>

                            <select {$disabled_text}>
                              <option value='' disabled selected>Choose One..</option>
                              <option value=''>Checking</option>
                              <option value=''>Savings</option>
                            </select>


               </span>";

    $check_type_field =
            "  <span class='ginput_accountinfo_right{$class_suffix}' id='{$field_id}_9_accountinfo_right'>


                          <label for='{$field_id}_9' {$sub_label_class_attribute}>$check_type_label</label>

                                <select {$disabled_text}>
                                  <option value='' disabled selected>Choose One..</option>
                                  <option value=''>Personal</option>
                                  <option value=''>Business</option>
                                </select>


                </span>";

  $wrapper_close = "</div>";

echo  "<div class='ginput_complex{$class_suffix} ginput_container ginput_container_creditcard' id='{$field_id}'>" .$wrapper . $account_name_field . $account_number_field . $routing_number_field . $account_type_field . $check_type_field . $wrapper_close . ' </div>';
