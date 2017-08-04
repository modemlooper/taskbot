<?php
/*
Plugin Name: BP Add Users to Groups
Description: This plug-in is intended to assist developers in adding bulk users to groups.
Version: 1.0
License: (GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html)
Author: modemlooper
Author URI: http://twitter.com/modemlooper
*/


function bp_add_users_to_groups_init() {
	include( dirname( __FILE__ ) . '/includes/add-users.php' );
}
add_action( 'bp_include', 'bp_add_users_to_groups_init' );
