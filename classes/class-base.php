<?php
/**
 *
 * TaskBot_Base Class.
 *
 * @package TaskBot
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'TaskBot_Base' ) ) :

	/**
	 * Register Tasks.
	 *
	 * @since 1.0.0
	 */
	class TaskBot_Base {

		/**
		 * Tasks object.
		 *
		 * @var object
		 * @since 1.0.0
		 */
		protected static $tb_instances = array();

		/**
		 * Add a TaskBot_Tasks instance object to the registry.
		 *
		 * @since 1.0.0
		 * @param TaskBot_Tasks $tb_instance TaskBot_Tasks instance.
		 */
		public static function add( TaskBot_Tasks $tb_instance ) {
			//taskbot()->tasks[ $tb_instance->tb_id ] = $tb_instance;
			self::$tb_instances[ $tb_instance->tb_id ] = $tb_instance;
		}

		/**
		 * Remove a TaskBot_Tasks instance object from the registry.
		 *
		 * @since 1.0.0
		 * @param string $tb_id A TaskBot_Tasks instance id.
		 */
		public static function remove( $tb_id ) {
			if ( array_key_exists( $tb_id, self::$tb_instances ) ) {
				unset( self::$tb_instances[ $tb_id ] );
			}
		}

		/**
		 * Get task.
		 *
		 * @since 1.0.0
		 * @param string $tb_id A TaskBot_Tasks instance id.
		 */
		public static function get( $tb_id ) {

			if ( empty( self::$tb_instances ) || empty( self::$tb_instances[ $tb_id ] ) ) {
				return false;
			}

			return self::$tb_instances[ $tb_id ];

		}

		/**
		 * Retrieve all TaskBot instances registered.
		 *
		 * @since  1.0.0
		 * @return TaskBot[] Array of all registered TaskBot instances.
		 */
		public static function get_all() {
			return self::$tb_instances;
		}
	}
endif ;
