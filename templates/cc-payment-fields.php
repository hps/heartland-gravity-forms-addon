<?php

			$wrapper = "<div id='HPS_secure_cc'>";

						$card_name_field =
					    "<div id=\"ss-card\" class=\"form-group\">
								<label for=\"iframesCardNumber\">Card Number<span class=\"red\">*</span></label>
								<div class=\"iframeholder\" id=\"iframesCardNumber\"></div>
							</div>
							<div id=\"ss-date\" class=\"form-group\">
								<label for=\"iframesCardExpiration\">Card Expiration<span class=\"red\">*</span></label>
								<div class=\"iframeholder\" id=\"iframesCardExpiration\"></div>
							</div>
							<div id=\"ss-cvv\" class=\"form-group\">
								<label for=\"iframesCardCvv\">Card CVV<span class=\"red\">*</span></label>
								<div class=\"iframeholder\" id=\"iframesCardCvv\"></div>
							</div>
";

						$card_field =
					      "  <span class='ginput_full{$class_suffix}' id='HPS_secure_cc {$field_id}_1_container' >

					            <label for='{$field_id}_1' id='{$field_id}_1_label' {$sub_label_class_attribute}>{$card_number_label}</label>

					              <div type='text' tabindex=2 class='card_type_icon' id='cc_number'></div>

					        </span>";

					  $expiration_field =
					       "  <span class='ginput_full{$class_suffix} ginput_cardextras' id='{$field_id}_2_container'>

					              <span class='ginput_cardinfo_left{$class_suffix}' id='{$field_id}_2_cardinfo_left'>

					                <span class='ginput_card_expiration_container ginput_card_field'>

					                    <label for='{$field_id}_2_month' {$sub_label_class_attribute}>{$expiration_label}</label>

					                    <input type='text'   tabindex=3 name='input_{$id}.2' id='_exp_date' placeholder='MM / YYYY' value='{$expiration_field}'/>

					                </span>

					          </span>";


					    $security_field =
					        "  <span class='ginput_cardinfo_right{$class_suffix} cvv-field' id='{$field_id}_2_cardinfo_right'>

					              <span class='ginput_card_expiration_container ginput_card_field'>

					                    <label for='{$field_id}_3' {$sub_label_class_attribute}>$security_code_label</label>

					                          <input type='text'  tabindex=4 name='input_{$id}.3' id='_cvv_number' placeholder='CVV' {$tabindex} {$disabled_text} class='ginput_card_security_code ginput_card_security_code_icon' value='{$security_code}' {$autocomplete} {$html5_output} {$security_code_placeholder} />

					              </span>

					           </span>";

			$wrapper_close = "</div>";



	$ss_js = "
	  <!-- The SecureSubmit Javascript Library -->
		<script type='text/javascript' src='https://api2.heartlandportico.com/SecureSubmit.v1/token/2.1/securesubmit.js' href='assets/securesubmit.js'></script>
		<!-- The Integration Code -->
		<script type='text/javascript'>
		  (function (document, Heartland) {
		    // Create a new `HPS` object with the necessary configuration
		    var hps = new Heartland.HPS({
		      publicKey: 'pkapi_cert_jKc1FtuyAydZhZfbB3',
		      type:      'iframe',
		      // Configure the iframe fields to tell the library where
		      // the iframe should be inserted into the DOM and some
		      // basic options

		      fields: {
		        cardNumber: {
		          target:      'iframesCardNumber',
		          placeholder: '•••• •••• •••• ••••'
		        },
		        cardExpiration: {
		          target:      'iframesCardExpiration',
		          placeholder: 'MM / YYYY'
		        },
		        cardCvv: {
		          target:      'iframesCardCvv',
		          placeholder: 'CVV'
		        }
		      },
		      // Collection of CSS to inject into the iframes.
		      // These properties can match the site's styles
		      // to create a seamless experience.
		      style: {
		        '.gform_wrapper #HPS_secure_cc input': {
			        		'box-sizing':'border-box',
			            'height': '34px',
			            'padding': '15px !important',
									'font-size': '14px',
									'min-width' : '100%'
		        },
						'.gform_wrapper .ginput_complex #HPS_secure_cc .ginput_cardinfo_right input.ginput_card_security_code,
						 .gform_wrapper .ginput_complex #HPS_secure_cc .ginput_cardinfo_right.cvv-field input.ginput_card_security_code' : {
									'max-width' : '100% !important'
						},
						'.gform_wrapper #HPS_secure_cc input,
						 .gform_wrapper #HPS_secure_cc select': {
			            'line-height': '1.42857143',
			            'color': '#555',
			            'background-color': '#fff',
			            'border': '1px solid #ccc',
			            'border-radius': '0px',
			            '-webkit-box-shadow': 'none',
			            'box-shadow': 'none',
			            '-webkit-transition': 'border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s',
			            '-o-transition': 'border-color ease-in-out .15s,box-shadow ease-in-out .15s',
			            'transition': 'border-color ease-in-out .15s,box-shadow ease-in-out .15s'
		        },
		        '.gform_wrapper #HPS_secure_cc input[type=text]:focus,
						 .gform_wrapper #HPS_secure_cc input[type=tel].focus,
						 .gform_wrapper #HPS_secure_cc input[type=text]:hover,
 						 .gform_wrapper #HPS_secure_cc input[type=tel].hover,
						 .gform_wrapper #HPS_secure_cc select:focus,
  					 .gform_wrapper #HPS_secure_cc select.hover' :{
				        	'border': '1px solid #3989e3',
				          'outline': 'none !important',
				          '-webkit-box-shadow': 'none',
				          'box-shadow': 'none'
		        },
						'.gform_wrapper .field_sublabel_below .ginput_complex.ginput_container #HPS_secure_cc label,
						 .gform_wrapper .field_sublabel_below div[class*=gfield_time_].ginput_container #HPS_secure_cc label' : {
									'text-transform' : 'uppercase !important',
									'margin' : '9px 0px 0px 0px !important'
						},
		        '.gform_wrapper #HPS_secure_cc input[type=submit]' : {
		        			'box-sizing':'border-box',
		              'padding': '10px',
		              'margin-bottom': '0',
		              'font-size': '14px',
		              'font-weight': '400',
		              'vertical-align': 'middle',
		              '-ms-touch-action': 'manipulation',
		              'touch-action': 'manipulation',
		              'cursor': 'pointer',
		              '-webkit-user-select': 'none',
		              '-moz-user-select': 'none',
		              '-ms-user-select': 'none',
		              'user-select': 'none',
		              'background-image': 'none',
		              'border': '1px solid #2e6da4',
		              'border-radius': '0px',
		              'color': '#fff',
		              'background-color': '#337ab7'
		        },
		        'input[type=submit]:hover':{
			        		'color': '#fff',
			            'background-color': '#286090',
			            'border-color': '#204d74'
		        },
		        'input[type=submit]:focus, input[type=submit].focus':{
			            'color': '#fff',
			            'background-color': '#286090',
			            'border-color': '#122b40',
			            'text-decoration': 'none',
			            'outline': '5px auto -webkit-focus-ring-color',
			    				'outline-offset': '-2px'
		        },
		        'input:nth-child(n+1), .ie8 input:nth-child(n+1)' :{
				          'height':'40px',
				          'border':'1px solid #ccc',
				          'width':'95%'
		        },
		        'input:nth-child(n+1):hover, input:nth-child(n+1):focus' :{
				          'border':'1px solid transparent',
				          'border-color':'#3989e3',
				          'outline': '0',
				          '-webkit-box-shadow': 'none',
				          'box-shadow': 'none'
		        },
		        '.ie8 input:nth-child(n+1):hover, input:nth-child(n+1):focus' :{
				          'border':'1px solid transparent',
				          'border-color':'#3989e3',
				          'outline': '0',
				          '-webkit-box-shadow': 'none',
				          'box-shadow': 'none'
		        },
						'.field_sublabel_below .ginput_complex.ginput_container #HPS_secure_cc label,
						 .gform_wrapper .field_sublabel_below div[class*=gfield_time_].ginput_container #HPS_secure_cc label' :{
		          		'margin-top':'10px !important'
		        },
						'.HPS_gform_card_icon_container' : {
									'height':'42px !important'
						},
		        '#HPS_secure_cc input[placeholder],
						 #HPS_secure_cc select[placeholder]' :{
		          		'letter-spacing':'3px'
		        },
						'div.HPS_gform_card_icon' : {
									'background' : 'transparent url('+location.href+ '../images/img/ss-shield@1x.png) no-repeat !important',
									'background-size' : '250px 30px !important',
									'width' : '540px',
									'height' : '42px !important'
						},
		        '.ginput_card_security_code_icon' : {
				          'background':'transparent url('+location.href+ '../images/img/cvv1.png) no-repeat right center',
				          'background-size' :'60px 40px'
		        },
		        '.card_type_icon' : {
				          'background':'transparent url('+location.href+ '../images/img/ss-inputcard-blank@2x.png) no-repeat right center',
				          'background-size' :'60px 40px'
		        },
		        '.invalid.card-type-visa' :{
				          'background':'transparent url('+location.href+ '../images/img/visa.png) no-repeat right center',
				          'background-size' :'83px 88px',
				          'background-position-y':'-44px'
		        },
		        '.valid.card-type-visa' :{
				          'background':'transparent url('+location.href+ '../images/img/visa.png) no-repeat right center',
				          'background-size' :'79px 109px'
		        },
		        '.invalid.card-type-discover' :{
				          'background':'transparent url('+location.href+ 'assets/images/discover.png) no-repeat right center',
				          'background-size' :'50px 30px',
									'background-position-y' :'-48px'
		        },
		        '.valid.card-type-discover' :{
				          'background':'transparent url('+location.href+ '../images/img/discover.png) no-repeat right center',
				          'background-size' :'69px 100px',
									'background-position-y' : '1px'
		        },
		        '.invalid.card-type-amex' :{
				          'background':'transparent url('+location.href+ '../images/img/amex.png) no-repeat right center',
				          'background-size' :'50px 30px',
				          'background-position-y':'-49'
		        },
		        '.valid.card-type-amex' :{
				          'background':'transparent url('+location.href+ '../images/img/amex.png) no-repeat right center',
				          'background-size' :'58px 100px',
									'background-position-y' : '0px'
		        },
		        '.invalid.card-type-mastercard' :{
				          'background':'transparent url('+location.href+ 'assets/images/ss-saved-mastercard.png) no-repeat right',
				          'background-size' :'85px 81px',
				          'background-position-y':'-55px'
		        },
		        '.valid.card-type-mastercard' :{
				          'background':'transparent url('+location.href+ '../images/img/mastercard.png) no-repeat right center',
				          'background-size' :'65px 100px',
				          'background-position-y':'-1px'
		        },
		        '.invalid.card-type-jcb' :{
				          'background':'transparent url('+location.href+ '../images/img/jcb.png) no-repeat right center',
				          'background-size' :'70px 100px',
				          'background-position-y':'-3px'
		        },
		        '.valid.card-type-jcb' :{
				          'background':'transparent url('+location.href+ 'assets/images/ss-saved-jcb@2x.png) no-repeat right top',
				          'background-size' :'70px 100px',
				          'background-position-y':'1px'
		        },
		        'input#heartland-field[name=cardNumber]::-ms-clear' : {
		          		'display':'none'
		        },
		          '#heartland-field-body' : {
		          		'width':'100%'
		        },
		          '#heartland-field-wrapper' : {
		          		'width':'100%'
		        },

						'@media only screen and (min-width:641px)': {

					        '#HPS_secure_cc .ginput_accountinfo_left' :{
					        			'padding-right':'10px'
									},
									'#HPS_secure_cc .ginput_accountinfo_left' :{
												'padding-right':'10px'
									},
									'#HPS_secure_cc .ginput_accountinfo_left,
									 #HPS_secure_cc .ginput_accountinfo_right' :{
												'display':'inline',
												'width' : '50%',
												'float' : 'left'
									},
									'.gform_wrapper #HPS_secure_cc div.HPS_gform_card_icon' :{
												'background':'transparent url(../images/img/ss-shield@1x.png) no-repeat !important',
												'background-size' : '320px 40px !important',
												'width' : '540px !important',
												'height' : '42px !important'
									},
									'.gform_wrapper .field_sublabel_below .ginput_complex.ginput_container #HPS_secure_cc label,
								   .gform_wrapper .field_sublabel_below div[class*=gfield_time_].ginput_container #HPS_secure_cc label' :{
												'margin-top':'15px !important',
												'margin-bottom' : '5px !important'
									},
									'.gform_wrapper
								   .ginput_container #HPS_secure_cc span:not(.ginput_price)' :{
												'margin-top':'0px',
												'margin-bottom' : '0px'
									},
									'.gform_wrapper .ginput_complex #HPS_secure_cc .ginput_cardinfo_right' :{
												'width':'50% !important',
												'padding-left' : '5px'
									},
									'.gform_wrapper .ginput_complex #HPS_secure_cc .ginput_cardinfo_left' :{
												'padding-right' : '5px'
									},
		      }
		      },
		      // Callback when a token is received from the service
		      onTokenSuccess: function (resp) {
		        alert('Here is a single-use token: ' + resp.token_value);
		      },
		      // Callback when an error is received from the service
		      onTokenError: function (resp) {
		        alert('There was an error: ' + resp.error.message);
		      }
		    });

		    // Attach a handler to interrupt the form submission
		    Heartland.Events.addHandler(document.getElementById('#HPS_secure_cc').form, 'submit', function (e) {
		      // Prevent the form from continuing to the `action` address
		      e.preventDefault();
		      // Tell the iframes to tokenize the data
		      hps.Messages.post(
		        {
		          accumulateData: true,
		          action: 'tokenize',
		          message: 'pkapi_cert_jKc1FtuyAydZhZfbB3'
		        },
		        'cardNumber'
		      );
		    });
		  }(document, Heartland));
		</script>";

		echo  "<div class='ginput_complex{$class_suffix} ginput_container ginput_container_creditcard' id='{$field_id}'>" .$wrapper  . $card_name_field . $wrapper_close . $ss_js . ' </div>';
