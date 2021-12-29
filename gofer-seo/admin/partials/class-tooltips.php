<?php
/**
 * Gofer SEO Tooltips Class
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

// FIXME Add missing translations.
// phpcs:disable WordPress.WP.I18n.MissingTranslatorsComment

/**
 * Class Gofer_SEO_Tooltips.
 *
 * @since 1.0.0
 */
class Gofer_SEO_Tooltips {

	/**
	 * Tooltip HTML content.
	 *
	 * @since 1.0.0
	 * @var array $tooltips_html {
	 *     @type string
	 * }
	 */
	private $tooltips_html = array();

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @param string $screen_id Screen ID or Hook Suffix.
	 */
	public function __construct( $screen_id ) {
		if ( current_user_can( 'gofer_seo_access' ) ) {
			$this->_set_tooltip_html( $screen_id );
		}
	}

	/**
	 * Set this Tooltips HTML.
	 *
	 * Sets the Tooltips HTML according to the screen id being rendered.
	 *
	 * @ignore
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $screen_id Current screen id.
	 */
	private function _set_tooltip_html( $screen_id ) {
		switch ( $screen_id ) {
			case 'toplevel_page_gofer_seo' :
				$this->tooltips_html = $this->get_html_general();
				break;
			case 'gofer-seo_page_gofer_seo_module_social_media' :
				$this->tooltips_html = $this->get_html_social_media();
				break;
			case 'gofer-seo_page_gofer_seo_module_sitemap' :
				$this->tooltips_html = $this->get_html_sitemap();
				break;
			case 'gofer-seo_page_gofer_seo_module_schema_graph' :
				$this->tooltips_html = $this->get_html_schema_graph();
				break;
			case 'gofer-seo_page_gofer_seo_module_crawlers' :
				$this->tooltips_html = $this->get_html_crawlers();
				break;
			case 'gofer-seo_page_gofer_seo_module_advanced' :
				$this->tooltips_html = $this->get_html_advanced();
				break;
			case 'gofer-seo_page_gofer_seo_module_debugger' :
				$this->tooltips_html = $this->get_html_debugger();
				break;
			default:
				$slug = preg_replace( '/^(edit-)/', '', $screen_id );
				if ( post_type_exists( $slug ) ) {
					//Gofer_SEO_Module_Loader::get_active_modules();
					$this->tooltips_html = $this->get_html_post_edit();
				} elseif ( taxonomy_exists( $slug ) ) {
					//Gofer_SEO_Module_Loader::get_active_modules();
					$this->tooltips_html = $this->get_html_term_edit();
				}
		}

		/**
		 * Set Help Text
		 *
		 * @since 1.0.0
		 *
		 * @param array  $this->tooltips_html Contains an array of html for each input label.
		 * @param string $screen_id           The current screen id.
		 */
		$this->tooltips_html = apply_filters( 'gofer_seo_set_tooltips_html', $this->tooltips_html, $screen_id );
	}

	/**
	 * Get Tooltip HTML
	 *
	 * Gets an individual help text if it exists, otherwise an error is returned.
	 * NOTE: Returning an empty string causes issues with the UI.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Input name/slug.
	 * @return string
	 */
	public function get_tooltip_html( $slug ) {
		if ( isset( $this->tooltips_html[ $slug ] ) ) {
			return esc_html( $this->tooltips_html[ $slug ] );
		}
		return 'ERROR: Missing Tooltip HTML: ' . $slug;
	}

	/**
	 * Get Format Shortcodes Info.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_format_shortcodes_info() {
		$shortcodes_examples = array(
			'title'                 => '[title]',
			'site_title'            => '[site_title]',
			'post_title'            => '[post_title source="any"]',
			'post_type_title'       => '[post_type_title]',
			'taxonomy_title'        => '[taxonomy_title]',
			'term_title'            => '[term_title taxonomy="category" source="any"]',
			'archive_title'         => '[archive_title]',
			'description'           => '[description]',
			'site_description'      => '[site_description]',
			'post_description'      => '[post_description source="any"]',
			'post_type_description' => '[post_type_description]',
			'taxonomy_description'  => '[taxonomy_description]',
			'term_description'      => '[term_description taxonomy="category" source="any"]',
			'author_username'       => '[author_username]',
			'author_nicename'       => '[author_nicename]',
			'author_nickname'       => '[author_nickname]',
			'author_display_name'   => '[author_display_name]',
			'author_firstname'      => '[author_firstname]',
			'author_lastname'       => '[author_lastname]',
			'date'                  => '[date format="F j, Y"]',
			'date_modified'         => '[date_modified format="F j, Y"]',
			'year'                  => '[year]',
			'month'                 => '[month]',
			'day'                   => '[day]',
			'post_date'             => '[post_date format="F j, Y"]',
			'post_date_modified'    => '[post_date_modified format="F j, Y"]',
			'post_year'             => '[post_year]',
			'post_month'            => '[post_month]',
			'post_day'              => '[post_day]',
			'current_date'          => '[current_date format="F j, Y"]',
			'current_year'          => '[current_year]',
			'current_month'         => '[current_month]',
			'current_day'           => '[current_day]',
			'search_value'          => '[search_value]',
			'request_uri'           => '[request_uri]',
			'request_words'         => '[request_words]',
			'page'                  => '[page]',
			'pages'                 => '[pages]',
			'meta'                  => '[meta key=""]',
			'site_meta'             => '[site_meta key=""]',
			'post_meta'             => '[post_meta key=""]',
			'term_meta'             => '[term_meta key=""]',
			'user_meta'             => '[user_meta key=""]',
		);
		$shortcodes_descriptions = array(
			'title'                 => sprintf( __( 'The title of the %1$s.', 'gofer-seo' ), __( 'current Context', 'gofer-seo' ) ),
			'site_title'            => sprintf( __( 'The title of the %1$s.', 'gofer-seo' ), __( 'Site', 'gofer-seo' ) ),
			'post_title'            => sprintf( __( 'The title of the %1$s.', 'gofer-seo' ), __( 'Post/Page', 'gofer-seo' ) ),
			'post_type_title'       => sprintf( __( 'The title of the %1$s.', 'gofer-seo' ), __( 'Post Type', 'gofer-seo' ) ),
			'taxonomy_title'        => sprintf( __( 'The title of the %1$s.', 'gofer-seo' ), __( 'Taxonomy', 'gofer-seo' ) ),
			'term_title'            => sprintf( __( 'The title of the %1$s.', 'gofer-seo' ), __( 'Term', 'gofer-seo' ) ),
			'archive_title'         => sprintf( __( 'The title of the %1$s.', 'gofer-seo' ), __( 'Archive', 'gofer-seo' ) ),
			'description'           => sprintf( __( 'The description of the %1$s.', 'gofer-seo' ), __( 'current Context', 'gofer-seo' ) ),
			'site_description'      => sprintf( __( 'The description of the %1$s.', 'gofer-seo' ), __( 'Site', 'gofer-seo' ) ),
			'post_description'      => sprintf( __( 'The description of the %1$s.', 'gofer-seo' ), __( 'Post/Page', 'gofer-seo' ) ),
			'post_type_description' => sprintf( __( 'The description of the %1$s.', 'gofer-seo' ), __( 'Post Type', 'gofer-seo' ) ),
			'taxonomy_description'  => sprintf( __( 'The description of the %1$s.', 'gofer-seo' ), __( 'Taxonomy', 'gofer-seo' ) ),
			'term_description'      => sprintf( __( 'The description of the %1$s.', 'gofer-seo' ), __( 'Term', 'gofer-seo' ) ),
			'author_username'       => sprintf( __( 'The Author\'s %1$s of the %2$s.', 'gofer-seo' ), __( 'Username', 'gofer-seo' ), __( 'Post', 'gofer-seo' ) ),
			'author_nicename'       => sprintf( __( 'The Author\'s %1$s of the %2$s.', 'gofer-seo' ), __( 'Nice-Name', 'gofer-seo' ), __( 'Post', 'gofer-seo' ) ),
			'author_nickname'       => sprintf( __( 'The Author\'s %1$s of the %2$s.', 'gofer-seo' ), __( 'Nickname', 'gofer-seo' ), __( 'Post', 'gofer-seo' ) ),
			'author_display_name'   => sprintf( __( 'The Author\'s %1$s of the %2$s.', 'gofer-seo' ), __( 'Display Name', 'gofer-seo' ), __( 'Post', 'gofer-seo' ) ),
			'author_firstname'      => sprintf( __( 'The Author\'s %1$s of the %2$s.', 'gofer-seo' ), __( 'First Name', 'gofer-seo' ), __( 'Post', 'gofer-seo' ) ),
			'author_lastname'       => sprintf( __( 'The Author\'s %1$s of the %2$s.', 'gofer-seo' ), __( 'Last Name', 'gofer-seo' ), __( 'Post', 'gofer-seo' ) ),
			'date'                  => sprintf( __( 'The %1$s of the %2$s.', 'gofer-seo' ), __( 'date', 'gofer-seo' ), __( 'current Context', 'gofer-seo' ) ),
			'date_modified'         => sprintf( __( 'The %1$s of the %2$s.', 'gofer-seo' ), __( 'modified date', 'gofer-seo' ), __( 'current Context', 'gofer-seo' ) ),
			'year'                  => sprintf( __( 'The %1$s of the %2$s.', 'gofer-seo' ), __( 'year', 'gofer-seo' ), __( 'current Context', 'gofer-seo' ) ),
			'month'                 => sprintf( __( 'The %1$s of the %2$s.', 'gofer-seo' ), __( 'month', 'gofer-seo' ), __( 'current Context', 'gofer-seo' ) ),
			'day'                   => sprintf( __( 'The %1$s of the %2$s.', 'gofer-seo' ), __( 'day', 'gofer-seo' ), __( 'current Context', 'gofer-seo' ) ),
			'post_date'             => sprintf( __( 'The %1$s of the %2$s.', 'gofer-seo' ), __( 'date', 'gofer-seo' ), __( 'Post/Page', 'gofer-seo' ) ),
			'post_date_modified'    => sprintf( __( 'The %1$s of the %2$s.', 'gofer-seo' ), __( 'modified date', 'gofer-seo' ), __( 'Post/Page', 'gofer-seo' ) ),
			'post_year'             => sprintf( __( 'The %1$s of the %2$s.', 'gofer-seo' ), __( 'year', 'gofer-seo' ), __( 'Post/Page', 'gofer-seo' ) ),
			'post_month'            => sprintf( __( 'The %1$s of the %2$s.', 'gofer-seo' ), __( 'month', 'gofer-seo' ), __( 'Post/Page', 'gofer-seo' ) ),
			'post_day'              => sprintf( __( 'The %1$s of the %2$s.', 'gofer-seo' ), __( 'day', 'gofer-seo' ), __( 'Post/Page', 'gofer-seo' ) ),
			'current_date'          => sprintf( __( 'The current %s.', 'gofer-seo' ), __( 'date', 'gofer-seo' ) ),
			'current_year'          => sprintf( __( 'The current %s.', 'gofer-seo' ), __( 'year', 'gofer-seo' ) ),
			'current_month'         => sprintf( __( 'The current %s.', 'gofer-seo' ), __( 'month', 'gofer-seo' ) ),
			'current_day'           => sprintf( __( 'The current %s.', 'gofer-seo' ), __( 'day', 'gofer-seo' ) ),
			'search_value'          => __( 'The search query that was entered.', 'gofer-seo' ),
			'request_uri'           => sprintf( __( 'The original URL path, like %1$s.', 'gofer-seo' ), __( '"/url-that-does-not-exist/"', 'gofer-seo' ) ),
			'request_words'         => sprintf( __( 'The URL path in human readable form, like %1$s.', 'gofer-seo' ), __( '"URL That Does Not Exist"', 'gofer-seo' ) ),
			'page'                  => __( 'The page number.', 'gofer-seo' ),
			'pages'                 => __( 'The total number of pages.', 'gofer-seo' ),
			'meta'                  => sprintf( __( 'The meta data of the %1$s.', 'gofer-seo' ), __( 'current Context', 'gofer-seo' ) ),
			'site_meta'             => sprintf( __( 'The %1$s meta data of the %2$s.', 'gofer-seo' ), __( 'site', 'gofer-seo' ), __( 'Site', 'gofer-seo' ) ),
			'post_meta'             => sprintf( __( 'The %1$s meta data of the %2$s.', 'gofer-seo' ), __( 'post', 'gofer-seo' ), __( 'Post', 'gofer-seo' ) ),
			'term_meta'             => sprintf( __( 'The %1$s meta data of the %2$s.', 'gofer-seo' ), __( 'term', 'gofer-seo' ), __( 'Term', 'gofer-seo' ) ),
			'user_meta'             => sprintf( __( 'The %1$s meta data of the %2$s.', 'gofer-seo' ), __( 'user', 'gofer-seo' ), __( 'User', 'gofer-seo' ) ),
		);

		$shortcodes_info = array();
		foreach ( $shortcodes_examples as $shortcode_slug => $shortcodes_example ) {
			$shortcodes_info[ $shortcode_slug ] = array(
				'example'     => $shortcodes_example,
				'description' => $shortcodes_descriptions[ $shortcode_slug ],
			);
		}

		return $shortcodes_info;
	}

	/**
	 * Tooltip HTML.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return array
	 */
	private function get_html_general() {
		$shortcodes_info = $this->get_format_shortcodes_info();
		$tooltips = array(
			'enable_social_media'                          => __( 'Enables Social Media module.', 'gofer-seo' ),
			'enable_sitemap'                               => __( 'Enables Sitemap module.', 'gofer-seo' ),
			'enable_schema_graph'                          => __( 'Enables Schema Graph module.', 'gofer-seo' ),
			'enable_crawlers'                              => __( 'Enables Crawlers module.', 'gofer-seo' ),
			'enable_advanced'                              => __( 'Enables Advanced module.', 'gofer-seo' ),
			'enable_debugger'                              => __( 'Enables Debugger module.', 'gofer-seo' ),

			// General.
			'show_admin_bar'                               => __( 'Enabled will display Gofer SEO in the top admin bar.', 'gofer-seo' ),
			'enable_canonical'                             => __( 'Enabled will automatically generate Canonical URLs on the WordPress installation. This will help to prevent duplicate content penalties by Google.', 'gofer-seo' ),
			'enable_canonical_paginated'                   => __( 'Enabled will set the Canonical URL for all paginated content to the first page.', 'gofer-seo' ),

			// Site/Defaults.
			'enable_site_title'                            => __( 'Enabled will use the Site Title setting for your homepage.', 'gofer-seo' ),
			'enable_site_description'                      => __( 'Enabled will use the Description setting for your homepage.', 'gofer-seo' ),
			'use_static_homepage'                          => __( 'Whether to use your static homepage.', 'gofer-seo' ),
			'site_name'                                    => __( 'The name the website represents. Could be domain name, company name, or person.', 'gofer-seo' ),
			'site_title'                                   => __( 'The Homepage title. If left unset, the WordPress Site Title setting (found in WordPress under Settings > General > Site Title) will be used.', 'gofer-seo' ),
			'site_description'                             => __( 'The Homepage description.', 'gofer-seo' ),
			'site_keywords'                                => __( 'Enter the most important keywords separated by a comma.', 'gofer-seo' ),
			'site_image'                                   => __( 'The site image, and default image to use.', 'gofer-seo' ),
			'site_logo'                                    => __( 'The site logo, or avatar, that best represents the website.', 'gofer-seo' ),
			'site_title_format'                            =>
				__( 'This controls the format of the title tag for your Homepage.', 'gofer-seo' ) . '<br />' .
				__( 'The following macros are supported:', 'gofer-seo' ) .
				'<dl>' .
					'<dt>' . $shortcodes_info['site_title']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['site_title']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['site_description']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['site_description']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_title']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_title']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['author_username']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['author_username']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['author_display_name']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['author_display_name']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['author_firstname']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['author_firstname']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['author_lastname']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['author_lastname']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['current_date']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['current_date']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['current_year']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['current_year']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['current_month']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['current_month']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['meta']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['meta']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['site_meta']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['site_meta']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_meta']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_meta']['description'] . '</dd>' .
				'</dl>',
			'site_description_format'                      =>
				__( 'This controls the format of Meta Descriptions. The following macros are supported:', 'gofer-seo' ) .
				'<dl>' .
					'<dt>' . $shortcodes_info['site_title']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['site_title']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['site_description']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['site_description']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['description']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['description']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_title']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_title']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_description']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_description']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['current_date']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['current_date']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['current_year']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['current_year']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['current_month']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['current_month']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['date']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['date']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['year']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['year']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['month']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['month']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['meta']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['meta']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['site_meta']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['site_meta']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_meta']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_meta']['description'] . '</dd>' .

//					'<dt>%wp_title%</dt>' .
//					'<dd>' . __( 'The original WordPress title', 'gofer-seo' ) . '</dd>' .
//					'<dt>%current_month_i18n%</dt>' .
//					'<dd>' . sprintf( __( 'The current %s (localized)', 'gofer-seo' ), __( 'month', 'gofer-seo' ) ) . '</dd>' .
				'</dl>',
			'home_meta_tags'                               => __( 'Text entered here will be applied to the header of the home page if you have set a static page in Settings, Reading, Front Page Displays. You can enter whatever additional headers you want here, even references to scripts & stylesheets.', 'gofer-seo' ),
			'posts_page_meta_tags'                         => __( 'Text entered here will be applied to the header of the posts page if you have Front page displays your latest posts selected in Settings, Reading. You can enter whatever additional headers you want here, even references to scripts & stylesheets.', 'gofer-seo' ),

			// Image.
			'image_source'                                 => __( 'Where to automatically fetch the image source.', 'gofer-seo' ),
			'image_source_meta_keys'                       => __( 'Meta Keys used for storing an image URL.', 'gofer-seo' ),

			// Post Type Content.
			'enable_post_types'                            => sprintf( __( 'Enable which Post Types you want to use %s with.', 'gofer-seo' ), GOFER_SEO_NAME ),
			'post_type_settings'                           => __( 'Settings to use with each Post Type.', 'gofer-seo' ),

			// Taxonomy Content.
			'enable_taxonomies'                            => sprintf( __( 'Enable which Taxonomies you want to use %s with.', 'gofer-seo' ), GOFER_SEO_NAME ),
			'taxonomy_settings'                            => __( 'Settings to use with each Taxonomy.', 'gofer-seo' ),

			// Archive Content.
			'archive_post_title_format'                    =>
				__( 'This controls the format of the title tag for Custom Post Archives.', 'gofer-seo' ) . '<br />' .
				__( 'The following macros are supported:', 'gofer-seo' ) .
				'<dl>' .
					'<dt>' . $shortcodes_info['site_title']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['site_title']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['site_description']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['site_description']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['archive_title']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['archive_title']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_type_title']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_type_title']['description'] . '</dd>' .
				'</dl>',
			'archive_taxonomy_term_title_format'           =>
				__( 'This controls the format of the title tag for Custom Post Archives.', 'gofer-seo' ) . '<br />' .
				__( 'The following macros are supported:', 'gofer-seo' ) .
				'<dl>' .
					'<dt>' . $shortcodes_info['site_title']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['site_title']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['site_description']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['site_description']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['archive_title']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['archive_title']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['taxonomy_title']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['taxonomy_title']['description'] . '</dd>' .
				'</dl>',
			'archive_date_title_format'                    =>
				__( 'This controls the format of the title tag for Date Archives.', 'gofer-seo' ) . '<br />' .
				__( 'The following macros are supported:', 'gofer-seo' ) .
				'<dl>' .
					'<dt>' . $shortcodes_info['site_title']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['site_title']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['site_description']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['site_description']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['archive_title']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['archive_title']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_title']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_title']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['date']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['date']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['year']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['year']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['month']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['month']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['day']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['day']['description'] . '</dd>' .
				'</dl>',
			'archive_author_title_format'                  =>
				__( 'This controls the format of the title tag for Author Archives.', 'gofer-seo' ) . '<br />' .
				__( 'The following macros are supported:', 'gofer-seo' ) .
				'<dl>' .
					'<dt>' . $shortcodes_info['site_title']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['site_title']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['site_description']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['site_description']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['archive_title']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['archive_title']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['author_display_name']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['author_display_name']['description'] . '</dd>' .
				'</dl>',
			'archive_date_enable_noindex'                  => __( 'Check this to ask search engines not to index Date Archives. Useful for avoiding duplicate content.', 'gofer-seo' ),
			'archive_author_enable_noindex'                => __( 'Check this to ask search engines not to index Author Archives. Useful for avoiding duplicate content.', 'gofer-seo' ),

			// Search Content.
			'search_title_format'                          =>
				__( 'This controls the format of the title tag for the Search page.', 'gofer-seo' ) . '<br />' .
				__( 'The following macros are supported:', 'gofer-seo' ) .
				'<dl>' .
					'<dt>' . $shortcodes_info['site_title']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['site_title']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['site_description']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['site_description']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['search_value']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['search_value']['description'] . '</dd>' .
				'</dl>',
			'search_enable_noindex'                        => __( 'Check this to ask search engines not to index the Search page. Useful for avoiding duplicate content.', 'gofer-seo' ),

			// 404 Content.
			'404_title_format'                             =>
				__( 'This controls the format of the title tag for the 404 page.', 'gofer-seo' ) . ' <br />' .
				__( 'The following macros are supported:', 'gofer-seo' ) .
				'<dl>' .
					'<dt>' . $shortcodes_info['site_title']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['site_title']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['site_description']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['site_description']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['request_uri']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['request_uri']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['request_words']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['request_words']['description'] . '</dd>' .
				'</dl>',
			'404_enable_noindex'                           => __( 'Check this to ask search engines not to index the 404 page.', 'gofer-seo' ),

			// Additional Content.
			'paginate_format'                              =>
				__( 'This string gets appended/prepended to titles of paged index pages (like home or archive pages).', 'gofer-seo' ) .
				__( 'The following macros are supported:', 'gofer-seo' ) .
				'<dl>' .
					'<dt>' . $shortcodes_info['page']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['page']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['pages']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['pages']['description'] . '</dd>' .
				'</dl>',
			'paginate_enable_noindex'                      => __( 'Check this to ask search engines not to index paginated pages/posts. Useful for avoiding duplicate content.', 'gofer-seo' ),

			// Verification.
			'verify_google'                                => __( 'Enter your verification code here to verify your site with Google Search Console.', 'gofer-seo' ),
			'verify_bing'                                  => __( 'Enter your verification code here to verify your site with Bing Webmaster Tools.', 'gofer-seo' ),
			'verify_pinterest'                             => __( 'Enter your verification code here to verify your site with Pinterest.', 'gofer-seo' ),
			'verify_yandex'                                => __( 'Enter your verification code here to verify your site with Yandex Webmaster Tools.', 'gofer-seo' ),
			'verify_baidu'                                 => __( 'Enter your verification code here to verify your site with Baidu Webmaster Tools.', 'gofer-seo' ),

			// Analytics
			'google_analytics'                             => __( 'Settings for Google Analytics', 'gofer-seo' ),
			'google_analytics-ua_id'                       => __( 'Enter your Google Analytics ID here to track visitor behavior on your site using Google Analytics.', 'gofer-seo' ),
			'google_analytics-enable_advanced_settings'    => __( 'Check to use advanced Google Analytics options.', 'gofer-seo' ),
			'google_analytics-track_domain'                => __( 'Enter your domain name without the http:// to set your cookie domain.', 'gofer-seo' ),
			'google_analytics-enable_track_multi_domains'  => __( 'Use this option to enable tracking of multiple or additional domains.', 'gofer-seo' ),
			'google_analytics-exclude_user_roles'          => __( 'Exclude logged-in users from Google Analytics tracking by role.', 'gofer-seo' ),
			'google_analytics-enable_enhance_ecommerce'    => __( 'This enables support for the Enhanced Ecommerce in Google Analytics.', 'gofer-seo' ),
			'google_analytics-enable_enhance_link_attributes' => __( 'This enables support for the Enhanced Link Attribution in Google Analytics.', 'gofer-seo' ),
			'google_analytics-enable_track_outbound_links' => __( 'Check this if you want to track outbound links with Google Analytics.', 'gofer-seo' ),
			'google_analytics-enable_track_outbound_forms' => __( 'Check this if you want to track outbound forms with Google Analytics.', 'gofer-seo' ),
			'google_analytics-enable_track_social_media'   => __( 'Check this if you want to track social media with Google Analytics.', 'gofer-seo' ),
			'google_analytics-enable_track_events'         => __( 'Check this if you want to track events with Google Analytics.', 'gofer-seo' ),
			'google_analytics-enable_track_url_changes'    => __( 'Check this if you want to track URL changes for single pages with Google Analytics.', 'gofer-seo' ),
			'google_analytics-enable_track_media_query'    => __( 'Check this if you want to track media query matching and queries with Google Analytics.', 'gofer-seo' ),
			'google_analytics-enable_track_page_visibility' => __( 'Check this if you want to track how long pages are in visible state with Google Analytics.', 'gofer-seo' ),
			'google_analytics-enable_track_impressions'    => __( 'Check this if you want to track when elements are visible within the viewport with Google Analytics.', 'gofer-seo' ),
			'google_analytics-enable_track_max_scroll'     => __( 'Check this if you want to track how far down a user scrolls a page with Google Analytics.', 'gofer-seo' ),
			'google_analytics-enable_advertising_features' => __( 'This enables support for the Display Advertiser Features in Google Analytics.', 'gofer-seo' ),
			'google_analytics-enable_anonymize_ip'         => __( 'This enables support for IP Anonymization in Google Analytics.', 'gofer-seo' ),
			'google_analytics-enable_clean_url'            => __( 'Check this if you want to ensure consistency in URL paths reported to Google Analytics.', 'gofer-seo' ),

			'google_tag_manager'                           => __( 'Settings for Google Tag Manager.', 'gofer-seo' ),
			'google_tag_manager-gtm_id'                    => __( 'Enter your Google Tag Manager Container ID to deploy your marketing tag in the source code. Ignore this setting if you are not familiar with Google Tag Manager.', 'gofer-seo' ),

			'generate_keywords'                            => __( 'Settings for Keyword Generator.', 'gofer-seo' ),
			'generate_keywords-enable_generator'           => __( 'This option allows you to toggle the use of Meta Keywords throughout the whole of the site.', 'gofer-seo' ),
			'generate_keywords-enable_on_static_posts_page' => __( 'Check this if you want your keywords on your Posts page (set in WordPress under Settings, Reading, Front Page Displays) and your archive pages to be dynamically generated from the keywords of the posts showing on that page.  If unchecked, it will use the keywords set in the edit page screen for the posts page.', 'gofer-seo' ),
			'generate_keywords-use_taxonomies'             => __( 'Check this if you want your taxonomies for a given post used as the Meta Keywords for this post (in addition to any keywords you specify on the Edit Post screen).', 'gofer-seo' ),

			'generate_description'                         => __( 'Settings for Description Generator.', 'gofer-seo' ),
			'generate_description-enable_generator'        => __( 'Check this and your Meta Descriptions for any Post Type will be auto-generated using the Post Excerpt, or the first 160 characters of the post content if there is no Post Excerpt. You can overwrite any auto-generated Meta Description by editing the post or page.', 'gofer-seo' ),
			'generate_description-use_excerpt'             => __( 'This option will auto generate your meta descriptions from your post content instead of your post excerpt. This is useful if you want to use your content for your autogenerated meta descriptions instead of the excerpt. WooCommerce users should read the documentation regarding this setting.', 'gofer-seo' ),
			'generate_description-use_content'             => __( 'Enable this to use the content to generate a description. Warning: Using this is more resource intense.', 'gofer-seo' ),

			// Advanced.
			'use_wp_title'                                 => __( 'Use wp_title to get the title used by the theme; this is disabled by default. If you use this option, set your title formats appropriately, as your theme might try to do its own title SEO as well.', 'gofer-seo' ),
			'enable_title_shortcodes'                      => __( 'Enable this to run shortcodes within the title.', 'gofer-seo' ),
			'enable_description_shortcodes'                => __( 'Check this and shortcodes will get executed for descriptions auto-generated from content.', 'gofer-seo' ),
			'enable_trim_description'                      => __( 'Check this to prevent your Description from being truncated regardless of its length.', 'gofer-seo' ),
			'show_paginate_descriptions'                   => __( 'Check this and your Meta Descriptions will be removed from page 2 or later of paginated content.', 'gofer-seo' ),
			'enable_attachment_redirect_to_parent'         => __( 'Redirect attachment pages to post parent.', 'gofer-seo' ),
			'admin_menu_order'                             => __( 'The numeric menu order. This controls the location on the admin menu.', 'gofer-seo' ),
			'exclude_urls'                                 => sprintf(
				__( 'Enter a comma separated list of pages here to be excluded by %1$s. This is helpful when using plugins which generate their own non-WordPress dynamic pages.  Ex: %3$s/forum/, /contact/%4$s%5$sFor instance, if you want to exclude the virtual pages generated by a forum plugin, all you have to do is add "forum" or "/forum" or "/forum/" or any URL with the word "forum" in it here, such as "%2$s/forum" or "%2$s/forum/example-page", and it will be excluded.', 'gofer-seo' ),
				GOFER_SEO_NAME,
				'http://example.com',
				'<em>',
				'</em>',
				'<br />'
			),
		);

		$tooltips = array_merge( $tooltips, $this->get_html_general_post_type_content() );
		$tooltips = array_merge( $tooltips, $this->get_html_general_taxonomy_content() );

		return $tooltips;
	}

	/**
	 * Get HTML - General - Post Type Content.
	 *
	 * @access private
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_html_general_post_type_content() {
		$shortcodes_info = $this->get_format_shortcodes_info();
		$child_tooltips = array(
			'enable_editor_meta_box' => __( 'Enable SEO Settings meta box within the post type editor.', 'gofer-seo' ),
			'title_format'           =>
				__( 'This controls the format of the title tag for Posts.', 'gofer-seo' ) . '<br />' .
				__( 'The following macros are supported:', 'gofer-seo' ) .
				'<dl>' .
					'<dt>' . $shortcodes_info['site_title']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['site_title']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['site_description']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['site_description']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_title']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_title']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['term_title']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['term_title']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['author_username']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['author_username']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['author_nicename']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['author_nicename']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['author_firstname']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['author_firstname']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['author_lastname']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['author_lastname']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['current_date']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['current_date']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['current_year']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['current_year']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['current_month']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['current_month']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['current_day']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['current_day']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_date']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_date']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_year']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_year']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_month']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_month']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_day']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_day']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['meta']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['meta']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_meta']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_meta']['description'] . '</dd>' .
				'</dl>',
			'description_format'     =>
				__( 'This controls the format of Meta Descriptions. The following macros are supported:', 'gofer-seo' ) .
				'<dl>' .
					'<dt>' . $shortcodes_info['site_title']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['site_title']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['site_description']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['site_description']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['description']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['description']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_title']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_title']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_description']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_description']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['current_date']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['current_date']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['current_year']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['current_year']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['current_month']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['current_month']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['current_day']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['current_day']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_date']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_date']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_year']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_year']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_month']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_month']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_day']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_day']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['meta']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['meta']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_meta']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_meta']['description'] . '</dd>' .
				'</dl>',
			'enable_noindex'         => __( 'Set the default NOINDEX setting for each Post Type.', 'gofer-seo' ),
			'enable_nofollow'        => __( 'Set the default NOFOLLOW setting for each Post Type.', 'gofer-seo' ),
			'custom_meta_tags'       => __( 'Custom meta to apply to the header of all Posts. You can enter whatever additional headers you want here, even references to scripts & stylesheets.', 'gofer-seo' ),
		);
		$post_types = gofer_seo_get_post_types( array(), 'name' );

		$tooltips = array();
		foreach ( $post_types as $post_type ) {
			foreach ( $child_tooltips as $slug => $child_tooltip ) {
				$tooltips[ 'post_type_settings-' . $post_type . '-' . $slug ] = $child_tooltip;
			}
		}

		return $tooltips;
	}

	/**
	 * Get HTML - General - Taxonomy Content.
	 *
	 * @access private
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_html_general_taxonomy_content() {
		$shortcodes_info = $this->get_format_shortcodes_info();
		$child_tooltips = array(
			'enable_editor_meta_box' => __( 'Enable SEO Settings meta box within the taxonomy editor.', 'gofer-seo' ),
			'title_format'           =>
				__( 'The following macros are supported:', 'gofer-seo' ) .
				'<dl>' .
					'<dt>' . $shortcodes_info['author_username']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['author_username']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['author_nicename']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['author_nicename']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['author_display_name']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['author_display_name']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['author_firstname']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['author_firstname']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['author_lastname']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['author_lastname']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['current_date']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['current_date']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['current_year']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['current_year']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['current_month']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['current_month']['description'] . '</dd>' .
				'</dl>',
			'description_format'     =>
				__( 'This controls the format of Meta Descriptions. The following macros are supported:', 'gofer-seo' ) .
				'<dl>' .
					'<dt>' . $shortcodes_info['site_title']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['site_title']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['site_description']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['site_description']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['description']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['description']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_title']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_title']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_description']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_description']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['current_date']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['current_date']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['current_year']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['current_year']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['current_month']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['current_month']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['current_day']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['current_day']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_date']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_date']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_year']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_year']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_month']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_month']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['post_day']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['post_day']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['meta']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['meta']['description'] . '</dd>' .
					'<dt>' . $shortcodes_info['term_meta']['example'] . '</dt>' .
					'<dd>' . $shortcodes_info['term_meta']['description'] . '</dd>' .
				'</dl>',
			'enable_noindex'         => __( 'Set the default NOINDEX setting for each Taxonomy.', 'gofer-seo' ),
			'enable_nofollow'        => __( 'Set the default NOFOLLOW setting for each Taxonomy.', 'gofer-seo' ),
		);

		$taxonomies = gofer_seo_get_taxonomies( array(), 'name' );

		$tooltips = array();
		foreach ( $taxonomies as $taxonomy ) {
			foreach ( $child_tooltips as $slug => $child_tooltip ) {
				$tooltips[ 'taxonomy_settings-' . $taxonomy . '-' . $slug ] = $child_tooltip;
			}
		}

		return $tooltips;
	}

	/**
	 * Tooltip HTML.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return array
	 */
	private function get_html_social_media() {
		$tooltips = array(
			// Site/Default.
			'enable_site_title'                        => sprintf( __( 'Checking this box will use the Home Title and Home Description set in %s, General Settings as the Open Graph title and description for your home page.', 'gofer-seo' ), GOFER_SEO_NAME ),
			'enable_site_description'                  => __( 'Checking this box will use the Site Description.', 'gofer-seo' ),
			'site_name'                                => __( 'The Site Name is the name that is used to identify your website.', 'gofer-seo' ),
			'site_title'                               => __( 'The Home Title is the Open Graph title for your home page.', 'gofer-seo' ),
			'site_description'                         => __( 'The Home Description is the Open Graph description for your home page.', 'gofer-seo' ),
			'site_image'                               => __( 'The Home Image is the Open Graph image for your home page.', 'gofer-seo' ),

			// Image.
			'default_image'                            => __( 'This option sets a default image that can be used for the Open Graph image. You can upload an image, select an image from your Media Library or paste the URL of an image here.', 'gofer-seo' ),
			'default_image_width'                      => __( 'This option lets you set a default width for your images, where unspecified.', 'gofer-seo' ),
			'default_image_height'                     => __( 'This option lets you set a default height for your images, where unspecified.', 'gofer-seo' ),
			'image_source'                             => __( 'This option lets you choose which image will be displayed by default for the Open Graph image. You may override this on individual posts.', 'gofer-seo' ),
			'image_source_meta_keys'                   => __( 'Enter the name of a custom field (or multiple field names separated by commas) to use that field to specify the Open Graph image on Pages or Posts.', 'gofer-seo' ),

			// Post Type Content.
			'enable_post_types'                        => __( 'Select which Post Types you want to set Open Graph meta values for.', 'gofer-seo' ),

			// Facebook.
			'fb_admin_id'                              => __( 'Enter your Facebook Admin ID here. You can enter multiple IDs separated by a comma.', 'gofer-seo' ),
			'fb_app_id'                                => __( 'Enter your Facebook App ID here. Information about how to get your Facebook App ID can be found at https://developers.facebook.com/docs/apps/register', 'gofer-seo' ),
			'fb_publisher_fb_url'                      => __( 'Link articles to the Facebook page associated with your website.', 'gofer-seo' ),
			'fb_use_post_author_fb_url'                => __( 'Allows your authors to be identified by their Facebook pages as content authors on the Opengraph meta for their articles.', 'gofer-seo' ),
			'fb_post_type_settings'                    => __( 'Facebook settings for each post type.', 'gofer-seo' ),
			//'fb_post_type_settings-post-fb_object_type' => __( 'Choose a default value that best describes the content of your post type.', 'gofer-seo' ),

			// Twitter.
			'twitter_card_type'                        => __( 'Select the default type of Twitter Card to display.', 'gofer-seo' ),
			'twitter_username'                         => __( 'Enter the Twitter username associated with your website here.', 'gofer-seo' ),
			'twitter_use_post_author_twitter_username' => __( 'Allows your authors to be identified by their Twitter usernames as content creators on the Twitter cards for their posts.', 'gofer-seo' ),

			// Generate.
			'generate_keywords'                        => __( 'Automatically generate article tags for Facebook type article when not provided.', 'gofer-seo' ),
			'generate_keywords-enable_generator'       => __( 'Use keywords in generated article tags.', 'gofer-seo' ),
			'generate_keywords-use_keywords'           => __( 'Enable to use keywords as part of generating keywords.', 'gofer-seo' ),
			'generate_keywords-use_taxonomies'         => __( 'Enable to use taxonomies as part of generating keywords.', 'gofer-seo' ),
			'generate_description'                     => __( 'This option will auto generate your Open Graph descriptions from your post content instead of your post excerpt. WooCommerce users should read the documentation regarding this setting.', 'gofer-seo' ),
			'generate_description-enable_generator'    => __( 'Check this is enable the description generator.', 'gofer-seo' ),
			'generate_description-use_excerpt'         => __( 'Enable to use the excerpt with the generated description.', 'gofer-seo' ),
			'generate_description-use_content'         => __( 'Enable to use the content with the generated description.', 'gofer-seo' ),

			// Advanced.
			'enable_title_shortcodes'                  => __( 'Run shortcodes that appear in social title meta tags.', 'gofer-seo' ),
			'enable_description_shortcodes'            => __( 'Run shortcodes that appear in social description meta tags.', 'gofer-seo' ),
		);

		$tooltips = array_merge( $tooltips, $this->get_html_social_media_facebook() );

		return $tooltips;
	}

	/**
	 * Get HTML - Social Media - Facebook.
	 *
	 * @access private
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_html_social_media_facebook() {
		$child_tooltips = array(
			'fb_object_type' => __( 'Choose a default value that best describes the content of your post type.', 'gofer-seo' ),
		);

		$post_types = gofer_seo_get_post_types( array(), 'name' );

		$tooltips = array();
		foreach ( $post_types as $post_type ) {
			foreach ( $child_tooltips as $slug => $child_tooltip ) {
				$tooltips[ 'fb_post_type_settings-' . $post_type . '-' . $slug ] = $child_tooltip;
			}
		}

		return $tooltips;
	}

	/**
	 * Tooltip HTML.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return array
	 */
	private function get_html_sitemap() {
		$tooltips = array(
			// General Settings.
			'enable_news_sitemap'               => __( 'Select which Post Types should appear in your Google News sitemap. This sitemap only includes posts that were published in the last 48 hours.', 'gofer-seo' ),
			'enable_rss_sitemap'                => __( 'Generate an RSS sitemap in addition to the regular XML Sitemap.', 'gofer-seo' ),
			'enable_indexes'                    => __( 'Organize sitemap entries into distinct files in your sitemap. We recommend you enable this setting if your sitemap contains more than 1,000 URLs.', 'gofer-seo' ),
			'posts_per_sitemap'                 => __( 'Allows you to specify the maximum number of posts in a sitemap (up to 50,000).', 'gofer-seo' ),

			// Site/Defaults.
			'site_priority'                     => sprintf( __( 'Manually set the %1$s of your %2$s.', 'gofer-seo' ), __( 'priority', 'gofer-seo' ), __( 'Site', 'gofer-seo' ) ),
			'site_frequency'                    => sprintf( __( 'Manually set the %1$s of your %2$s.', 'gofer-seo' ), __( 'frequency', 'gofer-seo' ), __( 'Site', 'gofer-seo' ) ),
			'post_type_default_priority'        => sprintf( __( 'Manually set the %1$s of your %2$s.', 'gofer-seo' ), __( 'priority', 'gofer-seo' ), __( 'Posts', 'gofer-seo' ) ),
			'post_type_default_frequency'       => sprintf( __( 'Manually set the %1$s of your %2$s.', 'gofer-seo' ), __( 'frequency', 'gofer-seo' ), __( 'Posts', 'gofer-seo' ) ),
			'taxonomy_default_priority'         => sprintf( __( 'Manually set the %1$s of your %2$s.', 'gofer-seo' ), __( 'priority', 'gofer-seo' ), __( 'Terms', 'gofer-seo' ) ),
			'taxonomy_default_frequency'        => sprintf( __( 'Manually set the %1$s of your %2$s.', 'gofer-seo' ), __( 'frequency', 'gofer-seo' ), __( 'Terms', 'gofer-seo' ) ),

			// Post Type Content.
			'enable_post_types'                 => __( 'Select which Post Types to enable the sitemap features.', 'gofer-seo' ),
			'post_type_settings'                => __( 'Settings for each post type.', 'gofer-seo' ),

			// Taxonomy Content.
			'enable_taxonomies'                 => __( 'Select which Taxonomies to enable the sitemap features.', 'gofer-seo' ),
			'taxonomy_settings'                 => __( 'Settings for each taxonomy.', 'gofer-seo' ),

			// Archive.
			'archive_settings-priority'         => sprintf( __( 'Manually set the %1$s of your %2$s.', 'gofer-seo' ), __( 'priority', 'gofer-seo' ), __( 'Archive', 'gofer-seo' ) ),
			'archive_settings-frequency'        => sprintf( __( 'Manually set the %1$s of your %2$s.', 'gofer-seo' ), __( 'frequency', 'gofer-seo' ), __( 'Archive', 'gofer-seo' ) ),
			'enable_archive_date'               => __( 'Include Date Archives in your sitemap.', 'gofer-seo' ),
			'archive_date_settings'             => __( 'Manually set Date Archive settings.', 'gofer-seo' ),
			'archive_date_settings-priority'    => sprintf( __( 'Manually set the %1$s of your %2$s.', 'gofer-seo' ), __( 'priority', 'gofer-seo' ), __( 'Date Archive', 'gofer-seo' ) ),
			'archive_date_settings-frequency'   => sprintf( __( 'Manually set the %1$s of your %2$s.', 'gofer-seo' ), __( 'frequency', 'gofer-seo' ), __( 'Date Archive', 'gofer-seo' ) ),
			'enable_archive_author'             => __( 'Include Author Archives in your sitemap.', 'gofer-seo' ),
			'archive_author_settings'           => __( 'Manually set Author Archive settings.', 'gofer-seo' ),
			'archive_author_settings-priority'  => sprintf( __( 'Manually set the %1$s of your %2$s.', 'gofer-seo' ), __( 'priority', 'gofer-seo' ), __( 'Author Archive', 'gofer-seo' ) ),
			'archive_author_settings-frequency' => sprintf( __( 'Manually set the %1$s of your %2$s.', 'gofer-seo' ), __( 'frequency', 'gofer-seo' ), __( 'Author Archive', 'gofer-seo' ) ),

			// Include.
			'include_urls'                      => __( 'URL to include. This field only accepts absolute URLs with the protocol specified.', 'gofer-seo' ),
			'include_urls-url'                  => __( 'The URL to include.', 'gofer-seo' ),
			'include_urls-priority'             => sprintf( __( 'The %1$s of the %2$s.', 'gofer-seo' ), __( 'priority', 'gofer-seo' ), __( 'URL', 'gofer-seo' ) ),
			'include_urls-frequency'            => sprintf( __( 'The %1$s of the %2$s.', 'gofer-seo' ), __( 'frequency', 'gofer-seo' ), __( 'URL', 'gofer-seo' ) ),
			'include_urls-modified_date'        => sprintf( __( 'The %1$s of the %2$s.', 'gofer-seo' ), __( 'last Modified Date', 'gofer-seo' ), __( 'URL', 'gofer-seo' ) ),

			// Exclude.
			'exclude_post_ids'                  => __( 'The page IDs, separated by commas, to exclude from the sitemap.', 'gofer-seo' ),
			'exclude_term_ids'                  => __( 'Exclude terms (category, tags, & custom taxonomies) from the sitemap.', 'gofer-seo' ),

			// Advanced.
			'include_images'                    => __( 'Include Images in your sitemap.', 'gofer-seo' ),
		);

		$tooltips = array_merge( $tooltips, $this->get_html_sitemap_post_type_content() );
		$tooltips = array_merge( $tooltips, $this->get_html_sitemap_taxonomy_content() );

		return $tooltips;
	}

	/**
	 * Get HTML - Sitemap - Post Type Content.
	 *
	 * @access private
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_html_sitemap_post_type_content() {
		$child_tooltips = array(
			'show_on'                  => sprintf( __( 'Select which sitemap to display the %s on.', 'gofer-seo' ), __( 'Posts', 'gofer-seo' ) ),
			'show_on-standard_sitemap' => __( 'Enables posts to display on the standard sitemap.', 'gofer-seo' ),
			'show_on-news_sitemap'     => __( 'Enables posts published within the last 48 hours to display on the news sitemap.', 'gofer-seo' ),
			'show_on-rss'              => __( 'Enables posts to display on the RSS feed.', 'gofer-seo' ),
			'priority'                 => sprintf( __( 'Manually set the %1$s of your %2$s.', 'gofer-seo' ), __( 'priority', 'gofer-seo' ), __( 'Posts', 'gofer-seo' ) ),
			'frequency'                => sprintf( __( 'Manually set the %1$s of your %2$s.', 'gofer-seo' ), __( 'frequency', 'gofer-seo' ), __( 'Posts', 'gofer-seo' ) ),
		);

		$post_types = gofer_seo_get_post_types( array(), 'name' );

		$tooltips = array();
		foreach ( $post_types as $post_type ) {
			foreach ( $child_tooltips as $slug => $child_tooltip ) {
				$tooltips[ 'post_type_settings-' . $post_type . '-' . $slug ] = $child_tooltip;
			}
		}

		return $tooltips;
	}

	/**
	 * Get HTML - Sitemap - Taxonomy Content.
	 *
	 * @access private
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_html_sitemap_taxonomy_content() {
		$child_tooltips = array(
			'show_on'                  => sprintf( __( 'Select which sitemap to display the %s on.', 'gofer-seo' ), __( 'Taxonomies', 'gofer-seo' ) ),
			'show_on-standard_sitemap' => __( 'Enables taxonomy to display on the standard sitemap.', 'gofer-seo' ),
			'priority'                 => sprintf( __( 'Manually set the %1$s of your %2$s.', 'gofer-seo' ), __( 'priority', 'gofer-seo' ), __( 'Taxonomy', 'gofer-seo' ) ),
			'frequency'                => sprintf( __( 'Manually set the %1$s of your %2$s.', 'gofer-seo' ), __( 'frequency', 'gofer-seo' ), __( 'Taxonomy', 'gofer-seo' ) ),
		);

		$taxonomies = gofer_seo_get_taxonomies( array(), 'name' );

		$tooltips = array();
		foreach ( $taxonomies as $taxonomy ) {
			foreach ( $child_tooltips as $slug => $child_tooltip ) {
				$tooltips[ 'taxonomy_settings-' . $taxonomy . '-' . $slug ] = $child_tooltip;
			}
		}

		return $tooltips;
	}

	/**
	 * Tooltip HTML.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return array
	 */
	private function get_html_schema_graph() {
		$tooltips = array(
			'site_represents'          => __( 'Select whether your website is primarily for a person or an organization.', 'gofer-seo' ),
			'organization_name'        => __( 'Enter your organization or business name.', 'gofer-seo' ),
			'organization_logo'        => __( 'Add a logo that represents your organization or business. The image must be in PNG, JPG or GIF format and a minimum size of 112px by 112px. If no image is selected, then the plugin will try to use the logo in the Customizer settings.', 'gofer-seo' ),
			'phone_contact_type'       => __( 'Select the type of contact for the phone number you have entered.', 'gofer-seo' ),
			'phone_number'             => __( 'Enter the primary phone number your organization or business. You must include the country code and the phone number must use the standard format for your country, for example: 1-888-888-8888.', 'gofer-seo' ),
			'person_user_id'           => __( 'Choose the user the site represents. Only users with the role of Author, Editor or Administrator will be listed here. Alternatively, you can choose Manually Enter to manually enter the site owner\'s name.', 'gofer-seo' ),
			'person_custom_name'       => __( 'Enter the name of the site owner here.', 'gofer-seo' ),
			'person_custom_image'      => __( 'Upload or enter the URL for the site owner\'s image or avatar.', 'gofer-seo' ),
			'social_profile_urls'      => __( 'Add the URLs for your website\'s social profiles here (Facebook, Twitter, Instagram, LinkedIn, etc.), one per line. These may be used in rich search results such as Google Knowledge Graph.', 'gofer-seo' ),
			'show_search_results_page' => __( 'Select this to output markup that notifies Google to display the Sitelinks Search Box within certain search results.', 'gofer-seo' ),
		);

		return $tooltips;
	}

	/**
	 * Tooltip HTML.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return array
	 */
	private function get_html_crawlers() {
		$tooltips = array(
			// General Settings.
			'enable_block_user_agent' => __( 'Block requests from blacklisted user-agents that are known to misbehave.', 'gofer-seo' ),
			'enable_block_referer'    => __( 'Block requests with a blacklisted referers using HTTP.', 'gofer-seo' ),
			'enable_log_blocked_bots' => __( 'Log and show recent requests from blocked bots.', 'gofer-seo' ),

			// Custom List.
			'use_custom_blacklist'    => __( 'Check this to edit the list of disallowed user agents for blocking bad bots.', 'gofer-seo' ),
			'user_agent_blacklist'    => __( 'This is the list of disallowed user agents used for blocking potentially malicious bots.', 'gofer-seo' ),
			'referer_blacklist'       => __( 'This is the list of disallowed referers used for blocking potentially malicious bots.', 'gofer-seo' ),

			// Robots.txt.
			'enable_override_robots_txt'              => __( 'Whether to include the default WP Robots.txt.', 'gofer-seo' ),
			'robots_txt_rules-user_agents-user_agent' => __( 'The name of the user-agent aka crawler.', 'gofer-seo' ),
			'robots_txt_rules-user_agents-rule_type'  => __( 'The type of action you want to apply.', 'gofer-seo' ),
			'robots_txt_rules-user_agents-rule_value' => __( 'The relative URL.', 'gofer-seo' ),
		);

		return $tooltips;
	}

	/**
	 * Tooltip HTML.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return array
	 */
	private function get_html_advanced() {
		$tooltips = array(
			'php_memory_limit'           => __( 'This setting allows you to raise your PHP memory limit to a reasonable value. Note: WordPress core and other WordPress plugins may also change the value of the memory limit.', 'gofer-seo' ),
			'php_max_execution_time'     => __( 'This setting allows you to raise your PHP execution time to a reasonable value.', 'gofer-seo' ),
			'enable_title_rewrite'       => __( 'Enable this option if you run into issues with the title tag being set by your theme or another plugin.', 'gofer-seo' ),
			'enable_unprotect_post_meta' => __( "Check this to unprotect internal postmeta fields for use with XMLRPC. If you don't know what that is, leave it unchecked.", 'gofer-seo' ),
			'enable_stop_heartbeat'      => __( 'Allows disabling WP\'s heartbeat JS and may help resolve an issue with it.<br />Please note, some parts of the WP platform depend on it, as well as some plugins.', 'gofer-seo' ),
		);

		return $tooltips;
	}

	/**
	 * Tooltip HTML.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return array
	 */
	private function get_html_debugger() {
		$tooltips = array(
			// System Settings.
			'clear_cache'       => __( 'Deletes cache stored by the plugin.', 'gofer-seo' ),

			// Error Settings.
			'enable_errors'     => __( 'Enables tracking backend errors. This setting alone only includes expected Gofer SEO errors or logs.', 'gofer-seo' ),
			'enable_wp_errors'  => __( 'Whether to include WP errors.<br />This is strongly discouraged depending on the scope of the site.', 'gofer-seo' ),
			'enable_error_logs' => __( 'This will create a log of important events (gofer-seo.log) in the wp-content directory which might help debugging. Make sure this directory is writable.', 'gofer-seo' ),

			// Errors List.
			'show_timestamps'   => __( 'Show timestamps on the table.', 'gofer-seo' ),
			'show_messages'     => __( 'Show messages on the table.', 'gofer-seo' ),
			'show_details'      => __( 'Show details on the table.', 'gofer-seo' ),
			'show_data'         => __( 'Show data on the table.', 'gofer-seo' ),
			'delete_errors'     => __( 'Deletes all errors stored.', 'gofer-seo' ),
		);

		return $tooltips;
	}

	/**
	 * Tooltip HTML.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return array
	 */
	private function get_html_post_edit() {
		$tooltips = array(
			// Module - General.
			'gofer_seo_modules-general-snippet'            => sprintf( __( 'Displays a preview of what visitors would see from this %s. Recommended character limit is 160, and Search Engines may shorten it.', 'gofer-seo' ), __( 'Page/Post', 'gofer-seo' ) ),
			'gofer_seo_modules-general-title'              => sprintf( __( 'A custom title that shows up in the title tag for this %s.', 'gofer-seo' ), __( 'Page/Post', 'gofer-seo' ) ),
			'gofer_seo_modules-general-description'        => sprintf( __( 'The META description for this %s. This will override any autogenerated descriptions.', 'gofer-seo' ), __( 'Page/Post', 'gofer-seo' ) ),
			'gofer_seo_modules-general-keywords'           => sprintf( __( 'A comma separated list of your most important keywords for this %s that will be written as META keywords.', 'gofer-seo' ), __( 'Page/Post', 'gofer-seo' ) ),
			'gofer_seo_modules-general-custom_link'        => sprintf( __( 'Override the canonical URLs for this %s.', 'gofer-seo' ), __( 'Page/Post', 'gofer-seo' ) ),
			'gofer_seo_modules-general-enable_noindex'     => sprintf( __( 'Check this box to ask search engines not to index this %s.', 'gofer-seo' ), __( 'Page/Post', 'gofer-seo' ) ),
			'gofer_seo_modules-general-enable_nofollow'    => sprintf( __( 'Check this box to ask search engines not to follow links from this %s.', 'gofer-seo' ), __( 'Page/Post', 'gofer-seo' ) ),
			'gofer_seo_modules-general-disable_analytics'  => sprintf( __( 'Disable Google Analytics on this %s.', 'gofer-seo' ), __( 'Page/Post', 'gofer-seo' ) ),
			'gofer_seo_modules-general-enable_force_disable' => sprintf( __( 'Disable SEO on this %s.', 'gofer-seo' ), __( 'Page/Post', 'gofer-seo' ) ),

			// Module - Social Media.
			'gofer_seo_modules-social_media-title'         => sprintf( __( 'This is the Open Graph title of this %s.', 'gofer-seo' ), __( 'Page/Post', 'gofer-seo' ) ),
			'gofer_seo_modules-social_media-description'   => sprintf( __( 'This is the Open Graph description of this %s.', 'gofer-seo' ), __( 'Page/Post', 'gofer-seo' ) ),
			'gofer_seo_modules-social_media-keywords'      => sprintf( __( 'A comma separated list of your most important keywords for this %s that will be written as META keywords.', 'gofer-seo' ), __( 'Page/Post', 'gofer-seo' ) ),
			'gofer_seo_modules-social_media-image'         => sprintf( __( 'This option lets you upload an image to use as the Open Graph image for this %s.', 'gofer-seo' ), __( 'Page/Post', 'gofer-seo' ) ),
			'gofer_seo_modules-social_media-image_width'   => __( 'Enter the width for your Open Graph image in pixels (i.e. 600).', 'gofer-seo' ),
			'gofer_seo_modules-social_media-image_height'  => __( 'Enter the height for your Open Graph image in pixels (i.e. 600).', 'gofer-seo' ),
			'gofer_seo_modules-social_media-video'         => sprintf( __( 'This option lets you specify a link to the Open Graph video used on this %s.', 'gofer-seo' ), __( 'Page/Post', 'gofer-seo' ) ),
			'gofer_seo_modules-social_media-video_width'   => __( 'Enter the width for your Open Graph video in pixels (i.e. 600).', 'gofer-seo' ),
			'gofer_seo_modules-social_media-video_height'  => __( 'Enter the height for your Open Graph video in pixels (i.e. 600).', 'gofer-seo' ),
			'gofer_seo_modules-social_media-facebook'      => '',
			'gofer_seo_modules-social_media-facebook-object_type' => sprintf( __( 'Select the Open Graph type that best describes the content of this %s.', 'gofer-seo' ), __( 'Page/Post', 'gofer-seo' ) ),
			'gofer_seo_modules-social_media-facebook-article_section' => sprintf( __( 'This Open Graph meta allows you to add a general section name that best describes this %s.', 'gofer-seo' ), __( 'Page/Post', 'gofer-seo' ) ),
			'gofer_seo_modules-social_media-twitter'       => '',
			'gofer_seo_modules-social_media-twitter-card_type' => sprintf( __( 'Select the Twitter Card type to use for this %s, overriding the default setting.', 'gofer-seo' ), __( 'Page/Post', 'gofer-seo' ) ),
			'gofer_seo_modules-social_media-twitter-image' => sprintf( __( 'This option lets you upload an image to use as the Twitter image for this %s.', 'gofer-seo' ), __( 'Page/Post', 'gofer-seo' ) ),

			// Module - Sitemap.
			'gofer_seo_modules-sitemap-priority'           => sprintf( __( 'Manually set the %1$s of your %2$s.', 'gofer-seo' ), __( 'priority', 'gofer-seo' ), __( 'Post', 'gofer-seo' ) ),
			'gofer_seo_modules-sitemap-frequency'          => sprintf( __( 'Manually set the %1$s of your %2$s.', 'gofer-seo' ), __( 'frequency', 'gofer-seo' ), __( 'Post', 'gofer-seo' ) ),
			'gofer_seo_modules-sitemap-enable_exclude'     => __( 'Manually exclude post from sitemap.', 'gofer-seo' ),
		);

		return $tooltips;
	}

	/**
	 * Tooltip HTML.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return array
	 */
	private function get_html_term_edit() {
		$tooltips = array(
			// Module - General.
			'gofer_seo_modules-general-title'              => sprintf( __( 'A custom title that shows up in the title tag for this %s.', 'gofer-seo' ), __( 'Term', 'gofer-seo' ) ),
			'gofer_seo_modules-general-description'        => sprintf( __( 'The META description for this %s. This will override any autogenerated descriptions.', 'gofer-seo' ), __( 'Term', 'gofer-seo' ) ),
			'gofer_seo_modules-general-keywords'           => sprintf( __( 'A comma separated list of your most important keywords for this %s that will be written as META keywords.', 'gofer-seo' ), __( 'Term', 'gofer-seo' ) ),
			'gofer_seo_modules-general-custom_link'        => sprintf( __( 'Override the canonical URLs for this %s.', 'gofer-seo' ), __( 'Term', 'gofer-seo' ) ),
			'gofer_seo_modules-general-noindex'            => sprintf( __( 'Check this box to ask search engines not to index this %s.', 'gofer-seo' ), __( 'Term', 'gofer-seo' ) ),
			'gofer_seo_modules-general-nofollow'           => sprintf( __( 'Check this box to ask search engines not to follow links from this %s.', 'gofer-seo' ), __( 'Term', 'gofer-seo' ) ),
			'gofer_seo_modules-general-disable_analytics'  => sprintf( __( 'Disable Google Analytics on this %s.', 'gofer-seo' ), __( 'Term', 'gofer-seo' ) ),
			'gofer_seo_modules-general-enable_force_disable' => sprintf( __( 'Disable SEO on this %s.', 'gofer-seo' ), __( 'Term', 'gofer-seo' ) ),

			// Module - Social Media.
			'gofer_seo_modules-social_media-title'         => sprintf( __( 'This is the Open Graph title of this %s.', 'gofer-seo' ), __( 'Term', 'gofer-seo' ) ),
			'gofer_seo_modules-social_media-description'   => sprintf( __( 'This is the Open Graph description of this %s.', 'gofer-seo' ), __( 'Term', 'gofer-seo' ) ),
			'gofer_seo_modules-social_media-keywords'      => sprintf( __( 'A comma separated list of your most important keywords for this %s that will be written as META keywords.', 'gofer-seo' ), __( 'Term', 'gofer-seo' ) ),
			'gofer_seo_modules-social_media-image'         => sprintf( __( 'This option lets you upload an image to use as the Open Graph image for this %s.', 'gofer-seo' ), __( 'Term', 'gofer-seo' ) ),
			'gofer_seo_modules-social_media-image_width'   => __( 'Enter the width for your Open Graph image in pixels (i.e. 600).', 'gofer-seo' ),
			'gofer_seo_modules-social_media-image_height'  => __( 'Enter the height for your Open Graph image in pixels (i.e. 600).', 'gofer-seo' ),
			'gofer_seo_modules-social_media-video'         => sprintf( __( 'This option lets you specify a link to the Open Graph video used on this %s.', 'gofer-seo' ), __( 'Term', 'gofer-seo' ) ),
			'gofer_seo_modules-social_media-video_width'   => __( 'Enter the width for your Open Graph video in pixels (i.e. 600).', 'gofer-seo' ),
			'gofer_seo_modules-social_media-video_height'  => __( 'Enter the height for your Open Graph video in pixels (i.e. 600).', 'gofer-seo' ),
			'gofer_seo_modules-social_media-facebook'      => '',
			'gofer_seo_modules-social_media-facebook-object_type' => sprintf( __( 'Select the Open Graph type that best describes the content of this %s.', 'gofer-seo' ), __( 'Term', 'gofer-seo' ) ),
			'gofer_seo_modules-social_media-facebook-article_section' => sprintf( __( 'This Open Graph meta allows you to add a general section name that best describes this %s.', 'gofer-seo' ), __( 'Term', 'gofer-seo' ) ),
			'gofer_seo_modules-social_media-twitter'       => '',
			'gofer_seo_modules-social_media-twitter-card_type' => sprintf( __( 'Select the Twitter Card type to use for this %s, overriding the default setting.', 'gofer-seo' ), __( 'Term', 'gofer-seo' ) ),
			'gofer_seo_modules-social_media-twitter-image' => sprintf( __( 'This option lets you upload an image to use as the Twitter image for this %s.', 'gofer-seo' ), __( 'Term', 'gofer-seo' ) ),
		);

		return $tooltips;
	}
}
// phpcs:enable
