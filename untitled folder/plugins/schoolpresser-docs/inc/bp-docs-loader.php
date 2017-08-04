<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Main Document Class.
 */
class BP_Documents_Component extends BP_Component {

	/**
	 * Start the document component setup process.
	 */
	public function __construct() {

		$bp = buddypress();

		parent::start(
			'documents',
			__( 'Documents', 'buddypress' ),
			schoolpresser_docs()->dir() . '/inc',
			array(
				'adminbar_myaccount_order' => 10,
			)
		);

		$bp->active_components[ $this->id ] = '1';

		$this->includes();
	}

	/**
	 * Include component files.
	 *
	 * @see BP_Component::includes() for a description of arguments.
	 *
	 * @param array $includes See BP_Component::includes() for a description.
	 */
	public function includes( $includes = array() ) {
		// Files to include.
		$includes = array(
			'template',
			'screens',
			'functions',
		);

		parent::includes( $includes );
	}

	/**
	 * Set up component global variables.
	 *
	 * @see BP_Component::setup_globals() for a description of arguments.
	 *
	 * @param array $args See BP_Component::setup_globals() for a description.
	 */
	public function setup_globals( $args = array() ) {
		$bp = buddypress();

		// Define a slug, if necessary.
		if ( ! defined( 'BP_DOCS_SLUG' ) ) {
			define( 'BP_DOCS_SLUG', $this->id );
		}

		$default_directory_title  = $this->id;

		// All globals for docs component.
		// Note that global_tables is included in this array.
		$args = array(
			'slug'                  => BP_DOCS_SLUG,
			'root_slug'             => isset( $bp->pages->documents->slug ) ? $bp->pages->documents->slug : BP_DOCS_SLUG,
			'has_directory'         => true,
			'directory_title'       => isset( $bp->pages->documents->title ) ? $bp->pages->documents->title : $default_directory_title,
			'notification_callback' => 'bp_docs_format_notifications',
			'search_string'         => __( 'Search Documents...', 'buddypress' ),
		);

		parent::setup_globals( $args );
	}

	/**
	 * Set up component navigation.
	 *
	 * @param array $main_nav Optional. See BP_Component::setup_nav() for description.
	 * @param array $sub_nav  Optional. See BP_Component::setup_nav() for description.
	 */
	public function setup_nav( $main_nav = array(), $sub_nav = array() ) {

		// Add 'Documents' to the main navigation.
		$main_nav = array(
			'name'                => _x( 'Documents', 'Profile docs screen nav', 'schoolpresser-docs' ),
			'slug'                => $this->slug,
			'position'            => 10,
			'screen_function'     => 'bp_docs_screen_user_docs',
			'default_subnav_slug' => 'documents',
			'item_css_id'         => $this->id,
		);

		parent::setup_nav( $main_nav, $sub_nav );
	}

	/**
	 * Set up the component entries in the WordPress Admin Bar.
	 *
	 * @param array $wp_admin_nav See BP_Component::setup_admin_bar() for a
	 *                            description.
	 */
	public function setup_admin_bar( $wp_admin_nav = array() ) {
		parent::setup_admin_bar( $wp_admin_nav );
	}


}

/**
 * Bootstrap the Document component.
 */
function bp_setup_documents() {
	$bp = buddypress();
	$bp->documents = new BP_Documents_Component();
}
add_action( 'bp_loaded', 'bp_setup_documents' );
