<?php
/**
 * Schema Graph ProfilePage Class
 *
 * Acts as the profile page class for Schema ProfilePage.
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Graph_ProfilePage.
 * 
 * @since 1.0.0
 *
 * @see  Gofer_SEO_Graph_CreativeWork
 * @see Gofer_SEO_Graph_WebPage
 * @see Schema ProfilePage
 * @link https://schema.org/ProfilePage
 */
class Gofer_SEO_Graph_ProfilePage extends Gofer_SEO_Graph_WebPage {

	/**
	 * Get Graph Slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_slug() {
		return 'ProfilePage';
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
		return 'Profile Page';
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
