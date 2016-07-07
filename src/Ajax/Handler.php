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
		add_rewrite_tag( '%jm-ajax%', '([^/]*)' );
		add_rewrite_rule( 'jm-ajax/([^/]*)/?', 'index.php?jm-ajax=$matches[1]', 'top' );
		add_rewrite_rule( 'index.php/jm-ajax/([^/]*)/?', 'index.php?jm-ajax=$matches[1]', 'top' );
	}

	/**
	 * Get JM Ajax Endpoint
	 * @param  string $request Optional
	 * @param  string $ssl     Optional
	 * @return string
	 */
	public static function get_endpoint( $request = '%%endpoint%%', $ssl = null ) {
		if ( strstr( get_option( 'permalink_structure' ), '/index.php/' ) ) {
			$endpoint = trailingslashit( home_url( '/index.php/jm-ajax/' . $request . '/', 'relative' ) );
		} elseif ( get_option( 'permalink_structure' ) ) {
			$endpoint = trailingslashit( home_url( '/jm-ajax/' . $request . '/', 'relative' ) );
		} else {
			$endpoint = add_query_arg( 'jm-ajax', $request, trailingslashit( home_url( '', 'relative' ) ) );
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