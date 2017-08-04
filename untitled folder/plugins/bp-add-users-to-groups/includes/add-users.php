<?php

/**
 * [bp_activate_my_bulk_actions description]
 * @param  [type] $bulk_actions [description]
 * @return [type]               [description]
 */
function bp_group_add_bulk_actions( $bulk_actions ) {
	$bulk_actions['group-add'] = __( 'Add to Group', 'bp-add-users' );
	$bulk_actions['group-remove'] = __( 'Remove from Group', 'bp-add-users' );
	return $bulk_actions;
}
add_filter( 'bulk_actions-users', 'bp_group_add_bulk_actions' );

/**
 * [my_bulk_action_handler description]
 * @param  [type] $redirect_to [description]
 * @param  [type] $doaction    [description]
 * @param  [type] $user_ids    [description]
 * @return [type]              [description]
 */
function bp_add_group_bulk_action_handler( $redirect_to, $doaction, $user_ids ) {

	global $wpdb;

	if ( 'group-add' !== $doaction ) {
		return $redirect_to;
	}

	$group_ids = isset( $_GET['group-select'] ) ? $_GET['group-select'] : array();

	if ( ! empty( $group_ids ) ) {
		foreach ( $user_ids as $user_id ) {

			foreach ( $group_ids as $group_id ) {
				groups_join_group( $group_id, $user_id );
			}

			apsa_activate_user( $user_id );
			$group_ids_meta = get_user_meta( $user_id, 'skip_group_sync', TRUE );

			if ( is_array( $group_ids_meta ) ) {
				$c = array_merge( $group_ids_meta, $group_ids );
				$group_ids = array_unique( $c, SORT_REGULAR );

			}

			update_user_meta( $user_id, 'skip_group_sync', $group_ids );
		}
		$redirect_to = add_query_arg( 'bulk_add_users', count( $user_ids ), $redirect_to );
	}

	return $redirect_to;
}
add_filter( 'handle_bulk_actions-users', 'bp_add_group_bulk_action_handler', 10, 3 );

/**
 * [my_bulk_action_handler description]
 * @param  [type] $redirect_to [description]
 * @param  [type] $doaction    [description]
 * @param  [type] $user_ids    [description]
 * @return [type]              [description]
 */
function bp_remove_group_bulk_action_handler( $redirect_to, $doaction, $user_ids ) {

	global $wpdb;

	if ( 'group-remove' !== $doaction ) {
		return $redirect_to;
	}

	$group_ids = isset( $_GET['group-select'] ) ? $_GET['group-select'] : array();

	if ( ! empty( $group_ids ) ) {
		foreach ( $user_ids as $user_id ) {

			foreach ( $group_ids as $group_id ) {
				groups_remove_member( $user_id, $group_id );
			}

			$group_ids_meta = get_user_meta( $user_id, 'skip_group_sync', TRUE );

			if ( is_array( $group_ids_meta ) ) {
				$group_ids = array_diff( $group_ids_meta, $group_ids );
			}

			update_user_meta( $user_id, 'skip_group_sync', $group_ids );
		}
		$redirect_to = add_query_arg( 'bulk_add_users', count( $user_ids ), $redirect_to );
	}

	return $redirect_to;
}
add_filter( 'handle_bulk_actions-users', 'bp_remove_group_bulk_action_handler', 10, 3 );

/**
 * [my_bulk_action_admin_notice description]
 * @return [type] [description]
 */
function bp_group_add_action_admin_notice() {

	if ( ! empty( $_REQUEST['bulk_add_users'] ) ) {
		$activated_count = intval( $_REQUEST['bulk_add_users'] );
		printf( '<div id="message" class="updated fade"><p>' .
		_n( 'Updated %s user(s).',
		'Updated %s user(s).',
		$activated_count,
		'bulk_add_users'
		) . '</p></div>', $activated_count );
      }
}
add_action( 'admin_notices', 'bp_group_add_action_admin_notice' );

/**
 * [bp_activate_filter description]
 */
function bp_group_add_filter() {
	?>

	<style>
		.multiselect {
		float: left;
		margin-right: 6px;
		}

		#checkbox {
			float: left;
		}

		.selectBox {
		  position: relative;
		}

		.selectBox select {
		  width: 100%;
		  font-weight: bold;
		}

		#checkboxes {
		  display: none;
		  position: absolute;
		  margin: 40px 0 0 0;
		  padding: 10px;
		  background: #ffffff;
		  border: 1px solid #dddddd;
		  border-radius: 4px;
		}

		#checkboxes label {
		  display: block;
		}

		#checkboxes label:hover {
		  background-color: #a8d3fc;
		}
	</style>

	<div id="checkbox">
		<div id="checkboxes">
				<?php
				$args['type'] = 'alphabetical';
				$args['show_hidden'] = true;
				$args['per_page'] = -1;

				$groups = groups_get_groups( $args );

				foreach ( $groups['groups'] as $group ) {
					echo '<label for="' . $group->id . '">';
					echo '<input type="checkbox" id="' . $group->id . '" name="group-select[]" value="' . $group->id . '" />' . $group->name . '</label>';
				}
			?>
		</div>
	</div>

	<div class="multiselect">
	  <div class="selectBox">
		<div class="button">Select Groups</div>
	  </div>
	</div>
	<?php
}
add_action( 'restrict_manage_users', 'bp_group_add_filter' );

function bp_group_add_action_js() {
	?>
	<script>
		jQuery(document).ready(function() {

			jQuery('.tablenav.bottom #checkboxes').remove();
			jQuery('.tablenav.bottom .multiselect').remove();

			jQuery('.selectBox').click( function () {
				jQuery('#checkboxes').toggle();
			});
		});
	</script>
	<?php
}

add_action( 'admin_head-users.php', 'bp_group_add_action_js' );

/**
 * [bp_filter_users_by_activation description]
 * @param  [type] $query [description]
 * @return [type]        [description]
 */
function bp_group_add_filter_users( $query ) {
	global $pagenow;

	if ( is_admin() &&
	     'users.php' === $pagenow &&
	     isset( $_GET['group_filter'] ) &&
	     ! empty( $_GET['group_filter'] )
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
//add_filter( 'pre_get_users', 'bp_group_add_filter_users' );

/**
 * [bp_activate_filter_javascript description]
 * @return [type] [description]
 */
function bp_group_add_filter_javascript() {
	?>
	<script type="text/javascript">
	    var el = jQuery("[name='group_filter']");
	    el.change(function() {
			jQuery("[name='group_filter']").val(jQuery(this).val());
	    });
	</script>
	<?php
}
add_action( 'in_admin_footer', 'bp_group_add_filter_javascript' );
