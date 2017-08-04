<?php
/**
 * SchoolPresser Docs Functions
 *
 * @package SchoolPresser_Docs
 */

/**
 * The bp_docs_is_action_edit function.
 */
function bp_docs_is_action_edit() {

	$variables = bp_action_variables();

	if ( isset( $variables[1] ) && 'edit' === $variables[1] ) {
		return true;
	}

	return false;
}

/**
 * [bp_docs_can_edit description]
 *
 * @return [type] [description]
 */
function bp_docs_can_edit() {

	$post_doc = bp_docs_single_doc_post_from_slug();

	if ( is_user_logged_in() && bp_loggedin_user_id() === (int) $post_doc->post_author ) {
		return true;
	}
	return apply_filters( 'bp_docs_can_edit', false );
}

/**
 * The bp_docs_loop_filter function.
 *
 * @access public
 */
function bp_docs_loop_filter( $query = array() ) {
	$bp = buddypress();

	$paged = ( isset( $_GET['mpage'] ) ) ? sanitize_title( wp_unslash( $_GET['mpage'] ) ) : 1;

	$query = array(
		'post_type'      => 'schoolpresser_docs',
		'posts_per_page' => 10,
		'orderby'        => 'modified',
		'paged'          => $paged,
		'meta_query'     => array(
			array(
				'key'     => 'spd_docs_attachment',
			),
		),
	);

	$query = apply_filters( 'bp_docs_loop_filter', $query );

	return $query;
}

/**
 * The bp_docs_css_class function.
 */
function bp_docs_css_class() {
	echo esc_attr( bp_get_docs_css_class() );
}
	/**
	 * The bp_get_docs_css_class function.
	 *
	 * @return string
	 */
function bp_get_docs_css_class() {
	return 'schoolpresser-docs';
}

/**
 * The bp_docs_userlink function.
 */
function bp_docs_userlink() {
		echo esc_url( bp_docs_get_userlink() );

}

	/**
	 * The bp_docs_get_userlink function.
	 */
function bp_docs_get_userlink() {
	global  $post;

	if ( $post->post_author ) {
		return bp_core_get_user_domain( $post->post_author );
	}

	return;
}

/**
 * The bp_docs_time_since function.
 *
 * @param mixed $photo_id Photo ID.
 * @return string
 */
function bp_docs_time_since( $photo_id ) {

	$attachment = get_post( $photo_id );
	$post_date = sprintf( __( '%1$s ago', 'bp_docs' ), human_time_diff( strtotime( $attachment->post_date ), current_time( 'timestamp' ) ) );

	return $post_date;
}


/**
 * The bp_docs_comments function.
 *
 * @param mixed $comment Comments.
 */
function bp_docs_comments( $comment ) {
	bp_docs_get_template_part( 'comments' );
}


/**
 * The bp_docs_add_activity_meta function.
 *
 * @param mixed $activity Activity object.
 */
function bp_docs_add_activity_meta( $activity ) {

	if ( ! empty( $_POST['attachment_id'] ) ) {
		bp_activity_update_meta( $activity->id, 'bp_docs_attachment_id', sanitize_text_field( wp_unslash( $_POST['attachment_id'] ) ) );
		update_post_meta( $_POST['attachment_id'], 'description', sanitize_text_field( wp_unslash( $_POST['content'] ) ) );
		update_post_meta( $_POST['attachment_id'], 'activity_id', sanitize_text_field( wp_unslash( $activity->id ) ) );
	}
}
add_action( 'bp_activity_after_save', 'bp_docs_add_activity_meta' );


/**
 * The bp_docs_delete_attachments_before_delete_post function.
 *
 * This is document clean up, deletes attachents/images when document is deleted
 * Also deletes the activity item associated with post id
 *
 * @param mixed $id Attachemnt ID.
 */
function bp_docs_delete_attachments_before_delete_post( $id ) {
	global $post_type;

	if ( ! $post_type && 'schoolpresser_docs' !== $post_type ) {
		return;
	}

	$subposts = get_children(array(
	    'post_parent' => $id,
	    'post_type'   => 'any',
	    'numberposts' => -1,
	    'post_status' => 'any',
	));

	if ( is_array( $subposts ) && count( $subposts ) > 0 ) {
		$uploadpath = wp_upload_dir();

		foreach ( $subposts as $subpost ) {

			$_wp_attached_file = get_post_meta( $subpost->ID, '_wp_attached_file', true );

			$original = basename( $_wp_attached_file );
			$pos = strpos( strrev( $original ), '.' );
			if ( strpos( $original, '.' ) !== false ) {
				$ext = explode( '.', strrev( $original ) );
				$ext = strrev( $ext[0] );
			} else {
				$ext = explode( '-', strrev( $original ) );
				$ext = strrev( $ext[0] );
			}

			$pattern = $uploadpath['basedir'] . '/' . dirname( $_wp_attached_file ) . '/' . basename( $original, '.' . $ext ) . '-[0-9]*x[0-9]*.' . $ext;
			$original = $uploadpath['basedir'] . '/' . dirname( $_wp_attached_file ) . '/' . basename( $original, '.' . $ext ) . '.' . $ext;
			if ( getimagesize( $original ) ) {
				$thumbs = glob( $pattern );
				if ( is_array( $thumbs ) && count( $thumbs ) > 0 ) {
					foreach ( $thumbs as $thumb ) {
						unlink( $thumb );
					}
				}
			}
			wp_delete_attachment( $subpost->ID, true );
		}
	}

	bp_activity_delete( array( 'secondary_item_id' => $id ) );
}
// Till wp 3.1.
add_action( 'delete_post', 'bp_docs_delete_attachments_before_delete_post' );
// From wp 3.2.
add_action( 'before_delete_post', 'bp_docs_delete_attachments_before_delete_post' );


/**
 * The bp_docs_user_can_delete function.
 *
 * @param mixed $user_id User ID.
 * @return boolean
 */
function bp_docs_user_can_delete( $user_id = 0 ) {

	if ( 0 === $user_id  ) {
		$user_id = bp_displayed_user_id();
	}

	if ( bp_loggedin_user_id() === (int) $user_id ) {
		return true;
	}
	return;
}

/**
 * [bp_docs_can_upload description]
 *
 * @param  integer $user_id [description]
 * @return [type]           [description]
 */
function bp_docs_can_upload( $user_id = 0 ) {

	if ( ! is_user_logged_in() ) {
		return false;
	}

	if ( 0 === $user_id  ) {
		$user_id = bp_loggedin_user_id();
	}

	return apply_filters( 'bp_docs_can_upload', true, $user_id );

}


/**
 * The bp_docs_user_can_access function.
 *
 * @param int $user_id (default: 0).
 * @return boolean
 */
function bp_docs_user_can_access( $user_id = 0 ) {

	if ( ! is_user_logged_in() ) {
		return;
	}

	if ( 0 === $user_id  ) {
		$user_id = bp_displayed_user_id();
	}

	if ( ! apply_filters( 'bp_docs_user_can_access', $user_id ) ) {
		return;
	}

	if ( bp_loggedin_user_id() === (int) $user_id ) {
		return true;
	}
	return;
}


/**
 * The bp_is_friend_boolean function.
 *
 * Thank you for being a friend.
 *
 * @return boolean
 */
function bp_is_friend_boolean() {
	$is_friend = bp_is_friend();

	if ( 'is_friend' === $is_friend ) {
		return true;
	}
	return false;
}


/**
 * The bp_docs_pagination_count function.
 *
 * @param object $query Query object.
 */
function bp_docs_pagination_count( $query ) {
	echo bp_docs_get_pagination_count( $query );
}
	/**
	 * Generate the "Viewing x-y of z albums" pagination message.
	 *
	 * @param object $query Query object.
	 * @return string
	 */
function bp_docs_get_pagination_count( $query ) {

	$action = ( 'documents' !== bp_current_action() && bp_is_user() ) ? __( 'documents', 'schoolpresser-docs' ) : __( 'document', 'schoolpresser-docs' );

	if ( bp_is_directory() && ! bp_current_action() ) {
		$action = __( 'document', 'schoolpresser-docs' );
	}

	$paged = ( isset( $_GET['mpage'] ) ) ? wp_unslash( $_GET['mpage'] ) : 1;
	$posts_per_page = $query->query['posts_per_page'];

	$start_num = intval( ( $paged - 1 ) * $posts_per_page ) + 1;
	$from_num  = bp_core_number_format( $start_num );
	$to_num    = bp_core_number_format( ( $start_num + ( $posts_per_page - 1 ) > $query->found_posts ) ? $query->found_posts : $start_num + ( $posts_per_page - 1 ) );
	$total     = bp_core_number_format( $query->found_posts );

	if ( 1 == $query->found_posts ) {
		$message = __( 'Viewing 1 ' . $action, 'schoolpresser-docs' );
	} else {
		$message = sprintf( _n( 'Viewing %1$s - %2$s of %3$s ' . $action . 's', 'Viewing %1$s - %2$s of %3$s ' . $action . 's', $query->found_posts, 'schoolpresser-docs' ), $from_num, $to_num, $total );
	}

	/**
		 * Filters the "Viewing x-y of z albums" pagination message.
		 *
		 * @param string $message  "Viewing x-y of z album" text.
		 * @param string $from_num Total amount for the low value in the range.
		 * @param string $to_num   Total amount for the high value in the range.
		 * @param string $total    Total amount of albums found.
		 */
	return apply_filters( 'bp_docs_get_pagination_count', $message, $from_num, $to_num, $total );
}


/**
 * The bp_docs_pagination_links function.
 *
 * @param mixed $query Query object.
 */
function bp_docs_pagination_links( $query ) {
	echo bp_docs_get_pagination_links( $query );
}

/**
 * [bp_docs_get_pagination_links description]
 *
 * @param  [type] $query [description]
 * @return [type]        [description]
 */
function bp_docs_get_pagination_links( $query ) {

	$paged = ( isset( $_GET['mpage'] ) ) ? wp_unslash( $_GET['mpage'] ) : 1;

	$pag_args = array(
		'mpage' => '%#%',
	);

	if ( defined( 'DOING_AJAX' ) && true === (bool) DOING_AJAX ) {
		$base = remove_query_arg( 's', wp_get_referer() );
	} else {
		$base = '';
	}

	echo paginate_links( array(
		'base'      => add_query_arg( $pag_args, $base ),
		'format'    => '',
		'total'     => ceil( (int) $query->found_posts / (int) $query->query['posts_per_page'] ),
		'current'   => $paged,
		'prev_text' => _x( '&larr;', 'docs pagination previous text', 'schoolpresser-docs' ),
		'next_text' => _x( '&rarr;', 'docs pagination next text', 'schoolpresser-docs' ),
	) );
}

/**
 * [bp_docs_directory_link description]
 *
 * @return [type] [description]
 */
function bp_docs_directory_link() {
	echo get_site_url() . '/' . BP_DOCS_SLUG;
}

/**
 * [bp_docs_download_link description]
 *
 * @param  [type] $post_id [description]
 * @return [type]          [description]
 */
function bp_docs_download_link( $post_id ) {
	$meta = get_post_meta( $post_id );
	echo isset( $meta['spd_docs_attachment_id'] ) ? esc_attr( $meta['spd_docs_attachment_id'][0] ) : 0 ;
}

/**
 * [bp_docs_file_type description]
 *
 * @param  [type] $post_id [description]
 * @return [type]          [description]
 */
function bp_docs_file_type( $post_id ) {
	$meta = get_post_meta( $post_id );
	$attachment = isset( $meta['spd_docs_attachment'] ) ? $meta['spd_docs_attachment'][0] : '';
	$file_meta = wp_check_filetype( $attachment );
	if ( empty( $file_meta['ext'] ) ) {
		return null;
	}
	return $file_meta['ext'];
}

/**
 * [bp_docs_file_type_image description]
 *
 * @param  [type] $type [description]
 * @return [type]       [description]
 */
function bp_docs_file_type_image( $type = null ) {
	if ( null === $type ) {
		$type = 'doc';
	}
	echo '<img src="' . esc_url( schoolpresser_docs()->url() ) . 'images/icons/' . esc_attr( $type ) . '.png">';
}

/**
 * [bp_docs_file_description description]
 *
 * @param  [type] $post_id [description]
 * @return [type]          [description]
 */
function bp_docs_file_description( $post_id ) {
	$meta = get_post_meta( $post_id );
	if ( ! empty( $meta['spd_docs_description'][0] ) ) {
			$ellipsis = mb_strlen( $meta['spd_docs_description'][0] ) >= 200 ? '...' : '';
			echo esc_textarea( substr( $meta['spd_docs_description'][0], 0, 200 ) . $ellipsis );
	}
}

/**
 * [bp_docs_file_permalink description]
 *
 * @param  [type] $post_id [description]
 * @return [type]          [description]
 */
function bp_docs_file_permalink( $post_id ) {
	$slug = basename( get_permalink( $post_id ) );
	echo trailingslashit( bp_get_root_domain() . '/documents/' . $slug );
}

/**
 * [bp_docs_group_name description]
 *
 * @param  [type] $post_id [description]
 * @return [type]          [description]
 */
function bp_docs_group_name( $post_id ) {
	$meta = get_post_meta( $post_id );
	if ( ! empty( $meta['spd_docs_groups'][0] ) ) {
		$group_ids = maybe_unserialize( $meta['spd_docs_groups'][0] );
		$last_key = bp_docs_last_array_key( $group_ids );
		$group_name = '';
		if ( is_array( $group_ids ) ) {
			foreach ( $group_ids as $group_id => $value ) {
				$comma = $last_key === $group_id ? '' : ', ';
				$group = groups_get_group( array( 'group_id' => $value ) );
				$group_permalink = trailingslashit( bp_get_root_domain() . '/' . bp_get_groups_root_slug() . '/' . $group->slug . '/documents' );
				$group_name .= '<a href="' . $group_permalink . '">' . $group->name . '</a>' . $comma;
			}
		}
		return $group_name;

	} else {
		return;
	}
}

/**
 * [bp_docs_can_download description]
 *
 * @return [type] [description]
 */
function bp_docs_can_download( $post_id ) {

	$can_access = false;

	if ( is_user_logged_in() ) {
		$can_access = true;
	}
	return apply_filters( 'bp_docs_can_download', $can_access, $post_id );
}

/**
 * [bp_docs_download_header description]
 *
 * @return [type] [description]
 */
function bp_docs_download_header() {

	/**
	 * TODO may need to add a crypt and salt for more security.
	 */

	if ( is_user_logged_in() && isset( $_GET['download'] ) && bp_docs_can_download( $_GET['download'] ) ) {
		$file = wp_get_attachment_url( $_GET['download'] );

		if ( $file ) {
			header( 'Content-Description: File Transfer' );
			header( 'Content-Type: application/octet-stream' );
			header( 'Content-Disposition: attachment; filename=' . basename( $file ) );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Pragma: public' );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			set_time_limit( 0 );
			$file = @fopen( $file, 'rb' );
			while ( ! feof( $file ) ) {
				print( @fread( $file, 1024 * 8 ));
				ob_flush();
				flush();
			}
		}
		exit;
	}
}
add_action( 'init', 'bp_docs_download_header' );

/**
 * [bp_docs_single_doc_reset_post description]
 */
function bp_docs_single_doc_reset_post() {

	$component = bp_current_component();
	$doc_post = get_page_by_path( bp_current_action(), OBJECT, 'schoolpresser_docs' );

	if ( null !== $doc_post ) {

		$action = ! empty( bp_current_action() ) ? bp_current_action() : '';

		if ( ! bp_is_user() && $action && BP_DOCS_SLUG === $component ) {

			$bp_pages = get_option( 'bp-pages' );
			$page_id = $bp_pages[ $component ];

			bp_theme_compat_reset_post( array(
				'ID'             => $page_id,
				'post_title'     => 'Documents',
				'post_author'    => 0,
				'post_date'      => 0,
				'post_content'   => '',
				'post_type'      => 'schoolpresser_docs',
				'post_status'    => 'publish',
				'is_archive'     => true,
				'comment_status' => 'closed',
			) );

			add_filter( 'bp_replace_the_content', 'bp_docs_single_doc_content' );

		}
	}

}
add_action( 'bp_screens', 'bp_docs_single_doc_reset_post' );

/**
 * [bp_docs_single_doc_content description]
 *
 * @return void
 */
function bp_docs_single_doc_content() {
	add_filter( 'bp_docs_loop_filter', 'bp_docs_single_doc_post_filter' );
	bp_docs_get_template_part( 'document-single' );
}

/**
 * [bp_docs_single_doc_post_filter description]
 *
 * @param  array $query unmodified post query.
 * @return array        modified post query
 */
function bp_docs_single_doc_post_filter( $query ) {

	$doc_post = bp_docs_single_doc_post_from_slug();

	if ( $doc_post ) {
		$query['p'] = $doc_post->ID;
	}

	return $query;

}

/**
 * [bp_docs_single_doc_post_from_slug description]
 *
 * @return [type] [description]
 */
function bp_docs_single_doc_post_from_slug() {
	$doc_post = get_page_by_path( bp_current_action(), OBJECT, 'schoolpresser_docs' );

	if ( $doc_post ) {
		return $doc_post;
	}
	return false;
}

/**
 * [bp_docs_edit_form_value description]
 *
 * @param  [type] $field [description]
 * @return [type]        [description]
 */
function bp_docs_edit_form_value( $field ) {

	$doc_post = get_page_by_path( bp_current_action(), OBJECT, 'schoolpresser_docs' );

	if ( $doc_post ) {

		switch ( $field ) {

			case 'title':
				echo esc_attr( $doc_post->post_title );
			break;
			case 'description':
				$post_meta = get_post_meta( $doc_post->ID, 'spd_docs_description', true );
				echo esc_textarea( $post_meta );
			break;
			case 'tags':
				$curent_terms = $doc_post ? get_the_terms( $doc_post->ID, 'post_tag' ) : array();

				if ( ! empty( $curent_terms ) ) {
					foreach ( $curent_terms as $curent_term ) {
						echo '<li>' . esc_attr( $curent_term->name ) . '</li>';
					}
				}

			break;

		}
	}
}

/**
 * [bp_doc_get_filtered_tax_name description]
 *
 * @return [type] [description]
 */
function bp_doc_get_filtered_tax_name() {

	if ( isset( $_GET['filter'] ) ) {

		$filters = wp_unslash( $_GET['filter'] );

		foreach ( $filters as $filter => $value ) {

			$term = get_term( $value, $filter );
			$taxonomy = get_taxonomy( $filter );

			if ( 'Categories' === $taxonomy->labels->name ) {
				$taxonomy->labels->name = '<span class="docs-meta-label">Subjects</span>';
			}

			echo 'Viewing ' . esc_attr( $taxonomy->labels->name ) . ': ' . esc_attr( $term->name );

		}
	}

}

/**
 * [bp_docs_get_subject description]
 *
 * @param  [type] $post_id [description]
 * @return [type]          [description]
 */
function bp_docs_get_subject( $post_id ) {

	$terms = get_the_terms( $post_id, 'category' );
	$out = '';

	if ( ! empty( $terms ) ) {
		$last_key = bp_docs_last_array_key( $terms );
		$out .= '<li><span class="docs-meta-label">Subjects:</span> ';
		foreach ( $terms as $term => $value ) {
			$comma = $last_key === $term ? '' : ', ';
			$out .= '<a href="' . get_site_url() . '/' . BP_DOCS_SLUG . '/' . '?filter[category]=' . $value->term_id . '">' . esc_attr( $value->name ) . '  </a>' . $comma;
		}
		$out .= '</li>';

	}

	echo $out;

}

/**
 * [bp_docs_get_item_type description]
 *
 * @param  [type]  $post_id [description]
 * @param  boolean $echo    [description]
 * @return [type]           [description]
 */
function bp_docs_get_item_type( $post_id, $echo = true ) {

	$terms = get_the_terms( $post_id, 'item_type' );
	$out = '';

	if ( ! empty( $terms ) ) {
		$last_key = bp_docs_last_array_key( $terms );
		$out .= '<li><span class="docs-meta-label">Item Type:</span> ';
		foreach ( $terms as $term => $value ) {
			$comma = $last_key === $term ? '' : ', ';
			$out .= '<a href="' . get_site_url() . '/' . BP_DOCS_SLUG . '/' . '?filter[item_type]=' . $value->term_id . '">' . esc_attr( $value->name ) . '</a>' . $comma;
		}
		$out .= '</li>';

	}
	if ( true === $echo ) {
		echo $out;
	} else {
		return $terms;
	}

}

/**
 * [bp_docs_get_license description]
 *
 * @param  [type]  $post_id [description]
 * @param  boolean $echo    [description]
 * @return [type]           [description]
 */
function bp_docs_get_license( $post_id, $echo = true ) {

	$terms = get_the_terms( $post_id, 'license' );
	$out = '';

	if ( ! empty( $terms ) ) {
		$out .= '<li><span class="docs-meta-label">License:</span> ';
		foreach ( $terms as $term => $value ) {
			$out .= '<a href="' . get_site_url() . '/' . BP_DOCS_SLUG . '/' . '?filter[license]=' . $value->term_id . '">' . esc_attr( $value->name ) . '</a>';
		}
		$out .= '</li>';

	}
	if ( true === $echo ) {
		echo $out;
	} else {
		return $terms;
	}

}

/**
 * [bp_docs_get_item_tags description]
 *
 * @param  [type] $post_id [description]
 * @return [type]          [description]
 */
function bp_docs_get_item_tags( $post_id ) {

	$tags = get_the_tags( $post_id );
	$out = '';

	if ( ! empty( $tags ) ) {
		$last_key = bp_docs_last_array_key( $tags );
		$out .= '<li><span class="docs-meta-label">Tags:</span> ';
		foreach ( $tags as $tag => $value ) {
			$comma = $last_key === $tag ? '' : ', ';
			$out .= '<a href="' . get_site_url() . '/' . BP_DOCS_SLUG . '/' . '?filter[post_tag]=' . $value->term_id . '">' . esc_attr( $value->name ) . '</a>' . $comma;
		}
		$out .= '</li>';

	}

	echo $out;

}

/**
 * [bp_docs_get_date description]
 *
 * @param  [type] $post_id [description]
 * @return [type]          [description]
 */
function bp_docs_get_date( $post_id ) {

	$meta = get_post_meta( $post_id );
	$out = '';

	if ( ! empty( $meta['spd_docs_date'] ) ) {
		$out .= '<li><span class="docs-meta-label">Date:</span> ';
			$out .= '<a href="#">' . esc_attr( $meta['spd_docs_date'][0] ) . '</a>';
		$out .= '</li>';

	}

	echo $out;

}

/**
 * [bp_docs_get_groups description]
 *
 * @return [type] [description]
 */
function bp_docs_get_groups() {

	if ( ! bp_is_group() ) {
		$name = bp_docs_group_name( get_the_ID() );
		if ( ! empty( $name ) ) {
			echo '<li><span class="docs-meta-label">' . esc_attr( 'Sections: ', 'schoolpresser_docs' ) . '</span>' . ' ' . $name . '</li>';
		}
	}

}

/**
 * [bp_docs_type_select_options description]
 *
 * @return [type] [description]
 */
function bp_docs_type_select_options( $context = 'create' ) {

	$terms = get_terms( 'item_type', array(
	    'hide_empty' => false,
	) );

	if ( 'edit' === $context ) {

		$doc_post = get_page_by_path( bp_current_action(), OBJECT, 'schoolpresser_docs' );

		$curent_terms = $doc_post ? get_the_terms( $doc_post->ID, 'item_type' ) : array();
		$curent_terms_id = array();

		foreach ( $curent_terms as $curent_term ) {
			$curent_terms_id[] = $curent_term->term_id;
		}
	}

	foreach ( $terms as $term ) {
		$selected = isset( $curent_terms_id ) && in_array( $term->term_id, $curent_terms_id, true ) ? 'checked="checked"' : '';
		// echo '<option value="' . esc_attr( $term->term_id ) . '" ' . $selected . '>' . esc_attr( $term->name ) . '</option>';
		echo '<div class="type-check"><input type="checkbox" id="doc-type" name="doc-type[]" value="' . esc_attr( $term->term_id ) . '" ' . $selected . '/><span class="group-name">' . esc_attr( $term->name ) . '</span></div>';
	}

}

/**
 * [bp_docs_license_select_options description]
 *
 * @param  string $context [description]
 * @return [type]          [description]
 */
function bp_docs_license_select_options( $context = 'create' ) {
	$terms = get_terms( 'license', array(
		'hide_empty' => false,
	) );

	if ( 'edit' === $context ) {
		$doc_post = get_page_by_path( bp_current_action(), OBJECT, 'schoolpresser_docs' );

		$curent_terms = $doc_post ? get_the_terms( $doc_post->ID, 'license' ) : array();
		$curent_terms_id = array();

		foreach ( $curent_terms as $curent_term ) {
			$curent_terms_id[] = $curent_term->term_id;
		}
	}

	foreach ( $terms as $term ) {
		$selected = isset( $curent_terms_id ) && in_array( $term->term_id, $curent_terms_id, true ) ? 'selected="selected"' : '';
		echo '<option value="' . esc_attr( $term->term_id ) . '" ' . $selected . '>' . esc_attr( $term->name ) . '</option>';
	}
}

/**
 * [bp_docs_subject_select_options description]
 *
 * @param  string $context [description]
 * @return [type]          [description]
 */
function bp_docs_subject_select_options( $context = 'create' ) {

	$terms = get_terms( 'category', array(
	    'hide_empty' => false,
	) );

	if ( 'edit' === $context ) {
		$doc_post = get_page_by_path( bp_current_action(), OBJECT, 'schoolpresser_docs' );

		$curent_terms = $doc_post ? get_the_terms( $doc_post->ID, 'category' ) : array();
		$curent_terms_id = array();

		foreach ( $curent_terms as $curent_term ) {
			$curent_terms_id[] = $curent_term->term_id;
		}
	}

	foreach ( $terms as $term ) {
		$selected = isset( $curent_terms_id ) && in_array( $term->term_id, $curent_terms_id ) ? 'checked="checked"' : '';
		// echo '<option value="' . esc_attr( $term->term_id ) . '" ' . $selected . '>' . esc_attr( $term->name ) . '</option>';
		echo '<div class="subject-check"><input type="checkbox" id="doc-subjects" name="doc-subjects[]" value="' . esc_attr( $term->term_id ) . '" ' . $selected . '/><span class="group-name">' . esc_attr( $term->name ) . '</span></div>';
	}

}

/**
 * [bp_docs_permission_select_options description]
 *
 * @return [type] [description]
 */
function bp_docs_permission_select_options( $context = 'create' ) {

	$options = array(
		array(
			'id' => 'private',
			'name' => 'Private',
			'description' => '  documents will ONLY be available if a logged in user is member of the group it was uploaded to.',
		),
		array(
			'id' => 'publish',
			'name' => 'Public',
			'description' => '  documents will be available to any logged in user even if they are not a member of the group.',
		),
		array(
			'id' => 'teased',
			'name' => 'Teased',
			'description' => '  members can see titles to documents but can’t access them unless they are a member of the group it was uploaded to.',
		),

	);

	if ( 'edit' === $context ) {
		$doc_post = get_page_by_path( bp_current_action(), OBJECT, 'schoolpresser_docs' );

		if ( $doc_post ) {
			$doc_post_meta = get_post_meta( $doc_post->ID, 'spd_docs_permission', true );
		}
	}

	foreach ( $options as $option ) {
		$selected = isset( $doc_post_meta ) && $doc_post_meta === $option['id'] || ! isset( $doc_post_meta ) && 'private' === $option['id'] ? 'checked="checked"' : '';
		echo '<input type="radio" name="doc-permission" value="' . esc_attr( $option['id'] ) . '" ' . $selected . '><label class="perm" for="' . esc_attr( $option['name'] ) . '">' . esc_attr( $option['name'] ) . ':<span>' . esc_attr( $option['description'] ) . '</span></label>';
	}

}

/**
 * [bp_docs_id description]
 *
 * @return [type] [description]
 */
function bp_docs_id() {

	$doc_post = get_page_by_path( bp_current_action(), OBJECT, 'schoolpresser_docs' );

	if ( $doc_post ) {
		return $doc_post->ID;
	} else {
		return 0;
	}

}

/**
 * [bp_docs_author description]
 *
 * @return [type] [description]
 */
function bp_docs_author() {

	$doc_post = get_page_by_path( bp_current_action(), OBJECT, 'schoolpresser_docs' );

	if ( $doc_post ) {
		return $doc_post->post_author;
	} else {
		return 0;
	}
}

/**
 * [bp_docs_is_teased description]
 *
 * @param  [type] $post_id [description]
 * @return boolean          [description]
 */
function bp_docs_is_teased( $post_id ) {

	$doc_post_meta = get_post_meta( $post_id, 'spd_docs_permission', true );

	if ( 'teased' !== $doc_post_meta ) {
		return false;
	}
	return true;
}

/**
 * [bp_docs_is_public description]
 *
 * @param  [type] $post_id [description]
 * @return boolean          [description]
 */
function bp_docs_is_public( $post_id ) {

	$doc_post_meta = get_post_meta( $post_id, 'spd_docs_permission', true );

	if ( 'public' !== $doc_post_meta ) {
		return false;
	}
	return true;
}

/**
 * [bp_docs_is_private description]
 *
 * @param  [type] $post_id [description]
 * @return boolean          [description]
 */
function bp_docs_is_private( $post_id ) {

	$doc_post_meta = get_post_meta( $post_id, 'spd_docs_permission', true );

	if ( 'private' !== $doc_post_meta ) {
		return false;
	}
	return true;
}

function bp_docs_can_view_meta( $post_id ) {

	$doc_post_meta = get_post_meta( $post_id, 'spd_docs_permission', true );
	$post_author_id = (int) get_post_field( 'post_author', $post_id );

	if ( bp_loggedin_user_id() === $post_author_id ) {
		return true;
	} elseif ( 'public' !== $doc_post_meta ) {
		return true;
	}

	return false;

}

/**
 * [bp_docs_is_group_checked description]
 *
 * @param  string  $context  [description]
 * @param  integer $group_id [description]
 * @return boolean           [description]
 */
function bp_docs_is_group_checked( $context = 'create', $group_id = 0 ) {

	if ( 'edit' === $context ) {
		$doc_post = get_page_by_path( bp_current_action(), OBJECT, 'schoolpresser_docs' );

		if ( $doc_post ) {
			$doc_post_meta = get_post_meta( $doc_post->ID, 'spd_docs_groups', true );
			$selected = isset( $doc_post_meta ) && in_array( (string) $group_id, (array) $doc_post_meta, true ) ? 'checked="checked"' : 'xxxx';
		}

		return $selected;
	}
	return '';
}

/**
 * [bp_docs_does_member_have_group description]
 *
 * @return [type] [description]
 */
function bp_docs_does_member_have_group() {

	if ( bp_is_active( 'groups' ) && class_exists( 'BP_Groups_Member' ) ) {
		$user_id = bp_loggedin_user_id();
		$groups = BP_Groups_Member::get_group_ids( $user_id );

		if ( ! empty( $groups ) ) {
			return true;
		}
	}

	return false;

}

/**
 * Helper function to get last key in array.
 *
 * @param  array $array
 * @return string|integer
 */
function bp_docs_last_array_key( $array ) {
	return key( array_slice( $array, -1, 1, true ) );
}

/**
 * [bp_docs_delete_doc description]
 *
 * @return void
 */
function bp_docs_delete_doc() {

	if ( isset( $_GET['document-delete'] ) ) {

		$post_id = (int) $_GET['document-delete'];
		$post_author_id = get_post_field( 'post_author', $post_id );

		if ( get_post_status( $post_id ) && bp_docs_user_can_delete( $post_author_id ) ) {

			$postobj = wp_trash_post( $post_id );

			if ( $postobj ) {
				wp_safe_redirect( get_site_url() . '/' . BP_DOCS_SLUG . '/' );
			}
		} else {
			wp_safe_redirect( get_site_url() . '/' . BP_DOCS_SLUG . '/' );
		}
	}
}
add_action( 'init', 'bp_docs_delete_doc' );

/**
 * [bp_docs_single_doc_access description]
 *
 * @return [type] [description]
 */
function bp_docs_single_doc_access() {

	if ( bp_is_group() || bp_is_user() ) {
		return;
	}

	$component = bp_current_component();
	$action = bp_current_action();
	$doc_post = get_page_by_path( $action, OBJECT, 'schoolpresser_docs' );

	if ( BP_DOCS_SLUG === $component && ! empty( $action ) && bp_docs_is_private( $doc_post->ID ) && (int) bp_loggedin_user_id() !== (int) $doc_post->post_author ) {
		bp_core_redirect( get_site_url() . '/' . BP_DOCS_SLUG . '/' );
	}
}
// add_action( 'init', 'bp_docs_single_doc_access' );
/**
 * [bp_docs_delete_link description]
 *
 * @return [type] [description]
 */
function bp_docs_delete_link() {
	echo esc_url( get_site_url() . '/' . BP_DOCS_SLUG . '/?document-delete=' . bp_docs_id() );
}

/**
 * Block certain activity types from being added
 *
 * @param  [type] $activity_object [description]
 * @return [type]                  [description]
 */
function bp_docs_activity_dont_save( $activity_object ) {
	$exclude = array(
		'new_document',
	);
	// if the activity type is empty, it stops BuddyPress BP_Activity_Activity::save() function.
	if ( in_array( $activity_object->type, $exclude, true ) ) {
		$activity_object->type = false;
	}
}
add_action( 'bp_activity_before_save', 'bp_docs_activity_dont_save', 10, 1 );

/**
 * [bp_docs_custom_post_status description]
 *
 * @return [type] [description]
 */
function bp_docs_custom_post_status() {
	register_post_status( 'teased', array(
		'label'                     => _x( 'Teased', 'post' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Unread <span class="count">(%s)</span>', 'Teased <span class="count">(%s)</span>' ),
	) );
}
add_action( 'init', 'bp_docs_custom_post_status' );

/**
 * [bp_docs_sanitize_set_status description]
 *
 * @param [type] $value      [description]
 * @param [type] $field_args [description]
 * @param [type] $field      [description]
 */
function bp_docs_sanitize_set_status( $value, $field_args, $field ) {

	global $wpdb;

	$post_id = $field->data_to_save['post_ID'];
	$post_status = sanitize_text_field( wp_unslash( $value ) );

	$wpdb->query(
	    $wpdb->prepare(
	        "UPDATE $wpdb->posts SET post_status = %s WHERE ID = %d",
	        $post_status, $post_id
	    )
	);

	return $value;
}

/**
 * Sets doc tab active class
 *
 * @param  string $tab
 * @return void
 */
function bp_docs_is_selected( $tab = '' ) {

	$component = bp_current_component();
	$action = bp_current_action();

	if ( BP_DOCS_SLUG === $component && 'all' === $tab  && ! $action && ! isset( $_GET['document_filter'] ) ) {
		echo 'selected';
	}

	if ( BP_DOCS_SLUG === $component && 'personal' === $tab && isset( $_GET['document_filter'] ) && $tab === $_GET['document_filter'] ||
			BP_DOCS_SLUG === $component && 'groups' === $tab && isset( $_GET['document_filter'] ) && $tab === $_GET['document_filter'] ) {
		echo 'selected';
	}

}
add_action( 'init', 'bp_docs_is_selected' );

/**
 * add activity for each document published
 *
 * @param  string $new_status
 * @param  string $old_status
 * @param  object $post
 * @return void
 */
function bp_doc_new_post( $new_status, $old_status, $post ) {
	if ( 'publish' === $new_status && 'publish' !== $old_status && 'schoolpresser_docs' === $post->post_type ) {
		bp_doc_add_activity( $_POST, $post );
	}
}
add_action( 'transition_post_status', 'bp_doc_new_post', 10, 3 );

/**
 * Add activity item for schoolpresser_doc cpt
 *
 * @param array  $post
 * @param object $post_object
 * @return void
 */
function bp_doc_add_activity( $post, $post_object ) {

	if ( ! isset( $post['spd_docs_attachment_id'] ) || empty( $post['spd_docs_attachment_id'] ) ) {
		return;
	}

	$user_id = isset( $post['user_ID'] ) ? sanitize_text_field( $post['user_ID'] ) : 0;
	$item_id = isset( $post['ID'] ) ? sanitize_text_field( $post['ID'] ) : 0;
	$description = isset( $post['spd_docs_description'] ) ? sanitize_text_field( substr( $post['spd_docs_description'], 0, 200 ) ) : '';
	$group_ids = isset( $post['spd_docs_groups'] ) ? $post['spd_docs_groups'] : null;
	$groups = '';

	if ( ! empty( $group_ids ) ) {
		foreach ( $group_ids as $group_id ) {
			$group = groups_get_group( array( 'group_id' => sanitize_text_field( $group_id ) ) );
			$group_permalink = trailingslashit( bp_get_root_domain() . '/' . bp_get_groups_root_slug() . '/' . $group->slug . '/' );
			$group_link = '<a href="' . $group_permalink . '">' . $group->name . '</a>';
			$groups .= $group_link . ', ';
		}
	}

	$ellipsis = mb_strlen( $description ) >= 190 ? '...' : '';

	$doc_title = '<a href="' . trailingslashit( bp_get_root_domain() . '/documents/' . $post_object->post_name ) . '">' . $post_object->post_title . '</a>';

	$args = array(
		'action' => bp_core_get_userlink( $user_id ) . ' added the document ' . $doc_title . ' to ' . $groups,
		'content' => $description . $ellipsis,
		'type' => 'new_doc',
		'component' => 'documents',
		'secondary_item_id' => $item_id,
		'user_id' => $user_id,
	);

	bp_activity_add( $args );

	// if ( ! empty( $group_ids ) ) {
	// foreach ( $group_ids as $group ) {
	//
	// $args['action'] = bp_core_get_userlink( $user_id ) . ' added the document ' . $doc_title;
	// $args['item_id'] = $group;
	// $args['component'] = 'groups';
	//
	// bp_activity_add( $args );
	//
	// }
	// }
}

/**
 * Bulk add to group action options
 *
 * @param  [type] $bulk_actions [description]
 * @return [type]               [description]
 */
function bp_documents_bulk_actions( $bulk_actions ) {
	$bulk_actions['group-doc-add'] = __( 'Add to Group', 'apsa-sync' );
	$bulk_actions['group-doc-remove'] = __( 'Remove from Group', 'apsa-sync' );
	return $bulk_actions;
}
add_filter( 'bulk_actions-edit-schoolpresser_docs', 'bp_documents_bulk_actions' );

/**
 * [bp_activate_filter description]
 */
function bp_documents_group_select() {

	if ( isset( $_GET['post_type'] ) && 'schoolpresser_docs' === $_GET['post_type'] ) {

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

		<script>
			jQuery(document).ready(function() {
				jQuery('.selectBox').click( function () {
					jQuery('#checkboxes').toggle();
				});
			});
		</script>
		<?php

	}
}
add_action( 'restrict_manage_posts', 'bp_documents_group_select' );

/**
 * Bulk Add or remove document from group action handler
 *
 * @param  [type] $redirect_to [description]
 * @param  [type] $doaction    [description]
 * @param  [type] $doc_ids     [description]
 * @return [type]              [description]
 */
function bp_documents_group_bulk_action_handler( $redirect_to, $doaction, $doc_ids ) {

	global $wpdb;

	$group_ids = isset( $_GET['group-select'] ) ? $_GET['group-select'] : array();

	if ( ! empty( $group_ids ) ) {

		if ( 'group-doc-add' === $doaction ) {

			foreach ( $doc_ids as $doc_id ) {
				$meta = get_post_meta( $doc_id , 'spd_docs_groups', true );

				if ( is_array( $meta ) ) {
					$c = array_merge( $meta, $group_ids );
					$d = array_unique( $c, SORT_REGULAR );
					update_post_meta( $doc_id , 'spd_docs_groups', $d );
				}
			}
		}

		if ( 'group-doc-remove' === $doaction ) {

			foreach ( $doc_ids as $doc_id ) {
				$meta = get_post_meta( $doc_id , 'spd_docs_groups', true );

				if ( is_array( $meta ) ) {
					$c = array_diff( $meta, $group_ids );
					update_post_meta( $doc_id , 'spd_docs_groups', $c );
				}
			}
		}

		$redirect_to = add_query_arg( 'bulk_add_docs', count( $doc_ids ), $redirect_to );

	}

	return $redirect_to;
}
add_filter( 'handle_bulk_actions-edit-schoolpresser_docs', 'bp_documents_group_bulk_action_handler', 10, 3 );

/**
 * [my_bulk_action_admin_notice description]
 * @return [type] [description]
 */
function bp_docs_add_action_admin_notice() {

	if ( ! empty( $_REQUEST['bulk_add_docs'] ) ) {
		$activated_count = intval( $_REQUEST['bulk_add_docs'] );
		printf( '<div id="message" class="updated fade"><p>' .
		_n( 'Updated %s document(s).',
		'Updated %s document(s).',
		$activated_count,
		'bulk_add_docs'
		) . '</p></div>', $activated_count );
      }
}
add_action( 'admin_notices', 'bp_docs_add_action_admin_notice' );
