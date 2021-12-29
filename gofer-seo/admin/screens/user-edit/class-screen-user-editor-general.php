<?php
/**
 * Admin Screen: User Editor - General
 *
 * @package Gofer SEO
 * @since   1.0.0
 */

/**
 * Class Gofer_SEO_Screen_User_Editor_General
 *
 * @since 1.0.0
 */
class Gofer_SEO_Screen_User_Editor_General extends Gofer_SEO_Screen_User_Editor {

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

		$default_noindex_value = '';
		if ( isset( $gofer_seo_options->options['modules']['general']['archive_author_enable_noindex'] ) ) {
			if ( false === $gofer_seo_options->options['modules']['general']['archive_author_enable_noindex'] ) {
				$default_noindex_value = ' (disabled)';
			} elseif ( true === $gofer_seo_options->options['modules']['general']['archive_author_enable_noindex'] ) {
				$default_noindex_value = ' (enabled)';
			}
		}

		$module_typeset = array(
			'title'                => array(
				'title'      => __( 'Title', 'gofer-seo' ),
				'input_type' => 'text',
				'conditions' => array(
					'action'                           => 'readonly',
					'relation'                         => 'OR',
					'gofer_seo_general-enable_force_disable' => array(
						'operator'    => '===',
						'right_value' => true,
					),
					'gofer_seo_general-enable_noindex' => array(
						'operator'    => '===',
						'right_value' => '1',
					),
				),
			),
			'description'          => array(
				'title'      => __( 'Description', 'gofer-seo' ),
				'input_type' => 'textarea',
				'attrs'      => array(
					'rows' => 3,
				),
				'conditions' => array(
					'action'                           => 'readonly',
					'relation'                         => 'OR',
					'gofer_seo_general-enable_force_disable' => array(
						'operator'    => '===',
						'right_value' => true,
					),
					'gofer_seo_general-enable_noindex' => array(
						'operator'    => '===',
						'right_value' => '1',
					),
				),
			),
			'enable_noindex'       => array(
				'title'      => __( 'NoIndex', 'gofer-seo' ),
				'type'       => 'select',
				'items'      => array(
					-1 => __( 'Use Default', 'gofer-seo' ) . $default_noindex_value,
					0  => __( 'Disable', 'gofer-seo' ),
					1  => __( 'Enable', 'gofer-seo' ),
				),
				'conditions' => array(
					'action' => 'disable',
					'gofer_seo_general-enable_force_disable' => array(
						'operator'    => '===',
						'right_value' => true,
					),
				),
				'esc'        => array(
					array( 'intval' ),
				),
			),
			'disable_analytics'    => array(
				'title'      => __( 'Disable Analytics', 'gofer-seo' ),
				'type'       => 'checkbox',
				'conditions' => array(
					'action' => 'disable',
					'gofer_seo_general-enable_force_disable' => array(
						'operator'    => '===',
						'right_value' => true,
					),
				),
			),
			'enable_force_disable' => array(
				'title' => __( 'Force Disable SEO', 'gofer-seo' ),
				'type'  => 'checkbox',
			),
		);

		// Set Module Tabs if not yet set.
		if ( ! isset( $input_typesets['gofer_seo_general'] ) ) {
			$input_typesets['gofer_seo_general'] = array(
				'title'      => __( 'Gofer SEO General Settings', 'gofer-seo' ),
				'type'       => 'table-form-table',
				'wrap'       => array(),
				'conditions' => array(),
				'layout'     => 'h2-input-column',
			);
		}

		if ( isset( $input_typesets['gofer_seo_general']['wrap'] ) ) {
			$input_typesets['gofer_seo_general']['wrap'] = array_replace( $input_typesets['gofer_seo_general']['wrap'], $module_typeset );
		} else {
			$input_typesets['gofer_seo_general']['wrap'] = $module_typeset;
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

		if ( ! isset( $values['gofer_seo_general'] ) ) {
			$values['gofer_seo_general'] = array();
		}

		if ( isset( $values['gofer_seo_general'] ) ) {
			$values['gofer_seo_general'] = array_replace( $values['gofer_seo_general'], $gofer_seo_user->meta['modules']['general'] );
		} elseif ( isset( $gofer_seo_user->meta['modules']['general'] ) ) {
			$values['gofer_seo_general'] = $gofer_seo_user->meta['modules']['general'];
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

		$gofer_seo_user->meta['modules']['general'] = array_replace_recursive(
			$gofer_seo_user->meta['modules']['general'],
			$values['gofer_seo_general']
		);

		$results = $gofer_seo_user->update_meta();

		return $values;
	}
}
