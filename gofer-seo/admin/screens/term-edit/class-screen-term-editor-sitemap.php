<?php
/**
 * Admin Screen: Term Editor - Sitemap
 *
 * @package Gofer SEO
 * @since   1.0.0
 */

/**
 * Class Gofer_SEO_Screen_Term_Editor_Sitemap
 *
 * @since 1.0.0
 */
class Gofer_SEO_Screen_Term_Editor_Sitemap extends Gofer_SEO_Screen_Term_Editor {

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
		$active_taxonomies = array_filter( $gofer_seo_options->options['modules']['sitemap']['enable_taxonomies'] );
		$active_taxonomies = array_keys( $active_taxonomies );

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

		$default_frequency_value = '';
		if (
				isset( $gofer_seo_options->options['modules']['sitemap']['taxonomy_settings'][ $taxonomy ]['frequency'] ) &&
				'default' !== $gofer_seo_options->options['modules']['sitemap']['taxonomy_settings'][ $taxonomy ]['frequency']
		) {
			$default_frequency_value = $gofer_seo_options->options['modules']['sitemap']['taxonomy_settings'][ $taxonomy ]['frequency'];
			$default_frequency_value = ' (' . $default_frequency_value . ')';
		} elseif (
				isset( $gofer_seo_options->options['modules']['sitemap']['taxonomy_default_frequency'] )
		) {
			$default_frequency_value = $gofer_seo_options->options['modules']['sitemap']['taxonomy_default_frequency'];
			$default_frequency_value = ' (' . $default_frequency_value . ')';
		}

		$module_typeset = array(
			'title' => __( 'Sitemap', 'gofer-seo' ),
			'type'  => 'tab',
			'wrap'  => array(
				'priority'       => array(
					'title' => __( 'Priority', 'gofer-seo' ),
					'type'  => 'range',
					'attrs' => array(
						'min' => -1,
						'max' => 10,
					),
				),
				'frequency'      => array(
					'title' => __( 'Frequency', 'gofer-seo' ),
					'type'  => 'select',
					'items' => array(
						'default' => __( 'Default', 'gofer-seo' ) . $default_frequency_value,
						'always'  => __( 'Always', 'gofer-seo' ),
						'hourly'  => __( 'Hourly', 'gofer-seo' ),
						'daily'   => __( 'Daily', 'gofer-seo' ),
						'weekly'  => __( 'Weekly', 'gofer-seo' ),
						//'bi_weekly' => 'Bi-Weekly',
						'monthly' => __( 'Monthly', 'gofer-seo' ),
						//'quarterly' => 'Quarterly',
						'never'   => __( 'Never', 'gofer-seo' ),
					),
				),
				'enable_exclude' => array(
					'title' => __( 'Exclude Post', 'gofer-seo' ),
					'type'  => 'checkbox',
				),
			),
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

		if ( isset( $input_typesets['gofer_seo_modules']['wrap']['sitemap'] ) ) {
			$input_typesets['gofer_seo_modules']['wrap']['sitemap'] = array_replace( $input_typesets['gofer_seo_modules']['wrap']['sitemap'], $module_typeset );
		} else {
			$input_typesets['gofer_seo_modules']['wrap']['sitemap'] = $module_typeset;
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
				//				'screens'  => $enabled_taxonomies,
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

		if ( isset( $values['gofer_seo_modules']['sitemap'] ) ) {
			$values['gofer_seo_modules']['sitemap'] = array_replace( $values['gofer_seo_modules']['sitemap'], $gofer_seo_term->meta['modules']['sitemap'] );
		} elseif ( isset( $gofer_seo_term->meta['modules']['sitemap'] ) ) {
			$values['gofer_seo_modules']['sitemap'] = $gofer_seo_term->meta['modules']['sitemap'];
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

		$gofer_seo_term->meta['modules']['sitemap'] = array_replace_recursive(
			$gofer_seo_term->meta['modules']['sitemap'],
			$values['gofer_seo_modules']['sitemap']
		);

		$results = $gofer_seo_term->update_meta();

		return $values;
	}
}
