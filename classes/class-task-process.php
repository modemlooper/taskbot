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
			//add_action( 'init', array( $this, 'set_actions' ) );
		}

		/**
		 * Process taskbot post item.
		 *
		 * @param  integer $post_id
		 * @since 1.0.0
		 * @return void
		 */
		public function post_save( $post_id ) {

			if ( ! isset( $_POST['_taskbot_task'] ) && ! empty( $_POST['_taskbot_task'] ) ) {
				return;
			}

			$tb = TaskBot_Base::get( $_POST['_taskbot_task'] );
			$tb_id = sanitize_text_field( wp_unslash( $_POST['_taskbot_task'] ) );
			$tb_recurring = isset( $_POST['_taskbot_recurrence'] ) ? $_POST['_taskbot_recurrence'] : 'daily';
			$tb_date = isset( $_POST['_taskbot_datetime_timestamp'] ) ? $_POST['_taskbot_datetime_timestamp'] : '';

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

			$tasks = $this->update( $post_id, array(
				'id' => $tb_id,
				'recurring' => $tb_recurring,
				'schedule' => $tb_date,
			) );

			$args = array(
				array(
					'post_id' => $post_id,
					'task' => $tb_id,
				),
			);

			$schedule = 'taskbot_run_' . $tb_id;

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

			//tb_error_log( $tb_recurring );
			//tb_error_log( $timestamp );
			//tb_error_log( $now );
			//tb_error_log( $nowtimestamp );
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

		public function set_actions() {

			$task_option = get_site_option( 'taskbot_tasks' );

			foreach ( $task_option as $key => $task  ) {

				if ( isset( $task['callback'] ) ) {
					add_action( 'taskbot_run_' . $key . '_test_task', $task['callback'] );
				}
			}
		}
	}

endif; // End class_exists check.
