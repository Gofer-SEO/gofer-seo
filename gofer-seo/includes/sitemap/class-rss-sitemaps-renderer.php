<?php
/**
 * Gofer SEO (RSS) Sitemaps: Renderer class
 *
 * Responsible for rendering Sitemaps data to XML in accordance with sitemap protocol.
 *
 * @package Gofer SEO
 * @subpackage Sitemaps
 */

/**
 * Class Gofer_SEO_RSS_Sitemaps_Renderer
 *
 * @since 1.0.0
 */
class Gofer_SEO_RSS_Sitemaps_Renderer extends WP_Sitemaps_Renderer {

	/**
	 * Gofer_SEO_RSS_Sitemaps_Renderer constructor.
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

		$sitemap_url = home_url( '/rss-sitemap.xsl' );

		if ( ! $wp_rewrite->using_permalinks() ) {
			$sitemap_url = home_url( '/?rss-sitemap-stylesheet=sitemap' );
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
		return apply_filters( 'gofer_seo_rss_sitemaps_stylesheet_url', $sitemap_url );
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

		$sitemap_url = home_url( '/rss-sitemap-index.xsl' );

		if ( ! $wp_rewrite->using_permalinks() ) {
			$sitemap_url = home_url( '/?rss-sitemap-stylesheet=index' );
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
		return apply_filters( 'gofer_seo_rss_sitemaps_stylesheet_index_url', $sitemap_url );
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
		$xml_rss = new SimpleXMLElement(
			sprintf(
				'%1$s%2$s%3$s',
				'<?xml version="1.0" encoding="UTF-8" ?>',
				$this->stylesheet,
				'<rss version="2.0" />'
			)
		);

		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		$site_context      = Gofer_SEO_Context::get_instance( array( 'context_type' => 'WP_Site' ) );

		$site_link = $site_context->get_url();
		$site_lang = get_bloginfo( 'language' );
		// Site Title.
		if (
				$gofer_seo_options->options['modules']['general']['enable_site_title'] &&
				! empty( $gofer_seo_options->options['modules']['general']['site_title'] )
		) {
			$site_title = $gofer_seo_options->options['modules']['general']['site_title'];
		} else {
			$site_title = $site_context->get_display_name();
			if ( empty( $site_title ) ) {
				$site_title = __( 'RSS Sitemap', 'gofer-seo' );
			}
		}
		// Site Description.
		if (
				$gofer_seo_options->options['modules']['general']['enable_site_description'] &&
				! empty( $gofer_seo_options->options['modules']['general']['site_description'] )
		) {
			$site_description = $gofer_seo_options->options['modules']['general']['site_description'];
		} else {
			$site_description = $site_context->get_description();
			if ( empty( $site_description ) ) {
				$site_description = __( 'RSS generated Sitemap.', 'gofer-seo' );
			}
		}

//		// TODO Site Image.
//		if ( ! empty( $gofer_seo_options->options['modules']['general']['site_image'] ) ) {
//			$site_image = $gofer_seo_options->options['modules']['general']['site_image'];
//			$site_image_data = array();
//			if ( is_numeric( $site_image ) ) {
//				$image_data = image_get_intermediate_size( $site_image );
//				if ( ! $image_data ) {
//					$image_data = image_get_intermediate_size( $site_image, 'thumbnail' );
//				}
//
//				if ( $image_data ) {
//					$site_image_data['url']         = $image_data['url'];
//					$site_image_data['link']        = $image_data['url'];
//					$site_image_data['title']       = get_the_title( $site_image );
//					$site_image_data['width']       = $image_data['width'];
//					$site_image_data['height']      = $image_data['height'];
//					$site_image_data['description'] = wp_get_attachment_caption( $site_image );
//				}
//			} else {
//				$site_image_data['url'] = $site_image;
//			}
//		}

		$xml_channel = $xml_rss->addChild( 'channel' );
		$xml_channel->addChild( 'title', $site_title );
		$xml_channel->addChild( 'description', $site_description );
		$xml_channel->addChild( 'link', $site_link );
		$xml_channel->addChild( 'language', $site_lang );

//		if ( ! empty( $site_image_data ) ) {
//			$xml_image = $xml_channel->addChild( 'image' );
//			foreach ( $site_image_data as $k1_name => $v1_value ) {
//				if ( in_array( $k1_name, array( 'url', 'link' ), true ) ) {
//					if ( ! empty( $v1_value ) ) {
//						$xml_image->addChild( $k1_name, esc_url( $v1_value ) );
//					}
//				} elseif ( in_array( $k1_name, array( 'title', 'description', 'width', 'height' ), true ) ) {
//					if ( ! empty( $v1_value ) ) {
//						$xml_image->addChild( $k1_name, esc_xml( $v1_value ) );
//					}
//				}
//			}
//		}

		foreach ( $url_list as $url_item ) {
			$xml_item = $xml_channel->addChild( 'item' );

			// Add each element as a child node to the <item> entry.
			foreach ( $url_item as $name => $value ) {
				if ( in_array( $name, array( 'link', 'guid' ), true ) ) {
					$xml_item->addChild( $name, esc_url( $value ) );
				} elseif ( in_array( $name, array( 'title', 'pubDate', 'description' ), true ) ) {
					if ( ! empty( $value ) ) {
						$xml_item->addChild( $name, esc_xml( $value ) );
					}
				} else {
					_doing_it_wrong(
						__METHOD__,
						sprintf(
							/* translators: %s: List of element names. */
							esc_html__( 'Fields other than %s are not currently supported for sitemaps.', 'gofer-seo' ),
							implode( ',', array( 'link', 'guid', 'title', 'pubDate', 'description' ) )
						),
						'1.0.0'
					);
				}
			}
		}

		return $xml_rss->asXML();
	}

}
