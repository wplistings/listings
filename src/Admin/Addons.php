<?php

namespace Listings\Admin;

class Addons {

	/**
	 * Handles output of the reports page in admin.
	 */
	public function output() {

		if ( false === ( $addons = get_transient( 'listings_addons_html' ) ) ) {

			$raw_addons = wp_remote_get(
				'https://wpjobmanager.com/add-ons/',
				array(
					'timeout'     => 10,
					'redirection' => 5,
					'sslverify'   => false
				)
			);

			if ( ! is_wp_error( $raw_addons ) ) {

				$raw_addons = wp_remote_retrieve_body( $raw_addons );

				// Get Products
				$dom = new \DOMDocument();
				libxml_use_internal_errors(true);
				$dom->loadHTML( $raw_addons );

				$xpath  = new \DOMXPath( $dom );
				$tags   = $xpath->query('//ul[@class="products"]');
				foreach ( $tags as $tag ) {
					$addons = $tag->ownerDocument->saveXML( $tag );
					break;
				}

				$addons = wp_kses_post( $addons );

				if ( $addons ) {
					set_transient( 'listings_addons_html', $addons, 60*60*24*7 ); // Cached for a week
				}
			}
		}

		?>
		<div class="wrap wp_job_manager wp_job_manager_addons_wrap">
			<h2><?php _e( 'WP Job Manager Add-ons', 'listings' ); ?></h2>

			<div id="job-manager-addons-banner" class="notice updated below-h2"><strong><?php _e( 'Do you need multiple add-ons?', 'wp-job-manager' ); ?></strong> <a href="https://wpjobmanager.com/add-ons/bundle/" class="button"><?php _e( 'Check out the core add-on bundle &rarr;', 'wp-job-manager' ); ?></a></div>

			<?php echo $addons; ?>
		</div>
		<?php
	}
}