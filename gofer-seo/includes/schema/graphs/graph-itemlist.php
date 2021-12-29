<?php
/**
 * Schema Graph ItemList Class
 *
 * Acts as the Item List class for Schema ItemList.
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Graph_ItemList.
 *
 * @since 1.0.0
 *
 * @see Schema ItemList
 * @link https://schema.org/ItemList
 */
class Gofer_SEO_Graph_ItemList extends Gofer_SEO_Graph {

	/**
	 * Get Graph Slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_slug() {
		return 'ItemList';
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
		return 'Item List';
	}

	/**
	 * Prepare data.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function prepare() {
		//return parent::prepare();
		return array();
	}

}
