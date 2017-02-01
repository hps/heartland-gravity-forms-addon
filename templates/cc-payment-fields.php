
<div class="ginput_complex<?php echo $class_suffix; ?> ginput_container ginput_container_creditcard hps_secure_cc" id="<?php echo $field_id; ?>">
  <div id="HPS_secure_cc">
    <div class="ss-shield"></div>
    <!-- make iframes styled like other form -->
    <style type="text/css">
      #iframes iframe{
        float:left;
        width:100%;
      }
      .iframeholder {
        height:50px;
        width:100%;
      }
      .ie8 form .iframeholder {
        padding:10px;
      }
      .iframeholder::after{
        content:'';
        display:block;
        width:100%;
        height:0px;
        clear:both;
        position:relative;
      }
      .ie8 form .form-group {
        margin-bottom:5px;
      }
      #iframesCardExpiration,
      #iframesCardCvv{
        margin-bottom:14px;
      }
      label[for=iframesCardNumber],
      label[for=iframesCardExpiration],
      label[for=iframesCardCvv]{
        text-transform:uppercase !important;
        font-weight:500 !important;
        font-size:14px !important;
        color:#000000 !important;
        margin-bottom:0px !important;
        font-family:sans-serif !important;
      }
      .ie8 form label {
        padding-left:10px;
        margin:0px;
      }
      #heartland-frame-cardExpiration,
      #heartland-frame-cardCvv,
      #heartland-frame-cardNumber,
      .ie8 #heartland-frame-cardExpiration,
      .ie8 #heartland-frame-cardCvv,
      .ie8 #heartland-frame-cardNumber  {
        width:100%;
      }
      #ss-banner {
        background:transparent url(assets/images/ss-shield@2x.png) no-repeat left center;
        height:40px;
        background-size:280px 34px;
        margin-bottom:10px;
      }
      .ie8 #ss-banner {
        background:transparent url(assets/images/ss-shield-ie.png) no-repeat left center;
      }
      .btn-primary{
        display:block;
        border-radius:0px;
        font-size:18px;
        float:right;
        background-color:#36b46e;
        border:1px solid #2a8d56;
        margin-bottom:10px;
        width:100%;
      }
      .btn-primary:hover,
      .btn-primary:focus{
        color: #fff;
        background-color: #2a8d56;
      }
      .ie8 .btn-primary {
        width:15%;
      }
      .red {
        margin-left:2px;
        font-size:17px !important;
      }
      @media screen and (min-width:767px) {
        #ss-date.form-group,
        #ss-cvv.form-group{
          display:inline-block;
          width:48%;
        }
        #ss-cvv.form-group {
          float:right;
        }
        #heartland-frame-cardNumber {
          width : 100%;
        }
      }
      @media screen and (min-width:450px) {
        .btn-primary,
        .ie8 .btn-primary {
          width:10em;
        }
      }
      }
    </style>
    <!-- The Payment Form -->
    <div id="iframes">
      <div id="ss-card" class="form-group">
        <label for="iframesCardNumber">Card Number<span class="red">*</span></label>
        <div class="iframeholder" id="iframesCardNumber"></div>
      </div>
      <div id="ss-date" class="form-group">
        <label for="iframesCardExpiration">Card Expiration<span class="red">*</span></label>
        <div class="iframeholder" id="iframesCardExpiration"></div>
      </div>
      <div id="ss-cvv" class="form-group">
        <label for="iframesCardCvv">Card CVV<span class="red">*</span></label>
        <div class="iframeholder" id="iframesCardCvv"></div>
      </div>

    </div>
  </div>
</div>
