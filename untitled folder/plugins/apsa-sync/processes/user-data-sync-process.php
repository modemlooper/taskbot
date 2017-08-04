<?php

/**
 * Taskbot_User_Sync.
 *
 * @package         Taskbot_User_Sync
 * @author          AplhaWeb
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
		exit;
}


if ( ! class_exists( 'Taskbot_User_Sync' ) ) {

	class Taskbot_User_Sync {

		use WP_Sync_Logger;

		/**
		 * @var         Taskbot_User_Sync $instance
		 * @since       1.0.0
		 */
		private static $instance;

		public $task = 'apsa_user_data';

		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      object self::$instance
		 */
		public static function instance() {
			if ( ! self::$instance ) {
				self::$instance = new Taskbot_User_Sync();
				self::$instance->hooks();
			}

			return self::$instance;
		}

		public function hooks() {
			add_action( 'taskbot_init', array( $this, 'register_task' ) );
			add_action( 'taskbot_add_' . $this->task, array( $this, 'add_items' ) );
			add_action( 'taskbot_run_' . $this->task, array( $this, 'process_item' ), 10, 2 );
			add_action( 'taskbot_batch_complete_' . $this->task, array( $this, 'complete' ) );
		}

		public function register_task() {

			$tb = taskbot_new_task( array(
				'id' => $this->task,
				'title' => 'User Data Sync',
				'description' => 'Syncs changed member data from NOAH',
				'data' => array( 'item' => 1 ),
				'fields' => array(
					array(
						'id' => 'my_field',
						'name' => 'My field',
						'type' => 'text',
					),
				),
			) );

		}


		public function add_items( $task ) {

			$changed_users = apsa_get_changed();

			if ( ! $changed_users ) {
				return;
			}

			$items = array();

			//$changed_users = array_slice( $changed_users, 0, 50 );

			foreach ( $changed_users as $user ) {
				if ( isset( $user['ContactId'] ) && ! empty( $user['ContactId'] ) ) {

					$items[] = $user['ContactId'];
				}
			}

			taskbot_add_items( array(
				'task' => $task,
				'items' => $items,
			) );
		}


		public function process_item( $data ) {

			//time_nanosleep( 0, 300000000 );

			$this->sync_data( $data['item'] );
			//tb_error_log( $data['item'] );

		}


		public function complete( $batch ) {

			$to = 'modemlooper@gmail.com';
			$subject = 'taskbot complete';
			$body = 'The task ' . $this->task . ' completed ' . current_time( 'h:i:s' );
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );

			wp_mail( $to, $subject, $body, $headers );
		}

	}

}

/**
 * The main function responsible for returning the one true APSA_USER_IMPORT
 * instance to functions everywhere.
 *
 * @since       1.0.0
 * @return      APSA_USER_IMPORT
 */
function apsa_taskbot_user_sync() {
	return Taskbot_User_Sync::instance();
}
apsa_taskbot_user_sync();



function apsa_get_changed() {

	$user_data = APSA_SSO()->webservice->fetch_changed();

	if ( $user_data && isset( $user_data['DataSet']['diffgr:diffgram']['NewDataSet']['Table'] ) ) {
		$apsa_data = $user_data['DataSet']['diffgr:diffgram']['NewDataSet']['Table'];

		$_data = array();
		foreach ( $apsa_data as $v ) {
			if ( isset( $_data[ $v['ContactId'] ] ) ) {
				// found duplicate
				continue;
			}
			  // remember unique item
			  $_data[ $v['ContactId'] ] = $v;
		}

		$data = array_values( $_data );

		return $data;
	}

	return null;

}
// add_action( 'init', 'apsa_get_changed' );
function apsa_test_add() {

	if ( ! isset( $_GET['taskadd'] ) ) {
		return;
	}

	$users = get_users();
	$users = array_slice( $users, 0, 10 );

	$array = array();

	foreach ( $users as $user ) {
		$array[] = $user->ID;
	}

	taskbot_add_items( array(
		'task' => 'apsa_user_data',
		'items' => $array,
	) );

}
add_action( 'init', 'apsa_test_add' );
