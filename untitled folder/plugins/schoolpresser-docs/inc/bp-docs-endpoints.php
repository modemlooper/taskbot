<?php
/**
 * SchoolPresser Docs Endpoints
 *
 * @package SchoolPresser_Docs
 */

add_action( 'rest_api_init', function () {
	register_rest_route( 'schoolpresser/v1', '/documents', array(
		'methods' => 'POST',
		'callback' => 'bp_docs_api_create_document',
	) );
} );

/**
 * [bp_docs_api_create_document description]
 *
 * @param  WP_REST_Request $request API request objects.
 * @return array
 */
function bp_docs_api_create_document( WP_REST_Request $request ) {

	if ( ! bp_docs_api_document_permission() ) {
		return array( 'error' => 'Permission denied' );
	}

	$params = $request->get_params();

	// error_log(print_r($params,true));
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/media.php' );

	$args = array(
	  'post_title'    => wp_strip_all_tags( $params['doc-title'] ),
	  'post_content'  => '',
	  'post_status'   => wp_strip_all_tags( $params['doc-permission'] ),
	  'post_author'   => bp_loggedin_user_id(),
	  'post_type' 	  => 'schoolpresser_docs',
	);

	if ( isset( $params['edit-doc-id'] ) ) {
		$args['ID'] = $params['edit-doc-id'];
	}

	$post_id = wp_insert_post( $args );

	if ( is_wp_error( $post_id ) ) {
		return array( 'error' => 'document post failed' );
	} else {

		if ( isset( $params['doc-description'] ) && ! empty( $params['doc-description'] ) ) {
			update_post_meta( $post_id, 'spd_docs_description', $params['doc-description'] );
		}

		if ( isset( $params['document-post-in'] ) && '0' !== $params['document-post-in'] ) {
			update_post_meta( $post_id, 'spd_docs_groups', $params['document-post-in'] );
		} else {
			delete_post_meta( $post_id, 'spd_docs_groups' );
		}

		$attachment_id = media_handle_upload( 'files', $post_id );

		if ( ! is_wp_error( $attachment_id ) ) {
			update_post_meta( $post_id, 'spd_docs_attachment', wp_get_attachment_url( $attachment_id ) );
			update_post_meta( $post_id, 'spd_docs_attachment_id', $attachment_id );
		}

		if ( isset( $params['doc-permission'] ) && ! empty( $params['doc-permission'] ) ) {
			wp_update_post( array(
				'ID'           => $post_id,
				'post_status'   => sanitize_text_field( wp_unslash( $params['doc-permission'] ) ),
			));
			update_post_meta( $post_id, 'spd_docs_permission', sanitize_text_field( wp_unslash( $params['doc-permission'] ) ) );
		} else {
			wp_update_post( array(
				'ID'           => $post_id,
				'post_status'   => 'private',
			));
			update_post_meta( $post_id, 'spd_docs_permission', 'private' );
		}

		if ( isset( $params['doc-subjects'] ) && ! empty( $params['doc-subjects'] ) ) {
			wp_set_object_terms( $post_id, null, 'category' );
			foreach ( $params['doc-subjects'] as $subject ) {
				wp_set_object_terms( $post_id, (int) sanitize_text_field( wp_unslash( $subject ) ), 'category', true );
			}
		}

		if ( isset( $params['doc-tags'] ) && ! empty( $params['doc-tags'] ) ) {
				wp_set_post_tags( $post_id, sanitize_text_field( wp_unslash( $params['doc-tags'] ) ) );
		}

		if ( isset( $params['doc-type'] ) && ! empty( $params['doc-type'] ) ) {
			wp_set_object_terms( $post_id, null, 'item_type' );
			foreach ( $params['doc-type'] as $type ) {
				wp_set_object_terms( $post_id, (int) sanitize_text_field( wp_unslash( $type ) ), 'item_type', true );
			}
		}

		if ( isset( $params['doc-license'] ) && ! empty( $params['doc-license'] ) ) {
			wp_set_object_terms( $post_id, (int) sanitize_text_field( wp_unslash( $params['doc-license'] ) ), 'license', false );
		}

		$post_obj = get_post( $post_id );

		$ttachmnt_id = isset( $attachment_id ) ? $attachment_id : 0;
		$description = isset( $params['doc-description'] ) ? sanitize_text_field( substr( $params['doc-description'], 0, 200 ) ) : '';
		$group_ids = isset( $params['document-post-in'] ) ? $params['document-post-in'] : null;

		$activity_args = array(
			'spd_docs_attachment_id' => $ttachmnt_id,
			'user_ID' => bp_loggedin_user_id(),
			'ID' => $post_id,
			'spd_docs_description' => $description,
			'spd_docs_groups' => $group_ids,
		);

		bp_doc_add_activity( $activity_args, $post_obj );

		// Start output of api response. This is a dirty method for API templating. Consider js template in future.
		ob_start();

		$query = new WP_Query( array( 'p' => $post_id, 'post_type' => 'schoolpresser_docs' ) );

		if ( $query->have_posts() ) :
			while ( $query->have_posts() ) : $query->the_post();
				bp_docs_get_template_part( 'document-index' );
			endwhile;
		endif;

		$output = ob_get_contents();
		ob_end_clean();

		return $output;

	}

	return;
}

/**
 * [bp_docs_api_document_permission description]
 *
 * @return [type] [description]
 */
function bp_docs_api_document_permission() {

	if ( ! is_user_logged_in() ) {
		return;
	}
	return true;
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'schoolpresser/v1', '/documents/tags', array(
		'methods' => 'GET',
		'callback' => 'bp_docs_api_tags_data',
	) );
} );

/**
 * [bp_docs_api_tags_data description]
 *
 * @return [type] [description]
 */
function bp_docs_api_tags_data() {

	if ( ! is_user_logged_in() ) {
		return;
	}

	$tags = get_terms( 'post_tag', array(
		'hide_empty' => false,
	) );
	$tag_array = array();

	if ( ! empty( $tags ) ) {
		foreach ( $tags as $tag ) {
			$tag_array[] = $tag->name;
		}
	}

	return $tag_array;
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'schoolpresser/v1', '/documents/subjects', array(
		'methods' => 'GET',
		'callback' => 'bp_docs_api_subjects_data',
	) );
} );

/**
 * [bp_docs_api_subjects_data description]
 *
 * @return [type] [description]
 */
function bp_docs_api_subjects_data() {

	if ( ! is_user_logged_in() ) {
		return;
	}

	$tags = get_terms( 'category', array(
		'hide_empty' => false,
	) );
	$tag_array = array();

	if ( ! empty( $tags ) ) {
		foreach ( $tags as $tag ) {
			$tag_array[] = $tag->name;
		}
	}

	return $tag_array;
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'schoolpresser/v1', '/documents/authors', array(
		'methods' => 'GET',
		'callback' => 'bp_docs_api_authors',
	) );
} );

/**
 * [bp_docs_api_authors description]
 *
 * @param  WP_REST_Request $request [description]
 * @return [type]                   [description]
 */
function bp_docs_api_authors( WP_REST_Request $request ) {

	if ( ! is_user_logged_in() ) {
		return;
	}

	$params = $request->get_params();
	$authors = array();
	if ( isset( $params['term'] ) ) {
		$users = BP_Core_User::get_users_by_letter( $params['term'] );
		if ( isset( $users['users'] ) ) {
			foreach ( $users['users'] as $user ) {
				$authors[] = $user->user_login;
			}
		}
	}
	return $authors;
}
