<?php

/**
 * Adds bulk actions to user list table
 *
 * @param  array $bulk_actions
 * @return array
 */
function bp_user_import_bulk_actions( $bulk_actions ) {
	$bulk_actions['sync'] = __( 'Sync Data', 'apsa-sync' );
	$bulk_actions['change_username'] = __( 'MemberKey to Username', 'apsa-sync' );
	return $bulk_actions;
}
add_filter( 'bulk_actions-users', 'bp_user_import_bulk_actions' );

/**
 * Handler for custom bulk actions
 *
 * @param  string $redirect_to
 * @param  string $doaction
 * @param  array  $user_ids
 * @return string
 */
function bp_user_import_bulk_action_handler( $redirect_to, $doaction, $user_ids ) {

	global $wpdb;

	if ( 'sync' === $doaction ) {
		apsa_run_user_data_sync( $user_ids );
		$redirect_to = add_query_arg( 'bulk_sync_users', count( $user_ids ), $redirect_to );
	}

	if ( 'change_username' === $doaction ) {
		apsa_run_change_username( $user_ids );
		$redirect_to = add_query_arg( 'bulk_sync_id', count( $user_ids ), $redirect_to );

	}

	return $redirect_to;
}
add_filter( 'handle_bulk_actions-users', 'bp_user_import_bulk_action_handler', 10, 3 );

/**
 * Gets member id from api data and saves as user meta
 *
 * @param  array $user_ids
 * @return void
 */
function bp_sync_id( $user_ids = array() ) {

	foreach ( $user_ids as $user_id ) {
		$data = get_user_meta( $user_id, 'apsa_data' );

		if ( $data && isset( $data[0]['Member']['MemberKey'] ) ) {
			update_user_meta( $user_id, 'apsa_member_id', $data[0]['Member']['MemberKey'] );
		}
	}
}

/**
 * Admin notice for synced users
 *
 * @return void
 */
function bp_user_sync_admin_notice() {

	if ( ! empty( $_REQUEST['bulk_sync_users'] ) ) {

		$activated_count = intval( $_REQUEST['bulk_sync_users'] );
		printf( '<div id="message" class="updated fade"><p>' . _n( '%s user(s) synced.', '%s user(s) synced.', $activated_count, 'bulk_sync_users' ) . '</p></div>', $activated_count );
	}
}
add_action( 'admin_notices', 'bp_user_sync_admin_notice' );

/**
 * Javascript to toggle the bottom select inputs
 *
 * @return void
 */
function bp_group_import_filter_javascript() {
	?>
	<script type="text/javascript">
	    var el = jQuery("[name='user_filter']");
	    el.change(function() {
	        el.val(jQuery(this).val());
	    });
	</script>
	<?php
}
add_action( 'in_admin_footer', 'bp_group_import_filter_javascript' );

/**
 * syncs user data from api. Saves data to meta, adds/removes user from groups and updates xprofile fields.
 *
 * @param  array $user_ids
 * @return void
 */
function apsa_run_user_data_sync( $user_ids = array() ) {

	if ( empty( $user_ids ) ) {
		return array(
			'response' => 'error',
			'data' => '',
		);
	}

	foreach ( $user_ids as $id ) {

		$member_id = get_user_meta( $id, 'apsa_member_id', true );

		if ( empty( $member_id ) ) {
			return array(
				'response' => 'error',
				'data' => '',
			);
		}

		$user_data = APSA_SSO()->webservice->fetch( $member_id );

		if ( $user_data && isset( $user_data['DataSet']['diffgr:diffgram']['NewDataSet'] ) ) {
			$apsa_data = $user_data['DataSet']['diffgr:diffgram']['NewDataSet'];
			update_user_meta( $id, 'apsa_data', $apsa_data );
			apsa_run_user_group_sync( $id, $apsa_data );

			if ( function_exists( 'apsa_sso_set_xprofile_data' ) ) {
				apsa_sso_set_xprofile_data( $id, $apsa_data );
			}

			update_user_meta( $id, 'apsa_last_synced', date( 'Y-m-d' ) );

			$userlink = bp_core_get_user_domain( $id );

			return array(
				'response' => 'synced',
				'data' => $userlink,
			);
		}
	}

}

/**
 * Changes username to member id from apsa_member_id meta
 *
 * @param  array $user_ids
 * @return void
 */
function apsa_run_change_username( $user_ids = array() ) {
	global $wpdb;

	if ( empty( $user_ids ) ) {
		return;
	}

	foreach ( $user_ids as $id ) {

		$member_id = get_user_meta( $id, 'apsa_member_id', true );

		if ( $member_id ) {
			wp_update_user( array(
				'ID'			=> $id,
				'user_login'	=> $member_id,
				'user_nicename' => sanitize_title( $member_id ),
			) );

			$wpdb->update( $wpdb->users, array( 'user_login' => $member_id ), array( 'ID' => $id ), array( '%s' ), array( '%d' ) );

			clean_user_cache( $id );
			wp_cache_delete( $id, 'users' );
			wp_cache_delete( 'bp_core_userdata_' . $id, 'bp' );
			wp_cache_delete( 'bp_user_username_' . $id, 'bp' );
			wp_cache_delete( 'bp_user_domain_' . $id, 'bp' );
		}
	}

}

/**
 * API import data sync
 *
 * @param  integer $member_id
 * @return array
 */
function apsa_import_user_data_sync( $member_id ) {

	if ( empty( $member_id ) ) {
		return array(
			'response' => 'error',
			'data' => '',
		);
	}

	$user_data = APSA_SSO()->webservice->fetch( $member_id );

	tb_error_log( $user_data );

	if ( $user_data && isset( $user_data['DataSet']['diffgr:diffgram']['NewDataSet'] ) ) {
		$apsa_data = $user_data['DataSet']['diffgr:diffgram']['NewDataSet'];

		$user = get_user_by( 'login', $member_id );

		if ( ! is_wp_error( $user ) ) {

			update_user_meta( $user->ID, 'apsa_member_id', $member_id );
			update_user_meta( $user->ID, 'apsa_data', $apsa_data );

			apsa_run_user_group_sync( $user->ID, $apsa_data );

			if ( function_exists( 'apsa_sso_set_xprofile_data' ) ) {
				apsa_sso_set_xprofile_data( $user->ID, $apsa_data );
			}

			if ( function_exists( 'apsa_process_memberexpir_logic' ) ) {
				apsa_process_memberexpir_logic( $user->ID, $apsa_data );
			}

			update_user_meta( $user->ID, 'apsa_last_synced', date( 'Y-m-d' ) );

			$userlink = bp_core_get_user_domain( $user->ID );

			return array(
				'response' => 'synced',
				'data' => $userlink,
			);

		}
	}

}

/**
 * Updates a users groups. joins or removes based on current membership status.
 *
 * @param  integer $user_id
 * @param  array   $apsa_data
 * @return void
 */
function apsa_run_user_group_sync( $user_id = 0, $apsa_data = array() ) {

	$data = $apsa_data;

	if ( ! empty( $data ) ) {

		$types = array(
			'Sections' => 'SectionCode',
			'Committees' => 'CommitteeCode',
		);

		$skip_group_ids = get_user_meta( $user_id, 'skip_group_sync', true );

		foreach ( $types as $type => $value ) {

			$typevalue = $value;

			if ( $data && isset( $data[ $type ] ) ) {

				if ( ! isset( $data[ $type ][0] ) ) {
					$sections = array();

					foreach ( $data[ $type ] as $key => $value ) {
						$sections[0][ $key ] = $value;
					}

					$sections = $sections;

				} else {
					$sections = $data[ $type ];
				}

				$current_group_ids = array();

				foreach ( $sections as $section ) {

					$section_code = 'Sections' === $type ? trim( substr( $section[ $typevalue ], 4 ) )  : $section[ $typevalue ];
					$end_date = isset( $section['EndDate'] ) ? $section['EndDate'] : '0000-00-0T00:00:00-00:00';
					$is_member_current = false;

					$date_parts = explode( 'T', $end_date );

					$now  = time();
					$date = isset( $date_parts[0] ) ? new DateTime( $date_parts[0] ) : '0000-00-0' ;
					$expire = $date->getTimestamp();

					if ( $expire > $now ) {
						$is_member_current = true;
					}

					$args['meta_query'] = array(
						array(
							'key'     => 'apsa_group_identifier',
							'value'   => $section_code,
							'compare' => '=',
						),
					);

					$args['show_hidden'] = true;
					$args['per_page'] = -1;

					$group = groups_get_groups( $args );

					if ( isset( $group['groups'] ) && ! empty( $group['groups'] ) ) {

						if ( ! $is_member_current || groups_is_user_banned( $user_id, $group['groups'][0]->id ) ) {
							groups_remove_member( $user_id, $group['groups'][0]->id );
						} else {
							$current_group_ids[] = $group['groups'][0]->id;
							groups_join_group( $group['groups'][0]->id, $user_id );

							// subscribe user to group email digest.
							if ( function_exists( 'ass_group_subscription' ) ) {
								$default_gsub = groups_get_groupmeta( $group['groups'][0]->id, 'ass_default_subscription' );
								ass_group_subscription( $default_gsub, $user_id, $group['groups'][0]->id );
							}

							// if ( 'ADMIN' === $section['Role'] ) {
							// 	groups_promote_member( $user_id, $group['groups'][0]->id, 'admin' );
							// }
						}
					}
				}

				apsa_remove_from_not_current_groups( $current_group_ids, $user_id );
			}
		}
	}
}


/**
 * Remove user from any groups they do not have a current end date.
 *
 * @param  array   $current_group_ids
 * @param  integer $user_id
 * @return void
 */
function apsa_remove_from_not_current_groups( $current_group_ids = array(), $user_id = 0 ) {

	if ( ! empty( $current_group_ids ) && 0 !== $user_id ) {

		$all_members_group_id = BP_Groups_Group::group_exists( 'all-members' );
		$current_group_ids[] = $all_members_group_id;

		$skip_group_ids = get_user_meta( $user_id, 'skip_group_sync', true );

		$groups = groups_get_groups(
			array(
				'exclude' => $current_group_ids,
				'show_hidden' => true,
				'per_page' => -1,
			)
		);

		if ( isset( $groups['groups'] ) && ! empty( $groups['groups'] ) ) {

			foreach ( $groups['groups'] as $group ) {

				if ( groups_is_user_member( $user_id, $group->id ) ) {
					if ( ! in_array( $group->id, $skip_group_ids, true ) ) {

					} else {
						$member = new BP_Groups_Member( $user_id, $group->id );
						$member->remove();
					}
				}
			}
		}
	}
}

/**
 * Checks if there is a member with login as memberkey returns user data or error
 *
 * @param  integer $member_id [description]
 * @return array
 */
function apsa_user_import_fetch( $member_id = 0 ) {

	$user = get_user_by( 'login', $member_id );

	if ( $user ) {

		$userlink = bp_core_get_user_domain( $user->ID );

		return array(
			'response' => 'exists',
			'data' => '<div id="message" class="updated error notice-error"><p>User with this ID already exists.    <a target="_blank" href="' . $userlink . '">view profile</a></p></div>',
		);
	}

	$user_data = APSA_SSO()->webservice->fetch( $member_id );

	if ( $user_data && isset( $user_data['DataSet']['diffgr:diffgram']['NewDataSet'] ) ) {
		$apsa_data = $user_data['DataSet']['diffgr:diffgram']['NewDataSet'];

		return array(
			'response' => 'new',
			'data' => $apsa_data,
		);
	}

	return array(
		'response' => 'error',
		'data' => '',
	);
}

/**
 * Create account from import tool
 *
 * @param  integer $member_id
 * @return array
 */
function apsa_user_import_create_account( $member_id = 0 ) {

	$user = get_user_by( 'login', $member_id );

	if ( $user ) {
		return array(
			'response' => 'error',
			'data' => '<div id="message" class="updated notice notice-message message-created"><p>User Exists.</a></p></div>',
		);
	}

	$user_data = APSA_SSO()->webservice->fetch( $member_id );

	$apsa_data = $user_data['DataSet']['diffgr:diffgram']['NewDataSet'];
	$exp_time = isset( $apsa_data['Member']['MembExpire'] ) ? strtotime( $apsa_data['Member']['MembExpire'] ) : null;

	$now      = time();
	$exp_date = $exp_time;
	$username = isset( $apsa_data['Member']['Username'] ) ? $apsa_data['Member']['Username'] : null;
	$force = isset( $_GET['force'] ) ? $_GET['force'] : false;

	if ( '1' !== $force || ! $user_data ) {

		if ( null === $exp_date || $exp_date < $now || null === $username ) {
			// error_log( 'error: ' . $member_id );
			return array(
				'response' => 'error',
				'data' => '<div id="message" class="updated error notice-error message-created"><p>Error Creating user. Membership Expired or Member Key does not exists.</p></div>',
			);
		}
	} else {

		$password  = wp_generate_password();

		$user_args = array(
			'user_login'      => $member_id,
			'user_pass'       => $password,
			'user_email'      => isset( $apsa_data['Member']['Email'] ) ? $apsa_data['Member']['Email'] : '',
			'first_name'      => isset( $apsa_data['Member']['FirstName'] ) ? $apsa_data['Member']['FirstName'] : '',
			'last_name'       => isset( $apsa_data['Member']['LastName'] ) ? $apsa_data['Member']['LastName'] : '',
			'user_registered' => date( 'Y-m-d H:i:s' ),
			'role'            => get_option( 'default_role' ),
			'user_url'        => isset( $apsa_data['Member']['WebsiteUrl'] ) ? $apsa_data['Member']['WebsiteUrl'] : '',
		);

		// Insert new user.
		$user_id = wp_insert_user( $user_args );

		// Validate inserted user.
		if ( is_wp_error( $user_id ) ) {
			$error_string = $user_id->get_error_message();
			return array(
			  'response' => 'error',
			  'data' => '<div id="message" class="updated error notice-error message-created"><p>Error Creating user.  ' . $error_string . '.</p></div>',
			);
		}

		// Set a user meta key for the APSA member ID.
		update_user_meta( $user_id, 'apsa_member_id', $member_id );

		// Save member data, this is used for profile info.
		update_user_meta( $user_id, 'apsa_data', $apsa_data );

		// Set last_activity so the show in BP members list.
		bp_update_user_last_activity( $user_id, date( 'Y-m-d H:i:s' ) );

		// Set a user meta key to remind us this user has never logged in.
		update_user_meta( $user_id, 'apsa_never_logged_in', 'true' );

		// Setup xProfile fields.
		if ( function_exists( 'xprofile_set_field_data' ) ) {
			apsa_sso_set_xprofile_data( $user_id, $apsa_data );
		}

		// Setup set user groups.
		if ( function_exists( 'apsa_run_user_group_sync' ) ) {
			apsa_run_user_group_sync( $user_id, $apsa_data );
		}

		$userlink = bp_core_get_user_domain( $user_id );

		return array(
			'response' => 'new',
			'data' => '<div id="message" class="updated notice notice-message message-created"><p>User created.   <a target="_blank" href="' . $userlink . '">view profile</a></p></div>',
		);

	}

}

/**
 * Hooked to login, runs user data sync.
 *
 * @param  integer $user_id
 * @param  string  $user_login
 * @param  string  $user_pass
 * @return void
 */
function apsa_sso_log_user_in_sync( $user_id, $user_login, $user_pass ) {

	$user = get_user_by( 'ID', $user_id );

	if ( $user ) {
		apsa_import_user_data_sync( $user->data->user_login );
	}
}
add_action( 'apsa_sso_before_log_user_in', 'apsa_sso_log_user_in_sync', 15, 3 );

/**
 * Block login and redirect with notice that user account has expired
 *
 * @param  integer $user_id
 * @param  string  $user_login
 * @param  string  $user_pass
 * @return void
 */
function apsa_login_redirect_if_deactivated( $user_id, $user_login, $user_pass ) {

	$user_info = get_userdata( $user_id );

	if ( '2' === $user_info->data->user_status ) {
		apsa_sso_set_error( 'account_expired', __( 'The account associated with this login is expired.', 'apsa-sync' ) );
		wp_redirect( get_site_url() . '/login/' );
		exit();

	}

}
add_action( 'apsa_sso_before_log_user_in', 'apsa_login_redirect_if_deactivated', 999, 3 );

/**
 * Pre login member expiration logic. Deactivates/Activates account based on section committee status.
 *
 * @param  integer $user_id
 * @param  array   $apsa_data
 * @return void
 */
function apsa_process_memberexpir_logic( $user_id = 0, $apsa_data ) {

	$skip_group_ids = get_user_meta( $user_id, 'skip_group_sync', TRUE );

	if ( ! empty( $skip_group_ids ) ) {
		apsa_activate_user( $user_id );
		return;
	}

	$sections = isset( $apsa_data['Sections'] ) ? apsa_check_if_current( $apsa_data['Sections'] ) : false;
	$commitees = isset( $apsa_data['Committees'] ) ? apsa_check_if_current( $apsa_data['Committees'] ) : false;

	$end_date = isset( $apsa_data['Member']['MembExpire'] ) ? $apsa_data['Member']['MembExpire'] : '0000-00-0T00:00:00-00:00';
	$date_parts = explode( 'T', $end_date );

	$now  = time();
	$date = isset( $date_parts[0] ) ? new DateTime( $date_parts[0] ) : '0000-00-0' ;
	$expire = $date->getTimestamp();

	$is_member_current = false;

	$all_members_group_id = BP_Groups_Group::group_exists( 'all-members' );

	if ( $expire > $now ) {
		$is_member_current = true;
	}

	if ( $is_member_current ) {
		apsa_activate_user( $user_id );
	}

	// If MembExpire is in the past remove them from the "All APSA Members" group or add them if not a member.
	if ( ! $is_member_current ) {
		$member = new BP_Groups_Member( $user_id, $all_members_group_id );
		$member->remove();
	} else {
		if ( ! groups_is_user_member( $user_id, $all_members_group_id ) ) {
			groups_join_group( $all_members_group_id, $user_id );
		}
	}

	// If MembExpire is in the past and they ARE in ANY committees/sections, check that members committee/section end date,
	// if that end date IS in the future for ANY of their committees/sections SKIP/ACTIVATE their account in WordPress.
	// (Filter them out of the WordPress members directory, this should be addressed under support/phase2 because of the complexity).
	if ( ! $is_member_current && $sections || $commitees ) {
		apsa_activate_user( $user_id );
	}

	// If MembExpire is in the past and they ARE in ANY committees/sections, check that members committee/section end date,
	// if that end date IS in the past for ALL of their committees/sections DEACTIVATE their account in WordPress.
	if ( ! $is_member_current && ! $sections && ! $commitees ) {
		apsa_deactivate_user( $user_id );
	}

}

/**
 * Helper functio to determn if any of the users sections or commitees end date are current
 *
 * @param  array $data
 * @return boolean
 */
function apsa_check_if_current( $data = array() ) {

	if ( empty( $data ) ) {
		return false;
	}

	$is_member_current = false;

	if ( ! isset( $data[0] ) ) {
		$sections = array();

		foreach ( $data as $key => $value ) {
			$sections[0][ $key ] = $value;
		}

		$sections = $sections;

	} else {
		$sections = $data;
	}

	foreach ( $sections as $section ) {

		$end_date = isset( $section['EndDate'] ) ? $section['EndDate'] : '0000-00-0T00:00:00-00:00';
		$date_parts = explode( 'T', $end_date );

		$now  = time();
		$date = isset( $date_parts[0] ) ? new DateTime( $date_parts[0] ) : '0000-00-0' ;
		$expire = $date->getTimestamp();

		if ( $expire > $now ) {
			$is_member_current = true;
		}
	}

	return $is_member_current;

}

/**
 * Helper function to set user status to 2 to prevent login
 *
 * @TODO Add better deactivation. This is not a true deactivation as the user is still visable on front end.
 * @param  integer $user_id
 * @return void
 */
function apsa_deactivate_user( $user_id = 0 ) {
	global $wpdb;

	// This is a partial fix, there isnt easy way outside of markig a user as spam to hide them after they have created content.
	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->users} SET user_status = 2 WHERE ID = %d", $user_id ) );
	delete_user_meta( $user_id, 'last_activity' );

}

/**
 * Helper function to set user status to 0 to allow login
 *
 * @param  integer $user_id
 * @return void
 */
function apsa_activate_user( $user_id = 0 ) {
	global $wpdb;

	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->users} SET user_status = 0 WHERE ID = %d", $user_id ) );
	bp_update_user_last_activity( $user_id, date( 'Y-m-d H:i:s' ) );

}


// 1. If MembExpire is in the past remove them from the "All APSA Members" group
//
// 2. If MembExpire is in the past and they ARE NOT in ANY committees/sections DEACTIVATE their account in WordPress.
//
// 3. If MembExpire is in the past and they ARE in ANY committees/sections, check that members committee/section end date, if that end date IS in the past for ALL of their committees/sections DEACTIVATE their account in WordPress.
//
// 4. If MembExpire is in the past and they ARE in ANY committees/sections, check that members committee/section end date, if that end date IS in the future for ANY of their committees/sections SKIP/ACTIVATE their account in WordPress. (Filter them out of the WordPress members directory, this should be addressed under support/phase2 because of the complexity).
//
// *Note: If members committee/section end date is in the past they will be removed from that committee/section.
//
// *Note: All expiration/end dates need to be stored locally so subscript on nightly sync can remove members that have expired...
