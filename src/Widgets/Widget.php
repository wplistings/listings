<?php

namespace Listings\Widgets;

use WP_Widget;

abstract class Widget extends WP_Widget {

    public $widget_cssclass;
    public $widget_description;
    public $widget_id;
    public $widget_name;
    public $settings;

    /**
     * Constructor
     */
    public function __construct() {
        $this->register();
    }

    /**
     * Register Widget
     */
    public function register() {
        $widget_ops = array(
            'classname'   => $this->widget_cssclass,
            'description' => $this->widget_description
        );

        parent::__construct( $this->widget_id, $this->widget_name, $widget_ops );

        add_action( 'save_post', array( $this, 'flush_widget_cache' ) );
        add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
        add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );
    }

    /**
     * get_cached_widget function.
     */
    function get_cached_widget( $args ) {
        $cache = wp_cache_get( $this->widget_id, 'widget' );

        if ( ! is_array( $cache ) )
            $cache = array();

        if ( isset( $cache[ $args['widget_id'] ] ) ) {
            echo $cache[ $args['widget_id'] ];
            return true;
        }

        return false;
    }

    /**
     * Cache the widget
     */
    public function cache_widget( $args, $content ) {
        $cache[ $args['widget_id'] ] = $content;

        wp_cache_set( $this->widget_id, $cache, 'widget' );
    }

    /**
     * Flush the cache
     * @return [type]
     */
    public function flush_widget_cache() {
        wp_cache_delete( $this->widget_id, 'widget' );
    }

    /**
     * update function.
     *
     * @see WP_Widget->update
     * @access public
     * @param array $new_instance
     * @param array $old_instance
     * @return array
     */
    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        if ( ! $this->settings )
            return $instance;

        foreach ( $this->settings as $key => $setting ) {
            $instance[ $key ] = sanitize_text_field( $new_instance[ $key ] );
        }

        $this->flush_widget_cache();

        return $instance;
    }

    /**
     * form function.
     *
     * @see WP_Widget->form
     * @access public
     * @param array $instance
     * @return void
     */
    function form( $instance ) {

        if ( ! $this->settings )
            return;

        foreach ( $this->settings as $key => $setting ) {

            $value = isset( $instance[ $key ] ) ? $instance[ $key ] : $setting['std'];

            switch ( $setting['type'] ) {
                case 'text' :
                    ?>
                    <p>
                        <label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
                        <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo $this->get_field_name( $key ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>" />
                    </p>
                    <?php
                    break;
                case 'number' :
                    ?>
                    <p>
                        <label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
                        <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo $this->get_field_name( $key ); ?>" type="number" step="<?php echo esc_attr( $setting['step'] ); ?>" min="<?php echo esc_attr( $setting['min'] ); ?>" max="<?php echo esc_attr( $setting['max'] ); ?>" value="<?php echo esc_attr( $value ); ?>" />
                    </p>
                    <?php
                    break;
            }
        }
    }
}