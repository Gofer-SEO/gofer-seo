<?php
/**
 * Data Object: User.
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

/**
 * Class Gofer_SEO_User.
 *
 * @since 1.0.0
 */
class Gofer_SEO_User {

	/**
	 * User ID.
	 *
	 * @since 1.0.0
	 *
	 * @var int $id
	 */
	public $id = 0;

	/**
	 * WP User Object.
	 *
	 * @since 1.0.0
	 *
	 * @var WP_User $user
	 */
	public $user;

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
	 * Gofer_SEO_User constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param int|WP_User $user
	 */
	public function __construct( $user ) {
		if ( $user instanceof WP_User ) {
			$this->user = $user;
		} elseif ( is_numeric( $user ) ) {
			$this->user = new WP_User( $user );
		}

		// Modules
		add_filter( 'gofer_seo_user_modules_typeset', array( $this, 'typeset_module_general' ) );
		//add_filter( 'gofer_seo_user_modules_typeset', array( $this, 'typeset_module_social_media' ) );
		add_filter( 'gofer_seo_user_modules_typeset', array( $this, 'typeset_module_sitemap' ) );

		$this->typesetter = new Gofer_SEO_Typesetter_Data();
		if ( $this->user instanceof WP_User ) {
			$this->id    = $this->user->ID;
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
				'cast' => apply_filters( 'gofer_seo_user_modules_typeset', array() ),
			),
		);

		$typesets = apply_filters( 'gofer_seo_user_typeset', $typesets );
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
			'enable_noindex'       => array(
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

		$typeset = array();

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
				if ( metadata_exists( 'user', $id, $name ) ) {
					$dynamic_meta_values = get_user_meta( $id, $name, true );
				}

				foreach ( $typeset['cast_dynamic'] as $k2_typeset_key => $v2_typeset ) {
					if ( ! isset( $dynamic_meta_values[ $k2_typeset_key ] ) ) {
						$dynamic_meta_values[ $k2_typeset_key ] = $v2_typeset['value'];
					}
				}

				$values[ $typeset_key ] = $dynamic_meta_values;
			} else {
				if ( metadata_exists( 'user', $id, $name ) ) {
					$values[ $typeset_key ] = get_user_meta( $id, $name, true );

					if ( in_array( 'bool', $typeset['type'], true ) ) {
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

				$results[ $typeset_key ] = update_user_meta( $id, $name, $dynamic_meta_values );
			} else {
				$results[ $typeset_key ] = update_user_meta( $id, $name, $values[ $typeset_key ] );
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
				in_array( 'cast', $typeset['type'], true )
			) {
				$results[ $typeset_key ] = $this->delete_meta_recursive( $id, $typeset['cast'], $prefix );
			} elseif (
				in_array( 'cast_dynamic', $typeset['type'], true )
			) {
				$results[ $typeset_key ] = delete_user_meta( $id, $name );
			} else {
				$results[ $typeset_key ] = delete_user_meta( $id, $name );
			}
		}

		return $results;
	}
}
