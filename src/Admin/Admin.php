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
		$this->setup = new Setup();
		$this->addons_page = new Addons();

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
		add_menu_page('Listings', 'Listings', 'manage_options', 'listings', function() {
		}, '', 25);
		if ( apply_filters( 'job_manager_show_addons_page', true ) ) {
			add_submenu_page('listings', __('Listings Add-ons', 'listings'), __('Add-ons', 'listings'), 'manage_options', 'listings-addons', array($this->addons_page, 'output'));
		}
		add_submenu_page( 'listings', __( 'Settings', 'listings' ), __( 'Settings', 'listings' ), 'manage_options', 'listings-settings', array( $this->settings_page, 'output' ) );
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
		
		if ( in_array( $screen->id, apply_filters( 'job_manager_admin_screen_ids', array( 'edit-job_listing', 'job_listing', 'listings_page_listings-settings', 'listings_page_listings-addons' ) ) ) ) {
			$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';

			wp_enqueue_style( 'jquery-ui-style', '//code.jquery.com/ui/' . $jquery_version . '/themes/smoothness/jquery-ui.css', array(), $jquery_version );
			wp_enqueue_style( 'job_manager_admin_css', LISTINGS_PLUGIN_URL . '/assets/css/admin.css' );
			wp_register_script( 'jquery-tiptip', LISTINGS_PLUGIN_URL. '/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), LISTINGS_VERSION, true );
			wp_enqueue_script( 'job_manager_admin_js', LISTINGS_PLUGIN_URL. '/assets/js/admin.min.js', array( 'jquery', 'jquery-tiptip', 'jquery-ui-datepicker' ), LISTINGS_VERSION, true );

			wp_localize_script( 'job_manager_admin_js', 'job_manager_admin', array(
				'date_format' => _x( 'yy-mm-dd', 'Date format for jQuery datepicker', 'wp-job-manager' )
			) );
		}

		wp_enqueue_style( 'job_manager_admin_menu_css', LISTINGS_PLUGIN_URL . '/assets/css/menu.css' );
	}
}