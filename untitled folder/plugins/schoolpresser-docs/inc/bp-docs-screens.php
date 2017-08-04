<?php

/** Theme Compatibility *******************************************************/

/**
 * The main theme compat class for SchoolPresser Docs.
 *
 * This class sets up the necessary theme compatibility actions to safely output
 * docs template parts to the_title and the_content areas of a theme.
 *
 * @since BuddyPress (1.7.0)
 */
class BPD_Theme_Compat {


	/**
	 * __construct function.
	 *
	 * @access public
	 */
	public function __construct() {

		$this->setup_actions();

	}


	/**
	 * The setup_actions function.
	 *
	 * @access public
	 * @return void
	 */
	public function setup_actions() {

		// Set page as a directory, flag it true.
		add_action( 'bp_screens', array( $this, 'docs_screen_index' ) );

		// Hook bp_setup_theme_compat and swap post data with template.
		add_action( 'bp_setup_theme_compat', array( $this, 'is_docs' ) );

	}


	/**
	 * The docs_screen_index function.
	 *
	 * @access public
	 */
	public function docs_screen_index() {
	    // Check if on docs directory.
	    if ( ! bp_displayed_user_id() && bp_is_current_component( 'documents' ) && ! bp_current_action() ) {

	        bp_update_is_directory( true, 'documents' );
	        bp_core_load_template( apply_filters( 'docs_screen_index', 'documents/directory-index' ) );

	    }
	}


	/**
	 * The template_hierarchy function.
	 *
	 * @access public
	 * @param mixed $templates Templates array.
	 * @return array $templates Array of custom templates.
	 */
	public function template_hierarchy( $templates ) {
	    // If on a page of  plugin, then we add our path to the template path array.
	    if ( bp_is_current_component( 'documents' ) ) {

	        $templates[] = schoolpresser_docs()->dir() . '/inc/templates';
	    }

	    return $templates;
	}




	/**
	 * The is_docs function.
	 *
	 * @access public
	 */
	public function is_docs() {

		if ( ! bp_current_action() && ! bp_displayed_user_id() && bp_is_current_component( 'documents' ) ) {

			do_action( 'bp_docs_screen_index' );

			// Add plugin path to template stack.
			add_filter( 'bp_get_template_stack', array( $this, 'template_hierarchy' ), 10, 1 );
			// First we reset the post.
			add_action( 'bp_template_include_reset_dummy_post_data', array( $this, 'directory_dummy_post' ) );
			// Then we filter 'the_content'.
			add_filter( 'bp_replace_the_content', array( $this, 'directory_content' ) );

		}
	}



	/**
	 * The directory_dummy_post function.
	 *
	 * Update the global $post with directory data
	 *
	 * @access public
	 */
	public function directory_dummy_post() {

		bp_theme_compat_reset_post( array(
			'ID'             => 0,
			'post_title'     => 'Documents',
			'post_author'    => 0,
			'post_date'      => 0,
			'post_content'   => '',
			'post_type'      => 'schoolpresser_docs',
			'post_status'    => 'publish',
			'is_archive'     => true,
			'comment_status' => 'closed',
		) );
	}


	/**
	 * The directory_content function.
	 *
	 * @access public
	 */
	public function directory_content() {
		bp_buffer_template_part( 'documents/directory-index' );
	}

}

new BPD_Theme_Compat();


/**
 * The bp_docs_get_templates_dir function.
 *
 * @access public
 */
function bp_docs_get_templates_dir() {
	return schoolpresser_docs()->dir() . 'inc/templates/documents';
}


/**
 * The bp_docs_get_template_part function.
 *
 * @access public
 * @param mixed $slug Template slug.
 * @param mixed $name Default: null.
 * @param bool  $load Default: true.
 * @return string
 */
function bp_docs_get_template_part( $slug, $name = null, $load = true ) {
	// Execute code for this part.
	do_action( 'get_template_part_' . $slug, $slug, $name );

	// Setup possible parts.
	$templates = array();
	if ( isset( $name ) ) {
		$templates[] = $slug . '-' . $name . '.php';
	}
	$templates[] = $slug . '.php';

	// Allow template parts to be filtered.
	$templates = apply_filters( 'bp_docs_get_template_part', $templates, $slug, $name );

	// Return the part that is found.
	return bp_docs_locate_template( $templates, $load, false );
}


/**
 * The bp_docs_locate_template function.
 *
 * @access public
 * @param mixed $template_names Template names.
 * @param bool  $load           Default: false.
 * @param bool  $require_once   Default: true.
 * @return string
 */
function bp_docs_locate_template( $template_names, $load = false, $require_once = true ) {
	// No file found yet.
	$located = false;

	// Try to find a template file.
	foreach ( (array) $template_names as $template_name ) {

		// Continue if template is empty.
		if ( empty( $template_name ) ) {
			continue;
		}

		// Trim off any slashes from the template name.
		$template_name = ltrim( $template_name, '/' );

		// Check child theme first.
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . 'documents/' . $template_name ) ) {
			$located = trailingslashit( get_stylesheet_directory() ) . 'documents/' . $template_name;
			break;

			// Check parent theme next.
		} elseif ( file_exists( trailingslashit( get_template_directory() ) . 'documents/' . $template_name ) ) {
			$located = trailingslashit( get_template_directory() ) . 'documents/' . $template_name;
			break;

			// Check theme compatibility last.
		} elseif ( file_exists( trailingslashit( bp_docs_get_templates_dir() ) . $template_name ) ) {
			$located = trailingslashit( bp_docs_get_templates_dir() ) . $template_name;
			break;
		}
	}

	if ( ( true === $load ) && ! empty( $located ) ) {
		load_template( $located, $require_once );
	}

	return $located;
}


/**
 * The bp_docs_screen_my_docs function.
 *
 * @access public
 * @return void
 */
function bp_docs_screen_user_docs() {
	$bp = buddypress();

	do_action( 'bp_docs_screen_user_docs' );

	add_action( 'bp_template_content', 'bp_docs_gallery_content' );

	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}



/**
 * The bp_docs_gallery_content function.
 *
 * @access public
 * @return void
 */
function bp_docs_gallery_content() {

	do_action( 'bp_docs_gallery_content' );

	$action_var = bp_action_variables();

	if ( is_user_logged_in() && bp_loggedin_user_id() === bp_displayed_user_id() ) :
	?>
	<header class="activity-header">
		<?php if ( bp_docs_can_upload() ) : ?>
			<a href="<?php bp_docs_directory_link(); ?>?add=true" class="button right"><?php _e( 'Upload Document', 'schoolpresser-docs' ); ?></a>
		<?php endif ; ?>
	</header><!-- .page-header -->
	<?php
	endif;

	bp_docs_get_template_part( 'single/home' );

}
