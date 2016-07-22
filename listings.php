<?php
/**
 * Plugin Name: Listings
 * Description: Manage listings from the WordPress admin panel, and allow users to list items directly to your site.
 * Version: 0.3.0
 * Author: The Look and Feel
 * Text Domain: listings
 */

// Define constants
define( 'LISTINGS_VERSION', '0.3.0' );
define( 'LISTINGS_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'LISTINGS_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
define( 'LISTINGS_PLUGIN_FILE', __FILE__ );

/**
 * @return \Listings\Plugin
 */
function listings() {
    static $instance;
    if ( is_null( $instance ) ) {
        $instance = new \Listings\Plugin();
        $instance->hooks();
    }
    return $instance;
}

function __load_listings() {
    if( version_compare( PHP_VERSION, '5.3', '<' ) ) {
        include('helpers/php-fallback.php');
        $fallback = new Listings_PHP_Fallback( 'Listings' );
        $fallback->trigger_notice();
        return;
    }

    $GLOBALS['listings'] = listings();
}

// autoloader
require 'vendor/autoload.php';

// create plugin object
add_action( 'plugins_loaded', '__load_listings', 10 );
