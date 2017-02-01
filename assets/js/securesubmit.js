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
        this.hps = null;

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
                // Create a new `HPS` object with the necessary configuration
                SecureSubmitObj.hps = new Heartland.HPS({
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
                        SecureSubmitObj.secureSubmitResponseHandler(response);
                    },
                    // Callback when an error is received from the service
                    onTokenError: function (response) {
                        SecureSubmitObj.secureSubmitResponseHandler(response);
                    }
                });
            }

            // bind SecureSubmit functionality to submit event
            $('#gform_' + this.formId).submit(function (event) {
                var $form = $(this);
                var ccInputPrefix = 'input_' + SecureSubmitObj.formId + '_' + SecureSubmitObj.ccFieldId + '_';

                SecureSubmitObj.form = $form;

                if ($('#securesubmit_response').size() === 0) {
                    if (SecureSubmitObj.isSecure) {
                        // Tell the iframes to tokenize the data
                        SecureSubmitObj.hps.Messages.post(
                            {
                                accumulateData: true,
                                action: 'tokenize',
                                message: SecureSubmitObj.apiKey
                            },
                            'cardNumber'
                        );
                    } else {
                        var hps = new Heartland.HPS({
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
                        });

                        hps.tokenize();
                    }

                    return false;
                }

                return true;
            });
        };

        this.secureSubmitResponseHandler = function (response) {
            var $form = this.form;
            var ccInputPrefix = 'input_' + this.formId + '_' + this.ccFieldId + '_';
            var ccInputSuffixes = ['1', '2_month', '2_year', '3'];
            var i, input;

            $('#securesubmit_response').remove();

            if (!this.isSecure) {
                for (i = 0; i < ccInputSuffixes.length; i++) {
                    input = $form.find('#' + ccInputPrefix + ccInputSuffixes[i]);
                    input.val('');
                }
            }

            $('#securesubmit_response').remove();
            $form.append($('<input type="hidden" name="securesubmit_response" id="securesubmit_response" />').val($.toJSON(response)));
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

        this.init();
    };
})(window, window.jQuery);