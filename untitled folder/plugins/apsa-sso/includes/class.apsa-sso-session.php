<?php
/**
 * APSA SSO Session
 *
 * This is a wrapper class for WP_Session / PHP $_SESSION
 *
 * @package         APSA_SSO\Session
 * @since           1.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * APSA_SSO_Session Class
 *
 * @since       1.0.0
 */
class APSA_SSO_Session {

	/**
	 * @access      private
	 * @since       1.0.0
	 * @var         array $session Holds our session data
	 */
	private $session;


	/**
	 * @access      private
	 * @since       1.0.0
	 * @var         bool $use_php_sessions Whether to use PHP $_SESSION or WP_Session
	 */
	private $use_php_sessions = false;


	/**
	 * @access      private
	 * @since       1.0.0
	 * @var         string $prefix Session index prefix
	 */
	private $prefix = '';


	/**
	 * Get things started
	 *
	 * Defines our WP_Session constants, includes the necessary libraries and
	 * retrieves the WP Session instance
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      void
	 */
	public function __construct() {
		$this->use_php_sessions = $this->use_php_sessions();

		if ( $this->use_php_sessions ) {
			if ( is_multisite() ) {
				$this->prefix = '_' . get_current_blog_id();
			}

			// Use PHP SESSION (must be enabled via the APSA_SSO_USE_PHP_SESSIONS constant)
			add_action( 'init', array( $this, 'maybe_start_session' ), -2 );
		} else {
			if ( ! $this->should_start_session() ) {
				return;
			}

			// Use WP_Session (default)
			if ( ! defined( 'WP_SESSION_COOKIE' ) ) {
				define( 'WP_SESSION_COOKIE', 'apsa_sso_wp_session' );
			}

			if ( ! class_exists( 'Recursive_ArrayAccess' ) ) {
				require_once APSA_SSO_DIR . 'includes/libraries/class-recursive-arrayaccess.php';
			}

			if ( ! class_exists( 'WP_Session' ) ) {
				require_once APSA_SSO_DIR . 'includes/libraries/class-wp-session.php';
				require_once APSA_SSO_DIR . 'includes/libraries/wp-session.php';
			}

			add_filter( 'wp_session_expiration_variant', array( $this, 'set_expiration_variant_time' ), 99999 );
			add_filter( 'wp_session_expiration', array( $this, 'set_expiration_time' ), 99999 );
		}

		if ( empty( $this->session ) && ! $this->use_php_sessions ) {
			add_action( 'plugins_loaded', array( $this, 'init' ), -1 );
		} else {
			add_action( 'init', array( $this, 'init' ), -1 );
		}
	}


	/**
	 * Setup the WP_Session instance
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      void
	 */
	public function init() {
		if ( $this->use_php_sessions ) {
			$this->session = isset( $_SESSION['apsa_sso' . $this->prefix ] ) && is_array( $_SESSION['apsa_sso' . $this->prefix ] ) ? $_SESSION['apsa_sso' . $this->prefix ] : array();
		} else {
			$this->session = WP_Session::get_instance();
		}

		return $this->session;
	}


	/**
	 * Retrieve session ID
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      string Session ID
	 */
	public function get_id() {
		return $this->session->session_id;
	}


	/**
	 * Retrieve a session variable
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       string $key Session key
	 * @return      string Session variable
	 */
	public function get( $key ) {
		$key = sanitize_key( $key );
		return isset( $this->session[ $key ] ) ? maybe_unserialize( $this->session[ $key ] ) : false;
	}


	/**
	 * Set a session variable
	 *
	 * @access      public
	 * @since       1.0.0
	 * @param       string $key Session key
	 * @param       int $value Session variable
	 * @return      string Session variable
	 */
	public function set( $key, $value ) {
		$key = sanitize_key( $key );

		if ( is_array( $value ) ) {
			$this->session[ $key ] = serialize( $value );
		} else {
			$this->session[ $key ] = $value;
		}

		if ( $this->use_php_sessions ) {
			$_SESSION['apsa_sso' . $this->prefix ] = $this->session;
		}

		return $this->session[ $key ];
	}


	/**
	 * Starts a new session if one hasn't started yet.
	 *
	 * Checks to see if the server supports PHP sessions
	 * or if the APSA_SSO_USE_PHP_SESSIONS constant is defined
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      bool $ret True if we are using PHP sessions, false otherwise
	 */
	public function use_php_sessions() {
		$ret = false;

		// If the database variable is already set, no need to run autodetection
		$apsa_sso_use_php_sessions = (bool) get_option( 'apsa_sso_use_php_sessions' );

		if ( ! $apsa_sso_use_php_sessions ) {

			// Attempt to detect if the server supports PHP sessions
			if ( function_exists( 'session_start' ) ) {
				$this->set( 'apsa_sso_use_php_sessions', 1 );

				if ( $this->get( 'apsa_sso_use_php_sessions' ) ) {
					$ret = true;

					// Set the database option
					update_option( 'apsa_sso_use_php_sessions', true );
				}
			}
		} else {
			$ret = $apsa_sso_use_php_sessions;
		}

		// Enable or disable PHP Sessions based on the APSA_SSO_USE_PHP_SESSIONS constant
		if ( defined( 'APSA_SSO_USE_PHP_SESSIONS' ) && APSA_SSO_USE_PHP_SESSIONS ) {
			$ret = true;
		} elseif ( defined( 'APSA_SSO_USE_PHP_SESSIONS' ) && ! APSA_SSO_USE_PHP_SESSIONS ) {
			$ret = false;
		}

		return (bool) apply_filters( 'apsa_sso_use_php_sessions', $ret );
	}


	/**
	 * Determines if we should start sessions
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      bool
	 */
	public function should_start_session() {
		$start_session = true;

		if ( ! empty( $_SERVER[ 'REQUEST_URI' ] ) ) {
			$blacklist = $this->get_blacklist();
			$uri       = ltrim( $_SERVER[ 'REQUEST_URI' ], '/' );
			$uri       = untrailingslashit( $uri );

			if ( in_array( $uri, $blacklist ) ) {
				$start_session = false;
			}

			if ( false !== strpos( $uri, 'feed=' ) ) {
				$start_session = false;
			}
		}

		return apply_filters( 'apsa_sso_start_session', $start_session );
	}


	/**
	 * Retrieve the URI blacklist
	 *
	 * These are the URIs where we never start sessions
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      array
	 */
	public function get_blacklist() {
		$blacklist = apply_filters( 'apsa_sso_session_start_uri_blacklist', array(
			'feed',
			'feed/rss',
			'feed/rss2',
			'feed/rdf',
			'feed/atom',
			'comments/feed'
		) );

		// Look to see if WordPress is in a sub folder or this is a network site that uses sub folders
		$folder = str_replace( network_home_url(), '', get_site_url() );

		if ( ! empty( $folder ) ) {
			foreach ( $blacklist as $path ) {
				$blacklist[] = $folder . '/' . $path;
			}
		}

		return $blacklist;
	}


	/**
	 * Starts a new session if one hasn't started yet.
	 */
	public function maybe_start_session() {
		if ( ! $this->should_start_session() ) {
			return;
		}

		if ( ! session_id() && ! headers_sent() ) {
			session_start();
		}
	}
}
