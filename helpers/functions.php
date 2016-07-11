<?php

/**
 * @access public
 * @param array $args
 * @return mixed|WP_Query
 */
function listings_get_listings( $args = array() ) {
	global $listings_keyword;

	$args = wp_parse_args( $args, array(
		'search_keywords'   => '',
		'search_categories' => array(),
		'offset'            => 0,
		'posts_per_page'    => 20,
		'orderby'           => 'date',
		'order'             => 'DESC',
		'fields'            => 'all'
	) );

	$query_args = array(
		'post_type'              => 'listing',
		'post_status'            => 'publish',
		'ignore_sticky_posts'    => 1,
		'offset'                 => absint( $args['offset'] ),
		'posts_per_page'         => intval( $args['posts_per_page'] ),
		'orderby'                => $args['orderby'],
		'order'                  => $args['order'],
		'tax_query'              => array(),
		'meta_query'             => array(),
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
		'cache_results'          => false,
		'fields'                 => $args['fields']
	);

	if ( $args['posts_per_page'] < 0 ) {
		$query_args['no_found_rows'] = true;
	}

	if ( ! empty( $args['search_categories'] ) ) {
		$field    = is_numeric( $args['search_categories'][0] ) ? 'term_id' : 'slug';
		$operator = 'all' === get_option( 'listings_category_filter_type', 'all' ) && sizeof( $args['search_categories'] ) > 1 ? 'AND' : 'IN';
		$query_args['tax_query'][] = array(
			'taxonomy'         => 'listings_category',
			'field'            => $field,
			'terms'            => array_values( $args['search_categories'] ),
			'include_children' => $operator !== 'AND' ,
			'operator'         => $operator
		);
	}

	$listings_keyword = sanitize_text_field( $args['search_keywords'] );

	if ( ! empty( $listings_keyword ) && strlen( $listings_keyword ) >= apply_filters( 'listings_keyword_length_threshold', 2 ) ) {
		$query_args['_keyword'] = $listings_keyword; // Does nothing but needed for unique hash
		add_filter( 'posts_clauses', 'listings_get_keyword_search' );
	}

	$query_args = apply_filters( 'listings_get_listings', $query_args, $args );

	if ( empty( $query_args['meta_query'] ) ) {
		unset( $query_args['meta_query'] );
	}

	if ( empty( $query_args['tax_query'] ) ) {
		unset( $query_args['tax_query'] );
	}

	// Polylang LANG arg
	if ( function_exists( 'pll_current_language' ) ) {
		$query_args['lang'] = pll_current_language();
	}

	// Filter args
	$query_args = apply_filters( 'listings_get_listing_query_args', $query_args, $args );

	// Generate hash
	$to_hash         = json_encode( $query_args ) . apply_filters( 'wpml_current_language', '' );
	$query_args_hash = 'listings__' . md5( $to_hash ) . \Listings\CacheHelper::get_transient_version( 'get_listings' );

	do_action( 'listings_before_get_listings', $query_args, $args );

	if ( false === ( $result = get_transient( $query_args_hash ) ) ) {
		$result = new WP_Query( $query_args );
		set_transient( $query_args_hash, $result, DAY_IN_SECONDS * 30 );
	}

	do_action( 'listings_after_get_listings', $query_args, $args );

	remove_filter( 'posts_clauses', 'listings_get_keyword_search' );

	return $result;
}

if ( ! function_exists( 'listings_get_keyword_search' ) ) :
	/**
	 * Join and where query for keywords
	 *
	 * @param array $args
	 * @return array
	 */
	function listings_get_keyword_search( $args ) {
		global $wpdb, $listings_keyword;

		$conditions   = array();
		$conditions[] = "{$wpdb->posts}.post_title LIKE '%" . esc_sql( $listings_keyword ) . "%'";
		$conditions[] = "{$wpdb->posts}.ID IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_value LIKE '%" . esc_sql( $listings_keyword ) . "%' )";
		$conditions[] = "{$wpdb->posts}.ID IN ( SELECT object_id FROM {$wpdb->term_relationships} AS tr LEFT JOIN {$wpdb->terms} AS t ON tr.term_taxonomy_id = t.term_id WHERE t.name LIKE '%" . esc_sql( $listings_keyword ) . "%' )";

		if ( ctype_alnum( $listings_keyword ) ) {
			$conditions[] = "{$wpdb->posts}.post_content RLIKE '[[:<:]]" . esc_sql( $listings_keyword ) . "[[:>:]]'";
		} else {
			$conditions[] = "{$wpdb->posts}.post_content LIKE '%" . esc_sql( $listings_keyword ) . "%'";
		}

		$args['where'] .= " AND ( " . implode( ' OR ', $conditions ) . " ) ";

		return $args;
	}
endif;

if ( ! function_exists( 'listings_notify_new_user' ) ) :
	/**
	 * Handle account creation.
	 *
	 * @param  int $user_id
	 * @param  string $password
	 */
	function listings_notify_new_user( $user_id, $password ) {
		global $wp_version;

		if ( version_compare( $wp_version, '4.3.1', '<' ) ) {
			wp_new_user_notification( $user_id, $password );
		} else {
			wp_new_user_notification( $user_id, null, 'both' );
		}
	}
endif;

if ( ! function_exists( 'listings_create_account' ) ) :
/**
 * Handle account creation.
 *
 * @param  array $args containing username, email, role
 * @return WP_error | bool was an account created?
 */
function listings_create_account( $args ) {
	global $current_user;

	$defaults = array(
		'username' => '',
		'email'    => '',
		'password' => wp_generate_password(),
		'role'     => get_option( 'default_role' )
	);

	$args = wp_parse_args( $args, $defaults );
	extract( $args );

	$username = sanitize_user( $username );
	$email    = apply_filters( 'user_registration_email', sanitize_email( $email ) );

	if ( empty( $email ) ) {
		return new WP_Error( 'validation-error', __( 'Invalid email address.', 'listings' ) );
	}

	if ( empty( $username ) ) {
		$username = sanitize_user( current( explode( '@', $email ) ) );
	}

	if ( ! is_email( $email ) ) {
		return new WP_Error( 'validation-error', __( 'Your email address isn&#8217;t correct.', 'listings' ) );
	}

	if ( email_exists( $email ) ) {
		return new WP_Error( 'validation-error', __( 'This email is already registered, please choose another one.', 'listings' ) );
	}

	// Ensure username is unique
	$append     = 1;
	$o_username = $username;

	while ( username_exists( $username ) ) {
		$username = $o_username . $append;
		$append ++;
	}

	// Final error checking
	$reg_errors = new WP_Error();
	$reg_errors = apply_filters( 'listings_registration_errors', $reg_errors, $username, $email );

	do_action( 'listings_register_post', $username, $email, $reg_errors );

	if ( $reg_errors->get_error_code() ) {
		return $reg_errors;
	}

	// Create account
	$new_user = array(
		'user_login' => $username,
		'user_pass'  => $password,
		'user_email' => $email,
		'role'       => $role
    );

    $user_id = wp_insert_user( apply_filters( 'listings_create_account_data', $new_user ) );

    if ( is_wp_error( $user_id ) ) {
    	return $user_id;
    }

    // Notify
	listings_notify_new_user( $user_id, $password );
    // Login
    wp_set_auth_cookie( $user_id, true, is_ssl() );
    $current_user = get_user_by( 'id', $user_id );

    return true;
}
endif;

/**
 * True if an the user can post a listing. If accounts are required, and reg is enabled, users can post (they signup at the same time).
 *
 * @return bool
 */
function listings_user_can_post_listing() {
	$can_post = true;

	if ( ! is_user_logged_in() ) {
		if ( listings_user_requires_account() && ! listings_enable_registration() ) {
			$can_post = false;
		}
	}

	return apply_filters( 'listings_user_can_post_listing', $can_post );
}

/**
 * True if an the user can edit a listing.
 *
 * @return bool
 */
function listings_user_can_edit_listing( $listing_id ) {
	$can_edit = true;

	if ( ! is_user_logged_in() || ! $listing_id ) {
		$can_edit = false;
	} else {
		$listing      = get_post( $listing_id );

		if ( ! $listing || ( absint( $listing->post_author ) !== get_current_user_id() && ! current_user_can( 'edit_post', $listing_id ) ) ) {
			$can_edit = false;
		}
	}

	return apply_filters( 'listings_user_can_edit_listing', $can_edit, $listing_id );
}

/**
 * True if registration is enabled.
 *
 * @return bool
 */
function listings_enable_registration() {
	return apply_filters( 'listings_enable_registration', get_option( 'listings_enable_registration' ) == 1 ? true : false );
}

/**
 * True if usernames are generated from email addresses.
 *
 * @return bool
 */
function listings_generate_username_from_email() {
	return apply_filters( 'listings_generate_username_from_email', get_option( 'listings_generate_username_from_email' ) == 1 ? true : false );
}

/**
 * True if an account is required to post a job.
 *
 * @return bool
 */
function listings_user_requires_account() {
	return apply_filters( 'listings_user_requires_account', get_option( 'listings_user_requires_account' ) == 1 ? true : false );
}

/**
 * True if users are allowed to edit submissions that are pending approval.
 *
 * @return bool
 */
function listings_user_can_edit_pending_submissions() {
	return apply_filters( 'listings_user_can_edit_pending_submissions', get_option( 'listings_user_can_edit_pending_submissions' ) == 1 ? true : false );
}

/**
 * Based on wp_dropdown_categories, with the exception of supporting multiple selected categories.
 * @see  wp_dropdown_categories
 */
function listings_dropdown_categories( $args = '' ) {
	$defaults = array(
		'orderby'         => 'id',
		'order'           => 'ASC',
		'show_count'      => 0,
		'hide_empty'      => 1,
		'child_of'        => 0,
		'exclude'         => '',
		'echo'            => 1,
		'selected'        => 0,
		'hierarchical'    => 0,
		'name'            => 'cat',
		'id'              => '',
		'class'           => 'listings-category-dropdown ' . ( is_rtl() ? 'chosen-rtl' : '' ),
		'depth'           => 0,
		'taxonomy'        => 'job_listing_category',
		'value'           => 'id',
		'multiple'        => true,
		'show_option_all' => false,
		'placeholder'     => __( 'Choose a category&hellip;', 'listings' ),
		'no_results_text' => __( 'No results match', 'listings' ),
		'multiple_text'   => __( 'Select Some Options', 'listings' )
	);

	$r = wp_parse_args( $args, $defaults );

	if ( ! isset( $r['pad_counts'] ) && $r['show_count'] && $r['hierarchical'] ) {
		$r['pad_counts'] = true;
	}

	extract( $r );

	// Store in a transient to help sites with many cats
	$categories_hash = 'listings_cats_' . md5( json_encode( $r ) . \Listings\CacheHelper::get_transient_version( 'listings_get_' . $r['taxonomy'] ) );
	$categories      = get_transient( $categories_hash );

	if ( empty( $categories ) ) {
		$categories = get_terms( $taxonomy, array(
			'orderby'         => $r['orderby'],
			'order'           => $r['order'],
			'hide_empty'      => $r['hide_empty'],
			'child_of'        => $r['child_of'],
			'exclude'         => $r['exclude'],
			'hierarchical'    => $r['hierarchical']
		) );
		set_transient( $categories_hash, $categories, DAY_IN_SECONDS * 30 );
	}

	$name       = esc_attr( $name );
	$class      = esc_attr( $class );
	$id         = $id ? esc_attr( $id ) : $name;

	$output = "<select name='" . esc_attr( $name ) . "[]' id='" . esc_attr( $id ) . "' class='" . esc_attr( $class ) . "' " . ( $multiple ? "multiple='multiple'" : '' ) . " data-placeholder='" . esc_attr( $placeholder ) . "' data-no_results_text='" . esc_attr( $no_results_text ) . "' data-multiple_text='" . esc_attr( $multiple_text ) . "'>\n";

	if ( $show_option_all ) {
		$output .= '<option value="">' . esc_html( $show_option_all ) . '</option>';
	}

	if ( ! empty( $categories ) ) {
		$walker = new \Listings\CategoryWalker();

		if ( $hierarchical ) {
			$depth = $r['depth'];  // Walk the full depth.
		} else {
			$depth = -1; // Flat.
		}

		$output .= $walker->walk( $categories, $depth, $r );
	}

	$output .= "</select>\n";

	if ( $echo ) {
		echo $output;
	}

	return $output;
}

/**
 * Get the page ID of a page if set, with PolyLang compat.
 * @param  string $page e.g. job_dashboard, submit_job_form, jobs
 * @return int
 */
function listings_get_page_id( $page ) {
	$page_id = get_option( 'listings_' . $page . '_page_id', false );
	if ( $page_id ) {
		return absint( function_exists( 'pll_get_post' ) ? pll_get_post( $page_id ) : $page_id );
	} else {
		return 0;
	}
}

/**
 * Get the permalink of a page if set
 * @param  string $page e.g. job_dashboard, submit_job_form, jobs
 * @return string|bool
 */
function listings_get_permalink( $page ) {
	if ( $page_id = listings_get_page_id( $page ) ) {
		return get_permalink( $page_id );
	} else {
		return false;
	}
}

/**
 * Filters the upload dir when $listings_upload is true
 * @param  array $pathdata
 * @return array
 */
function listings_upload_dir( $pathdata ) {
	global $listings_upload, $listings_uploading_file;

	if ( ! empty( $listings_upload ) ) {
		$dir = untrailingslashit( apply_filters( 'listings_upload_dir', 'listings-uploads/' . sanitize_key( $listings_uploading_file ), sanitize_key( $listings_uploading_file ) ) );

		if ( empty( $pathdata['subdir'] ) ) {
			$pathdata['path']   = $pathdata['path'] . '/' . $dir;
			$pathdata['url']    = $pathdata['url'] . '/' . $dir;
			$pathdata['subdir'] = '/' . $dir;
		} else {
			$new_subdir         = '/' . $dir . $pathdata['subdir'];
			$pathdata['path']   = str_replace( $pathdata['subdir'], $new_subdir, $pathdata['path'] );
			$pathdata['url']    = str_replace( $pathdata['subdir'], $new_subdir, $pathdata['url'] );
			$pathdata['subdir'] = $new_subdir;
		}
	}

	return $pathdata;
}

add_filter( 'upload_dir', 'listings_upload_dir' );

/**
 * Prepare files for upload by standardizing them into an array. This adds support for multiple file upload fields.
 * @param  array $file_data
 * @return array
 */
function listings_prepare_uploaded_files( $file_data ) {
	$files_to_upload = array();

	if ( is_array( $file_data['name'] ) ) {
		foreach( $file_data['name'] as $file_data_key => $file_data_value ) {
			if ( $file_data['name'][ $file_data_key ] ) {
				$type              = wp_check_filetype( $file_data['name'][ $file_data_key ] ); // Map mime type to one WordPress recognises
				$files_to_upload[] = array(
					'name'     => $file_data['name'][ $file_data_key ],
					'type'     => $type['type'],
					'tmp_name' => $file_data['tmp_name'][ $file_data_key ],
					'error'    => $file_data['error'][ $file_data_key ],
					'size'     => $file_data['size'][ $file_data_key ]
				);
			}
		}
	} else {
		$type              = wp_check_filetype( $file_data['name'] ); // Map mime type to one WordPress recognises
		$file_data['type'] = $type['type'];
		$files_to_upload[] = $file_data;
	}

	return $files_to_upload;
}

/**
 * Upload a file using WordPress file API.
 * @param  array $file Array of $_FILE data to upload.
 * @param  array $args Optional arguments
 * @return array|WP_Error Array of objects containing either file information or an error
 */
function listings_upload_file( $file, $args = array() ) {
	global $listings_upload, $listings_uploading_file;

	include_once( ABSPATH . 'wp-admin/includes/file.php' );
	include_once( ABSPATH . 'wp-admin/includes/media.php' );

	$args = wp_parse_args( $args, array(
		'file_key'           => '',
		'file_label'         => '',
		'allowed_mime_types' => get_allowed_mime_types()
	) );

	$listings_upload         = true;
	$listings_uploading_file = $args['file_key'];
	$uploaded_file              = new stdClass();

	if ( ! in_array( $file['type'], $args['allowed_mime_types'] ) ) {
		if ( $args['file_label'] ) {
			return new WP_Error( 'upload', sprintf( __( '"%s" (filetype %s) needs to be one of the following file types: %s', 'listings' ), $args['file_label'], $file['type'], implode( ', ', array_keys( $args['allowed_mime_types'] ) ) ) );
		} else {
			return new WP_Error( 'upload', sprintf( __( 'Uploaded files need to be one of the following file types: %s', 'listings' ), implode( ', ', array_keys( $args['allowed_mime_types'] ) ) ) );
		}
	} else {
		$upload = wp_handle_upload( $file, apply_filters( 'listings_submit_wp_handle_upload_overrides', array( 'test_form' => false ) ) );
		if ( ! empty( $upload['error'] ) ) {
			return new WP_Error( 'upload', $upload['error'] );
		} else {
			$uploaded_file->url       = $upload['url'];
			$uploaded_file->file      = $upload['file'];
			$uploaded_file->name      = basename( $upload['file'] );
			$uploaded_file->type      = $upload['type'];
			$uploaded_file->size      = $file['size'];
			$uploaded_file->extension = substr( strrchr( $uploaded_file->name, '.' ), 1 );
		}
	}

	$listings_upload         = false;
	$listings_uploading_file = '';

	return $uploaded_file;
}
