<?php global $post; ?>
<li>
    <a href="<?php echo get_permalink($post->ID); ?>">
        <div class="title">
            <h3><?php the_title(); ?></h3>
        </div>
        <div class="meta">
            <?php do_action( 'listings_list_meta_start' ); ?>

            <span class="date"><date><?php printf( __( '%s ago', 'listings' ), human_time_diff( get_post_time( 'U' ), current_time( 'timestamp' ) ) ); ?></date></span>

            <?php do_action( 'listings_list_meta_end' ); ?>
        </div>
    </a>
</li>