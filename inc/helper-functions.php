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
 * @since  2.0.0
 * @param  mixed  $taskbot    Metabox ID or Metabox config array.
 * @param  int    $object_id   Object ID.
 * @param  string $object_type Type of object being saved. (e.g., post, user, comment, or options-page).
 *                             Defaults to metabox object type.
 * @return TaskBot object
 */
function taskbot_get_task( $taskbot, $object_id = 0, $object_type = '' ) {

	if ( $taskbot instanceof TaskBot_Tasks ) {
		return $taskbot;
	}

	// See if we already have an instance of this metabox.
	$tb = TaskBot_Base::get( $taskbot['id'] );
	// If not, we'll initate a new metabox.
	$tb = $tb ? $tb : new TaskBot_Tasks( $taskbot, $object_id );

	if ( $tb && $object_id ) {
		$tb->object_id( $object_id );
	}

	if ( $tb && $object_type ) {
		$tb->object_type( $object_type );
	}

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

function taskbot_get_task_by_id( $id ) {

	//$tb = TaskBot_Base::get( $id );

	$tasks = get_site_option( 'taskbot_tasks' );

	if ( isset( $tasks[ $id ] ) ) {
		return $tasks[ $id ];
	}

	return;
}

function taskbot_get_task_data( $post_id ) {

	$meta = get_post_meta( $post_id );
	$data = array();

	if ( $meta ) {

		foreach ( $meta as $key => $value ) {
			if ( stristr( $key, '_taskbot_' ) !== false ) {
				$data[ $key ] = $value[0];
			}
		}

	}

	// $tb = TaskBot_Base::get( $id );
	return $data;
}
