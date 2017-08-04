<?php
/**
 * Plugin Name: APSA Sync
 * Plugin URI:  http://schoolpresser.com
 * Description: Plugin for syncing data from API
 * Version:     1.1.0
 * Author:      SchoolPresser
 * Author URI:  http://schoolpresser.com
 * Donate link: http://schoolpresser.com
 * License:     GPLv2
 * Text Domain: apsa-sync
 * Domain Path: /languages
 *
 * @link http://schoolpresser.com
 *
 * @package APSA Sync
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

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	  exit;
}

if ( ! class_exists( 'APSA_Sync_Processing' ) ) {

	/**
	 * Main APSA_Sync_Processing class.
	 *
	 * @since       1.0.0
	 */

	class APSA_Sync_Processing {

		/**
		 * Singleton instance of plugin
		 *
		 * @var TaskBot_Loader
		 * @since  1.0.0
		 */
		protected static $single_instance = null;

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
		 * Example_Background_Processing constructor.
		 */
		public function __construct() {
			add_action( 'plugins_loaded', array( $this, 'wpinit' ) );
			//add_action( 'taskbot_include', array( $this, 'init' ) );
			//add_action( 'bp_include', array( $this, 'bpfunc' ) );
		}

		public function wpinit() {
			include_once plugin_dir_path( __FILE__ ) . 'inc/api-endpoints.php';
			include_once plugin_dir_path( __FILE__ ) . 'inc/sync-user-data.php';
			include_once plugin_dir_path( __FILE__ ) . 'inc/user-import.php';
			include_once plugin_dir_path( __FILE__ ) . 'inc/batch-sync.php';
		}

		/**
		 * Init
		 */
		public function init() {
			include_once plugin_dir_path( __FILE__ ) . 'inc/class-logger.php';
			include_once plugin_dir_path( __FILE__ ) . 'processes/user-data-sync-process.php';
			include_once plugin_dir_path( __FILE__ ) . 'processes/group-member-sync-process.php';

		}

		public function bpfunc() {
			include_once plugin_dir_path( __FILE__ ) . 'inc/bp-functions.php';
		}

	}
}

/**
 * Grab the APSA_Sync_Processing object and return it.
 * Wrapper for APSA_Sync_Processing::get_instance()
 *
 * @since  1.0.0
 * @return APSA_Sync_Processing singleton instance of plugin class.
 */
function apsa_sync() {
	return APSA_Sync_Processing::get_instance();
}
apsa_sync();
