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
	protected function task( $task, $item, $data ) {

		$data = array(
			'task' => $task,
			'item' => $item,
			'data' => $data,
		);

		do_action( 'taskbot_run_' . $task['id'], $data );

		return false;
	}

	/**
	 * Batch Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 * @param array $batch
	 * @return void
	 */
	protected function complete( $batch ) {

		do_action( 'taskbot_complete_' . $batch['id'], $batch );

	}

}
