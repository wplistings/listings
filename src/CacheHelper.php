<?php

namespace Listings;

class CacheHelper {

	public static function init() {
		add_action( 'save_post', array( __CLASS__, 'flush_get_listings_cache' ) );
		add_action( 'listings_my_listing_do_action', array( __CLASS__, 'my_listing_do_action' ) );
		add_action( 'set_object_terms', array( __CLASS__, 'set_term' ), 10, 4 );
		add_action( 'edited_term', array( __CLASS__, 'edited_term' ), 10, 3 );
		add_action( 'create_term', array( __CLASS__, 'edited_term' ), 10, 3 );
		add_action( 'delete_term', array( __CLASS__, 'edited_term' ), 10, 3 );
		add_action( 'listings_clear_expired_transients', array( __CLASS__, 'clear_expired_transients' ), 10 );
	}

	/**
	 * Flush the cache
	 */
	public static function flush_get_listings_cache( $post_id ) {
		$post_types = apply_filters( 'listings_cacheable_post_types', array( 'listing' ) );

		if ( in_array( get_post_type( $post_id ), $post_types ) ) {
			self::get_transient_version( 'get_listings', true );
		}
	}

	/**
	 * Flush the cache
	 */
	public static function my_listing_do_action( $action ) {
		if ( 'mark_filled' === $action || 'mark_not_filled' === $action ) {
			self::get_transient_version( 'get_listings', true );
		}
	}

	/**
	 * When any post has a term set
	 */
	public static function set_term( $object_id = '', $terms = '', $tt_ids = '', $taxonomy = '' ) {
		self::get_transient_version( 'listings_get_' . sanitize_text_field( $taxonomy ), true );
	}

	/**
	 * When any term is edited
	 */
	public static function edited_term( $term_id = '', $tt_id = '', $taxonomy = '' ) {
		self::get_transient_version( 'listings_get_' . sanitize_text_field( $taxonomy ), true );
	}

	/**
	 * Get transient version
	 *
	 * When using transients with unpredictable names, e.g. those containing an md5
	 * hash in the name, we need a way to invalidate them all at once.
	 *
	 * When using default WP transients we're able to do this with a DB query to
	 * delete transients manually.
	 *
	 * With external cache however, this isn't possible. Instead, this function is used
	 * to append a unique string (based on time()) to each transient. When transients
	 * are invalidated, the transient version will increment and data will be regenerated.
	 *
	 * @param  string  $group   Name for the group of transients we need to invalidate
	 * @param  boolean $refresh true to force a new version
	 * @return string transient version based on time(), 10 digits
	 */
	public static function get_transient_version( $group, $refresh = false ) {
		$transient_name  = $group . '-transient-version';
		$transient_value = get_transient( $transient_name );

		if ( false === $transient_value || true === $refresh ) {
			self::delete_version_transients( $transient_value );
			set_transient( $transient_name, $transient_value = time() );
		}
		return $transient_value;
	}

	/**
	 * When the transient version increases, this is used to remove all past transients to avoid filling the DB.
	 *
	 * Note; this only works on transients appended with the transient version, and when object caching is not being used.
	 */
	private static function delete_version_transients( $version ) {
		if ( ! wp_using_ext_object_cache() && ! empty( $version ) ) {
			global $wpdb;
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s;", "\_transient\_%" . $version ) );
		}
	}

    /**
	 * Clear expired transients
	 */
	public static function clear_expired_transients() {
		global $wpdb;

		if ( ! wp_using_ext_object_cache() && ! defined( 'WP_SETUP_CONFIG' ) && ! defined( 'WP_INSTALLING' ) ) {
			$sql   = "
				DELETE a, b FROM $wpdb->options a, $wpdb->options b
				WHERE a.option_name LIKE %s
				AND a.option_name NOT LIKE %s
				AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
				AND b.option_value < %s;";
			$wpdb->query( $wpdb->prepare( $sql, $wpdb->esc_like( '_transient_listings_' ) . '%', $wpdb->esc_like( '_transient_timeout_listings_' ) . '%', time() ) );
		}
	}
}