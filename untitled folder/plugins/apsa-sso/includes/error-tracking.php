<?php
/**
 * Error Handler
 *
 * @package         APSA_SSO\Errors
 * @version         1.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prints all stored errors.
 * If errors exist, they are returned.
 *
 * @since       1.0.0
 * @return      void
 */
function apsa_sso_print_errors() {
	$errors = apsa_sso_get_errors();

	if ( $errors ) {
		$classes = apply_filters( 'apsa_sso_error_class', array(
			'apsa_sso_errors', 'apsa-sso-alert', 'apsa-sso-alert-error'
		) );

		echo '<div class="' . implode( ' ', $classes ) . '">';

		// Loop error codes and display errors
		foreach ( $errors as $error_id => $error ) {
			echo '<p class="apsa_sso_error" id="apsa_sso_error_' . $error_id . '"><strong>' . __( 'Error', 'apsa-sso' ) . '</strong>: ' . $error . '</p>';
		}

		echo '</div>';
		apsa_sso_clear_errors();
	}
}


/**
 * Get Errors
 *
 * @since       1.0.0
 * @return      mixed array if errors are present, false if none found
 */
function apsa_sso_get_errors() {
	return APSA_SSO()->session->get( 'apsa_sso_errors' );
}


/**
 * Set Error
 *
 * @since       1.0.0
 * @param       int $error_id ID of the error being set
 * @param       string $error_message Message to store with the error
 * @return      void
 */
function apsa_sso_set_error( $error_id, $error_message ) {
	$errors = apsa_sso_get_errors();

	if ( ! $errors ) {
		$errors = array();
	}

	$errors[ $error_id ] = $error_message;

	APSA_SSO()->session->set( 'apsa_sso_errors', $errors );
}


/**
 * Clears all stored errors.
 *
 * @since       1.0.0
 * @return      void
 */
function apsa_sso_clear_errors() {
	APSA_SSO()->session->set( 'apso_sso_errors', null );
}


/**
 * Removes (unsets) a stored error
 *
 * @since       1.0.0
 * @param       int $error_id ID of the error being set
 * @return      string
 */
function apsa_sso_unset_error( $error_id ) {
	$errors = apsa_sso_get_errors();

	if ( $errors ) {
		unset( $errors[ $error_id ] );
		APSA_SSO()->session->set( 'apsa_sso_errors', $errors );
	}
}


/**
 * Register die handler for apsa_sso_die()
 *
 * @since       1.0.0
 * @return      void
 */
function _apsa_sso_die_handler() {
	die();
}


/**
 * Wrapper function for wp_die(). This function adds filters for wp_die() which
 * kills execution of the script using wp_die(). This allows us to then to work
 * with functions using apsa_sso_die() in the unit tests.
 *
 * @since       1.0.0
 * @return      void
 */
function apsa_sso_die( $message = '', $title = '', $status = 400 ) {
	add_filter( 'wp_die_ajax_handler', '_apsa_sso_die_handler', 10, 3 );
	add_filter( 'wp_die_handler', '_apsa_sso_die_handler', 10, 3 );
	wp_die( $message, $title, array( 'response' => $status ));
}
