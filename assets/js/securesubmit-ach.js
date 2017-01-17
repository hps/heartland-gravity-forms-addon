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
            Heartland.Card.attachNameEvents('#{$field_id}_5');
            Heartland.Card.attachNumberEvents('#{$field_id}_6');
            Heartland.Card.attachNumberEvents('#routing_number');
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
                    var _accountName    =(SecureSubmitObj.ccFieldId!==null?$form.find('#' + ccInputPrefix + '5'):$form.find('#{$field_id}_5')).val();
                    var _accountNumber  =(SecureSubmitObj.ccFieldId!==null?$form.find('#' + ccInputPrefix + '6'):$form.find('#{$field_id}_6')).val().replace(/\D/g, '');
                    var _routingNumber  =(SecureSubmitObj.ccFieldId!==null?$form.find('#' + ccInputPrefix + '7'):$form.find('#routing_number')).val().replace(/\D/g, '');
                    var _accountType    =(SecureSubmitObj.ccFieldId!==null?$form.find('#' + ccInputPrefix + '8').val():$form.find('#account_type').val();
                    var _checkType      =(SecureSubmitObj.ccFieldId!==null?$form.find('#' + ccInputPrefix + '9').val():$form.find('#check_type').val();

                    var hps = new Heartland.HPS({
                        publicKey: SecureSubmitObj.apiKey,
                        accountName: _accountName,
                        accountNumber: _accountNumber,
                        routingNumber: _routingNumber,
                        accountType: _accountType,
                        checkType:_checkType,
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
            var ccInputSuffixes = ['5', '6', '7', '8', '9'];
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
