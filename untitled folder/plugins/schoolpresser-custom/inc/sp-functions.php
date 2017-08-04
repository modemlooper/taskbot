<?php

define( 'BP_DEFAULT_COMPONENT', 'profile' );
add_filter( 'bp_activity_do_mentions', '__return_false' );
add_filter( 'bp_activity_can_favorite', '__return_false' );
add_filter( 'bp_activity_can_comment', '__return_false' );

// These fix outgoing mail issues. SEE https://3.basecamp.com/3236404/buckets/3222622/messages/539429138#__recording_539559787
define( 'WPE_GOVERNOR', false );
add_filter( 'bp_email_use_wp_mail', '__return_false' );

/**
 * Returns group meta field value
 *
 * @param  string  $field
 * @param  integer $group_id
 * @return string
 */
function apsa_get_group_details_field( $field = '', $group_id = 0 ) {
	global $bp, $wpdb;

	$group_id = 0 === $group_id ? $bp->groups->current_group->id : $group_id;

	$field_value = groups_get_groupmeta( $group_id, $field );

	return $field_value;

}

/**
 * Apsa_group_details_markup
 *
 * @return void
 */
function apsa_group_details_markup() {
	global $bp, $wpdb;

	$group_id = is_admin() && isset( $_GET['gid'] ) ? $_GET['gid'] : $bp->groups->current_group->id;

	$identifier = isset( $group_id ) ? groups_get_groupmeta( $group_id, 'apsa_group_identifier' ) : '';
	$url = isset( $group_id ) ? groups_get_groupmeta( $group_id, 'apsa_group_url' ) : '';
	?>

	<label for="group-identifier"><?php esc_attr_e( 'Group Identifier', 'buddypress' ); ?></label>
	<p class="group-input-descritpion"><?php _e( 'Code from NOAH', 'boss' ); ?></p>
	<input type="text" name="group-identifier" id="group-identifier" value="<?php echo esc_attr( $identifier ); ?>" />

	<label for="group-url"><?php esc_attr_e( 'Group Website', 'buddypress' ); ?></label>
	<input type="text" name="group-url" id="group-url" value="<?php echo esc_url( $url ); ?>" />

	<?php

}
add_action( 'groups_custom_group_fields_editable', 'apsa_group_details_markup' );

/**
 * Apsa_group_details_save metabox data.
 *
 * @param  integer $group_id
 * @return void
 */
function apsa_group_details_save( $group_id ) {
	global $bp, $wpdb;

	$fields = array(
		'url',
		'identifier',
	);

	foreach ( $fields as $field ) {
		$key = 'group-' . $field;
		if ( isset( $_POST[ $key ] ) ) {

			switch ( $field ) {
				case 'url':
					$value = esc_url_raw( $_POST[ $key ] );
				break;
				case 'identifier':
						$value = trim( $_POST[ $key ] )  ? sanitize_text_field( $_POST[ $key ] ) : false;
				break;
			}

			if ( $value ) {
				groups_update_groupmeta( $group_id, 'apsa_group_' . $field, $value );
			}
		}
	}

}
add_action( 'groups_group_details_edited', 'apsa_group_details_save' );
add_action( 'groups_created_group',  'apsa_group_details_save' );


/**
 * Apsa_group_details_meta_box function.
 *
 * @access public
 * @return void
 */
function apsa_group_details_meta_box() {

	add_meta_box(
	    'details_id',
		__( 'Group Details', 'schoolpresser-custom' ),
	    'apsa_group_details_markup',
	    get_current_screen()->id,
		'side'
	);
}
add_action( 'bp_groups_admin_meta_boxes', 'apsa_group_details_meta_box' );


/**
 * Apsa_group_save_metabox function.
 *
 * @access public
 * @return void
 */
function apsa_group_save_metabox() {
	global $bp, $wpdb;

	if ( isset( $_GET['page'] ) && 'bp-groups' === $_GET['page'] && isset( $_POST['save'] ) && 'Save Changes' === $_POST['save'] ) {

		$group_id = isset( $_GET['gid'] ) ? (int) $_GET['gid'] : 0;

		$fields = array(
			'url',
			'identifier',
		);

		foreach ( $fields as $field ) {
			$key = 'group-' . $field;
			if ( isset( $_POST[ $key ] ) ) {

				switch ( $field ) {
					case 'url':
						$value = esc_url_raw( $_POST[ $key ] );
					break;
					case 'identifier':
						$value = sanitize_text_field( $_POST[ $key ] );
					break;
				}

				groups_update_groupmeta( $group_id, 'apsa_group_' . $field, $value );
			}
		}
	}
}
add_action( 'bp_group_admin_edit_after', 'apsa_group_save_metabox', 5, 1 );

/**
 * Metabox css
 *
 * @return void
 */
function apsa_groups_admin_css() {

	if ( isset( $_GET['page'] ) && 'bp-groups' === $_GET['page'] ) {
		?>
		<style>
			#details_id.postbox label {
				width: 100%;
				display: block;
				padding: 10px 0 5px 0;
			}

			#details_id.postbox input {
				width: 100%;
				margin-bottom: 10px;
			}

		</style>
		<?php
	}
}
add_action( 'admin_head', 'apsa_groups_admin_css' );

/**
 * Display company name below users nam in members list
 *
 * @return void
 */
function apsa_display_company_name_after_username() {

	$member_id = bp_is_group() ? bp_get_group_member_id() : bp_get_member_user_id();

	$args = array(
	   'field'   => 'Company Name',
	   'user_id' => $member_id,
	   );
	$company_name = bp_get_profile_field_data( $args );

	if ( $company_name ) {
		echo '<div class="company-name">Organization: ' . esc_attr( $company_name ) . '</div>';
	}

}
add_action( 'bp_directory_after_member_name', 'apsa_display_company_name_after_username' );

/**
 * Custom drop down filter options for group directory.
 *
 * @return void
 */
function apsa_groups_custom_loop_filters() {
	if ( bp_is_active( 'groups' ) ) {

		echo '<option value="section_id">Section ID</option>';

		if ( bp_get_groups_group_type_base() !== bp_current_action() ) {

			$group_types = bp_groups_get_group_types();

			foreach ( $group_types as $type ) {
				echo '<option value="' . esc_attr( $type ) . '">' . esc_attr( ucfirst( $type ) ) . '</option>';
			}
		}
	}

}
add_action( 'bp_groups_directory_order_options',  'apsa_groups_custom_loop_filters' );

/**
 * Filter the loop query for group identifier meta
 *
 * @param  string $query_string
 * @param  string $object
 * @return string
 */
function apsa_groups_filter_loop_query_string( $query_string = '', $object = '' ) {

	if ( 'groups' !== $object ) {
		return $query_string;
	}

	$args = wp_parse_args( $query_string, array(
		'action'  => false,
		'type'    => false,
		'user_id' => false,
		'page'    => 1,
	) );

	$group_types = bp_groups_get_group_types();

	if ( in_array( $args['type'], $group_types, true ) ) {

		$args['group_type'] = $args['type'];

		$query_string = empty( $args ) ? $query_string : $args;
	}

	if ( 'section_id' === $args['type'] ) {

		$args['group_type'] = 'section';

		// Group meta query.
		$args['meta_query'] = array(
			array(
				'key'     => 'apsa_group_identifier',
				'value'   => 1,
				'type'    => 'numeric',
				'compare' => '>=',
			),
		);

		$query_string = empty( $args ) ? $query_string : $args;
	}

	return $query_string;

}
add_filter( 'bp_ajax_querystring', 'apsa_groups_filter_loop_query_string', 12, 2 );


/**
 * Filters group sql for section id filter. Resorts ASC group object by group identifier
 *
 * @param  string $paged_groups_sql
 * @param  array  $sql
 * @param  array  $r
 * @return string
 */

function apsa_paged_groups_sql_filter( $paged_groups_sql, $sql, $r ) {

	global $wpdb;

	if ( 'section_id' !== $r['type'] ) {
		return $paged_groups_sql;
	}

	$bp = buddypress();

	$term_id = $wpdb->get_results( "SELECT term_id FROM {$wpdb->terms} WHERE slug = 'section'" );

	$new_sql = "SELECT group_id FROM {$bp->groups->table_name_groupmeta} WHERE meta_key = 'apsa_group_identifier' AND meta_value REGEXP '[0-9]+' AND group_id IN ( SELECT object_id FROM wp_term_relationships WHERE wp_term_relationships.term_taxonomy_id IN ({$term_id[0]->term_id}) ) ORDER BY meta_value * 1 ASC {$sql['pagination']}";

	return $new_sql;
}
add_filter( 'bp_groups_get_paged_groups_sql', 'apsa_paged_groups_sql_filter', 10, 3 );

/**
 * Filters the forum title and adds a span link to post form
 *
 * @param string  $title
 * @param integer $forum_id
 */
function apsa_add_topic_post_button_above_forum( $title, $forum_id ) {

	if ( bp_is_group() && ! bbp_is_forum_closed() ) {
		$title = $title . '<span class="topic-post"><a href="#new-topic-0">Post a Topic</a></span>';
	}

	return $title;
}
add_filter( 'bbp_get_forum_title', 'apsa_add_topic_post_button_above_forum', 10, 2 );

/**
 * If a search term is only numbers then use meta query for indetifier.
 *
 * @param array $query
 */
function apsa_add_identifier_meta_to_group_query( $query ) {

	$search_query_arg = bp_core_get_component_search_query_arg( 'groups' );

	if ( isset( $_REQUEST[ $search_query_arg ] ) && ! empty( $_REQUEST[ $search_query_arg ] ) ) {

		$search_terms = trim( wp_unslash( $_REQUEST[ $search_query_arg ] ) );

		if ( ctype_digit( $search_terms ) ) {

			$query['meta_query'] = array(
				array(
					'key'     => 'apsa_group_identifier',
					'value'   => $search_terms,
					'type'    => 'numeric',
					'compare' => '=',
				),
			);

			unset( $query['search_terms'] );

		}
	}

	return $query;
}
add_filter( 'bp_after_has_groups_parse_args', 'apsa_add_identifier_meta_to_group_query' );

/**
 * If a search term is only numbers then use meta query for indetifier.
 *
 * @param array $query
 */
function apsa_add_identifier_meta_to_group_ajax_query( $query, $object = '' ) {

	if ( 'groups' !== $object ) {
		return $query;
	}

	if ( isset( $_REQUEST['search_terms'] ) && ! empty( $_REQUEST['search_terms'] ) ) {

		$search_terms = trim( wp_unslash( $_REQUEST['search_terms'] ) );

		$query = wp_parse_args( $query );

		if ( ctype_digit( $search_terms ) ) {

			$query['meta_query'] = array(
				array(
					'key'     => 'apsa_group_identifier',
					'value'   => $search_terms,
					'type'    => 'numeric',
					'compare' => '=',
				),
			);

			unset( $query['search_terms'] );

			$query = http_build_query( $query );

		}
	}

	return $query;
}
add_filter( 'bp_ajax_querystring', 'apsa_add_identifier_meta_to_group_ajax_query', 12, 2 );

/**
 * Remove join button if group type is section.
 *
 * @param  array  $button
 * @param  object $group
 * @return array
 */
function remove_group_join_button( $button, $group ) {

	$group_type = bp_get_group_type();

	// remove button from all groups - leaving commented out incase this ever needs to be changed back
	// if ( 'Public Section' === $group_type || 'Private Section' === $group_type || 'Hidden Section' === $group_type ) {
		return array();
	// }
	// return $button;
}
add_filter( 'bp_get_group_join_button', 'remove_group_join_button', 10, 2 );

/**
 * Remove group tabs
 *
 * @return void
 */
function apsa_remove_group_tabs() {

	bp_core_remove_subnav_item( 'profile', 'edit' );

	if ( ! bp_is_group() ) {
		return;
	}

	$slug = bp_get_current_group_slug();
	$group_type = bp_groups_get_group_type( buddypress()->groups->current_group->id );

	if ( 'section' === $group_type ) {
		bp_core_remove_subnav_item( $slug, 'request-membership' );
	}

}
add_action( 'bp_init', 'apsa_remove_group_tabs', 999 );

/**
 * [bpex_admin_bar_remove_this description]
 *
 * @return void
 */
function bp_admin_bar_remove_items() {
	global $wp_admin_bar;
	$wp_admin_bar->remove_node( 'my-account-xprofile-edit' );
}
add_action( 'wp_before_admin_bar_render','bp_admin_bar_remove_items' );


/**
 * Block activity types from being saved.
 *
 * @param  object $activity_object
 * @return void
 */
function apsa_activity_dont_save( $activity_object ) {
	$exclude = array(
	        'joined_group',
			'activity_update',
			'new_member',
			'created_group',
			'updated_profile',
			'new_avatar',
			'new_event',
			'friendship_accepted',
			'friendship_created',
			'new_blog_comment',
			'new_blog_post',
	    );
	// If the activity type is empty, it stops BuddyPress BP_Activity_Activity::save() function.
	if ( in_array( $activity_object->type, $exclude, true ) ) {
		$activity_object->type = false;
	}
}
add_action( 'bp_activity_before_save', 'apsa_activity_dont_save', 10, 1 );

/**
 * Filter items in activiy filter drop down
 *
 * @param  array  $filters
 * @param  string $context
 * @return array
 */
function apsa_filter_activity_show_filters( $filters, $context ) {

	$excludes = array(
	        'joined_group',
			'activity_update',
			'group_details_updated',
			'updated_profile',
			'joined_group',
			'new_member',
			'created_group',
			'friendship_accepted,friendship_created',
			'new_blog_comment',
			'new_blog_post',
	    );

	foreach ( $excludes as $exclude  ) {
		unset( $filters[ $exclude ] );
	}

	return $filters;
}
add_filter( 'bp_get_activity_show_filters_options', 'apsa_filter_activity_show_filters', 10, 2 );

/**
 * [bp_restrict_pages description]
 *
 * @return void
 */
function bp_restrict_pages() {

	if ( ! is_user_logged_in() && 'members' === bp_current_component() || ! is_user_logged_in() && bp_is_user() ) {
		bp_core_redirect( '/' );
	}

}
add_action( 'bp_init', 'bp_restrict_pages' );


/**
 * [bp_add_imported_data_to_profile description]
 */
function bp_add_imported_data_to_profile() {

	$data = get_user_meta( bp_displayed_user_id(), 'apsa_data', true );
	$field_group_slug = bp_get_the_profile_group_slug();

	if ( 'personal-info' !== $field_group_slug ) {
		return;
	}

	if ( $data && isset( $data['Interests'] ) ) {

		$interest_items = '';
		foreach ( $data['Interests'] as $interest ) {

			$interest_items .= $interest['Description'] . ', ';

		}
		?>
			<tr<?php bp_field_css_class(); ?>>
				<td class="label">Interests</td>
				<td class="data"><?php echo esc_attr( rtrim( $interest_items, ', ' ) ); ?></td>
			</tr>
		<?php
	}

	if ( isset( $data['Degrees'] ) ) {
		$degree_items = '';

		$degrees = isset( $data['Degrees'][0] ) ? $data['Degrees'] : array( $data['Degrees'] );

		foreach ( $degrees as $degree ) {

			$degree_items .= '<div class="data-item"><div>School Name: ' . $degree['SchoolName'] . '</div>';
			$degree_items .= '<div>City: ' . $degree['City'] . '</div>';
			$degree_items .= '<div>State: ' . $degree['StateCode'] . '</div>';
			$degree_items .= '<div>Degree: ' . $degree['Degree'] . '</div>';
			$degree_items .= '<div>Degree Date: ' . $degree['DegreeDate'] . '</div></div>';

		}
		?>
			<tr<?php bp_field_css_class(); ?>>
				<td class="label">Degrees</td>
				<td class="data"><?php echo $degree_items; ?></td>
			</tr>
		<?php
	}

	if ( isset( $data['JobHistory'] ) ) {
		$job_items = '';

		$jobs = isset( $data['JobHistory'][0] ) ? $data['JobHistory'] : array( $data['JobHistory'] );

		foreach ( $jobs as $job ) {

			$job_items .= '<div class="data-item">';
			$job_items .= '<div>Company: ' . $job['Company'] . '</div>';
			$job_items .= '<div>Position: ' . $job['Position'] . '</div>';
			$job_items .= '<div>Department: ' . $job['Department'] . '</div>';
			$job_items .= '<div>City: ' . $job['City'] . '</div>';
			$job_items .= '<div>State: ' . $job['State'] . '</div>';
			$date_parts = explode( 'T', $job['StartDate'] );
			$job_items .= '<div>Date: ' . date( 'F d Y', strtotime( $date_parts[0] ) ) . '</div>';
			$job_items .= '</div>';
		}
		?>
			<tr<?php bp_field_css_class(); ?>>
				<td class="label">Work History</td>
				<td class="data"><?php echo $job_items; ?></td>
			</tr>
		<?php
	}

}
add_action( 'bp_profile_after_field_items', 'bp_add_imported_data_to_profile' );

/**
 * Add extra columns to user list
 *
 * @param array $columns
 * @return array
 */
function apsa_add_member_id_column( $columns ) {
	$columns['member_id'] = 'Member ID';
	return $columns;
}
add_filter( 'manage_users_columns', 'apsa_add_member_id_column' );
add_filter( 'manage_users_sortable_columns', 'apsa_add_member_id_column' );

function apsa_add_groups_column( $columns ) {
	$columns['user_groups'] = 'Groups';
	return $columns;
}
add_filter( 'manage_users_columns', 'apsa_add_groups_column' );

/**
 * [bp_filter_users_by_member_id description]
 *
 * @param  array $query
 * @return object
 */
function bp_filter_users_by_member_id( $query ) {
	global $pagenow;

	if ( is_admin() && 'users.php' === $pagenow && 'Member ID' === $query->get( 'orderby' ) ) {
		$query->set( 'orderby', 'meta_value_num' );
		$query->set( 'meta_key', 'apsa_member_id' );
	}
}
add_filter( 'pre_get_users', 'bp_filter_users_by_member_id' );

/**
 * Sort by last name
 *
 * @param  object $bp_user_query
 * @return void
 */
function alphabetize_by_last_name( $bp_user_query ) {
	if ( 'alphabetical' === $bp_user_query->query_vars['type'] ) {
		$bp_user_query->uid_clauses['orderby'] = "ORDER BY substring_index(u.display_name, ' ', -1)";
	}
}
add_action( 'bp_pre_user_query', 'alphabetize_by_last_name' );

/**
 * Lists member id in user list column
 *
 * @param  string  $value
 * @param  string  $column_name
 * @param  integer $user_id
 * @return string
 */
function apsa_show_member_id_column_content( $value, $column_name, $user_id ) {

	if ( 'member_id' === $column_name ) {
		$user_meta = get_user_meta( $user_id, 'apsa_member_id', true );

		if ( $user_meta ) {
			$value = $user_meta;
		}
	}

	if ( 'user_groups' === $column_name ) {

		$groups = groups_get_groups( array( 'user_id' => $user_id ) );
		$group_url = trailingslashit( get_bloginfo( 'url' ) ) . BP_GROUPS_SLUG;
		$value = '';

		if ( isset( $groups['groups'] ) && ! empty( $groups['groups'] ) ) {

			foreach ( $groups['groups'] as $group ) {

				$value .= '<a href="' . $group_url . '/' . $group->slug . '">' . $group->name . '</a>, ';
			}
		}

		$value = substr( $value, 0, -2 );
	}

	return $value;
}
add_action( 'manage_users_custom_column',  'apsa_show_member_id_column_content', 10, 3 );

/**
 * Add group ID column
 *
 * @TODO needs to be sortable but BP doesnt have a filter. See https://buddypress.trac.wordpress.org/browser/trunk/src/bp-groups/classes/class-bp-groups-list-table.php#L419
 * @param array $columns
 * @return array
 */
function apsa_add_group_id_column( $columns ) {
	$columns['group_id'] = _x( 'Group ID', 'Groups ID column header',  'buddypress' );

	return $columns;
}
add_filter( 'bp_groups_list_table_get_columns', 'apsa_add_group_id_column', 999 );

/**
 * Group identifier meta for group id content
 *
 * @param  string $retval
 * @param  string $column_name
 * @param  array  $item
 * @return string
 */
function apsa_column_content_group_id( $retval = '', $column_name, $item ) {

	if ( 'group_id' !== $column_name ) {
		return $retval;
	}

	$group_id = groups_get_groupmeta( $item['id'], 'apsa_group_identifier' );

	echo esc_attr( $group_id );

}
add_filter( 'bp_groups_admin_get_group_custom_column', 'apsa_column_content_group_id', 10, 3 );

/**
 * Removes WHERE clause from sql stament when on all members group.
 * This is a hack to fix the sql stament being too large and breaking the members list on a group when there are over 1500 members.
 *
 * @TODO find a better way, eh?
 * @param  object $user_object
 * @return object
 */
function filter_user_query( $user_object ) {

	$group_id = BP_Groups_Group::group_exists( 'all-members' );

	if ( isset( $user_object->query_vars['group_id'] ) && $group_id === $user_object->query_vars['group_id'] ) {

		$user_object->query_vars['type'] = 'alphabetical';
		$user_object->uid_clauses['where'] = '';
	}
	return $user_object;

}
add_filter( 'bp_pre_user_query', 'filter_user_query' );

/**
 * Ensure that the full content of forum posts is shown in email notifications.
 */
function apsa_full_forum_content_in_notifications( $content, $activity ) {
	if ( 'bbp_topic_create' !== $activity->type && 'bbp_reply_create' !== $activity->type ) {
		return $content;
	}

	$post = get_post( $activity->secondary_item_id );

	return wpautop( $post->post_content );
}
add_filter( 'bp_ass_activity_notification_content', 'apsa_full_forum_content_in_notifications', 10, 2 );
add_filter( 'ass_digest_content', 'apsa_full_forum_content_in_notifications', 10, 2 );

/**
 * Add counts to forum and docs tabs
 *
 * @return void
 */
function apsa_edit_tabs() {

	if ( ! bp_is_group() ) {
		 return;
	}

	buddypress()->groups->nav->edit_nav( array( 'name' => sprintf( 'Documents <span>%d</span>', apsa_get_group_doc_count( bp_current_item() ) ) ), 'documents', bp_current_item() );
	buddypress()->groups->nav->edit_nav( array( 'name' => sprintf( 'Forum <span>%d</span>', apsa_get_group_topic_count( bp_current_item() ) ) ), 'forum', bp_current_item() );

}
add_action( 'bp_actions', 'apsa_edit_tabs' );

/**
 * If on the homepage (connect) show the logged in users group activity
 *
 * @param  array $query
 * @return array
 */
function apsa_filter_connect_activity_query( $query ) {

	if ( ! is_front_page() ) {
		return $query;
	}

	$query['scope'] = 'connect';
	$query['show_hidden'] = true;

	return $query;
}
add_filter( 'bp_after_has_activities_parse_args', 'apsa_filter_connect_activity_query' );

/**
 * Custom scope to add document activity items to connect loop
 *
 * @param  array $retval
 * @return array
 */
function apsa_connect_filter_activity_scope( $retval = array(), $filter = array() ) {

	// Determine the user_id.
	if ( ! empty( $filter['user_id'] ) ) {
		$user_id = $filter['user_id'];
	} else {
		$user_id = bp_displayed_user_id()
			? bp_displayed_user_id()
			: bp_loggedin_user_id();
	}

	// Determine groups of user.
	$groups = groups_get_user_groups( $user_id );
	if ( empty( $groups['groups'] ) ) {
		$groups = array( 'groups' => 0 );
	}

	// Should we show all items regardless of sitewide visibility?
	$show_hidden = array();
	if ( ! empty( $user_id ) && ( $user_id !== bp_loggedin_user_id() ) ) {
		$show_hidden = array(
			'column' => 'hide_sitewide',
			'value'  => 0,
		);
	}

	$retval = array(
		'relation' => 'OR',
		array(
			'relation' => 'AND',
			array(
				'column' => 'type',
				'value'  => 'bbp_topic_create',
			),
			array(
				'column'  => 'item_id',
				'compare' => 'IN',
				'value'   => (array) $groups['groups'],
			),
		),
		array(
			array(
				'column' => 'type',
				'value'  => 'new_doc',
			),
		),
		$show_hidden,

		// Overrides.
		'override' => array(
			'filter'      => array( 'user_id' => 0 ),
			'show_hidden' => true,
		),
	);

	return $retval;
}
add_filter( 'bp_activity_set_connect_scope_args', 'apsa_connect_filter_activity_scope', 10, 2 );
