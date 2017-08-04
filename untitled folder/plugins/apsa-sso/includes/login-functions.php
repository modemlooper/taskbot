<?php
/**
 * Login Functions
 *
 * @package         APSA_SSO\Login
 * @version         1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Login Form
 *
 * @since       1.0.0
 * @param       string $redirect Redirect page URL
 * @return      string Login form
 */
function apsa_sso_login_form( $redirect = '' ) {
	global $apsa_sso_login_redirect;

	if ( empty( $redirect ) ) {
		$redirect = apsa_sso_get_current_page_url();
	}

	$apsa_sso_login_redirect = $redirect;

	ob_start();

	apsa_sso_get_template_part( 'shortcode', 'login' );

	return apply_filters( 'apsa_sso_login_form', ob_get_clean() );
}


/**
 * Process Login Form
 *
 * @since       1.0.0
 * @param       array $data Data sent from the login form
 * @return      void
 */
function apsa_sso_process_login_form( $data ) {

	if ( ! isset( $_POST['apsa_sso_action'] ) ) {
		return;
	}

	if ( wp_verify_nonce( $data['apsa_sso_login_nonce'], 'apsa-sso-login-nonce' ) ) {
		apsa_sso_clear_errors();

		// @TODO fix this. something broken in error sessions they wont clear unless you pass id.
		apsa_sso_unset_error( 'account_expired' );
		apsa_sso_unset_error( 'password_incorrect' );
		apsa_sso_unset_error( 'username_incorrect' );

		$user_data = get_user_by( 'login', $data['apsa_sso_user_login'] );
		$member_key = $user_data ? get_user_meta( $user_data->ID, 'apsa_member_id' ) : false;

		// If user currently exists and is not a user in other system, log them in.
		if ( $user_data && ! $member_key ) {
			$user_ID    = $user_data->ID;
			$user_email = $user_data->user_email;

			if ( wp_check_password( $data['apsa_sso_user_pass'], $user_data->user_pass, $user_data->ID ) ) {
				if ( isset( $data['remember'] ) ) {
					$data['remember'] = true;
				} else {
					$data['remember'] = false;
				}

				apsa_sso_log_user_in( $user_data->ID, $data['apsa_sso_user_login'], $data['apsa_sso_user_pass'], $data['remember'] );
			} else {
				apsa_sso_set_error( 'password_incorrect', __( 'The password you entered is incorrect', 'apsa-sso' ) );
			}
		} else {

			// Authorize the user to login OR create and account.
			$apsa_id = APSA_SSO()->webservice->auth( $data['apsa_sso_user_login'], $data['apsa_sso_user_pass'] );
			$user_ID = $apsa_id;
			$data['remember'] = true;

			if ( ! $apsa_id ) {
				apsa_sso_set_error( 'username_incorrect', __( 'The username you entered does not exist', 'apsa-sso' ) );
			} else {

				$user_data = get_user_by( 'login', $apsa_id );

				if ( $user_data ) {
					apsa_sso_log_user_in( $user_data->ID, $apsa_id, $data['apsa_sso_user_pass'], true );
				} else {
					$data['apsa_sso_user_login'] = $apsa_id;
					apsa_sso_process_registration( $data, $apsa_id );
				}
			}
		}

		// Check for errors and redirect if none present.
		$errors = apsa_sso_get_errors();

		if ( ! $errors ) {
			$redirect = apply_filters( 'apsa_sso_login_redirect', $data['apsa_sso_redirect'], $user_ID );

			wp_safe_redirect( $redirect );
			apsa_sso_die();
		}
	}
}
	add_action( 'apsa_sso_user_login', 'apsa_sso_process_login_form' );


/**
 * Process registration
 *
 * @since       1.0.0
 * @param       array  $data Data sent from the login form
 * @param       string $apsa_id The member ID returned by auth
 * @return      void
 */
function apsa_sso_process_registration( $data, $apsa_id ) {
	// This should never happen.
	if ( is_user_logged_in() ) {
		return;
	}

	do_action( 'apsa_sso_pre_process_registration' );

	if ( empty( $data['apsa_sso_user_login'] ) ) {
		apsa_sso_set_error( 'empty_username', __( 'Invalid username', 'apsa-sso' ) );
	}

	if ( username_exists( $data['apsa_sso_user_login'] ) ) {
		apsa_sso_set_error( 'username_unavailable', __( 'Username already taken', 'apsa-sso' ) );
	}

	if ( ! validate_username( $data['apsa_sso_user_login'] ) ) {
		apsa_sso_set_error( 'username_invalid', __( 'Invalid username', 'apsa-sso' ) );
	}

	$user_data = APSA_SSO()->webservice->fetch( $apsa_id );

	// Set data from API.
	if ( ! is_array( $user_data ) ) {
		apsa_sso_set_error( 'username_incorrect', __( 'The username you entered does not exist', 'apsa-sso' ) );
	} else {
		$apsa_data                   = $user_data['DataSet']['diffgr:diffgram']['NewDataSet'];
		$data['apsa_sso_user_email'] = $apsa_data['Member']['Email'];
		$data['apsa_data']           = $apsa_data;

		// We have to validate email from the API
		if ( email_exists( $data['apsa_sso_user_email'] ) ) {
			apsa_sso_set_error( 'email_unavailable', __( 'Email address already taken', 'apsa-sso' ) );
		}

		if ( empty( $data['apsa_sso_user_email'] ) || ! is_email( $data['apsa_sso_user_email'] ) ) {
			apsa_sso_set_error( 'email_invalid', __( 'Invalid email', 'apsa-sso' ) );
		}

		// Since we've already validated, this should never happen
		if ( empty( $_POST['apsa_sso_user_pass'] ) ) {
			apsa_sso_set_error( 'empty_password', __( 'Please enter a password', 'apsa-sso' ) );
		}
	}

	do_action( 'apsa_sso_process_registration' );

	// Check for errors and redirect if none present
	$errors = apsa_sso_get_errors();

	if (  empty( $errors ) ) {
		$redirect = apply_filters( 'apsa_sso_register_redirect', $data['apsa_sso_redirect'] );

		apsa_sso_register_and_login_new_user( array(
			'user_login'      => $data['apsa_sso_user_login'],
			'user_pass'       => $data['apsa_sso_user_pass'],
			'user_email'      => $data['apsa_sso_user_email'],
			'user_registered' => date( 'Y-m-d H:i:s' ),
			'role'            => get_option( 'default_role' ),
			'apsa_data'       => $user_data['DataSet']['diffgr:diffgram']['NewDataSet'],
		), $apsa_id );

		wp_safe_redirect( $redirect );
		apsa_sso_die();
	}
}


/**
 * Register and login new user
 *
 * @since       1.0.0
 * @param       array  $user_data Data for the new user
 * @param       string $apsa_id The APSA member ID for the user
 * @return      int
 */
function apsa_sso_register_and_login_new_user( $user_data = array(), $apsa_id ) {

	// Verify the array
	if ( empty( $user_data ) ) {
		return -1;
	}

	if ( apsa_sso_get_errors() ) {
		return -1;
	}

	$apsa_data = $user_data['apsa_data'];

	$user_args = apply_filters( 'apsa_sso_insert_user_args', array(
		'user_login'      => isset( $user_data['user_login'] ) ? $user_data['user_login'] : '',
		'user_pass'       => isset( $user_data['user_pass'] ) ? $user_data['user_pass']  : '',
		'user_email'      => isset( $user_data['user_email'] ) ? $user_data['user_email'] : '',
		'first_name'      => isset( $apsa_data['Member']['FirstName'] ) ? $apsa_data['Member']['FirstName'] : '',
		'last_name'       => isset( $apsa_data['Member']['LastName'] ) ? $apsa_data['Member']['LastName']  : '',
		'user_registered' => date( 'Y-m-d H:i:s' ),
		'role'            => get_option( 'default_role' ),
		'user_url'        => isset( $apsa_data['Member']['WebsiteUrl'] ) ? $apsa_data['Member']['WebsiteUrl'] : '',
	), $user_data );

	// Insert new user.
	$user_id = wp_insert_user( $user_args );

	// Validate inserted user.
	if ( is_wp_error( $user_id ) ) {
		return -1;
	}

	// Set a user meta key for the APSA member ID.
	update_user_meta( $user_id, 'apsa_member_id', $apsa_id );

	// Setup xProfile fields.
	apsa_sso_set_xprofile_data( $user_id, $apsa_data );

	// Add user to groups.
	if ( function_exists( 'apsa_run_user_group_sync' ) ) {
		apsa_run_user_group_sync( $user_id, $apsa_data );
	}

	// Allow themes and plugins to filter the user data.
	$user_data = apply_filters( 'apsa_sso_insert_user_data', $user_data, $user_args );

	// Allow themes and plugins to hook.
	do_action( 'apsa_sso_insert_user', $user_id, $user_data );

	// Login new user.
	apsa_sso_log_user_in( $user_id, $user_data['user_login'], $user_data['user_pass'] );

	// Return user id.
	return $user_id;
}


/**
 * Log user in
 *
 * @since       1.0.0
 * @param       int     $user_id User ID
 * @param       string  $user_login Username
 * @param       string  $user_pass Password
 * @param       boolean $remember Remember me
 * @return      void
 */
function apsa_sso_log_user_in( $user_id, $user_login, $user_pass, $remember = false ) {
	if ( $user_id < 1 ) {
		return;
	}

	do_action( 'apsa_sso_before_log_user_in', $user_id, $user_login, $user_pass );

	wp_set_auth_cookie( $user_id, $remember );
	wp_set_current_user( $user_id, $user_login );
	do_action( 'wp_login', $user_login, get_userdata( $user_id ) );
	do_action( 'apsa_sso_log_user_in', $user_id, $user_login, $user_pass );
}
