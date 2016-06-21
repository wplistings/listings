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
 * @param string $template_path (default: 'job_manager')
 */
function listings_get_template_part( $slug, $name = '', $template_path = 'job_manager' ) {
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
	listings_get_template( 'job-pagination.php', array( 'max_num_pages' => $max_num_pages, 'current_page' => absint( $current_page ) ) );
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
 * Output the company video
 */
function the_company_video( $post = null ) {
	$video    = get_the_company_video( $post );
	$filetype = wp_check_filetype( $video );

	// FV Wordpress Flowplayer Support for advanced video formats
	if ( shortcode_exists( 'flowplayer' ) ) {
		$video_embed = '[flowplayer src="' . esc_attr( $video ) . '"]';
	} elseif ( ! empty( $filetype['ext'] ) ) {
		$video_embed = wp_video_shortcode( array( 'src' => $video ) );
	} else {
		$video_embed = wp_oembed_get( $video );
	}

	$video_embed = apply_filters( 'the_company_video_embed', $video_embed, $post );

	if ( $video_embed ) {
		echo '<div class="company_video">' . $video_embed . '</div>';
	}
}

/**
 * Get the company video URL
 *
 * @param mixed $post (default: null)
 * @return string
 */
function get_the_company_video( $post = null ) {
	$post = get_post( $post );
	if ( $post->post_type !== 'job_listing' ) {
		return;
	}
	return apply_filters( 'the_company_video', $post->_company_video, $post );
}

/**
 * Display or retrieve the current company name with optional content.
 *
 * @access public
 * @param mixed $id (default: null)
 * @return void
 */
function the_company_name( $before = '', $after = '', $echo = true, $post = null ) {
	$company_name = get_the_company_name( $post );

	if ( strlen( $company_name ) == 0 )
		return;

	$company_name = esc_attr( strip_tags( $company_name ) );
	$company_name = $before . $company_name . $after;

	if ( $echo )
		echo $company_name;
	else
		return $company_name;
}

/**
 * get_the_company_name function.
 *
 * @access public
 * @param int $post (default: null)
 * @return string
 */
function get_the_company_name( $post = null ) {
	$post = get_post( $post );
	if ( $post->post_type !== 'job_listing' ) {
		return '';
	}

	return apply_filters( 'the_company_name', $post->_company_name, $post );
}

/**
 * get_the_company_website function.
 *
 * @access public
 * @param int $post (default: null)
 * @return void
 */
function get_the_company_website( $post = null ) {
	$post = get_post( $post );

	if ( $post->post_type !== 'job_listing' )
		return;

	$website = $post->_company_website;

	if ( $website && ! strstr( $website, 'http:' ) && ! strstr( $website, 'https:' ) ) {
		$website = 'http://' . $website;
	}

	return apply_filters( 'the_company_website', $website, $post );
}

/**
 * Display or retrieve the current company tagline with optional content.
 *
 * @access public
 * @param mixed $id (default: null)
 * @return void
 */
function the_company_tagline( $before = '', $after = '', $echo = true, $post = null ) {
	$company_tagline = get_the_company_tagline( $post );

	if ( strlen( $company_tagline ) == 0 )
		return;

	$company_tagline = esc_attr( strip_tags( $company_tagline ) );
	$company_tagline = $before . $company_tagline . $after;

	if ( $echo )
		echo $company_tagline;
	else
		return $company_tagline;
}

/**
 * get_the_company_tagline function.
 *
 * @access public
 * @param int $post (default: 0)
 * @return void
 */
function get_the_company_tagline( $post = null ) {
	$post = get_post( $post );

	if ( $post->post_type !== 'job_listing' )
		return;

	return apply_filters( 'the_company_tagline', $post->_company_tagline, $post );
}

/**
 * Display or retrieve the current company twitter link with optional content.
 *
 * @access public
 * @param mixed $id (default: null)
 * @return void
 */
function the_company_twitter( $before = '', $after = '', $echo = true, $post = null ) {
	$company_twitter = get_the_company_twitter( $post );

	if ( strlen( $company_twitter ) == 0 )
		return;

	$company_twitter = esc_attr( strip_tags( $company_twitter ) );
	$company_twitter = $before . '<a href="http://twitter.com/' . $company_twitter . '" class="company_twitter" target="_blank">' . $company_twitter . '</a>' . $after;

	if ( $echo )
		echo $company_twitter;
	else
		return $company_twitter;
}

/**
 * get_the_company_twitter function.
 *
 * @access public
 * @param int $post (default: 0)
 * @return void
 */
function get_the_company_twitter( $post = null ) {
	$post = get_post( $post );
	if ( $post->post_type !== 'job_listing' )
		return;

	$company_twitter = $post->_company_twitter;

	if ( strlen( $company_twitter ) == 0 )
		return;

	if ( strpos( $company_twitter, '@' ) === 0 )
		$company_twitter = substr( $company_twitter, 1 );

	return apply_filters( 'the_company_twitter', $company_twitter, $post );
}

/**
 * job_listing_class function.
 *
 * @access public
 * @param string $class (default: '')
 * @param mixed $post_id (default: null)
 * @return void
 */
function job_listing_class( $class = '', $post_id = null ) {
	// Separates classes with a single space, collates classes for post DIV
	echo 'class="' . join( ' ', get_job_listing_class( $class, $post_id ) ) . '"';
}

/**
 * get_job_listing_class function.
 *
 * @access public
 * @return array
 */
function get_job_listing_class( $class = '', $post_id = null ) {
	$post = get_post( $post_id );

	if ( $post->post_type !== 'job_listing' ) {
		return array();
	}

	$classes = array();

	if ( empty( $post ) ) {
		return $classes;
	}

	$classes[] = 'job_listing';
	if ( $job_type = listings_jobs_get_the_job_type() ) {
		$classes[] = 'job-type-' . sanitize_title( $job_type->name );
	}

	if ( listings_jobs_is_position_filled( $post ) ) {
		$classes[] = 'job_position_filled';
	}

	if ( listings_jobs_is_position_featured( $post ) ) {
		$classes[] = 'job_position_featured';
	}

	if ( ! empty( $class ) ) {
		if ( ! is_array( $class ) ) {
			$class = preg_split( '#\s+#', $class );
		}
		$classes = array_merge( $classes, $class );
	}

	return get_post_class( $classes, $post->ID );
}

/**
 * Displays job meta data on the single job page
 */
function job_listing_meta_display() {
	listings_get_template( 'content-single-job_listing-meta.php', array() );
}
add_action( 'single_job_listing_start', 'job_listing_meta_display', 20 );

/**
 * Displays job company data on the single job page
 */
function job_listing_company_display() {
	listings_get_template( 'content-single-job_listing-company.php', array() );
}
add_action( 'single_job_listing_start', 'job_listing_company_display', 30 );
