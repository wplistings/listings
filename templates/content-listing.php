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
        <h3><a href="<?php echo $permalink; ?>">
            <?php the_title(); ?>
        </a></h3>
        <div class="meta">
            <?php do_action( 'listings_list_meta_start' ); ?>

            <?php
            $categories = wp_get_post_terms($post->ID, 'listings_category');
            $categories_array = array_map(function($item) {
                $permalink = get_term_link($item);
                return '<a href="'.$permalink.'">'.$item->name.'</a>';
            }, $categories);
            $categories_string = implode(', ', $categories_array); ?>

            <span class="date"><date><?php printf( __( 'Posted %s ago', 'listings' ), human_time_diff( get_post_time( 'U' ), current_time( 'timestamp' ) ) ); ?></date></span>
            <span class="categories">in <?php echo $categories_string ?></span>

            <?php do_action( 'listings_list_meta_end' ); ?>
        </div>
    </div>
</li>