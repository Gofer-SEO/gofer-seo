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

function gofer_seo_get_icon_menu_base64() {
	return 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmVyc2lvbj0iMS4xIiB2aWV3Qm94PSIwIDAgMTAwIDEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczpjYz0iaHR0cDovL2NyZWF0aXZlY29tbW9ucy5vcmcvbnMjIiB4bWxuczpkYz0iaHR0cDovL3B1cmwub3JnL2RjL2VsZW1lbnRzLzEuMS8iIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+CiA8dGl0bGU+TWVudSBJY29uPC90aXRsZT4KIDxtZXRhZGF0YT4KICA8cmRmOlJERj4KICAgPGNjOldvcmsgcmRmOmFib3V0PSIiPgogICAgPGNjOmxpY2Vuc2UgcmRmOnJlc291cmNlPSJodHRwOi8vY3JlYXRpdmVjb21tb25zLm9yZy9wdWJsaWNkb21haW4vemVyby8xLjAvIi8+CiAgICA8ZGM6ZGF0ZT4yMDIyLTAxLTIyPC9kYzpkYXRlPgogICAgPGRjOmNyZWF0b3I+CiAgICAgPGNjOkFnZW50PgogICAgICA8ZGM6dGl0bGU+RWtvSlI8L2RjOnRpdGxlPgogICAgIDwvY2M6QWdlbnQ+CiAgICA8L2RjOmNyZWF0b3I+CiAgICA8ZGM6cHVibGlzaGVyPgogICAgIDxjYzpBZ2VudD4KICAgICAgPGRjOnRpdGxlPkVrb0pSPC9kYzp0aXRsZT4KICAgICA8L2NjOkFnZW50PgogICAgPC9kYzpwdWJsaXNoZXI+CiAgICA8ZGM6bGFuZ3VhZ2U+ZW4tVVM8L2RjOmxhbmd1YWdlPgogICAgPGRjOnNvdXJjZT5odHRwczovL3dvcmRwcmVzcy5vcmcvcGx1Z2lucy9nb2Zlci1zZW8vPC9kYzpzb3VyY2U+CiAgICA8ZGM6dGl0bGU+TWVudSBJY29uPC9kYzp0aXRsZT4KICAgPC9jYzpXb3JrPgogICA8Y2M6TGljZW5zZSByZGY6YWJvdXQ9Imh0dHA6Ly9jcmVhdGl2ZWNvbW1vbnMub3JnL3B1YmxpY2RvbWFpbi96ZXJvLzEuMC8iPgogICAgPGNjOnBlcm1pdHMgcmRmOnJlc291cmNlPSJodHRwOi8vY3JlYXRpdmVjb21tb25zLm9yZy9ucyNSZXByb2R1Y3Rpb24iLz4KICAgIDxjYzpwZXJtaXRzIHJkZjpyZXNvdXJjZT0iaHR0cDovL2NyZWF0aXZlY29tbW9ucy5vcmcvbnMjRGlzdHJpYnV0aW9uIi8+CiAgICA8Y2M6cGVybWl0cyByZGY6cmVzb3VyY2U9Imh0dHA6Ly9jcmVhdGl2ZWNvbW1vbnMub3JnL25zI0Rlcml2YXRpdmVXb3JrcyIvPgogICA8L2NjOkxpY2Vuc2U+CiAgPC9yZGY6UkRGPgogPC9tZXRhZGF0YT4KIDxnPgogIDxnIGZpbGw9IiNjZGNkY2QiPgogICA8cGF0aCBkPSJtNDEuMjA0IDguOTk1LTEuODA5NCAxLjQxNjhjMC41MzMzOCAwLjY2MjIyIDEuMDA2OSAxLjMyODggMS40NTggMS45Nzc0bDEuODkxNy0xLjMwODVjLTAuNDk3MzgtMC43MzI0OC0xLjAzOC0xLjQzNTQtMS41NDAzLTIuMDg1OHptMi44MTg4IDQuMDkzNi0xLjk3NzYgMS4xNjY0YzEuMjEyNiAxLjk3OTggMi4wNzE5IDQuMDU1MSAyLjkyNjYgNi4wMjk2bDIuMTM0Ni0wLjg0NDg4Yy0wLjgzODE1LTIuMjc0LTIuMDIxMS00LjM3NjItMy4wODM2LTYuMzUxMXptMy45MDY3IDguNTMxMS0yLjE1NzEgMC43ODUxNWMwLjc4MjkxIDIuMTk0IDEuNDY3MSA0LjQ0NjkgMi4xMDQ3IDYuNTAxMmwyLjE5NDUtMC42NzY2M2MtMC42NzIxNi0yLjI3MTgtMS40MTkzLTQuNDYxLTIuMTQyMS02LjYwOTd6bTIuODE4OCA4Ljc5NjYtMi4xOTA3IDAuNjg3OTJjMC42NzIzOCAyLjI4NDQgMS41MDEzIDQuNDQ3OCAyLjI5NTQgNi41OTUybDIuMTI3Mi0wLjg2MzYzYy0wLjg5NDE0LTIuMjI5Ni0xLjUyMy00LjIyMzEtMi4yMzE4LTYuNDE5NHptMy4xMTc4IDguNDkzNy0yLjA5MzUgMC45NDIxMWMwLjk0ODI3IDIuMjQ5IDIuMjYwNiA0LjI2ODIgMy40NTAzIDYuMTg3MWwxLjg5NTMtMS4yOTcyYy0xLjMzNzgtMS44ODIxLTIuMzAwMi0zLjkxNzctMy4yNTIxLTUuODMyem00LjU2ODQgNy42NDUxLTEuODMxOCAxLjM4NjljMS40MzY5IDEuOTYxMyAzLjA4MjMgMy42Njc2IDQuNjIzOCA1LjMxMjVsMS42MjYyLTEuNjIyNGMtMS42NjM4LTEuNjA4MS0zLjA2ODMtMy40MTA2LTQuNDE4My01LjA3NzF6bTYuMDQxMyA2LjYyODMtMS41NTUyIDEuNjkzNmMxLjczMzYgMS42MTkyIDMuNjU1NiAzLjA0ODIgNS40MzY2IDQuNDI1OWwxLjM2ODQtMS44NDMxYy0xLjg3MTItMS4zMzE5LTMuNTk4OS0yLjg2NzctNS4yNDk4LTQuMjc2NHptNy4wOTE4IDUuNjM3Ni0xLjM2MDcgMS44NDY3YzEuOTQ4NSAxLjM1MzUgMy40ODU4IDIuNzIxMSA1LjIwMzcgNC4yMzU1IDAuNTM4NTItMC41MjQ3MSAxLjA4NTItMS4wODIyIDEuNjIyNy0xLjYyMjJsLTAuMDExLTAuMDExYy0xLjc2NC0xLjY5OTQtMy41NjU1LTMuMDU2Ni01LjQ1NDctNC40NDl6bTcuMDk5MyA2LjIxNy0xLjc2MDggMS40NzY3YzAuNTk1MDcgMC42OTUwOSAxLjA5NTggMS40MjUzIDEuNTYzNiAyLjEzNDJsMS45MjktMS4yNTI0Yy0wLjUzMzMxLTAuODQ4MjQtMS4xNDctMS42MzY0LTEuNzMxOS0yLjM1ODV6IiBjb2xvcj0iIzAwMDAwMCIgZmlsbD0iI2NkY2RjZCIgc3Ryb2tlLWRhc2hhcnJheT0iNi44OTA3LCAyLjI5NjkiIHN0cm9rZS1kYXNob2Zmc2V0PSI0LjM2NDEiIHN0cm9rZS13aWR0aD0iMS45MTQxIiBzdHlsZT0iLWlua3NjYXBlLXN0cm9rZTpub25lIi8+CiAgPC9nPgogIDxnIGZpbGw9IiNmM2YxZjEiPgogICA8cGF0aCBkPSJtMzguNDM4IDEwLjkyNC0zLjQ0MzEgMC4xMTY3NmMwLjIzNzA0IDMuMjgzNC0wLjUzNzM2IDYuNTE5OC0xLjIzMzYgOS40MjQ1bDMuMjYzNyAxLjEwMjljMC44ODA1Mi0zLjYyNzIgMS4zMDYxLTcuNDQ2IDEuNDEzMS0xMC42NDR6bS01LjkyNTQgMTIuNDc1Yy0yLjA1NjUgNC4wMDktNS40MzMzIDYuOTgxOS04LjYyMTIgOS42ODI4bDIuMTcyIDIuNjc2NyAwLjczMjcxLTAuNTk0MzIgN2UtMyAtNGUtM2MwLjQ2NjI4LTAuMzgzOTcgMC45MjgxNC0wLjc3NjU1IDEuMzg3LTEuMTc3Nmw0ZS0zIC00ZS0zYzMuMTI0My0yLjUzNDcgNS40NzA1LTUuODU1NCA3LjM4NjItOS4wMDk4em0tMTEuMzAxIDExLjc4NGMtMy45NzQzIDIuODQ2NC03LjQwNyA2LjIwMDctMTAuMjg0IDkuOTAzNWwyLjg3NDkgMS44OTkyYzIuNjEzNy0zLjcyNDggNi4xNTE5LTYuNDQzIDkuNTE4MS05LjA3Nzd6bS0xMi4xMDUgMTMuMDg1Yy0yLjI5NyA0LjYzNDQtMi41ODg3IDkuNjA2NC0yLjQ2MjQgMTQuMzE4bDMuNDMxOS0wLjI3Mjk0Yy0wLjUwMDk0LTQuNTA3NCAwLjYzNTg1LTguNjQzNiAyLjEyNTctMTIuNTM2em0xLjM5MDggMTcuMzAyLTMuMzk0NSAwLjU5MDY5YzAuNTYwODkgMy40MDEyIDEuNzE5OCA2LjU4MzggMi44MjA4IDkuNTc3OWwzLjIwMDEtMS4yNzQ4Yy0xLjI2OTEtMi45NDktMS45NzU2LTUuOTk0OC0yLjYyNjQtOC44OTM4eiIgY29sb3I9IiMwMDAwMDAiIHN0cm9rZS1kYXNoYXJyYXk9IjEzLjc4MTQsIDMuNDQ1MzUiIHN0cm9rZS1kYXNob2Zmc2V0PSI0LjEzNDQiIHN0cm9rZS13aWR0aD0iMS45MTQxIiBzdHlsZT0iLWlua3NjYXBlLXN0cm9rZTpub25lIi8+CiAgIDxwYXRoIGQ9Im03MC4yNzggMzQuNTQ0Yy0xLjUyMjQgNC4wNzQyLTQuNDc2NiA3LjI2OTItNy4yMjExIDEwLjE0bDIuMzg4OCAyLjQ4MjNjMy42NDc3LTMuMzA0MyA2LjEzOS03LjI5MDkgOC4wNDg1LTExLjM5OHptLTkuNjkzOCAxMi4zODljLTMuNTA4OCAzLjAyMTMtNy4yNjk1IDUuNTQ1NC0xMC45MzggNy45NTE1bDEuODQ2OSAyLjkwODVjNC4xMTg0LTIuNTYwNiA3Ljg0MDUtNS4zOTU2IDExLjMzMS04LjI0MzZ6bS0xMy44NDcgOS43OTg1Yy00LjE1MiAyLjU5MDUtNy44MTM5IDUuMDYxMy0xMS41NTUgNy43MTE2bDIuMDMzNyAyLjc4MTRjMy45MTgyLTIuODExMyA4LjAwMDUtNS40NDQ2IDExLjM2NC03LjU4NjN6bS0xNC4zNTYgOS44MDU5Yy0zLjczMDcgMi43NTMxLTYuODY3OCA1LjkwNDktOS44MDIyIDkuMjQxM2wyLjY5OTIgMi4xMzg0YzIuNzAzOC0zLjQwNDEgNi4wMTM3LTYuMDcwNCA5LjIxOTQtOC42NTg2eiIgY29sb3I9IiMwMDAwMDAiIHN0cm9rZS1kYXNoYXJyYXk9IjEzLjc4MTQsIDMuNDQ1MzUiIHN0cm9rZS1kYXNob2Zmc2V0PSIuNjg5MDciIHN0cm9rZS13aWR0aD0iMS45MTQxIiBzdHlsZT0iLWlua3NjYXBlLXN0cm9rZTpub25lIi8+CiAgIDxwYXRoIGQ9Im03NC4zNTMgNzYuNjYxYy0yLjE0MTkgMC4wNzM5My00LjIyODIgMC4zMzY1NC02LjIwNTEgMC41NjQyN2wwLjQ4OTgxIDMuNDA5NWMyLjAxMzQtMC4yOTg2NiA0LjAxNDUtMC40MDc3NCA1Ljg5OTgtMC41MzE5MnptLTkuNjg2MyAxLjE1MTVjLTQuOTIyMyAwLjg0MzM4LTkuNDMgMi44MzQzLTEzLjQyNiA1LjEyMjFsMS44OTkyIDIuODc0OWMzLjgwMy0yLjUyODEgNy45OTk0LTMuNTkxOCAxMi4xOC00LjYxNzl6bS0xNi4xOTEgNy4xODUzYy0zLjY4MzggMi42MTg3LTcuOTkyNyAzLjM5MjEtMTIuMDcxIDMuODI4bDAuMTI2MzMgMy40NDMxYzUuMTU1Mi0wLjA2Nzg3IDkuNzgxMS0yLjA1MzggMTMuODU5LTQuNDAwMXptLTE5Ljc5MSAyLjk2ODMtMC44MTg2NSAzLjM0NTljMS42OTA0IDAuNDQ1MzYgMy4zOTgyIDAuNjc5NjIgNS4wMDk5IDAuODU3MTJsMC4yOTkxNy0zLjQzNTZjLTEuNTY1Mi0wLjEyMTYzLTMuMDcyNS0wLjQzNDEyLTQuNDkwNC0wLjc2NzQxeiIgY29sb3I9IiMwMDAwMDAiIHN0cm9rZS1kYXNoYXJyYXk9IjEzLjc4MDgsIDMuNDQ1MjEiIHN0cm9rZS1kYXNob2Zmc2V0PSI4Ljk1NzUiIHN0cm9rZS13aWR0aD0iMS45MTQiLz4KICAgPGNpcmNsZSBjeD0iMzYuNzYzIiBjeT0iNi4wNDUzIiByPSI1LjQyNjEiIHN0cm9rZS13aWR0aD0iLjgyMDMyIiBzdHlsZT0icGFpbnQtb3JkZXI6bWFya2VycyBzdHJva2UgZmlsbCIvPgogICA8Y2lyY2xlIGN4PSI3NC43NDUiIGN5PSIyOC4xMTEiIHI9IjguMzIiIHN0cm9rZS13aWR0aD0iMS4yNTc4IiBzdHlsZT0icGFpbnQtb3JkZXI6bWFya2VycyBzdHJva2UgZmlsbCIvPgogICA8Y2lyY2xlIGN4PSI4NS4yMzYiIGN5PSI3Ny4zMDgiIHI9IjExLjIxNCIgc3Ryb2tlLXdpZHRoPSIxLjY5NTMiIHN0eWxlPSJwYWludC1vcmRlcjptYXJrZXJzIHN0cm9rZSBmaWxsIi8+CiAgIDxjaXJjbGUgY3g9IjE2LjE0NCIgY3k9Ijg2LjM1MSIgcj0iMTIuNjYxIiBzdHJva2Utd2lkdGg9IjEuOTE0MSIgc3R5bGU9InBhaW50LW9yZGVyOm1hcmtlcnMgc3Ryb2tlIGZpbGwiLz4KICA8L2c+CiA8L2c+Cjwvc3ZnPgo=';
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
