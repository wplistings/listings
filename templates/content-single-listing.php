<?php global $post; ?>
<div class="single_listing">
	<meta itemprop="title" content="<?php echo esc_attr( $post->post_title ); ?>" />

	<?php
	do_action( 'listings_single_listing_start' );
	?>

	<div class="listings_description" itemprop="description">
		<?php echo apply_filters( 'listings_single_description', get_the_content() ); ?>
	</div>

	<?php
	do_action( 'listings_single_listing_end' );
	?>
</div>
