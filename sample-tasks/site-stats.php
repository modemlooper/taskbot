<?php
/**
 * Taskbot_Site_Stats.
 *
 * @package         Taskbot_Site_Stats
 * @author          AplhaWeb
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
		exit;
}


if ( ! class_exists( 'Taskbot_Site_Stats' ) ) {

	class Taskbot_Site_Stats {


		/**
		 * @var         Taskbot_Site_Stats $instance
		 * @since       1.0.0
		 */
		private static $instance;

		public $task = 'daily_stats';

		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      object self::$instance
		 */
		public static function instance() {
			if ( ! self::$instance ) {
				self::$instance = new Taskbot_Site_Stats();
				self::$instance->hooks();
			}

			return self::$instance;
		}

		public function hooks() {
			add_action( 'taskbot_init', array( $this, 'register_task' ) );
			add_action( 'taskbot_add_' . $this->task, array( $this, 'add_items' ) );
			add_action( 'taskbot_run_' . $this->task, array( $this, 'process_item' ), 10, 2 );
			add_action( 'taskbot_complete_' . $this->task, array( $this, 'complete' ) );
		}

		public function register_task() {

			$tb = taskbot_new_task( array(
				'id' => $this->task,
				'title' => 'Site Stats',
				'description' => 'Get email of sites stats.',
				'data' => array( 'item' => 1 ),
				'fields' => array(
					array(
						'id' => 'title',
						'name' => 'Instructions',
						'desc' => 'This is an example task. Site admin will recieve an email with new emails and posts since last time task has run. Learn how to add your own custom tasks <a href="https://github.com/modemlooper/taskbot/wiki">Taskbot Wiki</a>',
						'type' => 'title',
					),
				),
			) );

		}


		public function add_items( $task ) {

			taskbot_add_items( array(
				'task' => $task,
				'items' => array( '1' ),
			) );
		}


		public function process_item( $data ) {

			$date = date( 'Y-m-d', strtotime( ' -1 day' ) );

			$query_string = array(
			      'post_type' => 'post',
			      'date_query' => array(
			        'after' => $date,
			      ),
			      'post_status' => 'publish',
			);

			$query = new WP_Query( $query_string );

			$comment_query_string = array(
				  'date_query' => array(
					'after' => $date,
				  ),
			);

			$comment_query = new WP_Comment_Query( $comment_query_string );

			$posts_count = count( $query->posts );
			$comments_count = count( $comment_query->comments );

			$to_email = get_bloginfo( 'admin_email' );

			$to = $to_email;
			$subject = 'site stats complete';
			$body = 'There were ' . $posts_count . ' post(s) and ' . $comments_count . ' comment(s) since last tiem this task ran.' ;
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );

			wp_mail( $to, $subject, $body, $headers );

		}


		public function complete( $batch ) {

		}

	}

}

/**
 * The main function responsible for returning the one true Taskbot_Site_Stats
 * instance to functions everywhere.
 *
 * @since       1.0.0
 * @return      Taskbot_Site_Stats
 */
function taskbot_site_stats() {
	return Taskbot_Site_Stats::instance();
}
taskbot_site_stats();
