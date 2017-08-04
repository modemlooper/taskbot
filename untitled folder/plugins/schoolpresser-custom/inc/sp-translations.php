<?php

/**
 * Change text strings.
 *
 * @link http://codex.wordpress.org/Plugin_API/Filter_Reference/gettext
 */
function bp_docs_change_text_strings( $translated_text, $text, $domain ) {

	if ( ! bp_is_current_component( 'groups' )  ) {
		return $translated_text;
	}

	if ( 'buddypress' === $domain || 'boss' === $domain ) {

		$action = bp_current_action();
		$action_vars = bp_action_variables();

		$type = 'groups';

		if ( 'create' !== $action ) {
			global $groups_template;

			$group =& $groups_template->group;

			$group_type = isset( $group ) ? bp_groups_get_group_type( $group->id ) : false;

			$type = $group_type ? $group_type . 's' :  'groups';
		}

		if ( 'type' === $action ) {
				$type = isset( $action_vars[0] ) ? $action_vars[0] : 'groups';
		}

		switch ( $translated_text ) {

			case 'Group: Extra Links' :
				$translated_text = sprintf( __( 'Sections: Extra Links', 'schoolpresser_docs' ), ucfirst( $type ) );
			break;
			case 'Create a Group' :
				$translated_text = sprintf( __( 'Create a %s', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'Created a group' :
				$translated_text = sprintf( __( 'Created a %s', 'schoolpresser_docs' ), substr( $type, 0, -1 ) );
			break;
			case 'Leave Group' :
				$translated_text = sprintf( __( 'Leave %s', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'Join Group' :
				$translated_text = sprintf( __( 'Join %s', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'Public Group' :
				$translated_text = sprintf( __( 'Public %s', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'Private Group' :
				$translated_text = sprintf( __( 'Private %s', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'Hidden Group' :
				$translated_text = sprintf( __( 'Hidden %s', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'A dynamic list of recently active, popular, newest, or alphabetical groups' :
				$translated_text = sprintf( __( 'A dynamic list of recently active, popular, newest, or alphabetical %s', 'schoolpresser_docs' ), $type );
			break;
			case 'New Groups' :
				$translated_text = sprintf( __( 'New %s', 'schoolpresser_docs' ), ucfirst( $type ) );
			break;
			case 'Search Groups' :
				$translated_text = sprintf( __( 'Search %s...', 'schoolpresser_docs' ), ucfirst( $type ) );
			break;
			case 'Joined a group' :
				$translated_text = sprintf( __( 'Joined a %s', 'schoolpresser_docs' ), substr( $type, 0, -1 ) );
			break;
			case 'Group Memberships' :
				$translated_text = sprintf( __( '%s Memberships', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'Group details edited' :
				$translated_text = sprintf( __( '%s details edited', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'Group Updates' :
				$translated_text = sprintf( __( '%s Updates', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'Groups' :
				$translated_text = sprintf( __( '%s', 'schoolpresser_docs' ), ucfirst( $type ) );
			break;
			case 'All Groups <span>%s</span>' :
				$translated_text = sprintf( __( 'All %s <span>%s</span>', 'schoolpresser_docs' ), ucfirst( $type ), '%s' );
			break;
			case 'My Groups <span>%s</span>' :
				$translated_text = sprintf( __( 'My %s <span>%s</span>', 'schoolpresser_docs' ), ucfirst( $type ), '%s' );
			break;
			case 'Group logo of %s' :
				$translated_text = sprintf( __( '%s logo of %s', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ), '%s' );
			break;
			case 'Groups Settings' :
				$translated_text = sprintf( __( '%s Settings', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'Group Creation' :
				$translated_text = sprintf( __( '%s Creation', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'Group Photo Uploads' :
				$translated_text = sprintf( __( '%s Photo Uploads', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'Group Cover Image Uploads' :
				$translated_text = sprintf( __( 'Section Cover Image Uploads', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'Are you sure you want to leave this group?' :
				$translated_text = sprintf( __( 'Are you sure you want to leave this %s?', 'schoolpresser_docs' ), substr( $type, 0, -1 ) );
			break;
			case '(BuddyPress) Groups' :
				$translated_text = sprintf( __( '(BuddyPress) %s', 'schoolpresser_docs' ), ucfirst( $type ) );
			break;
			case 'Group Name (required)' :
				$translated_text = sprintf( __( '%s Name (required)', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'Group Description (required)' :
				$translated_text = sprintf( __( '%s Description (required)', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'Groups' :
				$translated_text = sprintf( __( '%s', 'schoolpresser_docs' ), ucfirst( $type ) );
			break;
			case 'Create Group and Continue' :
				$translated_text = sprintf( __( 'Create %s and Continue', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'This is a public group' :
				$translated_text = sprintf( __( 'This is a public %s', 'schoolpresser_docs' ), substr( $type, 0, -1 ) );
			break;
			case 'This is a private group' :
				$translated_text = sprintf( __( 'This is a private %s', 'schoolpresser_docs' ), substr( $type, 0, -1 ) );
			break;
			case 'This is a hidden group' :
				$translated_text = sprintf( __( 'This is a hidden %s', 'schoolpresser_docs' ), substr( $type, 0, -1 ) );
			break;
			case 'Group Invitations' :
				$translated_text = sprintf( __( '%s Invitations', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'Which members of this group are allowed to invite others?' :
				$translated_text = sprintf( __( 'Which members of this %s are allowed to invite others?', 'schoolpresser_docs' ), substr( $type, 0, -1 ) );
			break;
			case 'Any site member can join this group.' :
				$translated_text = sprintf( __( 'Any site member can join this %s.', 'schoolpresser_docs' ), substr( $type, 0, -1 ) );
			break;
			case 'This group will be listed in the group directory and in search results.' :
				$translated_text = sprintf( __( 'This section will be listed in the %s directory and in search results.', 'schoolpresser_docs' ), substr( $type, 0, -1 ) );
			break;
			case 'Group content and activity will be visible to any site member.' :
				$translated_text = sprintf( __( '%s content and activity will be visible to any site member.', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'Only users who request membership and are accepted can join the group.' :
				$translated_text = sprintf( __( 'Only users who request membership and are accepted can join the %s.', 'schoolpresser_docs' ), substr( $type, 0, -1 ) );
			break;
			case 'This group will be listed in the groups directory and in search results.' :
				$translated_text = sprintf( __( 'This section will be listed in the section directory and in %s results.', 'schoolpresser_docs' ), substr( $type, 0, -1 ) );
			break;
			case 'Group content and activity will only be visible to members of the group.' :
				$translated_text = sprintf( __( '%s content and activity will only be visible to members of the section.', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;

			case 'Only users who are invited can join the group.' :
				$translated_text = sprintf( __( 'Only users who are invited can join the %s.', 'schoolpresser_docs' ), substr( $type, 0, -1 ) );
			break;
			case 'This group will not be listed in the groups directory or search results.' :
				$translated_text = sprintf( __( 'This section will not be listed in the %s directory or search results.', 'schoolpresser_docs' ), substr( $type, 0, -1 ) );
			break;
			case 'All group members' :
				$translated_text = sprintf( __( 'All %s members', 'schoolpresser_docs' ), substr( $type, 0, -1 ) );
			break;
			case 'Group admins and mods only' :
				$translated_text = sprintf( __( '%s admins and mods only', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'Group admins only' :
				$translated_text = sprintf( __( '%s admins only', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'Upload an image to use as a profile photo for this group. The image will be shown on the main group page, and in search results.' :
				$translated_text = sprintf( __( 'Upload an image to use as a profile photo for this %s. The image will be shown on the main section page, and in %s results.', 'schoolpresser_docs' ), substr( $type, 0, -1 ) );
			break;
			case 'To skip the group profile photo upload process, hit the "Next Step" button.' :
				$translated_text = sprintf( __( 'To skip the %s profile photo upload process, hit the "Next Step" button.', 'schoolpresser_docs' ), substr( $type, 0, -1 ) );
			break;
			case 'The Cover Image will be used to customize the header of your group.' :
				$translated_text = sprintf( __( 'The Cover Image will be used to customize the header of your %s.', 'schoolpresser_docs' ), substr( $type, 0, -1 ) );
			break;
			case 'Group' :
				$translated_text = sprintf( __( '%s', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'Group Info' :
				$translated_text = sprintf( __( '%s Info', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'Group Admins' :
				$translated_text = sprintf( __( '%s Admins', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'Group Mods' :
				$translated_text = sprintf( __( '%s Mods', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'Group Activity' :
				$translated_text = sprintf( __( '%s Activity', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case '%1$s created the group %2$s' :
				$translated_text = sprintf( __( '%1$s created the %3s %2$s', 'schoolpresser_docs' ),'%1$s', '%2$s', substr( $type, 0, -1 ) );
			break;
			case 'Viewing 1 group' :
				$translated_text = sprintf( __( 'Viewing 1 %s', 'schoolpresser_docs' ), substr( $type, 0, -1 ) );
			break;
			case 'There were no groups found.' :
				$translated_text = sprintf( __( 'There were no %s found.', 'schoolpresser_docs' ), $type );
			break;
			case 'Notify group members of these changes via email' :
				$translated_text = sprintf( __( 'Notify %s members of these changes via email', 'schoolpresser_docs' ), substr( $type, 0, -1 ) );
			break;
			case 'WARNING: Deleting this group will completely remove ALL content associated with it. There is no way back, please be careful with this option.' :
				$translated_text = sprintf( __( 'WARNING: Deleting this %s will completely remove ALL content associated with it. There is no way back, please be careful with this option.', 'schoolpresser_docs' ), substr( $type, 0, -1 ) );
			break;
			case 'I understand the consequences of deleting this group.' :
				$translated_text = sprintf( __( 'I understand the consequences of deleting this %s.', 'schoolpresser_docs' ), substr( $type, 0, -1 ) );
			break;
			case 'Delete Group' :
				$translated_text = sprintf( __( 'Delete %s', 'schoolpresser_docs' ), substr( $type, 0, -1 ) );
			break;
			case 'Group Website' :
				$translated_text = sprintf( __( '%s Website', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'Group Identifier' :
				$translated_text = sprintf( __( '%s Identifier', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'Kick &amp; Ban' :
				$translated_text = sprintf( __( 'Block Member', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
			case 'This is a private group and you must request group membership in order to join.' :
				$translated_text = sprintf( __( 'Please contact the Membership Department to join at membership@apsanet.org.', 'schoolpresser_docs' ), ucfirst( substr( $type, 0, -1 ) ) );
			break;
		}
	}

	return $translated_text;
}
add_filter( 'gettext', 'bp_docs_change_text_strings', 20, 3 );


/**
 * Change text strings.
 *
 * @link http://codex.wordpress.org/Plugin_API/Filter_Reference/gettext
 */
function bp_docs_change_ngettext_strings( $translated_text, $single, $plural, $number, $domain ) {

	if ( 'buddypress' === $domain ) {

		$component = bp_current_component();
		$action = bp_current_action();
		$action_vars = bp_action_variables();

		if ( 'type' !== $action && empty( $action_vars ) && ! bp_is_group() ) {
			return $translated_text;
		}

		$type = isset( $action_vars[0] ) ? $action_vars[0] : 'groups';

		switch ( $translated_text ) {
			case 'Viewing %1$s - %2$s of %3$s groups' :
				$translated_text = sprintf( __( 'Viewing %1$s - %2$s of %3$s %4$s', 'schoolpresser_docs' ), '%1$s', '%2$s', '%3$s', $type );
			break;
		}
	}

	return $translated_text;
}
add_filter( 'ngettext', 'bp_docs_change_ngettext_strings', 10, 5 );


/**
 * [bp_docs_translate_search_form description]
 *
 * @param  [type] $default_text [description]
 * @param  [type] $component    [description]
 * @return [type]               [description]
 */
function bp_docs_translate_search_form( $default_text, $component ) {

	if ( ! bp_is_directory() ) {
		return $default_text;
	}

	$component = bp_current_component();
	$action = bp_current_action();
	$action_vars = bp_action_variables();

	if ( 'type' !== $action && empty( $action_vars ) && ! bp_is_group() ) {
		return $default_text;
	}

	$type = isset( $action_vars[0] ) ? $action_vars[0] : 'groups';

	return sprintf( __( 'Search %s...', 'schoolpresser_docs' ), ucfirst( $type ) );
}
add_filter( 'bp_get_search_default_text', 'bp_docs_translate_search_form', 10, 2 );
