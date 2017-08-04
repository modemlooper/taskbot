<?php
/**
 * TaskBot Metabox Class.
 *
 * @package TaskBot
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'TaskBot_Metaboxes' ) ) :

	/**
	 * Load TaskBot metaboxes.
	 *
	 * @since 1.0.0
	 */
	class TaskBot_Metaboxes {

		/**
		 * Parent plugin class.
		 *
		 * @var object
		 * @since 1.0.0
		 */
		protected $plugin = null;

		/**
		 * Holds an instance of the object.
		 *
		 * @var object TaskBot_Metaboxes
		 * @since 1.0.0
		 */
		private static $instance = null;

		public $tasks = array();

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param object $plugin this class.
		 */
		public function __construct( $plugin ) {
			$this->plugin = $plugin;
			$this->hooks();
		}

		/**
		 * Initiate our hooks.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function hooks() {
			add_action( 'cmb2_admin_init', array( $this, 'metaboxes' ) );
			add_action( 'cmb2_admin_init', array( $this, 'task_fields' ) );
		}

		/**
		 * CBM2 metaboxes config
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function metaboxes() {

			$prefix = '_taskbot_';

			/**
			 * Initiate the metabox
			 */
			$cmb = new_cmb2_box( array(
				'id'            => 'task_metabox',
				'title'         => __( 'Task', 'taskbot' ),
				'object_types'  => array( 'taskbot' ),
				'context'       => 'normal',
				'priority'      => 'high',
				'show_names'    => true,
			) );

			$cmb->add_field( array(
				'name'             => 'Task',
				'desc'             => 'Select an option',
				'id'               => $prefix . 'task',
				'type'             => 'select',
				'show_option_none' => true,
				'default'          => 'custom',
				'options'          => $this->get_tasks(),
			) );

			// Sidebar.
			$cmb2 = new_cmb2_box( array(
				'id'            => 'task_schedule',
				'title'         => __( 'Schedule', 'taskbot' ),
				'object_types'  => array( 'taskbot' ),
				'context'       => 'side',
				'priority'      => 'low',
				'show_names'    => true,
			) );

			$cmb2->add_field( array(
				'name' => 'Date & Time to run this task',
				'id'   => $prefix . 'datetime_timestamp',
				'desc' => 'The date and time must be more than 10 min in the future.',
				'type' => 'text_datetime_timestamp',
			) );

			$cmb2->add_field( array(
				'name' => 'Recurrence',
				'id'   => $prefix . 'recurrence',
				'description' => 'Choose an interval to rerun this task. One Time will run the task one time at the set date and time.',
				'type' => 'select',
				'options' => $this->get_schedules(),
			) );
		}

		/**
		 * Add cmb2 fields from task data
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function task_fields() {

			$tb = taskbot()->tasks;
			$prefix = '_taskbot_';

			foreach ( $tb as $task ) {

				/**
				 * Initiate the metabox
				 */
				$cmb = new_cmb2_box( array(
					'id'            => $task->task['id'],
					'title'         => $task->task['title'] . ' Options',
					'object_types'  => array( 'taskbot' ),
					'context'       => 'normal',
					'priority'      => 'high',
					'show_names'    => true,
					'attributes' => array( 'classes' => 'task-option' ),
				) );

				// Add each field.
				if ( isset( $task->task['fields'] ) && ! empty( $task->task['fields'] ) ) {
					foreach ( $task->task['fields'] as $field ) {
						$cmb->add_field( $field );
					}
				}
			}

		}

		/**
		 * Array of cron schedule intervals
		 *
		 * @since 1.0.0
		 * @return array
		 */
		public function get_schedules() {

			$schedules = wp_get_schedules();
			$sched_arr = array();

			foreach ( $schedules as $key => $value ) {
				$sched_arr[ $key ] = $value['display'];
			}

			unset( $sched_arr['wp_taskbot_process_cron_interval'] );

			$sched_arr['once'] = 'One Time';

			return apply_filters( 'taskbot_get_schedules', $sched_arr );

		}

		/**
		 * Array of tasks
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function get_tasks() {

			$tasks = taskbot()->tasks;

			foreach ( $tasks as $key => $value ) {
				if ( isset( $value->task['title'] ) && ! empty( $value->task['title'] ) ) {
					$this->tasks[ $value->task['id'] ] = $value->task['title'];
				}
			}

			return $this->tasks;
		}
	}
endif; // End class_exists check.
