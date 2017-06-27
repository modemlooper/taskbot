<?php

class TaskBot_Batch extends WP_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 'taskbot_process';


	public function __construct() {
		parent::__construct();

		//add_action( 'wp_process_current_' . $this->current_task, array( $this, $this->current_task ) );
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
	protected function task( $item ) {
		// Actions to perform

		sleep(5);

		do_action( 'taskbot_run_test_task', 'test_task', $item );

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

		// Show notice to user or perform some other arbitrary task...
	}

}