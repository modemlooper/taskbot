<?php
/**
 * Misc Functions
 *
 * @package         APSA_SSO\Functions
 * @version         1.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Get the current page URL
 *
 * @since       1.0.0
 * @return      string $page_url Current page URL
 */
function apsa_sso_get_current_page_url() {
	global $wp;

	if ( get_option( 'permalink_structure' ) ) {
		$base = trailingslashit( home_url( $wp->request ) );
	} else {
		$base = add_query_arg( $wp->query_string, '', trailingslashit( home_url( $wp->request ) ) );
		$base = remove_query_arg( array( 'post_type', 'name' ), $base );
	}

	$scheme = is_ssl() ? 'https' : 'http';
	$uri    = set_url_scheme( $base, $scheme );

	if ( is_front_page() ) {
		$uri = home_url( '/' );
	}

	$uri = apply_filters( 'aspa_sso_get_current_page_url', $uri );

	return $uri;
}