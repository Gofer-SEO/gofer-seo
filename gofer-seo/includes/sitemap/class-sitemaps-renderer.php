<?php
/**
 * Gofer SEO Sitemaps: Renderer class
 *
 * Responsible for rendering Sitemaps data to XML in accordance with sitemap protocol.
 *
 * @package Gofer SEO
 * @subpackage Sitemaps
 */

/**
 * Class Gofer_SEO_Sitemaps_Renderer
 *
 * @since 1.0.0
 */
class Gofer_SEO_Sitemaps_Renderer extends WP_Sitemaps_Renderer {

	/**
	 * Gofer_SEO_Sitemaps_Renderer constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Gets the URL for the sitemap stylesheet.
	 *
	 * @since 1.0.0
	 *
	 * @global WP_Rewrite $wp_rewrite WordPress rewrite component.
	 *
	 * @return string The sitemap stylesheet URL.
	 */
	public function get_sitemap_stylesheet_url() {
		global $wp_rewrite;

		$sitemap_url = home_url( '/sitemap.xsl' );

		if ( ! $wp_rewrite->using_permalinks() ) {
			$sitemap_url = home_url( '/?sitemap-stylesheet=sitemap' );
		}

		/**
		 * Filters the URL for the sitemap stylesheet.
		 *
		 * If a false value is returned, no stylesheet will be used and
		 * the "raw" XML of the sitemap will be displayed.
		 *
		 * @since 1.0.0
		 *
		 * @param string $sitemap_url Full URL for the sitemaps XSL file.
		 */
		return apply_filters( 'gofer_seo_sitemaps_stylesheet_url', $sitemap_url );
	}

	/**
	 * Gets the URL for the sitemap index stylesheet.
	 *
	 * @since 1.0.0
	 *
	 * @global WP_Rewrite $wp_rewrite WordPress rewrite component.
	 *
	 * @return string The sitemap index stylesheet URL.
	 */
	public function get_sitemap_index_stylesheet_url() {
		global $wp_rewrite;

		$sitemap_url = home_url( '/sitemap-index.xsl' );

		if ( ! $wp_rewrite->using_permalinks() ) {
			$sitemap_url = home_url( '/?sitemap-stylesheet=index' );
		}

		/**
		 * Filters the URL for the sitemap index stylesheet.
		 *
		 * If a false value is returned, no stylesheet will be used and
		 * the "raw" XML of the sitemap index will be displayed.
		 *
		 * @since 1.0.0
		 *
		 * @param string $sitemap_url Full URL for the sitemaps index XSL file.
		 */
		return apply_filters( 'gofer_seo_sitemaps_stylesheet_index_url', $sitemap_url );
	}

	/**
	 * Renders a sitemap index.
	 *
	 * @since 1.0.0
	 *
	 * @param array $sitemaps Array of sitemap URLs.
	 */
	public function render_index( $sitemaps ) {
		// Always follow and noindex the sitemap.
		header( 'X-Robots-Tag: noindex, follow', true );

		parent::render_index( $sitemaps );
	}

	/**
	 * Gets XML for a sitemap index.
	 *
	 * @since 1.0.0
	 *
	 * @param array $sitemaps Array of sitemap URLs.
	 * @return string|false A well-formed XML string for a sitemap index. False on error.
	 */
	public function get_sitemap_index_xml( $sitemaps ) {
		return parent::get_sitemap_index_xml( $sitemaps );
	}

	/**
	 * Renders a sitemap.
	 *
	 * @since 1.0.0
	 *
	 * @param array $url_list Array of URLs for a sitemap.
	 */
	public function render_sitemap( $url_list ) {
		// Always follow and noindex the sitemap.
		header( 'X-Robots-Tag: noindex, follow', true );

		parent::render_sitemap( $url_list );
	}

	/**
	 * Gets XML for a sitemap.
	 *
	 * @since 1.0.0
	 *
	 * @param array $url_list Array of URLs for a sitemap.
	 * @return string|false A well-formed XML string for a sitemap index. False on error.
	 */
	public function get_sitemap_xml( $url_list ) {
		$urlset = new SimpleXMLElement(
			sprintf(
				'%1$s%2$s%3$s',
				'<?xml version="1.0" encoding="UTF-8" ?>',
				$this->stylesheet,
				'<urlset
					xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
					xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" />'
			)
		);

		foreach ( $url_list as $url_item ) {
			$url = $urlset->addChild( 'url' );

			// Add each element as a child node to the <url> entry.
			foreach ( $url_item as $name => $value ) {
				if ( 'loc' === $name ) {
					$url->addChild( $name, esc_url( $value ) );
				} elseif ( in_array( $name, array( 'lastmod', 'changefreq', 'priority' ), true ) ) {
					$url->addChild( $name, esc_xml( $value ) );
				} elseif ( 'image:image' === $name ) {
					if ( ! empty( $value ) ) {
						$image = $url->addChild( 'image:image', null, 'http://www.google.com/schemas/sitemap-image/1.1' );
						foreach ( $value as $v2_value ) {
							foreach ( $v2_value as $k3_key => $v3_value ) {
								$image->addChild( $k3_key, $v3_value, 'http://www.google.com/schemas/sitemap-image/1.1' );
							}
						}
					}
				} else {
					_doing_it_wrong(
						__METHOD__,
						sprintf(
							/* translators: %s: List of element names. */
							esc_html__( 'Fields other than %s are not currently supported for sitemaps.', 'gofer-seo' ),
							implode( ',', array( 'loc', 'lastmod', 'changefreq', 'priority', 'image:image' ) )
						),
						'1.0.0'
					);
				}
			}
		}

		return $urlset->asXML();
	}

}
