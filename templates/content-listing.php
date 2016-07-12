<?php global $post; ?>
<li>
   <?php $permalink = get_permalink($post->ID); ?>
    <div class="thumbnail">
        <a href="<?php echo $permalink; ?>">
            <?php
            echo get_the_post_thumbnail($post->ID, 'thumbnail');
            ?>
        </a>
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
            <span class="categories">in <?php echo listings_get_terms_links_string($post, 'listings_category') ?></span>

            <?php do_action( 'listings_list_meta_end' ); ?>
        </div>
    </div>
</li>