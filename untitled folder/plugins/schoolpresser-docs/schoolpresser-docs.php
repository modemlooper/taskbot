<?php
/**
 * Plugin Name: SchoolPresser Docs
 * Plugin URI:  http://schoolpresser.com
 * Description: docs plugin for BuddyPress
 * Version:     1.0.0
 * Author:      SchoolPresser
 * Author URI:  http://schoolpresser.com
 * Donate link: http://schoolpresser.com
 * License:     GPLv2
 * Text Domain: schoolpresser-docs
 * Domain Path: /languages
 *
 * @link http://schoolpresser.com
 *
 * @package SchoolPress Docs
 * @version 1.0.0
 */

/**
 * Copyright (c) 2017 SchoolPresser (email : bmessenlehner@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Built using generator-plugin-wp
 */

DEFINE( 'BP_DOCS_SLUG', 'documents' );

/**
 * Autoloads files with classes when needed
 *
 * @since  NEXT
 * @param  string $class_name Name of the class being requested.
 * @return void
 */
function schoolpresser_docs_autoload_classes( $class_name ) {
	if ( 0 !== strpos( $class_name, 'SPD_' ) ) {
		return;
	}

	$filename = strtolower( str_replace(
		'_', '-',
		substr( $class_name, strlen( 'SPD_' ) )
	) );

	SchoolPresser_Docs::include_file( $filename );
}
spl_autoload_register( 'schoolpresser_docs_autoload_classes' );

/**
 * Main initiation class
 *
 * @since  NEXT
 */
final class SchoolPresser_Docs {

	/**
	 * Current version
	 *
	 * @var  string
	 * @since  NEXT
	 */
	const VERSION = '1.0.0';

	/**
	 * URL of plugin directory
	 *
	 * @var string
	 * @since  NEXT
	 */
	protected $url = '';

	/**
	 * Path of plugin directory
	 *
	 * @var string
	 * @since  NEXT
	 */
	protected $path = '';

	/**
	 * Plugin basename
	 *
	 * @var string
	 * @since  NEXT
	 */
	protected $basename = '';

	/**
	 * Singleton instance of plugin
	 *
	 * @var SchoolPresser_Docs
	 * @since  NEXT
	 */
	protected static $single_instance = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since  NEXT
	 * @return SchoolPresser_Docs A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin
	 *
	 * @since  NEXT
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function plugin_classes() {}

	/**
	 * Add hooks and filters
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function hooks() {
		$this->includes();
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Activate the plugin
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function _activate() {
		// Make sure any rewrite functionality has been loaded.
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin
	 * Uninstall routines should be in uninstall.php
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function _deactivate() {}

	/**
	 * Init hooks
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function init() {
		if ( $this->check_requirements() ) {
			load_plugin_textdomain( 'schoolpresser-docs', false, dirname( $this->basename ) . '/languages/' );
			$this->plugin_classes();
			$this->scripts();
		}
	}

	/**
	 * The includes function.
	 *
	 * @access private
	 * @return void
	 */
	private function includes() {
		require( dirname( __FILE__ ) . '/inc/bp-docs-function.php' );
		require( dirname( __FILE__ ) . '/inc/bp-docs-filters.php' );
		require( dirname( __FILE__ ) . '/inc/bp-docs-cpt.php' );
		require( dirname( __FILE__ ) . '/inc/bp-docs-loader.php' );
		require( dirname( __FILE__ ) . '/inc/bp-docs-screens.php' );
		require( dirname( __FILE__ ) . '/inc/bp-docs-endpoints.php' );
		require( dirname( __FILE__ ) . '/inc/bp-docs-group-extension.php' );
	}

	/**
	 * Check if the plugin meets requirements and
	 * disable it if they are not present.
	 *
	 * @since  NEXT
	 * @return boolean result of meets_requirements
	 */
	public function check_requirements() {
		if ( ! $this->meets_requirements() ) {

			// Add a dashboard notice.
			add_action( 'all_admin_notices', array( $this, 'requirements_not_met_notice' ) );

			// Deactivate our plugin.
			add_action( 'admin_init', array( $this, 'deactivate_me' ) );

			return false;
		}

		return true;
	}

	/**
	 * Register scripts.
	 *
	 * @since 1.0.0
	 */
	public function scripts() {

		if ( bp_current_component( 'documents' ) ) {

			// Register CSS file.
			wp_register_style( 'schoolpresser_docs', schoolpresser_docs()->url() . 'css/bp-docs.css' );
			wp_enqueue_style( 'schoolpresser_docs' );

			// Register js file.
			wp_enqueue_script( 'wp-api' );

			wp_register_script( 'jq_uploads', schoolpresser_docs()->url() . 'js/jq-upload.js', $deps = array( 'jquery', 'jquery-ui-widget', 'jquery-ui-autocomplete', 'jquery-ui-core', 'jquery-ui-position' ) );
			wp_enqueue_script( 'jq_uploads' );

			wp_register_script( 'schoolpresser_docs', schoolpresser_docs()->url() . 'js/bp-docs.js', $deps = array( 'jquery', 'wp-api' ) );
			do_action( 'bp_docs_load_localizations' );
			wp_enqueue_script( 'schoolpresser_docs' );
		}
	}

	/**
	 * Deactivates this plugin, hook this function on admin_init.
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function deactivate_me() {
		deactivate_plugins( $this->basename );
	}

	/**
	 * Check that all plugin requirements are met
	 *
	 * @since  NEXT
	 * @return boolean True if requirements are met.
	 */
	public static function meets_requirements() {
		// Do checks for required classes / functions
		// function_exists('') & class_exists('').
		// We have met all requirements.
		return true;
	}

	/**
	 * Adds a notice to the dashboard if the plugin requirements are not met
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function requirements_not_met_notice() {
		// Output our error.
		echo '<div id="message" class="error">';
		echo '<p>' . sprintf( __( 'SchoolPress Docs is missing requirements and has been <a href="%s">deactivated</a>. Please make sure all requirements are available.', 'schoolpresser-docs' ), admin_url( 'plugins.php' ) ) . '</p>';
		echo '</div>';
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  NEXT
	 * @param string $field Field to get.
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return self::VERSION;
			case 'basename':
			case 'url':
			case 'path':
			case 'docs':
			case 'component':
				return $this->$field;
			default:
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}

	/**
	 * Include a file from the includes directory
	 *
	 * @since  NEXT
	 * @param  string $filename Name of the file to be included.
	 * @return bool   Result of include call.
	 */
	public static function include_file( $filename ) {
		$file = self::dir( 'includes/class-' . $filename . '.php' );
		if ( file_exists( $file ) ) {
			return include_once( $file );
		}
		return false;
	}

	/**
	 * This plugin's directory
	 *
	 * @since  NEXT
	 * @param  string $path (optional) appended path.
	 * @return string       Directory and path
	 */
	public static function dir( $path = '' ) {
		static $dir;
		$dir = $dir ? $dir : trailingslashit( dirname( __FILE__ ) );
		return $dir . $path;
	}

	/**
	 * This plugin's url
	 *
	 * @since  NEXT
	 * @param  string $path (optional) appended path.
	 * @return string       URL and path
	 */
	public static function url( $path = '' ) {
		static $url;
		$url = $url ? $url : trailingslashit( plugin_dir_url( __FILE__ ) );
		return $url . $path;
	}
}

/**
 * Grab the SchoolPresser_Docs object and return it.
 * Wrapper for SchoolPresser_Docs::get_instance()
 *
 * @since  NEXT
 * @return SchoolPresser_Docs  Singleton instance of plugin class.
 */
function schoolpresser_docs() {
	return SchoolPresser_Docs::get_instance();
}
// Kick it off.
add_action( 'bp_include', array( schoolpresser_docs(), 'hooks' ) );

register_activation_hook( __FILE__ , array( schoolpresser_docs(), '_activate' ) );
register_deactivation_hook( __FILE__ , array( schoolpresser_docs(), '_deactivate' ) );


// BUG there seems to be an upload bug for non image uploads see https://core.trac.wordpress.org/ticket/39550.
function apsa_disable_real_mime_check( $data, $file, $filename, $mimes ) {
	$wp_filetype = wp_check_filetype( $filename, $mimes );

	$ext = $wp_filetype['ext'];
	$type = $wp_filetype['type'];
	$proper_filename = $data['proper_filename'];

	return compact( 'ext', 'type', 'proper_filename' );
}
add_filter( 'wp_check_filetype_and_ext', 'apsa_disable_real_mime_check', 10, 4 );
