<?php
/**
 * This template is used to display the login form with [apsa_sso_login]
 */
if ( ! is_user_logged_in() ) :
	global $apsa_sso_login_redirect;

	// Show any error messages after form submission
	apsa_sso_print_errors(); ?>
	<form id="apsa_sso_login_form" class="apsa_sso_form" action="<?php //echo esc_url( site_url( 'wp-login.php?wpe-login=apsa', 'login_post' ) ); ?>" method="post">
		<fieldset>
			<?php do_action( 'apsa_sso_login_fields_before' ); ?>
			<p class="apsa-sso-login-username">
				<label for="apsa_sso_user_login"><?php _e( 'Username', 'apsa-sso' ); ?></label>
				<input name="apsa_sso_user_login" id="apsa_sso_user_login" class="apsa-sso-required apsa-sso-input" type="text"/>
			</p>
			<p class="apsa-sso-login-password">
				<label for="apsa_sso_user_pass"><?php _e( 'Password', 'apsa-sso' ); ?></label>
				<input name="apsa_sso_user_pass" id="apsa_sso_user_pass" class="apsa-sso-password apsa-sso-required apsa-sso-input" type="password"/>
			</p>
			<p class="apsa-sso-login-remember">
				<label><input name="rememberme" type="checkbox" id="rememberme" value="forever" /> <?php _e( 'Remember Me', 'apsa-sso' ); ?></label>
			</p>
			<p class="apsa-sso-login-submit">
				<input type="hidden" name="apsa_sso_redirect" value="<?php echo esc_url( $apsa_sso_login_redirect ); ?>"/>
				<input type="hidden" name="apsa_sso_login_nonce" value="<?php echo wp_create_nonce( 'apsa-sso-login-nonce' ); ?>"/>
				<input type="hidden" name="apsa_sso_action" value="user_login"/>
				<input id="apsa_sso_login_submit" type="submit" class="apsa_sso_submit apsa-sso-submit" value="<?php _e( 'Log In', 'apsa-sso' ); ?>"/>
			</p>
			<p class="apsa-sso-lost-password">
				<a href="https://www.apsanet.org/Sign-In/ctrl/SendPassword?returnurl=%2fUser-Home" target="_blank">
					<?php _e( 'Lost Password?', 'apsa-sso' ); ?>
				</a>
			</p>
			<?php do_action( 'apsa_sso_login_fields_after' ); ?>
		</fieldset>
	</form>
<?php else : ?>
	<p class="apsa-sso-logged-in"><?php _e( 'You are already logged in', 'apsa-sso' ); ?></p>
<?php endif; ?>
