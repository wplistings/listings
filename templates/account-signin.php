<?php if ( is_user_logged_in() ) : ?>

	<fieldset>
		<label><?php _e( 'Your account', 'listings' ); ?></label>
		<div class="field account-sign-in">
			<?php
				$user = wp_get_current_user();
				printf( __( 'You are currently signed in as <strong>%s</strong>.', 'listings' ), $user->user_login );
			?>

			<a class="button" href="<?php echo apply_filters( 'listings_submit_form_logout_url', wp_logout_url( get_permalink() ) ); ?>"><?php _e( 'Sign out', 'listings' ); ?></a>
		</div>
	</fieldset>

<?php else :

	$account_required             = listings_user_requires_account();
	$registration_enabled         = listings_enable_registration();
	$generate_username_from_email = listings_generate_username_from_email();
	?>
	<fieldset>
		<label><?php _e( 'Have an account?', 'listings' ); ?></label>
		<div class="field account-sign-in">
			<a class="button" href="<?php echo apply_filters( 'listings_submit_form_login_url', wp_login_url( get_permalink() ) ); ?>"><?php _e( 'Sign in', 'listings' ); ?></a>

			<?php if ( $registration_enabled ) : ?>

				<?php printf( __( 'If you don&rsquo;t have an account you can %screate one below by entering your email address/username. Your account details will be confirmed via email.', 'listings' ), $account_required ? '' : __( 'optionally', 'listings' ) . ' ' ); ?>

			<?php elseif ( $account_required ) : ?>

				<?php echo apply_filters( 'listings_submit_form_login_required_message',  __('You must sign in to create a new listing.', 'listings' ) ); ?>

			<?php endif; ?>
		</div>
	</fieldset>
	<?php if ( $registration_enabled ) : ?>
		<?php if ( ! $generate_username_from_email ) : ?>
			<fieldset>
				<label><?php _e( 'Username', 'listings' ); ?> <?php echo apply_filters( 'listings_submit_form_required_label', ( ! $account_required ) ? ' <small>' . __( '(optional)', 'listings' ) . '</small>' : '' ); ?></label>
				<div class="field">
					<input type="text" class="input-text" name="create_account_username" id="account_username" value="<?php echo empty( $_POST['create_account_username'] ) ? '' : esc_attr( sanitize_text_field( stripslashes( $_POST['create_account_username'] ) ) ); ?>" />
				</div>
			</fieldset>
		<?php endif; ?>
		<fieldset>
			<label><?php _e( 'Your email', 'listings' ); ?> <?php echo apply_filters( 'listings_submit_form_required_label', ( ! $account_required ) ? ' <small>' . __( '(optional)', 'listings' ) . '</small>' : '' ); ?></label>
			<div class="field">
				<input type="email" class="input-text" name="create_account_email" id="account_email" placeholder="<?php esc_attr_e( 'you@yourdomain.com', 'listings' ); ?>" value="<?php echo empty( $_POST['create_account_email'] ) ? '' : esc_attr( sanitize_text_field( stripslashes( $_POST['create_account_email'] ) ) ); ?>" />
			</div>
		</fieldset>
		<?php do_action( 'listings_register_form' ); ?>
	<?php endif; ?>

<?php endif; ?>
