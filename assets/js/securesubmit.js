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
          
            console.log("hello");
          
          var hps = new Heartland.HPS({
            
            publicKey: 'pkapi_cert_jKc1FtuyAydZhZfbB3',
            
            type:      'iframe',

            fields: {
              
              cardName: {    
                target:      'iframesCardName',    
                placeholder: '•••• •••• •••• ••••'
              },
            
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
              '#iframes .red' :{
                    'color' : 'red'
              },
              '#iframes input': {
                    'box-sizing':'border-box',
                    'height': '34px',
                    'padding': '15px !important',
                    'font-size': '14px',
                    'min-width' : '100%'
                  },
            
              '#iframes input[type=submit]' : {
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

                    '#iframes .ginput_accountinfo_left' :{
                          'padding-right':'10px'
                    },
                    '#iframes .ginput_accountinfo_left' :{
                          'padding-right':'10px'
                    },
                    '#iframes .ginput_accountinfo_left,'+
                     '#iframes .ginput_accountinfo_right' :{
                          'display':'inline',
                          'width' : '50%',
                          'float' : 'left'
                    },
                    '#iframes div.HPS_gform_card_icon' :{
                          'background':'transparent url(../images/img/ss-shield@1x.png) no-repeat !important',
                          'background-size' : '320px 40px !important',
                          'width' : '540px !important',
                          'height' : '42px !important'
                    },
                    '#iframes label':{
                          'margin-top':'15px !important',
                          'margin-bottom' : '5px !important'
                    },
                     '.ginput_container #iframes span:not(.ginput_price)' :{
                          'margin-top':'0px',
                          'margin-bottom' : '0px'
                    },
                    '#iframes .ginput_cardinfo_right' :{
                          'width':'50% !important',
                          'padding-left' : '5px'
                    },
                    '#iframes .ginput_cardinfo_left' :{
                          'padding-right' : '5px'
                    },
            },
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
          Heartland.Events.addHandler(document.getElementById('#iframes').form, 'submit', function (e) {
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

            var SecureSubmitObj = this;
            Heartland.Card.attachNumberEvents('#cardName');
            Heartland.Card.attachNumberEvents('#cardNumber');
            Heartland.Card.attachExpirationEvents('#cardExpiration');
            Heartland.Card.attachCvvEvents('#CardCvv');

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
                    var _cardName=(SecureSubmitObj.ccFieldId!==null?$form.find('#' + ccInputPrefix + '1'):$form.find('#cardName')).val().replace(/\D/g, '');
                    var _cardNumber=(SecureSubmitObj.ccFieldId!==null?$form.find('#' + ccInputPrefix + '1'):$form.find('#cardNumber')).val().replace(/\D/g, '');
                    var _cardCvv=(SecureSubmitObj.ccFieldId!==null?$form.find('#' + ccInputPrefix + '3'):$form.find('#CardCvv')).val();
                    var _cardExpMonth=(SecureSubmitObj.ccFieldId!==null?$form.find('#' + ccInputPrefix + '2_month').val():$form.find('#_exp_date').val().split(' / ')[0]);
                    var _cardExpYear= (SecureSubmitObj.ccFieldId!==null?$form.find('#' + ccInputPrefix + '2_year').val():$form.find('#cardExpiration').val().split(' / ')[1]);

                    var hps = new Heartland.HPS({
                        publicKey: SecureSubmitObj.apiKey,
                        cardName: _cardName,
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
