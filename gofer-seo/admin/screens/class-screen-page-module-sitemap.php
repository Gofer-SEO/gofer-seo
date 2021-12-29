<?php
/**
 * Admin Screen: Page-Module - Sitemap
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

/**
 * Class Gofer_SEO_Screen_Page_Module_Sitemap
 *
 * @since 1.0.0
 */
class Gofer_SEO_Screen_Page_Module_Sitemap extends Gofer_SEO_Screen_Page_Module {

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
		return 'sitemap';
	}

	/**
	 * Get Submenu Slug.
	 *
	 * @inheritDoc
	 *
	 * @since 1.0.0
	 */
	public function get_submenu_slug() {
		return 'gofer_seo_module_sitemap';
	}

	/**
	 * Get Menu Title.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_menu_title() {
		return 'XML Sitemap';
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

		$module_values = $this->get_values();

		$exclude_term_ids_items = array();
		$enabled_taxonomies = array_filter( $module_values['enable_taxonomies'] );
		foreach ( $enabled_taxonomies as $taxonomy_slug => $value ) {
			if ( ! taxonomy_exists( $taxonomy_slug ) ) {
				continue;
			}

			$exclude_term_ids_items[ $taxonomy_slug ] = array();

			$args = array(
				'taxonomy'   => $taxonomy_slug,
				'hide_empty' => true,
				'fields'     => 'id=>name',
			);
			$exclude_term_ids_items[ $taxonomy_slug ] = gofer_seo_get_terms( $args );
			$exclude_term_ids_items[ $taxonomy_slug ]['optgroup_label'] = $taxonomies_items[ $taxonomy_slug ];
		}

		$input_typesets = array(
			/* **_________*********************************************************************************************/
			/* _/ General \___________________________________________________________________________________________*/
			'enable_news_sitemap'         => array(
				'title' => __( 'Enable News Sitemap', 'gofer-seo' ),
				'type'  => 'checkbox',
			),
			'enable_rss_sitemap'          => array(
				'title' => __( 'Enable RSS Sitemap', 'gofer-seo' ),
				'type'  => 'checkbox',
			),
			'enable_indexes'              => array(
				'title' => __( 'Enable Indexes', 'gofer-seo' ),
				'type'  => 'checkbox',
			),
			'posts_per_sitemap'           => array(
				'title'      => __( 'Posts Per Sitemap Index', 'gofer-seo' ),
				'type'       => 'number',
				'conditions' => array(
					'enable_indexes' => array(
						'operator'    => '===',
						'right_value' => true,
					),
				),
				'attrs'      => array(
					'min' => 1,
				),
			),

			/* **_________________*************************************************************************************/
			/* _/ Site / Defaults \___________________________________________________________________________________*/
			'site_priority'               => array(
				'title' => __( 'Site Priority', 'gofer-seo' ),
				'type'  => 'range',
				'attrs' => array(
					'min' => -1,
					'max' => 10,
				),
			),
			'site_frequency'              => array(
				'title' => __( 'Site Frequency', 'gofer-seo' ),
				'type'  => 'select',
				'items' => array(
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
			'post_type_default_priority'  => array(
				'title' => __( 'Post Type Default Priority', 'gofer-seo' ),
				'type'  => 'range',
				'attrs' => array(
					'min' => -1,
					'max' => 10,
				),
			),
			'post_type_default_frequency' => array(
				'title' => __( 'Post Type Default Frequency', 'gofer-seo' ),
				'type'  => 'select',
				'items' => array(
					'always'  => 'Always',
					'hourly'  => 'Hourly',
					'daily'   => 'Daily',
					'weekly'  => 'Weekly',
					//'bi_weekly' => 'Bi-Weekly',
					'monthly' => 'Monthly',
					//'quarterly' => 'Quarterly',
					'never'   => 'Never',
				),
			),
			'taxonomy_default_priority'   => array(
				'title' => __( 'Taxonomy Default Priority', 'gofer-seo' ),
				'type'  => 'range',
				'attrs' => array(
					'min' => -1,
					'max' => 10,
				),
			),
			'taxonomy_default_frequency'  => array(
				'title' => __( 'Taxonomy Default Frequency', 'gofer-seo' ),
				'type'  => 'select',
				'items' => array(
					'always'  => 'Always',
					'hourly'  => 'Hourly',
					'daily'   => 'Daily',
					'weekly'  => 'Weekly',
					//'bi_weekly' => 'Bi-Weekly',
					'monthly' => 'Monthly',
					//'quarterly' => 'Quarterly',
					'never'   => 'Never',
				),
			),

			/* **___________________***********************************************************************************/
			/* _/ Post Type Content \_________________________________________________________________________________*/
			'enable_post_types'           => array(
				'title' => __( 'Enable Post Types', 'gofer-seo' ),
				'type'  => 'multicheckbox',
				'items' => $post_types_items,
			),
			'post_type_settings'          => array(
				'title'           => __( 'Post Type Settings', 'gofer-seo' ),
				'type'            => 'wrap_dynamic',
				'items'           => $post_types_items,
				'wrap_dynamic'    => array(
					'show_on'   => array(
						'title' => __( 'Show On', 'gofer-seo' ),
						'type'  => 'wrap',
						'wrap'  => array(
							'standard_sitemap' => array(
								'title' => __( 'Standard Sitemap', 'gofer-seo' ),
								'type'  => 'checkbox',
							),
							'news_sitemap'     => array(
								'title'      => __( 'News Sitemap', 'gofer-seo' ),
								'type'       => 'checkbox',
								'conditions' => array(
									'enable_news_sitemap' => array(
										'operator'    => '===',
										'right_value' => true,
									),
								),
							),
							'rss'              => array(
								'title'      => __( 'RSS Feed', 'gofer-seo' ),
								'type'       => 'checkbox',
								'conditions' => array(
									'enable_rss_sitemap' => array(
										'operator'    => '===',
										'right_value' => true,
									),
								),
							),
						),
					),
					'priority'  => array(
						'title' => __( 'Priority', 'gofer-seo' ),
						'type'  => 'range',
						'attrs' => array(
							'min' => -1,
							'max' => 10,
						),
					),
					'frequency' => array(
						'title' => __( 'Frequency', 'gofer-seo' ),
						'type'  => 'select',
						'items' => array(
							'default' => 'Use Default',
							'always'  => 'Always',
							'hourly'  => 'Hourly',
							'daily'   => 'Daily',
							'weekly'  => 'Weekly',
							//'bi_weekly' => 'Bi-Weekly',
							'monthly' => 'Monthly',
							//'quarterly' => 'Quarterly',
							'never'   => 'Never',
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
			'enable_taxonomies'           => array(
				'title' => __( 'Enable Taxonomies', 'gofer-seo' ),
				'type'  => 'multicheckbox',
				'items' => $taxonomies_items,
			),
			'taxonomy_settings'           => array(
				'title'           => __( 'Taxonomy Settings', 'gofer-seo' ),
				'type'            => 'wrap_dynamic',
				'items'           => $taxonomies_items,
				'wrap_dynamic'    => array(
					'show_on'   => array(
						'title' => __( 'Show On', 'gofer-seo' ),
						'type'  => 'wrap',
						'wrap'  => array(
							'standard_sitemap' => array(
								'title' => __( 'Standard Sitemap', 'gofer-seo' ),
								'type'  => 'checkbox',
							),
						),
					),
					'priority'  => array(
						'title' => __( 'Priority', 'gofer-seo' ),
						'type'  => 'range',
						'attrs' => array(
							'min' => -1,
							'max' => 10,
						),
					),
					'frequency' => array(
						'title' => __( 'Frequency', 'gofer-seo' ),
						'type'  => 'select',
						'items' => array(
							'default' => 'Use Default',
							'always'  => 'Always',
							'hourly'  => 'Hourly',
							'daily'   => 'Daily',
							'weekly'  => 'Weekly',
							//'bi_weekly' => 'Bi-Weekly',
							'monthly' => 'Monthly',
							//'quarterly' => 'Quarterly',
							'never'   => 'Never',
						),
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
			'enable_archive_date'         => array(
				'title' => __( 'Enable Date Archive', 'gofer-seo' ),
				'type'  => 'checkbox',
			),
			'archive_date_settings'       => array(
				'title'      => __( 'Date Archive Settings', 'gofer-seo' ),
				'type'       => 'wrap',
				'wrap'       => array(
					'priority'  => array(
						'title' => __( 'Priority', 'gofer-seo' ),
						'type'  => 'range',
						'attrs' => array(
							'min' => -1,
							'max' => 10,
						),
					),
					'frequency' => array(
						'title' => __( 'Frequency', 'gofer-seo' ),
						'type'  => 'select',
						'items' => array(
							'default' => 'Use Default',
							'hourly'  => 'Hourly',
							'daily'   => 'Daily',
							'weekly'  => 'Weekly',
							'monthly' => 'Monthly',
							'yearly'  => 'Yearly',
							'never'   => 'Never',
						),
					),
				),
				'conditions' => array(
					'enable_archive_date' => array(
						'operator'    => '===',
						'right_value' => true,
					),
				),
			),
			'enable_archive_author'       => array(
				'title' => __( 'Enable Author Archive', 'gofer-seo' ),
				'type'  => 'checkbox',
			),
			'archive_author_settings'     => array(
				'title'      => __( 'Author Archive Settings', 'gofer-seo' ),
				'type'       => 'wrap',
				'wrap'       => array(
					'priority'  => array(
						'title' => __( 'Priority', 'gofer-seo' ),
						'type'  => 'range',
						'attrs' => array(
							'min' => -1,
							'max' => 10,
						),
					),
					'frequency' => array(
						'title' => __( 'Frequency', 'gofer-seo' ),
						'type'  => 'select',
						'items' => array(
							'default' => 'Use Default',
							'hourly'  => 'Hourly',
							'daily'   => 'Daily',
							'weekly'  => 'Weekly',
							'monthly' => 'Monthly',
							'yearly'  => 'Yearly',
							'never'   => 'Never',
						),
					),
				),
				'conditions' => array(
					'enable_archive_author' => array(
						'operator'    => '===',
						'right_value' => true,
					),
				),
			),

			/* **_________*********************************************************************************************/
			/* _/ Include \___________________________________________________________________________________________*/
			'include_urls'                => array(
				'title'        => __( 'Include URLs', 'gofer-seo' ),
				'type'         => 'add-field-list',
				'wrap_dynamic' => array(
					'url'           => array(
						'title' => __( 'Target URL', 'gofer-seo' ),
						'type'  => 'text',
						'attrs' => array(
							'placeholder' => 'example.com',
						),
						'esc'   => array(
							array( 'esc_url' ),
						),
					),
					'priority'      => array(
						'title' => __( 'Priority', 'gofer-seo' ),
						'type'  => 'range',
						'attrs' => array(
							'min'   => -1,
							'max'   => 10,
							'value' => '-1',
						),
					),
					'frequency'     => array(
						'title' => __( 'Frequency', 'gofer-seo' ),
						'type'  => 'select',
						'items' => array(
							'default' => 'Use Default',
							'hourly'  => 'Hourly',
							'daily'   => 'Daily',
							'weekly'  => 'Weekly',
							'monthly' => 'Monthly',
							'yearly'  => 'Yearly',
							'never'   => 'Never',
						),
						'attrs' => array(
							'value' => 'default',
						),
					),
					'modified_date' => array(
						'title' => __( 'Modified Date', 'gofer-seo' ),
						'type'  => 'date',
						'attrs' => array(
							'value' => wp_date( 'Y-m-d' ),
						),
					),
				),
				'items'        => array(),
			),

			/* **_________*********************************************************************************************/
			/* _/ Exclude \___________________________________________________________________________________________*/
			'exclude_post_ids'            => array(
				'title' => __( 'Exclude Post IDs', 'gofer-seo' ),
				'type'  => 'text',
				'attrs' => array(
					'placeholder' => '1,2,3',
				),
			),
			'exclude_term_ids'            => array(
				'title' => __( 'Exclude Terms', 'gofer-seo' ),
				'type'  => 'select2-multi-select',
				'items' => $exclude_term_ids_items,
			),

			/* **__________********************************************************************************************/
			/* _/ Advanced \__________________________________________________________________________________________*/
			'include_images'              => array(
				'title' => __( 'Include Images', 'gofer-seo' ),
				'type'  => 'checkbox',
			),

			/* **______________****************************************************************************************/
			/* _/ Sidebar HTML \______________________________________________________________________________________*/

			'html_sitemaps'               => array(
				'title'  => __( 'Sitemaps', 'gofer-seo' ),
				'type'   => 'html',
				'layout' => 'input-row',
			),
			'html_sitemap_general'        => array(
				'title'  => __( 'General Sitemap', 'gofer-seo' ),
				'type'   => 'html',
				'layout' => 'input-row',
			),
			'html_sitemap_news'           => array(
				'title'      => __( 'News Sitemap', 'gofer-seo' ),
				'type'       => 'html',
				'layout'     => 'input-row',
				'conditions' => array(
					'enable_news_sitemap' => array(
						'operator'    => '===',
						'right_value' => true,
					),
				),
			),

			'html_sitemap_rss'            => array(
				'title'      => __( 'RSS Sitemap', 'gofer-seo' ),
				'type'       => 'html',
				'layout'     => 'input-row',
				'conditions' => array(
					'enable_rss_sitemap' => array(
						'operator'    => '===',
						'right_value' => true,
					),
				),
			),
		);

		/**
		 * Sitemap Module Input Typeset.
		 *
		 * @since 1.0.0
		 *
		 * @see \Gofer_SEO_Screen_Page::get_input_typesets()
		 *
		 * @return array See `\Gofer_SEO_Screen_Page::get_input_typesets()` for details.
		 */
		$input_typesets = apply_filters( 'gofer_seo_admin_module_sitemap_input_typesets', $input_typesets );

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
			'general'           => array(
				'title'    => __( 'General Settings', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'enable_news_sitemap',
					'enable_rss_sitemap',
					'enable_indexes',
					'posts_per_sitemap',
				),
			),
			'site'              => array(
				'title'    => __( 'Site/Defaults', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'site_priority',
					'site_frequency',
					'post_type_default_priority',
					'post_type_default_frequency',
					'taxonomy_default_priority',
					'taxonomy_default_frequency',
				),
			),
			'post_type_content' => array(
				'title'    => __( 'Post Type Content', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'enable_post_types',
					'post_type_settings',
				),
			),
			'taxonomy_content'  => array(
				'title'    => __( 'Taxonomy Content', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'enable_taxonomies',
					'taxonomy_settings',
				),
			),
			'archive_content'   => array(
				'title'    => __( 'Archive Content', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'enable_archive_date',
					'archive_date_settings',
					'enable_archive_author',
					'archive_author_settings',
				),
			),
			'include'           => array(
				'title'    => __( 'Include', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'include_urls',
				),
			),
			'exclude'           => array(
				'title'    => __( 'Exclude', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'exclude_post_ids',
					'exclude_term_ids',
				),
			),
			'advanced'          => array(
				'title'    => __( 'Advanced', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'include_images',
				),
			),
			'html_sitemaps'     => array(
				'title'    => __( 'Sitemaps', 'gofer-seo' ),
				'context'  => 'gofer_seo_side',
				'priority' => 'default',
				'inputs'   => array(
					'html_sitemaps',
					'html_sitemap_general',
					'html_sitemap_news',
					'html_sitemap_rss',
				),
			),
		);

		/**
		 * Sitemap Module Meta Box Typesets.
		 *
		 * @since 1.0.0
		 *
		 * @see \Gofer_SEO_Screen_Page::get_meta_box_typesets()
		 *
		 * @param array $meta_box_typesets See `\Gofer_SEO_Screen_Page::get_meta_box_typesets()` for details.
		 */
		$meta_box_typesets = apply_filters( 'gofer_seo_admin_module_sitemap_meta_box_typesets', $meta_box_typesets );

		return $meta_box_typesets;
	}

	/**
	 * Add Submenu to Admin Menu.
	 *
	 * @inheritDoc
	 *
	 * @since 1.0.0
	 *
	 * @link  https://developer.wordpress.org/reference/functions/add_submenu_page/
	 */
	public function add_submenu() {
		$hook_suffix = add_submenu_page(
			$this->menu_parent_slug,               // Menu parent slug.
			__( 'Sitemap', 'gofer-seo' ), // Page title.
			__( 'XML Sitemap', 'gofer-seo' ),          // Menu title.
			'gofer_seo_access',                    // Capability.
			$this->submenu_slug,                   // Menu slug.
			array( $this, 'display_page' ),        // Callback function.
			$this->submenu_order                   // Position.
		);

		$this->set_hook_suffixes( array( $hook_suffix ) );
		$this->set_screen_ids( array( $hook_suffix ) );
	}

	/**
	 * Get Values from Target Source.
	 *
	 * Used by child classes to return the values being used.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed[] ${INPUT_SLUG}
	 *
	 */
	protected function get_values() {
		$values = parent::get_values();
		global $wp_rewrite;

		$values['html_sitemaps']        = sprintf(
			'<p>%s</p>',
			__( 'You can view your sitemap(s) by clicking on the links below.', 'gofer-seo' )
		);
		$href = ! $wp_rewrite->using_permalinks() ? '/?sitemap=index' : home_url( '/sitemap.xml' );
		$values['html_sitemap_general'] = sprintf(
			'<a href="%s" style="margin-left: 9px;" target="_blank">%s</a>',
			$href,
			__( 'Sitemap.xml', 'gofer-seo' )
		);
		$href = ! $wp_rewrite->using_permalinks() ? '/?news-sitemap=index' : home_url( '/news-sitemap.xml' );
		$values['html_sitemap_news']    = sprintf(
			'<a href="%s" style="margin-left: 9px;" target="_blank">%s</a>',
			$href,
			__( 'News-Sitemap.xml', 'gofer-seo' )
		);
		$href = ! $wp_rewrite->using_permalinks() ? '/?rss-sitemap=index' : home_url( '/rss-sitemap.xml' );
		$values['html_sitemap_rss']     = sprintf(
			'<a href="%s" style="margin-left: 9px;" target="_blank">%s</a>',
			$href,
			__( 'RSS-Sitemap.xml', 'gofer-seo' )
		);

		if ( ! get_option( 'blog_public' ) ) {
			$values['html_sitemaps'] .= sprintf(
				'<p>%s. %s.</p>',
				__( 'Warning: The website is currently configured to "Discourage search engines from indexing this site"', 'gofer-seo' ),
				sprintf(
					'%s <a href="%s">%s</a>',
					__( 'You can change this by going to', 'gofer-seo' ),
					admin_url( '/options-reading.php' ),
					__( 'Settings > Reading > Search Engine Visibility', 'gofer-seo' )
				)
			);
		}

		/**
		 * Sitemap Module Get Values.
		 *
		 * @since 1.0.0
		 *
		 * @param array $values The values of the inputs.
		 */
		$values = apply_filters( 'gofer_seo_admin_module_sitemap_get_values', $values );

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
		 * Sitemap Module Update Values.
		 *
		 * @since 1.0.0
		 *
		 * @param array $new_values The new set of input (typeset) values.
		 */
		$new_values = apply_filters( 'gofer_seo_admin_module_sitemap_update_values', $new_values );

		return parent::update_values( $new_values );
	}

}
