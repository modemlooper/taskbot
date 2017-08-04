<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AppBuddy_Ajax class.
 */
class BPD_CPT {

	/**
	 * __construct function.
	 *
	 * @access public
	 */
	public function __construct() {}

	public static function instance() {

		// Store the instance locally to avoid private static replication.
		static $instance = null;

		// Only run these methods if they haven't been run previously.
		if ( null === $instance ) {
			$instance = new BPD_CPT;
			$instance->setup_actions();
		}

		// Always return the instance.
		return $instance;

	}


	/**
	 * Setup_actions function.
	 *
	 * @access private
	 */
	private function setup_actions() {

		add_action( 'init', array( $this, 'bp_docs_post_type' ), 0 );
		add_action( 'admin_init', array( $this, 'add_columns' ) );
		//add_action( 'init', array( $this, 'customize_docs_tracking_args' ), 1000 );
		add_action( 'cmb2_admin_init', array( $this, 'cmb2_metaboxes' ) );

		add_action( 'init', array( $this, 'item_type_taxonomy' ), 0 );
		add_action( 'init', array( $this, 'license_taxonomy' ), 0 );

	}


	/**
	 * Checkin_post_type function.
	 *
	 * @access public
	 */
	public function bp_docs_post_type() {

		$labels = array(
			'name'                => _x( 'Documents', 'Documents', 'schoolpresser-docs' ),
			'singular_name'       => _x( 'Document', 'Document', 'schoolpresser-docs' ),
			'menu_name'           => __( 'Documents', 'schoolpresser-docs' ),
			'name_admin_bar'      => __( 'Documents', 'schoolpresser-docs' ),
			'parent_item_colon'   => __( 'Parent Document:', 'schoolpresser-docs' ),
			'all_items'           => __( 'All Document', 'schoolpresser-docs' ),
			'add_new_item'        => __( 'Add Document', 'schoolpresser-docs' ),
			'add_new'             => __( 'Add New', 'schoolpresser-docs' ),
			'new_item'            => __( 'New Document', 'schoolpresser-docs' ),
			'edit_item'           => __( 'Edit Document', 'schoolpresser-docs' ),
			'update_item'         => __( 'Update Document', 'schoolpresser-docs' ),
			'view_item'           => __( 'View Document', 'schoolpresser-docs' ),
			'search_items'        => __( 'Search Document', 'schoolpresser-docs' ),
			'not_found'           => __( 'Not found', 'schoolpresser-docs' ),
			'not_found_in_trash'  => __( 'Not found in Trash', 'schoolpresser-docs' ),
		);
		$args = array(
			'label'               => __( 'document', 'schoolpresser-docs' ),
			'description'         => __( 'User Documents', 'schoolpresser-docs' ),
			'labels'              => $labels,
			'supports'            => array( 'title' ),
			'taxonomies'          => array( 'post_tag', 'category' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_rest'       => true,
			'show_in_menu'        => true,
			'menu_position'       => 35,
			'menu_icon'           => 'dashicons-format-aside',
			'show_in_admin_bar'   => false,
			'show_in_nav_menus'   => false,
			'can_export'          => false,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
		);
		register_post_type( 'schoolpresser_docs', $args );

	}

	/**
	 * The add_columns function.
	 *
	 * @access public
	 */
	public function add_columns() {
		add_filter( 'manage_edit-schoolpresser_docs_columns', array( $this, 'add_new_docs_columns' ) );
		add_action( 'manage_schoolpresser_docs_posts_custom_column', array( $this, 'columns' ), 10, 2 );
	}


	/**
	 * The add_new_gallery_columns function.
	 *
	 * @access public
	 *
	 * @param mixed $gallery_columns Array of columns.
	 * @return array
	 */
	public function add_new_docs_columns( $gallery_columns ) {

	    $new_columns['cb']     = '<input type="checkbox" />';
		$new_columns['title']  = _x( 'Doc Name', 'schoolpresser-docs' );
		$new_columns['author'] = __( 'Author' );
		$new_columns['date']   = _x( 'Date', 'schoolpresser-docs' );
		$new_columns['groups']   = _x( 'Groups', 'schoolpresser-docs' );

	    return $new_columns;
	}

	/**
	 * Display column data
	 *
	 * @param  string $column
	 * @param  integer $post_id
	 * @return void
	 */
	public function columns( $column, $post_id ) {

		switch ( $column ) {

	        case 'groups' :
				$meta = get_post_meta( $post_id , 'spd_docs_groups', true );
				$group_items = '';

				if ( is_array( $meta ) ) {
					$args['include'] = $meta;

					$groups = groups_get_groups( $args );

					foreach ( $groups['groups'] as $group ) {
						$group_permalink = trailingslashit( bp_get_root_domain() . '/' . bp_get_groups_root_slug() . '/' . $group->slug . '/' );
						$group_link = '<a href="' . $group_permalink . '">' . $group->name . '</a>';
						$group_items .= $group_link . ', ';
					}
				}

				echo substr( $group_items, 0, -2 );

			break;

	    }
	}


	/**
	 * The customize_docs_tracking_args function.
	 *
	 * @access public
	 * @return void
	 */
	public function customize_docs_tracking_args() {
	    // Check if the Activity component is active before using it.
	    if ( ! bp_is_active( 'activity' ) ) {
	        return;
	    }

	    bp_activity_set_post_type_tracking_args( 'schoolpresser_docs', array(
	        'component_id'             => buddypress()->documents->id,
	        'action_id'                => 'new_blog_schoolpresser_docs',
	        'bp_activity_admin_filter' => __( 'Added a new document', 'schoolpresser-docs' ),
	        'bp_activity_front_filter' => __( 'Document', 'schoolpresser-docs' ),
	        'contexts'                 => array( 'activity', 'member', 'group' ),
	        'activity_comment'         => false,
	        'bp_activity_new_post'     => __( '%1$s added a new <a href="%2$s">document</a>', 'schoolpresser-docs' ),
	        'bp_activity_new_post_ms'  => __( '%1$s added a new <a href="%2$s">document</a>, on the site %3$s', 'schoolpresser-docs' ),
	        'position'                 => 100,
	    ) );

		add_post_type_support( 'schoolpresser_docs', 'buddypress-activity' );
	}


	/**
	 * The cmb metabox function.
	 *
	 * @access public
	 */
	public function cmb2_metaboxes() {

		$prefix = 'spd_docs_';

		$cmb = new_cmb2_box( array(
			'id'            => $prefix . 'metabox',
			'title'         => __( 'Document Options', 'schoolpresser-docs' ),
			'object_types'  => array( 'schoolpresser_docs' ),
		) );

		$cmb->add_field( array(
		    'name' => 'Description',
		    'id' => $prefix . 'description',
		    'type' => 'textarea_small',
		) );

		$cmb->add_field( array(
		    'name'    => 'Document',
		    'desc'    => 'Upload a document.',
		    'id'      => $prefix . 'attachment',
		    'type'    => 'file',
		    'options' => array(
		        'url' => false,
		    ),
		    'text'    => array(
		        'add_upload_file_text' => 'Add File',
		    ),
		) );

		$cmb->add_field( array(
		    'name'    => 'Groups',
		    'desc'    => 'Check the group this document should be displayed in.',
		    'id'      => $prefix . 'groups',
		    'type'    => 'multicheck',
		    'options' => $this->get_groups(),
		) );

		$cmb->add_field( array(
		    'name'    => 'Permission',
		    'desc'    => 'Select the permission for this document.',
		    'id'      => $prefix . 'permission',
		    'type'    => 'radio',
			'sanitization_cb' => 'bp_docs_sanitize_set_status',
			'default' => 'private',
		    'options' => array(
				'private' => '<strong>Private:</strong> documents will ONLY be available if a logged in user is member of the group it was uploaded to.',
				'publish' => '<strong>Public:</strong> documents will be available to any logged in user even if they are not a member of the group.',
				'teased' => '<strong>Teased:</strong> members can see titles to documents but can’t access them unless they are a member of the group it was uploaded to.',
			),
		) );

	}

	/**
	 * [get_groups description]
	 *
	 * @return array
	 */
	public function get_groups() {

		$groups_arr = array();

		$args = array(
			'show_hidden' => true,
			'per_page' => -1,
		);

		if ( ! current_user_can( 'edit_users' ) ) {
			$args['user_id'] = bp_loggedin_user_id();
		}

		$groups = groups_get_groups( $args );

		if ( isset( $groups['groups'] ) ) {
			foreach ( $groups['groups'] as $group ) {
				$groups_arr[ $group->id ] = $group->name;
			}
		}

		return $groups_arr;
	}


	// Register item type Taxonomy
	function item_type_taxonomy() {

		$labels = array(
			'name'                       => _x( 'Item Types', 'Taxonomy General Name', 'schoolpresser_docs' ),
			'singular_name'              => _x( 'Item Type', 'Taxonomy Singular Name', 'schoolpresser_docs' ),
			'menu_name'                  => __( 'Item Type', 'schoolpresser_docs' ),
			'all_items'                  => __( 'All Items', 'schoolpresser_docs' ),
			'parent_item'                => __( 'Parent Item', 'schoolpresser_docs' ),
			'parent_item_colon'          => __( 'Parent Item:', 'schoolpresser_docs' ),
			'new_item_name'              => __( 'New Item Name', 'schoolpresser_docs' ),
			'add_new_item'               => __( 'Add New Item', 'schoolpresser_docs' ),
			'edit_item'                  => __( 'Edit Item', 'schoolpresser_docs' ),
			'update_item'                => __( 'Update Item', 'schoolpresser_docs' ),
			'view_item'                  => __( 'View Item', 'schoolpresser_docs' ),
			'separate_items_with_commas' => __( 'Separate items with commas', 'schoolpresser_docs' ),
			'add_or_remove_items'        => __( 'Add or remove items', 'schoolpresser_docs' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'schoolpresser_docs' ),
			'popular_items'              => __( 'Popular Items', 'schoolpresser_docs' ),
			'search_items'               => __( 'Search Items', 'schoolpresser_docs' ),
			'not_found'                  => __( 'Not Found', 'schoolpresser_docs' ),
			'no_terms'                   => __( 'No items', 'schoolpresser_docs' ),
			'items_list'                 => __( 'Items list', 'schoolpresser_docs' ),
			'items_list_navigation'      => __( 'Items list navigation', 'schoolpresser_docs' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => true,
		);
		register_taxonomy( 'item_type', array( 'schoolpresser_docs' ), $args );

	}

	// Register item type Taxonomy
	function license_taxonomy() {

		$labels = array(
			'name'                       => _x( 'License', 'Taxonomy General Name', 'schoolpresser_docs' ),
			'singular_name'              => _x( 'License', 'Taxonomy Singular Name', 'schoolpresser_docs' ),
			'menu_name'                  => __( 'License', 'schoolpresser_docs' ),
			'all_items'                  => __( 'All Items', 'schoolpresser_docs' ),
			'parent_item'                => __( 'Parent Item', 'schoolpresser_docs' ),
			'parent_item_colon'          => __( 'Parent Item:', 'schoolpresser_docs' ),
			'new_item_name'              => __( 'New Item Name', 'schoolpresser_docs' ),
			'add_new_item'               => __( 'Add New Item', 'schoolpresser_docs' ),
			'edit_item'                  => __( 'Edit Item', 'schoolpresser_docs' ),
			'update_item'                => __( 'Update Item', 'schoolpresser_docs' ),
			'view_item'                  => __( 'View Item', 'schoolpresser_docs' ),
			'separate_items_with_commas' => __( 'Separate items with commas', 'schoolpresser_docs' ),
			'add_or_remove_items'        => __( 'Add or remove items', 'schoolpresser_docs' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'schoolpresser_docs' ),
			'popular_items'              => __( 'Popular Items', 'schoolpresser_docs' ),
			'search_items'               => __( 'Search Items', 'schoolpresser_docs' ),
			'not_found'                  => __( 'Not Found', 'schoolpresser_docs' ),
			'no_terms'                   => __( 'No items', 'schoolpresser_docs' ),
			'items_list'                 => __( 'Items list', 'schoolpresser_docs' ),
			'items_list_navigation'      => __( 'Items list navigation', 'schoolpresser_docs' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => true,
		);
		register_taxonomy( 'license', array( 'schoolpresser_docs' ), $args );

	}

}
BPD_CPT::instance();
