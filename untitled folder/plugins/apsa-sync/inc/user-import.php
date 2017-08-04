<?php
/**
 * Import users from API.
 *
 * @package         APSA_USER_IMPORT
 * @author          AplhaWeb
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
		exit;
}


if ( ! class_exists( 'APSA_USER_IMPORT' ) ) {

	/**
	 * Main APSA_USER_IMPORT class
	 *
	 * @since       1.0.0
	 */

	class APSA_USER_IMPORT {

		/**
		 * @var         APSA_USER_IMPORT $instance
		 * @since       1.0.0
		 */
		private static $instance;

		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      object self::$instance The one true APSA_USER_IMPORT
		 */
		public static function instance() {
			if ( ! self::$instance ) {
				self::$instance = new APSA_USER_IMPORT();
				self::$instance->init();
			}

			return self::$instance;
		}

		public function init() {
			add_action( 'admin_menu', array( $this, 'user_import_setup_menu' ) );

			wp_enqueue_script( 'wp-api' );
		}

		public function user_import_setup_menu() {
				add_submenu_page( 'users.php', 'Import Users Page', 'Import Users', 'manage_options', 'import-users', array( $this, 'page' ) );
		}

		public function page() {
			?>
			<div class='wrap'>
				<h2>Import User</h2>

				<p>Enter a member ID and click search</p>

				<div id="user-import-form-wrap">
					<form action="" method="post" id="user-import-form" name="import-form">

						<input type="text" id="user-id" name="user-id" value=""/>
						<input type="submit" id="user-search-submit" class="button button-primary button-small" name="user-search-submit" value="Search"/>
						<div class="spinner"></div>

					</form>
					<div class="api-response"></div>
				</div>

			</div>

			<style>
				#user-import-form-wrap {
					width: 300px;
				}

				input#user-id {
					margin-bottom: 10px;
				}

				#import-user-sumbit, #sync-user-sumbit {
					margin-bottom: 10px;
				}

				input#import-user-force {
					margin: 0 0 0 10px;
				}

			</style>

			<script>
				window.apsa_user_import = {};
				( function( window, $, that ) {

					// Constructor.
					that.init = function() {
						that.cache();
						that.bindEvents();
					}

					// Cache all the things.
					that.cache = function() {
						that.$c = {
							window: $( window ),
							body: $( 'body' ),
						};
					}

					// Combine all events.
					that.bindEvents = function() {

					}

					$(document).ready(function() {

						var member_id = '';

						$('#user-import-form').submit(function(e){

							e.preventDefault();
							var id = $('#user-id').val();
							var isnum = /^\d+$/.test(id);

							if ( ! id || ! isnum ) {
								alert('Only numbers are allowed.');
								return;
							}

							$("#user-import-form-wrap .spinner").css("visibility","visible");
							$('.api-response').html('');
							member_id = id;

							$.ajaxSetup({ headers: { 'X-WP-Nonce': wpApiSettings.nonce } });

							$.ajax( {
								 url: wpApiSettings.root + 'user-import/v1/lookup/' + id,
								 dataType: "json",
								 success: function( data ) {

									 if ( "exists" === data.response ) {
										 $html = data.data;
										 $html += '<button id="sync-user-sumbit" class="button button-primary button-small">Sync Data</button><div class="spinner"></div>'

										 $('.api-response').html($html);
									 } else if ( "new" === data.response ) {

										 $html = '<div id="message" class="updated notice notice-message"><p>User found.</p></div>';
										 $html += '<p>';
										 $html += 'First Name: ' + data.data.Member.FirstName;
										 $html += '</br>';
										 $html += 'Last Name: ' + data.data.Member.LastName;
										 $html += '</br>';
										 $html += 'Email: ' + data.data.Member.Email;
										 $html += '</br>';
										 $html += 'Member Key: ' + data.data.Member.MemberKey;
										 $html += '</br>';
										 $html += 'Username: ' + data.data.Member.Username;
										 $html += '</br>';

										 var exp = ( data.data.Member.MembExpire ) ? data.data.Member.MembExpire.split('T') : ['no expritation'];
										 $html += 'Expiration: ' + exp[0];

										 $html += '</p>';
										 $html += '<button id="import-user-sumbit" class="button button-primary button-small">Import User</button><input type="checkbox" id="import-user-force" value="1"/> force import<div class="spinner"></div>';

										 $('.api-response').html($html);
										 //console.log(data.data);

									 } else {
										 $('.api-response').html('<div id="message" class="updated error notice-error"><p>No User found with this ID.</p></div>');
									 }

									 $("#user-import-form-wrap .spinner").css("visibility","hidden");
								 }
							});
						});

						var running = false;

						$('body').on( 'click', '#import-user-sumbit', function(e){
							e.preventDefault();

							if ( false === running ) {
								running = true;

								$(".api-response .spinner").css("visibility","visible");
								$('.message-created').remove();

								var force = $('#import-user-force').is(':checked') ? '?force=' + $('#import-user-force').val() : '';

								$.ajaxSetup({ headers: { 'X-WP-Nonce': wpApiSettings.nonce } });

								$.ajax( {
									 url: wpApiSettings.root + 'user-import/v1/create/' + member_id + force,
									 dataType: "json",
									 success: function( data ) {
										 console.log(data);
										 running = false;
										 var $html = data.data;
										 $('.api-response').append($html);
										 $(".api-response .spinner").css("visibility","hidden");
									 }
								});

							}

						});

						$('body').on( 'click', '#sync-user-sumbit', function(e){
							e.preventDefault();
							$('.api-response .spinner').css('visibility','visible');
							$('.message-synced').remove();

							$.ajaxSetup({ headers: { 'X-WP-Nonce': wpApiSettings.nonce } });

							$.ajax( {
								 url: wpApiSettings.root + 'user-import/v1/sync/' + member_id,
								 dataType: "json",
								 success: function( data ) {
									 var $html = '<div id="message" class="updated notice notice-message message-synced"><p>User synced.   <a target="_blank" href="' + data.data + '">view profile</a></p></div>';
									 $('.api-response').append($html);
									 $(".api-response .spinner").css("visibility","hidden");
								 }
							});
						});

					});

					// Engage!
					$( that.init );

				})( window, jQuery, window.apsa_user_import );
			</script>


			<?php
		}

	}
}

/**
 * The main function responsible for returning the one true APSA_USER_IMPORT
 * instance to functions everywhere.
 *
 * @since       1.0.0
 * @return      APSA_USER_IMPORT
 */
function apsa_user_import() {
	return APSA_USER_IMPORT::instance();
}
add_action( 'init', 'apsa_user_import' );
