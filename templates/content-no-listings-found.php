<?php if ( defined( 'DOING_AJAX' ) ) : ?>
    <li class="no_listings_found"><?php _e( 'There are no listings matching your search.', 'listings' ); ?></li>
<?php else : ?>
    <p class="no_listings_found"><?php _e( 'There are no listings.', 'listings' ); ?></p>
<?php endif; ?>