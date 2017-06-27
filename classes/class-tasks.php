<?php
/**
 *
 * Tasks Class.
 *
 * @package TaskBot
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'TaskBot_Tasks' ) ) :

	/**
	 * Load Tasks.
	 *
	 * @since 1.0.0
	 */
	class TaskBot_Tasks {

		/**
		 * Task Config array
		 *
		 * @var   array
		 * @since 1.0.0
		 */
		public $task = array();

		/**
		 * Task Defaults
		 *
		 * @var   array
		 * @since 1.0.0
		 */
		protected $tb_defaults = array(
			'id'               => '',
			'title'            => '',
			'description'      => '',
			'data'		   => '',
			'fields'           => array(),
		);


		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 * @param object $config this task.
		 */
		public function __construct( $config ) {

			if ( empty( $config['id'] ) ) {
				wp_die( esc_html__( 'TaskBot configuration is required to have an ID parameter.', 'taskbot' ) );
			}

			$this->task = wp_parse_args( $config, $this->tb_defaults );
			$this->tb_id = $config['id'];

			TaskBot_Base::add( $this );

			/**
			 * Hook during initiation of TaskBot_Tasks object
			 *
			 * The dynamic portion of the hook name, $this->tb_id, is this task id.
			 *
			 * @param array $tb This TaskBot_Tasks object
			 */
			do_action( "taskbot_init_{$this->tb_id}", $this );
		}



	}

endif; // End class_exists check.
