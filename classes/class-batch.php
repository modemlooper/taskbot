<?php

class TaskBot_Batch extends WP_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 'taskbot_process';


	public function __construct() {
		parent::__construct();
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over
	 *
	 * @return mixed
	 */
	protected function task( $task, $item ) {
		// Actions to perform

		//sleep(1);

		do_action( 'taskbot_run_' . $task['id'] , $task, $item );

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
		$subject = 'sync complete';
		$body = 'The sync completed';
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		wp_mail( $to, $subject, $body, $headers );
	}

}
