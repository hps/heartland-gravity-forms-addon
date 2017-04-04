=== Heartland Secure Submit Addon for Gravity Forms ===
Contributors: markhagan
Tags: gravity, forms, gravityforms, heartland, payment, systems, gateway, token, tokenize
Tested up to: 4.5.1
Stable tag: trunk
License: GPLv2
License URI: https://github.com/hps/heartland-gravity-forms-addon/blob/master/LICENSE.md

SecureSubmit allows merchants to take PCI-Friendly Credit Card payments with Gravity Forms using Heartland Payment Systems Payment Gateway.

== Description ==

This plugin allows Gravity Forms to use the Heartland Payment Systems Gateway. All card data is tokenized using Heartland's SecureSubmit product.

Features of SecureSubmit:

* Only two configuration fields: public and secret API key
* Simple to install and configure.
* Tokenized payments help reduce PCI Scope
* Enables credit card saving for a friction-reduced checkout.

== Installation ==

  1. Sign Up for an account @ developer.heartlandpaymentsystems.com if you haven't already
  2. Download Gravity Forms
  3. Install AND Activate Gravity Forms WP plugin AND Heartland SecureSubmit for Gravity Forms WP plugin
  4. Configure Gravity Forms and SecureSubmit accounts:
      * Navigate to Settings to enter your API Keys provided by your Heartland Developer Portal Account
  5. Add Form:
      * Navigate to Forms > Add New Form > Edit Form
      * Add Required Fields:
        * Pricing
        * Product
        * Total
        * CC and/or ACH form
  6. Add new Feed:
      * Form Settings > SecureSubmit > Add new feed
  7. Add form to WP page

* NEED ADDITIONAL HELP? Contact Us  http://developer.heartlandpaymentsystems.com/support


== Changelog ==
= 1.2.2 =
* Fix issue with `maybe_validate` on multi-page forms

= 1.2.1 =
* Fix compatibility issues with older versions of PHP

= 1.2.0 =
* Add ACH feature
* Add secure credit card option with iframe tokenization

= 1.1.3 =
* Fix tokenization bug due to upstream change

= 1.1.2 =
* Upgraded license to GPLv2

= 1.1.1 =
* Fix directory resolution for templates and SDK inclusion

= 1.1.0 =
* Add ability to inject transaction id or authorization code in notifications.
* Fix bugs with authorize vs charge payment methods.

= 1.0.0 =
* Initial Release
