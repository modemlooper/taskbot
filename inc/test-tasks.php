<?php

function test_tasks() {

	$tb = taskbot_new_task( array(
		'id' => 'test_task',
		'title' => 'Test Task',
		'description' => 'This is the best',
		'fields' => array(
			array(
				'id' => 'my_field',
				'name' => 'My field',
				'type' => 'text',
			),
		),
	) );

	$tb2 = taskbot_new_task( array(
		'id' => 'test_task_r',
		'title' => 'Test Task 2',
		'description' => 'This is the best 2',
	) );

}
add_action( 'taskbot_init', 'test_tasks' );


function run_my_task( $task_args ) {
	//tb_error_log( taskbot_get_tasks_by_id( $task_args['task'] ) );
	tb_error_log( taskbot_get_task_data( $task_args['post_id'] ) );


}
add_action( 'taskbot_run_test_task', 'run_my_task' );

function get_tasks() {
	$tasks = taskbot_get_all_tasks();
	//tb_error_log( $tasks['test_task']->task );
}
// add_action( 'taskbot_init', 'get_tasks' );

function tb_error_log( $data ) {
	error_log( print_r( $data, true ) );
}
