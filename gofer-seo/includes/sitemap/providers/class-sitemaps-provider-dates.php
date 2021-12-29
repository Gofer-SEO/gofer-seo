<?php
/**
 * Gofer SEO Sitemaps: Provider - Dates
 *
 * Class intended to shadow WP 5.5 sitemap concept, and use hooks to add additional inputs.
 * This would allow switching between WP's and Gofer SEO's concepts,
 * and potentially making any updates easier to handle.
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Sitemaps_Provider_Dates.
 *
 * @since 1.0.0
 */
class Gofer_SEO_Sitemaps_Provider_Dates extends Gofer_SEO_Sitemaps_Provider {

	/**
	 * Provider name.
	 *
	 * This will also be used as the public-facing name in URLs.
	 *
	 * @since 1.0.0
	 *
	 * @var string $name
	 */
	protected $name = 'dates';

	/**
	 * Gofer SEO Sitemaps: Renderer class
	 *
	 * Responsible for rendering Sitemaps data to XML in accordance with sitemap protocol.
	 *
	 * @package Gofer SEO
	 * @subpackage Sitemaps
	 */
	protected $object_type = 'date';

	/**
	 * Gofer_SEO_Sitemaps_Provider_Dates constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'wp_sitemap_post_types', array( $this, 'sitemap_post_types' ) );
		add_filter( 'gofer_seo_sitemap_dates_post_types', array( $this, 'sitemap_dates_post_types' ) );

		add_filter( 'wp_sitemaps_dates_query_args', array( $this, 'sitemaps_dates_query_args' ), 10, 2 );
		add_filter( 'gofer_seo_sitemaps_dates_query_args', array( $this, 'sitemaps_dates_query_args' ), 10, 2 );

		add_filter( 'wp_sitemaps_dates_entry', array( $this, 'sitemaps_dates_entry' ), 10, 3 );
		add_filter( 'gofer_seo_sitemaps_dates_entry', array( $this, 'sitemaps_dates_entry' ), 10, 3 );
	}

	/**
	 * @return WP_Post_Type[] Array of registered post type objects keyed by their name.
	 */
	public function get_object_subtypes() {
		$post_types = array();

		/**
		 * Filters the list of post object sub types available within the sitemap.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_Post_Type[] $post_types Array of registered post type objects keyed by their name.
		 */
		return apply_filters( 'gofer_seo_sitemap_dates_post_types', $post_types );
	}

	/**
	 * Gets the max number of pages available for the object type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $post_type Optional. Post type name. Default empty.
	 * @return int Total number of pages.
	 */
	public function get_max_num_pages( $post_type = '' ) {
		if ( empty( $post_type ) ) {
			return 0;
		}

		$args                  = $this->get_query_args( $post_type );
		$args['fields']        = 'ids';
		$args['no_found_rows'] = false;

		$query = new WP_Query( $args );

		$min_num_pages = ( 'page' === $post_type && 'posts' === get_option( 'show_on_front' ) ) ? 1 : 0;
		return isset( $query->max_num_pages ) ? max( $min_num_pages, $query->max_num_pages ) : 1;
	}

	/**
	 * Get Query Args.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array $post_types Post type name.
	 * @return array Array of WP_Query arguments.
	 */
	public function get_query_args( $post_types ) {
		$args = array(
			'orderby'        => 'ID',
			//'orderby'        => 'post_date',
			'order'          => 'ASC',
			'post_type'      => $post_types,
			'posts_per_page' => 1000,
			'post_status'    => array( 'publish' ),
			'no_found_rows'  => true,
		);

		/**
		 * Filters the query arguments for post type sitemap queries.
		 *
		 * @see WP_Query::parse_query() for a full list of arguments.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $args      Array of WP_Query arguments.
		 * @param string $post_type Post type name.
		 */
		return apply_filters( 'gofer_seo_sitemaps_dates_query_args', $args, $post_types );
	}


//	public function get_sitemap_list() {
//		parent::get_sitemap_list();
//	}

	/**
	 * Gets a URL list for a post type sitemap.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $page_num  Page of results.
	 * @param string $post_type Optional. Post type name. Default empty.
	 * @return array Array of URLs for a sitemap.
	 */
	public function get_url_list( $page_num, $post_type = '' ) {
		// Bail early if the queried post type is not supported.
		$supported_types = $this->get_object_subtypes();

		if ( ! empty( $post_type ) && ! isset( $supported_types[ $post_type ] ) ) {
			return array();
		}
		$post_types = empty( $post_type ) ? array_keys( $supported_types ) : array( $post_type );

		global $wpdb;
		$args          = $this->get_query_args( $post_types );
		//$args['paged'] = $page_num;

		$date_results = gofer_seo_get_dates( $args );

		if ( $date_results ) {
			$year = null;
			foreach ( $date_results as $date_result ) {
				if ( $year !== $date_result->year ) {
					$year = $date_result->year;
					$sitemap_entry = array(
						'loc' => get_year_link( $date_result->year ),
					);

					/**
					 * Filters the sitemap entry for an individual post.
					 *
					 * @since 1.0.0
					 *
					 * @param array  $sitemap_entry Sitemap entry for the post.
					 * @param array  $date_result   Date array.
					 * @param string $post_type     Name of the post_type.
					 */
					$sitemap_entry = apply_filters( 'gofer_seo_sitemaps_dates_entry', $sitemap_entry, $date_result, $post_type );
					$url_list[]    = $sitemap_entry;
				}

				$sitemap_entry = array(
					'loc' => get_month_link( $date_result->year, $date_result->month ),
				);

				/**
				 * Filters the sitemap entry for an individual post.
				 *
				 * @since 1.0.0
				 *
				 * @param array  $sitemap_entry Sitemap entry for the post.
				 * @param array  $date_result   Date array.
				 * @param string $post_type     Name of the post_type.
				 */
				$sitemap_entry = apply_filters( 'gofer_seo_sitemaps_dates_entry', $sitemap_entry, $date_result, $post_type );
				$url_list[]    = $sitemap_entry;
			}
		}

		return $url_list;
	}


	/* **________________**********************************************************************************************/
	/* _/ EXTENDED/HOOKS \____________________________________________________________________________________________*/


	/**
	 * Sitemap Post Types.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post_Type[] $post_types
	 * @return WP_Post_Type[]
	 */
	public function sitemap_dates_post_types( $post_types ) {
		$gofer_seo_options  = Gofer_SEO_Options::get_instance();
		$enabled_post_types = gofer_seo_get_enabled_post_types( 'sitemap' );

		// Remove Post Types that are disabled.
		foreach ( $post_types as $key => $post_type ) {
			if ( ! in_array( $post_type->name, $enabled_post_types, true ) ) {
				unset( $post_types[ $key ] );
			}
		}

		// Add enabled Post Types with show_on enabled.
		foreach ( $enabled_post_types as $post_type_name ) {
			if (
					! isset( $post_types[ $post_type_name ] ) &&
					$gofer_seo_options->options['modules']['sitemap']['post_type_settings'][ $post_type_name ]['show_on']['standard_sitemap']
			) {
				$post_type_object = get_post_type_object( $post_type_name );
				if ( $post_type_object ) {
					$post_types[ $post_type_object->name ] = $post_type_object;
				}
			} elseif (
					isset( $post_types[ $post_type_name ] ) &&
					! $gofer_seo_options->options['modules']['sitemap']['post_type_settings'][ $post_type_name ]['show_on']['standard_sitemap']
			) {
				unset( $post_types[ $post_type_name ] );
			}

			// Validate.
			$args = array(
				'has_archive' => true,
				'_builtin'    => false,
			);
			$post_types_with_archives = get_post_types( $args );
			$post_types_with_archives[] = 'post';
			if (
					isset( $post_types[ $post_type_name ] ) &&
					in_array( $post_type_name, $post_types_with_archives, true ) &&
					! $post_types[ $post_type_name ] instanceof WP_Post_Type
			) {
				$post_type_object = get_post_type_object( $post_type_name );
				if ( $post_type_object ) {
					$post_types[ $post_type_object->name ] = $post_type_object;
				} else {
					unset( $post_types[ $post_type_name ] );
				}
			}

			if ( ! in_array( $post_type_name, $post_types_with_archives, true ) ) {
				unset( $post_types[ $post_type_name ] );
			}
		}

		return $post_types;
	}

	/**
	 * Sitemaps Dates Query Args.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $args      Array of WP_Query arguments.
	 * @param string $post_type Post type name.
	 * @return array
	 */
	public function sitemaps_dates_query_args( $args, $post_types ) {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		$post_types = is_array( $post_types ) ? $post_types : array( $post_types );

		$dates_args = array(
			//'orderby'        => 'ID',
			////'orderby'        => 'post_date',
			//'order'          => 'ASC',
			//'post_type'      => $post_types,
			//'posts_per_page' => 1000,
			//'post_status'    => array( 'publish' ),
			//'no_found_rows'  => true,



			'orderby'        => 'post_date',
			'posts_per_page' => $gofer_seo_options->options['modules']['sitemap']['posts_per_sitemap'],
			'has_archive'    => true,
			'_builtin'       => false,
			'cache_results'  => false,
			'has_password'   => false,
			'meta_query'     => array(
				'relation' => 'AND',

				// Exclude Term (Sitemap).
				array(
					'relation' => 'OR',
					array(
						'key'     => '_gofer_seo_modules_sitemap_enable_exclude',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => '_gofer_seo_modules_sitemap_enable_exclude',
						'value'   => '1',
						'compare' => '!=',
					),
				),

				// NoIndex Term (General).
				array(
					'relation' => 'OR',
					array(
						'key'     => '_gofer_seo_modules_general_enable_noindex',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => '_gofer_seo_modules_general_enable_noindex',
						'value'   => '1',
						'compare' => '!=',
					),
				),
			),
		);

		if ( $gofer_seo_options->options['modules']['sitemap']['exclude_post_ids'] ) {
			$dates_args['exclude'] = $gofer_seo_options->options['modules']['sitemap']['exclude_post_ids'];
		}

		// NoIndex Post Type, but Post is Index.
		foreach ( $post_types as $post_type ) {
			if ( $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post_type ]['enable_noindex'] ) {
				$dates_args['meta_query'][] = array(
					'relation' => 'OR',
					array(
						'key'     => '_gofer_seo_modules_general_enable_noindex',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => '_gofer_seo_modules_general_enable_noindex',
						'value'   => '1',
						'compare' => '!=',
					),
				);
			}
		}


		$args = wp_parse_args( $dates_args, $args );
		return $args;
	}

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
		if ( $object_type !== $this->object_type ) {
			return $sitemap_entry;
		}

		$args = array(
			'orderby'        => 'post_modified',
			'order'          => 'DESC',
			'paged'          => $page,
			'posts_per_page' => 1,
		);
		$args = wp_parse_args( $args, $this->get_query_args( $object_subtype ) );

		$query = new WP_Query( $args );
		if ( $query->have_posts() ) {
			// Last Modified.
			$timestamp = false;
			if ( '0000-00-00 00:00:00' !== $query->post->post_modified ) {
				$timestamp = $query->post->post_modified;
			} elseif ( '0000-00-00 00:00:00' !== $query->post->post_date ) {
				$timestamp = $query->post->post_date;
			}

			if ( $timestamp ) {
				$timezone_designator = gofer_seo_get_timezone_designator();
				$sitemap_entry['lastmod'] = mysql2date( 'Y-m-d\TH:i:s', $timestamp ) . $timezone_designator;
			}
		}

		return $sitemap_entry;
	}

	/**
	 * Sitemap Dates Entry.
	 *
	 * @since 1.0.0
	 *
	 * @param array   $sitemap_entry Sitemap entry for the post.
	 * @param object  $date          Date query object.
	 * @param string  $post_type     Name of the post_type.
	 * @return array
	 */
	public function sitemaps_dates_entry( $sitemap_entry, $date, $post_type ) {
		$args = array(
			'orderby'        => 'post_modified',
			'order'          => 'DESC',
			'year'           => $date->year,
			'monthnum'       => $date->month,
			'posts_per_page' => 1,
		);
		$args = wp_parse_args( $args, $this->get_query_args( $post_type ) );

		$query = new WP_Query( $args );

		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		$post = $query->post;
		$gofer_seo_post    = new Gofer_SEO_Post( $query->post );

		$timestamp = false;
		if ( '0000-00-00 00:00:00' !== $post->post_modified ) {
			$timestamp = $post->post_modified;
		} elseif ( '0000-00-00 00:00:00' !== $post->post_date ) {
			$timestamp = $post->post_date;
		}

		// Last Modified.
		if ( $timestamp ) {
			$timezone_designator = gofer_seo_get_timezone_designator();
			$sitemap_entry['lastmod'] = mysql2date( 'Y-m-d\TH:i:s', $timestamp ) . $timezone_designator;
		}

		// Priority.
		if ( -1 !== $gofer_seo_options->options['modules']['sitemap']['archive_date_settings']['priority'] ) {
			$sitemap_entry['priority'] = $gofer_seo_options->options['modules']['sitemap']['archive_date_settings']['priority'];
		} elseif ( -1 !== $gofer_seo_options->options['modules']['sitemap']['site_priority'] ) {
			$sitemap_entry['priority'] = $gofer_seo_options->options['modules']['sitemap']['site_priority'];
		}

		if ( 1 < $sitemap_entry['priority'] ) {
			$sitemap_entry['priority'] /= 10;
		}

		// Frequency.
		if ( 'default' !== $gofer_seo_options->options['modules']['sitemap']['archive_date_settings']['frequency'] ) {
			$sitemap_entry['changefreq'] = $gofer_seo_options->options['modules']['sitemap']['archive_date_settings']['frequency'];
		} elseif ( 'never' !== $gofer_seo_options->options['modules']['sitemap']['site_frequency'] ) {
			$sitemap_entry['changefreq'] = $gofer_seo_options->options['modules']['sitemap']['site_frequency'];
		}

		$sitemap_entry['image:image'] = array();

		return $sitemap_entry;
	}

}
