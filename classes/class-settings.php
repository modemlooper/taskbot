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
		 * Array of metaboxes/fields
		 *
		 * @var array
		 */
		protected $option_metabox = array();

		/**
		 * Options Page title
		 *
		 * @var string
		 */
		protected $title = '';

		/**
		 * Options Tab Pages
		 *
		 * @var array
		 */
		protected $options_pages = array();

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
		 * @var object TaskBot_Admin
		 * @since 1.0.0
		 */
		private static $instance = null;

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

			$this->title = __( 'TaskBot', 'taskbot' );
		}

		/**
		 * Initiate our hooks.
		 *
		 * @since 1.0.0
		 */
		public function hooks() {

			add_action( 'admin_init', array( $this, 'init' ) );
			add_action( 'admin_menu', array( $this, 'add_options_page' ) );

			// Override CMB's getter.
			add_filter( 'cmb2_override_option_get_' . $this->key, array( $this, 'get_override' ), 10, 2 );
			// Override CMB's setter.
			add_filter( 'cmb2_override_option_save_' . $this->key, array( $this, 'update_override' ), 10, 2 );
		}


			/**
			 * Set it off!
			 *
			 * @since 1.0.0
			 */
		public function init() {
			$option_tabs = self::option_fields();
			foreach ( $option_tabs as $index => $option_tab ) {
				register_setting( $option_tab['id'], $option_tab['id'] );
			}
		}

		/**
		 * Add menu options page
		 *
		 * @since 1.0.0
		 */
		public function add_options_page() {

			$option_tabs = self::option_fields();
			foreach ( $option_tabs as $index => $option_tab ) {
			 	if ( 0 === $index ) {
			 		//$this->options_pages[] = add_menu_page( $this->title, $this->title, 'manage_options', $option_tab['id'], array( $this, 'admin_page_display' ), 'dashicons-controls-repeat' ); // Link admin menu to first tab.
			 		add_submenu_page( 'edit.php?post_type=taskbot', $this->title, $option_tab['title'], 'manage_options', $option_tab['id'], array( $this, 'admin_page_display' ) ); // Duplicate menu link for first submenu page.
			 	} else {
			 		$this->options_pages[] = add_submenu_page( 'edit.php?post_type=taskbot', $this->title, $option_tab['title'], 'manage_options', $option_tab['id'], array( $this, 'admin_page_display' ) );
			 	}
			}

		}
		/**
		 * Admin page markup. Tabs and metabox output.
		 *
		 * @since  1.0.0
		 */
		public function admin_page_display() {
			$option_tabs = self::option_fields(); // get all option tabs.
			$tab_forms = array();
			?>
			<div class="wrap cmb_options_page <?php echo esc_attr( $this->key ); ?>">
			    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

			<div class="content-wrap col">

			    <!-- Options Page Nav Tabs -->
			    <!-- <h2 class="nav-tab-wrapper">
			    	<?php foreach ( $option_tabs as $option_tab ) :
			    		$tab_slug = $option_tab['id'];
			    		$nav_class = 'nav-tab';
			    		if ( $tab_slug === $_GET['page'] ) {
			    			$nav_class .= ' nav-tab-active'; // add active class to current tab.
			    			$tab_forms[] = $option_tab; // add current tab to forms to be rendered.
			    		}
			    	?>

			    	<a class="<?php echo esc_attr( $nav_class ); ?>" href="<?php menu_page_url( $tab_slug ); ?>"><?php echo esc_attr( $option_tab['title'] ); ?></a>
			    	<?php endforeach; ?>
			    </h2> -->
			    <!-- End of Nav Tabs -->

			    <?php foreach ( $tab_forms as $tab_form ) : // render all tab forms (normaly just 1 form). ?>
			    <div id="<?php echo esc_attr( $tab_form['id'] ); ?>" class="group">
			    	<?php cmb2_metabox_form( $tab_form, $tab_form['id'] ); ?>
			    </div>
			    <?php endforeach; ?>
			</div>
				<div class="clearfix"></div>
			</div>
			<?php
		}
		/**
		 * Add the options metabox to the array of metaboxes
		 *
		 * @since  1.0.0
		 */
		function option_fields() {

			$prefix = 'taskbot_';

			// Only need to initiate the array once per page-load.
			if ( ! empty( $this->option_metabox ) ) {
				return $this->option_metabox;
			}

			$this->option_metabox[] = array(
				'id'         => $prefix . 'settings', // id used as tab page slug, must be unique.
				'title'      => 'Settings',
				'show_on'    => array( 'key' => $prefix . 'options-page', 'value' => array( 'settings' ) ), // value must be same as id.
				'show_names' => true,
				'fields'     => array(
					array(
						'name' => __( 'Header Logo', 'theme_textdomain' ),
						'desc' => __( 'Logo to be displayed in the header menu.', 'theme_textdomain' ),
						'id' => 'header_logo', // each field id must be unique.
						'default' => '',
						'type' => 'text',
					),
				),
			);

			// insert extra tabs here.
			apply_filters( $this->key . '_metaboxes', $this->option_metabox );

			return $this->option_metabox;
		}

		/**
		 * Returns the option key for a given field id.
		 *
		 * @since  1.0.0
		 * @param string $field_id id of field.
		 * @return array
		 */
		public function get_option_key( $field_id ) {
			$option_tabs = $this->option_fields();
			foreach ( $option_tabs as $option_tab ) { // search all tabs.
				foreach ( $option_tab['fields'] as $field ) { // search all fields.
					if ( $field['id'] === $field_id ) {
						return $option_tab['id'];
					}
				}
			}
			return $this->key; // return default key if field id not found.
		}

		/**
		 * Register settings notices for display
		 *
		 * @since  1.0.0
		 * @param  int   $object_id Option key.
		 * @param  array $updated   Array of updated fields.
		 * @return void
		 */
		public function settings_notices( $object_id, $updated ) {
			if ( $object_id !== $this->key || empty( $updated ) ) {
				return;
			}
			add_settings_error( $this->key . '-notices', '', __( 'Settings updated.', 'myprefix' ), 'updated' );
			settings_errors( $this->key . '-notices' );
		}

		/**
		 * Replaces get_option with get_site_option
		 *
		 * @since  1.0.0
		 */
		public function get_override( $test, $default = false ) {
			return get_site_option( $this->key, $default );
		}

		/**
		 * Replaces update_option with update_site_option
		 *
		 * @since  1.0.0
		 */
		public function update_override( $test, $option_value ) {
			return update_site_option( $this->key, $option_value );
		}
		/**
		 * Public getter method for retrieving protected/private variables
		 *
		 * @since  1.0.0
		 * @param  string $field Field to retrieve
		 * @return mixed          Field value or exception is thrown
		 */
		public function __get( $field ) {
			// Allowed fields to retrieve
			if ( in_array( $field, array( 'key', 'metabox_id', 'title', 'options_page' ), true ) ) {
				return $this->{$field};
			}
			throw new Exception( 'Invalid property: ' . $field );
		}
	}

	/**
	 * Wrapper function around cmb2_get_option
	 *
	 * @since  1.0.0
	 * @param  string $key Options array key.
	 * @return mixed        Option value
	 */
	function taskbot_get_network_option( $key = '', $default = null ) {
		$opt_key = taskbot()->admin->key;
		if ( function_exists( 'cmb2_get_option' ) ) {
			// Use cmb2_get_option as it passes through some key filters.
			return cmb2_get_option( $opt_key, $key, $default );
		}
		// Fallback to get_option if CMB2 is not loaded yet.
		$opts = get_option( $opt_key, $key, $default );
		$val = $default;
		if ( 'all' === $key ) {
			$val = $opts;
		} elseif ( array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
			$val = $opts[ $key ];
		}
		return $val;
	}
endif; // End class_exists check.
