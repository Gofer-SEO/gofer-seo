<?php
/**
 * Gofer SEO: Static Methods
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Methods
 *
 * @since 1.0.0
 *
 * These are commonly used functions that can be pulled from anywhere.
 * (or in some cases they're functions waiting for a home)
 */
class Gofer_SEO_Methods {

	/**
	 * Attachment URL => PostIDs
	 *
	 * @var null|array
	 *
	 * @since 1.0.0
	 */
	public static $attachment_url_postids = null;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	function __construct() {}

	/**
	 * Clear WPE Cache
	 *
	 * Clears WP Engine cache.
	 *
	 * @since 1.0.0
	 */
	static function clear_wpe_cache() {
		if ( class_exists( 'WpeCommon' ) ) {
			WpeCommon::purge_memcached();
			WpeCommon::clear_maxcdn_cache();
			WpeCommon::purge_varnish_cache();
		}
	}

	/**
	 * Get Blog Page
	 *
	 * @since 1.0.0
	 *
	 * @param null $p
	 * @return array|null|string|WP_Post
	 */
	static function get_blog_page( $p = null ) {
		static $blog_page      = '';
		static $page_for_posts = '';

		if ( null === $p ) {
			global $post;
		} else {
			// Does not affect global.
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$post = $p;
		}
		if ( '' === $blog_page ) {
			if ( '' === $page_for_posts ) {
				$page_for_posts = get_option( 'page_for_posts' );
			}
			if ( $page_for_posts && is_home() && ( ! is_object( $post ) || ( $page_for_posts !== $post->ID ) ) ) {
				$blog_page = get_post( $page_for_posts );
			}
		}

		return $blog_page;
	}

	/**
	 * Absolutize URL
	 *
	 * Check whether a url is relative and if it is, make it absolute.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url URL to check.
	 * @return string
	 */
	static function absolutize_url( $url ) {
		if ( 0 !== strpos( $url, 'http' ) && '/' !== $url ) {
			if ( 0 === strpos( $url, '//' ) ) {
				// for //<host>/resource type urls.
				$scheme = wp_parse_url( home_url(), PHP_URL_SCHEME );
				$url    = $scheme . ':' . $url;
			} else {
				// for /resource type urls.
				$url = home_url( $url );
			}
		}
		return $url;
	}

	/**
	 * Attachment URL to Post ID
	 *
	 * Returns the (original) post/attachment ID from the URL param given. The function checks if URL is
	 * within, chacks for original attachment URLs, and then custom attachment URLs. The main intent for this function
	 * is to avoid having to query if possible (if cache was set prior), and if not, there is only 1 query per instance
	 * rather than multiple queries per instance.
	 * NOTE: Attempting to paginate the query actually caused the memory to peak higher.
	 * NOTE: The weakest point in this function is multiple calls to Result_2's SQL query for custom attachment URLs.
	 *
	 * This is intended to work much the same way as WP's `attachment_url_to_postid()`.
	 *
	 * @since 1.0.0
	 *
	 * @link https://developer.wordpress.org/reference/functions/attachment_url_to_postid/
	 * @see Gofer_SEO_Methods::set_transient_url_postids()
	 * @see get_transient()
	 * @link https://developer.wordpress.org/reference/functions/get_transient/
	 * @see wpdb::get_results()
	 * @link https://developer.wordpress.org/reference/classes/wpdb/get_results/
	 * @see wp_list_pluck()
	 * @link https://developer.wordpress.org/reference/functions/wp_list_pluck/
	 * @see wp_upload_dir()
	 * @link https://developer.wordpress.org/reference/functions/wp_upload_dir/
	 *
	 * @param string $url Full image URL.
	 * @return int
	 */
	public static function attachment_url_to_postid( $url ) {
		global $wpdb;
		static $results_1;
		static $results_2;

		$id      = 0;
		$url_md5 = md5( $url );

		// Gets the URL => PostIDs array.
		// If static variable is still empty, load transient data.
		if ( is_null( self::$attachment_url_postids ) ) {
			if ( is_multisite() ) {
				self::$attachment_url_postids = get_site_transient( 'gofer_seo_multisite_attachment_url_postids' );
			} else {
				self::$attachment_url_postids = get_transient( 'gofer_seo_attachment_url_postids' );
			}

			// If no transient data, set as (default) empty array.
			if ( false === self::$attachment_url_postids ) {
				self::$attachment_url_postids = array();
			}
		}

		// Search for URL and get ID.
		if ( isset( self::$attachment_url_postids[ $url_md5 ] ) ) {
			// If static is already loaded and has URL, then return the URL's Post ID.
			$id = intval( self::$attachment_url_postids[ $url_md5 ] );
		} else {
			// Check to make sure Image URL is not outside the website.
			$uploads_dir = wp_upload_dir();
			if ( false !== strpos( $url, $uploads_dir['baseurl'] . '/' ) ) {
				// Results_1 query looks for URLs with the original guid that is uncropped and unedited.
				if ( is_null( $results_1 ) ) {
					$results_1 = Gofer_SEO_Methods::attachment_url_to_postid_query_1();
				}

				if ( isset( $results_1[ $url_md5 ] ) ) {
					$id = intval( $results_1[ $url_md5 ] );
				}

				// TODO Add setting to enable; this is TOO MEMORY INTENSE which could result in 1 or more crashes,
				// TODO however some may still need custom image URLs.
				// TODO NOTE: Transient data does prevent continual crashes.
				// else {
				// Results_2 query looks for the URL that is cropped and edited. This searches JSON strings
				// and returns the original attachment ID (there is no custom attachment IDs).
				//
				// if ( is_null( $results_2 ) ) {
				// $results_2 = Gofer_SEO_Methods::attachment_url_to_postid_query_2();
				// }
				//
				// if ( isset( $results_2[ $url_md5 ] ) ) {
				// $id = intval( $results_2[ $url_md5 ] );
				// }
				// }
			}

			self::$attachment_url_postids[ $url_md5 ] = $id;

			add_action( 'shutdown', array( 'Gofer_SEO_Methods', 'set_transient_url_postids' ) );
		}

		return $id;
	}

	/**
	 * Set Transient URL Post IDs
	 *
	 * Sets the transient data at the last hook instead at every call.
	 *
	 * @since 1.0.0
	 *
	 * @see set_transient()
	 * @link https://developer.wordpress.org/reference/functions/set_transient/
	 */
	public static function set_transient_url_postids() {
		if ( is_multisite() ) {
			set_site_transient( 'gofer_seo_multisite_attachment_url_postids', self::$attachment_url_postids, 24 * HOUR_IN_SECONDS );
		} else {
			set_transient( 'gofer_seo_attachment_url_postids', self::$attachment_url_postids, 24 * HOUR_IN_SECONDS );
		}

	}

	/**
	 * Attachment URL to Post ID - Query 1
	 *
	 * This is intended to work solely with `Gofer_SEO_Methods::attachment_url_to_post_id()`. Calling this multiple times
	 * is memory intense.
	 *
	 * @since 1.0.0
	 *
	 * @see wpdb::get_results()
	 * @link https://developer.wordpress.org/reference/classes/wpdb/get_results/
	 *
	 * @return array
	 */
	public static function attachment_url_to_postid_query_1() {
		global $wpdb;

		$results_1 = wp_cache_get( 'gofer_seo_attachment_url_to_postid_1' );
		if ( false === $results_1 ) {
			$results_1 = $wpdb->get_results(
				"SELECT ID, MD5(guid) AS guid FROM $wpdb->posts WHERE post_type='attachment' AND post_status='inherit' AND post_mime_type LIKE 'image/%';",
				ARRAY_A
			);
		}
		wp_cache_set( 'gofer_seo_attachment_url_to_postid_1', $results_1, '', 30 * MINUTE_IN_SECONDS );

		if ( $results_1 ) {
			$results_1 = array_combine(
				wp_list_pluck( $results_1, 'guid' ),
				wp_list_pluck( $results_1, 'ID' )
			);
		} else {
			$results_1 = array();
		}

		return $results_1;
	}

	/**
	 * Attachment URL to Post ID - Query 2
	 *
	 * Unused/Conceptual function. This is intended to work solely with `Gofer_SEO_Methods::attachment_url_to_post_id()`.
	 * Calling this multiple times is memory intense. It's intended to query for custom images, and data for those types
	 * of images only exists in the postmeta database table
	 *
	 * TODO Investigate unserialize() memory consumption/leak.
	 *
	 * @since 1.0.0
	 *
	 * @see Gofer_SEO_Methods::attachment_url_to_postid()
	 * @see unserialize()
	 * @see wpdb::get_results()
	 * @see wp_upload_dir()
	 * @link https://www.evonide.com/breaking-phps-garbage-collection-and-unserialize/
	 * @link http://php.net/manual/en/function.unserialize.php
	 * @link https://developer.wordpress.org/reference/classes/wpdb/get_results/
	 * @link https://developer.wordpress.org/reference/functions/wp_upload_dir/
	 *
	 * @return array
	 */
	public static function attachment_url_to_postid_query_2() {
		global $wpdb;

		$tmp_arr = array();
		// @codingStandardsIgnoreStart WordPress.WP.PreparedSQL.NotPrepared
		$results_2 = $wpdb->get_results(
			"SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE `meta_key` = '_wp_attachment_metadata' AND `meta_value` != '" . serialize( array() ) . "';",
			ARRAY_A
		);
		// @codingStandardsIgnoreStop WordPress.WP.PreparedSQL.NotPrepared
		if ( $results_2 ) {
			for ( $i = 0; $i < count( $results_2 ); $i++ ) {
				// TODO Investigate potential memory leak(s); currently with unserialize.
				$meta_value = maybe_unserialize( $results_2[ $i ]['meta_value'] );

				// TODO Needs Discussion: Should this be added? To handle errors better instead of suspecting Gofer SEO is at fault and lessen support threads.
				/**
				 * This currently handles "warning" notices with unserialize which normally can't be handled with a try/catch.
				 * However, this notice should be identified and corrected; which is separate from the plugin, but
				 * can also triggered by the plugin.
				 *
				 * @since 1.0.0
				 *
				 * @see Gofer_SEO_Methods::error_handle_images()
				 * @see set_error_handler()
				 * @see restore_error_handler()
				 * @link http://php.net/manual/en/function.set-error-handler.php
				 * @link http://php.net/manual/en/function.restore-error-handler.php
				 */
				/*
				set_error_handler( 'Gofer_SEO_Methods::error_handle_images' );
				try {
					$meta_value = unserialize( $results_2[ $i ]['meta_value'] );
				} catch ( Exception $e ) {
					unset( $meta_value );
					restore_error_handler();
					continue;
				}
				restore_error_handler();
				*/

				// Images and Videos use different variable structures.
				if ( false === $meta_value || ! isset( $meta_value['file'] ) && ! isset( $meta_value['sizes'] ) ) {
					continue;
				}

				// Set the URL => PostIDs.
				$uploads_dir = wp_upload_dir();
				$custom_img_base_url = $uploads_dir['baseurl'] . '/' . str_replace( wp_basename( $meta_value['file'] ), '', $meta_value['file'] );
				foreach ( $meta_value['sizes'] as $image_size_arr ) {
					$tmp_arr[ md5( ( $custom_img_base_url . $image_size_arr['file'] ) ) ] = $results_2[ $i ]['post_id'];
				}

				unset( $meta_value );
			}
		}

		$results_2 = $tmp_arr;
		unset( $tmp_arr );

		return $results_2;
	}

	/**
	 * Error Hand Images
	 *
	 * Unused/Conceptual function potentially used in `Gofer_SEO_Methods::attachment_url_to_post_id_query_2()`.
	 * This is to handle errors where a normal try/catch wouldn't have the exception needed to catch.
	 *
	 * @since 1.0.0
	 *
	 * @see Gofer_SEO_Methods::attachment_url_to_post_id_query_2()
	 *
	 * @param $errno
	 * @param $errstr
	 * @param $errfile
	 * @param $errline
	 * @return bool
	 * @throws ErrorException
	 */
	public static function error_handle_images( $errno, $errstr, $errfile, $errline ) {
		// Possibly handle known issues differently.
		// Handles unserialize() warning notice.
		if ( 8 === $errno || strpos( $errstr , 'unserialize():' ) ) {
			throw new ErrorException( $errstr, $errno, 0, $errfile, $errline );
		} else {
			throw new ErrorException( $errstr, $errno, 0, $errfile, $errline );
		}

		return false;
	}

	/**
	 * Version Compare.
	 *
	 * Back up of an old function that may be useful.
	 *
	 * @since 1.0.0
	 *
	 * @param string       $current_version The current version being used.
	 * @param string|array $compare_version The version to compare with.
	 * @return bool
	 */
	public function version_match( $current_version, $compare_version ) {
		if ( is_array( $compare_version ) ) {
			foreach ( $compare_version as $compare_single ) {
				$recursive_result = $this->version_match( $current_version, $compare_single );
				if ( $recursive_result ) {
					return true;
				}
			}

			return false;
		}

		$current_parse = explode( '.', $current_version );

		if ( strpos( $compare_version, '-' ) ) {
			$compare_parse = explode( '-', $compare_version );
		} elseif ( strpos( $compare_version, '.' ) ) {
			$compare_parse = explode( '.', $compare_version );
		} else {
			return false;
		}

		$current_count = count( $current_parse );
		$compare_count = count( $compare_parse );
		for ( $i = 0; $i < $current_count || $i < $compare_count; $i++ ) {
			if ( isset( $compare_parse[ $i ] ) && 'x' === strtolower( $compare_parse[ $i ] ) ) {
				unset( $compare_parse[ $i ] );
			}

			if ( ! isset( $current_parse[ $i ] ) ) {
				unset( $compare_parse[ $i ] );
			} elseif ( ! isset( $compare_parse[ $i ] ) ) {
				unset( $current_parse[ $i ] );
			}
		}

		foreach ( $compare_parse as $index => $sub_number ) {
			if ( $current_parse[ $index ] !== $sub_number ) {
				return false;
			}
		}

		return true;
	}

}
