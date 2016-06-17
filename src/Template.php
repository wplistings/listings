<?php

namespace Listings;

class Template
{
    public $template_paths = [];

    public function __construct()
    {
        $this->template_paths[] = LISTINGS_PLUGIN_DIR . '/templates/';
    }

    public function register_template_path( $path )
    {
        $this->template_paths[] = $path;
    }

    public function get_template( $template_name, $args = array(), $template_path = 'listings', $default_path = '' )
    {
        if ( $args && is_array( $args ) ) {
            extract( $args );
        }
        include( $this->locate_template( $template_name, $template_path, $default_path ) );
    }

    public function locate_template($template_name, $template_path, $default_path)
    {
        // Look within passed path within the theme - this is priority
        $template = locate_template(
            array(
                trailingslashit( $template_path ) . $template_name,
                $template_name
            )
        );

        // Get default template
        if ( ! $template && $default_path !== false ) {
            foreach ( $this->template_paths as $path ) {
                if (file_exists(trailingslashit($path) . $template_name)) {
                    $template = trailingslashit($path) . $template_name;
                    break;
                }
            }
        }

        // Return what we found
        return apply_filters( 'listings_locate_template', $template, $template_name, $template_path );
    }

    public function get_template_part($slug, $name = '', $template_path = 'listings', $default_path = '')
    {
        $template = '';

        if ( $name ) {
            $template = $this->locate_template( "{$slug}-{$name}.php", $template_path, $default_path );
        }

        // If template file doesn't exist, look in yourtheme/slug.php and yourtheme/listings/slug.php
        if ( ! $template ) {
            $template = $this->locate_template( "{$slug}.php", $template_path, $default_path );
        }

        if ( $template ) {
            load_template( $template, false );
        }
    }
}