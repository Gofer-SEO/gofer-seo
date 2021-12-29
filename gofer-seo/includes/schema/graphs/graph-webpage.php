<?php
/**
 * Schema Graph WebPage Class
 *
 * Acts as the web page class for Schema WebPage.
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Graph_WebPage.
 *
 * @since 1.0.0
 *
 * @see  Gofer_SEO_Graph_CreativeWork
 * @see Schema WebPage
 * @link https://schema.org/WebPage
 */
class Gofer_SEO_Graph_WebPage extends Gofer_SEO_Graph_CreativeWork {

	/**
	 * Get Graph Slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_slug() {
		return 'WebPage';
	}

	/**
	 * Get Graph Name.
	 *
	 * Intended for frontend use when displaying which schema graphs are available.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_name() {
		return 'Web Page';
	}

	/**
	 * Prepare data.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function prepare() {
		global $post;
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		if (
				'post_type_archive' === Gofer_SEO_Context::get_is() &&
				function_exists( 'is_shop' ) &&
				function_exists( 'wc_get_page_id' ) &&
				is_shop()
		) {
			// WooCommerce - Shop Page.
			$shop_page = get_post( wc_get_page_id( 'shop' ) );
			$context   = Gofer_SEO_Context::get_instance( $shop_page );
		} elseif (
				class_exists( 'BuddyPress' ) &&
				'single_page' === Gofer_SEO_Context::get_is() &&
				bp_is_user()
		) {
			// BuddyPress - Member Page.
			$wp_user = wp_get_current_user();
			$context = Gofer_SEO_Context::get_instance( $wp_user );
		} elseif (
				class_exists( 'BuddyPress' ) &&
				'single_page' === Gofer_SEO_Context::get_is() &&
				(
					bp_is_group() ||
					bp_is_group_create()
				)
		) {
			// BuddyPress - Group Page(s).
			$bp_pages = get_option( 'bp-pages' );
			$context = array(
				'context_type' => 'WP_Post',
				'context_key'  => $bp_pages['groups'],
			);
			$context = Gofer_SEO_Context::get_instance( $context );
		} else {
			$context = Gofer_SEO_Context::get_instance();
		}

		$current_url  = $context->get_canonical_url();
		$current_name = $context->get_display_name();
		$current_desc = $context->get_description();

		$rtn_data = array(
			'@type'      => $this->slug,
			'@id'        => $current_url . '#' . strtolower( $this->slug ), // TODO Should this be `#webpage`?
			'url'        => $current_url,
			'inLanguage' => get_bloginfo( 'language' ),
			'name'       => $current_name,
			'isPartOf'   => array(
				'@id' => home_url() . '/#website',
			),
			'breadcrumb' => array(
				'@id' => $context->get_canonical_url() . '#breadcrumblist',
			),
		);
		if ( ! empty( $current_desc ) ) {
			$rtn_data['description'] = $current_desc;
		}

		// Handles pages.
		if ( is_singular() || is_single() ) {
			if ( is_attachment() ) {
				unset( $rtn_data['breadcrumb'] );
			}

			if ( has_post_thumbnail( $post ) ) {
				$image_id = get_post_thumbnail_id();

				$image_schema = $this->prepare_image( $this->get_site_image_data( $image_id ), $current_url . '#primaryimage' );
				if ( $image_schema ) {
					$rtn_data['image']              = $image_schema;
					$rtn_data['primaryImageOfPage'] = array( '@id' => $current_url . '#primaryimage' );
				}
			}

			$rtn_data['datePublished'] = mysql2date( DATE_W3C, $post->post_date_gmt, false );
			$rtn_data['dateModified']  = mysql2date( DATE_W3C, $post->post_modified_gmt, false );
		}

		if ( is_front_page() ) {
			$rtn_data['about'] = array(
				'@id' => home_url() . '/#' . $gofer_seo_options->options['modules']['schema_graph']['site_represents'],
			);
		}

		return $rtn_data;
	}

	/**
	 * Get Post Description.
	 *
	 * @deprecated Use Gofer_SEO_Context::get_instance( $post_object )->get_description().
	 * @since 1.0.0
	 *
	 * @param WP_Post $post See WP_Post for details.
	 * @return string
	 */
	protected function get_post_description( $post ) {
		$rtn_description = '';
		$gofer_seo_post = new Gofer_SEO_Post( $post );

		// Using Post's meta description is limited in content. With Schema's descriptions, there is no cap limit.
		$post_description = $gofer_seo_post->meta['modules']['general']['description'];

		// If there is no meta description, and the post isn't password protected, then use post excerpt or content.
		if ( ! $post_description && ! post_password_required( $post ) ) {
			if ( ! empty( $post->post_excerpt ) ) {
				$post_description = $post->post_excerpt;
			}
		}

		if ( ! empty( $post_description ) && is_string( $post_description ) ) {
			$rtn_description = $post_description;
		}

		return $rtn_description;
	}

}
