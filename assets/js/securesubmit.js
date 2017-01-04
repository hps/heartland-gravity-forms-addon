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

        for (prop in args) {
            if (args.hasOwnProperty(prop)) {
                this[prop] = args[prop];
            }
        }

        this.init = function () {

            var SecureSubmitObj = this;
            Heartland.Card.attachNumberEvents('#cc_number');
            Heartland.Card.attachExpirationEvents('#_exp_date');
            Heartland.Card.attachCvvEvents('#_cvv_number');

            if (!this.isCreditCardOnPage()) {
                return;
            }

            // initialize spinner
            if (!this.isAjax) {
                gformInitSpinner(this.formId);
            }


            // bind SecureSubmit functionality to submit event
            $('#gform_' + this.formId).submit(function (event) {
                var $form = $(this);
                var ccInputPrefix = 'input_' + SecureSubmitObj.formId + '_' + SecureSubmitObj.ccFieldId + '_';

                SecureSubmitObj.form = $form;

                if ($('#securesubmit_response').size() === 0) {
                    var _cardNumber=(SecureSubmitObj.ccFieldId!==null?$form.find('#' + ccInputPrefix + '1'):$form.find('#cc_number')).val().replace(/\D/g, '');
                    var _cardCvv=(SecureSubmitObj.ccFieldId!==null?$form.find('#' + ccInputPrefix + '3'):$form.find('#_cvv_number')).val();
                    var _cardExpMonth=(SecureSubmitObj.ccFieldId!==null?$form.find('#' + ccInputPrefix + '2_month').val():$form.find('#_exp_date').val().split(' / ')[0]);
                    var _cardExpYear= (SecureSubmitObj.ccFieldId!==null?$form.find('#' + ccInputPrefix + '2_year').val():$form.find('#_exp_date').val().split(' / ')[1]);

                    var hps = new Heartland.HPS({
                        publicKey: SecureSubmitObj.apiKey,
                        cardNumber: _cardNumber,
                        cardCvv: _cardCvv,
                        cardExpMonth:_cardExpMonth,
                        cardExpYear: _cardExpYear,
                        success: function (response) {
                            SecureSubmitObj.secureSubmitResponseHandler(response);
                        },
                        error: function (response) {
                            SecureSubmitObj.secureSubmitResponseHandler(response);
                        }
                    });

                    hps.tokenize();

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

            if (!response.message) {
                for (i = 0; i < ccInputSuffixes.length; i++) {
                    input = $form.find('#' + ccInputPrefix + ccInputSuffixes[i]);
                    input.val('');
                }
            }

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
