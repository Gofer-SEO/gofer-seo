<?php
/**
 * Gofer SEO Compatibility
 *
 * Initiates compatibility code with other plugins/themes.
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Compat.
 *
 * @since 1.0.0
 */
class Gofer_SEO_Compat {

	/**
	 * List of compatibility classes to execute and run.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $classes = array();

	/**
	 * Gofer_SEO_Compatibility constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->load();
	}

	/**
	 * Handles require_once files.
	 *
	 * @ignore
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function _requires() {
		global $wp_version;

		require_once GOFER_SEO_DIR . 'includes/compatibility/php/php-functions.php';
		require_once GOFER_SEO_DIR . 'includes/compatibility/php/static-class-php-functions.php';
		require_once GOFER_SEO_DIR . 'includes/compatibility/wp/wp-functions.php';
		require_once GOFER_SEO_DIR . 'includes/compatibility/wp/static-class-wp-functions.php';
		require_once GOFER_SEO_DIR . 'includes/compatibility/woocommerce/functions.php';

		require_once GOFER_SEO_DIR . 'includes/compatibility/class-compat-jetpack.php';
		require_once GOFER_SEO_DIR . 'includes/compatibility/class-compat-wpml.php';

		if ( version_compare( '5.5.0', $wp_version, '>' ) ) {
			// Compatibility - WP Sitemaps.
			require_once GOFER_SEO_DIR . 'includes/compatibility/wp/sitemaps/providers/class-wp-sitemaps-provider.php';
			require_once GOFER_SEO_DIR . 'includes/compatibility/wp/sitemaps/class-wp-sitemaps-renderer.php';
			require_once GOFER_SEO_DIR . 'includes/compatibility/wp/sitemaps/class-wp-sitemaps-stylesheet.php';
		}
	}

	/**
	 * Load.
	 *
	 * @since 1.0.0
	 */
	public function load() {
		$this->_requires();

		new Gofer_SEO_Compat_Jetpack();
		new Gofer_SEO_Compat_WPML();

		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		if ( $gofer_seo_options->options['enable_modules']['social_media'] ) {
			add_filter( 'twitter_card', array( $this, 'disable_twitter' ) );
		}

		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$http_user_agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
			if ( false !== stripos( $http_user_agent, 'Chrome/77.' ) ) {
				add_action( 'admin_head', array( $this, 'fix_chrome_overlapping_metabox' ) );
			}
		}
	}

	/**
	 * Disable Twitter.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	function disable_twitter( $card_properties ) {
		if ( apply_filters( 'gofer_seo_disable_twitter_plugin_card', true ) ) {
			return false;
		}

		return $card_properties;
	}

	/**
	 * Chrome Fix
	 *
	 * Fixes a CSS compatibility issue between Gutenberg and Chrome v77 that affects meta boxes.
	 * Change height of a specific Gutenberg CSS class.
	 *
	 * @see https://github.com/WordPress/gutenberg/issues/17406
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function fix_chrome_overlapping_metabox() {
		global $wp_version;

		if ( version_compare( $wp_version, '5.0', '<' ) ) {
			return;
		}

		// CSS class renamed from 'editor' to 'block-editor' in WP v5.2.
		if ( version_compare( $wp_version, '5.2', '<' ) ) {
			echo '<style>.editor-writing-flow { height: auto; }</style>';
		} else {
			echo '<style>.block-editor-writing-flow { height: auto; }</style>';
		}
	}

}
