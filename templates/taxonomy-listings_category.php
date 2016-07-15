<?php

get_header();

if ( ! empty( $posts ) ) {
    listings_get_template('listings-list-start.php');

    foreach ( $posts as $post ) {
        listings_get_template_part('content', 'listing');
    }

    listings_get_template('listings-list-end.php');
}

do_action( 'listings_sidebar' );

get_footer();