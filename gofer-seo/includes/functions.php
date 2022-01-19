<?php
/**
 * Gofer SEO: Functions
 *
 * Contains all general functions that are used throughout the plugin.
 *
 * @package Gofer SEO
 */

/**
 * Is Minify Enabled.
 *
 * @since 1.0.2
 *
 * @return bool
 */
function gofer_seo_is_min_enabled() {
	static $enabled;
	if ( ! is_null( $enabled ) ) {
		return $enabled;
	}
	$gofer_seo_options = Gofer_SEO_Options::get_instance();

	$enabled = $gofer_seo_options->options['modules']['advanced']['enable_min_files'];

	return $enabled;
}

/**
 * Get Enabled Post Types.
 *
 * @since 1.0.0
 *
 * @param string $module
 * @return string[]
 */
function gofer_seo_get_enabled_post_types( $module = 'general' ) {
	$enabled_post_types = array();
	$gofer_seo_options  = Gofer_SEO_Options::get_instance();

	switch ( $module ) {
		case 'general':
			$enabled_post_types = array_keys( array_filter( $gofer_seo_options->options['modules']['general']['enable_post_types'] ) );
			break;
		case 'social_media':
			$enabled_post_types = array_keys( array_filter( $gofer_seo_options->options['modules']['social_media']['enable_post_types'] ) );
			break;
		case 'sitemap':
			$enabled_post_types = array_keys( array_filter( $gofer_seo_options->options['modules']['sitemap']['enable_post_types'] ) );
			break;
	}

	return $enabled_post_types;
}

/**
 * Get Enabled Post Types.
 *
 * @since 1.0.0
 *
 * @param string $module
 * @return string[]
 */
function gofer_seo_get_enabled_taxonomies( $module = 'general' ) {
	$enabled_post_types = array();
	$gofer_seo_options  = Gofer_SEO_Options::get_instance();

	switch ( $module ) {
		case 'general':
			$enabled_post_types = array_keys( array_filter( $gofer_seo_options->options['modules']['general']['enable_taxonomies'] ) );
			break;
//		case 'social_media':
//			$enabled_post_types = array_keys( array_filter( $gofer_seo_options->options['modules']['social_media']['enable_post_types'] ) );
//			break;
		case 'sitemap':
			$enabled_post_types = array_keys( array_filter( $gofer_seo_options->options['modules']['sitemap']['enable_taxonomies'] ) );
			break;
	}

	return $enabled_post_types;
}

/**
 * Get Conflict Shortcodes.
 *
 * @since 1.0.0
 *
 * @return string[]
 */
function gofer_seo_get_conflict_shortcodes() {
	$conflict_shortcodes = array(
		'woocommerce_my_account'     => '[woocommerce_my_account]',     // 'WooCommerce Login'.
		'woocommerce_checkout'       => '[woocommerce_checkout]',       // 'WooCommerce Checkout'.
		'woocommerce_order_tracking' => '[woocommerce_order_tracking]', // 'WooCommerce Order Tracking'.
		'woocommerce_cart'           => '[woocommerce_cart]',           // 'WooCommerce Cart'.
		'wwp_registration_form'      => '[wwp_registration_form]',      // 'WooCommerce Registration'.
	);

	return $conflict_shortcodes;
}

/**
 * Do Shortcodes.
 *
 * @since 1.0.0
 *
 * @param string $content
 * @param array  $exclude_shortcodes
 * @return string
 */
function gofer_seo_do_shortcodes( $content, $exclude_shortcodes = array() ) {
	global $shortcode_tags;
	$tmp_shortcode_tags = $shortcode_tags;

	$conflict_shortcodes = gofer_seo_get_conflict_shortcodes();
	foreach ( $conflict_shortcodes as $slug => $shortcode ) {
		if ( is_numeric( $slug ) ) {
			$slug = str_replace( array( '[', ']' ), '', $slug );
		}

		if ( isset( $shortcode_tags[ $slug ] ) ) {
			unset( $shortcode_tags[ $slug ] );
		}
	}

	$new_content = do_shortcode( $content );

	// Restores any conflicting/duplicate shortcodes.
	// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	$shortcode_tags = $tmp_shortcode_tags;
	return $new_content;
}

/**
 * AJAX (JSON) Success.
 *
 * @since 1.0.0
 *
 * @param string $action
 * @param array  $data
 * @return void
 */
function gofer_seo_ajax_success( $action, $data = array() ) {
	static $s_data;
	if ( is_null( $s_data ) ) {
		$s_data = array();

		add_action( 'wp_ajax_' . $action, function() {
			$data = get_transient( 'gofer_seo_ajax_success_data' );
			if ( empty( $data ) ) {
				$data = __( 'Success.', 'gofer-seo' );
			}

			wp_send_json_success( $data );
		}, 20 );
	}

	if ( ! is_array( $data ) ) {
		$data = array( $data );
	}

	$s_data = array_merge_recursive( $s_data, $data );

	set_transient( 'gofer_seo_ajax_success_data', $s_data, 30 );
}

/**
 * Un-parse URL
 *
 * Convert back from parse_url.
 *
 * @since 1.0.0
 *
 * @link http://www.php.net/manual/en/function.parse-url.php#106731
 *
 * @param array $parsed_url {
 *     @type string $scheme
 *     @type string $host
 *     @type string $port
 *     @type string $user
 *     @type string $pass
 *     @type string $path
 *     @type string $query
 *     @type string $fragment
 * }
 * @return string
 */
function gofer_seo_unparse_url( $parsed_url ) {
	$scheme = isset( $parsed_url['scheme'] ) ? $parsed_url['scheme'] . '://' : '';
	$host   = isset( $parsed_url['host'] ) ? $parsed_url['host'] : '';
	if ( ! empty( $host ) && empty( $scheme ) ) {
		$scheme = '//';
	}
	$port     = isset( $parsed_url['port'] ) ? ':' . $parsed_url['port'] : '';
	$user     = isset( $parsed_url['user'] ) ? $parsed_url['user'] : '';
	$pass     = isset( $parsed_url['pass'] ) ? ':' . $parsed_url['pass'] : '';
	$pass     = ( $user || $pass ) ? "$pass@" : '';
	$path     = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '';
	$query    = isset( $parsed_url['query'] ) ? '?' . $parsed_url['query'] : '';
	$fragment = isset( $parsed_url['fragment'] ) ? '#' . $parsed_url['fragment'] : '';

	return "$scheme$user$pass$host$port$path$query$fragment";
}

/**
 * Get Timezone Designator.
 *
 * @since 1.0.0
 *
 * @return string
 */
function gofer_seo_get_timezone_designator() {
	static $timezone_designator;
	if ( ! is_null( $timezone_designator ) ) {
		return $timezone_designator;
	}

	$minutes             = 60 * get_option('gmt_offset');
	$sign                = $minutes < 0 ? "-" : "+";
	$minutes             = abs( $minutes );
	$timezone_designator = sprintf(
		"%s%02d:%02d",
		$sign,
		$minutes / 60,
		$minutes % 60
	);

	return $timezone_designator;
}

/**
 * Get UTM URL.
 *
 * Returns a UTM structured URL to our product page.
 *
 * @since 1.0.0
 *
 * @param string $url
 * @param array  $args
 * @return string
 */
function gofer_seo_get_utm_url( $url = 'http://gofer-seo.com/', $args = array() ) {
	$default_args = array(
		'utm_source'   => 'WordPress',
		'utm_medium'   => 'default',
		'utm_campaign' => 'default',
		'utm_term'     => '',
		'utm_content'  => '',
	);
	$args = wp_parse_args( $args, $default_args );
	foreach ( $args as $key => $value ) {
		if ( empty( $value ) ) {
			unset( $args[ $key ] );
		}
	}

	return add_query_arg( $args, $url );
}

/**
 * Get Link Paginated.
 *
 * @since 1.0.0
 *
 * @param string $link
 * @param null   $wp_obj
 * @return string
 */
function gofer_seo_get_link_paginated( $link, $wp_obj = null ) {
	if ( is_numeric( $wp_obj ) ) {
		$wp_obj = get_post( $wp_obj );
	} elseif ( is_null( $wp_obj ) ) {
		$context = Gofer_SEO_Context::get_instance();
		$wp_obj  = Gofer_SEO_Context::get_object( $context->context_type, $context->context_key );
	}

	if ( $wp_obj instanceof WP_Post && ( is_single() || is_singular() ) ) {
		if ( get_queried_object_id() === $wp_obj->ID ) {
			$page_number    = gofer_seo_the_page_number();
			$comment_number = gofer_seo_the_comment_number();

			if ( $page_number > 1 ) {
				if ( get_query_var( 'page' ) === $page_number ) {
					if ( ! get_option( 'permalink_structure' ) ) {
						// Non-pretty urls.
						$link = add_query_arg( 'page', $page_number, $link );
					} else {
						$link = trailingslashit( $link ) . user_trailingslashit( $page_number, 'single_paged' );
					}
				} else {
//					if ( get_query_var( 'p' ) || get_query_var( 'name' ) ) {
					if ( ! get_option( 'permalink_structure' ) ) {
						// Non-pretty urls.
						$link = add_query_arg( 'page', $page_number, $link );
					} else {
						$link = trailingslashit( $link ) . user_trailingslashit( $page_number, 'single_paged' );
					}
				}
			}
			if ( $comment_number ) {
				$link = get_comments_pagenum_link( $comment_number );
			}
		}
	} elseif ( $wp_obj instanceof WP_Term ) {
		$queried_obj = get_queried_object();

		if ( $queried_obj instanceof WP_Term && $queried_obj->term_id === $wp_obj->term_id ) {
			$page_number = gofer_seo_the_page_number();

			if ( $page_number > 1 ) {
				$pagination_base_name = 'page';
				if ( ! empty( $wp_rewrite ) && ! empty( $wp_rewrite->pagination_base ) ) {
					$pagination_base_name = $wp_rewrite->pagination_base;
				}

				if ( get_query_var( 'paged' ) === $page_number ) {
					if ( ! get_option( 'permalink_structure' ) ) {
						$link = add_query_arg( 'paged', $page_number, $link );
					} else {
						$link = trailingslashit( $link ) . user_trailingslashit( trailingslashit( $pagination_base_name ) . $page_number, 'paged' );
					}
				} else {
//					$taxonomy = get_query_var( 'taxonomy', '' );
//					if (
//							get_query_var( 'cat' ) ||
//							get_query_var( 'tag_id' ) ||
//							(
//								get_query_var( 'taxonomy' ) &&
//								get_query_var( 'term' ) &&
//								get_query_var( $taxonomy )
//							)
//					) {
					if ( ! get_option( 'permalink_structure' ) ) {
						// Non-pretty urls.
						$link = add_query_arg( 'paged', $page_number, $link );
					} else {
						$link = trailingslashit( $link ) . user_trailingslashit( trailingslashit( $pagination_base_name ) . $page_number, 'paged' );
					}
				}
			}
		}
	}
	// TODO Paginated Archives?

	return $link;
}

/**
 * Get the (Current) Page Number.
 *
 * Returns the number of the current page.
 * This can be used to determine if we're on a paginated page for example.
 *
 * @since 1.0.0
 *
 * @return int
 */
function gofer_seo_the_page_number() {
	global $post;
	if ( ( is_single() || is_singular() ) && false === strpos( $post->post_content, '<!--nextpage-->', 0 ) ) {
		return 0;
	}

	$page_number = get_query_var( 'page', 0 );
	if ( empty( $page_number ) ) {
		$page_number = get_query_var( 'paged', 0 );
	}

	return intval( $page_number );
}

/**
 * Get the (Total) Pages Number.
 *
 * @since 1.0.0
 *
 * @return int
 */
function gofer_seo_the_pages_number() {
	global $post;

	$total_pages = get_query_var( 'max_num_pages' );
	if ( empty( $total_pages ) ) {
		$total_pages = preg_match_all( '<!--nextpage-->', $post->post_content, $matches );
	}

	if ( $total_pages ) {
		// Counts separators which makes 1 additional page.
		$total_pages += 1;
		return intval( $total_pages );
	}
	return 0;
}

/**
 * Get the (Current) Comment Page Number.
 *
 * @since 1.0.0
 *
 * @return int
 */
function gofer_seo_the_comment_number() {
	$comment_page_number = get_query_var( 'cpage', 0 );

	return $comment_page_number;
}

/**
 * Get Module Class.
 *
 * Wrap function for `\Gofer_SEO_Module_Loader::get_loaded_module()`.
 *
 * @see \Gofer_SEO_Module_Loader::get_loaded_module()
 *
 * @since 1.0.0
 *
 * @param string $module_slug
 * @param string $origin
 * @return false|Gofer_SEO_Module
 */
function gofer_seo_get_module( $module_slug, $origin = 'core' ) {
	return Gofer_SEO::get_instance()->module_loader->get_loaded_module( $module_slug, $origin );
}
