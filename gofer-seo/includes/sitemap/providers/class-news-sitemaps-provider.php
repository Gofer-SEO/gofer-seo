<?php
/**
 * Gofer SEO (News) Sitemap: Provider
 *
 * Class intended to shadow WP 5.5 sitemap concept, and use hooks to add additional inputs.
 * This would allow switching between WP's and Gofer SEO's concepts,
 * and potentially making any updates easier to handle.
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_News_Sitemaps_Provider.
 *
 * @since 1.0.0
 */
class Gofer_SEO_News_Sitemaps_Provider extends WP_Sitemaps_Provider {

	/**
	 * Provider name.
	 *
	 * This will also be used as the public-facing name in URLs.
	 *
	 * @since 1.0.0
	 *
	 * @var string $name
	 */
	protected $name = '';

	/**
	 * Object Type.
	 *
	 * Object type name (e.g. 'post', 'term', 'user').
	 *
	 * @since 1.0.0
	 *
	 * @var string $object_type
	 */
	protected $object_type = '';

	/**
	 * Gofer_SEO_News_Sitemaps_Provider constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
//		add_filter( 'wp_sitemaps_index_entry', array( $this, 'sitemaps_index_entry' ), 10, 4 );
		add_filter( 'gofer_seo_news_sitemaps_index_entry', array( $this, 'sitemaps_index_entry' ), 10, 4 );
	}

	/**
	 * Get Object Subtypes.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_object_subtypes() {
		return array();
	}

	/**
	 * Get Max Number of Pages.
	 *
	 * @since 1.0.0
	 *
	 * @param string $object_subtype
	 * @return int
	 */
	public function get_max_num_pages( $object_subtype = '' ) {
		return 1000;
	}

//	public function get_query_args( $subtypes = array() ) {}

	/**
	 * Lists sitemap pages exposed by this provider.
	 *
	 * The returned data is used to populate the sitemap entries of the index.
	 *
	 * @since 1.0.0
	 *
	 * @return array[] Array of sitemap entries.
	 */
	public function get_sitemap_entries() {
		return $this->get_sitemap_list();
	}

	/**
	 * Get Sitemap List.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_sitemap_list() {
		$sitemaps      = array();
		$sitemap_types = array();

		// vvv `WP_Sitemaps_Provider::get_sitemap_type_data()`.
		$object_subtypes = $this->get_object_subtypes();
		// If there are no object subtypes (user), include a single sitemap for the entire object type.
		if ( empty( $object_subtypes ) ) {
			$sitemap_types[] = array(
				'name'  => '',
				'pages' => $this->get_max_num_pages(),
			);
		}

		foreach ( $object_subtypes as $object_subtype_name => $data ) {
			$object_subtype_name = (string) $object_subtype_name;

			$sitemap_types[] = array(
				'name'  => $object_subtype_name,
				'pages' => $this->get_max_num_pages( $object_subtype_name ),
			);
		}
		// ^^^ `WP_Sitemaps_Provider::get_sitemap_type_data()`.

		foreach ( $sitemap_types as $type ) {
			for ( $page = 1; $page <= $type['pages']; $page++ ) {
				$sitemap_entry = array(
					'loc' => $this->get_sitemap_url( $type['name'], $page ),
				);

				/**
				 * Filters the sitemap entry for the sitemap index.
				 *
				 * @since 1.0.0
				 *
				 * @param array  $sitemap_entry  Sitemap entry for the post.
				 * @param string $object_type    Object empty name.
				 * @param string $object_subtype Object subtype name.
				 *                               Empty string if the object type does not support subtypes.
				 * @param int    $page           Page number of results.
				 */
				$sitemap_entry = apply_filters( 'gofer_seo_news_sitemaps_index_entry', $sitemap_entry, $this->object_type, $type['name'], $page );

				$sitemaps[] = $sitemap_entry;
			}
		}

		return $sitemaps;
	}

	public function get_url_list( $page_num, $post_type = '' ) {}

	/**
	 * Gets the URL of a sitemap entry.
	 *
	 * @since 1.0.0
	 *
	 * @global WP_Rewrite $wp_rewrite WordPress rewrite component.
	 *
	 * @param string $name The name of the sitemap.
	 * @param int    $page The page of the sitemap.
	 * @return string The composed URL for a sitemap entry.
	 */
	public function get_sitemap_url( $name, $page ) {
		global $wp_rewrite;

		// Accounts for cases where name is not included, ex: sitemaps-users-1.xml.
		$params = array_filter(
			array(
				'news-sitemap'         => $this->name,
				'news-sitemap-subtype' => $name,
				'paged'                => $page,
			)
		);

		$basename = sprintf(
			'/news-sitemap-%1$s.xml',
			implode( '-', $params )
		);

		if ( ! $wp_rewrite->using_permalinks() ) {
			$basename = '/?' . http_build_query( $params, null, '&' );
		}

		return home_url( $basename );
	}


	/* **__________****************************************************************************************************/
	/* _/ EXTENDED \__________________________________________________________________________________________________*/


	/**
	 * Sitemaps Index Entry.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $sitemap_entry  Sitemap entry for the post.
	 * @param string $object_type    Object empty name.
	 * @param string $object_subtype Object subtype name.
	 *                               Empty string if the object type does not support subtypes.
	 * @param int    $page           Page number of results.
	 * @return array
	 */
	public function sitemaps_index_entry( $sitemap_entry, $object_type, $object_subtype, $page ) {
		return $sitemap_entry;
	}

}
