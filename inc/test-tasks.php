<?php

function test_tasks() {

	$tb = taskbot_new_task( array(
		'id' => 'test_task',
		'title' => 'Test Task',
		'description' => 'This is the best',
		'data' => array( 'item' => 1 ),
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
		'data' => array( 'item' => 1 ),
		'fields' => array(
			array(
				'id' => 'my_field_2',
				'name' => 'My field',
				'type' => 'text',
			),
		),
	) );

}
add_action( 'taskbot_init', 'test_tasks' );

function add_my_task( $task ) {
	tb_error_log( $task );
}
add_action( 'taskbot_add_test_task', 'add_my_task' );


function tb_error_log( $data ) {
	error_log( print_r( $data, true ) );
}
