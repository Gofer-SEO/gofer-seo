<?php
/**
 * Admin Screen: Page-Module - General
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

/**
 * Class Gofer_SEO_Screen_Page_Module_General
 *
 * @since 1.0.0
 */
class Gofer_SEO_Screen_Page_Module_General extends Gofer_SEO_Screen_Page_Module {

	protected $submenu_order = 3;

	protected function get_module_slug() {
		return 'general';
	}

	/**
	 * Submenu Slug.
	 *
	 * @inheritDoc
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_submenu_slug() {
		// TODO Revert to submenu when Dashboard is added as the top_level_*.
		// return 'gofer_seo_module_general';
		return $this->get_menu_parent_slug();
	}

	/**
	 * Get Menu Title.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_menu_title() {
		return 'General';
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
		$users_items      = gofer_seo_get_user_roles_as_items();

		array(
			'example' => array(
				'slug'            => 'example',
				'title'           => __( 'Show Admin Bar', 'gofer-seo' ),
				// Shorthand: type.
				// OR 'input_type'.
				'type'            => 'checkbox',
				// TODO Should items become the array keys for wrap_dynamic?
				// Shorthand: items.
				// OR 'input_items'.
				'wrap'            => array(),
				'wrap_dynamic'    => array(),
				'items'           => array(
					// {SLUG} => {TITLE}.
					'post'             => 'Post',
					'page'             => 'Page',
					'custom_post_type' => 'A Custom Post Type',
				),
				// Shorthand: conditions
				// OR condition
				// Affects wraps.
				'conditions'      => array(
					'action'   => 'show', // 'show', 'hide', 'enable', 'disable', & 'readonly'.
					'relation' => 'AND', // 'OR', & 'AND'.
					'fo'       => array(
						'left_var'  => 'fo',
						'operator'  => '!=',
						'right_var' => 'bar',
					),
					'fo2'      => array(
						'left_var'    => 'fo',
						'operator'    => '!=',
						'right_value' => true,
					),
					'bar'      => array(
						'operator'    => '===',
						'right_value' => false,
					),
				),
				// Affects wrapped items, but not the wrap(_dynamic) itself.
				'item_conditions' => array(
					'{SLUG}' => array(
						'action'       => 'hide', // 'show', 'hide', 'enable', 'disable', & 'readonly'.
						'relation'     => 'AND', // 'OR', & 'AND'.
						'{INPUT_NAME}' => array(
							'left_var'    => '{INPUT_NAME}',
							'operator'    => '!=',
							'right_value' => true,
						),
					),
				),
				'attr'            => array(
					'class' => 'gofer-seo-test-css',
				),
				'esc'             => array(
					array(
						'callback_function',
						array( array( 'args' ) ),
					),
					array(
						array( $this, 'callback_method' ),
						array( array( 'args' ) ),
					),

				),
				// (optional) If needing to modify others. This is be set by default.
				// ?Tooltip?.
			),
		);

		$input_typesets = array(
			/* **_________*********************************************************************************************/
			/* _/ Modules \___________________________________________________________________________________________*/
			'enable_social_media'                  => array(
				'title'      => __( 'Social Media', 'gofer-seo' ),
				'input_type' => 'checkbox',
			),
			'enable_sitemap'                       => array(
				'title'      => __( 'Sitemap', 'gofer-seo' ),
				'input_type' => 'checkbox',
			),
			'enable_schema_graph'                  => array(
				'title'      => __( 'Schema Graph', 'gofer-seo' ),
				'input_type' => 'checkbox',
			),
			'enable_crawlers'                      => array(
				'title'      => __( 'Crawlers', 'gofer-seo' ),
				'input_type' => 'checkbox',
			),
			'enable_advanced'                      => array(
				'title'      => __( 'Advanced', 'gofer-seo' ),
				'input_type' => 'checkbox',
			),
			'enable_debugger'                      => array(
				'title'      => __( 'Debugger', 'gofer-seo' ),
				'input_type' => 'checkbox',
			),

			/* **_________*********************************************************************************************/
			/* _/ General \___________________________________________________________________________________________*/
			'show_admin_bar'                       => array(
				'title'       => __( 'Show Admin Bar', 'gofer-seo' ),
				'input_type'  => 'checkbox',
				'input_items' => array( 1 => '' ),
			),
			'enable_canonical'                     => array(
				'title'      => __( 'Enable Canonical Link Tags', 'gofer-seo' ),
				'input_type' => 'checkbox',
			),
			'enable_canonical_paginated'           => array(
				'title'      => __( 'Enable Paginated Canonical Link Tags', 'gofer-seo' ),
				'input_type' => 'checkbox',
				'conditions' => array(
					'enable_canonical' => array(
						'operator'    => '==',
						'right_value' => true,
					),
				),
			),

			/* **_______________***************************************************************************************/
			/* _/ Site/Defaults \_____________________________________________________________________________________*/
			'enable_site_title'                    => array(
				'title'      => __( 'Enable Title.', 'gofer-seo' ),
				'input_type' => 'checkbox',
			),
			'enable_site_description'              => array(
				'title'      => __( 'Enable Description.', 'gofer-seo' ),
				'input_type' => 'checkbox',
			),
			'use_static_homepage'                  => array(
				'title'      => __( 'Use Static Homepage.', 'gofer-seo' ),
				'input_type' => 'checkbox',
			),
			'site_title'                           => array(
				'title'      => __( 'Site Title', 'gofer-seo' ),
				'input_type' => 'text',
				'conditions' => array(
					'action'            => 'readonly',
					'enable_site_title' => array(
						'operator'    => '!==',
						'right_value' => true,
					),
				),
				'attrs'      => array(
					'placeholder' => get_bloginfo( 'name' ),
				),
			),
			'site_description'                     => array(
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
					'rows'        => 3,
					'placeholder' => get_bloginfo( 'description' ),
				),
			),
			'site_name'                            => array(
				'title'      => __( 'Site Name', 'gofer-seo' ),
				'input_type' => 'text',
				'attrs'      => array(
					'autocomplete' => 'off',
				),
			),
			'site_keywords'                        => array(
				'title'      => __( 'Site Keywords', 'gofer-seo' ),
				'input_type' => 'text',
			),
			'site_image'                           => array(
				'title'      => __( 'Site Image', 'gofer-seo' ),
				'input_type' => 'image',
			),
			'site_logo'                            => array(
				'title'      => __( 'Site Logo', 'gofer-seo' ),
				'input_type' => 'image',
			),
			'site_title_format'                    => array(
				'title'      => __( 'Site Title Format', 'gofer-seo' ),
				'input_type' => 'text',
				'conditions' => array(
					'enable_site_title' => array(
						'operator'    => '==',
						'right_value' => true,
					),
				),
			),
			'site_description_format'              => array(
				'title'      => __( 'Site Description Format', 'gofer-seo' ),
				'input_type' => 'text',
				'conditions' => array(
					'enable_site_description' => array(
						'operator'    => '==',
						'right_value' => true,
					),
				),
			),
			'home_meta_tags'                       => array(
				'title'      => __( 'Custom Home Page Header Meta Tags', 'gofer-seo' ),
				'input_type' => 'textarea',
			),
			'posts_page_meta_tags'                 => array(
				'title'      => __( 'Custom Posts Page Header Meta Tags', 'gofer-seo' ),
				'input_type' => 'textarea',
			),

			/* **_______***********************************************************************************************/
			/* _/ Image \_____________________________________________________________________________________________*/
			'image_source'                         => array(
				'title' => __( 'Image Source', 'gofer-seo' ),
				'type'  => 'select',
				'items' => array(
					'default'  => 'Default',
					'featured' => 'Featured Image',
					'attach'   => 'Attached',
					'content'  => 'Content',
					'custom'   => 'Custom',
					'author'   => 'Author Image',
					'auto'     => 'Auto',
				),
			),
			'image_source_meta_keys'               => array(
				'title' => __( 'Meta Keys', 'gofer-seo' ),
				'type'  => 'text',
			),

			/* **___________________***********************************************************************************/
			/* _/ Post Type Content \_________________________________________________________________________________*/
			'enable_post_types'                    => array(
				'title' => __( 'Enable Post Types', 'gofer-seo' ),
				'type'  => 'multicheckbox',
				'items' => $post_types_items,
			),
			'post_type_settings'                   => array(
				'title'           => __( 'Post Type Settings', 'gofer-seo' ),
				'type'            => 'wrap_dynamic',
				'items'           => $post_types_items,
				'wrap_dynamic'    => array(
					'enable_editor_meta_box' => array(
						'title' => __( 'Enable Meta Box', 'gofer-seo' ),
						'type'  => 'checkbox',
					),
					'title_format'           => array(
						'title'      => __( 'Title Format', 'gofer-seo' ),
						'type'       => 'text',
						'conditions' => array(
							'action'         => 'readonly',
							'enable_noindex' => array(
								'operator'    => '==',
								'right_value' => true,
							),
						),
					),
					'description_format'     => array(
						'title'      => __( 'Description Format', 'gofer-seo' ),
						'type'       => 'text',
						'conditions' => array(
							'action'         => 'readonly',
							'enable_noindex' => array(
								'operator'    => '==',
								'right_value' => true,
							),
						),
					),
					'enable_noindex'         => array(
						'title' => __( 'Enable NoIndex', 'gofer-seo' ),
						'type'  => 'checkbox',
					),
					'enable_nofollow'        => array(
						'title' => __( 'Enable NoFollow', 'gofer-seo' ),
						'type'  => 'checkbox',
					),
					'custom_meta_tags'       => array(
						'title'      => __( 'Custom Meta Tags', 'gofer-seo' ),
						'type'       => 'textarea',
						'conditions' => array(
							'action'         => 'readonly',
							'enable_noindex' => array(
								'operator'    => '==',
								'right_value' => true,
							),
						),
					),
				),
				'conditions'      => array(
					'enable_post_types[]' => array(
						'operator'    => '!=',
						'right_value' => '0',
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

			/* **__________________************************************************************************************/
			/* _/ Taxonomy Content \__________________________________________________________________________________*/
			'enable_taxonomies'                    => array(
				'title' => __( 'Enable Taxonomies', 'gofer-seo' ),
				'type'  => 'multicheckbox',
				'items' => $taxonomies_items,
			),
			'taxonomy_settings'                    => array(
				'title'           => __( 'Taxonomy Settings', 'gofer-seo' ),
				'type'            => 'wrap_dynamic',
				'items'           => $taxonomies_items,
				'wrap_dynamic'    => array(
					'enable_editor_meta_box' => array(
						'title' => __( 'Enable Meta Box', 'gofer-seo' ),
						'type'  => 'checkbox',
					),
					'title_format'           => array(
						'title'      => __( 'Title Format', 'gofer-seo' ),
						'type'       => 'text',
						'conditions' => array(
							'action'         => 'readonly',
							'enable_noindex' => array(
								'operator'    => '==',
								'right_value' => true,
							),
						),
					),
					'description_format'     => array(
						'title'      => __( 'Description Format', 'gofer-seo' ),
						'type'       => 'text',
						'conditions' => array(
							'action'         => 'readonly',
							'enable_noindex' => array(
								'operator'    => '==',
								'right_value' => true,
							),
						),
					),
					'enable_noindex'         => array(
						'title' => __( 'Enable NoIndex', 'gofer-seo' ),
						'type'  => 'checkbox',
					),
					'enable_nofollow'        => array(
						'title' => __( 'Enable NoFollow', 'gofer-seo' ),
						'type'  => 'checkbox',
					),
				),
				'conditions'      => array(
					'enable_taxonomies[]' => array(
						'operator'    => '!=',
						'right_value' => '0',
					),
				),
				'item_conditions' => array_combine(
					array_keys( $taxonomies_items ),
					array_map(
						function( $taxonomy_slug ) {
							return array(
								'enable_taxonomies[]' => array(
									'operator'    => '==',
									'right_value' => $taxonomy_slug,
								),
							);
						},
						array_keys( $taxonomies_items )
					)
				),
			),

			/* **_________________*************************************************************************************/
			/* _/ Archive Content \___________________________________________________________________________________*/
			'archive_post_title_format'            => array(
				'title' => __( 'Post/Page Archive Title Format', 'gofer-seo' ),
				'type'  => 'text',
			),
			'archive_taxonomy_term_title_format'   => array(
				'title' => __( 'Taxonomy Archive Title Format', 'gofer-seo' ),
				'type'  => 'text',
			),
			'archive_date_title_format'            => array(
				'title'      => __( 'Date Archive Title Format', 'gofer-seo' ),
				'type'       => 'text',
				'conditions' => array(
					'action'                      => 'readonly',
					'archive_date_enable_noindex' => array(
						'operator'    => '==',
						'right_value' => true,
					),
				),
			),
			'archive_author_title_format'          => array(
				'title'      => __( 'Author Archive Title Format', 'gofer-seo' ),
				'type'       => 'text',
				'conditions' => array(
					'action'                        => 'readonly',
					'archive_author_enable_noindex' => array(
						'operator'    => '==',
						'right_value' => true,
					),
				),
			),
			'archive_date_enable_noindex'          => array(
				'title' => __( 'Enable NoIndex on Date Archive', 'gofer-seo' ),
				'type'  => 'checkbox',
			),
			'archive_author_enable_noindex'        => array(
				'title' => __( 'Enable NoIndex on Author Archive', 'gofer-seo' ),
				'type'  => 'checkbox',
			),

			/* **________________**************************************************************************************/
			/* _/ Search Content \____________________________________________________________________________________*/
			'search_title_format'                  => array(
				'title'      => __( 'Search Title Format', 'gofer-seo' ),
				'type'       => 'text',
				'conditions' => array(
					'action'                => 'readonly',
					'search_enable_noindex' => array(
						'operator'    => '==',
						'right_value' => true,
					),
				),
			),
			'search_enable_noindex'                => array(
				'title' => __( 'Enable NoIndex on Search Page', 'gofer-seo' ),
				'type'  => 'checkbox',
			),

			/* **_____________*****************************************************************************************/
			/* _/ 404 Content \_______________________________________________________________________________________*/
			'404_title_format'                     => array(
				'title'      => __( '404 Title Format', 'gofer-seo' ),
				'type'       => 'text',
				'conditions' => array(
					'action'             => 'readonly',
					'404_enable_noindex' => array(
						'operator'    => '==',
						'right_value' => true,
					),
				),
			),
			'404_enable_noindex'                   => array(
				'title' => __( 'Enable NoIndex on 404 Page', 'gofer-seo' ),
				'type'  => 'checkbox',
			),

			/* **____________________**********************************************************************************/
			/* _/ Additional Content \________________________________________________________________________________*/
			'paginate_format'                      => array(
				'title'      => __( 'Pagination Title Format', 'gofer-seo' ),
				'type'       => 'text',
				'conditions' => array(
					'action'                  => 'readonly',
					'paginate_enable_noindex' => array(
						'operator'    => '==',
						'right_value' => true,
					),
				),
			),
			'paginate_enable_noindex'              => array(
				'title' => __( 'Enable NoIndex on Pagination', 'gofer-seo' ),
				'type'  => 'checkbox',
			),
			'paginate_enable_nofollow'             => array(
				'title' => __( 'Enable NoFollow on Pagination', 'gofer-seo' ),
				'type'  => 'checkbox',
			),

			/* **________**********************************************************************************************/
			/* _/ Verify \____________________________________________________________________________________________*/
			'verify_google'                        => array(
				'title' => __( 'Verify Google', 'gofer-seo' ),
				'type'  => 'text',
			),
			'verify_bing'                          => array(
				'title' => __( 'Verify Bing', 'gofer-seo' ),
				'type'  => 'text',
			),
			'verify_pinterest'                     => array(
				'title' => __( 'Verify Pinterest', 'gofer-seo' ),
				'type'  => 'text',
			),
			'verify_yandex'                        => array(
				'title' => __( 'Verify Yandex', 'gofer-seo' ),
				'type'  => 'text',
			),
			'verify_baidu'                         => array(
				'title' => __( 'Verify Baidu', 'gofer-seo' ),
				'type'  => 'text',
			),

			/* **___________*******************************************************************************************/
			/* _/ Analytics \_________________________________________________________________________________________*/
			'google_analytics'                     => array(
				'title' => __( 'Google Analytics', 'gofer-seo' ),
				'type'  => 'wrap',
				'wrap'  => array(
					'ua_id'                          => array(
						'title' => __( 'Tracking ID', 'gofer-seo' ),
						'type'  => 'text',
						'attrs' => array(
							'placeholder' => 'UA-########-#',
						),
					),
					'exclude_user_roles'             => array(
						'title'      => __( 'Exclude Roles from Tracking', 'gofer-seo' ),
						'type'       => 'multicheckbox',
						'items'      => $users_items,
						'conditions' => array(
							'google_analytics-ua_id' => array(
								'operator'    => '!=',
								'right_value' => '',
							),
						),
					),
					'enable_advanced_settings'       => array(
						'title'      => __( 'Enable Advanced Settings', 'gofer-seo' ),
						'type'       => 'checkbox',
						'conditions' => array(
							'google_analytics-ua_id' => array(
								'operator'    => '!=',
								'right_value' => '',
							),
						),
					),
					'track_domain'                   => array(
						'title'      => __( 'Cookie Domain', 'gofer-seo' ),
						'type'       => 'text',
						'conditions' => array(
							'google_analytics-ua_id' => array(
								'operator'    => '!=',
								'right_value' => '',
							),
							'google_analytics-enable_advanced_settings' => array(
								'operator'    => '==',
								'right_value' => true,
							),
						),
						'esc'        => array(
							array(
								'esc_url',
							),
						),
					),
					'enable_track_multi_domains'     => array(
						'title'      => __( 'Enable Linker for Cross-Domain', 'gofer-seo' ),
						'type'       => 'checkbox',
						'conditions' => array(
							'google_analytics-ua_id' => array(
								'operator'    => '!=',
								'right_value' => '',
							),
							'google_analytics-enable_advanced_settings' => array(
								'operator'    => '==',
								'right_value' => true,
							),
						),
					),
					'track_multi_domains'            => array(
						'title'      => __( 'Auto-Link Domains', 'gofer-seo' ),
						'type'       => 'textarea',
						'conditions' => array(
							'google_analytics-ua_id' => array(
								'operator'    => '!=',
								'right_value' => '',
							),
							'google_analytics-enable_advanced_settings' => array(
								'operator'    => '==',
								'right_value' => true,
							),
							'google_analytics-enable_track_multi_domains' => array(
								'operator'    => '==',
								'right_value' => true,
							),
						),
						'attrs'      => array(
							'rows' => 6,
						),
					),
					'enable_enhance_ecommerce'       => array(
						'title'      => __( 'Enhance eCommerce', 'gofer-seo' ),
						'type'       => 'checkbox',
						'conditions' => array(
							'google_analytics-ua_id' => array(
								'operator'    => '!=',
								'right_value' => '',
							),
							'google_analytics-enable_advanced_settings' => array(
								'operator'    => '==',
								'right_value' => true,
							),
						),
					),
					'enable_enhance_link_attributes' => array(
						'title'      => __( 'Enhance Link Attributes', 'gofer-seo' ),
						'type'       => 'checkbox',
						'conditions' => array(
							'google_analytics-ua_id' => array(
								'operator'    => '!=',
								'right_value' => '',
							),
							'google_analytics-enable_advanced_settings' => array(
								'operator'    => '==',
								'right_value' => true,
							),
						),
					),
					'enable_advertising_features'    => array(
						'title'      => __( 'Enable Advertising Features', 'gofer-seo' ),
						'type'       => 'checkbox',
						'conditions' => array(
							'google_analytics-ua_id' => array(
								'operator'    => '!=',
								'right_value' => '',
							),
							'google_analytics-enable_advanced_settings' => array(
								'operator'    => '==',
								'right_value' => true,
							),
						),
					),
					'enable_anonymize_ip'            => array(
						'title'      => __( 'Anonymize IP', 'gofer-seo' ),
						'type'       => 'checkbox',
						'conditions' => array(
							'google_analytics-ua_id' => array(
								'operator'    => '!=',
								'right_value' => '',
							),
							'google_analytics-enable_advanced_settings' => array(
								'operator'    => '==',
								'right_value' => true,
							),
						),
					),

					// AutoTrack.
					'enable_track_outbound_links'    => array(
						'title'      => __( 'Track Outbound Links', 'gofer-seo' ),
						'type'       => 'checkbox',
						'conditions' => array(
							'google_analytics-ua_id' => array(
								'operator'    => '!=',
								'right_value' => '',
							),
							'google_analytics-enable_advanced_settings' => array(
								'operator'    => '==',
								'right_value' => true,
							),
						),
					),
					'enable_track_outbound_forms'    => array(
						'title'      => __( 'Track Outbound Forms', 'gofer-seo' ),
						'type'       => 'checkbox',
						'conditions' => array(
							'google_analytics-ua_id' => array(
								'operator'    => '!=',
								'right_value' => '',
							),
							'google_analytics-enable_advanced_settings' => array(
								'operator'    => '==',
								'right_value' => true,
							),
						),
					),
					'enable_track_social_media'      => array(
						'title'      => __( 'Track Social Media', 'gofer-seo' ),
						'type'       => 'checkbox',
						'conditions' => array(
							'google_analytics-ua_id' => array(
								'operator'    => '!=',
								'right_value' => '',
							),
							'google_analytics-enable_advanced_settings' => array(
								'operator'    => '==',
								'right_value' => true,
							),
						),
					),
					'enable_track_events'            => array(
						'title'      => __( 'Track Events', 'gofer-seo' ),
						'type'       => 'checkbox',
						'conditions' => array(
							'google_analytics-ua_id' => array(
								'operator'    => '!=',
								'right_value' => '',
							),
							'google_analytics-enable_advanced_settings' => array(
								'operator'    => '==',
								'right_value' => true,
							),
						),
					),
					'enable_track_url_changes'       => array(
						'title'      => __( 'Track URL Changes', 'gofer-seo' ),
						'type'       => 'checkbox',
						'conditions' => array(
							'google_analytics-ua_id' => array(
								'operator'    => '!=',
								'right_value' => '',
							),
							'google_analytics-enable_advanced_settings' => array(
								'operator'    => '==',
								'right_value' => true,
							),
						),
					),
					'enable_track_media_query'       => array(
						'title'      => __( 'Track Media Query', 'gofer-seo' ),
						'type'       => 'checkbox',
						'conditions' => array(
							'google_analytics-ua_id' => array(
								'operator'    => '!=',
								'right_value' => '',
							),
							'google_analytics-enable_advanced_settings' => array(
								'operator'    => '==',
								'right_value' => true,
							),
						),
					),
					'enable_track_page_visibility'   => array(
						'title'      => __( 'Track Page Visibility', 'gofer-seo' ),
						'type'       => 'checkbox',
						'conditions' => array(
							'google_analytics-ua_id' => array(
								'operator'    => '!=',
								'right_value' => '',
							),
							'google_analytics-enable_advanced_settings' => array(
								'operator'    => '==',
								'right_value' => true,
							),
						),
					),
					'enable_track_impressions'       => array(
						'title'      => __( 'Track Impressions', 'gofer-seo' ),
						'type'       => 'checkbox',
						'conditions' => array(
							'google_analytics-ua_id' => array(
								'operator'    => '!=',
								'right_value' => '',
							),
							'google_analytics-enable_advanced_settings' => array(
								'operator'    => '==',
								'right_value' => true,
							),
						),
					),
					'enable_track_max_scroll'        => array(
						'title'      => __( 'Track Max Scroll', 'gofer-seo' ),
						'type'       => 'checkbox',
						'conditions' => array(
							'google_analytics-ua_id' => array(
								'operator'    => '!=',
								'right_value' => '',
							),
							'google_analytics-enable_advanced_settings' => array(
								'operator'    => '==',
								'right_value' => true,
							),
						),
					),
					'enable_clean_url'               => array(
						'title'      => __( 'Clean URL', 'gofer-seo' ),
						'type'       => 'checkbox',
						'conditions' => array(
							'google_analytics-ua_id' => array(
								'operator'    => '!=',
								'right_value' => '',
							),
							'google_analytics-enable_advanced_settings' => array(
								'operator'    => '==',
								'right_value' => true,
							),
						),
					),
				),
			),

			/* **__________********************************************************************************************/
			/* _/ Generate \__________________________________________________________________________________________*/
			'generate_keywords'                    => array(
				'title' => __( 'Generate Keywords', 'gofer-seo' ),
				'type'  => 'wrap',
				'wrap'  => array(
					'enable_generator'            => array(
						'title' => __( 'Enable', 'gofer-seo' ),
						'type'  => 'checkbox',
					),
					'enable_on_static_posts_page' => array(
						'title'      => __( 'Enable on Static Posts Page', 'gofer-seo' ),
						'type'       => 'checkbox',
						'conditions' => array(
							'generate_keywords-enable_generator' => array(
								'operator'    => '===',
								'right_value' => true,
							),
						),

					),
					'use_taxonomies'              => array(
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
			'generate_description'                 => array(
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
			'use_wp_title'                         => array(
				'title' => __( 'Use WP Title', 'gofer-seo' ),
				'type'  => 'checkbox',
			),
			'enable_title_shortcodes'              => array(
				'title' => __( 'Enable Title Shortcodes', 'gofer-seo' ),
				'type'  => 'checkbox',
			),
			'enable_description_shortcodes'        => array(
				'title' => __( 'Enable Description Shortcodes', 'gofer-seo' ),
				'type'  => 'checkbox',
			),
			'enable_trim_description'              => array(
				'title' => __( 'Trim Description', 'gofer-seo' ),
				'type'  => 'checkbox',
			),
			'show_paginate_descriptions'           => array(
				'title' => __( 'Show Descriptions on Pagination', 'gofer-seo' ),
				'type'  => 'checkbox',
			),
			'enable_attachment_redirect_to_parent' => array(
				'title' => __( 'Redirect Attachments to Parent', 'gofer-seo' ),
				'type'  => 'checkbox',
			),
			'admin_menu_order'                     => array(
				'title' => __( 'Menu Order Number', 'gofer-seo' ),
				'type'  => 'number',
			),
			'exclude_urls'                         => array(
				'title' => __( 'Exclude URLs', 'gofer-seo' ),
				'type'  => 'textarea',
			),
		);

		/**
		 * General Module Input Typeset.
		 *
		 * @since 1.0.0
		 *
		 * @see \Gofer_SEO_Screen_Page::get_input_typesets()
		 *
		 * @return array See `\Gofer_SEO_Screen_Page::get_input_typesets()` for details.
		 */
		$input_typesets = apply_filters( 'gofer_seo_admin_module_general_input_typesets', $input_typesets );

		return $input_typesets;
	}

	/**
	 * The Meta Box Params/Configuration.
	 *
	 * @inheritDoc
	 *
	 * @since 1.0.0
	 *
	 * @return array[] See parent method for details.
	 */
	protected function get_meta_box_typesets() {
		$meta_box_typesets = array(
			'enable_modules'     => array(
				'title'    => __( 'Active Modules', 'gofer-seo' ),
				'context'  => 'gofer_seo_side',
				'priority' => 'default',
				'inputs'   => array(
					'enable_social_media',
					'enable_sitemap',
					'enable_schema_graph',
					'enable_crawlers',
					'enable_advanced',
					'enable_debugger',
				),
			),
			'general'            => array(
				'title'    => __( 'General Settings', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'show_admin_bar',
					'enable_canonical',
					'enable_canonical_paginated',
				),
			),
			'site'               => array(
				'title'    => __( 'Site/Defaults', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'enable_site_title',
					'enable_site_description',
					'use_static_homepage',
					'site_title',
					'site_description',
					'site_name',
					'site_keywords',
					'site_image',
					'site_logo',
					'site_title_format',
					'site_description_format',
					'home_meta_tags',
					'posts_page_meta_tags',
				),
			),
			'image'              => array(
				'title'    => __( 'Image', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'image_source',
					'image_source_meta_keys',
				),
			),
			'post_type_content'  => array(
				'title'    => __( 'Post Type Content', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'enable_post_types',
					'post_type_settings',
				),
			),
			'taxonomy_content'   => array(
				'title'    => __( 'Taxonomy Content', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'enable_taxonomies',
					'taxonomy_settings',
				),
			),
			'archive_content'    => array(
				'title'    => __( 'Archive Content', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'archive_post_title_format',
					'archive_taxonomy_term_title_format',
					'archive_date_title_format',
					'archive_author_title_format',
					'archive_date_enable_noindex',
					'archive_author_enable_noindex',
				),
			),
			'search_content'     => array(
				'title'    => __( 'Search Content', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'search_title_format',
					'search_enable_noindex',
				),
			),
			'404_content'        => array(
				'title'    => __( '404 Content', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'404_title_format',
					'404_enable_noindex',
				),
			),
			'additional_content' => array(
				'title'    => __( 'Additional Content', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'paginate_format',
					'paginate_enable_noindex',
					'paginate_nofollow',
				),
			),
			'verification'       => array(
				'title'    => __( 'Verification', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'verify_google',
					'verify_bing',
					'verify_pinterest',
					'verify_yandex',
					'verify_baidu',
				),
			),
			'analytics'          => array(
				'title'    => __( 'Analytics', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'google_analytics',
				),
			),
			'generate'           => array(
				'title'    => __( 'Generate', 'gofer-seo' ),
				'context'  => 'gofer_seo_advanced',
				'priority' => 'default',
				'inputs'   => array(
					'generate_keywords',
					'generate_description',
				),
			),
			'advanced'           => array(
				'title'    => __( 'Advanced', 'gofer-seo' ),
				'context'  => 'gofer_seo_advanced',
				'priority' => 'default',
				'inputs'   => array(
					'use_wp_title',
					'enable_title_shortcodes',
					'enable_description_shortcodes',
					'enable_trim_description',
					'show_paginate_descriptions',
					'enable_attachment_redirect_to_parent',
					'admin_menu_order',
					'exclude_urls',
				),

			),
		);

		/**
		 * General Module Meta Box Typesets.
		 *
		 * @since 1.0.0
		 *
		 * @see \Gofer_SEO_Screen_Page::get_meta_box_typesets()
		 *
		 * @param array $meta_box_typsets See `\Gofer_SEO_Screen_Page::get_meta_box_typesets()` for details.
		 */
		$meta_box_typesets = apply_filters( 'gofer_seo_admin_module_general_meta_box_typesets', $meta_box_typesets );

		return $meta_box_typesets;
	}

	/**
	 * Get Values from Target Source.
	 *
	 * Used by child classes to return the values being used.
	 * If multiple sources are used, and may use similar slugs, then handle the differentiations
	 * here, and ensure the inputs_typeset array key matches the array keys here.
	 *
	 * NOTE: Avoid nesting variables unless it is a wrap/cast or *_dynamic.
	 *
	 * @inheritDoc
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	protected function get_values() {
		$values = parent::get_values();

		// Any additional variables the page/module would modify goes here.
		$options = Gofer_SEO_Options::get_instance();
		foreach ( $options->options['enable_modules'] as $module_slug => $module_enabled ) {
			$values[ 'enable_' . $module_slug ] = $module_enabled;
		}

		/**
		 * General Module Get Values.
		 *
		 * @since 1.0.0
		 *
		 * @param array $values The values of the inputs.
		 */
		$values = apply_filters( 'gofer_seo_admin_module_general_get_values', $values );

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
	 * @return bool
	 */
	protected function update_values( $new_values ) {
		// Any additional variables the page/module would modify goes here.
		$options = Gofer_SEO_Options::get_instance();
		foreach ( $options->options['enable_modules'] as $module_slug => $module_enabled ) {
			$options->options['enable_modules'][ $module_slug ] = $new_values[ 'enable_' . $module_slug ];
			unset( $new_values[ 'enable_' . $module_slug ] );
		}

		/**
		 * General Module Update Values.
		 *
		 * @since 1.0.0
		 *
		 * @param array $new_values The new set of input (typeset) values.
		 */
		$new_values = apply_filters( 'gofer_seo_admin_module_general_update_values', $new_values );

		parent::update_values( $new_values );
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
			__( 'General Settings', 'gofer-seo' ), // Page title.
			__( 'General', 'gofer-seo' ),          // Menu title.
			'gofer_seo_access',                    // Capability.
			// TODO Revert to submenu when Dashboard is added as the top_level_*.
			//$this->submenu_slug,                   // Menu slug.
			$this->menu_parent_slug,                 // Override top_level.
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

		// Any additional scripts for General.
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
	 * Admin Bar (Sub) Menu.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 */
	public function admin_bar_submenu( $wp_admin_bar ) {
		if ( ! is_admin() ) {
			$context = Gofer_SEO_Context::get_instance();
			$url = $context->get_canonical_url();
			if ( ! $url ) {
				return;
			}
			$url = rawurlencode( $url );


			$wp_admin_bar->add_menu(
				array(
					'id'     => 'gofer-seo-external-analyzers',
					'parent' => GOFER_SEO_NICENAME,
					'title'  => __( 'Analyze page', 'gofer-seo' ),
				)
			);

			$submenu_items = array(
				array(
					'id'    => 'gofer-seo-external-analyze-inlinks',
					'title' => __( 'Check links to this URL', 'gofer-seo' ),
					'href'  => 'https://search.google.com/search-console/links/drilldown?resource_id=' . rawurlencode( get_option( 'siteurl' ) ) . '&type=EXTERNAL&target=' . $url . '&domain=',
				),
				array(
					'id'    => 'gofer-seo-external-analyze-cache',
					'title' => __( 'Check Google Cache', 'gofer-seo' ),
					'href'  => '//webcache.googleusercontent.com/search?strip=1&q=cache:' . $url,
				),
				array(
					'id'    => 'gofer-seo-external-analyze-structureddata',
					'title' => __( 'Google Structured Data Test', 'gofer-seo' ),
					'href'  => 'https://search.google.com/structured-data/testing-tool#url=' . $url,
				),
				array(
					'id'    => 'gofer-seo-external-analyze-facebookdebug',
					'title' => __( 'Facebook Debugger', 'gofer-seo' ),
					'href'  => '//developers.facebook.com/tools/debug/og/object?q=' . $url,
				),
				array(
					'id'    => 'gofer-seo-external-analyze-pinterestvalidator',
					'title' => __( 'Pinterest Rich Pins Validator', 'gofer-seo' ),
					'href'  => 'https://developers.pinterest.com/tools/url-debugger/?link=' . $url,
				),
				array(
					'id'    => 'gofer-seo-external-analyze-htmlvalidation',
					'title' => __( 'HTML Validator', 'gofer-seo' ),
					'href'  => '//validator.w3.org/check?uri=' . $url,
				),
				array(
					'id'    => 'gofer-seo-external-analyze-cssvalidation',
					'title' => __( 'CSS Validator', 'gofer-seo' ),
					'href'  => '//jigsaw.w3.org/css-validator/validator?uri=' . $url,
				),
				array(
					'id'    => 'gofer-seo-external-analyze-pagespeed',
					'title' => __( 'Google Page Speed Test', 'gofer-seo' ),
					'href'  => '//developers.google.com/speed/pagespeed/insights/?url=' . $url,
				),
				array(
					'id'    => 'gofer-seo-external-analyze-google-mobile-friendly',
					'title' => __( 'Mobile-Friendly Test', 'gofer-seo' ),
					'href'  => 'https://www.google.com/webmasters/tools/mobile-friendly/?url=' . $url,
				),
			);

			foreach ( $submenu_items as $menu_item ) {
				$menu_args = array(
					'parent' => 'gofer-seo-external-analyzers',
					'id'     => $menu_item['id'],
					'title'  => $menu_item['title'],
					'href'   => $menu_item['href'],
					'meta'   => array(
						'target' => '_blank',
					),
				);

				$wp_admin_bar->add_menu( $menu_args );
			}
		}

		parent::admin_bar_submenu( $wp_admin_bar );
	}

}
