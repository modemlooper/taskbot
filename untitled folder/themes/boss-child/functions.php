<?php
/**
 * @package Boss Child Theme
 * The parent theme functions are located at /boss/buddyboss-inc/theme-functions.php
 * Add your own functions in this file...
 */

/**
 * Sets up theme defaults
 *
 * @since Boss Child Theme 1.0.0
 */
function boss_child_theme_setup() {
	/**
	* Makes child theme available for translation.
	* Translations can be added into the /languages/ directory.
	* Read more at: http://www.buddyboss.com/tutorials/language-translations/
	*/

	// Translate text from the PARENT theme.
	load_theme_textdomain( 'boss', get_stylesheet_directory() . '/languages' );

	// Translate text from the CHILD theme only.
	// Change 'boss' instances in all child theme files to 'boss_child_theme'.
	// load_theme_textdomain( 'boss_child_theme', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'boss_child_theme_setup' );

/**
 * Enqueues scripts and styles for child theme front-end.
 *
 * @since Boss Child Theme  1.0.0
 */
function boss_child_theme_scripts_styles() {
	/**
	* Scripts and Styles loaded by the parent theme can be unloaded if needed
	* using wp_deregister_script or wp_deregister_style.
	*
	* See the WordPress Codex for more information about those functions:
	* http://codex.wordpress.org/Function_Reference/wp_deregister_script
	* http://codex.wordpress.org/Function_Reference/wp_deregister_style
	*/

	/*
	* Styles
	*/
	wp_enqueue_style( 'boss-child-custom', get_stylesheet_directory_uri() . '/css/custom.css' );
}
add_action( 'wp_enqueue_scripts', 'boss_child_theme_scripts_styles', 9999 );


/****************************** CUSTOM FUNCTIONS ******************************/

/**
 * Apsa_page_title custom function to filter buddyboss page title.
 *
 * @return void
 */
function apsa_page_title() {
	echo esc_attr( apply_filters( 'apsa_page_title', buddyboss_get_page_title() ) );
}

/**
 * Apsa_filter_page_title_group_type filter title for group type.
 *
 * @param  string $title
 * @return string
 */
function apsa_filter_page_title_group_type( $title = '' ) {

	$component = bp_current_component();
	$action = bp_current_action();
	$action_vars = bp_action_variables();

	if ( bp_get_groups_group_type_base() !== $action || 'create' !== $action && empty( $action_vars ) && ! bp_is_group() ) {
		return $title;
	}

	$type = isset( $action_vars[0] ) ? $action_vars[0] : 'groups';

	return ucfirst( substr( $type, 0, -1 ) );

}
add_filter( 'apsa_page_title', 'apsa_filter_page_title_group_type' );

/**
 * Echos the current group type
 *
 * @param  integer $group_id
 * @return void
 */
function apsa_group_type( $group_id = 0 ) {

	$group_type = apsa_get_group_type( $group_id );

	echo esc_attr( $group_type );

}

/**
 * Returns the current group type
 *
 * @param  integer $group_id
 * @return string
 */
function apsa_get_group_type( $group_id = 0 ) {

	$group_type = bp_groups_get_group_type( bp_get_group_id() );

	$group_type = $group_type ? $group_type : 'group';

	return ucfirst( $group_type );

}

/**
 * Returns total group count for group type
 *
 * @param  string  $type
 * @param  integer $user_id
 * @param  boolean $hidden
 * @return mixed
 */
function apsa_get_total_group_type_count( $type = '', $user_id = 0, $hidden = true ) {

	$args = array(
		'group_type' => $type,
		'show_hidden' => $hidden,
	);

	if ( 0 !== $user_id ) {
		$args['user_id'] = $user_id;
	}

	$groups = groups_get_groups( $args );

	if ( isset( $groups['total'] ) ) {
		return $groups['total'];
	}

	return;

}

/**
 * Filters total group count
 *
 * @TODO cache this
 * @param  integer $count
 * @return string
 */
function apsa_filter_total_group_type_count( $count = 0 ) {

	if ( bp_get_groups_group_type_base() === bp_current_action() && bp_is_directory() ) {
		$type = substr( bp_action_variable(), 0, -1 );
		if ( $type_count = apsa_get_total_group_type_count( $type ) ) {
			return $type_count;
		}
	}

	return $count;

}
add_filter( 'bp_get_total_group_count', 'apsa_filter_total_group_type_count' );

/**
 * Filters the my groups tab for type directory
 *
 * @TODO cache this
 * @param  integer $count
 * @return string
 */
function apsa_filter_total_group_type_count_user( $count = 0 ) {

	if ( bp_get_groups_group_type_base() === bp_current_action() && bp_is_directory() ) {
		$type = substr( bp_action_variable(), 0, -1 );
		if ( $type_count = apsa_get_total_group_type_count( $type, bp_loggedin_user_id() ) ) {
			return $type_count;
		}
	}

	return $count;

}
add_filter( 'bp_get_total_group_count_for_user', 'apsa_filter_total_group_type_count_user' );

/**
 * Returns total group count for all groups are when on /type/group-type slug
 *
 * @return string
 */
function apsa_groups_get_total_group_count() {

	if ( bp_get_groups_group_type_base() === bp_current_action() && bp_is_directory() ) {
		$type = substr( bp_action_variable(), 0, -1 );
		return apsa_get_total_group_type_count( $type );
	}

	return groups_get_total_group_count();

}

/**
 * Directory permalink for group type directory
 *
 * @return void
 */
function apsa_groups_directory_permalink() {
	if ( bp_get_groups_group_type_base() === bp_current_action() && bp_is_directory() ) {
		echo trailingslashit( bp_get_root_domain() . '/' . bp_get_groups_root_slug() . '/' . bp_get_groups_group_type_base() . '/' . bp_action_variable() );
	}
}

/**
 * Calculats total count of docs
 *
 * @return integer
 */
function apsa_get_total_doc_count() {
	$counts = wp_count_posts( 'schoolpresser_docs', 'readable' );
	$amount = 0;

	foreach ( $counts as $count ) {
		$amount = $amount + (int) $count;
	}
	return $amount;
}

/**
 * Calculats total count of docs
 *
 * @return integer
 */
function apsa_get_total_user_doc_count( $user_id = 0 ) {

	$query = array(
		'post_type'      => 'schoolpresser_docs',
		'author' => $user_id,
		'posts_per_page' => -1,
	);

	$posts = new WP_Query( $query );
	return $posts->found_posts;
}

/**
 * Filter the login link to custom page.
 *
 * @param  string $login_url
 * @param  string $redirect
 * @param  bolean $force_reauth
 * @return string
 */
function apsa_custom_login_page( $login_url, $redirect, $force_reauth ) {
	if ( ! is_user_logged_in() ) {
		return home_url( '/login/' );
	}
	return $login_url;
}
add_filter( 'login_url', 'apsa_custom_login_page', 10, 3 );

/**
 * Turn TinyMCE back on for BP Email.
 */
function bbp_enable_visual_editor( $args = array() ) {
	$args['tinymce'] = true;
	return $args;
}
add_filter( 'bbp_after_get_the_content_parse_args', 'bbp_enable_visual_editor' );

/**
 * Custom widget area for homepage.
 */
function apsa_custom_widget_areas() {
	if ( function_exists( 'register_sidebar' ) ) {
		register_sidebar(array(
			'name' => 'Homepage Notice',
			'id' => 'homepage-notice',
			'before_widget' => '<div class = "widgetizedArea">',
			'after_widget' => '</div>',
			'before_title' => '<h3>',
			'after_title' => '</h3>',
			)
		);
	}
}
add_action( 'init', 'apsa_custom_widget_areas' );

/**
 * Remove the 'Private:' flag on private forums on the front end.
 */
function apsa_remove_private_prefix_for_forums( $prefix, $post ) {
	if ( ! function_exists( 'bbp_get_forum_post_type' ) ) {
		return $prefix;
	}

	if ( $post->post_type !== bbp_get_forum_post_type() ) {
		return $prefix;
	}

	return '%s';
}
add_filter( 'private_title_format', 'apsa_remove_private_prefix_for_forums', 10, 2 );
add_filter( 'protected_title_format', 'apsa_remove_private_prefix_for_forums', 10, 2 );

/**
 * Returns count of all forum topics
 *
 * @return integer
 */
function apsa_get_total_topic_count() {

	if ( false === ( $count = get_transient( 'all_topic_count' ) ) ) {
		$args = array( 'post_type' => 'topic' );
		$the_query = new WP_Query( $args );
		$count = $the_query->found_posts;
	  	set_transient( 'all_topic_count', $count, 5 * MINUTE_IN_SECONDS );
	}

	return $count;

}

/**
 * Returns group document count
 *
 * @param  string $group_slug
 * @return integer
 */
function apsa_get_group_doc_count( $group_slug = '' ) {

	$group_id = BP_Groups_Group::group_exists( $group_slug );

	$query = array(
		'post_type' => 'schoolpresser_docs',
		'post_status' => 'any',
		'posts_per_page' => -1,
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key'     => 'spd_docs_groups',
				'value'	  => serialize( strval( $group_id ) ),
				'compare' => 'LIKE',
			),
		),
	);

	$posts = new WP_Query( $query );
	return $posts->found_posts;

}

/**
 * Returns group forum topic count
 *
 * @param  string $group_slug
 * @return integer
 */
function apsa_get_group_topic_count( $group_slug = '' ) {

	$group_id = BP_Groups_Group::group_exists( $group_slug );
	$forum_id = groups_get_groupmeta( $group_id, 'forum_id' );
	$topic_count = bbp_get_forum_topic_count( $forum_id[0] );

	return $topic_count;

}

/**
 * Filter sidebar recent members args
 *
 * @param  array $args
 * @return array
 */
function apsa_filter_recent_members_args( $args ) {

	return $args;

}
add_action( 'recent_members_filter_args', 'apsa_filter_recent_members_args' );
