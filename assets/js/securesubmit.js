/*jslint browser:true, unparam:true*/
/*global hps, gformInitSpinner*/
(function (window, $) {
    'use strict';

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
        this.gatewayConfig = null;

        var prop;
        for (prop in args) {
            if (args.hasOwnProperty(prop)) {
                this[prop] = args[prop];
            }
        }

        this.ccInputSuffixes = ['1', '2_month', '2_year', '3'];
        this.ccInputPrefix = 'input_' + this.formId + '_' + this.ccFieldId + '_';

        this.init = function () {

            var SecureSubmitObj = this;

            if (!this.isCreditCardOnPage()) {
                return;
            }

            // Initialize spinner
            if (!this.isAjax) {
                //gformInitSpinner(this.formId);
            }
            
            if (!GlobalPayments) {
                console.log('Warning! Securesubmit Payment fields cannot be loaded ');
                return;
            }
            GlobalPayments.configure(JSON.parse(this.gatewayConfig));
            var cardForm = GlobalPayments.ui.form({
            	/*
                 * Configure the iframe fields to tell the library where
                 * the iframe should be inserted into the DOM and some
                 * basic options.
                 */
                 fields: {
                     "card-number": {
                         target:      '#iframesCardNumber',
                         placeholder: '•••• •••• •••• ••••'
                     },
                     "card-expiration": {
                         target:      '#iframesCardExpiration',
                         placeholder: 'MM / YYYY'
                     },
                     "card-cvv": {
                         target:      '#iframesCardCvv',
                         placeholder: 'CVV'
                     },
                     "submit": {
                         text: "Get Token and Submit",
                         target: "#iframesGetTokenButton"
                     }
                 }                 
                 
              });

              cardForm.on("token-success", this.secureSubmitResponseHandler.bind(this));
              cardForm.on("token-error", this.secureSubmitResponseHandler.bind(this));
        };

        // Handles tokenization response
        this.secureSubmitResponseHandler = function (response) {
            // Preevent any wierdness
            if ($('#securesubmit_response').length !== 0) {
                return false;
            }

            // Clear any potentially lingering elements
            $('#securesubmit_response').remove();
            $('#securesubmit_cca_data').remove();

            var $form = $('#gform_' + this.formId);
            var i, input;

            var heartland = response.details || response;
            /*
             * If 3DSecure is enabled, the tokenization
             * will send back the CCA token as well
             */
            /*
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
			*/
            /*
             * Create hidden form inputs to capture
             * the values passed back from tokenization.
             */
            var last4 = document.createElement('input');
            last4.type = 'hidden';
            last4.id = 'last_four';
            last4.name = 'last_four';
            last4.value = heartland.cardLast4;
            $form.append($(last4));

            var cType = document.createElement('input');
            cType.type = 'hidden';
            cType.id = 'card_type';
            cType.name = 'card_type';
            cType.value = heartland.cardType;
            $form.append($(cType));

            var expMo = document.createElement('input');
            expMo.type = 'hidden';
            expMo.id = 'exp_month';
            expMo.name = 'exp_month';
            expMo.value = heartland.expiryMonth;
            $form.append($(expMo));

            var expYr = document.createElement('input');
            expYr.type = 'hidden';
            expYr.id = 'exp_year';
            expYr.name = 'exp_year';
            expYr.value = heartland.expiryYear;
            $form.append($(expYr));
            
            //assign value to token_value for server process
            response.token_value = response.paymentReference;
            
            // Add tokenization response to the form
            this.createSecureSubmitResponseNode($.toJSON(response));

            /*
             * If 3dSecure is enabled, create a hidden form
             * element top capture the CCA token.
             */
            if (this.isSecure && this.isCCA && cardinal.token_value) {
                //this.createCardinalTokenNode(cardinal.token_value);
                //this.cca();
                //return false;
            }
            $form.submit();
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
        	
            var $form = $('#gform_' + this.formId);
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
