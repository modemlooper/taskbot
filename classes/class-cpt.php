<?php
/**
 * TaskBot Settings Class.
 *
 * @package TaskBot
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'TaskBot_CPT' ) ) :

	/**
	 * Load TaskBot plugin settings pages.
	 *
	 * @since 1.0.0
	 */
	class TaskBot_CPT {

		/**
		 * Parent plugin class.
		 *
		 * @var object
		 * @since 1.0.0
		 */
		protected $plugin = null;

		/**
		 * Holds an instance of the object.
		 *
		 * @var object TaskBot_CPT
		 * @since 1.0.0
		 */
		private static $instance = null;

		/**
		 * Registered tasks.
		 *
		 * @var array
		 * @since 1.0.0
		 */
		public $tasks = array();

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @param object $plugin this class.
		 */
		public function __construct( $plugin ) {
			$this->plugin = $plugin;
			$this->hooks();
		}

		/**
		 * Initiate our hooks.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function hooks() {
			add_action( 'init', array( $this, 'taskbot_post_type' ), 0 );
			add_action( 'init', array( $this, 'get_tasks' ) );
			add_action( 'cmb2_admin_init', array( $this, 'metaboxes' ) );
			add_action( 'cmb2_admin_init', array( $this, 'task_fields' ) );
			add_filter( 'post_updated_messages', array( $this, 'taskbot_cpt_messages' ) );
			add_action( 'admin_menu' , array( $this, 'remove_metabox' ) );
		}

		/**
		 * Remove unused metaboxes
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function remove_metabox() {
			remove_meta_box( 'slugdiv', 'taskbot', 'normal' );
		}

		/**
		 * Register TaskBot CPT
		 *
		 * @since 1.0.0
		 */
		public function taskbot_post_type() {

			$labels = array(
				'name'                  => _x( 'Tasks', 'Post Type General Name', 'taskbot' ),
				'singular_name'         => _x( 'Task', 'Post Type Singular Name', 'taskbot' ),
				'menu_name'             => __( 'TaskBot', 'taskbot' ),
				'name_admin_bar'        => __( 'TaskBot', 'taskbot' ),
				'archives'              => __( 'Task Archives', 'taskbot' ),
				'attributes'            => __( 'Task Attributes', 'taskbot' ),
				'parent_item_colon'     => __( 'Parent Task:', 'taskbot' ),
				'all_items'             => __( 'All Tasks', 'taskbot' ),
				'add_new_item'          => __( 'Add New Task', 'taskbot' ),
				'add_new'               => __( 'Add Task', 'taskbot' ),
				'new_item'              => __( 'New Task', 'taskbot' ),
				'edit_item'             => __( 'Edit Task', 'taskbot' ),
				'update_item'           => __( 'Update Task', 'taskbot' ),
				'view_item'             => __( 'View Task', 'taskbot' ),
				'view_items'            => __( 'View Tasks', 'taskbot' ),
				'search_items'          => __( 'Search Task', 'taskbot' ),
				'not_found'             => __( 'Not found', 'taskbot' ),
				'not_found_in_trash'    => __( 'Not found in Trash', 'taskbot' ),
				'featured_image'        => __( 'Featured Image', 'taskbot' ),
				'set_featured_image'    => __( 'Set featured image', 'taskbot' ),
				'remove_featured_image' => __( 'Remove featured image', 'taskbot' ),
				'use_featured_image'    => __( 'Use as featured image', 'taskbot' ),
				'insert_into_item'      => __( 'Insert into item', 'taskbot' ),
				'uploaded_to_this_item' => __( 'Uploaded to this Task', 'taskbot' ),
				'items_list'            => __( 'Tasks list', 'taskbot' ),
				'items_list_navigation' => __( 'Tasks list navigation', 'taskbot' ),
				'filter_items_list'     => __( 'Filter tasks list', 'taskbot' ),
			);
			$args = array(
				'label'                 => __( 'Task', 'taskbot' ),
				'description'           => __( 'Post Type Description', 'taskbot' ),
				'labels'                => $labels,
				'supports'              => array( 'title' ),
				'taxonomies'            => array(),
				'hierarchical'          => false,
				'public'                => true,
				'show_ui'               => true,
				'show_in_menu'          => true,
				'menu_position'         => 999,
				'menu_icon'             => 'dashicons-controls-repeat',
				'show_in_admin_bar'     => true,
				'show_in_nav_menus'     => false,
				'can_export'            => true,
				'has_archive'           => false,
				'exclude_from_search'   => true,
				'publicly_queryable'    => false,
				'capability_type'       => 'page',
				'show_in_rest'          => false,
			);
			register_post_type( 'taskbot', $args );

		}

		/**
		 * Custom post type notices for taskbot cpt
		 *
		 * @param  array $messages
		 * @return array
		 */
		public function taskbot_cpt_messages( $messages ) {
			$post             = get_post();
			$post_type        = get_post_type( $post );
			$post_type_object = get_post_type_object( $post_type );

			$messages['taskbot'] = array(
				0  => '', // Unused. Messages start at index 1.
				1  => __( 'Task updated.', 'taskbot' ),
				2  => __( 'Custom field updated.', 'taskbot' ),
				3  => __( 'Custom field deleted.', 'taskbot' ),
				4  => __( 'Task updated.', 'taskbot' ),
				5  => isset( $_GET['revision'] ) ? sprintf( __( 'Task restored to revision from %s', 'taskbot' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6  => __( 'Task published.', 'taskbot' ),
				7  => __( 'Task saved.', 'taskbot' ),
				8  => __( 'Task submitted.', 'taskbot' ),
				9  => sprintf(
					__( 'Task scheduled for: <strong>%1$s</strong>.', 'taskbot' ),
					date_i18n( __( 'M j, Y @ G:i', 'taskbot' ), strtotime( $post->post_date ) )
				),
				10 => __( 'Task draft updated.', 'taskbot' ),
			);

			if ( $post_type_object->publicly_queryable ) {
				$permalink = get_permalink( $post->ID );

				$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View task', 'taskbot' ) );
				$messages[ $post_type ][1] .= $view_link;
				$messages[ $post_type ][6] .= $view_link;
				$messages[ $post_type ][9] .= $view_link;

				$preview_permalink = add_query_arg( 'preview', 'true', $permalink );
				$preview_link      = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview task', 'taskbot' ) );
				$messages[ $post_type ][8] .= $preview_link;
				$messages[ $post_type ][10] .= $preview_link;
			}

			return $messages;
		}

		/**
		 * CBM2 metaboxes config
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function metaboxes() {

			$prefix = '_taskbot_';

			/**
			 * Initiate the metabox
			 */
			$cmb = new_cmb2_box( array(
				'id'            => 'task_metabox',
				'title'         => __( 'Task', 'taskbot' ),
				'object_types'  => array( 'taskbot' ),
				'context'       => 'normal',
				'priority'      => 'high',
				'show_names'    => true,
			) );

			$cmb->add_field( array(
				'name'             => 'Task',
				'desc'             => 'Select an option',
				'id'               => $prefix . 'task',
				'type'             => 'select',
				'show_option_none' => true,
				'default'          => 'custom',
				'options'          => $this->tasks,
			) );

			// Sidebar.
			$cmb2 = new_cmb2_box( array(
				'id'            => 'task_schedule',
				'title'         => __( 'Schedule', 'taskbot' ),
				'object_types'  => array( 'taskbot' ),
				'context'       => 'side',
				'priority'      => 'low',
				'show_names'    => true,
			) );

			$cmb2->add_field( array(
				'name' => 'Date & Time to run this task',
				'id'   => $prefix . 'datetime_timestamp',
				'type' => 'text_datetime_timestamp',
			) );

			$cmb2->add_field( array(
				'name' => 'Recurrence',
				'id'   => $prefix . 'recurrence',
				'description' => 'Choose an interval to rerun this task. One Time will run the task one time at the set date and time.',
				'type' => 'select',
				'options' => $this->get_schedules(),
			) );
		}

		/**
		 * Add cmb2 fields from task data
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function task_fields() {

			$tb = taskbot_get_all_tasks();
			$prefix = '_taskbot_';

			foreach ( $tb as $task ) {

				/**
				 * Initiate the metabox
				 */
				$cmb = new_cmb2_box( array(
					'id'            => $task->task['id'],
					'title'         => $task->task['title'] . ' Options',
					'object_types'  => array( 'taskbot' ),
					'context'       => 'normal',
					'priority'      => 'high',
					'show_names'    => true,
					'attributes' => array( 'classes' => 'task-option' ),
				) );

				// Add each field.
				if ( isset( $task->task['fields'] ) && ! empty( $task->task['fields'] ) ) {
					foreach ( $task->task['fields'] as $field ) {
						$cmb->add_field( $field );
					}
				}
			}

		}

		/**
		 * Array of cron schedule intervals
		 *
		 * @since 1.0.0
		 * @return array
		 */
		public function get_schedules() {

			$schedules = wp_get_schedules();
			$sched_arr = array();

			foreach ( $schedules as $key => $value ) {
				$sched_arr[ $key ] = $value['display'];
			}

			$sched_arr['once'] = 'One Time';

			return apply_filters( 'taskbot_get_schedules', $sched_arr );

		}

		/**
		 * Array of tasks
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function get_tasks() {

			$tasks = taskbot_get_all_tasks();

			foreach ( $tasks as $key => $value ) {
				if ( isset( $value->task['title'] ) && ! empty( $value->task['title'] ) ) {
					$this->tasks[ $value->task['id'] ] = $value->task['title'];
				}
			}
		}
	}
endif; // End class_exists check.

/**
 * Remove row actions
 *
 * @since 1.0.0
 * @param array $actions
 * @return array
 */
function taskbot_remove_row_actions( $actions ) {
	if ( get_post_type() === 'taskbot' ) {
		// unset( $actions['edit'] );
		// unset( $actions['view'] );
		// unset( $actions['trash'] );
		unset( $actions['inline hide-if-no-js'] );
	}
	return $actions;
}
add_filter( 'post_row_actions', 'taskbot_remove_row_actions', 10, 1 );

/**
 * Remove cpt list columns
 *
 * @since 1.0.0
 * @param array $columns
 * @return array
 */
function taskbot_hide_cpt_columns( $columns ) {
	unset( $columns['date'] );
	return $columns;
}
add_filter( 'manage_edit-taskbot_columns', 'taskbot_hide_cpt_columns' );

/**
 * Remove filter actions
 *
 * @since 1.0.0
 * @return void
 */
function taskbot_remove_filter_actions() {

	global $current_screen;
	if ( 'taskbot' !== $current_screen->post_type ) {
		return;
	}
	?>
		<style>
			select[name="m"] { display:none }
			select[id="cat"] { display:none }
			#post-query-submit { display:none }
		</style>
	<?php
}
add_action( 'admin_head-edit.php', 'taskbot_remove_filter_actions' );

/**
 * Remove publshing actions
 *
 * @since 1.0.0
 * @return void
 */
function taskbot_remove_publishing_actions() {
	global $current_screen;
	if ( 'taskbot' === $current_screen->post_type ) {
		echo '<style type="text/css">
        #misc-publishing-actions,
        #minor-publishing-actions{
        display:none;
        }
        </style>';
	}
}
add_action( 'admin_head-post.php', 'taskbot_remove_publishing_actions' );
add_action( 'admin_head-post-new.php', 'taskbot_remove_publishing_actions' );
