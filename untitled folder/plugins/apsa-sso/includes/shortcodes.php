<?php
/**
 * Shortcodes
 *
 * @package         APSA_SSO\Shortcodes
 * @since           1.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Login Shortcode
 *
 * Shows a login form allowing users to log in. This function simply
 * calls the apsa_sso_login_form function to display the login form.
 *
 * @since       1.0.0
 * @param       array $atts Shortcode attributes
 * @param       string $content
 * @return      string
 */
function apsa_sso_login_form_shortcode( $atts, $content = null ) {
	$redirect = '';

	extract( shortcode_atts( array(
			'redirect' => $redirect
		), $atts, 'apsa_login' )
	);

	if ( empty( $redirect ) ) {
		$redirect = home_url();
	}

	return apsa_sso_login_form( $redirect );
}
add_shortcode( 'apsa_sso_login', 'apsa_sso_login_form_shortcode' );