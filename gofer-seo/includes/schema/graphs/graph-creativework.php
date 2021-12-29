<?php
/**
 * Schema Graph CollectionPage Class
 *
 * Acts as the collection page class for Schema CollectionPage.
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Graph_CreativeWork.
 *
 * @since 1.0.0
 *
 * @see Schema CreativeWork
 * @link https://schema.org/CreativeWork
 */
abstract class Gofer_SEO_Graph_CreativeWork extends Gofer_SEO_Graph {

	/**
	 * Get Graph Slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_slug() {
		return 'CreativeWork';
	}

}
