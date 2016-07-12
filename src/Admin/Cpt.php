<?php

namespace Listings\Admin;

class Cpt {

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        add_filter( 'manage_edit-listing_columns', array( $this, 'columns' ) );
        add_action( 'manage_listing_posts_custom_column', array( $this, 'custom_columns' ), 2 );
    }

    /**
     * columns function.
     *
     * @param array $columns
     * @return array
     */
    public function columns( $columns ) {
        if ( ! is_array( $columns ) ) {
            $columns = array();
        }

        unset( $columns['author'] );

        $offset = 1;
        $columns = array_slice($columns, 0, $offset, true) +
            array('listing_image' => '') +
            array_slice($columns, $offset, NULL, true);

        return $columns;
    }

    /**
     * custom_columns function.
     *
     * @access public
     * @param mixed $column
     * @return void
     */
    public function custom_columns( $column ) {
        global $post;

        switch ( $column ) {
            case "listing_image" :
                echo get_the_post_thumbnail($post->ID, 'thumbnail');
                break;
        }
    }
}