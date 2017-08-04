<?php
/**
 * Webservice
 *
 * This class acts as a webservice handler for all calls to the
 * APSA webservice API
 *
 * @package         APSA_SSO\Webservice
 * @since           1.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Main webservice class
 *
 * @access      public
 * @since       1.0.0
 */
class APSA_SSO_Webservice {


	/**
	 * @access      private
	 * @since       1.0.0
	 * @var         string $security_key The API security key
	 */
	private $security_key = '774F946E-6805-4115-A5B9-6C8E0DBDCDB8';
	// private $security_key = 'c5CAjEqawAtRAwaw';
	/**
	 * @access      public
	 * @since       1.0.0
	 * @var         string $api_url The base URL of the webservice API
	 */
	public $api_url = 'https://www.apsanet.org/DesktopModules/NOAH_Clients/APSA/wordpressapi.asmx';
	// public $api_url = 'https://www.apsanet.org/DesktopModules/NOAH_Clients/APSA/HigherLogic.asmx';
	/**
	 * @access      private
	 * @since       1.0.0
	 * @var         string $contact_id The ID of the user we are working with
	 */
	private $contact_id = '';


	/**
	 * Get things started
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      void
	 */
	public function __construct() {}


	/**
	 * Make a call to the API
	 *
	 * @access      private
	 * @since       1.0.0
	 * @param       array  $api_data The data to pass to the API
	 * @param       string $type Whether this is a GET or POST request
	 * @return      mixed $response Response from the API
	 */
	private function call( $api_data = null, $url = false, $type = 'POST' ) {
		$request_data = apply_filters( 'apsa_sso_api_request_data', array(
			'method'  => $type,
			'timeout' => 45,
			'body'    => $api_data,
		) );

		$api_url = trailingslashit( $this->api_url );

		if ( $url ) {
			$api_url .= esc_attr( $url );
		}

		if ( $type == 'POST' ) {
			$response = wp_remote_post( $api_url, $request_data );
		}

		if ( is_wp_error( $response ) ) {
			// Handle errors
		}

		$response = wp_remote_retrieve_body( $response );

		return $response;
	}


	/**
	 * Authenticate a given user
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       string $id The username to authenticate
	 * @param       string $password The password to authenticate with
	 * @return      mixed User ID if authenticated, false otherwise
	 */
	public function auth( $username, $password ) {
		$api_data = array(
			'Securitykey' => $this->security_key,
			'userName'    => $username,
			'password'    => $password,
		);

		$response = $this->call( $api_data, 'AuthenticateUser' );

		if ( ! class_exists( 'XML2Array' ) ) {
			require_once APSA_SSO_DIR . 'includes/libraries/XML2Array.php';
		}

		if ( $response ) {
			$response = XML2Array::createArray( $response );
		}

		if ( ! is_array( $response ) || ! array_key_exists( 'string', $response ) || $response['string'] == '' ) {
			return false;
		}

		$this->contact_id = $response['string'];
		return $this->contact_id;
	}


	/**
	 * Get the member data for an authenticated user
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       string $id The member ID to fetch data for
	 * @return      bool True if authenticated, false otherwise
	 */
	public function fetch( $id ) {
		$api_data = array(
			'Securitykey' => $this->security_key,
			'MemberKey'   => $id,
		);

		$response = $this->call( $api_data, 'GetMemberDetails' );

		if ( ! class_exists( 'XML2Array' ) ) {
			require_once APSA_SSO_DIR . 'includes/libraries/XML2Array.php';
		}

		if ( $response ) {
			$response = XML2Array::createArray( $response );
		}

		if ( ! is_array( $response ) ) {
			return false;
		}

		return $response;
	}

	/**
	 * Get the changed member IDs
	 *
	 * @access 		public
	 * @param       string $date_since The date to check since members have changed.
	 * @since       1.0.0
	 * @return      array user ids
	 */
	public function fetch_changed( $date_since = null ) {

		$changed_since = null !== $date_since ? $date_since : date( 'Y-m-d', strtotime( '-1 days' ) );

		$api_data = array(
			'Securitykey' => $this->security_key,
			'ChangedSince' => $changed_since,
		);

		$response = $this->call( $api_data, 'GetChangedMembers' );

		if ( ! class_exists( 'XML2Array' ) ) {
			require_once APSA_SSO_DIR . 'includes/libraries/XML2Array.php';
		}

		if ( $response ) {
			$response = XML2Array::createArray( $response );
		}

		if ( ! is_array( $response ) ) {
			return false;
		}

		return $response;
	}

	/**
	 * Get group members data
	 *
	 * @param  string $group_key  NOAH group key
	 * @param  string $group_type Type of group (sections or committees )
	 * @return array
	 */
	public function fetch_group_members( $group_key = '', $group_type = '' ) {

		if ( empty( $group_key ) ) {
			error_log( 'key: ' . $group_key );
			return false;
		}

		if ( empty( $group_key ) ) {
			error_log( 'Type: ' . $group_type );
			return false;
		}

		$api_data = array(
			'Securitykey' => $this->security_key,
			'groupType' => $group_type,
			'groupKey' => $group_key,
		);

		$response = $this->call( $api_data, 'GetGroupMembers' );

		if ( ! class_exists( 'XML2Array' ) ) {
			require_once APSA_SSO_DIR . 'includes/libraries/XML2Array.php';
		}

		if ( $response ) {
			$response = XML2Array::createArray( $response );
		}

		if ( ! is_array( $response ) ) {
			return false;
		}

		return $response;
	}
}
