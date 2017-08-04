<?php
/**
 * TaskBot Settings Class.
 *
 * @package TaskBot
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'TaskBot_Settings' ) ) :

	/**
	 * Load TaskBot plugin settings pages.
	 *
	 * @since 1.0.0
	 */
	class TaskBot_Settings {

		/**
		 * Option key, and option page slug
		 *
		 * @var string
		 */
		private $key = 'taskbot_options';

		/**
		 * Settings page metabox id
		 *
		 * @var string
		 */
		private $metabox_id = 'taskbox_option_metabox';

		/**
		 * Settings Page title
		 *
		 * @var string
		 */
		protected $title = '';

		/**
		 * Settings Page hook
		 *
		 * @var string
		 */
		protected $options_page = '';

		/**
		 * Holds an instance of the object.
		 *
		 * @var object TaskBot_CPT
		 * @since 1.0.0
		 */
		private static $instance = null;

		/*
		* Constructor
		* @since 0.1.0
		*/
		public function __construct() {
			// Set our title
			$this->title = __( 'Settings', 'taskbot' );
		}

		/**
		 * Get the running object
		 *
		 * @return TaskBot_Settings
		 **/
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
				self::$instance->hooks();
			}
			return self::$instance;
		}

		/**
		 * Initiate our hooks
		 *
		 * @since 0.1.0
		 */
		public function hooks() {
			add_action( 'admin_init', array( $this, 'init' ) );
			add_action( 'admin_menu', array( $this, 'add_options_page' ) );
			add_action( 'cmb2_admin_init', array( $this, 'add_options_page_metabox' ) );

			// Override CMB's getter
			add_filter( 'cmb2_override_option_get_' . $this->key, array( $this, 'get_override' ), 10, 2 );
			// Override CMB's setter
			add_filter( 'cmb2_override_option_save_' . $this->key, array( $this, 'update_override' ), 10, 2 );
		}

		/**
		 * Register our setting to WP
		 *
		 * @since  0.1.0
		 */
		public function init() {
			register_setting( $this->key, $this->key );
		}

		/**
		 * Add menu options page
		 *
		 * @since 0.1.0
		 */
		public function add_options_page() {

			$this->options_page = add_submenu_page( 'edit.php?post_type=taskbot', $this->title, $this->title, 'manage_options', $this->key, array( $this, 'admin_page_display' ) );
			// $this->options_page = add_menu_page( $this->title, $this->title, 'manage_options', $this->key, array( $this, 'admin_page_display' ) );
			// add_action( "admin_head-{$this->options_page}", array( $this, 'enqueue_js' ) );
			// Include CMB CSS in the head to avoid FOUC
			add_action( "admin_print_styles-{$this->options_page}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
		}

		/**
		 * Admin page markup. Mostly handled by CMB2
		 *
		 * @since  0.1.0
		 */
		public function admin_page_display() {
			?>
			<div class="wrap cmb2-options-page <?php echo $this->key; ?>">
			<style>
			.cmb2-wrap {
				margin-bottom: 30px !important;
			}

			.cmb-th + .cmb-td {
				float: none !important;
				margin-left: 200px;
			}

			p.cmb2-metabox-description {

			}

			.cmb2-wrap .cmb-row {
				border-bottom: 1px solid #e0e0e0;
			}
			</style>
			<h2>TaskBot <?php echo esc_html( get_admin_page_title() ); ?></h2>
			<?php cmb2_metabox_form( $this->metabox_id, $this->key ); ?>
			</div>
			<?php
		}

		/**
		 * Add the options metabox to the array of metaboxes
		 *
		 * @since  0.1.0
		 */
		function add_options_page_metabox() {

			// hook in our save notices.
			add_action( "cmb2_save_options-page_fields_{$this->metabox_id}", array( $this, 'settings_notices' ), 10, 2 );

			$cmb = new_cmb2_box( array(
				'id'         => $this->metabox_id,
				'hookup'     => false,
				'cmb_styles' => false,
				'show_on'    => array(
				// These are important, don't remove.
				'key'   => 'options-page',
				'value' => array( $this->key ),
				),
			) );

			$cmb->add_field( 	array(
				'name' => __( 'Batch Size', 'taskbot' ),
				'description' => __( 'Batch array size. TaskBot cuts up tasks into chunks for efficient pocessing. Server resources can fail if large arrays are held in memory. If this happens, choose a lower amount for batches.', 'taskbot' ),
				'id' => 'taskbot_batch_size', // each field id must be unique.
				'default' => '500',
				'type' => 'select',
				'options' => array(
					'50' => '50',
					'100' => '100',
					'200' => '200',
					'350' => '350',
					'500' => '500',
					'1000' => '1000',
				),
			) );

		}

		/**
		 * Register settings notices for display
		 *
		 * @since  0.1.0
		 * @param  int   $object_id Option key
		 * @param  array $updated   Array of updated fields
		 * @return void
		 */
		public function settings_notices( $object_id, $updated ) {
			if ( $object_id !== $this->key || empty( $updated ) ) {
				return;
			}

			add_settings_error( $this->key . '-notices', '', __( 'Settings updated.', 'taskbot' ), 'updated' );
			settings_errors( $this->key . '-notices' );
		}

		/**
		 * Replaces get_option with get_site_option
		 *
		 * @since  0.1.0
		 */
		public function get_override( $test, $default = false ) {
			return get_site_option( $this->key, $default );
		}

		/**
		 * Replaces update_option with update_site_option
		 *
		 * @since  0.1.0
		 */
		public function update_override( $test, $option_value ) {
			return update_site_option( $this->key, $option_value );
		}

		/**
		 * Public getter method for retrieving protected/private variables
		 *
		 * @since  0.1.0
		 * @param  string $field Field to retrieve
		 * @return mixed  Field value or exception is thrown
		 */
		public function __get( $field ) {
			// Allowed fields to retrieve
			if ( in_array( $field, array( 'key', 'metabox_id', 'title', 'options_page' ), true ) ) {
				return $this->{$field};
			}

			throw new Exception( 'Invalid property: ' . $field );
		}

	}

endif;

/**
 * Helper function to get/return the TaskBot_Settings object
 *
 * @since  0.1.0
 * @return TaskBot_Settings object
 */
function taskbot_network_admin() {
	return TaskBot_Settings::get_instance();
}
taskbot_network_admin();

/**
 * Wrapper function around cmb2_get_option
 *
 * @since  0.1.0
 * @param  string $key     Options array key
 * @param  mixed  $default Optional default value
 * @return mixed           Option value
 */
function taskbot_get_option( $key = '', $default = false ) {
	$opt_key = taskbot_network_admin()->key;

	if ( function_exists( 'cmb2_get_option' ) ) {
		// Use cmb2_get_option as it passes through some key filters.
		return cmb2_get_option( $opt_key, $key, $default );
	}

	// Fallback to get_option if CMB2 is not loaded yet.
	$opts = get_option( $opt_key, $default );

	$val = $default;

	if ( 'all' == $key ) {
		$val = $opts;
	} elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
		$val = $opts[ $key ];
	}

	return $val;
}
