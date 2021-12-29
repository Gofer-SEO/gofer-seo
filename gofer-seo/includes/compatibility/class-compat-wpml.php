<?php
/**
 * Gofer SEO Compatibility: WordPress Multilingual (WPML)
 *
 * @package Gofer SEO
 */

/**
 * Compatibility with WPML - WordPress Multilingual Plugin
 *
 * @since 1.0.0
 *
 * @link https://wpml.org/
 */
class Gofer_SEO_Compat_WPML {

	/**
	 * Gofer_SEO_WPML constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( function_exists( 'icl_object_id' ) ) {
			add_filter( 'gofer_seo_home_url', array( $this, 'filter_home_url' ) );
		}
	}

	/**
	 * Filter Home URL.
	 *
	 * Returns specified url filtered by wpml.
	 * This is needed to obtain the correct domain in which WordPress is running on.
	 *
	 * @since 1.0.0
	 *
	 * @param string $path Relative path or url.
	 * @param string filtered url.
	 * @return string
	 */
	public function filter_home_url( $path ) {
		// Used for backwards-compatibility.
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$url = apply_filters( 'wpml_home_url', home_url( '/' ) );

		// Remove query string.
		preg_match_all( '/\?[\s\S]+/', $url, $matches );

		// Get base.
		$url  = preg_replace( '/\?[\s\S]+/', '', $url );
		$url  = trailingslashit( $url );
		$url .= preg_replace( '/\//', '', $path, 1 );

		// Add query string.
		if ( count( $matches ) > 0 && count( $matches[0] ) > 0 ) {
			$url .= $matches[0][0];
		}

		return $url;
	}

}
