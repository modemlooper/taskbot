<?php

function taskbot_get_groups() {

	$args['meta_query'] = array(
		array(
			'key'     => 'apsa_group_identifier',
			'compare' => 'EXISTS',
		),
	);

	$args['show_hidden'] = true;
	$args['per_page'] = -1;

	$group = groups_get_groups( $args );

	if ( isset( $group['groups'] ) && ! empty( $group['groups'] ) ) {

		$groups = array();

		foreach ( $group['groups'] as $group ) {
			$groups[ $group->id ] = $group->name;
		}

		return $groups;
	}

	return;

}
