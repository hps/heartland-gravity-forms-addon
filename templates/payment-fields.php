<?php

			$wrapper = "<form id='iframes' action='' method='GET'>";

						$card_name =
					    "<div id='ss-name' class='form-group'>
							<label for='iframesCardName'>Card Name<span class='red'>*</span></label>
							<div class='iframeholder' id='iframesCardName'></div>
							</div>
							";

						$card_field =
					      "	<div id='ss-card' class='form-group'>
								<label for='iframesCardNumber'>Card Number<span class='red'>*</span></label>
								<div class='iframeholder' id='iframesCardNumber'></div>
								</div>
								";

					  $expiration_field =
					       "  
								 <div id='ss-date' class='form-group'>
								 <label for='iframesCardExpiration'>Card Expiration<span class='red'>*</span></label>
								 <div class='iframeholder' id='iframesCardExpiration'></div>
								 </div>
								 ";

					    $security_field =
					        "  
									<div id='ss-cvv' class='form-group'>
									<label for='iframesCardCvv'>Card CVV<span class='red'>*</span></label>
									<div class='iframeholder' id='iframesCardCvv'></div>
									</div>
									";

				$wrapper_close = "
						<input type='submit' class='btn btn-primary' value='Submit' />
						</form>
						";

								$ss_js = "
									<script type='text/javascript' src='https:api2.heartlandportico.com/SecureSubmit.v1/token/2.1/securesubmit.js'></script>
									<script type='text/javascript'>
									(function (document, Heartland) {  
								  }(document, Heartland));
									</script>
									";
		

		echo  "<div class='ginput_complex{$class_suffix} ginput_container ginput_container_creditcard' id='{$field_id}'>" .$wrapper  . $card_name . $card_field . $expiration_field . $security_field .  $wrapper_close . $ss_js .  ' </div>';