<?php
/**
 * TaskBot Helper Functions
 *
 * @package TaskBot
 */

/**
 * Get down with OOP!
 *
 * @since  1.0.0
 * @param  array $task_config Task Config array.
 * @return TaskBot_Task object Instantiated TaskBot_Task object
 */
function taskbot_new_task( array $task_config ) {
		return taskbot_get_task( $task_config );
}

/**
 * Retrieve a TaskBot instance by the task ID
 *
 * @since  1.0.0
 * @param  mixed  $taskbot    Metabox ID or Metabox config array.
 * @return TaskBot object
 */
function taskbot_get_task( $taskbot ) {

	if ( $taskbot instanceof TaskBot_Tasks ) {
		return $taskbot;
	}

	// See if we already have an instance of this metabox.
	$tb = TaskBot_Base::get( $taskbot['id'] );
	// If not, we'll initate a new metabox.
	$tb = $tb ? $tb : new TaskBot_Tasks( $taskbot );

	return $tb;
}
