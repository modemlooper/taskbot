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

	//tb_error_log( taskbot_get_task_metadata( 27 ) );
	//tb_error_log( $task );

	$user_query = new WP_User_Query( array( 'role' => 'Subscriber' ) );

	foreach ( $user_query->results as $item ) {
		taskbot()->batch->push_to_queue( $item->ID );
	}

	taskbot()->batch->save()->dispatch();

	//tb_error_log( $user_query->results );
}
add_action( 'taskbot_add_test_task', 'add_my_task' );

function my_batch_task( $tb_id, $item ) {
	tb_error_log($item);
}
add_action( 'taskbot_run_test_task', 'my_batch_task', 10, 2 );

function tb_error_log( $data ) {
	error_log( print_r( $data, true ) );
}
