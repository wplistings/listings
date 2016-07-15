<?php

/**
 * Get and include template files.
 *
 * @param mixed $template_name
 * @param array $args (default: array())
 * @param string $template_path (default: '')
 * @return void
 */
function listings_get_template( $template_name, $args = array(), $template_path = 'listings' ) {
	listings()->template->get_template($template_name, $args, $template_path);
}

/**
 * Get template part (for templates in loops).
 *
 * @param string $slug
 * @param string $name (default: '')
 * @param string $template_path (default: 'listings')
 */
function listings_get_template_part( $slug, $name = '', $template_path = 'listings' ) {
	listings()->template->get_template_part($slug, $name, $template_path);
}

/**
 * Add custom body classes
 * @param  array $classes
 * @return array
 */
function listings_body_class( $classes ) {
	$classes   = (array) $classes;
	$classes[] = sanitize_title( wp_get_theme() );

	return array_unique( $classes );
}

add_filter( 'body_class', 'listings_body_class' );

/**
 * Get jobs pagination for [jobs] shortcode
 * @return [type] [description]
 */
function listings_get_listing_pagination( $max_num_pages, $current_page = 1 ) {
	ob_start();
	listings_get_template( 'pagination.php', array( 'max_num_pages' => $max_num_pages, 'current_page' => absint( $current_page ) ) );
	return ob_get_clean();
}

/**
 * Resize and get url of the image
 *
 * @param  string $logo
 * @param  string $size
 * @return string
 */
function listings_get_resized_image( $logo, $size ) {
	global $_wp_additional_image_sizes;

	if ( $size !== 'full' && strstr( $logo, WP_CONTENT_URL ) && ( isset( $_wp_additional_image_sizes[ $size ] ) || in_array( $size, array( 'thumbnail', 'medium', 'large' ) ) ) ) {

		if ( in_array( $size, array( 'thumbnail', 'medium', 'large' ) ) ) {
			$img_width  = get_option( $size . '_size_w' );
			$img_height = get_option( $size . '_size_h' );
			$img_crop   = get_option( $size . '_size_crop' );
		} else {
			$img_width  = $_wp_additional_image_sizes[ $size ]['width'];
			$img_height = $_wp_additional_image_sizes[ $size ]['height'];
			$img_crop   = $_wp_additional_image_sizes[ $size ]['crop'];
		}

		$upload_dir        = wp_upload_dir();
		$logo_path         = str_replace( array( $upload_dir['baseurl'], $upload_dir['url'], WP_CONTENT_URL ), array( $upload_dir['basedir'], $upload_dir['path'], WP_CONTENT_DIR ), $logo );
		$path_parts        = pathinfo( $logo_path );
		$resized_logo_path = str_replace( '.' . $path_parts['extension'], '-' . $size . '.' . $path_parts['extension'], $logo_path );

		if ( strstr( $resized_logo_path, 'http:' ) || strstr( $resized_logo_path, 'https:' ) ) {
			return $logo;
		}

		if ( ! file_exists( $resized_logo_path ) ) {
			ob_start();

			$image = wp_get_image_editor( $logo_path );

			if ( ! is_wp_error( $image ) ) {

				$resize = $image->resize( $img_width, $img_height, $img_crop );

			   	if ( ! is_wp_error( $resize ) ) {

			   		$save = $image->save( $resized_logo_path );

					if ( ! is_wp_error( $save ) ) {
						$logo = dirname( $logo ) . '/' . basename( $resized_logo_path );
					}
				}
			}

			ob_get_clean();
		} else {
			$logo = dirname( $logo ) . '/' . basename( $resized_logo_path );
		}
	}

	return $logo;
}

/**
 * @param $post
 * @param $taxonomy
 * @return string
 */
function listings_get_terms_links_string($post, $taxonomy)
{
	$categories = wp_get_post_terms($post->ID, $taxonomy);

    if ( empty($categories) || is_wp_error($categories ) ) {
        return '';
    }

	$categories_array = array_map(function ($item) {
		$permalink = get_term_link($item);
		return '<a href="' . $permalink . '">' . $item->name . '</a>';
	}, $categories);
	$categories_string = implode(', ', $categories_array);
	return $categories_string;
}

/**
 * @param $post
 * @param $clickable bool
 * @return string
 */
function listings_get_list_thumbnail_output($post, $clickable)
{
	$thumbnail = get_the_post_thumbnail($post->ID, 'thumbnail');

	if (empty($thumbnail)) {
		return apply_filters('listings_get_list_thumbnail_default', '&nbsp;', $post, $clickable);
	}

	$output = '';
	$permalink = get_permalink($post->ID);

	if ($clickable) {
		$output .= '<a href="' . $permalink . '">';
	}

	$output .= $thumbnail;

	if ($clickable) {
		$output .= '</a>';
	}

	return apply_filters('listings_get_list_thumbnail_output', $output, $post, $clickable);
}