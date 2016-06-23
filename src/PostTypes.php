<?php

namespace Listings;

class PostTypes {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_post_types' ), 0 );

        add_filter( 'the_job_description', 'wptexturize'        );
        add_filter( 'the_job_description', 'convert_smilies'    );
        add_filter( 'the_job_description', 'convert_chars'      );
        add_filter( 'the_job_description', 'wpautop'            );
        add_filter( 'the_job_description', 'shortcode_unautop'  );
        add_filter( 'the_job_description', 'prepend_attachment' );

        if ( ! empty( $GLOBALS['wp_embed'] ) ) {
            add_filter( 'the_job_description', array( $GLOBALS['wp_embed'], 'run_shortcode' ), 8 );
            add_filter( 'the_job_description', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 );
        }

        // Single job content
        $this->job_content_filter( true );
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

        if ( current_theme_supports( 'job-manager-templates' ) ) {
            $has_archive = _x( 'jobs', 'Post type archive slug - resave permalinks after changing this', 'listings' );
        } else {
            $has_archive = false;
        }

        $rewrite     = array(
            'slug'       => _x( 'listings', 'Job permalink - resave permalinks after changing this', 'listings' ),
            'with_front' => false,
            'feeds'      => true,
            'pages'      => false
        );

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
                'has_archive' 			=> $has_archive,
                'show_in_nav_menus' 	=> true
            ) )
        );

        /**
         * Feeds
         */
        add_feed( 'listing_feed', array( $this, 'listing_feed' ) );
    }

    /**
     * Toggle filter on and off
     */
    private function job_content_filter( $enable ) {
        if ( ! $enable ) {
            remove_filter( 'the_content', array( $this, 'job_content' ) );
        } else {
            add_filter( 'the_content', array( $this, 'job_content' ) );
        }
    }

    /**
     * Add extra content before/after the post for single job listings.
     */
    public function job_content( $content ) {
        global $post;

        if ( ! is_singular( 'listing' ) || ! in_the_loop() || 'listing' !== $post->post_type ) {
            return $content;
        }

        ob_start();

        $this->job_content_filter( false );

        do_action( 'listing_content_start' );

        listings_get_template_part( 'content-single', 'job_listing' );

        do_action( 'listing_content_end' );

        $this->job_content_filter( true );

        return apply_filters( 'listings_single_listing_content', ob_get_clean(), $post );
    }

    /**
     * Fix post name when wp_update_post changes it
     * @param  array $data
     * @return array
     */
    public function fix_post_name( $data, $postarr ) {
        if ( 'listing' === $data['post_type'] && 'pending' === $data['post_status'] && ! current_user_can( 'publish_posts' ) ) {
            $data['post_name'] = $postarr['post_name'];
        }
        return $data;
    }
}
