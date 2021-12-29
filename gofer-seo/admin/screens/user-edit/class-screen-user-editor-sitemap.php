<?php
/**
 * Admin Screen: User Editor - Sitemap
 *
 * @package Gofer SEO
 * @since   1.0.0
 */

/**
 * Class Gofer_SEO_Screen_User_Editor_Sitemap
 *
 * @since 1.0.0
 */
class Gofer_SEO_Screen_User_Editor_Sitemap extends Gofer_SEO_Screen_User_Editor {

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
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		if (
				isset( $gofer_seo_options->options['modules']['sitemap']['enable_archive_author'] ) &&
				true === $gofer_seo_options->options['modules']['sitemap']['enable_archive_author']
		) {
			$default_frequency_value = '';
			if (
					isset( $gofer_seo_options->options['modules']['sitemap']['archive_author_settings']['frequency'] ) &&
					'default' !== $gofer_seo_options->options['modules']['sitemap']['archive_author_settings']['frequency']
			) {
				$default_frequency_value = $gofer_seo_options->options['modules']['sitemap']['archive_author_settings']['frequency'];
				$default_frequency_value = ' (' . $default_frequency_value . ')';
			}

			$module_typeset = array(
				'priority'       => array(
					'title'      => __( 'Priority', 'gofer-seo' ),
					'type'       => 'range',
					'attrs'      => array(
						'min' => -1,
						'max' => 10,
					),
					'conditions' => array(
						'action'                           => 'disable',
						'gofer_seo_sitemap-enable_exclude' => array(
							'operator'    => '===',
							'right_value' => true,
						),
					),
				),
				'frequency'      => array(
					'title'      => __( 'Frequency', 'gofer-seo' ),
					'type'       => 'select',
					'items'      => array(
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
					'conditions' => array(
						'action'                           => 'disable',
						'gofer_seo_sitemap-enable_exclude' => array(
							'operator'    => '===',
							'right_value' => true,
						),
					),
				),
				'enable_exclude' => array(
					'title' => __( 'Exclude User Page', 'gofer-seo' ),
					'type'  => 'checkbox',
				),
			);

			// Set Module Tabs if not yet set.
			if ( ! isset( $input_typesets['gofer_seo_sitemap'] ) ) {
				$input_typesets['gofer_seo_sitemap'] = array(
					'title'      => __( 'Gofer SEO Sitemap Settings', 'gofer-seo' ),
					'type'       => 'table-form-table',
					'wrap'       => array(),
					'conditions' => array(),
					'layout'     => 'h2-input-column',
				);
			}

			if ( isset( $input_typesets['gofer_seo_sitemap']['wrap'] ) ) {
				$input_typesets['gofer_seo_sitemap']['wrap'] = array_replace( $input_typesets['gofer_seo_sitemap']['wrap'], $module_typeset );
			} else {
				$input_typesets['gofer_seo_sitemap']['wrap'] = $module_typeset;
			}
		}

		return $input_typesets;
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
		global $user_id;

		$gofer_seo_user = new Gofer_SEO_User( $user_id );

		if ( ! isset( $values['gofer_seo_sitemap'] ) ) {
			$values['gofer_seo_sitemap'] = array();
		}

		if ( isset( $values['gofer_seo_sitemap'] ) ) {
			$values['gofer_seo_sitemap'] = array_replace( $values['gofer_seo_sitemap'], $gofer_seo_user->meta['modules']['sitemap'] );
		} elseif ( isset( $gofer_seo_user->meta['modules']['sitemap'] ) ) {
			$values['gofer_seo_sitemap'] = $gofer_seo_user->meta['modules']['sitemap'];
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
		global $user_id;

		$gofer_seo_user = new Gofer_SEO_User( $user_id );

		$gofer_seo_user->meta['modules']['sitemap'] = array_replace_recursive(
			$gofer_seo_user->meta['modules']['sitemap'],
			$values['gofer_seo_sitemap']
		);

		$results = $gofer_seo_user->update_meta();

		return $values;
	}
}
