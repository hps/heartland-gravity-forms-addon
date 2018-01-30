=== Heartland Secure Submit Addon for Gravity Forms ===
Contributors: markhagan
Tags: gravity, forms, gravityforms, heartland, payment, systems, gateway, token, tokenize
Tested up to: 4.9
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

= 1.3.8 =
* Allow non-US/CAN cardholders to submit proper billing address for non-subscription payments

= 1.3.7 =
* Fix subscription payments with limited number of payments to schedule correct number of payments

= 1.3.6 =
* Add transaction success and failure actions

= 1.3.5 =
* Fix issue with missing billing zip code for subscription payment methods

= 1.3.4 =
* Added 3DSecure to Secure Credit Card and native Credit Card form types

= 1.3.3 =
* Fix issue with state field passing full name instead of abbreviation

= 1.3.2 =
* Fix issue with annual subscriptions
* Fix issue with country values in address when country field is hidden

= 1.3.1 =
* Fix address issue when invalid countries are supplied by normalizing country values

= 1.3.0 =
* Add support for PayPlan CC schedules

= 1.2.3 =
* Fix compatibility issues with Gravity Forms 2.* when passing card/check holder information to gateway

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
