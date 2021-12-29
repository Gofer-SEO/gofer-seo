<?php
/**
 * Data Object: Term.
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

/**
 * Class Gofer_SEO_Term.
 *
 * @since 1.0.0
 */
class Gofer_SEO_Term {

	/**
	 * Term ID.
	 *
	 * @since 1.0.0
	 *
	 * @var int $id
	 */
	public $id = 0;

	/**
	 * Term Slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string $slug
	 */
	public $slug = '';

	/**
	 * Term Title.
	 *
	 * @since 1.0.0
	 *
	 * @var string $title
	 */
	public $title = '';

	/**
	 * WP Term Object.
	 *
	 * @since 1.0.0
	 *
	 * @var WP_Term $term
	 */
	public $term;

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
	 * Gofer_SEO_Term constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param int|WP_Term $term
	 */
	public function __construct( $term ) {
		if ( $term instanceof WP_Term ) {
			$this->term = $term;
		} elseif ( is_numeric( $term ) ) {
			$this->term = WP_Term::get_instance( $term );
		}

		// Modules
		add_filter( 'gofer_seo_term_modules_typeset', array( $this, 'typeset_module_general' ) );
		add_filter( 'gofer_seo_term_modules_typeset', array( $this, 'typeset_module_social_media' ) );
		add_filter( 'gofer_seo_term_modules_typeset', array( $this, 'typeset_module_sitemap' ) );

		$this->typesetter = new Gofer_SEO_Typesetter_Data();
		if ( $this->term instanceof WP_Term ) {
			$this->id    = $this->term->term_id;
			$this->slug  = $this->term->slug;
			$this->title = $this->term->name;
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
				'cast' => apply_filters( 'gofer_seo_term_modules_typeset', array() ),
			),
		);

		$typesets = apply_filters( 'gofer_seo_term_typeset', $typesets );
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
						'value' => 'article',
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
			if ( false === $typeset ) {
				continue;
			}

			$name = $prefix . $typeset_key;
			if ( in_array( 'cast', $typeset['type'], true ) ) {
				$values[ $typeset_key ] = $this->get_meta_recursive( $id, $typeset['cast'], $name );
			} elseif ( in_array( 'cast_dynamic', $typeset['type'], true ) ) {
				// Dynamics are single meta items.
				$dynamic_meta_values = array();
				if ( metadata_exists( 'term', $id, $name ) ) {
					$dynamic_meta_values = get_term_meta( $id, $name, true );
				}

				foreach ( $typeset['cast_dynamic'] as $k2_typeset_key => $v2_typeset ) {
					if ( ! isset( $dynamic_meta_values[ $k2_typeset_key ] ) ) {
						$dynamic_meta_values[ $k2_typeset_key ] = $v2_typeset['value'];
					}
				}

				$values[ $typeset_key ] = $dynamic_meta_values;
			} else {
				if ( metadata_exists( 'term', $id, $name ) ) {
					$values[ $typeset_key ] = get_term_meta( $id, $name, true );

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
			if ( false === $typeset ) {
				continue;
			}

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

				$results[ $typeset_key ] = update_term_meta( $id, $name, $dynamic_meta_values );
			} else {
				$results[ $typeset_key ] = update_term_meta( $id, $name, $values[ $typeset_key ] );
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

		$results = $this->delete_meta_recursive( $this->id, $typesets );
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
			if ( false === $typeset ) {
				continue;
			}

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
				$results[ $typeset_key ] = delete_term_meta( $id, $name );
			} else {
				$results[ $typeset_key ] = delete_term_meta( $id, $name );
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

		$thumbnail_id = get_term_meta( $this->id, 'thumbnail_id', true );
		if ( $thumbnail_id ) {
			$image_data = image_get_intermediate_size( $thumbnail_id );
			if ( ! $image_data ) {
				$image_data = image_get_intermediate_size( $thumbnail_id, 'full' );
			}

			$image_width     = '';
			$image_height    = '';
			$image_mime_type = '';
			if ( $image_data ) {
				$image_width     = $image_data['width'];
				$image_height    = $image_data['height'];
				$image_mime_type = $image_data['mime-type'];
			}

			$images[] = array(
				'id'        => $thumbnail_id,
				'url'       => wp_get_attachment_url( $thumbnail_id ),
				'caption'   => wp_get_attachment_caption( $thumbnail_id ),
				'title'     => get_the_title( $thumbnail_id ),
				'width'     => $image_width,
				'height'    => $image_height,
				'mime_type' => $image_mime_type,
			);
		}

		return $images;
	}
}
