<?php
/**
 * TaskBot Helper Functions
 *
 * @package TaskBot
 */


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

function tb_error_log( $data ) {
	error_log( print_r( $data, true ) );
}
