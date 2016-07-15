<?php

class Listings_PHP_Fallback {

    /**
     * @var string
     */
    private $plugin_name = '';

    /**
     * @param $plugin_name
     */
    public function __construct( $plugin_name ) {

        $this->plugin_name = $plugin_name;
    }

    /**
     * @return bool
     */
    public function trigger_notice() {
        if( ! current_user_can( 'activate_plugins' ) ) {
            return false;
        }

        // get rid of "Plugin activated" notice
        if( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }

        // show notice to user
        add_action( 'admin_notices', array( $this, 'show_notice' ) );

        return true;
    }

    /**
     * @return void
     */
    public function show_notice() {
        ?>
        <div class="updated">
            <p><?php printf( '<strong>%s</strong> did not activate because it requires <strong>PHP v5.3</strong> or higher, while your server is running <strong>PHP v%s</strong>.', $this->plugin_name, PHP_VERSION ); ?>
            <p><?php printf( '<a href="%s">Updating your PHP version</a> makes your site faster, more secure and should be easy for your host.', 'http://www.wpupdatephp.com/update/#utm_source=wp-plugin&utm_medium=listings&utm_campaign=activation-notice' ); ?></p>
        </div>
        <?php
    }
}