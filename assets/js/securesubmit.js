/*jslint browser:true, unparam:true*/
/*global hps, gformInitSpinner*/
(function (window, $) {
    'use strict';
    var addHandler = window.GlobalPayments
        ? GlobalPayments.events.addHandler
        : function () { };

    window.SecureSubmit = function (args) {

        this.form = null;
        this.apiKey = null;
        this.pageNo = null;
        this.formId = null;
        this.ccFieldId = null;
        this.ccPage = null;
        this.isAjax = null;
        this.isSecure = false;
        this.isCCA = false;
        this.ccaData = null;
        this.hps = null;
        this.isInit = false;
        this.isCert = false;

        var prop;
        for (prop in args) {
            if (args.hasOwnProperty(prop)) {
                this[prop] = args[prop];
            }
        }

        this.ccInputSuffixes = ['1', '2_month', '2_year', '3'];
        this.ccInputPrefix = 'input_' + this.formId + '_' + this.ccFieldId + '_';

        this.init = function () {

            document.getElementById("gform_submit_button_" + this.formId).style.display = "none";
            document.getElementById("gp-error").style.display = 'none';

            var SecureSubmitObj = this;

            if (!this.isCreditCardOnPage()) {
                return;
            }

            // Initialize spinner
            if (!this.isAjax) {
                gformInitSpinner(this.formId);
            }

            if (this.isSecure) {
                // Configure GlobalPayments
                GlobalPayments.configure({
                    publicApiKey: SecureSubmitObj.apiKey
                });

                // Mapping of payment fields with GlobalPayment Js
                SecureSubmitObj.hps = GlobalPayments.ui.form({
                    fields: {
                        "card-holder-name": {
                            placeholder: "John Smith",
                            target: "#credit-card-card-holder"
                        },
                        "card-number": {
                            placeholder: "•••• •••• •••• ••••",
                            target: "#credit-card-card-number"
                        },
                        "card-expiration": {
                            placeholder: "MM / YYYY",
                            target: "#credit-card-card-expiration"
                        },
                        "card-cvv": {
                            placeholder: "•••",
                            target: "#credit-card-card-cvv",
                        }
                    },
                    /*
                     * Collection of CSS to inject into the iframes.
                     * These properties can match the site's styles
                     * to create a seamless experience.
                     */
                    styles: {
                        'html' : {
                            "-webkit-text-size-adjust": "100%"
                        },
                        'body' : {
                            'width' : '100%'
                        },
                        '#secure-payment-field-wrapper' : {
                            'position' : 'relative',
                            'width' : '100%'
                        },
                        '#secure-payment-field' : {
                            'box-sizing':'border-box',
                            'display': 'block',
                            'width': '100%',
                            'height': '48px',
                            'padding': '6px 12px',
                            'font-size': '14px',
                            'line-height': '1.42857143',
                            'color': '#555',
                            'background-color': '#fff',
                            'border': '1px solid #b5b5b5',
                            '-webkit-box-shadow': 'inset 0 1px 1px rgba(0,0,0,.075)',
                            'box-shadow': 'inset 0 1px 1px rgba(0,0,0,.075)',
                            '-webkit-transition': 'border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s',
                            '-o-transition': 'border-color ease-in-out .15s,box-shadow ease-in-out .15s',
                            'transition': 'border-color ease-in-out .15s,box-shadow ease-in-out .15s'
                        },
                        '#secure-payment-field:focus' : {
                            "border": "1px solid lightblue",
                            "box-shadow": "0 1px 3px 0 #cecece",
                            "outline": "none"
                        },
                        'input#heartland-field[name=cardCvv]' : {
                            'background' : 'transparent url(' + SecureSubmitObj.baseUrl + '/assets/images/cvv.png) no-repeat right',
                            'background-size' :'63px 40px'
                        },
                        '.card-cvv' : {
                            'background' : 'transparent url(' + SecureSubmitObj.baseUrl + '/assets/images/cvv.png) no-repeat right',
                            'background-size' : '63px 40px'
                        },
                        '.card-cvv.card-type-amex' : {
                            'background' : 'transparent url(' + SecureSubmitObj.baseUrl + '/assets/images/cvv-amex.png) no-repeat right',
                            'background-size' : '63px 40px'
                        },
                        '.card-number' : {
                            'background' : 'transparent url(' + SecureSubmitObj.baseUrl + '/assets/images/ss-inputcard-blank@2x.png) no-repeat right',
                            'background-size' : '55px 35px'
                        },
                        '.card-number.invalid.card-type-amex' : {
                            'background' : 'transparent url(' + SecureSubmitObj.baseUrl + '/assets/images/ss-saved-amex@2x.png) no-repeat right',
                            'background-position-y' : '-41px',
                            'background-size' : '50px 90px'
                        },
                        '.card-number.invalid.card-type-discover' : {
                            'background' : 'transparent url(' + SecureSubmitObj.baseUrl + '/assets/images/ss-saved-discover@2x.png) no-repeat right',
                            'background-position-y' : '-44px',
                            'background-size' : '85px 90px'
                        },
                        '.card-number.invalid.card-type-jcb' : {
                            'background' : 'transparent url(' + SecureSubmitObj.baseUrl + '/assets/images/ss-saved-jcb@2x.png) no-repeat right',
                            'background-position-y' : '-44px',
                            'background-size' : '55px 94px'
                        },
                        '.card-number.invalid.card-type-mastercard' : {
                            'background' : 'transparent url(' + SecureSubmitObj.baseUrl + '/assets/images/ss-saved-mastercard@2x.png) no-repeat right',
                            'background-position-y' : '-41px',
                            'background-size' : '82px 86px'
                        },
                        '.card-number.invalid.card-type-visa' : {
                            'background' : 'transparent url(' + SecureSubmitObj.baseUrl + '/assets/images/ss-saved-visa@2x.png) no-repeat right',
                            'background-position-y' : '-44px',
                            'background-size' : '83px 88px',
                        },
                        '.card-number.valid.card-type-amex' : {
                            'background' : 'transparent url(' + SecureSubmitObj.baseUrl + '/assets/images/ss-saved-amex@2x.png) no-repeat right',
                            'background-position-y' : '3px',
                            'background-size' : '50px 90px',
                        },
                        '.card-number.valid.card-type-discover' : {
                            'background' : 'transparent url(' + SecureSubmitObj.baseUrl + '/assets/images/ss-saved-discover@2x.png) no-repeat right',
                            'background-position-y' : '1px',
                            'background-size' : '85px 90px'
                        },
                        '.card-number.valid.card-type-jcb' : {
                            'background' : 'transparent url(' + SecureSubmitObj.baseUrl + '/assets/images/ss-saved-jcb@2x.png) no-repeat right top',
                            'background-position-y' : '2px',
                            'background-size' : '55px 94px'
                        },
                        '.card-number.valid.card-type-mastercard' : {
                            'background' : 'transparent url(' + SecureSubmitObj.baseUrl + '/assets/images/ss-saved-mastercard.png) no-repeat right',
                            'background-position-y' : '2px',
                            'background-size' : '82px 86px'
                        },
                        '.card-number.valid.card-type-visa' : {
                            'background' : 'transparent url(' + SecureSubmitObj.baseUrl + '/assets/images/ss-saved-visa@2x.png) no-repeat right top',
                            'background-size' : '82px 86px'
                        },
                        '.card-number::-ms-clear' : {
                            'display' : 'none'
                        },
                        'input[placeholder]' : {
                            'letter-spacing' : '.5px'
                        },
                    }

                });
                // apply style for iframes
                var content = document.getElementById('credit-card-card-holder');
                content.firstChild.style.width = "98%";
                var content = document.getElementById('credit-card-card-number');
                content.firstChild.style.width = "98%";

                SecureSubmitObj.hps.on("token-success", (resp) => {
                    clearFields();
                    SecureSubmitObj.secureSubmitResponseHandler(resp);
                });
                SecureSubmitObj.hps.on("token-error", (resp) => {
                    // show error to the consumer
                    var error_message = resp?.error?.message ?? resp?.reasons[0]?.message;
                    document.getElementById("gp-error").style.display = 'block';
                    document.getElementById("gp-error").textContent = error_message;
                    document.getElementById("credit-card-card-submit").disabled = false;

                    return true;
                });
            }

            function clearFields() {
                document.getElementById("gp-error").style.display = 'none';
                document.getElementById("credit-card-card-submit").disabled = true;
            }

            function triggerSubmit(){
                // manually include iframe submit button
                const fields = ['submit'];
                const target = SecureSubmitObj.hps.frames['card-number'];

                for (const type in SecureSubmitObj.hps.frames) {
                    if (SecureSubmitObj.hps.frames.hasOwnProperty(type)) {
                        fields.push(type);
                    }
                }

                for (const type in SecureSubmitObj.hps.frames) {
                    if (!SecureSubmitObj.hps.frames.hasOwnProperty(type)) {
                        continue;
                    }

                    const frame = SecureSubmitObj.hps.frames[type];

                    if (!frame) {
                        continue;
                    }

                    GlobalPayments.internal.postMessage.post({
                        data: {
                            fields: fields,
                            target: target.id
                        },
                        id: frame.id,
                        type: 'ui:iframe-field:request-data'
                    }, frame.id);
                }
            }

            // Bind SecureSubmit functionality to submit event.
            $('#gform_' + this.formId).submit(function (event) {
                event.preventDefault();

                SecureSubmitObj.form = $(this);
                $('#credit-card-card-submit').prop('disabled', true);

                // If we have what we need, we can submit the form.
                if ($('#securesubmit_cca_data').length
                    && $('#securesubmit_response').length) {
                    return true;
                }

                if (!$('#securesubmit_response').length) {

                    if (SecureSubmitObj.isSecure) {

                        event.preventDefault();
                        triggerSubmit();
                        return false;

                    } else {
                        // Not using iFrames. No Cardinal tokenization
                        event.preventDefault();
                        triggerSubmit();
                        return false;

                    }

                }

                // IF 3dSecure is enabled, init and start the CCA process
                if (SecureSubmitObj.isCCA) {
                    if (!$('#securesubmit_cca_data').length) {
                        SecureSubmitObj.cca();
                        return false;
                    }
                } else {
                    // 3DSecure is disabled
                    return true;
                }
                return false;
            });
        };

        // Handles tokenization response
        this.secureSubmitResponseHandler = function (response) {

            // Preevent any wierdness
            if ($('#securesubmit_response').length) {
                return false;
            }

            // Clear any potentially lingering elements
            $('#securesubmit_response').remove();
            $('#securesubmit_cca_data').remove();

            var $form = this.form;
            var i, input;

            var heartland = response.heartland || response;

            /*
             * If 3DSecure is enabled, the tokenization
             * will send back the CCA token as well
             */
             var  cardinal = null;
             if (this.isCCA && response.cardinal) {
                cardinal = response.cardinal;
             }

            if (this.isSecure) {
                // Clear the fields if using iFrames
                for (i = 0; i < this.ccInputSuffixes.length; i++) {
                    input = $form.find('#' + this.ccInputPrefix + this.ccInputSuffixes[i]);
                    input.val('');
                }
            }

            /*
             * Create hidden form inputs to capture
             * the values passed back from tokenization.
             */
            var last4 = document.createElement('input');
            last4.type = 'hidden';
            last4.id = 'last_four';
            last4.name = 'last_four';
            last4.value = heartland.details.cardLast4;
            $form.append($(last4));

            var cType = document.createElement('input');
            cType.type = 'hidden';
            cType.id = 'card_type';
            cType.name = 'card_type';
            cType.value = heartland.details.cardType;
            $form.append($(cType));

            var expMo = document.createElement('input');
            expMo.type = 'hidden';
            expMo.id = 'exp_month';
            expMo.name = 'exp_month';
            expMo.value = heartland.details.expiryMonth;
            $form.append($(expMo));

            var expYr = document.createElement('input');
            expYr.type = 'hidden';
            expYr.id = 'exp_year';
            expYr.name = 'exp_year';
            expYr.value = heartland.details.expiryYear;
            $form.append($(expYr));

            // Add tokenization response to the form
            this.createSecureSubmitResponseNode($.toJSON(heartland));

            /*
             * If 3dSecure is enabled, create a hidden form
             * element top capture the CCA token.
             */
            if (this.isSecure && this.isCCA && cardinal.token_value) {
                this.createCardinalTokenNode(cardinal.token_value);
                this.cca();
                return false;
            }

            $form.submit();
            document.getElementById("gform_" + this.formId).submit();
            return false;
        };

        this.isCreditCardOnPage = function () {
            /*
             * If current page is false or no credit card page number,
             * assume this is not a multi-page form
             */
            var currentPage = this.getCurrentPageNumber();
            if (!this.ccPage || !currentPage) {
                return true;
            }
            return this.ccPage === currentPage;
        };

        this.getCurrentPageNumber = function () {
            var currentPageInput = $('#gform_source_page_number_' + this.formId);
            var currentInput = currentPageInput.val();
            if(currentInput == 0)
            {
                currentInput = this.pageNo;
                $('#gform_source_page_number_' + this.formId).val(currentInput);
            }
            return currentPageInput.length > 0 ? parseInt(currentInput, 10) : false;
        };

        this.createCardinalTokenNode = function (value) {
            var $form = this.form;
            var cardinalToken = document.createElement('input');
            cardinalToken.type = 'hidden';
            cardinalToken.id = 'securesubmit_cardinal_token';
            cardinalToken.name = 'securesubmit_cardinal_token';
            cardinalToken.value = value;
            $form.append($(cardinalToken));
        }

        this.createSecureSubmitResponseNode = function (value) {
            var $form = this.form;
            var secureSubmitResponse = document.createElement('input');
            secureSubmitResponse.type = 'hidden';
            secureSubmitResponse.id = 'securesubmit_response';
            secureSubmitResponse.name = 'securesubmit_response';
            secureSubmitResponse.value = value;
            $form.append($(secureSubmitResponse));
        }

        this.getOrderTotal = function () {
            var $orderTotalElement = $('div.ginput_container_total').find(
                'input[id^="input_' + this.formId + '_"]'
            );
            if ($orderTotalElement) {
                return ($orderTotalElement.val() * 100);
            }
            return 0;
        }

        this.cca = function () {
            /*
             * If we arleady have the CCA data
             * then we can skip the CCA process.
             */
            if ($('#securesubmit_cca_data').length) {
                return true;
            }

            var $form = this.form;

            try {

                Cardinal.setup('init', {
                    jwt: this.ccaData.jwt
                });

                if (!this.isInit) {
                    /*
                     * The below callback function will be called
                     * after the authentication process completes.
                     */
                    Cardinal.on('payments.validated', function (data, jwt) {
                        data.jwt = jwt;
                        // Create a hidden input element to store the CCA data
                        var ccaData = document.createElement('input');
                        ccaData.type = 'hidden';
                        ccaData.id = 'securesubmit_cca_data';
                        ccaData.name = 'securesubmit_cca_data';
                        ccaData.value = Heartland.JSON.stringify(data);
                        $form.append($(ccaData));

                        if (!$('#securesubmit_cardinal_token').length
                            && (data.Token && data.Token.Token)) {
                            var cardinalToken = document.createElement('input');
                            cardinalToken.type = 'hidden';
                            cardinalToken.id = 'securesubmit_cardinal_token';
                            cardinalToken.name = 'securesubmit_cardinal_token';
                            cardinalToken.value = data.Token.Token;
                            $form.append($(cardinalToken));
                        }
                        $form.submit();
                    });
                    this.isInit = true;
                }

                Cardinal.trigger('jwt.update', this.ccaData.jwt);

                var options = {
                    OrderDetails: {
                        OrderNumber: this.ccaData.orderNumber + 'cca',
                        Amount: this.getOrderTotal()
                    }
                };

                if (this.isSecure) {
                    if ($('#securesubmit_cardinal_token').length) {
                        options.Token = {
                            CardCode: 0,
                            Token: $('#securesubmit_cardinal_token').val(),
                            ExpirationMonth: $('#exp_month').val(),
                            ExpirationYear: $('#exp_year').val()
                        };
                    }
                } else {
                    /*
                     * Not using iFrames
                     * Build Account data
                     */
                    options.Consumer = {
                        Account: {
                            AccountNumber: $form.find('#' + this.ccInputPrefix + '1').val().replace(/\D/g, ''),
                            ExpirationMonth: $form.find('#' + this.ccInputPrefix + '2_month').val(),
                            ExpirationYear: $form.find('#' + this.ccInputPrefix + '2_year').val()
                        }
                    };
                }

                Cardinal.start('cca', options);
                return true;

            } catch(e){
                console.log( (window["Cardinal"] === undefined ?
                    "Cardinal Cruise did not load properly. " :
                    "An error occurred during processing. ") + e
                );
            }
        }

        this.init();
    };
})(window, window.jQuery);
