<?php
/**
 * Schema Graph CollectionPage Class
 *
 * Acts as the collection page class for Schema CollectionPage.
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Graph_CollectionPage.
 *
 * @since 1.0.0
 *
 * @see Schema CollectionPage
 * @link https://schema.org/CollectionPage
 */
class Gofer_SEO_Graph_CollectionPage extends Gofer_SEO_Graph_WebPage {

	/**
	 * Get Graph Slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_slug() {
		return 'CollectionPage';
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
		return 'Collection Page';
	}

	/**
	 * Prepare data.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function prepare() {
		return parent::prepare();
	}

}
