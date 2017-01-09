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






/*global $, jQuery*/
var hps = (function ($) {
    "use strict";

    var Validator, OptionValidator, FieldValidator, HPS;

    Validator = function (fail, message) {
        this.fail = fail;
        this.message = message;
    };

    Validator.prototype.validate = function () {
        if (this.fail) {
            HPS.error(this.message);
        }
    };

    OptionValidator = function (field, options) {
        this.message = field + " is missing";
        this.fail = HPS.empty(options[field]);
    };

    FieldValidator = function (field, type) {
        this.field = field;
        this.type = type;
        this.element = (typeof field === 'object') ? field : $("#" + field);
        this.message = this.element.attr("id") + " is invalid";
        this.fail = HPS.empty(this.element) || !this.element.is(this.type);
    };

    OptionValidator.prototype = new Validator();
    OptionValidator.prototype.constructor = OptionValidator;
    FieldValidator.prototype = new Validator();
    FieldValidator.prototype.constructor = FieldValidator;

    HPS = {

        Tag: "SecureSubmit",

        Urls: {
            CERT: "https://cert.api2.heartlandportico.com/Hps.Exchange.PosGateway.Hpf.v1/api/token",
            PROD: "https://api2.heartlandportico.com/SecureSubmit.v1/api/token"
        },

        getData: function (element) {
            return element.data(HPS.Tag);
        },

        setData: function (element, data) {
            element.data(HPS.Tag, data);
        },

        hasData: function (element) {
            return typeof HPS.getData(element) === 'object';
        },

		tokenize: function (options) {
			var gateway_url, params, env;

            // add additional service parameters
            params = $.param({
                "api_key": options.data.public_key,
                "object": "token",
                "token_type": "supt",
                "_method": "post",
                "card[name]": $.trim(options.data.number),
                "card[number]": $.trim(options.data.number),
                "card[cvc]": $.trim(options.data.cvc),
                "card[exp_month]": $.trim(options.data.exp_month),
                "card[exp_year]": $.trim(options.data.exp_year)
            });

            env = options.data.public_key.split("_")[1];

            if (env === "cert") {
                gateway_url = HPS.Urls.CERT;
            } else {
                gateway_url = HPS.Urls.PROD;
            }

            // request token
            $.ajax({
                cache: false,
                url: gateway_url,
                data: params,
                dataType: "jsonp",
                success: function (response) {

                    // Request failed, handle error
                    if (typeof response.error === 'object') {
                        // call error handler if provided and valid
                        if (typeof options.error === 'function') {
                            options.error(response.error);
                        }
                        // handle exception
                        HPS.error(response.error.message);
                    }
					else if(typeof options.success === 'function') {
						options.success(response);
					}
                }
            });

		},

        empty: function (val) {
            return val === undefined || val.length === 0;
        },

        error: function (message) {
            $.error([HPS.Tag, ": ", message].join(""));
        },

        configureElement: function (options) {

            // set plugin data
            HPS.setData($(this), {
                public_key: options.public_key,
                success: options.success,
                error: options.error,
                validators: [
                    new OptionValidator("public_key", options),
                    new FieldValidator("card_number", "input"),
                    new FieldValidator("card_cvc", "input"),
                ]
            });

            // add event handler for form submission
            $(this).submit(function (e) {

                var theForm, data, i, cardType;

                // stop form from submitting
                e.preventDefault();

                // remove name attributes from sensitive fields
                $("#{$field_id}_5").removeAttr("name");
                $("#cc_number").removeAttr("name");
                $("#_cvv_number").removeAttr("name");
                $("cc_number").removeAttr("name");

                theForm = $(this);

                // get data from storage
                data = HPS.getData(theForm);

                // validate data plugin options
                for (i = 0; i < data.validators.length; i += 1) {
                    data.validators[i].validate();
                }

                // validate form - jQuery validate plugin
                if (typeof theForm.validate === 'function') {
                    theForm.validate();
                    // validation failed
                    if (!theForm.valid()) {
                        return;
                    }
                }

				HPS.tokenize({
					data: {
						public_key: data.public_key,
                    name: $.trim($("#{$field_id}_5").val()),
		                number: $.trim($("#cc_number").val()),
		                cvc: $.trim($("#_cvv_number").val()),
		                exp_month: $.trim($("#_exp_date").val())
					},
					success: function(response){

		                // create field and append to form
		                $("<input>").attr({
		                    type: "hidden",
		                    id: "token_value",
		                    name: "token_value",
		                    value: response.token_value
		                }).appendTo(theForm);

                        var re = {
                            visa: /^4[0-9]{12}(?:[0-9]{3})?$/,
                            mastercard: /^5[1-5][0-9]{14}$/,
                            amex: /^3[47][0-9]{13}$/,
                            diners: /^3(?:0[0-5]|[68][0-9])[0-9]{11}$/,
                            discover: /^6(?:011|5[0-9]{2})[0-9]{12}$/,
                            jcb: /^(?:2131|1800|35\d{3})\d{11}$/
                        };

                        if (re.visa.test($.trim($("#card_number").val()))) {
                            cardType = 'visa';
                        } else if (re.mastercard.test($.trim($("#cc_number").val()))) {
                            cardType = 'mastercard';
                        } else if (re.amex.test($.trim($("#cc_number").val()))) {
                            cardType = 'amex';
                        } else if (re.diners.test($.trim($("#cc_number").val()))) {
                            cardType = 'diners';
                        } else if (re.discover.test($.trim($("#cc_number").val()))) {
                            cardType = 'discover';
                        } else if (re.jcb.test($.trim($("#cc_number").val()))) {
                            cardType = 'jcb';
                        }

                        $("<input>").attr({
                            type: "hidden",
                            id: "card_type",
                            name: "card_type",
                            value: cardType
                        }).appendTo(theForm);

                        $("<input>").attr({
                            type: "hidden",
                            id: "_exp_date",
                            name: "_exp_date",
                            value: $.trim($("#_exp_date").val())
                        }).appendTo(theForm);

                        $("<input>").attr({
                            type: "hidden",
                            id: "last_four",
                            name: "last_four",
                            value: $("#cc_number").val().slice(-4)
                        }).appendTo(theForm);

		                // success handler provided
		                if (typeof data.success === 'function') {
		                    // call the handler with payload
		                    if (data.success(response) === false) {
		                        return; // stop processing
		                    }
		                }

		                theForm.unbind('submit'); // unbind event handler
		                theForm.submit(); // submit the form
					},
					error: function(response){
	                    if (typeof data.error === 'function') {
	                        data.error(response);
	                    }
					}
				});

            });
        }
    };

    $.fn.SecureSubmit = function (options) {

        return this.each(function () {
            if (!$(this).is("form") || typeof options !== 'object' || HPS.hasData($(this))) {

                return;
            }

            HPS.configureElement.apply(this, [options]);
        });
    };

	return HPS;

}(jQuery.noConflict()));
