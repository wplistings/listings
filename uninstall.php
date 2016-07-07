<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

wp_clear_scheduled_hook( 'listings_delete_old_previews' );
wp_clear_scheduled_hook( 'listings_check_for_expired_jobs' );

$options = array(
	'listings_version',
	'listings_per_page',
);

foreach ( $options as $option ) {
	delete_option( $option );
}