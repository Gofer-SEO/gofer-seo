<?php
/**
 * Admin Screen: Post Editor - Social Media
 *
 * @package Gofer SEO
 * @since   1.0.0
 */

/**
 * Class Gofer_SEO_Screen_Post_Editor_Social_Media
 *
 * @since 1.0.0
 */
class Gofer_SEO_Screen_Post_Editor_Social_Media extends Gofer_SEO_Screen_Post_Editor {

	/**
	 * Get Active Post-Types/Screens.
	 *
	 * This checks if the post type is enabled, and if show meta-box is enabled, and
	 * returns an array of post type slugs.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_active_post_types() {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		$active_post_types = array_filter( $gofer_seo_options->options['modules']['social_media']['enable_post_types'] );
		$active_post_types = array_keys( $active_post_types );

		// TODO Create settings for enable_editor_meta_box.
		foreach ( $active_post_types as $index => $post_type ) {
			if ( false === $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post_type ]['enable_editor_meta_box'] ) {
				unset( $active_post_types[ $index ] );
			}
		}
		$active_post_types = array_values( $active_post_types );

		return $active_post_types;
	}

	/**
	 * The Input Typesets (Params/Configuration)
	 *
	 * @since 1.0.0
	 *
	 * @see   \Gofer_SEO_Screen_Edit::get_input_typesets()
	 *
	 * @param array[] For details, see `\Gofer_SEO_Screen_Edit::get_input_typesets()`.
	 * @return array[] For details, see `\Gofer_SEO_Screen_Edit::get_input_typesets()`.
	 */
	public function get_input_typesets( $input_typesets ) {
		$module_typeset = array(
			'title' => __( 'Social Media', 'gofer-seo' ),
			'type'  => 'tab',
			'wrap'  => array(
				'title'        => array(
					'title'      => __( 'Title', 'gofer-seo' ),
					'input_type' => 'text',
				),
				'description'  => array(
					'title'      => __( 'Description', 'gofer-seo' ),
					'input_type' => 'textarea',
					'attrs'      => array(
						'rows' => 3,
					),
				),
				'keywords'     => array(
					'title'      => __( 'Keywords', 'gofer-seo' ),
					'input_type' => 'text',
				),
				//'enable_image' => array(),
				'image'        => array(
					'title'      => __( 'Image', 'gofer-seo' ),
					'input_type' => 'image-media',
				),
				'image_width'  => array(
					'title'      => __( 'Image Width', 'gofer-seo' ),
					'type'       => 'number',
					'conditions' => array(
						'action' => 'hide',
						'gofer_seo_modules-social_media-image' => array(
							'operator'    => 'match',
							'right_value' => '^[^0]$|^[^0-][0-9]+$',
						),
					),
				),
				'image_height' => array(
					'title'      => __( 'Image Height', 'gofer-seo' ),
					'type'       => 'number',
					'conditions' => array(
						'action' => 'hide',
						'gofer_seo_modules-social_media-image' => array(
							'operator'    => 'match',
							'right_value' => '^[^0]$|^[^0-][0-9]+$',
						),
					),
				),
				'video'        => array(
					'title'      => __( 'Video URL', 'gofer-seo' ),
					'input_type' => 'text',
				),
				'video_width'  => array(
					'title' => __( 'Video Width', 'gofer-seo' ),
					'type'  => 'number',
				),
				'video_height' => array(
					'title' => __( 'Video Height', 'gofer-seo' ),
					'type'  => 'number',
				),
				'facebook'     => array(
					'title' => __( 'Facebook', 'gofer-seo' ),
					'type'  => 'wrap',
					'wrap'  => array(
						'object_type'     => array(
							'title' => __( 'Object Type', 'gofer-seo' ),
							'type'  => 'select',
							'items' => array(
								'default'  => __( '- Use Default -', 'gofer-seo' ),
								'standard' => array(
									'optgroup_label' => __( 'Standard', 'gofer-seo' ),
									'article'        => __( 'Article', 'gofer-seo' ),
									// 'book'           => __( 'Book', 'gofer-seo' ),
									// 'profile'        => __( 'Profile', 'gofer-seo' ),
									'website'        => __( 'Website', 'gofer-seo' ),
								),
//								'music'    => array(
//									'optgroup_label' => __( 'Music', 'gofer-seo' ),
//									'album'          => __( 'Album', 'gofer-seo' ),
//									'playlist'       => __( 'Playlist', 'gofer-seo' ),
//									'radio_station'  => __( 'Radio Station', 'gofer-seo' ),
//									'song'           => __( 'Song', 'gofer-seo' ),
//
//								),
//								'video'    => array(
//									'optgroup_label' => __( 'Video', 'gofer-seo' ),
//									'episode'        => __( 'Episode', 'gofer-seo' ),
//									'movie'          => __( 'Movie', 'gofer-seo' ),
//									'tv_show'        => __( 'TV Show', 'gofer-seo' ),
//									'other'          => __( 'Other', 'gofer-seo' ),
//								),
							),
						),
						//'debug'       => array(), // TODO Button.
						'article_section' => array(
							'title'      => __( 'Article Section', 'gofer-seo' ),
							'type'       => 'text',
							'conditions' => array(
								'gofer_seo_modules-social_media-facebook-object_type' => array(
									'operator'    => '===',
									'right_value' => 'article',
								),
							),
						),
					),
				),
				'twitter'      => array(
					'title' => __( 'Twitter', 'gofer-seo' ),
					'type'  => 'wrap',
					'wrap'  => array(
						'card_type' => array(
							'title' => __( 'Card Type', 'gofer-seo' ),
							'type'  => 'select',
							'items' => array(
								'summary'             => 'Summary',
								'summary_large_image' => 'Summary Large Image',
							),
						),
						'image'     => array(
							'title'      => __( 'Image', 'gofer-seo' ),
							'input_type' => 'image-media',
						),
					),
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

		if ( isset( $input_typesets['gofer_seo_modules']['wrap']['social_media'] ) ) {
			$input_typesets['gofer_seo_modules']['wrap']['social_media'] = array_replace( $input_typesets['gofer_seo_modules']['wrap']['social_media'], $module_typeset );
		} else {
			$input_typesets['gofer_seo_modules']['wrap']['social_media'] = $module_typeset;
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
		$enabled_post_types = $this->get_active_post_types();
		if ( ! isset( $meta_box_typesets['gofer_seo'] ) ) {
			$meta_box_typesets['gofer_seo'] = array(
				'title'    => __( 'Gofer SEO', 'gofer-seo' ),
				'context'  => 'normal',
				'priority' => 'default',
				'screens'  => $enabled_post_types,
				'inputs'   => array(
					'gofer_seo_modules',
				),
			);
		} else {
			$meta_box_typesets['gofer_seo']['screens'] = array_replace(
				$meta_box_typesets['gofer_seo']['screens'],
				$enabled_post_types
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
		global $post;

		$gofer_seo_post = new Gofer_SEO_Post( $post );

		if ( ! isset( $values['gofer_seo_modules'] ) ) {
			$values['gofer_seo_modules'] = array();
		}
		if ( ! isset( $values['gofer_seo_modules']['social_media'] ) ) {
			$values['gofer_seo_modules']['social_media'] = array();
		}

		$values['gofer_seo_modules']['social_media'] = array_replace( $values['gofer_seo_modules']['social_media'], $gofer_seo_post->meta['modules']['social_media'] );

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
		global $post;

		$gofer_seo_post = new Gofer_SEO_Post( $post );

		$gofer_seo_post->meta['modules']['social_media'] = array_replace_recursive(
			$gofer_seo_post->meta['modules']['social_media'],
			$values['gofer_seo_modules']['social_media']
		);

		$gofer_seo_post->update_meta();

		return $values;
	}
}
