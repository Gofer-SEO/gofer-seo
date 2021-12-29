<?php
/**
 * Schema Graph SearchResultsPage Class
 *
 * Acts as the search results page class for Schema SearchResultsPage.
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Graph_SearchResultsPage.
 *
 * @since 1.0.0
 *
 * @see  Gofer_SEO_Graph_CreativeWork
 * @see Gofer_SEO_Graph_WebPage
 * @see Schema SearchResultsPage
 * @link https://schema.org/SearchResultsPage
 */
class Gofer_SEO_Graph_SearchResultsPage extends Gofer_SEO_Graph_WebPage {

	/**
	 * Get Graph Slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_slug() {
		return 'SearchResultsPage';
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
		return 'Search Results Page';
	}

	/**
	 * Prepare
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function prepare() {
		return parent::prepare();
	}

}
