<?php
/**
 * Gofer SEO (News) Sitemaps: Renderer class
 *
 * Responsible for rendering Sitemaps data to XML in accordance with sitemap protocol.
 *
 * @package Gofer SEO
 * @subpackage Sitemaps
 */

/**
 * Class Gofer_SEO_News_Sitemaps_Renderer
 *
 * @since 1.0.0
 */
class Gofer_SEO_News_Sitemaps_Renderer extends WP_Sitemaps_Renderer {

	/**
	 * Gofer_SEO_News_Sitemaps_Renderer constructor.
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

		$sitemap_url = home_url( '/news-sitemap.xsl' );

		if ( ! $wp_rewrite->using_permalinks() ) {
			$sitemap_url = home_url( '/?news-sitemap-stylesheet=sitemap' );
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
		return apply_filters( 'gofer_seo_news_sitemaps_stylesheet_url', $sitemap_url );
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

		$sitemap_url = home_url( '/news-sitemap-index.xsl' );

		if ( ! $wp_rewrite->using_permalinks() ) {
			$sitemap_url = home_url( '/?news-sitemap-stylesheet=index' );
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
		return apply_filters( 'gofer_seo_news_sitemaps_stylesheet_index_url', $sitemap_url );
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
		$xml_sitemapindex = new SimpleXMLElement(
			sprintf(
				'%1$s%2$s%3$s',
				'<?xml version="1.0" encoding="UTF-8" ?>',
				$this->stylesheet_index,
				'<sitemapindex
					xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
					xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" />'
			)
		);

		foreach ( $sitemaps as $entry ) {
			$xml_sitemap = $xml_sitemapindex->addChild( 'sitemap' );

			// Add each element as a child node to the <sitemap> entry.
			foreach ( $entry as $name => $value ) {
				if ( 'loc' === $name ) {
					$xml_sitemap->addChild( $name, esc_url( $value ) );
				} elseif ( 'lastmod' === $name ) {
					$xml_sitemap->addChild( $name, esc_xml( $value ) );
				} else {
					_doing_it_wrong(
						__METHOD__,
						sprintf(
							/* translators: %s: List of element names. */
							esc_html__( 'Fields other than %s are not currently supported for the sitemap index.', 'gofer-seo' ),
							implode( ',', array( 'loc', 'lastmod' ) )
						),
						'1.0.0'
					);
				}
			}
		}

		return $xml_sitemapindex->asXML();
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
		$xml_urlset = new SimpleXMLElement(
			sprintf(
				'%1$s%2$s%3$s',
				'<?xml version="1.0" encoding="UTF-8" ?>',
				$this->stylesheet,
				'<urlset
 					xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
 					xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" />'

			)
		);

		foreach ( $url_list as $url_item ) {
			$xml_url = $xml_urlset->addChild( 'url' );
			// Add each element as a child node to the <url> entry.
			foreach ( $url_item as $name => $value ) {
				if ( 'loc' === $name ) {
					$xml_url->addChild( $name, esc_url( $value ) );
				} elseif ( 'news:news' === $name ) {
					if ( ! empty( $value ) ) {
						$xml_news = $xml_url->addChild( $name, null, 'http://www.google.com/schemas/sitemap-news/0.9' );
						foreach ( $value as $k2_key => $v2_value ) {
							if ( in_array( $k2_key, array( 'news:publication_date', 'news:title' ), true ) ) {
								$xml_news->addChild( $k2_key, esc_xml( $v2_value ) );
							} elseif ( 'news:publication' === $k2_key ) {
								$xml_news_publication = $xml_news->addChild( $k2_key );
								foreach ( $v2_value as $k3_key => $v3_value ) {
									if ( in_array( $k3_key, array( 'news:name', 'news:language' ), true ) ) {
										$xml_news_publication->addChild( $k3_key, $v3_value );
									}
								}
							}
						}
					}
				} else {
					_doing_it_wrong(
						__METHOD__,
						sprintf(
							/* translators: %s: List of element names. */
							esc_html__( 'Fields other than %s are not currently supported for news sitemaps.', 'gofer-seo' ),
							implode( ', ', array( 'news:publication', 'news:publication/news:name', 'news:publication/news:language', 'news:publication_date', 'news:title', '' ) )
						),
						'1.0.0'
					);
				}
			}
		}

		return $xml_urlset->asXML();
	}

}
