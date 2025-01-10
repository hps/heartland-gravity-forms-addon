=== Heartland Secure Submit Addon for Gravity Forms ===
Contributors: markhagan
Tags: gravityforms, heartland, securesubmit, token, tokenize
Tested up to: 6.6.2
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
= 2.1.2 =
* Fix: UI Glitch - On Secure Submit payment checkout page

= 2.1.1 =
* Fix: Conditional logic issue - for ACH & CC payment type selection mode

= 2.1.0 =
* Fix: Recurring payment issue and depricated issues

= 2.0.0 =
* Removing support for non-iframes as per PCI compliance

= 1.4.8 =
* SecureSubmit js to Globalpayment JS migration

= 1.4.7 =
* Fix: Compatibility issue w/GF2.6.4

= 1.4.6 =
* Fix: Sub total amount floating point issue.

= 1.4.5 =
* Fix: Send correct start date for recurring payments when frequency is semi-monthly

= 1.4.4 =
* Fix: Recurring schedules with frequency 'semi-monthly' had incorrect parameters included in the request, causing it to fail.

= 1.4.3 =
* Fix: Page won't allow new ACH transaction attempt after a decline/fail

= 1.4.2 =
* Perform automatic void on ACH transaction timeouts

= 1.4.1 =
* Add CardCode property to Cardinal request object with a default of 0

= 1.4.0 =
* Add customer identifier to initial subscription payments

= 1.3.13 =
* Fix handling of special characters in ACH/eCheck account holder name
* Update spelling of ACH/eCheck account holder name in input placeholder

= 1.3.12 =
* Allow payment action override for subscription feeds
* Allow API keys override for subscription feeds
* Add supported Canadian provinces
* Fix handling of state in address when abbreviation is used

= 1.3.11 =
* Fix subscription setup fee not being applied during initial payment

= 1.3.10 =
* Delegate account/routing number validation to check processor for ACH payments

= 1.3.9 =
* Fix feed validation miscategorizing missing credit card and/or ACH payment data

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
