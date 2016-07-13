<?php global $post; ?>
<li>
   <?php $permalink = get_permalink($post->ID); ?>
    <div class="thumbnail">
            <?php echo listings_get_list_thumbnail_output($post, true); ?>
    </div>
    <div class="body">
        <div class="title">
            <h3><a href="<?php echo $permalink; ?>">
                <?php the_title(); ?>
            </a></h3>
        </div>
        <div class="meta">
            <?php do_action( 'listings_list_meta_start' ); ?>

            <span class="date"><date><?php printf( __( 'Posted %s ago', 'listings' ), human_time_diff( get_post_time( 'U' ), current_time( 'timestamp' ) ) ); ?></date></span>
            <?php
            $categories = listings_get_terms_links_string($post, 'listings_category');
            if ( ! empty( $categories ) ) {
                echo '<span class="categories">in ' . $categories . '</span>';
            }
            ?>

            <?php do_action( 'listings_list_meta_end' ); ?>
        </div>
    </div>
</li>