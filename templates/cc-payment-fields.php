<div class="ginput_complex<?php echo $class_suffix; ?> ginput_container ginput_container_creditcard hps_secure_cc" id="<?php echo $field_id; ?>">
  <div id="HPS_secure_cc">
    <div class="ss-shield"<?php echo $this->get_tabindex(); // added to get rid of GF silly confusion for the menu item being the next index from the first input ?>></div>
    <div id="secure-submit-card" class="form-group">
      <label for="iframesCardHolder">Card Holder<span class="red">*</span></label>
      <input type="text" name="card_name" placeholder="John Doe" <?php echo $this->get_tabindex(); ?>/><br /><br />
    </div>
    <!-- The Payment Form -->
    <div id="secure-submit-card" class="form-group">
      <label for="iframesCardNumber">Card Number<span class="red">*</span></label>
      <div class="iframeholder" id="iframesCardNumber" <?php echo $this->get_tabindex(); ?>></div>
    </div>
    <div id="secure-submit-date" class="form-group">
      <label for="iframesCardExpiration">Card Expiration<span class="red">*</span></label>
      <div class="iframeholder" id="iframesCardExpiration" <?php echo $this->get_tabindex(); ?>></div>
    </div>
    <div id="secure-submit-cvv" class="form-group">
      <label for="iframesCardCvv">Card CVV<span class="red">*</span></label>
      <div class="iframeholder" id="iframesCardCvv" <?php echo $this->get_tabindex(); ?>></div>
    </div>
    <div id="iframesGetTokenButton" <?php echo $this->get_tabindex(); ?>></div>
  </div>
</div>

<?php if (is_admin()): ?>
  <script>
    window.SecureSubmitAdmin = window.SecureSubmitAdmin || {};
    window.SecureSubmitAdmin.initAdminCCFields = window.SecureSubmitAdmin.initAdminCCFields || function () {};
    window.SecureSubmitAdmin.initAdminCCFields();
  </script>
<?php endif; ?>
