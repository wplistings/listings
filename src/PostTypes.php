<?php

namespace Listings;

class PostTypes {

    /**
     * Constructor
     */
    public function __construct() {
        // Only load default post type when it is switched _on_
        if ( get_option('listings_enable_default_post_type', true) == false ) {
            return;
        }

        add_action( 'init', array( $this, 'register_post_types' ), 0 );

        add_filter( 'listings_single_description', 'wptexturize'        );
        add_filter( 'listings_single_description', 'convert_smilies'    );
        add_filter( 'listings_single_description', 'convert_chars'      );
        add_filter( 'listings_single_description', 'wpautop'            );
        add_filter( 'listings_single_description', 'shortcode_unautop'  );
        add_filter( 'listings_single_description', 'prepend_attachment' );

        if ( ! empty( $GLOBALS['wp_embed'] ) ) {
            add_filter( 'listings_single_description', array( $GLOBALS['wp_embed'], 'run_shortcode' ), 8 );
            add_filter( 'listings_single_description', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 );
        }

        // Single job content
        $this->listing_content_filter( true );

        // Only load default categories when it is switched _on_
        if ( get_option('listings_enable_default_categories', true) == false ) {
            return;
        }

        add_action( 'init', array( $this, 'register_taxonomies' ), 0 );
    }

    /**
     * register_post_types function.
     *
     * @access public
     * @return void
     */
    public function register_post_types() {
        if ( post_type_exists( "listing" ) )
            return;

        /**
         * Post types
         */
        $singular  = __( 'Listing', 'listings' );
        $plural    = __( 'Listings', 'listings' );

        $rewrite     = array(
            'slug'       => _x( 'listings', 'Listing permalink - resave permalinks after changing this', 'listings' ),
            'with_front' => false,
            'feeds'      => true,
            'pages'      => false
        );

        $template_archive = get_option('listings_use_template_archive', 1) == 1;

        register_post_type( "listing",
            apply_filters( "listings_register_post_type_listing", array(
                'labels' => array(
                    'name' 					=> $plural,
                    'singular_name' 		=> $singular,
                    'menu_name'             => __( 'Listings', 'listings' ),
                    'all_items'             => sprintf( __( 'All %s', 'listings' ), $plural ),
                    'add_new' 				=> __( 'Add New', 'listings' ),
                    'add_new_item' 			=> sprintf( __( 'Add %s', 'listings' ), $singular ),
                    'edit' 					=> __( 'Edit', 'listings' ),
                    'edit_item' 			=> sprintf( __( 'Edit %s', 'listings' ), $singular ),
                    'new_item' 				=> sprintf( __( 'New %s', 'listings' ), $singular ),
                    'view' 					=> sprintf( __( 'View %s', 'listings' ), $singular ),
                    'view_item' 			=> sprintf( __( 'View %s', 'listings' ), $singular ),
                    'search_items' 			=> sprintf( __( 'Search %s', 'listings' ), $plural ),
                    'not_found' 			=> sprintf( __( 'No %s found', 'listings' ), $plural ),
                    'not_found_in_trash' 	=> sprintf( __( 'No %s found in trash', 'listings' ), $plural ),
                    'parent' 				=> sprintf( __( 'Parent %s', 'listings' ), $singular ),
                    'featured_image'        => __( 'Listing image', 'listings' ),
                    'set_featured_image'    => __( 'Set listing image', 'listings' ),
                    'remove_featured_image' => __( 'Remove listing image', 'listings' ),
                    'use_featured_image'    => __( 'Use as listing image', 'listings' ),
                ),
                'description' => sprintf( __( 'This is where you can create and manage %s.', 'listings' ), $plural ),
                'public' 				=> true,
                'show_ui' 				=> true,
                'capability_type' 		=> 'post',
                'map_meta_cap'          => true,
                'publicly_queryable' 	=> true,
                'exclude_from_search' 	=> false,
                'hierarchical' 			=> false,
                'rewrite' 				=> $rewrite,
                'query_var' 			=> true,
                'supports' 				=> array( 'title', 'editor', 'custom-fields', 'publicize', 'thumbnail' ),
                'has_archive' 			=> $template_archive,
                'show_in_nav_menus' 	=> true
            ) )
        );
    }

    public function register_taxonomies()
    {
        $labels = array(
            'name'              => _x( 'Categories', 'taxonomy general name', 'listings' ),
            'singular_name'     => _x( 'Category', 'taxonomy singular name', 'listings' ),
            'search_items'      => __( 'Search Categories', 'listings' ),
            'all_items'         => __( 'All Categories', 'listings' ),
            'parent_item'       => __( 'Parent Category', 'listings' ),
            'parent_item_colon' => __( 'Parent Category:', 'listings' ),
            'edit_item'         => __( 'Edit Category', 'listings' ),
            'update_item'       => __( 'Update Category', 'listings' ),
            'add_new_item'      => __( 'Add New Category', 'listings' ),
            'new_item_name'     => __( 'New Category Name', 'listings' ),
            'menu_name'         => __( 'Categories', 'listings' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'listing-category' ),
        );

        register_taxonomy( 'listings_category', array( 'listing' ), $args );
    }

    /**
     * Toggle filter on and off
     */
    private function listing_content_filter( $enable ) {
        if ( ! $enable ) {
            remove_filter( 'the_content', array( $this, 'listing_content' ) );
        } else {
            add_filter( 'the_content', array( $this, 'listing_content' ) );
        }
    }

    /**
     * Add extra content before/after the post for single job listings.
     */
    public function listing_content( $content ) {
        global $post;

        if ( ! is_singular( 'listing' ) || ! in_the_loop() || 'listing' !== $post->post_type ) {
            return $content;
        }

        ob_start();

        $this->listing_content_filter( false );

        do_action( 'listing_content_start' );

        listings_get_template_part( 'content-single', 'listing' );

        do_action( 'listing_content_end' );

        $this->listing_content_filter( true );

        return apply_filters( 'listings_single_content', ob_get_clean(), $post );
    }
}
