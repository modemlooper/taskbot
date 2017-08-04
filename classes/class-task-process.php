<?php
/**
 *
 * TaskBot_Task_Process Class.
 *
 * @package TaskBot
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'TaskBot_Task_Process' ) ) :

	/**
	 * Load Tasks.
	 *
	 * @since 1.0.0
	 */
	class TaskBot_Task_Process {

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 * @param object $config this task.
		 */
		public function __construct() {
			add_action( 'save_post_taskbot', array( $this, 'post_save' ) );
			add_action( 'delete_post', array( $this, 'post_delete' ) );
			add_action( 'trashed_post', array( $this, 'post_delete' ) );
			add_action( 'init', array( $this, 'set_actions' ) );

		}

		/**
		 * Process taskbot post item.
		 *
		 * @param  integer $post_id
		 * @since 1.0.0
		 * @return void
		 */
		public function post_save( $post_id ) {

			if ( ! isset( $_POST['_taskbot_task'] ) || empty( $_POST['_taskbot_task'] ) ) {
				return;
			}

			$tb = TaskBot_Base::get( $_POST['_taskbot_task'] );
			$tb_id = sanitize_text_field( wp_unslash( $_POST['_taskbot_task'] ) );
			$tb_recurring = isset( $_POST['_taskbot_recurrence'] ) ? $_POST['_taskbot_recurrence'] : 'daily';
			$tb_date = isset( $_POST['_taskbot_datetime_timestamp'] ) ? $_POST['_taskbot_datetime_timestamp'] : '';

			if ( isset( $tb_date['date'] ) && empty( $tb_date['date'] ) ) {
				return;
			}

			$tb_date = array_map( 'esc_attr', $tb_date );

			// Get current timestamp.
			$now = new DateTime();
			$nowtimestamp = $now->getTimestamp();
			$timestamp = null;

			if ( ! empty( $tb_date ) ) {

				$tb_time = date( 'H:i:s', strtotime( $tb_date['time'] . ' UTC' ) );
				$tb_day = explode( '/', $tb_date['date'] );
				$gmdate = get_gmt_from_date( $tb_date['date'] . ' ' . $tb_time );

				$date = new DateTime( $gmdate );
				$timestamp = $date->getTimestamp();
				$tb_date['timestamp'] = $timestamp;
			}

			$fields_data = $this->process_fields( $tb_id, $_POST );
			$extra_data = isset( $tb->task['data'] ) ? $tb->task['data'] : '';

			$tasks = $this->update( $post_id, array(
				'id' => $tb_id,
				'recurring' => $tb_recurring,
				'schedule' => $tb_date,
				'fields' => $fields_data,
				'data' => $extra_data,
			) );

			$args = array( $post_id );
			$schedule = 'taskbot_do_' . $tb_id;

			if ( $timestamp > $nowtimestamp ) {

				$prev_timestamp = wp_next_scheduled( $schedule, $args );
				wp_unschedule_event( $timestamp, $schedule, $args );

				if ( ! wp_next_scheduled( $prev_timestamp ) ) {

					if ( 'once' === $tb_recurring ) {
						wp_schedule_single_event( $timestamp, $schedule, $args );
					} else {
						wp_schedule_event( $timestamp, $tb_recurring, $schedule, $args );
					}
				}
			}
		}

		/**
		 * Process delete taskbot post item.
		 *
		 * @param  integer $post_id
		 * @since 1.0.0
		 * @return void
		 */
		public function post_delete( $post_id ) {

			if ( 'taskbot' === get_post_type( $post_id ) ) {
				$this->delete( $post_id );
			}
		}

		/**
		 * Update tasks
		 *
		 * @param string $key Key.
		 * @param array  $data Data.
		 * @since 1.0.0
		 * @return array
		 */
		public function update( $key, $data ) {

			$task_option = get_site_option( 'taskbot_tasks' );

			if ( $key && ! empty( $data ) ) {
				$task_option[ $key ] = $data;
				update_site_option( 'taskbot_tasks', $task_option );
			}

			return $task_option;
		}

		/**
		 * Delete tasks
		 *
		 * @param string $key Key.
		 * @since 1.0.0
		 * @return array
		 */
		public function delete( $key ) {

			$task_option = get_site_option( 'taskbot_tasks' );

			if ( $key ) {
				unset( $task_option[ $key ] );
				update_site_option( 'taskbot_tasks', $task_option );
			}

			return $task_option;
		}

		/**
		 * Setup action hook for each task
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function set_actions() {

			$task_option = get_site_option( 'taskbot_tasks' );

			if ( ! empty( $task_option ) ) {
				foreach ( $task_option as $key => $task  ) {
					add_action( 'taskbot_do_' . $task['id'], array( $this, 'add_this_task' ) );
				}
			}

		}

		/**
		 * Hook for cron job to tasks action.
		 *
		 * @since 1.0.0
		 * @param  string $tb_id
		 * @return void
		 */
		public function add_this_task( $tb_id ) {

			$task = taskbot_get_task_by_id( $tb_id );

			/**
			 * This hook should run a function that sends items to be batch processed.
			 * The dynamic portion of the hook name, $task['id'], is this task id.
			 *
			 * @since 1.0.0
			 * @var array $task Tasks data including post meta.
			 */
			do_action( 'taskbot_add_' . $task['id'], $task );

		}

		/**
		 * Returns array of CMB2 fields data for the task
		 *
		 * @since 1.0.0
		 * @param  string $tb_id
		 * @param  array  $data
		 * @return array
		 */
		public function process_fields( $tb_id, $data = array() ) {

			if ( ! $tb_id || empty( $data ) ) {
				return;
			}

			$fields_data = array();

			$task = TaskBot_Base::get( $tb_id );

			if ( $task && ! empty( $task ) ) {
				foreach ( $task->task['fields'] as $key => $value ) {

					if ( isset( $data[ $value['id'] ] ) ) {
						$fields_data[  $value['id'] ] = $data[ $value['id'] ];
					}
				}
			}

			/**
			 * Filters the fields data saved to task. Useful to add extra info you need to process during batch run.
			 *
			 * @since 1.0.0
			 * @var array $fields_data
			 * @var string $tb_id
			 */
			return apply_filters( 'taskbot_task_data_filter', $fields_data, $tb_id );

		}
	}

endif; // End class_exists check.

/**
 * Helper function to add tasks to que.
 *
 * @since 1.0.0
 * @param array $task
 */
function taskbot_add_items( $task = array() ) {

	$defaults = array(
		'task' => '',
		'items' => array(),
		'data' => '',
	);

	$data = wp_parse_args( $task, $defaults );

	if ( ! empty( $data['task'] ) && ! empty( $data['items'] ) ) {

		$array_size = taskbot_get_option( 'taskbot_batch_size' ) ? taskbot_get_option( 'taskbot_batch_size' ) : 500;

		/**
		 * Filter for array chunk size. If you have problems with arrays being to big for server resources. Lower this ammount.
		 *
		 * @var integer $array_size
		 */
		$chunk_amount = apply_filters( 'taskbot_batch_chunk' , $array_size );

		// Break up large arrays so there isnt memory timeout.
		$chunks = array_chunk( $data['items'], $chunk_amount );

		foreach ( $chunks as $key => $value ) {

			taskbot()->batch->add( array(
				'task' => $data['task'],
				'items' => $chunks[ $key ],
				'data' => $data['data'],
			) );
		}
	}
}
