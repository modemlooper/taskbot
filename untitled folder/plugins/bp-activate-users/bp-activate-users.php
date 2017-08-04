<?php
/*
Plugin Name: BP Activate Users
Description: This plug-in is intended to assist developers in making sure all previous wordpress users have been correctly pulled into Buddypress.
Version: 1.0
License: (GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html)
Author: modemlooper
Author URI: http://twitter.com/modemlooper
*/


function bp_activate_users_init() {
	include( dirname( __FILE__ ) . '/includes/activate-users.php' );
}
add_action( 'bp_include', 'bp_activate_users_init' );
