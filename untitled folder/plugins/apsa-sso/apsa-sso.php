<?php
/**
 * Plugin Name:     APSA SSO
 * Description:     Single sign-on plugin for APSA
 * Version:         1.0.0
 * Author:          Daniel J Griffiths
 * Author URI:      https://section214.com
 * Text Domain:     apsa-sso
 *
 * @package         APSA_SSO
 * @author          Daniel J Griffiths <dgriffiths@section214.com>
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Main APSA_SSO class
 *
 * @since       1.0.0
 */
if ( ! class_exists( 'APSA_SSO' ) ) {

	class APSA_SSO {

		/**
		 * @var         APSA_SSO $instance The one true APSA_SSO
		 * @since       1.0.0
		 */
		private static $instance;


		/**
		 * @var         object|APSA_SSO_Session This holds anything stored in the session
		 * @since       1.0.0
		 */
		public $session;


		/**
		 * @var         object|APSA_SSO_Webservice This holds the webservice API object
		 * @since       1.0.0
		 */
		public $webservice;


		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      object self::$instance The one true APSA_SSO
		 */
		public static function instance() {
			if ( ! self::$instance ) {
				self::$instance = new APSA_SSO();
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->load_textdomain();
				self::$instance->session    = new APSA_SSO_Session();
				self::$instance->webservice = new APSA_SSO_Webservice();
			}

			return self::$instance;
		}


		/**
		 * Setup plugin constants
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function setup_constants() {
			// Plugin path
			define( 'APSA_SSO_DIR', plugin_dir_path( __FILE__ ) );

			// Plugin URL
			define( 'APSA_SSO_URL', plugin_dir_url( __FILE__ ) );

			// Plugin version
			define( 'APSA_SSO_VER', '1.0.0' );
		}

		/**
		 * Include necessary files
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function includes() {
			require_once APSA_SSO_DIR . 'includes/actions.php';
			require_once APSA_SSO_DIR . 'includes/error-tracking.php';
			require_once APSA_SSO_DIR . 'includes/functions.php';
			require_once APSA_SSO_DIR . 'includes/login-functions.php';
			require_once APSA_SSO_DIR . 'includes/shortcodes.php';
			require_once APSA_SSO_DIR . 'includes/template-functions.php';
			require_once APSA_SSO_DIR . 'includes/class.apsa-sso-session.php';
			require_once APSA_SSO_DIR . 'includes/class.apsa-sso-webservice.php';
			require_once APSA_SSO_DIR . 'includes/xprofile-functions.php';

		}


		/**
		 * Load plugin language files
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      void
		 */
		public function load_textdomain() {
			// Set filter for language directory
			$lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
			$lang_dir = apply_filters( 'apsa_sso_lang_dir', $lang_dir );

			// WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), 'apsa-sso' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'apsa-sso', $locale );

			// Setup paths to current locale file
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/apsa-sso/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/apsa-sso folder
				load_textdomain( 'apsa-sso', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/apsa-sso/languages/ filder
				load_textdomain( 'apsa-sso', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'apsa-sso', false, $lang_dir );
			}
		}
	}
}


/**
 * The main function responsible for returning the one true APSA_SSO
 * instance to functions everywhere.
 *
 * @since       1.0.0
 * @return      APSA_SSO The one true APSA_SSO
 */
function apsa_sso() {
	return APSA_SSO::instance();
}
add_action( 'plugins_loaded', 'apsa_sso' );
