<?php

namespace Listings;

class Template
{
    public $template_paths = array();

    public function __construct()
    {
        $this->template_paths[] = LISTINGS_PLUGIN_DIR . '/templates/';
    }

    public function hooks()
    {
        add_filter('template_include', array($this, 'template_include'), 10, 1);
    }

    public function template_include($template)
    {
        if ( is_tax('listings_category' ) ) {
            return $this->locate_template('taxonomy-listings_category.php', 'listings');
        }

        if ( is_post_type_archive('listing') ) {
            if ( get_option('listings_use_template_archive', 1) == 1 ) {
                return $this->locate_template('archive-listing.php', 'listings');
            }
        }

        return $template;
    }

    public function register_template_path( $path )
    {
        $this->template_paths[] = $path;
    }

    public function get_template( $template_name, $args = array(), $template_path = 'listings' )
    {
        if ( $args && is_array( $args ) ) {
            extract( $args );
        }
        include( $this->locate_template( $template_name, $template_path ) );
    }

    public function locate_template($template_name, $template_path)
    {
        // Look within passed path within the theme - this is priority
        $template = locate_template(
            array(
                trailingslashit( $template_path ) . $template_name,
                $template_name
            )
        );

        // Get default template
        if ( ! $template ) {
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

    public function get_template_part($slug, $name = '', $template_path = 'listings')
    {
        $template = '';

        if ( $name ) {
            $template = $this->locate_template( "{$slug}-{$name}.php", $template_path );
        }

        // If template file doesn't exist, look in yourtheme/slug.php and yourtheme/listings/slug.php
        if ( ! $template ) {
            $template = $this->locate_template( "{$slug}.php", $template_path );
        }

        if ( $template ) {
            load_template( $template, false );
        }
    }
}