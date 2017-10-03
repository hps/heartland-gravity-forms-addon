/*jslint browser:true, unparam:true*/
/*global hps, gformInitSpinner*/
(function (window, $) {
    'use strict';

    window.SecureSubmit = function (args) {
        var prop;

        this.form = null;
        this.apiKey = null;
        this.formId = null;
        this.ccFieldId = null;
        this.ccPage = null;
        this.isAjax = null;
        this.isSecure = false;
        this.isCCA = false;
        this.ccaData = null;
        this.hps = null;
        this.isInit = false;

        for (prop in args) {
            if (args.hasOwnProperty(prop)) {
                this[prop] = args[prop];
            }
        }

        this.init = function () {
            var SecureSubmitObj = this;

            if (!this.isCreditCardOnPage()) {
                return;
            }

            // initialize spinner
            if (!this.isAjax) {
                gformInitSpinner(this.formId);
            }

            if (this.isSecure) {
                var options = {
                    publicKey: SecureSubmitObj.apiKey,
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
                            'background':'transparent url(' + SecureSubmitObj.baseUrl
                                + '/assets/images/cvv1.png) no-repeat right',
                            'background-size' :'63px 40px'
                        },
                        'input#heartland-field[name=cardNumber]' : {
                            'background':'transparent url(' + SecureSubmitObj.baseUrl
                                + '/assets/images/ss-inputcard-blank@2x.png) no-repeat right',
                            'background-size' :'55px 35px'
                        },
                        '#heartland-field.invalid.card-type-visa' :{
                            'background':'transparent url(' + SecureSubmitObj.baseUrl
                                + '/assets/images/ss-saved-visa@2x.png) no-repeat right',
                            'background-size' :'83px 88px',
                            'background-position-y':'-44px'
                        },
                        '#heartland-field.valid.card-type-visa' :{
                            'background':'transparent url(' + SecureSubmitObj.baseUrl
                                + '/assets/images/ss-saved-visa@2x.png) no-repeat right top',
                            'background-size' :'82px 86px'
                        },
                        '#heartland-field.invalid.card-type-discover' :{
                            'background':'transparent url(' + SecureSubmitObj.baseUrl
                                + '/assets/images/ss-saved-discover@2x.png) no-repeat right bottom',
                            'background-size' :'85px',
                            'background-position-y':'-44px'
                        },
                        '#heartland-field.valid.card-type-discover' :{
                            'background':'transparent url(' + SecureSubmitObj.baseUrl
                                + '/assets/images/ss-saved-discover@2x.png) no-repeat right top',
                            'background-size' :'85px'
                        },
                        '#heartland-field.invalid.card-type-amex' :{
                            'background':'transparent url(' + SecureSubmitObj.baseUrl
                                + '/assets/images/ss-savedcards-amex@2x.png) no-repeat right',
                            'background-size' :'50px 90px',
                            'background-position-y':'-44px'
                        },
                        '#heartland-field.valid.card-type-amex' :{
                            'background':'transparent url(' + SecureSubmitObj.baseUrl
                                + '/assets/images/ss-savedcards-amex@2x.png) no-repeat right top',
                            'background-size' :'50px 90px'
                        },
                        '#heartland-field.invalid.card-type-mastercard' :{
                            'background':'transparent url(' + SecureSubmitObj.baseUrl
                                + '/assets/images/ss-saved-mastercard.png) no-repeat right',
                            'background-size' :'62px 105px',
                            'background-position-y':'-55px'
                        },
                        '#heartland-field.valid.card-type-mastercard' :{
                            'background':'transparent url(' + SecureSubmitObj.baseUrl
                                + '/assets/images/ss-saved-mastercard.png) no-repeat right',
                            'background-size' :'62px 105px',
                            'background-position-y':'-4px'
                        },
                        '#heartland-field.invalid.card-type-jcb' :{
                            'background':'transparent url(' + SecureSubmitObj.baseUrl
                                + '/assets/images/ss-saved-jcb@2x.png) no-repeat right',
                            'background-size' :'65px 98px',
                            'background-position-y':'-47px'
                        },
                        '#heartland-field.valid.card-type-jcb' :{
                            'background':'transparent url(' + SecureSubmitObj.baseUrl
                                + '/assets/images/ss-saved-jcb@2x.png) no-repeat right top',
                            'background-size' :'65px 98px',
                            'background-position-y':'1px'
                        },
                        'input#heartland-field[name=cardNumber]::-ms-clear' : {
                            'display':'none'
                        },
                        '#heartland-field-wrapper' : {
                            'width':'100%'
                        }
                    },
                    // Callback when a token is received from the service
                    onTokenSuccess: function (response) {
                        console.log('Tokenize - GOOD');
                        console.log(response);
                        SecureSubmitObj.secureSubmitResponseHandler(response);
                    },
                    // Callback when an error is received from the service
                    onTokenError: function (response) {
                        console.log('Tokenize - ERROR');
                        console.log(response);
                        SecureSubmitObj.secureSubmitResponseHandler(response);
                    }
                };

                // 3DSecure
                if (SecureSubmitObj.isCCA && SecureSubmitObj.ccaData) {
                    options.cca = {
                        jwt: SecureSubmitObj.ccaData.jwt,
                        orderNumber: SecureSubmitObj.ccaData.orderNumber
                    };
                }

                // Create a new `HPS` object with the necessary configuration
                SecureSubmitObj.hps = new Heartland.HPS(options);

                var count = 0;
                Heartland.Events.addHandler(document, 'securesubmitIframeReady', function () {
                    if (++count === 3) {
                        $('#HPS_secure_cc iframe').each(function (i, el) {
                            var $el = $(el);
                            $el.attr('tabindex', $el.parent().attr('tabindex'));
                            $el.parent().removeAttr('tabindex')
                        });
                    }
                });
                
                console.log('Init SecureSubmitObj');
                console.log(SecureSubmitObj);
            }
            
            // bind SecureSubmit functionality to submit event
            $('#gform_' + this.formId).submit(function (event) {
                var $form = $(this);
                var ccInputPrefix = 'input_' + SecureSubmitObj.formId + '_' + SecureSubmitObj.ccFieldId + '_';

                SecureSubmitObj.form = $form;

                if ($('#securesubmit_response').size() === 0) {
                    
                    if (SecureSubmitObj.isSecure) {
                        // Using iFrames
                        // Tell the iframes to tokenize the data
                        console.log('Tokenize iFrames');
                        SecureSubmitObj.hps.Messages.post(
                            {
                                accumulateData: true,
                                action: 'tokenize',
                                message: SecureSubmitObj.apiKey,
                                data: SecureSubmitObj.hps.options
                            },
                            'cardNumber'
                        );
                        console.log('Done tokenizing');
                        if (SecureSubmitObj.isCCA) {
                            console.log('Get CCA Token');
                            SecureSubmitObj.cca();
                        }
                        return false;
                    } else {
                        // Not using iFrames
                        var options = {
                            publicKey: SecureSubmitObj.apiKey,
                            cardNumber: $form.find('#' + ccInputPrefix + '1').val().replace(/\D/g, ''),
                            cardCvv: $form.find('#' + ccInputPrefix + '3').val(),
                            cardExpMonth: $form.find('#' + ccInputPrefix + '2_month').val(),
                            cardExpYear: $form.find('#' + ccInputPrefix + '2_year').val(),
                            success: function (response) {
                                SecureSubmitObj.secureSubmitResponseHandler(response);
                            },
                            error: function (response) {
                                SecureSubmitObj.secureSubmitResponseHandler(response);
                            }
                        };
    
                        // 3DSecure
                        if (SecureSubmitObj.isCCA && SecureSubmitObj.ccaData) {
                            options.cca = {
                                jwt: SecureSubmitObj.ccaData.jwt,
                                orderNumber: SecureSubmitObj.ccaData.orderNumber
                            };
                        }

                        var hps = new Heartland.HPS(options);

                        console.log('Tokenize non iFrames');
                        hps.tokenize();
                        if (SecureSubmitObj.isCCA) {
                            console.log('Get CCA Token');
                            SecureSubmitObj.cca();
                        }
                        return false;
                    }
                    
                }
               
                return true;
            });
        };

        // Handles tokenization response
        this.secureSubmitResponseHandler = function (response) {
            var $form = this.form;
            var ccInputPrefix = 'input_' + this.formId + '_' + this.ccFieldId + '_';
            var ccInputSuffixes = ['1', '2_month', '2_year', '3'];
            var i, input;

            var heartland = response.heartland || response;
            var cardinal = response.cardinal;
            
            console.log('heartland');
            console.log(heartland);
            console.log('cardinal');
            console.log(cardinal);
            
            $('#securesubmit_response').remove();

            if (!this.isSecure) {
                // Clear the fields if not using iFrames
                for (i = 0; i < ccInputSuffixes.length; i++) {
                    input = $form.find('#' + ccInputPrefix + ccInputSuffixes[i]);
                    input.val('');
                }
            }

            $('#securesubmit_response').remove();
            $form.append($('<input type="hidden" name="securesubmit_response" id="securesubmit_response" />').val($.toJSON(response)));
            console.log('secureSubmitResponseHandler called');
            console.log($('#securesubmit_response').val());
            if (this.isCCA) {
                this.createCardinalTokenNode($form, response.cardinal.token_value);
                this.cca();
                return;
            }
            console.log('Ready to submit form');
            return false;
            $form.submit();
        };

        this.isCreditCardOnPage = function () {
            var currentPage = this.getCurrentPageNumber();

            // if current page is false or no credit card page number, assume this is not a multi-page form
            if (!this.ccPage || !currentPage) {
                return true;
            }

            return this.ccPage === currentPage;
        };

        this.getCurrentPageNumber = function () {
            var currentPageInput = $('#gform_source_page_number_' + this.formId);
            return currentPageInput.length > 0 ? parseInt(currentPageInput.val(), 10) : false;
        };

        this.createCardinalTokenNode = function (form, value) {
            console.log('createCardinalTokenNode');
            var cardinalToken = document.createElement('input');
            cardinalToken.type = 'hidden';
            cardinalToken.id = 'securesubmit_cardinal_token';
            cardinalToken.name = 'securesubmit_cardinal_token';
            cardinalToken.value = value;
            form.appendChild(cardinalToken);
        }
        
        this.cca = function () {

            var $form = this.form;
            try {
                Cardinal.setup('init', {
                    jwt: this.ccaData.jwt
                });

                Cardinal.configure({
                    logging: {
                        debug: "verbose"
                    }
                });

                // The below callback function will be called
                // after the authentication process completes.
                Cardinal.on('payments.validated', function (data, jwt) {
                    console.log('payments.validated');
                    data.jwt = jwt;
                    
                    var cca = document.createElement('input');
                    cca.type = 'hidden';
                    cca.id = 'securesubmit_cca_data';
                    cca.name = 'securesubmit_cca_data';
                    cca.value = Heartland.JSON.stringify(data);
                    $form.append($(cca));

                    if ( !$('#securesubmit_cardinal_token') ) {
                        if ( data.Token && data.Token.Token ) {
                            createCardinalTokenNode(form, data.Token.Token);
                        }
                    }
                    console.log('payments.validated.data');
                    console.log(data);
                    //$form.submit();
                });
                
                console.log('jwt.update');
                Cardinal.trigger('jwt.update', this.ccaData.jwt);

                var options = {
                    OrderDetails: {
                        OrderNumber: this.ccaData.orderNumber + 'cca'
                    }
                };
                if ( !$('#securesubmit_cardinal_token') ) {
                    if ( data.Token && data.Token.Token ) {
                        options.Token = {
                            Token: token,
                            ExpirationMonth: document.getElementById('exp_month').value,
                            ExpirationYear: document.getElementById('exp_year').value
                        }
                    }
                }
                console.log('Cardinal.start');
                Cardinal.start('cca', options);
            } catch(e){
                // An error occurred
                console.log( (window["Cardinal"] === undefined ? "Cardinal Cruise did not load properly. " : "An error occurred during processing. ") + e );
            }
        }
        
        this.init();
    };
})(window, window.jQuery);