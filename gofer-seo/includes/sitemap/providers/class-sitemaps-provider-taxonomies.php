<?php
/**
 * Gofer SEO Sitemaps: Provider - Taxonomies
 *
 * Class intended to shadow WP 5.5 sitemap concept, and use hooks to add additional inputs.
 * This would allow switching between WP's and Gofer SEO's concepts,
 * and potentially making any updates easier to handle.
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Sitemaps_Provider_Taxonomies.
 *
 * @since 1.0.0
 */
class Gofer_SEO_Sitemaps_Provider_Taxonomies extends Gofer_SEO_Sitemaps_Provider {

	/**
	 * Provider name.
	 *
	 * This will also be used as the public-facing name in URLs.
	 *
	 * @since 1.0.0
	 *
	 * @var string $name
	 */
	protected $name = 'taxonomies';

	/**
	 * Object Type.
	 *
	 * Object type name (e.g. 'post', 'term', 'user').
	 *
	 * @since 1.0.0
	 *
	 * @var string $object_type
	 */
	protected $object_type = 'term';

	/**
	 * Gofer_SEO_Sitemaps_Provider_Taxonomies constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		add_filter( 'wp_sitemap_taxonomies', array( $this, 'sitemap_taxonomies' ) );
		add_filter( 'gofer_seo_sitemap_taxonomies', array( $this, 'sitemap_taxonomies' ) );

		add_filter( 'wp_sitemaps_taxonomies_query_args', array( $this, 'sitemaps_taxonomies_query_args' ), 10, 2 );
		add_filter( 'gofer_seo_sitemaps_taxonomies_query_args', array( $this, 'sitemaps_taxonomies_query_args' ), 10, 2 );

		add_filter( 'wp_sitemaps_taxonomies_entry', array( $this, 'sitemaps_taxonomies_entry' ), 10, 3 );
		add_filter( 'gofer_seo_sitemaps_taxonomies_entry', array( $this, 'sitemaps_taxonomies_entry' ), 10, 3 );
	}

	/**
	 * @return WP_Taxonomy[] Array of registered post type objects keyed by their name.
	 */
	public function get_object_subtypes() {
		$taxonomies = array();

		/**
		 * Filters the list of taxonomy object subtypes available within the sitemap.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_Taxonomy[] $taxonomies Array of registered taxonomy objects keyed by their name.
		 */
		return apply_filters( 'gofer_seo_sitemap_taxonomies', $taxonomies );
	}

	/**
	 * Gets the max number of pages available for the object type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $taxonomy Taxonomy name.
	 * @return int Total number of pages.
	 */
	public function get_max_num_pages( $taxonomy = '' ) {
		if ( empty( $taxonomy ) ) {
			return 0;
		}

		$term_count = wp_count_terms( $this->get_query_args( $taxonomy ) );

		return (int) ceil( $term_count / parent::get_max_num_pages( $taxonomy ) );
	}

	/**
	 * Get Query Args.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array $taxonomies Taxonomy name.
	 * @return array Array of WP_Term_Query arguments.
	 */
	public function get_query_args( $taxonomies ) {
		$args = array(
			'fields'       => 'ids',
			'taxonomy'     => $taxonomies,
			'orderby'      => 'term_order',
			'number'       => 1000,
			'hide_empty'   => true,
			'hierarchical' => false,
		);

		/**
		 * Filters the taxonomy terms query arguments.
		 *
		 * Allows modification of the taxonomy query arguments before querying.
		 *
		 * @since 1.0.0
		 *
		 * @see WP_Term_Query::__construct() for a full list of arguments
		 *
		 * @param array        $args       Array of WP_Term_Query arguments.
		 * @param string|array $taxonomies Taxonomy name.
		 */
		return apply_filters( 'gofer_seo_sitemaps_taxonomies_query_args', $args, $taxonomies );
	}

	/**
	 * Gets a URL list for a taxonomy sitemap.
	 *
	 * @since 5.5.0
	 *
	 * @param int    $page_num Page of results.
	 * @param string $taxonomy Optional. Taxonomy name. Default empty.
	 * @return array Array of URLs for a sitemap.
	 */
	public function get_url_list( $page_num, $taxonomy = '' ) {
		// Bail early if the queried post type is not supported.
		$supported_types = $this->get_object_subtypes();

		if ( ! empty( $taxonomy ) && ! isset( $supported_types[ $taxonomy ] ) ) {
			return array();
		}
		$taxonomies = empty( $taxonomy ) ? array_keys( $supported_types ) : array( $taxonomy );
		if ( empty( $taxonomies ) ) {
			return array();
		}

		$url_list = array();

		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		// Offset by how many terms should be included in previous pages.
		$offset = ( $page_num - 1 ) * $gofer_seo_options->options['modules']['sitemap']['posts_per_sitemap'];

		$args           = $this->get_query_args( $taxonomies );
		$args['offset'] = $offset;
		$args['fields'] = 'all';

		$term_query = new WP_Term_Query( $args );

		if ( ! empty( $term_query->terms ) ) {
			foreach ( $term_query->terms as $term ) {
				$term_link = get_term_link( $term, $term->taxonomy );

				if ( is_wp_error( $term_link ) ) {
					continue;
				}

				$sitemap_entry = array(
					'loc' => $term_link,
				);

				/**
				 * Filters the sitemap entry for an individual term.
				 *
				 * @since 1.0.0
				 *
				 * @param array   $sitemap_entry Sitemap entry for the term.
				 * @param WP_Term $term          Term object.
				 * @param string  $taxonomy      Taxonomy name.
				 */
				$sitemap_entry = apply_filters( 'gofer_seo_sitemaps_taxonomies_entry', $sitemap_entry, $term, $term->taxonomy );
				$url_list[]    = $sitemap_entry;
			}
		}

		return $url_list;
	}


	/* **________________**********************************************************************************************/
	/* _/ EXTENDED/HOOKS \____________________________________________________________________________________________*/


	/**
	 * Sitemap Taxonomies.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Taxonomy[] $taxonomies
	 * @return WP_Taxonomy[]
	 */
	public function sitemap_taxonomies( $taxonomies ) {
		$gofer_seo_options  = Gofer_SEO_Options::get_instance();
		$enabled_taxonomies = gofer_seo_get_enabled_taxonomies( 'sitemap' );

		// Remove Taxonomies that are disabled.
		foreach ( $taxonomies as $key => $taxonomy ) {
			if ( ! in_array( $taxonomy->name, $enabled_taxonomies, true ) ) {
				unset( $taxonomies[ $key ] );
			}
		}

		// Add enabled Taxonomies.
		foreach ( $enabled_taxonomies as $taxonomy_name ) {
			if (
				! isset( $taxonomies[ $taxonomy_name ] ) &&
				$gofer_seo_options->options['modules']['sitemap']['taxonomy_settings'][ $taxonomy_name ]['show_on']['standard_sitemap']
			) {
				$taxonomy_object = get_taxonomy( $taxonomy_name );
				if ( $taxonomy_object ) {
					$taxonomies[ $taxonomy_object->name ] = $taxonomy_object;
				}
			} elseif (
				isset( $taxonomies[ $taxonomy_name ] ) &&
				! $gofer_seo_options->options['modules']['sitemap']['taxonomy_settings'][ $taxonomy_name ]['show_on']['standard_sitemap']
			) {
				unset( $taxonomies[ $taxonomy_name ] );
			}

			// Validate.
			if (
				isset( $taxonomies[ $taxonomy_name ] ) &&
				! $taxonomies[ $taxonomy_name ] instanceof WP_Taxonomy
			) {
				$taxonomy_object = get_taxonomy( $taxonomy_name );
				if ( $taxonomy_object ) {
					$taxonomies[ $taxonomy_object->name ] = $taxonomy_object;
				} else {
					unset( $taxonomies[ $taxonomy_name ] );
				}
			}
		}

		return $taxonomies;
	}

	/**
	 * Sitemaps Taxonomies Query Args.
	 *
	 * @since 1.0.0
	 *
	 * @see WP_Term_Query::__construct() for a full list of arguments
	 *
	 * @param array        $args       Array of WP_Term_Query arguments.
	 * @param string|array $taxonomies Taxonomy name.
	 * @return array
	 */
	public function sitemaps_taxonomies_query_args( $args, $taxonomies ) {
//		$taxonomies = get_taxonomy( $taxonomies );
//		if ( ! $taxonomies ) {
//			return $args;
//		}

		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		$taxonomies = is_array( $taxonomies ) ? $taxonomies : array( $taxonomies );
		if ( empty( $taxonomies ) ) {
			return $args;
		}

		$terms_args = array(
			'orderby'    => 'name',
			'number'     => $gofer_seo_options->options['modules']['sitemap']['posts_per_sitemap'],
			'meta_query' => array(
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

		foreach ( $taxonomies as $taxonomy ) {
			$taxonomy = get_taxonomy( $taxonomy );
			if ( ! empty( $gofer_seo_options->options['modules']['sitemap']['exclude_term_ids'] ) ) {
				if ( isset( $gofer_seo_options->options['modules']['sitemap']['exclude_term_ids'][ $taxonomy->name ] ) ) {
					$terms_args['exclude'] = array_merge( $terms_args['exclude'], $gofer_seo_options->options['modules']['sitemap']['exclude_term_ids'][ $taxonomy->name ] );
				}
			}

			// NoIndex Taxonomy, but Term is Index.
			if ( $gofer_seo_options->options['modules']['general']['taxonomy_settings'][ $taxonomy->name ]['enable_noindex'] ) {
				$terms_args['meta_query'][] = array(
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

		$args = wp_parse_args( $terms_args, $args );
		return $args;
	}

	/**
	 * Sitemaps Index Entry.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $sitemap_entry  Sitemap entry for the post|term|user.
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
			'fields'  => 'all',
			'orderby' => 'post_data',
			'paged'   => $page,
		);
		$args = wp_parse_args( $args, $this->get_query_args( $object_subtype ) );

		$query = new WP_Query( $args );
		if ( $query->have_posts() ) {
			// Last Modified.
			$timestamp = false;
			if ( '0000-00-00 00:00:00' !== $query->post->post_modified_gmt ) {
				$timestamp = $query->post->post_modified_gmt;
			} elseif ( '0000-00-00 00:00:00' !== $query->post->post_date_gmt ) {
				$timestamp = $query->post->post_date_gmt;
			}

			if ( $timestamp ) {
				$timezone_designator = gofer_seo_get_timezone_designator();
				$sitemap_entry['lastmod']  = mysql2date( 'Y-m-d\TH:i:s', $timestamp ) . $timezone_designator;
			}
		}

		return $sitemap_entry;
	}

	/**
	 * Sitemap Taxonomies Entry.
	 *
	 * @since 1.0.0
	 *
	 * @param array       $sitemap_entry Sitemap entry for the term.
	 * @param WP_Term|int $term          Term object OR id.
	 * @param string      $taxonomy      Taxonomy name.
	 * @return array
	 */
	public function sitemaps_taxonomies_entry( $sitemap_entry, $term, $taxonomy ) {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		$gofer_seo_term    = new Gofer_SEO_Term( $term );
		$wp_term           = $gofer_seo_term->term;
		$wp_taxonomy       = get_taxonomy( $wp_term->taxonomy );


		$query_last_modified = new WP_Query(
			array(
				'post_type'      => $wp_taxonomy->object_type,
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'orderby'        => 'modified',
				'order'          => 'DESC',
				'taxonomy'       => $wp_term->taxonomy,
				'term'           => $wp_term->slug,
			)
		);

		$timestamp = false;
		if ( $query_last_modified->have_posts() ) {
			// Last Modified.
			if ( '0000-00-00 00:00:00' !== $query_last_modified->post->post_modified_gmt ) {
				$timestamp = $query_last_modified->post->post_modified_gmt;
			} elseif ( '0000-00-00 00:00:00' !== $query_last_modified->post->post_date_gmt ) {
				$timestamp = $query_last_modified->post->post_date_gmt;
			}

			if ( $timestamp ) {
				$timezone_designator = gofer_seo_get_timezone_designator();
				$sitemap_entry['lastmod']  = mysql2date( 'Y-m-d\TH:i:s', $timestamp ) . $timezone_designator;
			}
		}

		// Priority.
		if ( -1 !== $gofer_seo_term->meta['modules']['sitemap']['priority'] ) {
			$sitemap_entry['priority'] = $gofer_seo_term->meta['modules']['sitemap']['priority'];
		} elseif ( -1 !== $gofer_seo_options->options['modules']['sitemap']['taxonomy_settings'][ $wp_term->taxonomy ]['priority'] ) {
			$sitemap_entry['priority'] = $gofer_seo_options->options['modules']['sitemap']['taxonomy_settings'][ $wp_term->taxonomy ]['priority'];
		} elseif ( -1 !== $gofer_seo_options->options['modules']['sitemap']['taxonomy_default_priority'] ) {
			$sitemap_entry['priority'] = $gofer_seo_options->options['modules']['sitemap']['taxonomy_default_priority'];
		} elseif ( -1 !== $gofer_seo_options->options['modules']['sitemap']['site_priority'] ) {
			$sitemap_entry['priority'] = $gofer_seo_options->options['modules']['sitemap']['site_priority'];
		}

		if ( 1 < $sitemap_entry['priority'] ) {
			$sitemap_entry['priority'] /= 10;
		}

		// Frequency.
		if ( 'default' !== $gofer_seo_term->meta['modules']['sitemap']['frequency'] ) {
			$sitemap_entry['changefreq'] = $gofer_seo_term->meta['modules']['sitemap']['frequency'];
		} elseif ( 'default' !== $gofer_seo_options->options['modules']['sitemap']['taxonomy_settings'][ $wp_term->taxonomy ]['frequency'] ) {
			$sitemap_entry['changefreq'] = $gofer_seo_options->options['modules']['sitemap']['taxonomy_settings'][ $wp_term->taxonomy ]['frequency'];
		} elseif ( 'default' !== $gofer_seo_options->options['modules']['sitemap']['taxonomy_default_frequency'] ) {
			$sitemap_entry['changefreq'] = $gofer_seo_options->options['modules']['sitemap']['taxonomy_default_frequency'];
		} elseif ( 'never' !== $gofer_seo_options->options['modules']['sitemap']['site_frequency'] ) {
			$sitemap_entry['changefreq'] = $gofer_seo_options->options['modules']['sitemap']['site_frequency'];
		}

		// Images.
		$images = $gofer_seo_term->get_images();
		$sitemap_entry['image:image'] = array();
		foreach ( $images as $image ) {
			$sitemap_entry['image:image'][] = array(
				'loc'           => $image['url'],
				'image:caption' => $image['caption'],
				'image:title'   => $image['title'],
				// 'image:geo_location' => '',
				// 'image:license' => '',
			);
		}

		// RSS.
		/**
		 * @link https://www.bing.com/webmaster/help/media-rss-mrss-video-feed-specification-350cfabf
		 */
		if ( $timestamp ) {
			// RSS expects the GMT date.
			$rss = array(
				'title'       => $wp_term->name,
				'link'        => $sitemap_entry['loc'],
				'pubDate'     => wp_date( 'r', mysql2date( 'U', $timestamp ) ),
				'description' => $wp_term->description,
				// 'thumbnail'   => '',
				// 'content'     => '',
			);
		}

		return $sitemap_entry;
	}

}
