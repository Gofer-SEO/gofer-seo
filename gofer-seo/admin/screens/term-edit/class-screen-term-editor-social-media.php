<?php
/**
 * Admin Screen: Term Editor - Social Media
 *
 * @package Gofer SEO
 * @since   1.0.0
 */

/**
 * Class Gofer_SEO_Screen_Term_Editor_Social_Media
 *
 * @since 1.0.0
 */
class Gofer_SEO_Screen_Term_Editor_Social_Media extends Gofer_SEO_Screen_Term_Editor {

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
								'activities'    => array(
									'optgroup_label' => __( 'Activities', 'gofer-seo' ),
									'activity'       => __( 'Activity', 'gofer-seo' ),
									'sport'          => __( 'Sport', 'gofer-seo' ),
								),
								'businesses'    => array(
									'optgroup_label' => __( 'Businesses', 'gofer-seo' ),
									'bar'            => __( 'Bar', 'gofer-seo' ),
									'company'        => __( 'Company', 'gofer-seo' ),
									'cafe'           => __( 'Cafe', 'gofer-seo' ),
									'hotel'          => __( 'Hotel', 'gofer-seo' ),
									'restaurant'     => __( 'Restaurant', 'gofer-seo' ),
								),
								'groups'        => array(
									'optgroup_label' => __( 'Groups', 'gofer-seo' ),
									'cause'          => __( 'Cause', 'gofer-seo' ),
									'sports_league'  => __( 'Sports League', 'gofer-seo' ),
									'sports_team'    => __( 'Sports Team', 'gofer-seo' ),
								),
								'organizations' => array(
									'optgroup_label' => __( 'Organizations', 'gofer-seo' ),
									'band'           => __( 'Band', 'gofer-seo' ),
									'government'     => __( 'Government', 'gofer-seo' ),
									'non_profit'     => __( 'Non Profit', 'gofer-seo' ),
									'school'         => __( 'School', 'gofer-seo' ),
									'university'     => __( 'University', 'gofer-seo' ),
								),
								'people'        => array(
									'optgroup_label' => __( 'People', 'gofer-seo' ),
									'actor'          => __( 'Actor', 'gofer-seo' ),
									'athlete'        => __( 'Athlete', 'gofer-seo' ),
									'author'         => __( 'Author', 'gofer-seo' ),
									'director'       => __( 'Director', 'gofer-seo' ),
									'musician'       => __( 'Musician', 'gofer-seo' ),
									'politician'     => __( 'Politician', 'gofer-seo' ),
									'profile'        => __( 'Profile', 'gofer-seo' ),
									'public_figure'  => __( 'Public Figure', 'gofer-seo' ),
								),
								'places'        => array(
									'city'           => __( 'City', 'gofer-seo' ),
									'country'        => __( 'Country', 'gofer-seo' ),
									'landmark'       => __( 'Landmark', 'gofer-seo' ),
									'state_province' => __( 'State Province', 'gofer-seo' ),
								),
								'products_and_entertainment' => array(
									'optgroup_label' => __( 'Products and Entertainment', 'gofer-seo' ),
									'album'          => __( 'Album', 'gofer-seo' ),
									'book'           => __( 'Book', 'gofer-seo' ),
									'drink'          => __( 'Drink', 'gofer-seo' ),
									'food'           => __( 'Food', 'gofer-seo' ),
									'game'           => __( 'Game', 'gofer-seo' ),
									'movie'          => __( 'Movie', 'gofer-seo' ),
									'product'        => __( 'Product', 'gofer-seo' ),
									'song'           => __( 'Song', 'gofer-seo' ),
									'tv_show'        => __( 'TV Show', 'gofer-seo' ),
									'episode'        => __( 'Episode', 'gofer-seo' ),
								),
								'websites'      => array(
									'optgroup_label' => __( 'Websites', 'gofer-seo' ),
									'article'        => __( 'Article', 'gofer-seo' ),
									'website'        => __( 'Website', 'gofer-seo' ),
								),
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

		if ( isset( $values['gofer_seo_modules']['social_media'] ) ) {
			$values['gofer_seo_modules']['social_media'] = array_replace( $values['gofer_seo_modules']['social_media'], $gofer_seo_term->meta['modules']['social_media'] );
		} elseif ( isset( $gofer_seo_term->meta['modules']['social_media'] ) ) {
			$values['gofer_seo_modules']['social_media'] = $gofer_seo_term->meta['modules']['social_media'];
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

		$gofer_seo_term->meta['modules']['social_media'] = array_replace_recursive(
			$gofer_seo_term->meta['modules']['social_media'],
			$values['gofer_seo_modules']['social_media']
		);

		$results = $gofer_seo_term->update_meta();

		return $values;
	}
}
