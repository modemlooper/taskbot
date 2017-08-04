<?php

/**
 * Taskbot_Group_Member_Sync.
 *
 * @package         Taskbot_Group_Member_Sync
 * @author          AplhaWeb
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
		exit;
}


if ( ! class_exists( 'Taskbot_Group_Member_Sync' ) ) {

	class Taskbot_Group_Member_Sync {

		use WP_Sync_Logger;

		/**
		 * @var         Taskbot_Group_Member_Sync $instance
		 * @since       1.0.0
		 */
		private static $instance;

		public $task = 'apsa_group_member_sync';

		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      object self::$instance
		 */
		public static function instance() {
			if ( ! self::$instance ) {
				self::$instance = new Taskbot_Group_Member_Sync();
				self::$instance->hooks();
			}

			return self::$instance;
		}

		public function hooks() {
			add_action( 'taskbot_init', array( $this, 'register_task' ) );
			add_action( 'taskbot_add_' . $this->task, array( $this, 'add_items' ) );
			add_action( 'taskbot_run_' . $this->task, array( $this, 'process_item' ) );
			add_action( 'taskbot_complete_' . $this->task, array( $this, 'complete' ) );
		}

		public function register_task() {

			$tb = taskbot_new_task( array(
				'id' => $this->task,
				'title' => 'Group Member Sync',
				'description' => 'Syncs groups members from NOAH',
				'data' => array( 'item' => 1 ),
				'fields' => array(
					array(
						'id' => 'title',
						'name' => 'Instructions',
						'desc' => '<p>Selected groups will sync group members from NOAH. Choose a date and time for this task to run, and a reoccurence if you wish to have the task run more than once.</p>
						<p>* The date and time must be more than 10 min in the future.</p>',
						'type' => 'title',
					),
					array(
						'id' => 'group_ids',
						'name' => 'Select Groups',
						'type' => 'multicheck',
						'options' => taskbot_get_groups(),
					),
				),
			) );

		}

		public function add_items( $task ) {

			if ( isset( $task['fields']['group_ids'] ) && ! empty( $task['fields']['group_ids'] ) ) {

				$group_keys = array();

				foreach ( $task['fields']['group_ids'] as $group_id ) {

					$group_meta = groups_get_groupmeta( $group_id, 'apsa_group_identifier' );

					if ( ! empty( $group_meta ) ) {
						$group_keys[ $group_id ] = $group_meta;
					}
				}

				foreach ( $group_keys as $group_id => $value ) {

					$group_type = bp_groups_get_group_type( $group_id );
					$group_identifier = '';
					$group_identifier_type = '';

					switch ( $group_type ) {
						case 'section':
							$group_identifier = 'SEC0';
							$group_identifier_type = 'Sections';
						break;
						case 'committee':
							$group_identifier = '';
							$group_identifier_type = 'Committees';
						break;
						case 'role':
							$group_identifier = 'SEC0';
							$group_identifier_type = 'Roles';
						break;
						case 'deptchair':
							$group_identifier = '';
							$group_identifier_type = 'DeptChairs';
						break;
					}

					if ( empty( $group_identifier_type ) ) {
						continue;
					}

					$query = new BP_Group_Member_Query(array(
						'group_id'   => $group_id,
					));

					$group_member_keys = array();

					if ( ! empty( $query->results ) ) {

						foreach ( $query->results as $member ) {

							$member_key = get_user_meta( $member->ID, 'apsa_member_id', true );

							if ( $member_key ) {
								$group_member_keys[] = $member_key;
							}
						}
					}

					$changed_users = apsa_fetch_group_members( $group_identifier . $value, $group_identifier_type );

					tb_error_log( $changed_users );

					if ( isset( $changed_users[ $group_identifier_type ] ) && ! empty( $changed_users[ $group_identifier_type ] ) ) {

						//$changed_users = array_slice( $changed_users, 0, 10 );

						$items = array();

						foreach ( $changed_users[ $group_identifier_type ] as $user ) {
							if ( isset( $user['MemberKey'] ) && ! empty( $user['MemberKey'] ) ) {
								$items[] = $user['MemberKey'];
							}
						}

						tb_error_log( $items );

						$remove_items = array();

						if ( ! empty( $group_member_keys ) ) {
							$remove_items = array_diff( $group_member_keys,  $items );
						}

						//tb_error_log( $group_id );
						//tb_error_log( $remove_items );
						//tb_error_log( $items );


						if ( ! empty( $remove_items ) ) {

							// taskbot_add_items( array(
							// 	'task' => $task,
							// 	'items' => $remove_items,
							// 	'data' => array(
							// 		'section_id' => $group_identifier . $value,
							// 		'group_id' => $group_id,
							// 		'remove' => true,
							// 	),
							// ) );

						}

						// taskbot_add_items( array(
						// 	'task' => $task,
						// 	'items' => $items,
						// 	'data' => array(
						// 		'section_id' => $group_identifier . $value,
						// 		'group_id' => $group_id,
						// 		'remove' => false,
						// 	),
						// ) );

					}
				}
			}
		}

		public function process_item( $data ) {

			//time_nanosleep( 0, 300000000 );

			$remove = $data['data']['remove'];

			$user = get_user_by( 'login', $data['item'] );

			if ( empty( $user ) ) {
				$user_id = $this->create_user( $data['item'] );
			} else {
				$user_id = isset( $user->ID ) ? $user->ID : 0;
			}

			if ( $remove ) {
				tb_error_log( 'remove ' . $data['data']['group_id'] . ' ' . $data['item'] . ' ' . $user_id );
				$member = new BP_Groups_Member( $user_id, $data['data']['group_id'] );
				$member->remove();
				//groups_remove_member( $user_id, $data['data']['group_id'] );
			} else {
				tb_error_log( 'sync ' . $data['data']['group_id'] . ' ' . $data['item'] . ' ' . $user_id );
				$this->group_sync( $user_id, $data['data']['group_id'] );
			}

			//tb_error_log( $data['item'] );
			// tb_error_log( $data['data']['group_id'] );
			// $remove = $data['data']['remove'] ? 'remove' : 'add';
			// tb_error_log( $remove );

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
 * The main function responsible for returning the one true Taskbot_Group_Member_Sync
 * instance to functions everywhere.
 *
 * @since       1.0.0
 * @return      Taskbot_Group_Member_Sync
 */
function apsa_taskbot_group_member_sync() {
	return Taskbot_Group_Member_Sync::instance();
}
apsa_taskbot_group_member_sync();


function apsa_fetch_group_members( $group_key = '', $group_type = '' ) {

	$user_data = APSA_SSO()->webservice->fetch_group_members( trim( $group_key ), trim( $group_type ) );

	if ( $user_data && isset( $user_data['DataSet']['diffgr:diffgram']['NewDataSet'] ) ) {
		$apsa_data = $user_data['DataSet']['diffgr:diffgram']['NewDataSet'];

		return $apsa_data;
	}

	return null;
}
