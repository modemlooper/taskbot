<?php

/**
 * Add username (login) change metabox on BuddyPress extended profile admin page
 *
 * @return void
 */
function apsa_bp_user_meta_box() {

	add_meta_box(
		'metabox_id',
		__( 'Change Username', 'apsa' ),
		'apsa_bp_user_inner_meta_box',
		get_current_screen()->id,
		'side',
		'low'
	);
}
add_action( 'bp_members_admin_user_metaboxes', 'apsa_bp_user_meta_box' );

/**
 * Username change metabox inputs
 *
 * @return void
 */
function apsa_bp_user_inner_meta_box() {
	?>
	<p>Enter an API MemberKey and click Update Profile. This will change the username and connect this user to their account in NOAH.</p>
	<p>WARNING: this can cause problems and disconnect user from the API if the keys are not correct. Only change username to match an API MemberKEY. This can be useful if you need to merge two accounts or if a user was added manually and you need to sync the account to the API.</p>
	<input type="checkbox" id="username-change-verify" name="username_change_verifiy" value="1" /><label> verify change</label>
	<input type="text" id="username-field" name="username_field" value="<?php echo apsa_get_admn_ext_profile_user_login(); ?>">
	<?php
}

/**
 * Username change metabox save logic.
 *
 * @return void
 */
function apsa_bp_user_save_metabox() {

	if ( isset( $_POST['save'] ) && 'Update Profile' === $_POST['save'] && isset( $_POST['username_change_verifiy'] ) ) {

		$meta_val = isset( $_POST['username_field'] ) ? sanitize_text_field( trim( $_POST['username_field'] ) ) : '';

		if ( '' === $meta_val ) {
			return;
		}

		$user_id = isset( $_GET['user_id'] ) ? $_GET['user_id'] : bp_loggedin_user_id();

		if ( $user_id ) {
			update_user_meta( $user_id, 'apsa_member_id', $meta_val );
			apsa_run_change_username( array( $user_id ) );
		}
	}
}
add_action( 'bp_members_admin_update_user', 'apsa_bp_user_save_metabox' );

/**
 * Helper function to return username (user_login)
 *
 * @return string
 */
function apsa_get_admn_ext_profile_user_login() {
	$user_id = isset( $_GET['user_id'] ) ? $_GET['user_id'] : bp_loggedin_user_id();
	$user = get_userdata( $user_id );
	if ( $user ) {
		return $user->user_login;
	}
	return '';
}
