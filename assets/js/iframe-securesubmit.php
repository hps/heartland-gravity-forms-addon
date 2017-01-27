
    (function (document, Heartland) {
        // Create a new `HPS` object with the necessary configuration
        var hps = new Heartland.HPS({
            publicKey: '<?php $pubKey?>',
            type:      'iframe',
            // Configure the iframe fields to tell the library where
            // the iframe should be inserted into the DOM and some
            // basic options
            fields: {
                cardNumber: {
                    target:      'iframesCardNumber',
                    placeholder: '•••• •••• •••• ••••'
                    <?php $disabled_text?>
                },
                cardExpiration: {
                    target:      'iframesCardExpiration',
                    placeholder: 'MM / YYYY'
                    <?php $disabled_text?>
                },
                cardCvv: {
                    target:      'iframesCardCvv',
                    placeholder: 'CVV'
                    <?php $disabled_text?>
                }
            },
            // Collection of CSS to inject into the iframes.
            // These properties can match the site's styles
            // to create a seamless experience.
            style: {
                'input[type=text]': {
                    'box-sizing':'border-box',
                    'display': 'block',
                    'width': '100%',
                    'height': '34px',
                    'padding': '6px 12px',
                    'font-size': '14px',
                    'line-height': '1.42857143',
                    'color': '#555',
                    'background-color': '#fff',
                    'background-image': 'none',
                    'border': '1px solid #ccc',
                    'border-radius': '4px',
                    '-webkit-box-shadow': 'inset 0 1px 1px rgba(0,0,0,.075)',
                    'box-shadow': 'inset 0 1px 1px rgba(0,0,0,.075)',
                    '-webkit-transition': 'border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s',
                    '-o-transition': 'border-color ease-in-out .15s,box-shadow ease-in-out .15s',
                    'transition': 'border-color ease-in-out .15s,box-shadow ease-in-out .15s'
                },
                'input[type=text]:focus, input[type=tel].focus':{
                    'border-color': '#3989e3',
                    'outline': '0',
                    '-webkit-box-shadow': 'none',
                    'box-shadow': 'none'
                },
                'input[type=submit]' : {
                    'box-sizing':'border-box',
                    'display': 'inline-block',
                    'padding': '6px 12px',
                    'margin-bottom': '0',
                    'font-size': '14px',
                    'font-weight': '400',
                    'line-height': '1.42857143',
                    'text-align': 'center',
                    'white-space': 'nowrap',
                    'vertical-align': 'middle',
                    '-ms-touch-action': 'manipulation',
                    'touch-action': 'manipulation',
                    'cursor': 'pointer',
                    '-webkit-user-select': 'none',
                    '-moz-user-select': 'none',
                    '-ms-user-select': 'none',
                    'user-select': 'none',
                    'background-image': 'none',
                    'border': '1px solid transparent',
                    'border-radius': '4px',
                    'color': '#fff',
                    'background-color': '#337ab7',
                    'border-color': '#2e6da4'
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
                '#heartland-field' :{
                    'padding-left':'10px'
                },
                '#heartland-field[placeholder]' :{
                    'letter-spacing':'3px'
                },
                'input#heartland-field[name=cardCvv]' : {
                    'background':'transparent url('+location.href+ 'assets/images/cvv1.png) no-repeat right',
                    'background-size' :'63px 40px'
                },
                'input#heartland-field[name=cardNumber]' : {
                    'background':'transparent url('+location.href+ 'assets/images/ss-inputcard-blank@2x.png) no-repeat right',
                    'background-size' :'55px 35px',
                    'height':'40px',
                    'width':'95%'
                },
                '#heartland-field.invalid.card-type-visa' :{
                    'background':'transparent url('+location.href+ 'assets/images/ss-saved-visa@2x.png) no-repeat right',
                    'background-size' :'83px 88px',
                    'background-position-y':'-44px'
                },
                '#heartland-field.valid.card-type-visa' :{
                    'background':'transparent url('+location.href+ 'assets/images/ss-saved-visa@2x.png) no-repeat right top',
                    'background-size' :'82px 86px'
                },
                '#heartland-field.invalid.card-type-discover' :{
                    'background':'transparent url('+location.href+ 'assets/images/ss-saved-discover@2x.png) no-repeat right bottom',
                    'background-size' :'85px 85px'
                },
                '#heartland-field.valid.card-type-discover' :{
                    'background':'transparent url('+location.href+ 'assets/images/ss-saved-discover@2x.png) no-repeat right top',
                    'background-size' :'85px 83px'
                },
                '#heartland-field.invalid.card-type-amex' :{
                    'background':'transparent url('+location.href+ 'assets/images/ss-savedcards-amex@2x.png) no-repeat right',
                    'background-size' :'50px 90px',
                    'background-position-y':'-44'
                },
                '#heartland-field.valid.card-type-amex' :{
                    'background':'transparent url('+location.href+ 'assets/images/ss-savedcards-amex@2x.png) no-repeat right top',
                    'background-size' :'50px 90px'
                },
                '#heartland-field.invalid.card-type-mastercard' :{
                    'background':'transparent url('+location.href+ 'assets/images/ss-saved-mastercard.png) no-repeat right',
                    'background-size' :'85px 81px',
                    'background-position-y':'-55px'
                },
                '#heartland-field.valid.card-type-mastercard' :{
                    'background':'transparent url('+location.href+ 'assets/images/ss-saved-mastercard.png) no-repeat right',
                    'background-size' :'62px 105px',
                    'background-position-y':'-4px'
                },
                '#heartland-field.invalid.card-type-jcb' :{
                    'background':'transparent url('+location.href+ 'assets/images/ss-saved-jcb@2x.png) no-repeat right',
                    'background-size' :'75px 78px',
                    'background-position-y':'-38px'
                },
                '#heartland-field.valid.card-type-jcb' :{
                    'background':'transparent url('+location.href+ 'assets/images/ss-saved-jcb@2x.png) no-repeat right top',
                    'background-size' :'75px 78px',
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
                '@media only screen and (min-width:767px)': {
                    'input:nth-child(n+1)#heartland-field' :{
                        'width':'97.5%'
                    }
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
        Heartland.Events.addHandler(document.getElementById('gform_3'), 'submit', function (e) {
            // Prevent the form from continuing to the `action` address
            e.preventDefault();
            // Tell the iframes to tokenize the data
            hps.Messages.post(
                {
                    accumulateData: true,
                    action: 'tokenize',
                    message: '<?php $pubKey?>'
                },
                'cardNumber'
            );
        });
    }(document, Heartland));