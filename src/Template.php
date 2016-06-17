<?php

namespace Listings;

class Template
{
    public function get_template($template_name, $args, $template_path, $default_path)
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
            $default_path = $default_path ? $default_path : LISTINGS_PLUGIN_DIR . '/templates/';
            if ( file_exists( trailingslashit( $default_path ) . $template_name ) ) {
                $template = trailingslashit( $default_path ) . $template_name;
            }
        }

        // Return what we found
        return apply_filters( 'job_manager_locate_template', $template, $template_name, $template_path );
    }

    public function get_template_part($slug, $name, $template_path, $default_path)
    {
        $template = '';

        if ( $name ) {
            $template = $this->locate_template( "{$slug}-{$name}.php", $template_path, $default_path );
        }

        // If template file doesn't exist, look in yourtheme/slug.php and yourtheme/job_manager/slug.php
        if ( ! $template ) {
            $template = $this->locate_template( "{$slug}.php", $template_path, $default_path );
        }

        if ( $template ) {
            load_template( $template, false );
        }
    }
}