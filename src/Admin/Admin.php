<?php

namespace Listings\Admin;

class Admin {

	/**
	 * __construct function.
	 *
	 * @access public
	 */
	public function __construct() {
		$this->settings_page = new Settings();
		$this->cpt = new Cpt();

		add_action( 'admin_menu', array( $this, 'admin_menu' ), 12 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * admin_menu function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_menu() {
		add_options_page(__('Listings Settings'), __('Listings'), 'manage_options', 'listings-settings', array($this->settings_page, 'output'));
//		add_menu_page('Listings', 'Listings', 'manage_options', 'listings', function() {
//		}, '', 25);
//		add_submenu_page( 'listings', __( 'Settings', 'listings' ), __( 'Settings', 'listings' ), 'manage_options', 'listings-settings', array( $this->settings_page, 'output' ) );
	}

	/**
	 * admin_enqueue_scripts function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		global $wp_scripts;

		$screen = get_current_screen();

		wp_enqueue_style( 'listings_admin_css', LISTINGS_PLUGIN_URL . '/assets/css/admin.css' );

		if ( in_array( $screen->id, apply_filters( 'listings_admin_screen_ids', array( 'edit-listing', 'listing', 'listings_page_listings-settings' ) ) ) ) {
			$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';

			wp_enqueue_style( 'jquery-ui-style', '//code.jquery.com/ui/' . $jquery_version . '/themes/smoothness/jquery-ui.css', array(), $jquery_version );
			wp_register_script( 'jquery-tiptip', LISTINGS_PLUGIN_URL. '/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), LISTINGS_VERSION, true );
			wp_enqueue_script( 'listings_admin_js', LISTINGS_PLUGIN_URL. '/assets/js/admin.min.js', array( 'jquery', 'jquery-tiptip', 'jquery-ui-datepicker' ), LISTINGS_VERSION, true );

			wp_localize_script( 'listings_admin_js', 'listings_admin', array(
				'date_format' => _x( 'yy-mm-dd', 'Date format for jQuery datepicker', 'listings' )
			) );
		}
	}
}