<?php
/**
 * Gofer SEO Sitemaps: Provider - Users
 *
 * Class intended to shadow WP 5.5 sitemap concept, and use hooks to add additional inputs.
 * This would allow switching between WP's and Gofer SEO's concepts,
 * and potentially making any updates easier to handle.
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Sitemaps_Provider_Users.
 *
 * @since 1.0.0
 */
class Gofer_SEO_Sitemaps_Provider_Users extends Gofer_SEO_Sitemaps_Provider {

	/**
	 * Provider name.
	 *
	 * This will also be used as the public-facing name in URLs.
	 *
	 * @since 1.0.0
	 *
	 * @var string $name
	 */
	protected $name = 'users';

	/**
	 * Object Type.
	 *
	 * Object type name (e.g. 'post', 'term', 'user').
	 *
	 * @since 1.0.0
	 *
	 * @var string $object_type
	 */
	protected $object_type = 'user';

	/**
	 * Gofer_SEO_Sitemaps_Provider_Users constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'wp_sitemaps_users_query_args', array( $this, 'sitemaps_users_query_args' ), 10, 2 );
		add_filter( 'gofer_seo_sitemaps_users_query_args', array( $this, 'sitemaps_users_query_args' ), 10, 2 );

		add_filter( 'wp_sitemaps_users_entry', array( $this, 'sitemaps_users_entry' ), 10, 3 );
		add_filter( 'gofer_seo_sitemaps_users_entry', array( $this, 'sitemaps_users_entry' ), 10, 3 );
	}

	/**
	 * Gets the max number of pages available for the object type.
	 *
	 * @since 1.0.0
	 *
	 * @see WP_Sitemaps_Provider::max_num_pages
	 *
	 * @param string $object_subtype Optional. Not applicable for Users but
	 *                               required for compatibility with the parent
	 *                               provider class. Default empty.
	 * @return int Total page count.
	 */
	public function get_max_num_pages( $object_subtype = '' ) {
		$args  = $this->get_query_args();
		$query = new WP_User_Query( $args );

		$total_users = $query->get_total();

		return (int) ceil( $total_users / parent::get_max_num_pages( $object_subtype ) );
	}

	/**
	 * Returns the query args for retrieving users to list in the sitemap.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of WP_User_Query arguments.
	 */
	protected function get_query_args() {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		$public_post_types = get_post_types(
			array(
				'public' => true,
			)
		);

		// We're not supporting sitemaps for author pages for attachments.
		unset( $public_post_types['attachment'] );

		$args = array(
			'has_published_posts' => array_keys( $public_post_types ),
			'number'              => $gofer_seo_options->options['modules']['sitemap']['posts_per_sitemap'],
		);

		/**
		 * Filters the query arguments for authors with public posts.
		 *
		 * Allows modification of the authors query arguments before querying.
		 *
		 * @see WP_User_Query::prepare_query() for a full list of arguments
		 *
		 * @since 1.0.0
		 *
		 * @param array $args Array of WP_User_Query arguments.
		 */
		$args = apply_filters( 'gofer_seo_sitemaps_users_query_args', $args );

		return $args;
	}

	/**
	 * Gets a URL list for a user sitemap.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $page_num       Page of results.
	 * @param string $object_subtype Optional. Not applicable for Users but
	 *                               required for compatibility with the parent
	 *                               provider class. Default empty.
	 * @return array Array of URLs for a sitemap.
	 */
	public function get_url_list( $page_num, $object_subtype = '' ) {
		$args          = $this->get_query_args();
		$args['paged'] = $page_num;

		$query    = new WP_User_Query( $args );
		$users    = $query->get_results();
		$url_list = array();

		foreach ( $users as $user ) {
			$sitemap_entry = array(
				'loc' => get_author_posts_url( $user->ID ),
			);

			/**
			 * Filters the sitemap entry for an individual user.
			 *
			 * @since 1.0.0
			 *
			 * @param array   $sitemap_entry Sitemap entry for the user.
			 * @param WP_User $user          User object.
			 */
			$sitemap_entry = apply_filters( 'gofer_seo_sitemaps_users_entry', $sitemap_entry, $user );
			$url_list[]    = $sitemap_entry;
		}

		return $url_list;
	}


	/* **________________**********************************************************************************************/
	/* _/ EXTENDED/HOOKS \____________________________________________________________________________________________*/


	/**
	 * Sitemap Users Query Args.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Array of WP_User_Query arguments.
	 * @return array
	 */
	public function sitemaps_users_query_args( $args ) {
		return $args;
	}

	/**
	 * Sitemap Users Entry.
	 *
	 * @since 1.0.0
	 *
	 * @param array   $sitemap_entry Sitemap entry for the user.
	 * @param WP_User $user          User object.
	 * @return array
	 */
	public function sitemaps_users_entry( $sitemap_entry, $user ) {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		$gofer_seo_user    = new Gofer_SEO_User( $user );

		$args = array(
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'orderby '       => 'modified',
			'order'          => 'DESC',
			'author'         => $user->ID,
		);
		$query_last_modified = new WP_Query( $args );

		$timestamp = false;
		if ( $query_last_modified->have_posts() ) {
			// Last Modified.
			if ( '0000-00-00 00:00:00' !== $query_last_modified->post->post_modified_gmt ) {
				$timestamp = $query_last_modified->post->post_modified_gmt;
			} elseif ( '0000-00-00 00:00:00' !== $query_last_modified->post->post_date_gmt ) {
				$timestamp = $query_last_modified->post->post_date_gmt;
			}

			if ( $timestamp ) {
				$sitemap_entry['lastmod'] = wp_date( 'Y-m-d\TH:i:s\Z', mysql2date( 'U', $timestamp ) );
			}
		}

		// Priority.
		if ( -1 !== $gofer_seo_user->meta['modules']['sitemap']['priority'] ) {
			$sitemap_entry['priority'] = $gofer_seo_user->meta['modules']['sitemap']['priority'];
		} elseif ( -1 !== $gofer_seo_options->options['modules']['sitemap']['archive_author_settings']['priority'] ) {
			$sitemap_entry['priority'] = $gofer_seo_options->options['modules']['sitemap']['archive_author_settings']['priority'];
		} elseif ( -1 !== $gofer_seo_options->options['modules']['sitemap']['site_priority'] ) {
			$sitemap_entry['priority'] = $gofer_seo_options->options['modules']['sitemap']['site_priority'];
		}

		if ( 1 < $sitemap_entry['priority'] ) {
			$sitemap_entry['priority'] /= 10;
		}

		// Frequency.
		if ( 'default' !== $gofer_seo_user->meta['modules']['sitemap']['frequency'] ) {
			$sitemap_entry['changefreq'] = $gofer_seo_user->meta['modules']['sitemap']['frequency'];
		} elseif ( 'default' !== $gofer_seo_options->options['modules']['sitemap']['archive_author_settings']['frequency'] ) {
			$sitemap_entry['changefreq'] = $gofer_seo_options->options['modules']['sitemap']['archive_author_settings']['frequency'];
		} elseif ( 'never' !== $gofer_seo_options->options['modules']['sitemap']['site_frequency'] ) {
			$sitemap_entry['changefreq'] = $gofer_seo_options->options['modules']['sitemap']['site_frequency'];
		}

		return $sitemap_entry;
	}

}
