<?php

trait WP_Sync_Logger {


	/**
	 * Sync User
	 *
	 * @param string  $member_key
	 * @param integer $user_id
	 *
	 * @return void
	 */
	protected function sync_user( $member_key = 0, $user_id = 0 ) {

		$apsa_data = $this->fetch_user( $member_key );

		if ( $apsa_data ) {
			$this->sync_user_data( $user_id, $apsa_data );
		}

	}

	/**
	 * Create User
	 *
	 * @param string $member_key
	 *
	 * @return string
	 */
	protected function create_user( $member_key = 0 ) {

		$apsa_data = $this->fetch_user( $member_key );

		if ( $apsa_data ) {
			if ( $user_id = $this->insert_user( $apsa_data ) ) {
				$this->sync_user_data( $user_id, $apsa_data );

				return $user_id;
			}
		}
	}

	/**
	 * Fetch User data
	 *
	 * @param string $member_key
	 *
	 * @return string
	 */
	protected function fetch_user( $member_key = 0 ) {

		$apsa_data = APSA_SSO()->webservice->fetch( $member_key );

		if ( $apsa_data && isset( $apsa_data['DataSet']['diffgr:diffgram']['NewDataSet'] ) ) {
			$apsa_data = $apsa_data['DataSet']['diffgr:diffgram']['NewDataSet'];
			return $apsa_data;
		}

		return false;
	}

	/**
	 * Insert New User
	 *
	 * @param string $user_data
	 *
	 * @return string
	 */
	protected function insert_user( $apsa_data = array() ) {

		if ( ! empty( $apsa_data ) ) {

			if ( ! isset( $apsa_data['Member']['FirstName'] ) ) {
				return;
			}

			$member_key = isset( $apsa_data['Member']['MemberKey'] ) ? $apsa_data['Member']['MemberKey'] : 0;
			$password  = wp_generate_password();

			$user_args = array(
				'user_login'      => $member_key,
				'user_pass'       => $password,
				'user_email'      => isset( $apsa_data['Member']['Email'] ) ? $apsa_data['Member']['Email'] : ' ',
				'first_name'      => isset( $apsa_data['Member']['FirstName'] ) ? $apsa_data['Member']['FirstName'] : ' ',
				'last_name'       => isset( $apsa_data['Member']['LastName'] ) ? $apsa_data['Member']['LastName'] : ' ',
				'user_registered' => date( 'Y-m-d H:i:s' ),
				'role'            => get_option( 'default_role' ),
				'user_url'        => isset( $apsa_data['Member']['WebsiteUrl'] ) ? $apsa_data['Member']['WebsiteUrl'] : ' ',
			);

			// Insert new user.
			$user_id = wp_insert_user( $user_args );

			// Validate inserted user.
			if ( ! is_wp_error( $user_id ) ) {
				apsa_activate_user( $user_id );
				return $user_id;
			}
		}

		return false;

	}

	/**
	 * Sync User Data
	 *
	 * @param string $user_data
	 *
	 * @return string
	 */
	protected function sync_user_data( $user_id = 0, $apsa_data = array() ) {

		if ( $apsa_data && 0 !== $user_id ) {

			$member_key = isset( $apsa_data['Member']['MemberKey'] ) ? $apsa_data['Member']['MemberKey'] : 0;

			update_user_meta( $user_id, 'apsa_member_id', $member_key );
			update_user_meta( $user_id, 'apsa_data', $apsa_data );
			update_user_meta( $user_id, 'apsa_last_synced', date( 'Y-m-d' ) );

			if ( function_exists( 'apsa_run_user_group_sync' ) ) {
				//apsa_run_user_group_sync( $user_id, $apsa_data );
			}

			if ( function_exists( 'apsa_sso_set_xprofile_data' ) ) {
				apsa_sso_set_xprofile_data( $user_id, $apsa_data );
			}

			if ( function_exists( 'apsa_process_memberexpir_logic' ) ) {
				apsa_process_memberexpir_logic( $user_id, $apsa_data );
			}
		}

	}

	/**
	 * Callback method for sync user data task
	 *
	 * @param  integer $item
	 * @return void
	 */
	public function sync_data( $item = 0 ) {

		$user = get_user_by( 'login', $item );

		if ( $user ) {
			$this->sync_user( $item, $user->ID );
		} else {
			$this->create_user( $item );
		}
	}

	/**
	 * Add user to group if not banned and set default subscription
	 *
	 * @param  integer $user_id
	 * @param  integer $group_id
	 * @return void
	 */
	public function group_sync( $user_id = 0, $group_id = 0 ) {

		if ( 0 !== $user_id && 0 !== $group_id ) {

			if ( ! groups_is_user_member( $user_id, $group_id ) && ! groups_is_user_banned( $user_id, $group_id ) ) {
				groups_join_group( $group_id, $user_id );

				// subscribe user to group email digest.
				if ( function_exists( 'ass_group_subscription' ) ) {
					$default_gsub = groups_get_groupmeta( $group_id, 'ass_default_subscription' );
					ass_group_subscription( $default_gsub, $user_id, $group_id );
				}
			}
		}
	}

	/**
	 * Callback method for set email digest task
	 *
	 * @param  integer $group_id
	 * @return void
	 */
	public function email_digest( $group_id = 0 ) {
		global $wpdb;

		$bp = buddypress();
		$user_ids = $wpdb->get_col( $wpdb->prepare( "SELECT user_id FROM {$bp->groups->table_name_members} WHERE group_id = %d", $group_id ) );

		// subscribe user to group email digest.
		if ( ! empty( $user_ids ) && function_exists( 'ass_group_subscription' ) ) {
			$subs = array_fill_keys( $user_ids, 'dig' );
			groups_update_groupmeta( $group_id , 'ass_default_subscription', 'dig' );
			groups_update_groupmeta( $group_id , 'ass_subscribed_users', $subs );
		}
	}

	/**
	 * Set all members group to email digest
	 *
	 * @param  integer $user_id
	 * @return void
	 */
	public function all_members_email_digest( $user_id = 0 ) {
		$group_id = groups_get_id( 'all-members' );
		ass_group_subscription( 'dig', $user_id, $group_id );
	}

}
