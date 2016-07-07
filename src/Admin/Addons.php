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
		<div class="wrap listings listings_addons_wrap">
			<h2><?php _e( 'Listings Add-ons', 'listings' ); ?></h2>

			<?php echo $addons; ?>
		</div>
		<?php
	}
}