<?php
/**
 * Plugin Name: Heartland Secure Submit Addon for Gravity Forms
 * Plugin URI: https://developer.heartlandpaymentsystems.com/securesubmit
 * Description: Integrates Gravity Forms with SecureSubmit, enabling end users to purchase goods and services through Gravity Forms.
 * Version: 2.1.0
 * Author: SecureSubmit
 * Author URI: https://developer.heartlandpaymentsystems.com/securesubmit
 */

define('GF_SECURESUBMIT_VERSION', '2.1.1');

add_action('gform_loaded', array('GF_SecureSubmit_Bootstrap', 'load'), 5);

/**
 * Class GF_SecureSubmit_Bootstrap
 */
class GF_SecureSubmit_Bootstrap
{
    public static function load()
    {
        if (!method_exists('GFForms', 'include_payment_addon_framework')) {
            return;
        }

        require_once 'classes/class-gf-securesubmit.php';

        GFAddOn::register('GFSecureSubmit');
    }
}

/**
 * @return \GFSecureSubmit|null
 */
function gf_securesubmit()
{
    return GFSecureSubmit::get_instance();
}
