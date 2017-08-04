<?php
/**
 * Shortcodes
 *
 * @package         APSA_SSO\Actions
 * @since           1.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Hooks APSA_SSO actions, when present in the $_GET and $_POST
 * superglobals. Every apsa_sso_action present is called using
 * WordPress's do_action function. These functions are called on init.
 *
 * @since       1.0.0
 * @return      void
*/
function apsa_sso_get_actions() {
	if ( isset( $_GET['apsa_sso_action'] ) ) {
		do_action( 'apsa_sso_' . $_GET['apsa_sso_action'], $_GET );
	}

	if ( isset( $_POST['apsa_sso_action'] ) ) {
		do_action( 'apsa_sso_' . $_POST['apsa_sso_action'], $_POST );
	}
}
add_action( 'init', 'apsa_sso_get_actions' );
