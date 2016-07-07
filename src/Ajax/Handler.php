<?php

namespace Listings\Ajax;

class Handler {
	/** @var string */
	public $ajax_prefix = '';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->ajax_prefix = 'listings_ajax_';

		add_action( 'init', array( __CLASS__, 'add_endpoint') );
		add_action( 'template_redirect', array( __CLASS__, 'do_listings_ajax'), 0 );
	}
	
	public function registerAction( Action $action ) {
		$action_string = $action->getActionString();
		
		add_action( $this->ajax_prefix . $action_string, array( $action, 'doAction' ) );
	}

	/**
	 * Add our endpoint for frontend ajax requests
	 */
	public static function add_endpoint() {
		add_rewrite_tag( '%listings-ajax%', '([^/]*)' );
		add_rewrite_rule( 'listings-ajax/([^/]*)/?', 'index.php?listings-ajax=$matches[1]', 'top' );
		add_rewrite_rule( 'index.php/listings-ajax/([^/]*)/?', 'index.php?listings-ajax=$matches[1]', 'top' );
	}

	/**
	 * Get Listings Ajax Endpoint
	 * @param  string $request Optional
	 * @param  string $ssl     Optional
	 * @return string
	 */
	public static function get_endpoint( $request = '%%endpoint%%', $ssl = null ) {
		if ( strstr( get_option( 'permalink_structure' ), '/index.php/' ) ) {
			$endpoint = trailingslashit( home_url( '/index.php/listings-ajax/' . $request . '/', 'relative' ) );
		} elseif ( get_option( 'permalink_structure' ) ) {
			$endpoint = trailingslashit( home_url( '/listings-ajax/' . $request . '/', 'relative' ) );
		} else {
			$endpoint = add_query_arg( 'listings-ajax', $request, trailingslashit( home_url( '', 'relative' ) ) );
		}
		return esc_url_raw( $endpoint );
	}

	/**
	 * Check for Listings Ajax request and fire action
	 */
	public static function do_listings_ajax() {
		/** @var $wp_query \WP_Query */
		global $wp_query;

		if ( ! empty( $_GET['listings-ajax'] ) ) {
			 $wp_query->set( 'listings-ajax', sanitize_text_field( $_GET['listings-ajax'] ) );
		}

   		if ( $action = $wp_query->get( 'listings-ajax' ) ) {
   			if ( ! defined( 'DOING_AJAX' ) ) {
				define( 'DOING_AJAX', true );
			}

			// Not home - this is an ajax endpoint
			$wp_query->is_home = false;

   			do_action( 'listings_ajax_' . sanitize_text_field( $action ) );
   			die();
   		}
	}
}