<?php

namespace Listings;

class Shortcodes {

    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode( 'listings', array( $this, 'output_listings' ) );
    }

    /**
     * @access public
     * @param mixed $atts
     * @return void
     */
    public function output_listings( $atts ) {
        ob_start();

        extract( $atts = shortcode_atts( apply_filters( 'listings_output_litings_defaults', array(
            'per_page'                  => get_option( 'listings_per_page' ),
            'orderby'                   => 'featured',
            'order'                     => 'DESC',

            // Filters + cats
            'show_categories'           => true,
            'show_pagination'           => false,
            'show_more'                 => true,

            // Limit what listings are shown based on category and type
            'categories'                => '',

            // Default values for filters
            'keywords'                  => '',
            'selected_category'         => '',
        ) ), $atts ) );

        if ( ! get_option( 'listings_enable_default_categories' ) ) {
            $show_categories = false;
        }

        // String and bool handling
        $show_more                 = $this->string_to_bool( $show_more );
        $show_pagination           = $this->string_to_bool( $show_pagination );

        // Array handling
        $categories         = is_array( $categories ) ? $categories : array_filter( array_map( 'trim', explode( ',', $categories ) ) );

        // Get keywords and location from querystring if set
        if ( ! empty( $_GET['search_keywords'] ) ) {
            $keywords = sanitize_text_field( $_GET['search_keywords'] );
        }
        if ( ! empty( $_GET['search_category'] ) ) {
            $selected_category = sanitize_text_field( $_GET['search_category'] );
        }

        $listings = listings_get_listings( apply_filters( 'listings_output_listings_args', array(
            'search_keywords'   => $keywords,
            'search_categories' => $categories,
            'orderby'           => $orderby,
            'order'             => $order,
            'posts_per_page'    => $per_page,
        ) ) );

        if ( $listings->have_posts() ) : ?>

            <?php listings_get_template( 'listings-list-start.php' ); ?>

            <?php while ( $listings->have_posts() ) : $listings->the_post(); ?>
                <?php listings_get_template_part( 'content', 'listing' ); ?>
            <?php endwhile; ?>

            <?php listings_get_template( 'listings-list-end.php' ); ?>

            <?php if ( $listings->found_posts > $per_page && $show_more ) : ?>

                <?php if ( $show_pagination ) : ?>
                    <?php echo listings_get_listing_pagination( $listings->max_num_pages ); ?>
                <?php else : ?>
                    <a class="load_more_listings" href="#"><strong><?php _e( 'Load more listings', 'listings' ); ?></strong></a>
                <?php endif; ?>

            <?php endif; ?>

        <?php else :
            do_action( 'listings_output_listings_no_results' );
        endif;

        wp_reset_postdata();

        $data_attributes_string = '';
        $data_attributes        = array(
            'keywords'        => $keywords,
            'show_pagination' => $show_pagination ? 'true' : 'false',
            'per_page'        => $per_page,
            'orderby'         => $orderby,
            'order'           => $order,
            'categories'      => implode( ',', $categories ),
        );

        foreach ( $data_attributes as $key => $value ) {
            $data_attributes_string .= 'data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
        }

        $listings_output = apply_filters( 'listings_listings_output', ob_get_clean() );

        return '<div class="listings" ' . $data_attributes_string . '>' . $listings_output . '</div>';
    }

    /**
     * Output some content when no results were found
     */
    public function output_no_results() {
        listings_get_template( 'content-no-listings-found.php' );
    }

    /**
     * Get string as a bool
     * @param  string $value
     * @return bool
     */
    public function string_to_bool( $value ) {
        return ( is_bool( $value ) && $value ) || in_array( $value, array( '1', 'true', 'yes' ) ) ? true : false;
    }
}