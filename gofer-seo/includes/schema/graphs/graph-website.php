<?php
/**
 * Schema Graph WebSite Class
 *
 * Acts as the website class for Schema WebSite.
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Graph_WebSite.
 *
 * @since 1.0.0
 *
 * @see  Gofer_SEO_Graph_CreativeWork
 * @see Schema WebSite
 * @link https://schema.org/WebSite
 */
class Gofer_SEO_Graph_WebSite extends Gofer_SEO_Graph_CreativeWork {

	/**
	 * Get Graph Slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_slug() {
		return 'WebSite';
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
		return 'Website';
	}

	/**
	 * Prepare
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function prepare() {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		$rtn_data = array(
			'@type'     => $this->slug,
			'@id'       => home_url() . '/#' . strtolower( $this->slug ),
			'url'       => home_url() . '/',
			'name'      => get_bloginfo( 'name' ),
			'publisher' => array(
				'@id' => home_url() . '/#' . $gofer_seo_options->options['modules']['schema_graph']['site_represents'],
			),
		);

		if ( $gofer_seo_options->options['modules']['schema_graph']['show_search_results_page'] ) {
			$rtn_data['potentialAction'] = array(
				'@type'       => 'SearchAction',
				'target'      => home_url() . '/?s={search_term_string}',
				'query-input' => 'required name=search_term_string',
			);
		}

		return $rtn_data;
	}

}
