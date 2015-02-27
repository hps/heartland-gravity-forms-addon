/*jslint browser:true, unparam:true*/
/*global hps, gforms_securesubmit_admin_strings, ajaxurl*/
(function (window, $) {
  window.SecureSubmitAdminClass = function () {
    this.sendEmailFields = ['recipient_address'];

    this.validateKey = function (keyName, key) {
      if (key.length === 0) {
        this.setKeyStatus(keyName, "");
        return;
      }

      $('#' + keyName).val(key.trim());

      this.setKeyStatusIcon(keyName, "<img src='" + gforms_securesubmit_admin_strings.spinner + "'/>");

      if (keyName === "public_api_key") {
        this.validatePublicApiKey(keyName, key);
      } else {
        this.validateSecretApiKey(keyName, key);
      }
    };

    this.validateSecretApiKey = function (keyName, key) {
      $.post(
        ajaxurl,
        {
          action : "gf_validate_secret_api_key",
          keyName: keyName,
          key : key
        },
        function (response) {
          response = response.trim();

          if (response === "valid") {
            window.SecureSubmitAdmin.setKeyStatus(keyName, "1");
          } else if (response === "invalid") {
            window.SecureSubmitAdmin.setKeyStatus(keyName, "0");
          } else {
            window.SecureSubmitAdmin.setKeyStatusIcon(keyName, gforms_securesubmit_admin_strings.validation_error);
          }
        }
      );
    };

    this.validatePublicApiKey = function (keyName, key) {
      this.setKeyStatusIcon(keyName, "<img src='" + gforms_securesubmit_admin_strings.spinner + "'/>");

      hps.tokenize({
        data: {
          public_key: key,
          number:     '4111111111111111',
          cvc:        '123',
          exp_month:  '01',
          exp_year:   (new Date()).getFullYear() + 1
        },
        success: function (response) {
          if (response.object === 'token') {
            window.SecureSubmitAdmin.setKeyStatus(keyName, "1");
          } else {
            window.SecureSubmitAdmin.setKeyStatus(keyName, "0");
          }
        },
        error: function (response) {
          window.SecureSubmitAdmin.setKeyStatus(keyName, "0");
        }
      });
    };

    this.initKeyStatus = function (keyName) {
      var is_valid = $('#' + keyName + '_is_valid').val();
      var key = $('#' + keyName).val();

      if (is_valid.length > 0) {
        this.setKeyStatus(keyName, is_valid);
      } else if (key.length > 0) {
        this.validateKey(keyName, key);
      }
    };

    this.setKeyStatus = function (keyName, is_valid) {
      $('#' + keyName + '_is_valid').val(is_valid);

      var iconMarkup = "";
      if (is_valid === "1") {
        iconMarkup = "<i class=\"fa icon-check fa-check gf_valid\"></i>";
      } else if (is_valid === "0") {
        iconMarkup = "<i class=\"fa icon-remove fa-times gf_invalid\"></i>";
      }

      this.setKeyStatusIcon(keyName, iconMarkup);
    };

    this.setKeyStatusIcon = function (keyName, iconMarkup) {
      var icon = $('#' + keyName + "_status_icon");
      if (icon.length > 0) {
        icon.remove();
      }

      $('#' + keyName).after("<span id='" + keyName + "_status_icon'>&nbsp;&nbsp;" + iconMarkup + "</span>");
    };

    this.initSendEmailFieldsToggle = function () {
      this.toggleSendEmailFields($('#gaddon-setting-row-send_email input:checked').val());
    };

    this.toggleSendEmailFields = function (value) {
      if (value === 'yes') {
        this.toggleFields(this.sendEmailFields, 'send_email', 'show');
      } else {
        this.toggleFields(this.sendEmailFields, 'send_email', 'hide');
      }
    };

    this.toggleFields = function (fields, prefix, showOrHide) {
      var length = fields.length;
      var i, field;
      for (i = 0; i < length; i++) {
        field = fields[i];
        if (showOrHide === 'show') {
          $('#gaddon-setting-row-' + prefix + '_' + field).show();
        } else {
          $('#gaddon-setting-row-' + prefix + '_' + field).hide();
        }
      }
    };
  };

  $(document).ready(function () {
    window.SecureSubmitAdmin = new window.SecureSubmitAdminClass();

    window.SecureSubmitAdmin.initKeyStatus('public_api_key');
    window.SecureSubmitAdmin.initKeyStatus('secret_api_key');
    window.SecureSubmitAdmin.initSendEmailFieldsToggle();
  });
})(window, window.jQuery);