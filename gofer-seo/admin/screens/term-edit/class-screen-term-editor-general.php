<?php
/**
 * Admin Screen: Term Editor - General
 *
 * @package Gofer SEO
 * @since   1.0.0
 */

/**
 * Class Gofer_SEO_Screen_Term_Editor_General
 *
 * @since 1.0.0
 */
class Gofer_SEO_Screen_Term_Editor_General extends Gofer_SEO_Screen_Term_Editor {

	/**
	 * Action - Current Screen
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Screen $current_screen
	 */
	public function current_screen( $current_screen ) {
		parent::current_screen( $current_screen );
		if ( ! in_array( $current_screen->taxonomy, $this->get_active_taxonomies(), true ) ) {
			return;
		}

		// TODO Finish Adding quick_editor.
		// add_filter( 'manage_edit-' . $current_screen->taxonomy . '_columns', array( $this, 'taxonomy_columns' ), 10, 1 );
		// add_filter( 'manage_' . $current_screen->taxonomy . '_custom_column', array( $this, 'taxonomy_custom_columns' ), 10, 3 );
	}

	/**
	 * TODO Finish Adding quick_editor.
	 * @param $columns
	 * @return mixed
	 */
	public function taxonomy_columns( $columns ) {
		$columns['gofer_seo_general_title']       = __( 'SEO Title', 'gofer-seo' );
		$columns['gofer_seo_general_description'] = __( 'SEO Description', 'gofer-seo' );
		$columns['gofer_seo_general_keywords']    = __( 'SEO Keywords', 'gofer-seo' );

		return $columns;
	}

	/**
	 * TODO Finish Adding quick_editor.
	 * @param $out
	 * @param $column_name
	 * @param $id
	 * @return mixed
	 */
	public function taxonomy_custom_columns( $out, $column_name, $id ) {
		switch ( $column_name ) {
			case 'gofer_seo_general_title':
				echo esc_html( get_term_meta( $id, '_gofer_seo_general_title', true ) );
				break;
			case 'gofer_seo_general_description':
				echo esc_html( get_term_meta( $id, '_gofer_seo_general_description', true ) );
				break;
			case 'gofer_seo_general_keywords':
				echo esc_html( get_term_meta( $id, '_gofer_seo_general_keywords', true ) );
				break;
		}

		return $out;
	}

	/**
	 * Get Active Taxonomies/Screens.
	 *
	 * This checks if the taxonomy is enabled, and if show meta-box is enabled, and
	 * returns an array of taxonomy slugs.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_active_taxonomies() {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		$active_taxonomies = array_filter( $gofer_seo_options->options['modules']['general']['enable_taxonomies'] );
		$active_taxonomies = array_keys( $active_taxonomies );

		foreach ( $active_taxonomies as $index => $taxonomy ) {
			if ( false === $gofer_seo_options->options['modules']['general']['taxonomy_settings'][ $taxonomy ]['enable_editor_meta_box'] ) {
				unset( $active_taxonomies[ $index ] );
			}
		}
		$active_taxonomies = array_values( $active_taxonomies );

		return $active_taxonomies;
	}

	/**
	 * The Input Typesets (Params/Configuration)
	 *
	 * @since 1.0.0
	 *
	 * @see \Gofer_SEO_Screen_Edit::get_input_typesets()
	 *
	 * @param array[] For details, see `\Gofer_SEO_Screen_Edit::get_input_typesets()`.
	 * @return array[] For details, see `\Gofer_SEO_Screen_Edit::get_input_typesets()`.
	 */
	public function get_input_typesets( $input_typesets ) {
		global $taxonomy;
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		$default_noindex_value = '';
		if ( isset( $gofer_seo_options->options['modules']['general']['taxonomy_settings'][ $taxonomy ]['enable_noindex'] ) ) {
			if ( false === $gofer_seo_options->options['modules']['general']['taxonomy_settings'][ $taxonomy ]['enable_noindex'] ) {
				$default_noindex_value = ' (disabled)';
			} elseif ( true === $gofer_seo_options->options['modules']['general']['taxonomy_settings'][ $taxonomy ]['enable_noindex'] ) {
				$default_noindex_value = ' (enabled)';
			}
		}
		$default_nofollow_value = '';
		if ( isset( $gofer_seo_options->options['modules']['general']['taxonomy_settings'][ $taxonomy ]['enable_nofollow'] ) ) {
			if ( false === $gofer_seo_options->options['modules']['general']['taxonomy_settings'][ $taxonomy ]['enable_nofollow'] ) {
				$default_nofollow_value = ' (disabled)';
			} elseif ( true === $gofer_seo_options->options['modules']['general']['taxonomy_settings'][ $taxonomy ]['enable_nofollow'] ) {
				$default_nofollow_value = ' (enabled)';
			}
		}


		$enabled_taxonomies = $this->get_active_taxonomies();
		$module_typeset = array(
			'title' => __( 'General', 'gofer-seo' ),
			'type'  => 'tab',
			'wrap'  => array(
				'title'                => array(
					'title'      => __( 'Title', 'gofer-seo' ),
					'input_type' => 'text',
				),
				'description'          => array(
					'title'      => __( 'Description', 'gofer-seo' ),
					'input_type' => 'textarea',
					'attrs'      => array(
						'rows' => 3,
					),
				),
				'keywords'             => array(
					'title'      => __( 'Keywords', 'gofer-seo' ),
					'input_type' => 'text',
				),
				'custom_link'          => array(
					'title'      => __( 'Custom Link', 'gofer-seo' ),
					'input_type' => 'text',
				),
				'enable_noindex'       => array(
					'title' => __( 'NoIndex', 'gofer-seo' ),
					'type'  => 'select',
					'items' => array(
						-1 => __( 'Use Default', 'gofer-seo' ) . $default_noindex_value,
						0  => __( 'Disable', 'gofer-seo' ),
						1  => __( 'Enable', 'gofer-seo' ),
					),
					'esc'   => array(
						array( 'intval' ),
					),
				),
				'enable_nofollow'      => array(
					'title' => __( 'NoFollow', 'gofer-seo' ),
					'type'  => 'select',
					'items' => array(
						-1 => __( 'Use Default', 'gofer-seo' ) . $default_nofollow_value,
						0  => __( 'Disable', 'gofer-seo' ),
						1  => __( 'Enable', 'gofer-seo' ),
					),
					'esc'   => array(
						array( 'intval' ),
					),
				),
				'disable_analytics'    => array(
					'title' => __( 'Disable Analytics', 'gofer-seo' ),
					'type'  => 'checkbox',
				),
				'enable_force_disable' => array(
					'title' => __( 'Force Disable SEO', 'gofer-seo' ),
					'type'  => 'checkbox',
				),
			),
//			'conditions' => array(
//				'taxonomy' => array(
//					'operator'    => '===',
//					'right_value' => $enabled_taxonomies,
//				),
//			),
		);

		// Set Module Tabs if not yet set.
		if ( ! isset( $input_typesets['gofer_seo_modules'] ) ) {
			$input_typesets['gofer_seo_modules'] = array(
				'title'      => __( 'Modules', 'gofer-seo' ),
				'type'       => 'tabs',
				'wrap'       => array(),
				'conditions' => array(),
				'layout'     => 'input-row',
			);
		}

		if ( isset( $input_typesets['gofer_seo_modules']['wrap']['general'] ) ) {
			$input_typesets['gofer_seo_modules']['wrap']['general'] = array_replace( $input_typesets['gofer_seo_modules']['wrap']['general'], $module_typeset );
		} else {
			$input_typesets['gofer_seo_modules']['wrap']['general'] = $module_typeset;
		}

		return $input_typesets;
	}

	/**
	 * The Meta Box Typesets (Params/Configuration).
	 *
	 * @since 1.0.0
	 *
	 * @see   \Gofer_SEO_Screen_Edit::get_meta_box_typesets()
	 *
	 * @param array[] For details, see `\Gofer_SEO_Screen_Edit::get_meta_box_typesets()`.
	 * @return array[] For details, see `\Gofer_SEO_Screen_Edit::get_meta_box_typesets()`.
	 */
	public function get_meta_box_typesets( $meta_box_typesets ) {
		$enabled_taxonomies = $this->get_active_taxonomies();
		$screen_ids = array_merge( $enabled_taxonomies,
			array_map(
				function( $value ) {
					return 'edit-' . $value;
				},
				$enabled_taxonomies
			)
		);

		if ( ! isset( $meta_box_typesets['gofer_seo'] ) ) {
			$meta_box_typesets['gofer_seo'] = array(
				'title'    => __( 'Gofer SEO', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'screens'  => $screen_ids,
				'inputs'   => array(
					'gofer_seo_modules',
				),
			);
		} else {
			$meta_box_typesets['gofer_seo']['screens'] = array_replace(
				$meta_box_typesets['gofer_seo']['screens'],
				$enabled_taxonomies
			);
		}

		if ( ! in_array( 'gofer_seo_modules', $meta_box_typesets['gofer_seo']['inputs'], true ) ) {
			$meta_box_typesets['gofer_seo']['inputs'][] = 'gofer_seo_modules';
		}

		return $meta_box_typesets;
	}

	/**
	 * Get Values from Target Source.
	 *
	 * Used by child classes to return the values being edited.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed[] $values ${INPUT_SLUG}
	 * @return mixed[] ${INPUT_SLUG}
	 */
	public function get_values( $values ) {
		global $tag;

		$gofer_seo_term = new Gofer_SEO_Term( $tag );

		if ( ! isset( $values['gofer_seo_modules'] ) ) {
			$values['gofer_seo_modules'] = array();
		}

		if ( isset( $values['gofer_seo_modules']['general'] ) ) {
			$values['gofer_seo_modules']['general'] = array_replace( $values['gofer_seo_modules']['general'], $gofer_seo_term->meta['modules']['general'] );
		} elseif ( isset( $gofer_seo_term->meta['modules']['general'] ) ) {
			$values['gofer_seo_modules']['general'] = $gofer_seo_term->meta['modules']['general'];
		}

		return $values;
	}

	/**
	 * Update Values to Target Source.
	 *
	 * Used by other classes to handle operations differently.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed[] $values ${INPUT_SLUG}
	 * @return mixed[] $values ${INPUT_SLUG}
	 */
	public function update_values( $values ) {
		global $tag;

		$gofer_seo_term = new Gofer_SEO_Term( $tag );

		$gofer_seo_term->meta['modules']['general'] = array_replace_recursive(
			$gofer_seo_term->meta['modules']['general'],
			$values['gofer_seo_modules']['general']
		);

		$results = $gofer_seo_term->update_meta();

		return $values;
	}
}
