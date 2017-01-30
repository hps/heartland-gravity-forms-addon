
<div class="ginput_complex<?php echo $class_suffix; ?> ginput_container ginput_container_creditcard hps_secure_cc" id="<?php echo $field_id; ?>">
    <div id="HPS_secure_cc">
        <div class="ss-shield"></div>
        <!-- make iframes styled like other form -->
        <style type="text/css">
        	#iframes iframe{
        		float:left;
        		width:100%;
        	}
          .iframeholder {
            height:50px;
            width:100%;
          }
          .ie8 form .iframeholder {
            padding:10px;
          }
        	.iframeholder::after{
        		content:'';
        		display:block;
        		width:100%;
        		height:0px;
        		clear:both;
        		position:relative;
        	}
          .ie8 form .form-group {
            margin-bottom:5px;
          }
          #iframesCardExpiration,
          #iframesCardCvv{
            margin-bottom:14px;
          }
          label[for=iframesCardNumber],
          label[for=iframesCardExpiration],
          label[for=iframesCardCvv]{
            text-transform:uppercase !important;
            font-weight:500 !important;
            font-size:14px !important;
            color:#000000 !important;
            margin-bottom:0px !important;
            font-family:sans-serif !important;
          }
          .ie8 form label {
            padding-left:10px;
            margin:0px;
          }
          #heartland-frame-cardExpiration,
          #heartland-frame-cardCvv,
          #heartland-frame-cardNumber,
          .ie8 #heartland-frame-cardExpiration,
          .ie8 #heartland-frame-cardCvv,
          .ie8 #heartland-frame-cardNumber  {
            width:100%;
          }
          #ss-banner {
            background:transparent url(assets/images/ss-shield@2x.png) no-repeat left center;
            height:40px;
            background-size:280px 34px;
            margin-bottom:10px;
          }
          .ie8 #ss-banner {
            background:transparent url(assets/images/ss-shield-ie.png) no-repeat left center;
          }
          .btn-primary{
            display:block;
            border-radius:0px;
            font-size:18px;
            float:right;
            background-color:#36b46e;
            border:1px solid #2a8d56;
            margin-bottom:10px;
            width:100%;
          }
          .btn-primary:hover,
          .btn-primary:focus{
            color: #fff;
            background-color: #2a8d56;
          }
          .ie8 .btn-primary {
            width:15%;
          }
          .red {
            margin-left:2px;
            font-size:17px !important;
          }
          @media screen and (min-width:767px) {
            #ss-date.form-group,
            #ss-cvv.form-group{
              display:inline-block;
              width:48%;
            }
            #ss-cvv.form-group {
              float:right;
            }
            #heartland-frame-cardNumber {
              width : 100%;
            }
          }
          @media screen and (min-width:450px) {
            .btn-primary,
            .ie8 .btn-primary {
              width:10em;
            }
          }
        }
        </style>
        <!-- The Payment Form -->
        <div id="iframes">
            <div id="ss-card" class="form-group">
                <label for="iframesCardNumber">Card Number<span class="red">*</span></label>
                <div class="iframeholder" id="iframesCardNumber"></div>
            </div>
            <div id="ss-date" class="form-group">
                <label for="iframesCardExpiration">Card Expiration<span class="red">*</span></label>
                <div class="iframeholder" id="iframesCardExpiration"></div>
            </div>
            <div id="ss-cvv" class="form-group">
                <label for="iframesCardCvv">Card CVV<span class="red">*</span></label>
                <div class="iframeholder" id="iframesCardCvv"></div>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
    (function (document, Heartland,$) {
        // Create a new `HPS` object with the necessary configuration
        var hps = new Heartland.HPS({
            publicKey: '<?php echo $pubKey?>',
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
              '#ss-cvv':{
                'width':'50%'
              },
              '#ss-date':{
                'width':'50%'
              },
                'iframe' : {
                    'width':'100%'
                },
                '#heartland-field': {
                    'box-sizing':'border-box',
                    'display': 'block',
                    'width': '100%',
                    'height': '48px',
                    'padding': '6px 12px',
                    'font-size': '14px',
                    'line-height': '1.42857143',
                    'color': '#555',
                    'background-color': '#fff',
                    'background-image': 'none',
                    'border': '1px solid #b5b5b5',
                    '-webkit-box-shadow': 'inset 0 1px 1px rgba(0,0,0,.075)',
                    'box-shadow': 'inset 0 1px 1px rgba(0,0,0,.075)',
                    '-webkit-transition': 'border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s',
                    '-o-transition': 'border-color ease-in-out .15s,box-shadow ease-in-out .15s',
                    'transition': 'border-color ease-in-out .15s,box-shadow ease-in-out .15s'
                },
                '#heartland-field:focus':{
                    'border-color': '#3989e3',
                    'outline': '0',
                    '-webkit-box-shadow': 'none',
                    'box-shadow': 'none'
                },
                '#heartland-field[placeholder]' :{
                    'letter-spacing':'3px',
                    'font-size':'small !important',
                    'text-transform':'uppercase !important',
                    'color':'#333333'
                },
                'input#heartland-field[name=cardCvv]' : {
                    'background':'transparent url(<?php echo $baseURL ?>/assets/images/cvv1.png) no-repeat right',
                    'background-size' :'63px 40px'
                },
                'input#heartland-field[name=cardNumber]' : {
                    'background':'transparent url(<?php echo $baseURL ?>/assets/images/ss-inputcard-blank@2x.png) no-repeat right',
                    'background-size' :'55px 35px'
                },               
                '#heartland-field.invalid.card-type-visa' :{
                    'background':'transparent url(<?php echo $baseURL ?>/assets/images/ss-saved-visa@2x.png) no-repeat right',
                    'background-size' :'83px 88px',
                    'background-position-y':'-44px'
                },
                '#heartland-field.valid.card-type-visa' :{
                    'background':'transparent url(<?php echo $baseURL ?>/assets/images/ss-saved-visa@2x.png) no-repeat right top',
                    'background-size' :'82px 86px'
                },
                '#heartland-field.invalid.card-type-discover' :{
                    'background':'transparent url(<?php echo $baseURL ?>/assets/images/ss-saved-discover@2x.png) no-repeat right bottom',
                    'background-size' :'85px',
                    'background-position-y':'-44px'
                },
                '#heartland-field.valid.card-type-discover' :{
                    'background':'transparent url(<?php echo $baseURL ?>/assets/images/ss-saved-discover@2x.png) no-repeat right top',
                    'background-size' :'85px'
                },
                '#heartland-field.invalid.card-type-amex' :{
                    'background':'transparent url(<?php echo $baseURL ?>/assets/images/ss-savedcards-amex@2x.png) no-repeat right',
                    'background-size' :'50px 90px',
                    'background-position-y':'-44px'
                },
                '#heartland-field.valid.card-type-amex' :{
                    'background':'transparent url(<?php echo $baseURL ?>/assets/images/ss-savedcards-amex@2x.png) no-repeat right top',
                    'background-size' :'50px 90px'
                },
                '#heartland-field.invalid.card-type-mastercard' :{
                    'background':'transparent url(<?php echo $baseURL ?>/assets/images/ss-saved-mastercard.png) no-repeat right',
                    'background-size' :'62px 105px',
                    'background-position-y':'-55px'
                },
                '#heartland-field.valid.card-type-mastercard' :{
                    'background':'transparent url(<?php echo $baseURL ?>/assets/images/ss-saved-mastercard.png) no-repeat right',
                    'background-size' :'62px 105px',
                    'background-position-y':'-4px'
                },
                '#heartland-field.invalid.card-type-jcb' :{
                    'background':'transparent url(<?php echo $baseURL ?>/assets/images/ss-saved-jcb@2x.png) no-repeat right',
                    'background-size' :'65px 98px',
                    'background-position-y':'-47px'
                },
                '#heartland-field.valid.card-type-jcb' :{
                    'background':'transparent url(<?php echo $baseURL ?>/assets/images/ss-saved-jcb@2x.png) no-repeat right top',
                    'background-size' :'65px 98px',
                    'background-position-y':'1px'
                },
                'input#heartland-field[name=cardNumber]::-ms-clear' : {
                    'display':'none'
                },
                '#heartland-field-wrapper' : {
                    'width':'100%'
                },
                
                '@media only screen and (min-width:767px)': {
                  
                }
            },
            // Callback when a token is received from the service
            onTokenSuccess: function (response) {
                jQuery("#<?php echo $field_id; ?> #securesubmit_response").remove();
                jQuery("#<?php echo $field_id; ?>").append(jQuery('<input type="hidden" name="securesubmit_response" id="securesubmit_response" />').val(jQuery.toJSON(response)));
                jQuery("#gform_<?php echo $form_id ?>").unbind('submit'); // unbind event handler
                document.getElementById('gform_<?php echo $form_id ?>').submit();
            },
            // Callback when an error is received from the service
            onTokenError: function (resp) {
                alert('There was an error: ' + resp.error.message);
            }
        });
        // Attach a handler to interrupt the form submission
        Heartland.Events.addHandler(document.getElementById('gform_<?php echo $form_id ?>'), 'submit', function (e) {
            // Prevent the form from continuing to the `action` address
            e.preventDefault();
            // Tell the iframes to tokenize the data
            hps.Messages.post(
                {
                    accumulateData: true,
                    action: 'tokenize',
                    message: '<?php echo $pubKey?>'
                },
                'cardNumber'
            );
        });
    }(document, Heartland,jQuery));
</script>