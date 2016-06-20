<?php

if ( ! function_exists( 'get_job_listings_keyword_search' ) ) :
	/**
	 * Join and where query for keywords
	 *
	 * @param array $args
	 * @return array
	 */
	function get_job_listings_keyword_search( $args ) {
		global $wpdb, $job_manager_keyword;

		$conditions   = array();
		$conditions[] = "{$wpdb->posts}.post_title LIKE '%" . esc_sql( $job_manager_keyword ) . "%'";
		$conditions[] = "{$wpdb->posts}.ID IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_value LIKE '%" . esc_sql( $job_manager_keyword ) . "%' )";
		$conditions[] = "{$wpdb->posts}.ID IN ( SELECT object_id FROM {$wpdb->term_relationships} AS tr LEFT JOIN {$wpdb->terms} AS t ON tr.term_taxonomy_id = t.term_id WHERE t.name LIKE '%" . esc_sql( $job_manager_keyword ) . "%' )";

		if ( ctype_alnum( $job_manager_keyword ) ) {
			$conditions[] = "{$wpdb->posts}.post_content RLIKE '[[:<:]]" . esc_sql( $job_manager_keyword ) . "[[:>:]]'";
		} else {
			$conditions[] = "{$wpdb->posts}.post_content LIKE '%" . esc_sql( $job_manager_keyword ) . "%'";
		}

		$args['where'] .= " AND ( " . implode( ' OR ', $conditions ) . " ) ";

		return $args;
	}
endif;

if ( ! function_exists( 'get_job_listing_post_statuses' ) ) :
/**
 * Get post statuses used for jobs
 *
 * @access public
 * @return array
 */
function get_job_listing_post_statuses() {
	return apply_filters( 'job_listing_post_statuses', array(
		'draft'           => _x( 'Draft', 'post status', 'wp-job-manager' ),
		'expired'         => _x( 'Expired', 'post status', 'wp-job-manager' ),
		'preview'         => _x( 'Preview', 'post status', 'wp-job-manager' ),
		'pending'         => _x( 'Pending approval', 'post status', 'wp-job-manager' ),
		'pending_payment' => _x( 'Pending payment', 'post status', 'wp-job-manager' ),
		'publish'         => _x( 'Active', 'post status', 'wp-job-manager' ),
	) );
}
endif;

if ( ! function_exists( 'get_featured_job_ids' ) ) :
/**
 * Gets the ids of featured jobs.
 *
 * @access public
 * @return array
 */
function get_featured_job_ids() {
	return get_posts( array(
		'posts_per_page' => -1,
		'post_type'      => 'job_listing',
		'post_status'    => 'publish',
		'meta_key'       => '_featured',
		'meta_value'     => '1',
		'fields'         => 'ids'
	) );
}
endif;

if ( ! function_exists( 'get_job_listing_categories' ) ) :
/**
 * Get job categories
 *
 * @access public
 * @return array
 */
function get_job_listing_categories() {
	if ( ! get_option( 'job_manager_enable_categories' ) ) {
		return array();
	}

	return get_terms( "job_listing_category", array(
		'orderby'       => 'name',
	    'order'         => 'ASC',
	    'hide_empty'    => false,
	) );
}
endif;

if ( ! function_exists( 'wp_job_manager_notify_new_user' ) ) :
	/**
	 * Handle account creation.
	 *
	 * @param  int $user_id
	 * @param  string $password
	 */
	function wp_job_manager_notify_new_user( $user_id, $password ) {
		global $wp_version;

		if ( version_compare( $wp_version, '4.3.1', '<' ) ) {
			wp_new_user_notification( $user_id, $password );
		} else {
			wp_new_user_notification( $user_id, null, 'both' );
		}
	}
endif;

if ( ! function_exists( 'job_manager_create_account' ) ) :
/**
 * Handle account creation.
 *
 * @param  array $args containing username, email, role
 * @param  string $deprecated role string
 * @return WP_error | bool was an account created?
 */
function wp_job_manager_create_account( $args, $deprecated = '' ) {
	global $current_user;

	// Soft Deprecated in 1.20.0
	if ( ! is_array( $args ) ) {
		$username = '';
		$password = wp_generate_password();
		$email    = $args;
		$role     = $deprecated;
	} else {
		$defaults = array(
			'username' => '',
			'email'    => '',
			'password' => wp_generate_password(),
			'role'     => get_option( 'default_role' )
		);

		$args = wp_parse_args( $args, $defaults );
		extract( $args );
	}

	$username = sanitize_user( $username );
	$email    = apply_filters( 'user_registration_email', sanitize_email( $email ) );

	if ( empty( $email ) ) {
		return new WP_Error( 'validation-error', __( 'Invalid email address.', 'wp-job-manager' ) );
	}

	if ( empty( $username ) ) {
		$username = sanitize_user( current( explode( '@', $email ) ) );
	}

	if ( ! is_email( $email ) ) {
		return new WP_Error( 'validation-error', __( 'Your email address isn&#8217;t correct.', 'wp-job-manager' ) );
	}

	if ( email_exists( $email ) ) {
		return new WP_Error( 'validation-error', __( 'This email is already registered, please choose another one.', 'wp-job-manager' ) );
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
	$reg_errors = apply_filters( 'job_manager_registration_errors', $reg_errors, $username, $email );

	do_action( 'job_manager_register_post', $username, $email, $reg_errors );

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

    $user_id = wp_insert_user( apply_filters( 'job_manager_create_account_data', $new_user ) );

    if ( is_wp_error( $user_id ) ) {
    	return $user_id;
    }

    // Notify
    wp_job_manager_notify_new_user( $user_id, $password, $new_user );
    // Login
    wp_set_auth_cookie( $user_id, true, is_ssl() );
    $current_user = get_user_by( 'id', $user_id );

    return true;
}
endif;

/**
 * True if an the user can post a job. If accounts are required, and reg is enabled, users can post (they signup at the same time).
 *
 * @return bool
 */
function job_manager_user_can_post_job() {
	$can_post = true;

	if ( ! is_user_logged_in() ) {
		if ( listings_user_requires_account() && ! listings_enable_registration() ) {
			$can_post = false;
		}
	}

	return apply_filters( 'job_manager_user_can_post_job', $can_post );
}

/**
 * True if an the user can edit a job.
 *
 * @return bool
 */
function job_manager_user_can_edit_job( $job_id ) {
	$can_edit = true;

	if ( ! is_user_logged_in() || ! $job_id ) {
		$can_edit = false;
	} else {
		$job      = get_post( $job_id );

		if ( ! $job || ( absint( $job->post_author ) !== get_current_user_id() && ! current_user_can( 'edit_post', $job_id ) ) ) {
			$can_edit = false;
		}
	}

	return apply_filters( 'job_manager_user_can_edit_job', $can_edit, $job_id );
}

/**
 * True if registration is enabled.
 *
 * @return bool
 */
function listings_enable_registration() {
	return apply_filters( 'listings_enable_registration', get_option( 'job_manager_enable_registration' ) == 1 ? true : false );
}

/**
 * True if usernames are generated from email addresses.
 *
 * @return bool
 */
function listings_generate_username_from_email() {
	return apply_filters( 'listings_generate_username_from_email', get_option( 'job_manager_generate_username_from_email' ) == 1 ? true : false );
}

/**
 * True if an account is required to post a job.
 *
 * @return bool
 */
function listings_user_requires_account() {
	return apply_filters( 'listings_user_requires_account', get_option( 'job_manager_user_requires_account' ) == 1 ? true : false );
}

/**
 * True if users are allowed to edit submissions that are pending approval.
 *
 * @return bool
 */
function job_manager_user_can_edit_pending_submissions() {
	return apply_filters( 'job_manager_user_can_edit_pending_submissions', get_option( 'job_manager_user_can_edit_pending_submissions' ) == 1 ? true : false );
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
		'class'           => 'job-manager-category-dropdown ' . ( is_rtl() ? 'chosen-rtl' : '' ),
		'depth'           => 0,
		'taxonomy'        => 'job_listing_category',
		'value'           => 'id',
		'multiple'        => true,
		'show_option_all' => false,
		'placeholder'     => __( 'Choose a category&hellip;', 'wp-job-manager' ),
		'no_results_text' => __( 'No results match', 'wp-job-manager' ),
		'multiple_text'   => __( 'Select Some Options', 'wp-job-manager' )
	);

	$r = wp_parse_args( $args, $defaults );

	if ( ! isset( $r['pad_counts'] ) && $r['show_count'] && $r['hierarchical'] ) {
		$r['pad_counts'] = true;
	}

	extract( $r );

	// Store in a transient to help sites with many cats
	$categories_hash = 'jm_cats_' . md5( json_encode( $r ) . \Listings\CacheHelper::get_transient_version( 'jm_get_' . $r['taxonomy'] ) );
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
function job_manager_get_page_id( $page ) {
	$page_id = get_option( 'job_manager_' . $page . '_page_id', false );
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
function job_manager_get_permalink( $page ) {
	if ( $page_id = job_manager_get_page_id( $page ) ) {
		return get_permalink( $page_id );
	} else {
		return false;
	}
}

/**
 * Filters the upload dir when $job_manager_upload is true
 * @param  array $pathdata
 * @return array
 */
function job_manager_upload_dir( $pathdata ) {
	global $job_manager_upload, $job_manager_uploading_file;

	if ( ! empty( $job_manager_upload ) ) {
		$dir = untrailingslashit( apply_filters( 'job_manager_upload_dir', 'job-manager-uploads/' . sanitize_key( $job_manager_uploading_file ), sanitize_key( $job_manager_uploading_file ) ) );

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
add_filter( 'upload_dir', 'job_manager_upload_dir' );

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
 * @param  array $file_data Array of $_FILE data to upload.
 * @param  array $args Optional arguments
 * @return array|WP_Error Array of objects containing either file information or an error
 */
function listings_upload_file( $file, $args = array() ) {
	global $job_manager_upload, $job_manager_uploading_file;

	include_once( ABSPATH . 'wp-admin/includes/file.php' );
	include_once( ABSPATH . 'wp-admin/includes/media.php' );

	$args = wp_parse_args( $args, array(
		'file_key'           => '',
		'file_label'         => '',
		'allowed_mime_types' => get_allowed_mime_types()
	) );

	$job_manager_upload         = true;
	$job_manager_uploading_file = $args['file_key'];
	$uploaded_file              = new stdClass();

	if ( ! in_array( $file['type'], $args['allowed_mime_types'] ) ) {
		if ( $args['file_label'] ) {
			return new WP_Error( 'upload', sprintf( __( '"%s" (filetype %s) needs to be one of the following file types: %s', 'wp-job-manager' ), $args['file_label'], $file['type'], implode( ', ', array_keys( $args['allowed_mime_types'] ) ) ) );
		} else {
			return new WP_Error( 'upload', sprintf( __( 'Uploaded files need to be one of the following file types: %s', 'wp-job-manager' ), implode( ', ', array_keys( $args['allowed_mime_types'] ) ) ) );
		}
	} else {
		$upload = wp_handle_upload( $file, apply_filters( 'submit_job_wp_handle_upload_overrides', array( 'test_form' => false ) ) );
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

	$job_manager_upload         = false;
	$job_manager_uploading_file = '';

	return $uploaded_file;
}

/**
 * Calculate and return the job expiry date
 * @param  int $job_id
 * @return string
 */
function calculate_job_expiry( $job_id ) {
	// Get duration from the product if set...
	$duration = get_post_meta( $job_id, '_job_duration', true );

	// ...otherwise use the global option
	if ( ! $duration ) {
		$duration = absint( get_option( 'job_manager_submission_duration' ) );
	}

	if ( $duration ) {
		return date( 'Y-m-d', strtotime( "+{$duration} days", current_time( 'timestamp' ) ) );
	}

	return '';
}

/**
 * Duplicate a listing.
 * @param  int $post_id
 * @return int 0 on fail or the post ID.
 */
function job_manager_duplicate_listing( $post_id ) {
	if ( empty( $post_id ) || ! ( $post = get_post( $post_id ) ) ) {
		return 0;
	}

	/** @var $wpdb \wpdb */
	global $wpdb;

	/**
	 * Duplicate the post.
	 */
	$new_post_id = wp_insert_post( array(
		'comment_status' => $post->comment_status,
		'ping_status'    => $post->ping_status,
		'post_author'    => $post->post_author,
		'post_content'   => $post->post_content,
		'post_excerpt'   => $post->post_excerpt,
		'post_name'      => $post->post_name,
		'post_parent'    => $post->post_parent,
		'post_password'  => $post->post_password,
		'post_status'    => 'preview',
		'post_title'     => $post->post_title,
		'post_type'      => $post->post_type,
		'to_ping'        => $post->to_ping,
		'menu_order'     => $post->menu_order
	) );

	/**
	 * Copy taxonomies.
	 */
	$taxonomies = get_object_taxonomies( $post->post_type );

	foreach ( $taxonomies as $taxonomy ) {
		$post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );
		wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
	}

	/*
	 * Duplicate post meta, aside from some reserved fields.
	 */
	$post_meta = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id=%d", $post_id ) );

	if ( ! empty( $post_meta ) ) {
		$post_meta = wp_list_pluck( $post_meta, 'meta_value', 'meta_key' );
		foreach ( $post_meta as $meta_key => $meta_value ) {
			if ( in_array( $meta_key, apply_filters( 'job_manager_duplicate_listing_ignore_keys', array( '_filled', '_featured', '_job_expires', '_job_duration', '_package_id', '_user_package_id' ) ) ) ) {
				continue;
			}
			update_post_meta( $new_post_id, $meta_key, $meta_value );
		}
	}

	update_post_meta( $new_post_id, '_filled', 0 );
	update_post_meta( $new_post_id, '_featured', 0 );

	return $new_post_id;
}
