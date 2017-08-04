<?php

/**
 * [bp_activate_my_bulk_actions description]
 * @param  [type] $bulk_actions [description]
 * @return [type]               [description]
 */
function bp_activate_my_bulk_actions( $bulk_actions ) {
	$bulk_actions['activate'] = __( 'Activate', 'bp-activate-users' );
	return $bulk_actions;
}
add_filter( 'bulk_actions-users', 'bp_activate_my_bulk_actions' );

/**
 * [my_bulk_action_handler description]
 * @param  [type] $redirect_to [description]
 * @param  [type] $doaction    [description]
 * @param  [type] $user_ids    [description]
 * @return [type]              [description]
 */
function bp_activate_bulk_action_handler( $redirect_to, $doaction, $user_ids ) {

	global $wpdb;

	if ( 'activate' !== $doaction ) {
		return $redirect_to;
	}

	foreach ( $user_ids as $user_id ) {
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->users} SET user_status = 0 WHERE ID = %d", $user_id ) );
		update_user_meta( $user_id, 'last_activity', current_time( 'mysql' ) );
	}
	$redirect_to = add_query_arg( 'bulk_activate_users', count( $user_ids ), $redirect_to );
	return $redirect_to;
}
add_filter( 'handle_bulk_actions-users', 'bp_activate_bulk_action_handler', 10, 3 );

/**
 * [my_bulk_action_admin_notice description]
 * @return [type] [description]
 */
function bp_activate_action_admin_notice() {

	if ( ! empty( $_REQUEST['bulk_activate_users'] ) ) {
		$activated_count = intval( $_REQUEST['bulk_activate_users'] );
		printf( '<div id="message" class="updated fade"><p>' .
		_n( 'Activated %s users.',
		'Activated %s users.',
		$activated_count,
		'bulk_activate_users'
		) . '</p></div>', $activated_count );
      }
}
add_action( 'admin_notices', 'bp_activate_action_admin_notice' );

/**
 * [bp_activate_filter description]
 */
function bp_activate_filter() {
	echo '<select name="user_filter" style="float:none;">';
	echo '<option value="">Activated</option>';
	echo '<option value="activated">Not Activated</option>';
	echo '</select>';
	echo '<input id="post-query-submit" type="submit" class="button" value="Filter" name="">';
}
add_action( 'restrict_manage_users', 'bp_activate_filter' );

/**
 * [bp_filter_users_by_activation description]
 * @param  [type] $query [description]
 * @return [type]        [description]
 */
function bp_activate_filter_users_by_activation( $query ) {
	global $pagenow;

	if ( is_admin() &&
	     'users.php' === $pagenow &&
	     isset( $_GET['user_filter'] ) &&
	     ! empty( $_GET['user_filter'] )
	   ) {

	    $meta_query = array(
	        array(
	            'key'   => 'last_activity',
	            'compare' => 'NOT EXISTS',
	        )
	    );
	    $query->set( 'meta_query', $meta_query );
	}
}
add_filter( 'pre_get_users', 'bp_activate_filter_users_by_activation' );

/**
 * [bp_activate_filter_javascript description]
 * @return [type] [description]
 */
function bp_activate_filter_javascript() {
	?>
	<script type="text/javascript">
	    var el = jQuery("[name='user_filter']");
	    el.change(function() {
	        el.val(jQuery(this).val());
	    });
	</script>
	<?php
}
add_action( 'in_admin_footer', 'bp_activate_filter_javascript' );
