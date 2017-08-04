<?php
/**
 * SchoolPresser Docs filters
 *
 * @package SchoolPresser_Docs
 */

/**
 * The bp_docs_loop_profile_filter function.
 *
 * @param mixed $query Object.
 * @return object
 */
function bp_docs_loop_profile_filter( $query ) {

	$paged = ( isset( $_GET['mpage'] ) ) ? wp_unslash( $_GET['mpage'] ) : 1;

	if ( bp_is_user() ) {

		$author     = bp_displayed_user_id();
		$action     = bp_current_action();
		$action_var = bp_action_variables();

		if ( 'documents' === $action ) {

			$query = array(
				'post_type' => 'schoolpresser_docs',
				'author' => $author,
				'post_status' => 'any',
				'posts_per_page' => 10,
				'orderby' => 'modified',
				'paged' => $paged,
			);

		}
	}

	return $query;
}
add_filter( 'bp_docs_loop_filter', 'bp_docs_loop_profile_filter' );


/**
 * The bp_docs_loop_group_filter function.
 *
 * @param mixed $query Object.
 * @return object
 */
function bp_docs_loop_group_filter( $query ) {

	$paged = ( isset( $_GET['mpage'] ) ) ? wp_unslash( $_GET['mpage'] ) : 1;

	if ( bp_is_group() ) {

		$group_id     = bp_get_group_id();
		$action     = bp_current_action();
		$action_var = bp_action_variables();

		if ( 'documents' === $action ) {

			$query = array(
				'post_type' => 'schoolpresser_docs',
				'post_status' => 'any',
				'posts_per_page' => 10,
				'orderby' => 'modified',
				'paged' => $paged,
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key'     => 'spd_docs_groups',
						'value'	  => serialize( strval( $group_id ) ),
						'compare' => 'LIKE',
					),
				),
			);

		}
	}

	return $query;
}
add_filter( 'bp_docs_loop_filter', 'bp_docs_loop_group_filter' );

/**
 * [bp_docs_taxonomy_filter description]
 *
 * @param  [type] $query
 * @return [type]
 */
function bp_docs_taxonomy_filter( $query ) {

	if ( isset( $_GET['filter'] ) ) {

		$filters = wp_unslash( $_GET['filter'] );

		$tax_query = array();
		$tax_query['relation'] = 'OR';

		foreach ( $filters as $filter => $value ) {

			$tax_query[] = array(
				'taxonomy' => $filter,
				'field'    => 'id',
				'terms'    => $value,
			);

		}

		$query['tax_query'] = $tax_query;

	}
	return $query;
}
add_filter( 'bp_docs_loop_filter', 'bp_docs_taxonomy_filter' );

/**
 * [bp_docs_tabs_filter description]
 *
 * @param  [type] $query
 * @return [type]
 */
function bp_docs_tabs_filter( $query ) {

	if ( isset( $_GET['document_filter'] ) ) {
		$filter = $_GET['document_filter'];
	} elseif ( isset( $_POST['action'] ) && 'facetwp_refresh' === $_POST['action'] ) {
		$filter = isset( $_POST['data']['http_params']['get']['document_filter'] ) ? $_POST['data']['http_params']['get']['document_filter'] : '';
	} else {
		return $query;
	}

	$user_id = bp_loggedin_user_id();

	switch ( $filter ) {

		case 'personal':
			$query['author'] = (int) $user_id;
		break;

		case 'groups':
			$all_groups = BP_Groups_Member::get_group_ids( $user_id );
			$paged = ( isset( $_GET['mpage'] ) ) ? $_GET['mpage'] : 1;

			$query = array(
				'post_type' => 'schoolpresser_docs',
				'post_status' => 'any',
				'posts_per_page' => 10,
				'orderby' => 'modified',
				'paged' => $paged,
				'meta_query'   => array(
					'relation' => 'AND',
					array(
						'key'  => 'spd_docs_attachment',
					),
					array(
						'key'  => 'spd_docs_groups',
					),
				),
			);

		break;

	}

	return $query;
}
add_filter( 'bp_docs_loop_filter', 'bp_docs_tabs_filter' );


/**
 * Returns true user can see teased premission doc.
 *
 * @param  boolean $can_access
 * @param  integer $post_id
 * @return boolean
 */
function bp_docs_filter_teased_download_access( $can_access, $post_id ) {

	$post_author_id = get_post_field( 'post_author', $post_id );

	if ( ! bp_is_group() && ! bp_is_user() && ! bp_docs_is_teased( $post_id ) && (int) bp_loggedin_user_id() !== (int) $post_author_id ) {
		$can_access = false;
	}
	return $can_access;

}
//add_filter( 'bp_docs_can_download', 'bp_docs_filter_teased_download_access', 10, 2 );


/**
 * Filter docs by group id meta when on sections tab
 *
 * @param  string $where
 * @return string
 */
function bp_docs_filter_posts_where( $where ) {
	global $wpdb;

	$component = bp_current_component();
	$action = bp_current_action();

	if ( BP_DOCS_SLUG === $component && isset( $_GET['document_filter'] ) && 'groups' === $_GET['document_filter'] ) {

		$all_groups = BP_Groups_Member::get_group_ids( bp_loggedin_user_id() );
		$sqlstmt = '';

		// Loop group ids and build sql for each serialized string of group id.
		foreach ( $all_groups['groups'] as $group => $value ) {
			$id = serialize( strval( $value ) );
			$sqlstmt .= ( 0 === $group ) ? " meta_value LIKE '%$id%'" : " OR meta_value LIKE '%$id%'";
		}

		$where .= " AND ID IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE $sqlstmt )";

	}

	// Include private doc IDs of logged in user to sql WHERE when viewing all docs tab.
	if ( BP_DOCS_SLUG === $component && ! isset( $_GET['document_filter'] ) && ! $action ) {
		$user_id = (int) bp_loggedin_user_id();

		$search = isset( $_GET['document_search'] ) && isset( $_GET['document_search_submit'] ) ? sanitize_title( wp_unslash( $_GET['document_search'] ) ) : false;

		if ( $search ) {
			$srchsql = "AND post_title LIKE '%$search%'";

			$where .= " AND ID IN ( SELECT ID FROM {$wpdb->posts} WHERE post_type = 'schoolpresser_docs' AND post_author = $user_id AND post_status = 'private' $srchsql )";
			$where .= " OR ID IN ( SELECT ID FROM {$wpdb->posts} WHERE post_type = 'schoolpresser_docs' AND post_status != 'private' $srchsql )";
		}
	}

	return $where;
}
add_filter( 'posts_where' , 'bp_docs_filter_posts_where' );

/**
 * Filter to tell facetwp that a custom loop is the loop to process.
 * Used in conjunction with wp_query arg facetwp = true.
 *
 * @param  boolean $is_main_query
 * @param  object  $query
 * @return boolean
 */
function bp_docs_facetwp_is_main_query( $is_main_query, $query ) {

	if ( isset( $query->query_vars['facetwp'] ) ) {
		$is_main_query = true;
	}
	return $is_main_query;
}
add_filter( 'facetwp_is_main_query', 'bp_docs_facetwp_is_main_query', 10, 2 );

/**
 * Fix to allow facetwp to index serialized meta.
 *
 * @param  array  $params
 * @param  object $class
 * @return array
 */
function bp_docs_index_serialized_data( $params, $class ) {

	if ( 'permission' === $params['facet_name'] ) {
		$values = (array) $params['facet_value'];
		foreach ( $values as $val ) {
			$params['facet_value'] = $val;
			$params['facet_display_value'] = $val;
			$class->insert( $params );
		}
		return false; // skip default indexing
	}
	return $params;
}
add_filter( 'facetwp_index_row', 'bp_docs_index_serialized_data', 10, 2 );

/**
 * Switch indexed display value to only show year.
 *
 * @param  array  $params
 * @param  object $class
 * @return array
 */
function bp_docs_date_value_data( $params, $class ) {

	if ( 'date' === $params['facet_name'] ) {
		$values = (array) $params['facet_value'];
		foreach ( $values as $val ) {
			$date = new DateTime( $params['facet_display_value'] );
			$result = $date->format('Y');
			$params['facet_value'] = $result;
			$params['facet_display_value'] = $result;
			$class->insert( $params );
		}
		return false; // skip default indexing
	}
	return $params;
}
add_filter( 'facetwp_index_row', 'bp_docs_date_value_data', 10, 2 );

/**
 * Add docs post type to facetwp indexer
 *
 * @param  array $args
 * @return array
 */
add_filter( 'facetwp_indexer_query_args', function( $args ) {
	$args['post_type'] = 'schoolpresser_docs';
	$args['post_status'] = 'any';
	return $args;
});

/**
 * Filter facetwp ajax wp_query
 *
 * @param  array $query_args
 * @return array
 */
add_filter( 'facetwp_query_args', function( $query_args, $class ) {
	if ( 'documents' === $class->ajax_params['template'] ) {
		$query_args = bp_docs_loop_filter( $query_args );
		$query_args['post_type'] = 'schoolpresser_docs';
		$query_args['post_status'] = 'any';
		$query_args['posts_per_page'] = 10;
		$query_args['facetwp'] = true;
	}
	return $query_args;
}, 10, 2 );
