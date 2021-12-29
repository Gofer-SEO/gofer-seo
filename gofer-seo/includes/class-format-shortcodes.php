<?php
/**
 * Gofer SEO Format Shortcodes
 *
 * Used to convert format strings with format shortcodes.
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Format_Shortcodes.
 *
 * @since 1.0.0
 */
class Gofer_SEO_Format_Shortcodes {

	/**
	 * The Context to base values on.
	 *
	 * @since 1.0.0
	 *
	 * @var Gofer_SEO_Context $context
	 */
	public $context;

	/**
	 * Stores other (theme/plugin) shortcodes.
	 *
	 * Intended to prevent conflicts by storing other shortcodes when adding Format Shortcodes,
	 * and restore the other shortcodes when finished.
	 *
	 * @since 1.0.0
	 *
	 * @var callable[]
	 */
	private $other_shortcodes = array();

	/**
	 * Gofer_SEO_Format_Shortcodes constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Site|WP_Post|WP_Post_Type|WP_Taxonomy|WP_Term|WP_User|null $object
	 */
	public function __construct( $object = null ) {
		$this->context = Gofer_SEO_Context::get_instance( $object );

		$this->load();
	}

	/**
	 * Load Format Shortcodes.
	 *
	 * @since 1.0.0.
	 */
	public function load() {

		/**
		 * Format Shortcodes Class is Loaded.
		 *
		 * @since 1.0.0
		 *
		 * @param Gofer_SEO_Format_Shortcodes $this
		 */
		do_action( 'gofer_seo_format_shortcodes_loaded', $this );
	}
	/**
	 * Format a String.
	 *
	 * Converts a title/description format (setting) with the shortcodes' return value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $str The string to format.
	 * @return string
	 */
	public function format( $str ) {
		$this->add_shortcodes();
		$formatted_string = do_shortcode( $str );
		$this->remove_shortcodes();

		return $formatted_string;
	}

	/**
	 * Get Registered Shortcodes.
	 *
	 * @since 1.0.0
	 *
	 * @return callable
	 */
	private function get_registered_shortcodes() {
		/**
		 * @var callable $shortcodes
		 */
		$shortcodes = array(
			'title'                 => array( $this, 'title' ),
			'site_title'            => array( $this, 'site_title' ),
			'post_title'            => array( $this, 'post_title' ),
			'post_type_title'       => array( $this, 'post_type_title' ),
			'taxonomy_title'        => array( $this, 'taxonomy_title' ),
			'term_title'            => array( $this, 'term_title' ),
			'archive_title'         => array( $this, 'archive_title' ),
			'description'           => array( $this, 'description' ),
			'site_description'      => array( $this, 'site_description' ),
			'post_description'      => array( $this, 'post_description' ),
			'post_type_description' => array( $this, 'post_type_description' ),
			'taxonomy_description'  => array( $this, 'taxonomy_description' ),
			'term_description'      => array( $this, 'term_description' ),
			'author_username'       => array( $this, 'author_username' ),
			'author_nicename'       => array( $this, 'author_nicename' ),
			'author_nickname'       => array( $this, 'author_nickname' ),
			'author_display_name'   => array( $this, 'author_display_name' ),
			'author_firstname'      => array( $this, 'author_firstname' ),
			'author_lastname'       => array( $this, 'author_lastname' ),
			'date'                  => array( $this, 'date' ),
			'date_modified'         => array( $this, 'date_modified' ),
			'year'                  => array( $this, 'year' ),
			'month'                 => array( $this, 'month' ),
			'day'                   => array( $this, 'day' ),
			'post_date'             => array( $this, 'post_date' ),
			'post_date_modified'    => array( $this, 'post_date_modified' ),
			'post_year'             => array( $this, 'post_year' ),
			'post_month'            => array( $this, 'post_month' ),
			'post_day'              => array( $this, 'post_day' ),
			'current_date'          => array( $this, 'current_date' ),
			'current_year'          => array( $this, 'current_year' ),
			'current_month'         => array( $this, 'current_month' ),
			'current_day'           => array( $this, 'current_day' ),
			'search_value'          => array( $this, 'search_value' ),
			'request_uri'           => array( $this, 'request_uri' ),
			'request_words'         => array( $this, 'request_words' ),
			'page'                  => array( $this, 'page' ),
			'pages'                 => array( $this, 'pages' ),
			'meta'                  => array( $this, 'meta' ),
			'site_meta'             => array( $this, 'site_meta' ),
			'post_meta'             => array( $this, 'post_meta' ),
			'term_meta'             => array( $this, 'term_meta' ),
			'user_meta'             => array( $this, 'user_meta' ),
		);

		/**
		 * Register Format Shortcodes.
		 *
		 * @since 1.0.0
		 *
		 * @param array[] $shortcodes An array of callbacks.
		 */
		$shortcodes = apply_filters( 'gofer_seo_register_format_shortcodes', $shortcodes );

		return $shortcodes;
	}

	/**
	 * Add Shortcodes.
	 *
	 * @since 1.0.0
	 */
	private function add_shortcodes() {
		$registered_shortcodes = $this->get_registered_shortcodes();

		foreach ( $registered_shortcodes as $tag => $callback ) {
			// Store any other plugin/theme shortcodes, and restore after running shortcodes.
			if ( shortcode_exists( $tag ) ) {
				global $shortcode_tags;
				$this->other_shortcodes[ $tag ] = $shortcode_tags[ $tag ];
			}
			add_shortcode( $tag, $callback );
		}
	}

	/**
	 * Remove Shortcodes.
	 *
	 * @since 1.0.0
	 */
	private function remove_shortcodes() {
		$registered_shortcodes = $this->get_registered_shortcodes();

		foreach ( $registered_shortcodes as $tag => $callback ) {
			remove_shortcode( $tag );
		}

		// Restore other shortcodes.
		foreach ( $this->other_shortcodes as $tag => $callback ) {
			global $shortcode_tags;
			// Restores any conflicting/duplicate shortcodes.
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$shortcode_tags[ $tag ] = $callback;
		}
		$this->other_shortcodes = array();
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function title( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'title'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Site':
				$rtn_str = $this->site_title( $attrs );
				break;

			case 'WP_Post' :
				$rtn_str = $this->post_title( $attrs );
				break;

			case 'WP_Post_Type':
				$rtn_str = $this->post_type_title( $attrs );
				break;

			case 'WP_Term':
				$rtn_str = $this->term_title( $attrs );
				break;

			case 'WP_User':
				$rtn_str = $this->author_display_name( $attrs );
				break;

			case 'var_date':
			case 'var_date_year':
			case 'var_date_month':
			case 'var_date_day':
				$rtn_str = $this->archive_title( $attrs );
				break;
		}

		/**
		 * Prototype Filter.
		 *
		 * TODO Investigate WP filters.
		 *     - do_shortcode_tag() - https://developer.wordpress.org/reference/functions/do_shortcode_tag/
		 *     - 'pre_do_shortcode_tag' filter - https://developer.wordpress.org/reference/hooks/pre_do_shortcode_tag/
		 *     - 'do_shortcode_tag' filter - https://developer.wordpress.org/reference/hooks/do_shortcode_tag/
		 *     - 'shortcode_atts_{$shortcode}' filter - https://developer.wordpress.org/reference/hooks/shortcode_atts_shortcode/
		 * TODO OR Add filter to all shortcodes.
		 */
		$rtn_str = apply_filters( 'gofer_seo_format_shortcode_site_title', $rtn_str, $attrs, $this->context );

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function site_title( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'site_title'
		);
		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		$rtn_str = '';

		// Check for (static) home page title.
		if ( is_page() && $gofer_seo_options->options['modules']['general']['use_static_homepage'] ) {
			$homepage_id = get_option( 'page_on_front' );
			if ( $homepage_id ) {
				$gofer_seo_post = new Gofer_SEO_Post( $homepage_id );
				if ( ! empty( $gofer_seo_post->meta['modules']['general']['title'] ) ) {
					$rtn_str = $gofer_seo_post->meta['modules']['general']['title'];
				}
			}
		}

		// Check for plugin title.
		if ( $gofer_seo_options->options['modules']['general']['enable_site_title'] ) {
			if ( ! empty( $gofer_seo_options->options['modules']['general']['site_title'] ) ) {
				$rtn_str = $gofer_seo_options->options['modules']['general']['site_title'];
			}
		}

		// If still empty, fallback on WP site title.
		if ( empty( $rtn_str ) ) {
			$rtn_str = get_bloginfo( 'name' );
		}

		return $rtn_str;
	}

	/**
	 * Shortcode - Post Title.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function post_title( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(
				'source' => 'any',
			),
			$attrs,
			'post_title'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Post' :
				$gofer_seo_post = new Gofer_SEO_Post( $this->context->context_key );
				if (
						! empty( $gofer_seo_post->meta['modules']['general']['title'] ) &&
						'wp' !== $attrs['source']
				) {
					$rtn_str = $gofer_seo_post->meta['modules']['general']['title'];
				} elseif ( 'seo' !== $attrs['source'] ) {
					$rtn_str = $this->context->get_display_name();
				}
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function post_type_title( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'post_type_title'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Post':
				$post = $this->context::get_object( $this->context->context_type, $this->context->context_key );

				$args = array(
					'context_type' => 'WP_Post_Type',
					'context_key'  => $post->post_type,
				);
				$post_type_context = $this->context::get_instance( $args );

				$rtn_str = $post_type_context->get_display_name();
				break;

			case 'WP_Post_Type':
				$rtn_str = $this->context->get_display_name();
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function taxonomy_title( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'taxonomy_title'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Taxonomy':
				$rtn_str = $this->context->get_display_name();
				break;

			case 'WP_Term':
				$term = $this->context::get_object( $this->context->context_type, $this->context->context_key );
				$args = array(
					'context_type' => 'WP_Taxonomy',
					'context_key'  => $term->taxonomy,
				);
				$taxonomy_context = $this->context::get_instance( $args );

				$rtn_str = $taxonomy_context->get_display_name();
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function term_title( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(
				'taxonomy' => 'category',
				'source'   => 'any',
			),
			$attrs,
			'term_title'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Post':
				$post      = Gofer_SEO_Context::get_object( 'WP_Post', $this->context->context_key );
				$post_type = Gofer_SEO_Context::get_object( 'WP_Post_Type', $post->post_type );
				if ( in_array( $attrs['taxonomy'], $post_type->taxonomies, true ) ) {
					$args = array(
						'fields'     => 'ids',
						'hide_empty' => true,
						'taxonomy'   => $attrs['taxonomy'],
					);
					$term_ids = get_terms( $args );

					if ( ! empty( $term_ids ) ) {
						$args_context = array(
							'context_type' => 'WP_Term',
							'context_key'  => $term_ids[0],
						);
						$term_context = Gofer_SEO_Context::get_instance( $args_context );

						$gofer_seo_term = new Gofer_SEO_Term( $term_context->context_key );
						if (
								! empty( $gofer_seo_term->meta['modules']['general']['title'] ) &&
								'wp' !== $attrs['source']
						) {
							$rtn_str = $gofer_seo_term->meta['modules']['general']['title'];
						} elseif ( 'seo' !== $attrs['source'] ) {
							$rtn_str = $term_context->get_display_name();
						}
					}
				}
				break;

			case 'WP_Term':
				$gofer_seo_term = new Gofer_SEO_Term( $this->context->context_key );
				if (
						! empty( $gofer_seo_term->meta['modules']['general']['title'] ) &&
						'wp' !== $attrs['source']
				) {
					$rtn_str = $gofer_seo_term->meta['modules']['general']['title'];
				} elseif ( 'seo' !== $attrs['source'] ) {
					$rtn_str = $this->context->get_display_name();
				}
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function archive_title( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'archive_title'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Post_Type':
				// Post Type Archive.
				$rtn_str = $this->context->get_display_name();
				break;

			case 'WP_User':
				// Author Archive.
				$gofer_seo_user = new Gofer_SEO_User( $this->context->context_key );
				if ( ! empty( $gofer_seo_user->meta['modules']['general']['title'] ) ) {
					$rtn_str = $gofer_seo_user->meta['modules']['general']['title'];
				} else {
					$rtn_str = $this->context->get_display_name();
				}
				break;

			case 'WP_Term':
				// Taxonomy-Term Archive.
				$gofer_seo_term = new Gofer_SEO_Term( $this->context->context_key );
				if ( ! empty( $gofer_seo_term->meta['modules']['general']['title'] ) ) {
					$rtn_str = $gofer_seo_term->meta['modules']['general']['title'];
				} else {
					$rtn_str = $this->context->get_display_name();
				}
				break;

			case 'var_date_year':
				/* translators: %s is replaced with the Year. */
				$rtn_str = sprintf( __( 'Yearly Archive %s', 'gofer-seo' ), get_the_date( 'Y' ) );
				break;

			case 'var_date_month':
				/* translators: %s is replaced with the Month Year */
				$rtn_str = sprintf( __( 'Monthly Archive %s', 'gofer-seo' ), get_the_date( 'F Y' ) );
				break;

			case 'var_date':
			case 'var_date_day':
				/* translators: %s is replaced with the Month Day, Year. */
				$rtn_str = sprintf( __( 'Daily Archive %s', 'gofer-seo' ), get_the_date( 'F j, Y' ) );
				//	$rtn_str = $this->context->get_display_name();
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function description( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'description'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Site':
				$rtn_str = $this->site_description( $attrs );
				break;

			case 'WP_Post' :
				$rtn_str = $this->post_description( $attrs );
				break;

			case 'WP_Post_Type':
				$rtn_str = $this->post_type_description( $attrs );
				break;

			case 'WP_Term':
				$rtn_str = $this->term_description( $attrs );
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function site_description( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'site_description'
		);
		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		$rtn_str = '';

		// Check for (static) home page description.
		if ( is_page() && $gofer_seo_options->options['modules']['general']['use_static_homepage'] ) {
			$homepage_id = get_option( 'page_on_front' );
			if ( $homepage_id ) {
				$gofer_seo_post = new Gofer_SEO_Post( $homepage_id );
				if ( ! empty( $gofer_seo_post->meta['modules']['general']['description'] ) ) {
					$rtn_str = $gofer_seo_post->meta['modules']['general']['description'];
				}
			}
		}

		// Check for plugin description.
		if ( $gofer_seo_options->options['modules']['general']['enable_site_description'] ) {
			if ( ! empty( $gofer_seo_options->options['modules']['general']['site_description'] ) ) {
				$rtn_str = $gofer_seo_options->options['modules']['general']['site_description'];
			}
		}

		// If still empty, fallback on WP site description.
		if ( empty( $rtn_str ) ) {
			$rtn_str = get_bloginfo( 'description' );
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function post_description( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(
				'source' => 'any',
			),
			$attrs,
			'post_description'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Post' :
				$gofer_seo_post = new Gofer_SEO_Post( $this->context->context_key );
				if (
						! empty( $gofer_seo_post->meta['modules']['general']['description'] ) &&
						'wp' !== $attrs['source']
				) {
					$rtn_str = $gofer_seo_post->meta['modules']['general']['description'];
				} elseif ( 'seo' !== $attrs['source'] ) {
					$rtn_str = $this->context->get_description();
				}
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function post_type_description( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'post_type_description'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Post':
				$post = $this->context::get_object( $this->context->context_type, $this->context->context_key );

				$args = array(
					'context_type' => 'WP_Post_Type',
					'context_key'  => $post->post_type,
				);
				$post_type_context = $this->context::get_instance( $args );

				$rtn_str = $post_type_context->get_description();
				break;

			case 'WP_Post_Type':
				$rtn_str = $this->context->get_description();
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function taxonomy_description( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'taxonomy_description'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Taxonomy':
				$rtn_str = $this->context->get_description();
				break;

			case 'WP_Term':
				$term = $this->context::get_object( $this->context->context_type, $this->context->context_key );
				$args = array(
					'context_type' => 'WP_Taxonomy',
					'context_key'  => $term->taxonomy,
				);
				$taxonomy_context = $this->context::get_instance( $args );

				$rtn_str = $taxonomy_context->get_description();
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function term_description( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(
				'taxonomy' => 'category',
				'source'   => 'any',
			),
			$attrs,
			'term_description'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Post':
				$post      = Gofer_SEO_Context::get_object( 'WP_Post', $this->context->context_key );
				$post_type = Gofer_SEO_Context::get_object( 'WP_Post_Type', $post->post_type );
				if ( in_array( $attrs['taxonomy'], $post_type->taxonomies, true ) ) {
					$args = array(
						'fields'     => 'ids',
						'hide_empty' => true,
						'taxonomy'   => $attrs['taxonomy'],
					);
					$term_ids = get_terms( $args );

					if ( ! empty( $term_ids ) ) {
						$args_context = array(
							'context_type' => 'WP_Term',
							'context_key'  => $term_ids[0],
						);
						$term_context = Gofer_SEO_Context::get_instance( $args_context );

						$gofer_seo_term = new Gofer_SEO_Term( $term_context->context_key );
						if (
							! empty( $gofer_seo_term->meta['modules']['general']['description'] ) &&
							'wp' !== $attrs['source']
						) {
							$rtn_str = $gofer_seo_term->meta['modules']['general']['description'];
						} elseif ( 'seo' !== $attrs['source'] ) {
							$rtn_str = $term_context->get_description();
						}
					}
				}
				break;

			case 'WP_Term':
				$gofer_seo_term = new Gofer_SEO_Term( $this->context->context_key );
				if ( ! empty( $gofer_seo_term->meta['modules']['general']['description'] ) ) {
					$rtn_str = $gofer_seo_term->meta['modules']['general']['description'];
				} else {
					$rtn_str = $this->context->get_description();
				}
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function author_username( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'author_username'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Post':
				$post = $this->context::get_object( 'WP_Post', $this->context->context_key );
				$args = array(
					'context_type' => 'WP_User',
					'context_key'  => $post->post_author,
				);
				$user_context = Gofer_SEO_Context::get_instance( $args );

				// user_login.
				$rtn_str = $user_context->get_slug();
				break;

			case 'WP_User':
				// user_login.
				$rtn_str = $this->context->get_slug();
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function author_nicename( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'author_nicename'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Post':
				$post = $this->context::get_object( 'WP_Post', $this->context->context_key );
				$user = Gofer_SEO_Context::get_object( 'WP_User', $post->post_author );

				$rtn_str = $user->user_nicename;
				break;

			case 'WP_User':
				$user = Gofer_SEO_Context::get_object( 'WP_User', $this->context->context_key );

				$rtn_str = $user->user_nicename;
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function author_nickname( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'author_nickname'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Post':
				$post = $this->context::get_object( 'WP_Post', $this->context->context_key );
				$user = Gofer_SEO_Context::get_object( 'WP_User', $post->post_author );

				$rtn_str = $user->nickname;
				break;

			case 'WP_User':
				$user = Gofer_SEO_Context::get_object( 'WP_User', $this->context->context_key );

				$rtn_str = $user->nickname;
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function author_display_name( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'author_display_name'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Post':
				$post = $this->context::get_object( 'WP_Post', $this->context->context_key );
				$args = array(
					'context_type' => 'WP_User',
					'context_key'  => $post->post_author,
				);
				$user_context = Gofer_SEO_Context::get_instance( $args );

				$rtn_str = $user_context->get_display_name();
				break;

			case 'WP_User':
				$rtn_str = $this->context->get_display_name();
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function author_firstname( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'author_firstname'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Post':
				$post = $this->context::get_object( 'WP_Post', $this->context->context_key );
				$user = Gofer_SEO_Context::get_object( 'WP_User', $post->post_author );

				$rtn_str = $user->first_name;
				break;

			case 'WP_User':
				$user = Gofer_SEO_Context::get_object( 'WP_User', $this->context->context_key );

				$rtn_str = $user->first_name;
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function author_lastname( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'author_lastname'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Post':
				$post = $this->context::get_object( 'WP_Post', $this->context->context_key );
				$user = Gofer_SEO_Context::get_object( 'WP_User', $post->post_author );

				$rtn_str = $user->last_name;
				break;

			case 'WP_User':
				$user = Gofer_SEO_Context::get_object( 'WP_User', $this->context->context_key );

				$rtn_str = $user->last_name;
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 * @throws Exception
	 */
	public function date( $attrs ) {
		global $wp_version;
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(
				'format' => get_option( 'date_format' ),
			),
			$attrs,
			'date'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Post':
				$rtn_str = $this->post_date( $attrs );
				break;

			case 'var_date':
				$attrs['format'] = empty( $attrs['format'] ) ? 'F j, Y' : $attrs['format'];
				$rtn_str = get_the_date( $attrs['format'] );
				break;

			case 'var_date_year':
				$attrs['format'] = empty( $attrs['format'] ) ? 'Y' : $attrs['format'];
				$rtn_str = get_the_date( $attrs['format'] );
				break;

			case 'var_date_month':
				$attrs['format'] = empty( $attrs['format'] ) ? 'F' : $attrs['format'];
				$rtn_str = get_the_date( $attrs['format'] );
				break;

			case 'var_date_day':
				$attrs['format'] = empty( $attrs['format'] ) ? 'j' : $attrs['format'];
				$rtn_str = get_the_date( $attrs['format'] );
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 * @throws Exception
	 */
	public function date_modified( $attrs ) {
		global $wp_version;
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(
				'format' => get_option( 'date_format' ),
			),
			$attrs,
			'date_modified'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Post':
				$rtn_str = $this->post_date_modified( $attrs );
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 * @throws Exception
	 */
	public function year( $attrs ) {
		global $wp_version;
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'year'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Post':
				$rtn_str = $this->post_year( $attrs );
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 * @throws Exception
	 */
	public function month( $attrs ) {
		global $wp_version;
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'month'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Post':
				$rtn_str = $this->post_month( $attrs );
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 * @throws Exception
	 */
	public function day( $attrs ) {
		global $wp_version;
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'day'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Post':
				$rtn_str = $this->post_day( $attrs );
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 * @throws Exception
	 */
	public function post_date( $attrs ) {
		global $wp_version;
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(
				'format' => get_option( 'date_format' ),
			),
			$attrs,
			'post_date'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Post':
				$post = Gofer_SEO_Context::get_object( 'WP_Post', $this->context->context_key );
				if ( version_compare( $wp_version, '5.3.0', '>=' ) ) {
					$rtn_str = wp_date( $attrs['format'], get_post_datetime( $post ) );
				} else {
					$rtn_str = date_i18n( $attrs['format'], get_the_date( 'U', $post ) );
				}
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 * @throws Exception
	 */
	public function post_date_modified( $attrs ) {
		global $wp_version;
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(
				'format' => get_option( 'date_format' ),
			),
			$attrs,
			'post_date_modified'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Post':
				$post = Gofer_SEO_Context::get_object( 'WP_Post', $this->context->context_key );
				if ( version_compare( $wp_version, '5.3.0', '>=' ) ) {
					$rtn_str = wp_date( $attrs['format'], get_post_datetime( $post, 'modified' ) );
				} else {
					$rtn_str = date_i18n( $attrs['format'], get_the_modified_date( 'U', $post ) );
				}
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 * @throws Exception
	 */
	public function post_year( $attrs ) {
		global $wp_version;
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'post_year'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Post':
				$post = Gofer_SEO_Context::get_object( 'WP_Post', $this->context->context_key );
				if ( version_compare( $wp_version, '5.3.0', '>=' ) ) {
					$rtn_str = wp_date( 'Y', get_post_datetime( $post ) );
				} else {
					$rtn_str = date_i18n( 'Y', get_the_date( 'U', $post ) );
				}
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 * @throws Exception
	 */
	public function post_month( $attrs ) {
		global $wp_version;
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'post_month'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Post':
				$post = Gofer_SEO_Context::get_object( 'WP_Post', $this->context->context_key );
				if ( version_compare( $wp_version, '5.3.0', '>=' ) ) {
					$rtn_str = wp_date( 'F', get_post_datetime( $post ) );
				} else {
					$rtn_str = date_i18n( 'F', get_the_date( 'U', $post ) );
				}
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 * @throws Exception
	 */
	public function post_day( $attrs ) {
		global $wp_version;
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'post_day'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'WP_Post':
				$post = Gofer_SEO_Context::get_object( 'WP_Post', $this->context->context_key );
				if ( version_compare( $wp_version, '5.3.0', '>=' ) ) {
					$rtn_str = wp_date( 'j', get_post_datetime( $post ) );
				} else {
					$rtn_str = date_i18n( 'j', get_the_date( 'U', $post ) );
				}
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 * @throws Exception
	 */
	public function current_date( $attrs ) {
		global $wp_version;
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(
				'format' => get_option( 'date_format' ),
			),
			$attrs,
			'current_date'
		);

		if ( version_compare( $wp_version, '5.3.0', '>=' ) ) {
			$rtn_str = wp_date( $attrs['format'], time() );
		} else {
			$rtn_str = date_i18n( $attrs['format'], time() );
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 * @throws Exception
	 */
	public function current_year( $attrs ) {
		global $wp_version;
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'current_year'
		);

		if ( version_compare( $wp_version, '5.3.0', '>=' ) ) {
			$rtn_str = wp_date( 'Y', time() );
		} else {
			$rtn_str = date_i18n( 'Y', time() );
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 * @throws Exception
	 */
	public function current_month( $attrs ) {
		global $wp_version;
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'current_month'
		);

		if ( version_compare( $wp_version, '5.3.0', '>=' ) ) {
			$rtn_str = wp_date( 'F', time() );
		} else {
			$rtn_str = date_i18n( 'F', time() );
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 * @throws Exception
	 */
	public function current_day( $attrs ) {
		global $wp_version;
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'current_day'
		);

		if ( version_compare( $wp_version, '5.3.0', '>=' ) ) {
			$rtn_str = wp_date( 'j', time() );
		} else {
			$rtn_str = date_i18n( 'j', time() );
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function search_value( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'search_value'
		);
		$rtn_str = '';

		switch ( $this->context->context_type ) {
			case 'var_search':
				global $s;
				$rtn_str = esc_attr( stripslashes( $s ) );
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function request_uri( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'search_value'
		);
		$rtn_str = '';

		if ( is_404() && isset( $_SERVER['REQUEST_URI'] ) ) {
			$rtn_str = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function request_words( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'request_words'
		);
		$rtn_str = '';

		if ( is_404() && isset( $_SERVER['REQUEST_URI'] ) ) {
			$rtn_str = preg_replace(
				'/(http|https)?(www\.)?(\.html|\.htm|[^\w\d])/',
				' ',
				esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) )
			);
			$rtn_str = Gofer_SEO_PHP_Functions::ucwords( trim( $rtn_str ) );
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function page( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'page'
		);
		$rtn_str = '';

		if ( is_paged() || 1 < gofer_seo_the_page_number() ) {
			global $paged;
			$page = get_query_var( 'page' );
			if ( $paged > $page ) {
				$page = $paged;
			}

			$rtn_str = $page;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function pages( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(),
			$attrs,
			'pages'
		);
		$rtn_str = '';

		if ( is_paged() || 1 < gofer_seo_the_page_number() ) {
			global $numpages;
			$rtn_str = '1';
			$pages = gofer_seo_the_pages_number();
			if ( 0 < $pages ) {
				$rtn_str = strval( $pages );
			}
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function meta( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(
				'key' => '',
			),
			$attrs,
			'meta'
		);
		$rtn_str = '';

		if ( empty( $attrs['key'] ) ) {
			return $rtn_str;
		}

		switch ( $this->context->context_type ) {
			case 'WP_Site':
				$rtn_str = $this->site_meta( $attrs );
				break;

			case 'WP_Post':
				$rtn_str = $this->post_meta( $attrs );
				break;

			case 'WP_Term':
				$rtn_str = $this->term_meta( $attrs );
				break;

			case 'WP_User':
				$rtn_str = $this->user_meta( $attrs );
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function site_meta( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(
				'key' => '',
			),
			$attrs,
			'site_meta'
		);
		$rtn_str = '';

		if ( empty( $attrs['key'] ) ) {
			return $rtn_str;
		}

		switch ( $this->context->context_type ) {
			case 'WP_Site':
				$result = get_site_meta( $this->context->context_key, $attrs['key'], true );
				if ( false !== $result ) {
					$rtn_str = $result;
				}
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function post_meta( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(
				'key' => '',
			),
			$attrs,
			'post_meta'
		);
		$rtn_str = '';

		if ( empty( $attrs['key'] ) ) {
			return $rtn_str;
		}

		switch ( $this->context->context_type ) {
			case 'WP_Post':
				$result = get_post_meta( $this->context->context_key, $attrs['key'], true );
				if ( false !== $result ) {
					$rtn_str = $result;
				}
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function term_meta( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(
				'key' => '',
			),
			$attrs,
			'term_meta'
		);
		$rtn_str = '';

		if ( empty( $attrs['key'] ) ) {
			return $rtn_str;
		}

		switch ( $this->context->context_type ) {
			case 'WP_Term':
				$result = get_term_meta( $this->context->context_key, $attrs['key'], true );
				if ( false !== $result ) {
					$rtn_str = $result;
				}
				break;
		}

		return $rtn_str;
	}

	/**
	 * Shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function user_meta( $attrs ) {
		$attrs = is_array( $attrs ) ? $attrs : array();
		$attrs = shortcode_atts(
			array(
				'key' => '',
			),
			$attrs,
			'user_meta'
		);
		$rtn_str = '';

		if ( empty( $attrs['key'] ) ) {
			return $rtn_str;
		}

		switch ( $this->context->context_type ) {
			case 'WP_Post':
				$post = $this->context::get_object( 'WP_Post', $this->context->context_key );

				$result = get_user_meta( $post->post_author, $attrs['key'], true );
				if ( false !== $result ) {
					$rtn_str = $result;
				}
				break;

			case 'WP_User':
				$result = get_user_meta( $this->context->context_key, $attrs['key'], true );
				if ( false !== $result ) {
					$rtn_str = $result;
				}
				break;
		}

		return $rtn_str;
	}

}
