<?php


function apsa_api_endpoint() {
	register_rest_route( 'user-import/v1', '/lookup/(?P<id>\d+)', array(
		'methods' => 'GET',
		'callback' => 'apsa_api_call_import_user',
		'permission_callback' => function () {
			return current_user_can( 'edit_others_posts' );
		},
	) );

	register_rest_route( 'user-import/v1', '/create/(?P<id>\d+)', array(
		'methods' => 'GET',
		'callback' => 'apsa_api_import_create_account',
	) );

	register_rest_route( 'user-import/v1', '/sync/(?P<id>\d+)', array(
		'methods' => 'GET',
		'callback' => 'apsa_api_import_sync_account',
		'permission_callback' => function () {
			return current_user_can( 'edit_others_posts' );
		},
	) );
}
add_action( 'rest_api_init', 'apsa_api_endpoint' );

function apsa_api_call_import_user( WP_REST_Request $request ) {
	// error_log( 'apsa_api_call_import_user' );
	$member_id = $request->get_param( 'id' );
	return apsa_user_import_fetch( $member_id );
}

function apsa_api_import_create_account( WP_REST_Request $request ) {
	// error_log( 'apsa_api_import_create_account' );
	$member_id = $request->get_param( 'id' );
	$force = $request->get_param( 'force' );
	return apsa_user_import_create_account( $member_id, $force );
}

function apsa_api_import_sync_account( WP_REST_Request $request ) {
	// error_log( 'apsa_api_import_sync_account' );
	$member_id = $request->get_param( 'id' );
	return apsa_import_user_data_sync( $member_id );
}
