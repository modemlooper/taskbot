<?php
/**
 * Bulk sync users from API.
 *
 * @package         APSA_BULK_SYNC
 * @author          AplhaWeb
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
		exit;
}


if ( ! class_exists( 'APSA_BULK_SYNC' ) ) {

	/**
	 * Main APSA_BULK_SYNC class.
	 *
	 * @since       1.0.0
	 */

	class APSA_BULK_SYNC {

		/**
		 * @var         APSA_BULK_SYNC $instance The one true APSA_BULK_SYNC
		 * @since       1.0.0
		 */
		private static $instance;

		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      object self::$instance The one true APSA_BULK_SYNC
		 */
		public static function instance() {
			if ( ! self::$instance ) {
				self::$instance = new APSA_BULK_SYNC();
				self::$instance->init();
			}

			return self::$instance;
		}

		public function init() {
			add_action( 'admin_menu', array( $this, 'user_sync_setup_menu' ) );
		}

		public function user_sync_setup_menu() {
				add_submenu_page( 'tools.php', 'Bulk Sync Page', 'Bulk Sync', 'manage_options', 'bulk-sync', array( $this, 'page' ) );
		}

		public function page() {
			?>
			<style>
				span.run-notice {
					color: white;
					background: green;
					padding: 4px 6px;
					border-radius: 4px;
					font-size: 12px;
					margin: 0 10px;
				}

			</style>

			<div class='wrap'>
				<h2>Bulk Sync</h2>

				<p>Bulk Sync runs every night to update user data. You do not need to run this tool unless an update needs to be forced.</p>

				<hr></hr>
				<h2>Sync Proccesses

				<?php
				if ( is_bulk_sync_in_progress() ) {
					echo '<span class="run-notice">currently running</span>';
				}
					?>
				 </h2>
				<table class="widefat striped">
						<thead>
							<tr>
								<th scope="col">Name</th>
								<th scope="col">Actions</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>Sync Updated Users</td>
								<td>
									<input type="button" onclick="location.href='<?php echo wp_nonce_url( admin_url( 'tools.php?page=bulk-sync&process=updated' ), 'process' ); ?>';" value="start" class="button button-primary button-small" <?php sync_is_disabled(); ?>/>
									<input type="button" onclick="location.href='<?php echo wp_nonce_url( admin_url( 'tools.php?page=bulk-sync&process=stop' ), 'process' ); ?>';" value="stop" class="button button-primary button-small"/>
								</td>
							</tr>
							<tr>
								<td>Sync All Imported Users ( WARNING: see below. )</td>
								<td>
									<input type="button" onclick="location.href='<?php echo wp_nonce_url( admin_url( 'tools.php?page=bulk-sync&process=all' ), 'process' ); ?>';" value="start" class="button button-primary button-small" <?php sync_is_disabled(); ?>/>
									<input type="button" onclick="location.href='<?php echo wp_nonce_url( admin_url( 'tools.php?page=bulk-sync&process=stop' ), 'process' ); ?>';" value="stop" class="button button-primary button-small"/>
								</td>
							</tr>
							<tr>
								<td>Subscribe Group Members to Email Digest</td>
								<td>
									<input type="button" onclick="location.href='<?php echo wp_nonce_url( admin_url( 'tools.php?page=bulk-sync&process=email-digest' ), 'process' ); ?>';" value="start" class="button button-primary button-small" <?php sync_is_disabled(); ?>/>
									<input type="button" onclick="location.href='<?php echo wp_nonce_url( admin_url( 'tools.php?page=bulk-sync&process=stop' ), 'process' ); ?>';" value="stop" class="button button-primary button-small"/>
								</td>
							</tr>
							<tr>
								<td>Subscribe All Members Group to Email Digest</td>
								<td>
									<input type="button" onclick="location.href='<?php echo wp_nonce_url( admin_url( 'tools.php?page=bulk-sync&process=all-members-email-digest' ), 'process' ); ?>';" value="start" class="button button-primary button-small" <?php sync_is_disabled(); ?>/>
									<input type="button" onclick="location.href='<?php echo wp_nonce_url( admin_url( 'tools.php?page=bulk-sync&process=stop' ), 'process' ); ?>';" value="stop" class="button button-primary button-small"/>
								</td>
							</tr>
						</tbody>
				</table>

			</p> * Bulk syncing all users is server intensive and should not be done unless absolutely neccesary.
				You can force a bulk sync of All users by clicking the start button. Once a process has started it will run until all members are updated.
				You can abort the sync process by clicking the stop button. Restarting will start the entire process over and sync all users.
				 Only one process can be run at a time.
			</p>
			<p>If you need to sync a specific user, visit the User menu item in the admin, select a user, then choose Sync Data from the bulk actions.</p>

			</div>

			<?php

			sync_get_current_batch();
		}


	}
}


function is_bulk_sync_in_progress() {
	$is_sync = get_site_option( 'wp_sync_process_current' );
	return $is_sync;
}

function sync_is_disabled() {
	$disabled = is_bulk_sync_in_progress() ? 'disabled' : '';
	echo $disabled;
}

function sync_get_current_batch() {

	$current_key = get_site_option( 'wp_sync_process_current' );

	$current = get_site_option( $current_key );

	if ( $current ) {
		echo count( $current );
	}

}

/**
 * The main function responsible for returning the one true APSA_BULK_SYNC
 * instance to functions everywhere.
 *
 * @since       1.0.0
 * @return      APSA_BULK_SYNC
 */
function APSA_BULK_SYNC() {
	return APSA_BULK_SYNC::instance();
}
add_action( 'init', 'APSA_BULK_SYNC' );
