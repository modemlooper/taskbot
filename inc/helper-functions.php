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

/**
 * Returns all tasks
 *
 * @return array Array of all task objects
 */
function taskbot_get_all_tasks() {
	return TaskBot_Base::get_all();
}

/**
 *
 * Get saved task data from cpt id
 *
 * @since 1.0.0
 * @param  integer $post_id
 * @return string
 */
function taskbot_get_task_by_id( $post_id ) {

	$tasks = get_site_option( 'taskbot_tasks' );

	foreach ( $tasks as $task => $value ) {
		if ( $post_id === $task ) {
			return $value;
		}
	}

	return;
}

/**
 * Returns meta data from task cpt id
 *
 * @since 1.0.0
 * @param  integer $post_id
 * @return array
 */
function taskbot_get_task_metadata( $post_id ) {

	$meta = get_post_meta( $post_id );

	$fields_data = taskbot_task_process()->process_fields( $meta['_taskbot_task'][0], $meta );

	return $fields_data;
}
