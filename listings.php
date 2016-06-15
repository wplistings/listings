<?php
/**
 * Plugin Name: Listings
 * Description: Manage listings from the WordPress admin panel, and allow users to list items directly to your site.
 * Version: 1.0.0
 * Author: The Look and Feel
 * Text Domain: listings
 */

// Define constants
define( 'LISTINGS_VERSION', '1.0.0' );
define( 'LISTINGS_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'LISTINGS_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );

include('vendor/autoload.php');
$GLOBALS['listings'] = new \Listings\Plugin();
