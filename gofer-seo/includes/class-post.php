<?php
/**
 * Data Object: Post.
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

/**
 * Class Gofer_SEO_Post.
 *
 * @since 1.0.0
 */
class Gofer_SEO_Post {
	// Essential WP Data.
	/**
	 * Post ID.
	 *
	 * @since 1.0.0
	 *
	 * @var int $id
	 */
	public $id = 0;

	/**
	 * Post Slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string $slug
	 */
	public $slug = '';

	/**
	 * Post Title.
	 *
	 * @since 1.0.0
	 *
	 * @var string $title
	 */
	public $title = '';

	/**
	 * WP Post Object.
	 *
	 * @since 1.0.0
	 *
	 * @var WP_Post $post
	 */
	public $post;

	/**
	 * Meta Data.
	 *
	 * Saved plugin settings.
	 *
	 * @since 1.0.0
	 *
	 * @var array|mixed
	 */
	public $meta = array();

	/**
	 * Typesetter.
	 *
	 * @since 1.0.0
	 *
	 * @var Gofer_SEO_Typesetter_Data $typesetter
	 */
	private $typesetter;

	/**
	 * Image Ids => URLs.
	 *
	 * @since 1.0.0
	 *
	 * @var array $image_ids_urls
	 */
	public static $image_ids_urls;

	/**
	 * Get Instance
	 *
	 * TODO Finish function.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {
		// TODO ? Add get_instance concept with cache?
	}

	/**
	 * Gofer_SEO_Post constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param int|WP_Post $post
	 */
	public function __construct( $post ) {
		if ( $post instanceof WP_Post ) {
			$this->post = $post;
		} elseif ( is_numeric( $post ) ) {
			$this->post = WP_Post::get_instance( $post );
		}

		// Modules
		add_filter( 'gofer_seo_post_modules_typeset', array( $this, 'typeset_module_general' ) );
		add_filter( 'gofer_seo_post_modules_typeset', array( $this, 'typeset_module_social_media' ) );
		add_filter( 'gofer_seo_post_modules_typeset', array( $this, 'typeset_module_sitemap' ) );

		$this->typesetter = new Gofer_SEO_Typesetter_Data();
		if ( $this->post instanceof WP_Post ) {
			$this->id    = $this->post->ID;
			$this->slug  = $this->post->post_name;
			$this->title = $this->post->post_title;
		}

		$this->meta = $this->typesetter->validate_values_with_typeset( $this->get_meta(), $this->get_meta_typesets() );
	}

	/**
	 * Get Meta Typesets.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed|void
	 */
	private function get_meta_typesets() {
		static $typesets;
		if ( null !== $typesets ) {
			return $typesets;
		}

		$typesets = array(
			// Always store the version
			'version' => array(
				'type'  => 'string',
				'value' => GOFER_SEO_VERSION,
			),
			'modules' => array(
				'type' => 'cast',
				'cast' => apply_filters( 'gofer_seo_post_modules_typeset', array() ),
			),
		);

		$typesets = apply_filters( 'gofer_seo_post_typeset', $typesets );
		return $typesets;
	}

	/* **_________________*********************************************************************************************/
	/* _/ Typeset Filters \___________________________________________________________________________________________*/

	/**
	 * Module Typeset - General.
	 *
	 * TODO Move to module?
	 * @since 1.0.0
	 *
	 * @param array $typesets
	 * @return array
	 */
	public function typeset_module_general( $typesets ) {
		if ( ! isset( $typesets['general'] ) ) {
			$typesets['general'] = array();
		}

		$typeset = array(
			'title'                => array(
				'type'  => 'string',
				'value' => '',
			),
			'description'          => array(
				'type'  => 'string',
				'value' => '',
			),
			'keywords'             => array(
				'type'  => 'string',
				'value' => '',
			),
			'custom_link'          => array(
				'type'  => 'string',
				'value' => '',
			),
			'enable_noindex'       => array(
				'type'  => 'int',
				'value' => -1,
			),
			'enable_nofollow'      => array(
				'type'  => 'int',
				'value' => -1,
			),
			'disable_analytics'    => array(
				'type'  => 'bool',
				'value' => false,
			),
			'enable_force_disable' => array(
				'type'  => 'bool',
				'value' => false,
			),
		);

		$typesets['general'] = array_replace_recursive(
			$typesets['general'],
			array( 'cast' => $typeset )
		);

		return $typesets;
	}

	/**
	 * Module Typeset - Social Media.
	 *
	 * @since 1.0.0
	 *
	 * @param array $typesets
	 * @return array
	 */
	public function typeset_module_social_media( $typesets ) {
		if ( ! isset( $typesets['social_media'] ) ) {
			$typesets['social_media'] = array();
		}

		$typeset = array(
			'title'        => array(
				'type'  => 'string',
				'value' => '',
			),
			'description'  => array(
				'type'  => 'string',
				'value' => '',
			),
			'keywords'     => array(
				'type'  => 'string',
				'value' => '',
			),
			'enable_image' => array(
				'type'  => 'bool',
				'value' => false,
			),
			'image'        => array(
				'type'  => array( 'int', 'string' ),
				'value' => '',
			),
			'image_width'  => array(
				'type'  => 'int',
				'value' => 0,
			),
			'image_height' => array(
				'type'  => 'int',
				'value' => 0,
			),
			'video'        => array(
				'type'  => array( 'int', 'string' ),
				'value' => '',
			),
			'video_width'  => array(
				'type'  => 'int',
				'value' => 0,
			),
			'video_height' => array(
				'type'  => 'int',
				'value' => 0,
			),
			'facebook'     => array(
				'type' => 'cast',
				'cast' => array(
					'object_type'     => array(
						'type'  => 'string',
						'value' => 'default',
					),
					'article_section' => array(
						'type'  => 'string',
						'value' => '',
					),
					//'debug'       => array(),
				),
			),
			'twitter'      => array(
				'type' => 'cast',
				'cast' => array(
					'card_type' => array(
						'type'  => 'string',
						'value' => 'summary',
					),
					'image'     => array(
						'type'  => 'string',
						'value' => '',
					),
				),
			),
		);

		$typesets['social_media'] = array_replace_recursive(
			$typesets['social_media'],
			array( 'cast' => $typeset )
		);

		return $typesets;
	}

	/**
	 * Module Typeset - Sitemap.
	 *
	 * @since 1.0.0
	 *
	 * @param array $typesets
	 * @return array
	 */
	public function typeset_module_sitemap( $typesets ) {
		if ( ! isset( $typesets['sitemap'] ) ) {
			$typesets['sitemap'] = array();
		}

		$typeset = array(
			'priority'       => array(
				'type'  => 'int',
				'value' => -1,
			),
			'frequency'      => array(
				'type'  => 'string',
				'value' => 'default',
			),
			'enable_exclude' => array(
				'type'  => 'bool',
				'value' => false,
			),
		);

		$typesets['sitemap'] = array_replace_recursive(
			$typesets['sitemap'],
			array( 'cast' => $typeset )
		);

		return $typesets;
	}

	/* ****************************************************************************************************************/

	/**
	 * Get Meta Defaults.
	 *
	 * @since 1.0.0
	 *
	 * @param string $action Action determines to get the default values, or fill in dynamic values.
	 *                       Accepts 'default', and 'fill'
	 * @return array|mixed
	 */
	public function get_meta_defaults( $action = 'default' ) {
		$typesets = $this->get_meta_typesets();
		return $this->typesetter->validate_values_with_typeset(
			$this->typesetter->get_typesets_default_values( $typesets, $action ),
			$typesets
		);
	}

	/**
	 * Get Meta.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_meta() {
		$typesets = $this->get_meta_typesets();
		$defaults = $this->get_meta_defaults( 'fill' );

		$meta = $this->get_meta_recursive( $this->id, $typesets, '_gofer_seo' );

		$meta = array_replace_recursive( $defaults, $meta );
		$meta = array_replace( $defaults, $meta );

		return $meta;
	}

	/**
	 * Get Meta - Recursive method.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id
	 * @param array  $typesets
	 * @param string $prefix
	 * @return array
	 */
	private function get_meta_recursive( $id, $typesets, $prefix = '' ) {
		if ( ! empty( $prefix ) ) {
			$prefix .= '_';
		}

		$values = array();
		foreach ( $typesets as $typeset_key => $typeset ) {
			$typeset = $this->typesetter->validate_typeset( $typeset );
			$name = $prefix . $typeset_key;
			if ( in_array( 'cast', $typeset['type'], true ) ) {
				$values[ $typeset_key ] = $this->get_meta_recursive( $id, $typeset['cast'], $name );
			} elseif ( in_array( 'cast_dynamic', $typeset['type'], true ) ) {
				// Dynamics are single meta items.
				$dynamic_meta_values = array();
				if ( metadata_exists( 'post', $id, $name ) ) {
					$dynamic_meta_values = get_post_meta( $id, $name, true );
				}

				foreach ( $typeset['cast_dynamic'] as $k2_typeset_key => $v2_typeset ) {
					if ( ! isset( $dynamic_meta_values[ $k2_typeset_key ] ) ) {
						$dynamic_meta_values[ $k2_typeset_key ] = $v2_typeset['value'];
					}
				}

				$values[ $typeset_key ] = $dynamic_meta_values;
			} else {
				if ( metadata_exists( 'post', $id, $name ) ) {
					$values[ $typeset_key ] = get_post_meta( $id, $name, true );

					if ( in_array( 'bool', $typeset['type'], true ) || in_array( 'boolean', $typeset['type'], true ) ) {
						if ( '0' === $values[ $typeset_key ] ) {
							$values[ $typeset_key ] = false;
						} elseif ( '1' === $values[ $typeset_key ] ) {
							$values[ $typeset_key ] = true;
						}
					}
				}
			}
		}

		return $values;
	}

	/**
	 * Update Meta.
	 *
	 * @since 1.0.0
	 *
	 * @param array $meta
	 * @param array $args
	 * @return bool[]|bool
	 */
	public function update_meta( $meta = array(), $args = array() ) {
		if ( empty( $meta ) ) {
			$meta = $this->meta;
		} elseif ( ! is_array( $meta ) ) {
			return false;
		}

		$typesets        = $this->get_meta_typesets();
		$meta['version'] = GOFER_SEO_VERSION;
		$meta            = $this->typesetter->validate_values_with_typeset( $meta, $this->get_meta_typesets() );

		$args_default = array(
			'update_object' => true,
		);
		$args = wp_parse_args( $args, $args_default );

		$results = $this->update_meta_recursive( $this->id, $meta, $typesets, '_gofer_seo' );

		return $results;
	}

	/**
	 * Update Meta - Recursive method.
	 *
	 * @since 1.0.0
	 *
	 * @param string      $id
	 * @param array|mixed $values
	 * @param array       $typesets
	 * @param string      $prefix
	 * @return array
	 */
	private function update_meta_recursive( $id, $values, $typesets, $prefix = '' ) {
		if ( ! empty( $prefix ) ) {
			$prefix .= '_';
		}

		$results = array();
		foreach ( $typesets as $typeset_key => $typeset ) {
			$typeset = $this->typesetter->validate_typeset( $typeset );
			$name = $prefix . $typeset_key;
			if ( in_array( 'cast', $typeset['type'], true ) ) {
				$results[ $typeset_key ] = $this->update_meta_recursive( $id, $values[ $typeset_key ], $typeset['cast'], $name );
			} elseif ( in_array( 'cast_dynamic', $typeset['type'], true ) ) {
				// Save dynamic types to a single meta entry.
				// Fill missing defaults, and leave 'inactive' settings alone.
				$dynamic_meta_values = $values[ $typeset_key ];
				foreach ( $dynamic_meta_values as $k2_item_slug => $v2_item_value ) {
					foreach ( $typeset['cast_dynamic'] as $k3_typeset_key => $v3_typeset ) {
						if ( ! isset( $v2_item_value[ $k3_typeset_key ] ) ) {
							$dynamic_meta_values[ $k2_item_slug ][ $k3_typeset_key ] = $v3_typeset['value'];
						}
					}
				}

				$results[ $typeset_key ] = update_post_meta( $id, $name, $dynamic_meta_values );
			} else {
				$results[ $typeset_key ] = update_post_meta( $id, $name, $values[ $typeset_key ] );
			}
		}

		return $results;
	}

	/**
	 * Delete Meta.
	 *
	 * @since 1.0.0
	 */
	public function delete_meta() {
		$typesets = $this->get_meta_typesets();

		$results = $this->delete_meta_recursive( $this->id, $typesets, '_gofer_seo' );
	}

	/**
	 * Delete Meta - Recursive method.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id
	 * @param array  $typesets
	 * @param string $prefix
	 * @return array
	 */
	private function delete_meta_recursive( $id, $typesets, $prefix = '' ) {
		if ( ! empty( $prefix ) ) {
			$prefix .= '_';
		}

		$results = array();
		foreach ( $typesets as $typeset_key => $typeset ) {
			$typeset = $this->typesetter->validate_typeset( $typeset );
			$name = $prefix . $typeset_key;


			if (
					in_array( 'cast', $typeset['type'], true ) ||
					in_array( 'tabs', $typeset['type'], true ) ||
					in_array( 'tab', $typeset['type'], true )
			) {
				$results[ $typeset_key ] = $this->delete_meta_recursive( $id, $typeset['cast'], $prefix );
			} elseif (
					in_array( 'cast_dynamic', $typeset['type'], true ) ||
					in_array( 'add-fields-list', $typeset['type'], true )
			) {
				$results[ $typeset_key ] = delete_post_meta( $id, $name );
			} else {
				$results[ $typeset_key ] = delete_post_meta( $id, $name );
			}
		}

		return $results;
	}

	/**
	 * Get Images.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_images() {
		$images = array();
		$image_defaults = array(
			'id'        => 0,
			'url'       => '',
			'caption'   => '',
			'title'     => '',
			'width'     => 0,
			'height'    => 0,
			'mime_type' => '',
		);

		if ( 'attachment' === $this->post->post_type ) {
			if ( false !== strpos( $this->post->post_mime_type, 'image/' ) ) {
				$image_data = image_get_intermediate_size( $this->id );
				if ( ! $image_data ) {
					$image_data = image_get_intermediate_size( $this->id, 'full' );
				}

				if ( $image_data ) {
					$images[] = array(
						'id'        => $this->id,
						'url'       => gofer_seo_sanitize_sitemap_url( $image_data['url'] ),
						'caption'   => wp_get_attachment_caption( $this->id ),
						'title'     => get_the_title( $this->id ),
						'width'     => $image_data['width'],
						'height'    => $image_data['height'],
						'mime_type' => $image_data['mime-type'],
					);
				}
			}

			return $images;
		}

		static $post_thumbnails;
		$post_image_ids   = array();
		$transient_update = false;

		// Set Image IDs w/ URLs.
		if ( is_null( self::$image_ids_urls ) ) {
			// Get Transient/Cache data.
			if ( is_multisite() ) {
				self::$image_ids_urls = get_site_transient( 'gofer_seo_multisite_attachment_ids_urls' );
			} else {
				self::$image_ids_urls = get_transient( 'gofer_seo_attachment_ids_urls' );
			}

			// Set default if no data exists.
			if ( false === self::$image_ids_urls ) {
				self::$image_ids_urls = array();
			}
		}

		if ( is_null( $post_thumbnails ) ) {
			global $wpdb;

			$post_thumbnails = wp_cache_get( 'gofer_seo_post_thumbnails' );
			if ( false === $post_thumbnails ) {
				$post_thumbnails = $wpdb->get_results( "SELECT post_ID, meta_value FROM $wpdb->postmeta WHERE meta_key = '_thumbnail_id'", ARRAY_A );
				wp_cache_set( 'gofer_seo_post_thumbnails', $post_thumbnails, '', DAY_IN_SECONDS );
			}

			if ( $post_thumbnails ) {
				$post_thumbnails = array_combine(
					wp_list_pluck( $post_thumbnails, 'post_ID' ),
					wp_list_pluck( $post_thumbnails, 'meta_value' )
				);
			}
		}
		if ( isset( $post_thumbnails[ $this->post->ID ] ) ) {
			array_push( $post_image_ids, $post_thumbnails[ $this->id ] );
		}


		// Check images galleries in the content.
		// DO NOT run the_content filter here as it might cause issues with other shortcodes.
		if ( has_shortcode( $this->post->post_content, 'gallery' ) ) {
			// Get the default WP gallery images.
			$galleries = get_post_galleries( $this->post, false );
			if ( ! empty( $galleries ) ) {
				foreach ( $galleries as $gallery ) {
					$gallery_ids = explode( ',', $gallery['ids'] );

					if ( ! empty( $gallery_ids ) ) {
						foreach ( $gallery_ids as $image_id ) {
							// Skip if invalid id.
							if ( ! is_numeric( $image_id ) ) {
								continue;
							}
							$image_id = intval( $image_id );
							array_push( $post_image_ids, $image_id );
						}
					}
				}
			}

			/**
			 * @link https://hayashikejinan.com/wp-content/uploads/jetpack_api/classes/Jetpack_PostImages.html
			 */
			if ( class_exists( 'Jetpack_PostImages' ) ) {
				// Get the jetpack gallery images.
				$jetpack = Jetpack_PostImages::get_images( $this->id );
				if ( $jetpack ) {
					foreach ( $jetpack as $jetpack_image ) {
						$images[] = array(
							'id'       => $this->id,
							'url'      => $jetpack_image['src'],
							'width'    => $jetpack_image['src_width'],
							'height'   => $jetpack_image['src_height'],
							'caption'  => wp_get_attachment_caption( $image_id ),
							'title'    => get_the_title( $image_id ),
							'alt_text' => $jetpack_image['alt_text'],
						);
					}
				}
			}
		}

		// Check WooCommerce product gallery.
		if ( gofer_seo_is_woocommerce_active() ) {
			$wc_image_ids = get_post_meta( $this->id, '_product_image_gallery', true );
			if ( ! empty( $woo_images ) ) {
				$wc_image_ids = array_filter( explode( ',', $wc_image_ids ) );
				foreach ( $wc_image_ids as $image_id ) {
					if ( is_numeric( $image_id ) ) {
						$image_id = intval( $image_id );
						array_push( $post_image_ids, $image_id );
					}
				}
			}
		}

		$post_image_urls = $this->parse_content_for_image_urls( $this->post->post_content );
		if ( ! empty( $post_image_urls ) ) {
			// Remove any invalid/empty images.
			$post_image_urls = array_filter( $post_image_urls, 'gofer_seo_is_url_valid' );

			// If possible, get ID from URL, and store the post's attachment ID => URL value.
			// This is to base the attachment query on the ID instead of the URL; which is less SQL intense.
			foreach ( $post_image_urls as $k1_index => $v1_image_url ) {
				$v1_image_url                 = gofer_seo_sanitize_sitemap_url( $v1_image_url );
				$post_image_urls[ $k1_index ] = $v1_image_url;
				$attachment_id                = Gofer_SEO_Methods::attachment_url_to_postid( $v1_image_url );

				if ( $attachment_id ) {
					if ( ! isset( self::$image_ids_urls[ $attachment_id ] ) ) {
						// Use transient/cache data.
						self::$image_ids_urls[ $attachment_id ] = array( $v1_image_url );

						$transient_update = true;
					} else {
						// If transient/cache data is already set, and URL is not already stored.
						if ( ! in_array( $v1_image_url, self::$image_ids_urls[ $attachment_id ], true ) ) {
							self::$image_ids_urls[ $attachment_id ][] = $v1_image_url;

							$transient_update = true;
						}
					}

					// Store and use ID instead.
					array_push( $post_image_ids, $attachment_id );
					unset( $post_image_urls[ $k1_index ] );
				}
			}
		}

		// Site's Images.
		if ( $post_image_ids ) {
			// Filter out duplicates.
			$post_image_ids = array_unique( $post_image_ids );

			foreach ( $post_image_ids as $v1_image_id ) {
				// Set base URL to display later in this instance, or later (transient/cache) instances.
				// Converting ID from URL can also be heavy on memory & time.
				if ( ! isset( self::$image_ids_urls[ $v1_image_id ] ) ) {
					// Sets any remaining post image IDs that weren't converted from URL.
					self::$image_ids_urls[ $v1_image_id ] = array(
						'base_url' => gofer_seo_sanitize_sitemap_url( wp_get_attachment_url( $v1_image_id ) ),
					);

					$transient_update = true;
				} else {
					if ( empty( self::$image_ids_urls[ $v1_image_id ]['base_url'] ) ) {
						self::$image_ids_urls[ $v1_image_id ]['base_url'] = gofer_seo_sanitize_sitemap_url( wp_get_attachment_url( $v1_image_id ) );

						$transient_update = true;
					}
				}

				$image_data = image_get_intermediate_size( $v1_image_id );
				if ( ! $image_data ) {
					$image_data = image_get_intermediate_size( $v1_image_id, 'full' );
				}

				$image_width     = '';
				$image_height    = '';
				$image_mime_type = '';
				if ( $image_data ) {
					$image_width     = $image_data['width'];
					$image_height    = $image_data['height'];
					$image_mime_type = $image_data['mime-type'];
				}

				// Set return variable for image data/attributes.
				$images[] = array(
					'id'        => $v1_image_id,
					'url'       => self::$image_ids_urls[ $v1_image_id ]['base_url'],
					'caption'   => wp_get_attachment_caption( $v1_image_id ),
					'title'     => get_the_title( $v1_image_id ),
					'width'     => $image_width,
					'height'    => $image_height,
					'mime_type' => $image_mime_type,
				);
			}
		}

		// External/Custom images remaining.
		if ( ! empty( $post_image_urls ) ) {
			foreach ( $post_image_urls as $v1_image_url ) {
				$images[] = wp_parse_args( array( 'url' => $v1_image_url ), $image_defaults );
			}
		}

		if ( $transient_update ) {
			add_action( 'shutdown', array( $this, 'set_transient_attachment_ids_urls' ) );
		}

		return $images;
	}

	/**
	 * Set Transient Attachment IDs => URLS
	 *
	 * Set Transient for Image IDs => URLs
	 *
	 * @since 1.0.0
	 */
	public function set_transient_attachment_ids_urls() {
		if ( is_multisite() ) {
			set_site_transient( 'gofer_seo_multisite_attachment_ids_urls', self::$image_ids_urls, DAY_IN_SECONDS );
		} else {
			set_transient( 'gofer_seo_attachment_ids_urls', self::$image_ids_urls, DAY_IN_SECONDS );
		}
	}

	/**
	 * Parse Content for Images
	 *
	 * Parse the post for images.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content the post content.
	 * @return array
	 */
	public function parse_content_for_image_urls( $content ) {
		// These tags should be WITHOUT trailing space because some plugins such as the nextgen gallery put newlines immediately after <img.
		$total = substr_count( $content, '<img' ) + substr_count( $content, '<IMG' );
		// no images found.
		if ( 0 === $total ) {
			return array();
		}

		$image_urls = array();

		if ( class_exists( 'DOMDocument' ) ) {
			$dom = new domDocument();

			// Non-compliant HTML might give errors, so ignore them.
			libxml_use_internal_errors( true );
			$dom->loadHTML( $content );
			libxml_clear_errors();

			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$dom->preserveWhiteSpace = false;

			$matches = $dom->getElementsByTagName( 'img' );
			foreach ( $matches as $match ) {
				$image_urls[] = $match->getAttribute( 'src' );
			}
		} else {
			// Fall back to regex, but also report an error.
			static $img_err_msg;
			if ( ! isset( $img_err_msg ) ) {
				// Log this error message only once, not per post.
				$img_err_msg = true;
				new Gofer_SEO_Error( 'gofer_seo_post_parse_images_dom_missing', 'DOMDocument not found; using REGEX' );
			}
			preg_match_all( '/<img.*src=([\'"])?(.*?)\\1/', $content, $matches );
			if ( $matches && isset( $matches[2] ) ) {
				$image_urls = array_merge( $image_urls, $matches[2] );
			}
		}

		return $image_urls;
	}

}
