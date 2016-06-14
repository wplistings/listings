<?php
/**
 * Plugin Name: Listings
 * Description: Manage job listings from the WordPress admin panel, and allow users to post jobs directly to your site.
 * Version: 1.0.0
 * Author: The Look and Feel
 * Text Domain: listings
 */

// Define constants
define( 'JOB_MANAGER_VERSION', '1.25.0' );
define( 'JOB_MANAGER_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'JOB_MANAGER_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );

include('vendor/autoload.php');
$GLOBALS['listings'] = new \Listings\Plugin();