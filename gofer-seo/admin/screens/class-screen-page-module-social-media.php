<?php
/**
 * Admin Screen: Page-Module - Social Media
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

/**
 * Class Gofer_SEO_Screen_Page_Module_Social_Media
 *
 * @since 1.0.0
 */
class Gofer_SEO_Screen_Page_Module_Social_Media extends Gofer_SEO_Screen_Page_Module {

	/**
	 * Get Module Slug.
	 *
	 * @inheritDoc
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_module_slug() {
		return 'social_media';
	}

	/**
	 * Get Submenu Slug.
	 *
	 * @inheritDoc
	 *
	 * @since 1.0.0
	 */
	public function get_submenu_slug() {
		return 'gofer_seo_module_social_media';
	}

	/**
	 * Get Menu Title.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_menu_title() {
		return 'Social Media';
	}

	/**
	 * The Input Typesets (Params/Configuration)
	 *
	 * @inheritDoc
	 *
	 * @since 1.0.0
	 *
	 * @return array[] See parent method for details.
	 */
	protected function get_input_typesets() {
		$post_types_items = gofer_seo_get_post_types_as_items();
		$taxonomies_items = gofer_seo_get_taxonomies_as_items();

		$input_typesets = array(
			/* **_________________*************************************************************************************/
			/* _/ Site / Defaults \___________________________________________________________________________________*/
			'enable_site_title'                        => array(
				'title'      => __( 'Use Title', 'gofer-seo' ),
				'input_type' => 'checkbox',
			),
			'enable_site_description'                  => array(
				'title'      => __( 'Use Description.', 'gofer-seo' ),
				'input_type' => 'checkbox',
			),
			'site_name'                                => array(
				'title'      => __( 'Site Name', 'gofer-seo' ),
				'input_type' => 'text',
				'attrs'      => array(
					'autocomplete' => 'off',
				),
			),
			'site_title'                               => array(
				'title'      => __( 'Site Title', 'gofer-seo' ),
				'input_type' => 'text',
				'conditions' => array(
					'action'            => 'readonly',
					'enable_site_title' => array(
						'operator'    => '!==',
						'right_value' => true,
					),
				),
			),
			'site_description'                         => array(
				'title'      => __( 'Site Description', 'gofer-seo' ),
				'input_type' => 'textarea',
				'conditions' => array(
					'action'                  => 'readonly',
					'enable_site_description' => array(
						'operator'    => '!==',
						'right_value' => true,
					),
				),
				'attrs'      => array(
					'rows' => 3,
				),
			),
			'site_image'                               => array(
				'title'      => __( 'Site Image', 'gofer-seo' ),
				'input_type' => 'image',
			),

			/* **_______***********************************************************************************************/
			/* _/ Image \_____________________________________________________________________________________________*/
			'default_image'                            => array(
				'title'      => __( 'Default Image', 'gofer-seo' ),
				'input_type' => 'image',
			),
			'default_image_width'                      => array(
				'title'      => __( 'Default Image Width', 'gofer-seo' ),
				'type'       => 'number',
				'conditions' => array(
					'action'        => 'hide',
					'default_image' => array(
						'operator'    => 'match',
						'right_value' => '^[^0]$|^[^0-][0-9]+$',
					),
				),
			),
			'default_image_height'                     => array(
				'title'      => __( 'Default Image Height', 'gofer-seo' ),
				'type'       => 'number',
				'conditions' => array(
					'action'        => 'hide',
					'default_image' => array(
						'operator'    => 'match',
						'right_value' => '^[^0]$|^[^0-][0-9]+$',
					),
				),
			),
			'image_source'                             => array(
				'title' => __( 'Image Source', 'gofer-seo' ),
				'type'  => 'select',
				'items' => array(
					'default'  => 'Default',
					'auto'     => 'Auto',
					'featured' => 'Featured Image',
					'attach'   => 'Attached',
					'content'  => 'Content',
					'author'   => 'Author Image',
					'custom'   => 'Custom',
				),
			),
			'image_source_meta_keys'                   => array(
				'title' => __( 'Meta Keys', 'gofer-seo' ),
				'type'  => 'text',
			),

			/* **___________________***********************************************************************************/
			/* _/ Post Type Content \_________________________________________________________________________________*/
			'enable_post_types'                        => array(
				'title' => __( 'Enable Post Types', 'gofer-seo' ),
				'type'  => 'multicheckbox',
				'items' => $post_types_items,
			),
			/*
			'post_type_settings'                       => array(
				'title'           => __( 'Post Type Settings', 'gofer-seo' ),
				'type'            => 'wrap_dynamic',
				'items'           => $post_types_items,
				'wrap_dynamic'    => array(
					'enable_editor_meta_box' => array(
						'title'      => __( 'Enable Meta Box', 'gofer-seo' ),
						'type'       => 'checkbox',
						'conditions' => array(
							'action'         => 'disable',
							'enable_noindex' => array(
								'operator'    => '==',
								'right_value' => true,
							),
						),
					),
				),
				// Each item.
				'item_conditions' => array_combine(
					array_keys( $post_types_items ),
					array_map(
						function( $post_type_slug ) {
							return array(
								'enable_post_types[]' => array(
									'operator'    => '==',
									'right_value' => $post_type_slug,
								),
							);
						},
						array_keys( $post_types_items )
					)
				),
			),
			*/

			/* **__________********************************************************************************************/
			/* _/ Facebook \__________________________________________________________________________________________*/
			'fb_admin_id'                              => array(
				'title' => __( 'Facebook ID', 'gofer-seo' ),
				'type'  => 'text',
			),
			'fb_app_id'                                => array(
				'title' => __( 'App ID', 'gofer-seo' ),
				'type'  => 'text',
			),
			'fb_publisher_fb_url'                      => array(
				'title' => __( 'Publisher URL', 'gofer-seo' ),
				'type'  => 'text',
			),
			'fb_use_post_author_fb_url'                => array(
				'title' => __( 'Use Post Author\'s URL', 'gofer-seo' ),
				'type'  => 'checkbox',
			),
			'fb_post_type_settings'                    => array(
				'title'           => __( 'Post Type Settings', 'gofer-seo' ),
				'type'            => 'wrap_dynamic',
				'items'           => $post_types_items,
				'wrap_dynamic'    => array(
					'fb_object_type' => array(
						'title' => __( 'Object Type', 'gofer-seo' ),
						'type'  => 'select',
						'items' => array(
							'standard' => array(
								'optgroup_label' => __( 'Standard', 'gofer-seo' ),
								'article'        => __( 'Article', 'gofer-seo' ),
								// 'book'           => __( 'Book', 'gofer-seo' ),
								// 'profile'        => __( 'Profile', 'gofer-seo' ),
								'website'        => __( 'Website', 'gofer-seo' ),
							),
//							'music'    => array(
//								'optgroup_label' => __( 'Music', 'gofer-seo' ),
//								'album'          => __( 'Album', 'gofer-seo' ),
//								'playlist'       => __( 'Playlist', 'gofer-seo' ),
//								'radio_station'  => __( 'Radio Station', 'gofer-seo' ),
//								'song'           => __( 'Song', 'gofer-seo' ),
//
//							),
//							'video'    => array(
//								'optgroup_label' => __( 'Video', 'gofer-seo' ),
//								'episode'        => __( 'Episode', 'gofer-seo' ),
//								'movie'          => __( 'Movie', 'gofer-seo' ),
//								'tv_show'        => __( 'TV Show', 'gofer-seo' ),
//								'other'          => __( 'Other', 'gofer-seo' ),
//							),
						),
					),
				),
				// Each item.
				'item_conditions' => array_combine(
					array_keys( $post_types_items ),
					array_map(
						function( $post_type_slug ) {
							return array(
								'enable_post_types[]' => array(
									'operator'    => '==',
									'right_value' => $post_type_slug,
								),
							);
						},
						array_keys( $post_types_items )
					)
				),
			),

			/* **_________*********************************************************************************************/
			/* _/ Twitter \___________________________________________________________________________________________*/
			'twitter_card_type'                        => array(
				'title' => __( 'Twitter Card Type', 'gofer-seo' ),
				'type'  => 'select',
				'items' => array(
					'summary'             => 'Summary',
					'summary_large_image' => 'Summary Large Image',
				),
			),
			'twitter_username'                         => array(
				'title' => __( 'Twitter Username', 'gofer-seo' ),
				'type'  => 'text',
			),
			'twitter_use_post_author_twitter_username' => array(
				'title' => __( 'Use Author\'s Twitter Username', 'gofer-seo' ),
				'type'  => 'checkbox',
			),

			/* **__________********************************************************************************************/
			/* _/ Generate \__________________________________________________________________________________________*/
			'generate_keywords'                        => array(
				'title' => __( 'Generate Keywords', 'gofer-seo' ),
				'type'  => 'wrap',
				'wrap'  => array(
					'enable_generator' => array(
						'title' => __( 'Enable', 'gofer-seo' ),
						'type'  => 'checkbox',
					),
					'use_keywords'     => array(
						'title'      => __( 'Use Keywords', 'gofer-seo' ),
						'type'       => 'checkbox',
						'conditions' => array(
							'generate_keywords-enable_generator' => array(
								'operator'    => '===',
								'right_value' => true,
							),
						),

					),
					'use_taxonomies'   => array(
						'title'      => __( 'Use Taxonomies', 'gofer-seo' ),
						'type'       => 'multicheckbox',
						'items'      => $taxonomies_items,
						'conditions' => array(
							'generate_keywords-enable_generator' => array(
								'operator'    => '===',
								'right_value' => true,
							),
						),
					),
				),
			),
			'generate_description'                     => array(
				'title' => __( 'Generate Descriptions', 'gofer-seo' ),
				'type'  => 'wrap',
				'wrap'  => array(
					'enable_generator' => array(
						'title' => __( 'Enable', 'gofer-seo' ),
						'type'  => 'checkbox',
					),
					'use_excerpt'      => array(
						'title'      => __( 'Use Excerpt', 'gofer-seo' ),
						'type'       => 'checkbox',
						'conditions' => array(
							'generate_description-enable_generator' => array(
								'operator'    => '===',
								'right_value' => true,
							),
						),
					),
					'use_content'      => array(
						'title'      => __( 'Use Content', 'gofer-seo' ),
						'type'       => 'checkbox',
						'conditions' => array(
							'generate_description-enable_generator' => array(
								'operator'    => '===',
								'right_value' => true,
							),
						),
					),
				),

			),

			/* **__________********************************************************************************************/
			/* _/ Advanced \__________________________________________________________________________________________*/
			'enable_title_shortcodes'                  => array(
				'title' => __( 'Enable Title Shortcodes', 'gofer-seo' ),
				'type'  => 'checkbox',
			),
			'enable_description_shortcodes'            => array(
				'title' => __( 'Enable Description Shortcodes', 'gofer-seo' ),
				'type'  => 'checkbox',
			),
		);

		/**
		 * Social Media Module Input Typeset.
		 *
		 * @since 1.0.0
		 *
		 * @see \Gofer_SEO_Screen_Page::get_input_typesets()
		 *
		 * @return array See `\Gofer_SEO_Screen_Page::get_input_typesets()` for details.
		 */
		$input_typesets = apply_filters( 'gofer_seo_admin_module_social_media_input_typesets', $input_typesets );

		return $input_typesets;
	}

	/**
	 * The Meta Box Typesets (Params/Configuration).
	 *
	 * @inheritDoc
	 *
	 * @since 1.0.0
	 *
	 * @return array[] See parent method for details.
	 */
	protected function get_meta_box_typesets() {
		$meta_box_typesets = array(
			'site'              => array(
				'title'    => __( 'Site/Defaults', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'enable_site_title',
					'enable_site_description',
					'site_name',
					'site_title',
					'site_description',
					'site_image',
				),
			),
			'image'             => array(
				'title'    => __( 'Image', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'default_image',
					'default_image_width',
					'default_image_height',
					'image_source',
					'image_source_meta_keys',
				),
			),
			'post_type_content' => array(
				'title'    => __( 'Post Type Content', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'enable_post_types',
				),
			),
			'facebook'          => array(
				'title'    => __( 'Facebook', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'fb_admin_id',
					'fb_app_id',
					'fb_publisher_fb_url',
					'fb_use_post_author_fb_url',
					'fb_post_type_settings',
				),
			),
			'twitter'           => array(
				'title'    => __( 'Twitter', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'twitter_card_type',
					'twitter_username',
					'twitter_use_post_author_twitter_username',
				),
			),
			'generate'          => array(
				'title'    => __( 'Generate', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'generate_keywords',
					'generate_description',
				),
			),
			'advanced'          => array(
				'title'    => __( 'Advanced', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'enable_title_shortcodes',
					'enable_description_shortcodes',
				),
			),
		);

		/**
		 * Social Media Module Meta Box Typesets.
		 *
		 * @since 1.0.0
		 *
		 * @see \Gofer_SEO_Screen_Page::get_meta_box_typesets()
		 *
		 * @param array $meta_box_typsets See `\Gofer_SEO_Screen_Page::get_meta_box_typesets()` for details.
		 */
		$meta_box_typesets = apply_filters( 'gofer_seo_admin_module_social_media_meta_box_typesets', $meta_box_typesets );

		return $meta_box_typesets;
	}

	/**
	 * Add Submenu to Admin Menu.
	 *
	 * @inheritDoc
	 *
	 * @since 1.0.0
	 *
	 * @link https://developer.wordpress.org/reference/functions/add_submenu_page/
	 */
	public function add_submenu() {
		$hook_suffix = add_submenu_page(
			$this->menu_parent_slug,               // Menu parent slug.
			__( 'Social Media', 'gofer-seo' ),     // Page title.
			__( 'Social Media', 'gofer-seo' ),     // Menu title.
			'gofer_seo_access',                    // Capability.
			$this->submenu_slug,                   // Menu slug.
			array( $this, 'display_page' ),        // Callback function.
			$this->submenu_order                   // Position.
		);

		$this->set_hook_suffixes( array( $hook_suffix ) );
		$this->set_screen_ids( array( $hook_suffix ) );
	}

	/**
	 * WP Hook - Current Screen.
	 *
	 * Triggers after `admin_menu` & `admin_init`.
	 * Useful for adding enqueue scripts, post transitions, etc.
	 * CANNOT be used to add wp_ajax_*, or adding menus.
	 *
	 * @since 1.0.0
	 *
	 * @link https://developer.wordpress.org/reference/hooks/current_screen/
	 *
	 * @param WP_Screen $current_screen
	 */
	public function current_screen( $current_screen ) {
		parent::current_screen( $current_screen );
		if ( ! in_array( $current_screen->id, $this->get_hook_suffixes( 'submenus' ), true ) ) {
			return;
		}

		// Any additional scripts for Social Media.
	}

	/**
	 * Register/Enqueue Styles.
	 *
	 * @since 1.0.0
	 *
	 * @param $hook_suffix
	 */
	public function admin_register_styles( $hook_suffix ) {
		parent::admin_register_styles( $hook_suffix );
		if ( ! in_array( $hook_suffix, $this->get_hook_suffixes( 'submenus' ), true ) ) {
			return;
		}

		// Styles that would be used on module screens.
	}

	/**
	 * Register/Enqueue Scripts.
	 *
	 * @since 1.0.0
	 *
	 * @param $hook_suffix
	 */
	public function admin_register_scripts( $hook_suffix ) {
		parent::admin_register_scripts( $hook_suffix );
		if ( ! in_array( $hook_suffix, $this->get_hook_suffixes( 'submenus' ), true ) ) {
			return;
		}

		// Scripts that would be used on module screens.
	}

	/**
	 * Get Values from Target Source.
	 *
	 * Used by child classes to return the values being used.
	 *
	 * @since 1.0.0
	 *
	 * @return array ${INPUT_SLUG}
	 *
	 */
	protected function get_values() {
		$values = parent::get_values();

		/**
		 * Social Media Module Get Values.
		 *
		 * @since 1.0.0
		 *
		 * @param array $values The values of the inputs.
		 */
		$values = apply_filters( 'gofer_seo_admin_module_social_media_get_values', $values );

		return $values;
	}

	/**
	 * Update Values to Target Source.
	 *
	 * Used by other classes to handle operations differently.
	 *
	 * @inheritDoc
	 *
	 * @since 1.0.0
	 *
	 * @return bool True on success.
	 */
	protected function update_values( $new_values ) {

		/**
		 * Social Media Module Update Values.
		 *
		 * @since 1.0.0
		 *
		 * @param array $new_values The new set of input (typeset) values.
		 */
		$new_values = apply_filters( 'gofer_seo_admin_module_social_media_update_values', $new_values );

		return parent::update_values( $new_values );
	}

}
