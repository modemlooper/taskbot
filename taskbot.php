<?php
/**
 * Plugin Name: TaskBot
 * Plugin URI:  https://wptaskbot.com
 * Description: Background task processing for WordPress.
 * Version:     1.0.0
 * Author:      modemlooper
 * Author URI:  https://wptaskbot.com
 * Donate link: https://wptaskbot.com
 * License:     GPLv2
 * Text Domain: taskbot
 * Domain Path: /languages
 *
 * @link https://wptaskbot.com
 *
 * @package TaskBot
 * @version 1.0.0
 */

/**
 * Copyright (c) 2017 TaskBot (email : contact@wptaskbot.com)
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

/** SHOUTOUT:
 This plugin uses:
 script from https://github.com/A5hleyRich/wp-background-processing
 And CMB2 https://github.com/CMB2/CMB2
 **/

/**
 * Autoloads files with classes when needed
 *
 * @since  1.0.0
 * @param  string $class_name Name of the class being requested.
 * @return void
 */
function taskbot_autoload_classes( $class_name ) {
	if ( 0 !== strpos( $class_name, 'TaskBot_' ) ) {
		return;
	}

	$filename = strtolower( str_replace(
		'_', '-',
		substr( $class_name, strlen( 'TaskBot_' ) )
	) );

	TaskBot_Loader::include_file( $filename );
}
spl_autoload_register( 'taskbot_autoload_classes' );

/**
 * Main initiation class
 *
 * @since  1.0.0
 */
final class TaskBot_Loader {

	/**
	 * Current version
	 *
	 * @var  string
	 * @since  1.0.0
	 */
	const VERSION = '1.0.0';

	/**
	 * URL of plugin directory
	 *
	 * @var string
	 * @since  1.0.0
	 */
	protected $url = '';

	/**
	 * Path of plugin directory
	 *
	 * @var string
	 * @since  1.0.0
	 */
	protected $path = '';

	/**
	 * Plugin basename
	 *
	 * @var string
	 * @since  1.0.0
	 */
	protected $basename = '';

	/**
	 * Singleton instance of plugin
	 *
	 * @var TaskBot_Loader
	 * @since  1.0.0
	 */
	protected static $single_instance = null;

	/**
	 * Settings pages
	 *
	 * @var TaskBot_Settings
	 * @since  1.0.0
	 */
	protected $settings = '';

	/**
	 * Custom post type
	 *
	 * @var TaskBot_CPT
	 * @since  1.0.0
	 */
	protected $cpt = '';


	/**
	 * CMB2
	 *
	 * @var TaskBot_Metaboxes
	 * @since  1.0.0
	 */
	protected $metaboxes = '';

	/**
	 * Batch object to process.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $batch = '';

	public $tasks = array();

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since  1.0.0
	 * @return TaskBot_Loader A single instance of this class.
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
	 * @since  1.0.0
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );


		$this->load_libs();
		$this->plugin_classes();


	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function plugin_classes() {

		$this->settings = new TaskBot_Settings();
		$this->cpt = new TaskBot_CPT( $this );
		$this->batch = new TaskBot_Batch();

		new TaskBot_Task_Process();

	}

	/**
	 * Add hooks and filters
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Activate the plugin
	 *
	 * @since  1.0.0
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
	 * @since  1.0.0
	 * @return void
	 */
	public function _deactivate() {}

	/**
	 * Init hooks
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function init() {
		if ( $this->check_requirements() ) {
			load_plugin_textdomain( 'taskbot', false, dirname( $this->basename ) . '/languages/' );
		}

		$this->loaded();

	}

	public function loaded() {
		$this->includes();

		do_action( 'taskbot_init' );
		$this->tasks = TaskBot_Base::get_all();
		add_action( 'admin_print_scripts', array( $this, 'scripts' ) );

		require_once  __DIR__ . '/inc/helper-functions.php';
		$this->metaboxes = new TaskBot_Metaboxes( $this );

	}

	/**
	 * The includes function.
	 *
	 * @access private
	 * @return void
	 */
	private function includes() {

		require_once  __DIR__ . '/inc/tb-functions.php';

		do_action( 'taskbot_include' );

		require_once  __DIR__ . '/sample-tasks/site-stats.php';

	}

	/**
	 * Load libraries.
	 *
	 * @since 1.0.0
	 */
	private function load_libs() {

		// Load cmb2.
		if ( file_exists( __DIR__ . '/vendors/cmb2/init.php' ) ) {
			require_once  __DIR__ . '/vendors/cmb2/init.php';
		} elseif ( file_exists( __DIR__ . '/vendors/CMB2/init.php' ) ) {
			require_once  __DIR__ . '/vendors/CMB2/init.php';
		}

		require_once __DIR__ . '/vendors/wp-bg-processing/classes/wp-async-request.php';
		require_once __DIR__ . '/vendors/wp-bg-processing/classes/wp-background-process.php';

	}

	/**
	 * Check if the plugin meets requirements and
	 * disable it if they are not present.
	 *
	 * @since  1.0.0
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

		global $typenow;

		if ( 'taskbot' === $typenow ) {
			// Register out javascript file.
			wp_register_script( 'taskbot', taskbot()->url() . 'assets/js/taskbot.js' );

			wp_localize_script( 'taskbot', 'taskbot', taskbot()->tasks );

			// Enqueued script with localized data.
			wp_enqueue_script( 'taskbot' );
		}

	}

	/**
	 * Deactivates this plugin, hook this function on admin_init.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function deactivate_me() {
		deactivate_plugins( $this->basename );
	}

	/**
	 * Check that all plugin requirements are met
	 *
	 * @since  1.0.0
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
	 * @since  1.0.0
	 * @return void
	 */
	public function requirements_not_met_notice() {
		// Output our error.
		echo '<div id="message" class="error">';
		echo '<p>' . sprintf( __( 'TaskBot is missing requirements and has been <a href="%s">deactivated</a>. Please make sure all requirements are available.', 'taskbot' ), admin_url( 'plugins.php' ) ) . '</p>';
		echo '</div>';
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  1.0.0
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
	 * @since  1.0.0
	 * @param  string $filename Name of the file to be included.
	 * @return bool   Result of include call.
	 */
	public static function include_file( $filename ) {
		$file = self::dir( 'classes/class-' . $filename . '.php' );
		if ( file_exists( $file ) ) {
			return include_once( $file );
		}
		return false;
	}

	/**
	 * This plugin's directory
	 *
	 * @since  1.0.0
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
	 * @since  1.0.0
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
 * Grab the TaskBot_Loader object and return it.
 * Wrapper for TaskBot_Loader::get_instance()
 *
 * @since  1.0.0
 * @return TaskBot_Loader singleton instance of plugin class.
 */
function taskbot() {
	return TaskBot_Loader::get_instance();
}
// Kick it off.
add_action( 'plugins_loaded', array( taskbot(), 'hooks' ) );

register_activation_hook( __FILE__ , array( taskbot(), '_activate' ) );
register_deactivation_hook( __FILE__ , array( taskbot(), '_deactivate' ) );
