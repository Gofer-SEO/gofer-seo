<?php
/**
 * Gofer SEO Compatibility: Jetpack
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Compat_Jetpack.
 *
 * @since 1.0.0
 */
class Gofer_SEO_Compat_Jetpack {

	/**
	 * Gofer_SEO_Compat_Jetpack constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( class_exists( 'jetpack' ) ) {
			add_filter( 'jetpack_get_available_modules', array( $this, 'remove_jetpack_sitemap' ) );
			add_filter( 'jetpack_site_verification_output', array( $this, 'filter_jetpack_site_verification_output' ), 10, 1 );
		}
	}

	/**
	 * Remove Jetpack's sitemap.
	 *
	 * @since 1.0.0
	 *
	 * @param array $modules All the Jetpack modules.
	 * @return array
	 */
	public function remove_jetpack_sitemap( $modules ) {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		if ( $gofer_seo_options->options['enable_modules']['sitemap'] ) {
			// Remove Jetpack's sitemap.
			unset( $modules['sitemaps'] );
		}

		return $modules;
	}

	/**
	 * Filter Jetpack's site verification.
	 *
	 * If we have a value for a particular verification, use ours.
	 *
	 * @since 1.0.0
	 *
	 * @param $ver_tag
	 * @return string
	 */
	function filter_jetpack_site_verification_output( $ver_tag ) {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		if ( ! empty( $gofer_seo_options->options['modules']['general']['verify_pinterest'] ) && strpos( $ver_tag, 'p:domain_verify' ) ) {
			return '';
		}
		if ( ! empty( $gofer_seo_options->options['modules']['general']['verify_google'] ) && strpos( $ver_tag, 'google-site-verification' ) ) {
			return '';
		}
		if ( ! empty( $gofer_seo_options->options['modules']['general']['verify_bing'] ) && strpos( $ver_tag, 'msvalidate.01' ) ) {
			return '';
		}
		if ( ! empty( $gofer_seo_options->options['modules']['general']['verify_yandex'] ) && strpos( $ver_tag, 'yandex-verification' ) ) {
			return '';
		}
		if ( ! empty( $gofer_seo_options->options['modules']['general']['verify_baidu'] ) && strpos( $ver_tag, 'baidu-site-verification' ) ) {
			return '';
		}

		return $ver_tag;
	}

}
