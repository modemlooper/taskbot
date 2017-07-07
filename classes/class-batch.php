<?php

class TaskBot_Batch extends WP_Background_Process {

	/**
	 * Action
	 *
	 * @since 1.0.0
	 * @var string
	 * @access protected
	 */
	protected $action = 'taskbot_process';

	/**
	 * __construct
	 *
	 * @since 1.0.0.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Task
	 *
	 * Method to perform add actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @since 1.0.0
	 * @param mixed $task Task data.
	 * @param mixed $item Queue item to iterate over.
	 * @return mixed
	 */
	protected function task( $task, $item ) {

		//sleep(1);

		time_nanosleep(0, 100000000);

		do_action( 'taskbot_run_' . $task['id'], $item, $task );

		return false;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		parent::complete();

		$to = 'modemlooper@gmail.com';
		$subject = 'taskbot complete';
		$body = 'The task completed ' . current_time( 'h:i:s' );
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		wp_mail( $to, $subject, $body, $headers );
	}

}
