<?php
/**
 * Gofer SEO Options Class.
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

/**
 * Class Gofer_SEO_Options
 *
 * @since 1.0.0
 */
class Gofer_SEO_Options {

	/**
	 * Multi-Singleton Instance.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var null $instances Multiton Class Instance.
	 */
	protected static $instances = array();

	/**
	 * Is Cache.
	 *
	 * After all hooks have been added, begin caching.
	 * Options class is required during early instances before 3rd party hooks have been added.
	 * Plugin fully loads on ( hook: `plugins_loaded`, priority: 3 ).
	 * Caching enables on ( hook: `plugins_loaded`, priority: 100 ).
	 *
	 * @since 1.0.0
	 *
	 * @var bool $is_cache Used to begin caching variables.
	 */
	protected $is_cache = false;

	/**
	 * Value for wp_options > option_name column.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $option_name = 'gofer_seo_options';

	/**
	 * Stores plugin options.
	 *
	 * @since 1.0.0
	 *
	 * @var array|null
	 */
	public $options = null;

	/**
	 * Typesetter.
	 *
	 * @since 1.0.0
	 *
	 * @var Gofer_SEO_Typesetter_Data $typesetter
	 */
	private $typesetter;
	
	/**
	 * Throws error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @ignore
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin\' huh?', 'gofer-seo' ), esc_html( GOFER_SEO_VERSION ) );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @ignore
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin\' huh?', 'gofer-seo' ), esc_html( GOFER_SEO_VERSION ) );
	}

	/**
	 * Get Singleton Instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Gofer_SEO_Options
	 */
	public static function get_instance() {
		$site_id = 0;
		if ( is_multisite() ) {
			$site_id = get_current_blog_id();
		}

		if ( ! isset( self::$instances[ $site_id ] ) || null === self::$instances[ $site_id ] ) {
			self::$instances[ $site_id ] = new self();
		}

		return self::$instances[ $site_id ];
	}

	/**
	 * Gofer_SEO_Options constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->add_hooks();

		$this->typesetter = new Gofer_SEO_Typesetter_Data();
		$this->options    = $this->get_options();
	}

	/**
	 * Add Hooks.
	 *
	 * @since 1.0.0
	 *
	 * @see gofer_seo_options_enable_modules_typeset hook.
	 * @hook gofer_seo_options_modules_typeset
	 */
	private function add_hooks() {
		add_action( 'plugins_loaded', array( $this, 'enable_cache' ), 100 );
		// Runs after `gofer_seo_set_post_type_objects_transient()`.
		add_action( 'init', array( $this, 'refresh_options' ), 10000 );

		// Module Manager.
		add_filter( 'gofer_seo_options_enable_modules_typeset', array( $this, 'typeset_enable_modules' ) );

		// Modules.
		add_filter( 'gofer_seo_options_modules_typeset', array( $this, 'typeset_module_general' ) );
		add_filter( 'gofer_seo_options_modules_typeset', array( $this, 'typeset_module_social_media' ) );
		add_filter( 'gofer_seo_options_modules_typeset', array( $this, 'typeset_module_sitemap' ) );
		add_filter( 'gofer_seo_options_modules_typeset', array( $this, 'typeset_module_schema_graph' ) );
		add_filter( 'gofer_seo_options_modules_typeset', array( $this, 'typeset_module_crawlers' ) );
		add_filter( 'gofer_seo_options_modules_typeset', array( $this, 'typeset_module_advanced' ) );
		add_filter( 'gofer_seo_options_modules_typeset', array( $this, 'typeset_module_debugger' ) );
	}

	/**
	 * Enable Cache.
	 *
	 * Used to enable the cache after all plugins have loaded.
	 *
	 * @return void
	 */
	public function enable_cache() {
		$this->is_cache = true;
	}

	/**
	 * Refresh Options.
	 *
	 * Used to refresh options after register_post_type() & register_taxonomy() has been used.
	 * Runs after `gofer_seo_set_post_type_objects_transient()` and adds any post types possibly missing.
	 *
	 * @since 1.0.0
	 *
	 * @see gofer_seo_set_post_type_objects_transient()
	 */
	public function refresh_options() {
		$this->options = $this->get_options();
	}

	/**
	 * Options Typesets.
	 *
	 * @since 1.0.0
	 *
	 * @return array {
	 *     @type array[ ${typeset_key} ] {
	 *         @type string|array $type         Identifies the variable type(s).
	 *                                          Supports variables with more than one type, and identifies by the first type detected.
	 *                                          Accepts...
	 *                                          - cast, cast_dynamic
	 *                                          - int, integer
	 *                                          - bool, boolean
	 *                                          - float, double, (?real?)
	 *                                          - string
	 *                                          - array
	 *                                          - object (sets to array)
	 *                                          - unset
	 *         @type array        $cast         Additional typesets to cast the variable as.
	 *         @type array        $cast_dynamic Additional (dynamic) typesets to cast the variable as.
	 *         @type string[]     $items        Used to determine the active array keys to set or update. {
	 *             @type string ${ARRAY_INT} => ${SLUG}
	 *         }
	 *         @type mixed        $value        The (default) value.
	 *         @type array        $sanitize {
	 *             Sanitize function/method to use.
	 *
	 *             @type array ${ARRAY_INT} {
	 *                 @type string|array|callable ${[0]} callback
	 *                 @type array                 ${[1]} callback_args
	 *             }
	 *             and/or
	 *             @type array ${TYPE_SLUG} {
	 *                 @type array ${ARRAY_INT} {
	 *                     @type string|array|callable ${[0]} callback
	 *                     @type array                 ${[1]} callback_args
	 *                 }
	 *             }
	 *         }
	 *
	 *         // TODO *** Concept ideas (NOT YET IMPLEMENTED) ***
	 *         @type array[]      $callbacks    Executes callbacks, in numeric array key order, whenever the value changes.
	 *                                          Allowing other variables to update their values.
	 *     }
	 * }
	 */
	public function get_options_typesets() {
		static $s_typesets;
		if ( $this->is_cache && null !== $s_typesets ) {
			return $s_typesets;
		}

		$typesets = array(
			'version'        => array(
				'type'  => 'string',
				'value' => GOFER_SEO_VERSION,
			),

			'enable_modules' => array(
				'type' => 'cast',

				/**
				 * Module Manager Typeset.
				 *
				 * @since 1.0.0
				 *
				 * @param array $typeset See method return documentation.
				 */
				'cast' => apply_filters( 'gofer_seo_options_enable_modules_typeset', array() ),
			),

			'modules'        => array(

				/**
				 * Modules Typeset.
				 *
				 * @since 1.0.0
				 *
				 * @param array $typeset See method return documentation.
				 */
				'cast' => apply_filters( 'gofer_seo_options_modules_typeset', array() ),
			),
		);

		/**
		 * Modules Typeset.
		 *
		 * @since 1.0.0
		 *
		 * @param array $typesets See method return documentation.
		 */
		$typesets = apply_filters( 'gofer_seo_options_typeset', $typesets );

		if ( $this->is_cache ) {
			$s_typesets = $typesets;
		}

		return $typesets;
	}

	/**
	 * Module Manager Typeset.
	 *
	 * @since 1.0.0
	 *
	 * @param array $typeset
	 * @return array
	 */
	public function typeset_enable_modules( $typeset ) {
		$module_typeset = array(
			'social_media' => array(
				'type'  => 'boolean',
				'value' => false,
			),
			'sitemap'      => array(
				'type'  => 'boolean',
				'value' => true,
			),
			'schema_graph' => array(
				'type'  => 'boolean',
				'value' => true,
			),
			'crawlers'     => array(
				'type'  => 'boolean',
				'value' => true,
			),
			'advanced'     => array(
				'type'  => 'boolean',
				'value' => false,
			),
			'debugger'     => array(
				'type'  => 'boolean',
				'value' => false,
			),
			// Not yet implemented.
			//'importer_exporter' => array(
			//	'type'  => 'boolean',
			//	'value' => true,
			//),
		);

		$typeset = array_replace_recursive( $typeset, $module_typeset );

		return $typeset;
	}

	/**
	 * Module Typeset - General.
	 *
	 * @since 1.0.0
	 *
	 * @param array $typesets
	 * @return array
	 */
	public function typeset_module_general( $typesets ) {
		if ( ! isset( $typesets['general'] ) ) {
			$typesets['general'] = array();
		}

		$post_types = gofer_seo_get_post_types( array(), 'name' );
		$post_types_enable = array_map(
			function( $value ) {
				$default_enabled = array(
					'post',
					'page',
				);
				if ( in_array( $value, $default_enabled, true ) ) {
					return true;
				}
				return false;
			},
			$post_types
		);
		$post_types_enable = array_combine( $post_types, $post_types_enable );

		$taxonomies = gofer_seo_get_taxonomies( array(), 'name' );
		$taxonomies_enable = array_map(
			function( $value ) {
				$default_enabled = array(
					'categories',
				);
				if ( in_array( $value, $default_enabled, true ) ) {
					return true;
				}
				return false;
			},
			$taxonomies
		);
		$taxonomies_enable = array_combine( $taxonomies, $taxonomies_enable );

		$typeset = array(
			/* **_________*****************************************************************************************/
			/* _/ General \_______________________________________________________________________________________*/
			'show_admin_bar'                       => array(
				'type'  => 'bool',
				'value' => true,
			),
			'enable_canonical'                     => array(
				'type'  => 'bool',
				'value' => true,
			),
			'enable_canonical_paginated'           => array(
				'type'  => 'bool',
				'value' => false,
			),

			/* **______________________________********************************************************************/
			/* _/ Site / Front Page (Defaults) \__________________________________________________________________*/
			'enable_site_title'                    => array(
				'type'  => 'bool',
				'value' => true,
			),
			'enable_site_description'              => array(
				'type'  => 'bool',
				'value' => true,
			),
			'use_static_homepage'                  => array(
				'type'  => 'bool',
				'value' => false,
			),
			'site_title'                           => array(
				'type'  => 'string',
				'value' => '',
			),
			'site_description'                     => array(
				'type'  => 'string',
				'value' => '',
			),
			'site_name'                            => array(
				'type'  => 'string',
				'value' => '',
			),
			'site_keywords'                        => array(
				'type'  => 'string',
				'value' => '',
			),
			'site_image'                           => array(
				'type'     => array( 'int', 'string' ),
				'value'    => '',
				'sanitize' => array(
					'string' => array(
						array( 'esc_url_raw' ),
						// Add callback to check if url is actually from the site.
						// array( 'gofer_seo_attachment_url_to_id' ),
					),
				),
			),
			'site_logo'                            => array(
				'type'     => array( 'int', 'string' ),
				'value'    => '',
				'sanitize' => array(
					'string' => array(
						array( 'esc_url_raw' ),
					),
				),
			),
			'site_title_format'                    => array(
				'type'  => 'string',
				'value' => '[post_title]',
			),
			'site_description_format'              => array(
				'type'  => 'string',
				'value' => '[description]',
			),
			'home_meta_tags'                       => array(
				'type'  => 'string',
				'value' => '',
			),
			'posts_page_meta_tags'                 => array(
				'type'  => 'string',
				'value' => '',
			),

			/* **_______*******************************************************************************************/
			/* _/ Image \_________________________________________________________________________________________*/
			'image_source'                         => array(
				'type'  => 'string',
				'value' => 'default',
			),
			'image_source_meta_keys'               => array(
				'type'  => 'string',
				'value' => '',
			),

			/* **_____________________________*********************************************************************/
			/* _/ Post Type Content (Dynamic) \___________________________________________________________________*/
			'enable_post_types'                    => array(
				'type'  => 'array',
				// 'sanitize' => 'bool[]', // TODO Add sanitize for array( string => bool ).
				'value' => $post_types_enable,
			),
			'post_type_settings'                   => array(
				'type'    => 'cast_dynamic',
				'dynamic' => array(
					'enable_editor_meta_box' => array(
						'type'  => 'bool',
						'value' => true,
					),
					'title_format'           => array(
						'type'  => 'string',
						'value' => '[post_title] | [site_title]',
					),
					'description_format'     => array(
						'type'  => 'string',
						'value' => '[description]',
					),
					// Options are boolean, and Post, Term, & User objects are int (boolean).
					// (-1 = Use Options, 0 = Index, 1 = NoIndex).
					'enable_noindex'         => array(
						'type'  => 'bool',
						'value' => false,
					),
					'enable_nofollow'        => array(
						'type'  => 'bool',
						'value' => false,
					),
					'custom_meta_tags'       => array(
						'type'  => 'string',
						'value' => '',
					),
				),
				'items'   => $post_types,
				'value'   => array(
					'post' => array(
						'enable_editor_meta_box' => true,
						'title_format'           => '[post_title] | [site_title]',
						'description_format'     => '[description]',
						'enable_noindex'         => false,
						'enable_nofollow'        => false,
						'custom_meta_tags'       => '',
					),
					'page' => array(
						'enable_editor_meta_box' => true,
						'title_format'           => '[post_title] | [site_title]',
						'description_format'     => '[description]',
						'enable_noindex'         => false,
						'enable_nofollow'        => false,
						'custom_meta_tags'       => '',
					),
				),
			),

			/* **____________________________**********************************************************************/
			/* _/ Taxonomy Content (Dynamic) \____________________________________________________________________*/
			'enable_taxonomies'                    => array(
				'type'  => 'array',
				'value' => $taxonomies_enable,
			),
			'taxonomy_settings'                    => array(
				'type'    => 'cast_dynamic',
				'dynamic' => array(
					'enable_editor_meta_box' => array(
						'type'  => 'bool',
						'value' => true,
					),
					'title_format'           => array(
						'type'  => 'string',
						'value' => '[taxonomy_title] | [site_title]',
					),
					'description_format'     => array(
						'type'  => 'string',
						'value' => '[description]',
					),
					'enable_noindex'         => array(
						'type'  => 'bool',
						'value' => false,
					),
					'enable_nofollow'        => array(
						'type'  => 'bool',
						'value' => false,
					),
				),
				'items'   => $taxonomies,
				'value'   => array(
					'category' => array(
						'enable_editor_meta_box' => true,
						'title_format'           => '',
						'description_format'     => '',
						'enable_noindex'         => false,
						'enable_nofollow'        => false,
					),
				),
			),

			/* **_________________*********************************************************************************/
			/* _/ Archive Content \_______________________________________________________________________________*/
			// TODO Add `archive_title_format` to `post_type_settings`, and use `archive_post_title_format` as default.
			'archive_post_title_format'            => array(
				'type'  => 'string',
				'value' => '[archive_title] | [site_title]',
			),
			'archive_taxonomy_term_title_format'   => array(
				'type'  => 'string',
				'value' => '[archive_title] | [site_title]',
			),
			'archive_date_title_format'            => array(
				'type'  => 'string',
				'value' => '[date] | [site_title]',
			),
			'archive_author_title_format'          => array(
				'type'  => 'string',
				'value' => '[author_nickname] | [site_title]',
			),
			'archive_date_enable_noindex'          => array(
				'type'  => 'bool',
				'value' => false,
			),
			'archive_author_enable_noindex'        => array(
				'type'  => 'bool',
				'value' => false,
			),

			/* **_________________*********************************************************************************/
			/* _/  Search Content \_______________________________________________________________________________*/
			'search_title_format'                  => array(
				'type'  => 'string',
				'value' => '[search_value] | [site_title]',
			),
			'search_enable_noindex'                => array(
				'type'  => 'bool',
				'value' => false,
			),

			/* **_____________*************************************************************************************/
			/* _/ 404 Content \___________________________________________________________________________________*/
			'404_title_format'                     => array(
				'type'  => 'string',
				/* translators: %s is the shortcode. */
				'value' => sprintf( __( 'Nothing found for %1$s', 'gofer-seo' ), '[request_words]' ),
			),
			'404_enable_noindex'                   => array(
				'type'  => 'bool',
				'value' => false,
			),

			/* **____________________******************************************************************************/
			/* _/ Additional Content \____________________________________________________________________________*/
			'paginate_format'                      => array(
				'type'  => 'string',
				/* translators: %1$s is a dash, and %2$s is the shortcode. */
				'value' => sprintf( __( ' %1$s Page %2$s of %3$s', 'gofer-seo' ), '-', '[page]', '[pages]' ),
			),
			'paginate_enable_noindex'              => array(
				'type'  => 'bool',
				'value' => false,
			),
			'paginate_enable_nofollow'             => array(
				'type'  => 'bool',
				'value' => false,
			),

			/* **__________________********************************************************************************/
			/* _/ SEO Verification \______________________________________________________________________________*/
			'verify_google'                        => array(
				'type'  => 'string',
				'value' => '',
			),
			'verify_bing'                          => array(
				'type'  => 'string',
				'value' => '',
			),
			'verify_pinterest'                     => array(
				'type'  => 'string',
				'value' => '',
			),
			'verify_yandex'                        => array(
				'type'  => 'string',
				'value' => '',
			),
			'verify_baidu'                         => array(
				'type'  => 'string',
				'value' => '',
			),

			/* **___________***************************************************************************************/
			/* _/ Analytics \_____________________________________________________________________________________*/
			'google_analytics'                     => array(
				'type' => 'cast',
				'cast' => array(
					'ua_id'                          => array(
						'type'  => 'string',
						'value' => '',
					),
					'enable_advanced_settings'       => array(
						'type'  => 'bool',
						'value' => false,
					),
					'track_domain'                   => array(
						'type'  => 'string',
						'value' => '',
					),
					'enable_track_multi_domains'     => array(
						'type'  => 'bool',
						'value' => false,
					),
					'track_multi_domains'            => array(
						'type'  => 'string',
						'value' => '',
					),
					'exclude_user_roles'             => array(
						'type'  => 'array',
						'value' => array(
							'administrator' => true,
							'author'        => false,
							'contributor'   => false,
							'editor'        => false,
							'subscriber'    => false,
						),
					),
					'enable_enhance_ecommerce'       => array(
						'type'  => 'bool',
						'value' => false,
					),
					'enable_enhance_link_attributes' => array(
						'type'  => 'bool',
						'value' => false,
					),
					'enable_track_outbound_links'    => array(
						'type'  => 'bool',
						'value' => false,
					),
					'enable_track_outbound_forms'    => array(
						'type'  => 'bool',
						'value' => false,
					),
					'enable_track_social_media'      => array(
						'type'  => 'bool',
						'value' => false,
					),
					'enable_track_events'            => array(
						'type'  => 'bool',
						'value' => false,
					),
					'enable_track_url_changes'       => array(
						'type'  => 'bool',
						'value' => false,
					),
					'enable_track_media_query'       => array(
						'type'  => 'bool',
						'value' => false,
					),
					'enable_track_page_visibility'   => array(
						'type'  => 'bool',
						'value' => false,
					),
					'enable_track_impressions'       => array(
						'type'  => 'bool',
						'value' => false,
					),
					'enable_track_max_scroll'        => array(
						'type'  => 'bool',
						'value' => false,
					),
					'enable_advertising_features'    => array(
						'type'  => 'bool',
						'value' => false,
					),
					'enable_anonymize_ip'            => array(
						'type'  => 'bool',
						'value' => false,
					),
					'enable_clean_url'               => array(
						'type'  => 'bool',
						'value' => false,
					),
				),
			),

			/* **_________________*********************************************************************************/
			/* _/ (Auto) Generate \_______________________________________________________________________________*/
			'generate_keywords'                    => array(
				'type' => 'cast',
				'cast' => array(
					'enable_generator'            => array(
						'type'  => 'bool',
						'value' => false,
					),
					// TODO Add use_keywords.
					'enable_on_static_posts_page' => array(
						'type'  => 'bool',
						'value' => true,
					),
					'use_taxonomies'              => array(
						'type'  => 'array',
						'value' => $taxonomies_enable,
					),
				),
			),
			'generate_description'                 => array(
				'type' => 'cast',
				'cast' => array(
					'enable_generator' => array(
						'type'  => 'bool',
						'value' => true,
					),
					'use_excerpt'      => array(
						'type'  => 'bool',
						'value' => true,
					),
					'use_content'      => array(
						'type'  => 'bool',
						'value' => false,
					),

				),
			),

			/* **__________****************************************************************************************/
			/* _/ Advanced \______________________________________________________________________________________*/
			'use_wp_title'                         => array(
				'type'  => 'bool',
				'value' => false,
			),
			'enable_title_shortcodes'              => array(
				'type'  => 'bool',
				'value' => true,
			),
			'enable_description_shortcodes'        => array(
				'type'  => 'bool',
				'value' => true,
			),
			'enable_trim_description'              => array(
				'type'  => 'bool',
				'value' => true,
			),
			'show_paginate_descriptions'           => array(
				'type'  => 'bool',
				'value' => true,
			),
			'enable_attachment_redirect_to_parent' => array(
				'type'  => 'bool',
				'value' => false,
			),
			'admin_menu_order'                     => array(
				'type'  => 'int',
				'value' => 80,
			),
			'exclude_urls'                         => array(
				'type'  => 'string',
				'value' => '',
			),
		);

		$typesets['general'] = array_replace_recursive( $typesets['general'], array( 'cast' => $typeset ) );

		return $typesets;
	}

	/**
	 * Module Typeset - Social Media.
	 *
	 * @since 1.0.0
	 *
	 * @param array $typesets
	 * @return array
	 */
	public function typeset_module_social_media( $typesets ) {
		if ( ! isset( $typesets['social_media'] ) ) {
			$typesets['social_media'] = array();
		}

		$post_types = gofer_seo_get_post_types( array(), 'name' );
		$post_types_enable = array_map(
			function( $value ) {
				$default_enabled = array(
					'post',
					'page',
				);
				if ( in_array( $value, $default_enabled, true ) ) {
					return true;
				}
				return false;
			},
			$post_types
		);
		$post_types_enable = array_combine( $post_types, $post_types_enable );

		$taxonomies = gofer_seo_get_taxonomies( array(), 'name' );
		$taxonomies_enable = array_map(
			function( $value ) {
				$default_enabled = array(
					'categories',
				);
				if ( in_array( $value, $default_enabled, true ) ) {
					return true;
				}
				return false;
			},
			$taxonomies
		);
		$taxonomies_enable = array_combine( $taxonomies, $taxonomies_enable );

		$typeset = array(
			/* **______________________________************************************************************************/
			/* _/ Site / Front Page (Defaults) \______________________________________________________________________*/
			'enable_site_title'                        => array(
				'type'  => 'bool',
				'value' => true,
			),
			'enable_site_description'                  => array(
				'type'  => 'bool',
				'value' => true,
			),
			'site_name'                                => array(
				'type'  => 'string',
				'value' => '',
			),
			'site_title'                               => array(
				'type'  => 'string',
				'value' => '',
			),
			'site_description'                         => array(
				'type'  => 'string',
				'value' => '',
			),
			'site_image'                               => array(
				'type'  => 'int',
				'value' => 0,
			),

			/* **_______***********************************************************************************************/
			/* _/ Image \_____________________________________________________________________________________________*/
			'default_image'                            => array(
				'type'  => array( 'int', 'string' ),
				'value' => GOFER_SEO_IMAGES_URL . 'default-user-image.png',
			),
			'default_image_width'                      => array(
				'type'  => array( 'int', 'string' ),
				'value' => 0,
			),
			'default_image_height'                     => array(
				'type'  => array( 'int', 'string' ),
				'value' => 0,
			),
			'image_source'                             => array(
				'type'  => 'string',
				'value' => 'featured',
			),
			'image_source_meta_keys'                   => array(
				'type'  => 'string',
				'value' => '',
			),

			/* **_____________________________*************************************************************************/
			/* _/ Post Type Content (Dynamic) \_______________________________________________________________________*/
			'enable_post_types'                        => array(
				'type'  => 'array',
				'value' => $post_types_enable,
			),
			//'post_type_settings' => array(),

			/* **__________********************************************************************************************/
			/* _/ Facebook \__________________________________________________________________________________________*/
			'fb_admin_id'                              => array(
				'type'  => 'string',
				'value' => '',
			),
			'fb_app_id'                                => array(
				'type'  => 'string',
				'value' => '',
			),
			'fb_publisher_fb_url'                      => array(
				'type'  => 'string',
				'value' => '',
			),
			'fb_use_post_author_fb_url'                => array(
				'type'  => 'bool',
				'value' => true,
			),
			'fb_post_type_settings'                    => array(
				// 'type' => 'cast_dynamic',
				'cast_dynamic' => array(
					'fb_object_type' => array(
						'type'  => 'string',
						'value' => 'article',
					),

				),
				'items'        => $post_types,
				'value'        => array(
					'post' => array(
						'fb_object_type' => 'article',
					),
				),
			),

			/* **_________*********************************************************************************************/
			/* _/ Twitter \___________________________________________________________________________________________*/
			'twitter_card_type'                        => array(
				'type'  => 'string',
				'value' => 'summary',
			),
			'twitter_username'                         => array(
				'type'  => 'string',
				'value' => '',
			),
			'twitter_use_post_author_twitter_username' => array(
				'type'  => 'bool',
				'value' => false,
			),

			/* **_________________*************************************************************************************/
			/* _/ (Auto) Generate \___________________________________________________________________________________*/
			'generate_keywords'                        => array(
				'cast' => array(
					'enable_generator' => array(
						'type'  => 'bool',
						'value' => false,
					),
					'use_keywords'     => array(
						'type'  => 'bool',
						'value' => false,
					),
					// TODO Add ?enable_on_static_posts_page?
					'use_taxonomies'   => array(
						'type'  => 'array',
						'value' => $taxonomies_enable,
					),
				),
			),
			'generate_description'                     => array(
				'type' => 'cast',
				'cast' => array(
					'enable_generator' => array(
						'type'  => 'bool',
						'value' => true,
					),
					'use_excerpt'      => array(
						'type'  => 'bool',
						'value' => true,
					),
					'use_content'      => array(
						'type'  => 'bool',
						'value' => false,
					),
				),
			),

			/* **__________********************************************************************************************/
			/* _/ Advanced \__________________________________________________________________________________________*/
			'enable_title_shortcodes'                  => array(
				'type'  => 'bool',
				'value' => false,
			),
			'enable_description_shortcodes'            => array(
				'type'  => 'bool',
				'value' => false,
			),
		);


		$typesets['social_media'] = array_replace_recursive(
			$typesets['social_media'],
			array( 'cast' => $typeset )
		);

		return $typesets;
	}

	/**
	 * Module Typeset - Sitemap.
	 *
	 * @since 1.0.0
	 *
	 * @param array $typesets
	 * @return array
	 */
	public function typeset_module_sitemap( $typesets ) {
		if ( ! isset( $typesets['sitemap'] ) ) {
			$typesets['sitemap'] = array();
		}

		$post_types = gofer_seo_get_post_types( array(), 'name' );
		$post_types_enable = array_map(
			function( $value ) {
				$default_enabled = array(
					'post',
				);
				if ( in_array( $value, $default_enabled, true ) ) {
					return true;
				}
				return false;
			},
			$post_types
		);
		$post_types_enable = array_combine( $post_types, $post_types_enable );

		$taxonomies = gofer_seo_get_taxonomies( array(), 'name' );
		$taxonomies_enable = array_map(
			function( $value ) {
				$default_enabled = array(
					'categories',
				);
				if ( in_array( $value, $default_enabled, true ) ) {
					return true;
				}
				return false;
			},
			$taxonomies
		);
		$taxonomies_enable = array_combine( $taxonomies, $taxonomies_enable );


		$typeset = array(
			/* **_________*********************************************************************************************/
			/* _/ General \___________________________________________________________________________________________*/
			'enable_news_sitemap'         => array(
				'type'  => 'bool',
				'value' => false,
			),
			'enable_rss_sitemap'          => array(
				'type'  => 'bool',
				'value' => false,
			),
			'enable_indexes'              => array(
				'type'  => 'bool',
				'value' => true,
			),
			'posts_per_sitemap'           => array(
				'type'  => 'int',
				'value' => 1000,
			),


			/* **_________________*************************************************************************************/
			/* _/ Site / Defaults \___________________________________________________________________________________*/
			'site_priority'               => array(
				'type'  => 'int',
				'value' => 10,
			),
			'site_frequency'              => array(
				'type'  => 'string',
				'value' => 'always',
			),
			'post_type_default_priority'  => array(
				'type'  => 'int',
				'value' => 7,
			),
			'post_type_default_frequency' => array(
				'type'  => 'string',
				'value' => 'weekly',
			),
			'taxonomy_default_priority'   => array(
				'type'  => 'int',
				'value' => 3,
			),
			'taxonomy_default_frequency'  => array(
				'type'  => 'string',
				'value' => 'monthly',
			),


			/* **___________________***********************************************************************************/
			/* _/ Post Type Content \_________________________________________________________________________________*/
			'enable_post_types'           => array(
				'type'  => 'array',
				'value' => $post_types_enable,
			),
			'post_type_settings'          => array(
				'type'         => 'cast_dynamic',
				'cast_dynamic' => array(
					'show_on'   => array(
						'type'  => 'cast',
						'cast'  => array(
							'standard_sitemap' => array(
								'type'  => 'bool',
								'value' => true,
							),
							'news_sitemap'     => array(
								'type'  => 'bool',
								'value' => false,
							),
							'rss'              => array(
								'type'  => 'bool',
								'value' => false,
							),
						),
						'value' => '',
					),
					'priority'  => array(
						'type'  => 'int',
						'value' => -1,
					),
					'frequency' => array(
						'type'  => 'string',
						'value' => 'default',
					),
				),
				'items'        => $post_types,
				'value'        => array(
					'post' => array(
						'show_on'   => array(
							'standard_sitemap' => true,
							'video_sitemap'    => false,
							'news_sitemap'     => false,
							'rss'              => false,
						),
						'priority'  => -1,
						'frequency' => 'default',
					),
				),
			),


			/* **__________________************************************************************************************/
			/* _/ Taxonomy Content \__________________________________________________________________________________*/
			'enable_taxonomies'           => array(
				'type'  => 'array',
				'value' => $taxonomies_enable,
			),
			'taxonomy_settings'           => array(
				'type'         => 'cast_dynamic',
				'cast_dynamic' => array(
					'show_on'   => array(
						'type'  => 'cast',
						'cast'  => array(
							'standard_sitemap' => array(
								'type'  => 'bool',
								'value' => true,
							),
						),
						'value' => '',
					),
					'priority'  => array(
						'type'  => 'int',
						'value' => -1,
					),
					'frequency' => array(
						'type'  => 'string',
						'value' => 'default',
					),
				),
				'items'        => $taxonomies,
				'value'        => array(
					'category' => array(
						'show_on'   => array(
							'standard_sitemap' => true,
							'video_sitemap'    => false,
						),
						'priority'  => -1,
						'frequency' => 'default',
					),
				),
			),


			/* **_________________*************************************************************************************/
			/* _/ Archive Content \___________________________________________________________________________________*/
			'enable_archive_date'         => array(
				'type'  => 'bool',
				'value' => false,
			),
			'archive_date_settings'       => array(
				'cast' => array(
					'priority'  => array(
						'type'  => 'int',
						'value' => -1,
					),
					'frequency' => array(
						'type'  => 'string',
						'value' => 'default',
					),
				),
			),
			'enable_archive_author'       => array(
				'type'  => 'bool',
				'value' => false,
			),
			'archive_author_settings'     => array(
				'cast' => array(
					'priority'  => array(
						'type'  => 'int',
						'value' => -1,
					),
					'frequency' => array(
						'type'  => 'string',
						'value' => 'default',
					),
				),
			),


			/* **_________*********************************************************************************************/
			/* _/ Include \___________________________________________________________________________________________*/
			'include_urls'                => array(
				'cast_dynamic' => array(
					'url'           => array(
						'type'     => 'string',
						'value'    => '',
						'sanitize' => array(
							'string' => array(
								array( 'esc_url_raw' ),
							),
						),
					),
					'priority'      => array(
						'type'  => 'int',
						'value' => -1,
					),
					'frequency'     => array(
						'type'  => 'string',
						'value' => 'default',
					),
					'modified_date' => array(
						'type'  => 'string',
						'value' => wp_date( 'Y-m-d' ),
					),
				),
				'items'        => array(
					// 0 => '0',
					// 1 => '1',
					// 2 => '2',
				),
				'value'        => array(
				// 0 => array(
				//     'url'           => 'example.com/test1',
				//     'priority'      => -1,
				//     'frequency'     => 'hourly',
				//     'modified_date' => '2020-07-12 02:43:05',
				// ),
				// 1 => array(
				//     'url'           => 'example.com/test2',
				//     'priority'      => 6,
				//     'frequency'     => 'daily',
				//     'modified_date' => '2020-06-27 18:23:00',
				// ),
				// 2 => array(
				//     'url'           => 'example.com/test3',
				//     'priority'      => 9,
				//     'frequency'     => 'weekly',
				//     'modified_date' => '2019-11-30 13:54:45',
				// ),
				),
				'sanitize'     => array(
					'cast_dynamic' => array(
						// Checks for items with empty URLs and removes the item.
						array(
							'array_filter',
							array(
								function( $value ) {
									if ( empty( $value['url'] ) ) {
										return false;
									}
									return true;
								},
							),
						),
						array(
							'array_values',
						),
					),
				),
			),


			/* **_________*********************************************************************************************/
			/* _/ Exclude \___________________________________________________________________________________________*/
			'exclude_post_ids'            => array(
				'type'  => 'string',
				'value' => '',
			),
			'exclude_term_ids'            => array(
				'type'  => 'int[]',
				'value' => array(),
			),


			/* **__________********************************************************************************************/
			/* _/ Advanced \__________________________________________________________________________________________*/
			'include_images'              => array(
				'type'  => 'bool',
				'value' => true,
			),
		);

		$typesets['sitemap'] = array_replace_recursive(
			$typesets['sitemap'],
			array( 'cast' => $typeset )
		);

		return $typesets;
	}

	/**
	 * Module Typeset - Schema Graph.
	 *
	 * @since 1.0.0
	 *
	 * @param array $typesets
	 * @return array
	 */
	public function typeset_module_schema_graph( $typesets ) {
		if ( ! isset( $typesets['schema_graph'] ) ) {
			$typesets['schema_graph'] = array();
		}

		$typeset = array(
			/* **_________*********************************************************************************************/
			/* _/ General \___________________________________________________________________________________________*/
			'site_represents'          => array(
				'type'  => 'string',
				'value' => 'organization',
			),
			'person_user_id'           => array(
				'type'  => 'int',
				'value' => 1,
			),
			'person_custom_name'       => array(
				'type'  => 'string',
				'value' => '',
			),
			'person_custom_image'      => array(
				'type'  => array( 'int', 'string' ),
				'value' => '',
			),
			'organization_name'        => array(
				'type'  => 'string',
				'value' => '',
			),
			'organization_logo'        => array(
				'type'  => array( 'int', 'string' ),
				'value' => '',
			),
			'phone_contact_type'       => array(
				'type'  => 'string',
				'value' => '',
			),
			'phone_number'             => array(
				'type'  => 'string',
				'value' => '',
			),
			'social_profile_urls'      => array(
				'type'  => 'string',
				'value' => '',
			),
			'show_search_results_page' => array(
				'type'  => 'bool',
				'value' => false,
			),

		);

		$typesets['schema_graph'] = array_replace_recursive(
			$typesets['schema_graph'],
			array( 'cast' => $typeset )
		);

		return $typesets;
	}

	/**
	 * Module Typeset - Crawlers.
	 *
	 * @since 1.0.0
	 *
	 * @param array $typesets
	 * @return array
	 */
	public function typeset_module_crawlers( $typesets ) {
		if ( ! isset( $typesets['crawlers'] ) ) {
			$typesets['crawlers'] = array();
		}

		$typeset = array(
			/* **_________________*************************************************************************************/
			/* _/ Block HTTP Bots \___________________________________________________________________________________*/
			'enable_block_user_agent'    => array(
				'type'  => 'bool',
				'value' => false,
			),
			'enable_block_referer'       => array(
				'type'  => 'bool',
				'value' => false,
			),
			'enable_log_blocked_bots'    => array(
				'type'  => 'bool',
				'value' => false,
			),
			'use_custom_blacklist'       => array(
				'type'  => 'bool',
				'value' => false,
			),
			'user_agent_blacklist'       => array(
				'type'  => 'string',
				'value' => implode(
					"\n",
					Gofer_SEO_Module_Crawlers::default_agent_blacklist()
				),
			),
			'referer_blacklist'          => array(
				'type'  => 'string',
				'value' => implode(
					"\n",
					Gofer_SEO_Module_Crawlers::default_referer_blacklist()
				),
			),

			/* **___________*******************************************************************************************/
			/* _/ Block Log \_________________________________________________________________________________________*/
			'blocked_bots_log'           => array(
				'type'  => 'string',
				'value' => '',
			),

			/* **____________******************************************************************************************/
			/* _/ Robots.txt \________________________________________________________________________________________*/
			'enable_override_robots_txt' => array(
				'type'  => 'boolean',
				'value' => false,
			),

			'robots_txt_rules'           => array(
				'cast' => array(
					'user_agents' => array(
						'cast_dynamic' => array(
							'user_agent'  => array(
								'type'     => 'string',
								'value'    => '',
								'sanitize' => array(
									'string' => array(
										array( 'strval' ),
									),
								),
							),
							'crawl_delay' => array(
								'type'  => 'int',
								'value' => 0,
							),
							'path_rules'  => array(
								'type'  => 'string[]',
								'value' => array(),
							),
						),
						'items'        => array(),
						'value'        => array(
							'*' => array(
								'user_agent'  => '*',
								'crawl_delay' => 0,
								'disallow'    => array(
									'/wp-admin/',
								),
								'allow'       => array(
									'/wp-admin/admin-ajax.php',
								),
								'path_rules'  => array(
									'/wp-admin/' => 'disallow',
									'/wp-admin/admin-ajax.php' => 'allow',
								),
							),
						),
					),
					'sitemaps'    => array(
						'type'  => 'string[]',
						'value' => array(),
					),
				),
			),
		);

		$typesets['crawlers'] = array_replace_recursive(
			$typesets['crawlers'],
			array( 'cast' => $typeset )
		);

		return $typesets;
	}

	/**
	 * Module Typeset - Advanced.
	 *
	 * @since 1.0.0
	 *
	 * @param array $typesets
	 * @return array
	 */
	public function typeset_module_advanced( $typesets ) {
		if ( ! isset( $typesets['advanced'] ) ) {
			$typesets['advanced'] = array();
		}

		$typeset = array(
			/* **__****************************************************************************************************/
			/* _/  \__________________________________________________________________________________________________*/
			'php_memory_limit'           => array(
				'type'  => 'int',
				'value' => -1,
			),
			'php_max_execution_time'     => array(
				'type'  => 'int',
				'value' => -1,
			),
			'enable_title_rewrite'       => array(
				'type'  => 'boolean',
				'value' => false,
			),
			'enable_unprotect_post_meta' => array(
				'type'  => 'bool',
				'value' => false,
			),
			'enable_stop_heartbeat'      => array(
				'type'  => 'bool',
				'value' => false,
			),
			'enable_min_files'           => array(
				'type'  => 'bool',
				'value' => true,
			),
		);

		$typesets['advanced'] = array_replace_recursive(
			$typesets['advanced'],
			array( 'cast' => $typeset )
		);

		return $typesets;
	}

	/**
	 * Module Typeset - Debugger.
	 *
	 * @since 1.0.0
	 *
	 * @param array $typesets
	 * @return array
	 */
	public function typeset_module_debugger( $typesets ) {
		if ( ! isset( $typesets['debugger'] ) ) {
			$typesets['debugger'] = array();
		}

		$typeset = array(
			/* **__****************************************************************************************************/
			/* _/  \__________________________________________________________________________________________________*/
			'enable_errors'     => array(
				'type'  => 'bool',
				'value' => false,
			),
			'enable_wp_errors'  => array(
				'type'  => 'bool',
				'value' => false,
			),
			'enable_error_logs' => array(
				'type'  => 'bool',
				'value' => false,
			),

			/* **________**********************************************************************************************/
			/* _/ Errors \____________________________________________________________________________________________*/
			'show_timestamps'   => array(
				'type'  => 'bool',
				'value' => true,
			),
			'show_messages'     => array(
				'type'  => 'bool',
				'value' => true,
			),
			'show_details'      => array(
				'type'  => 'bool',
				'value' => false,
			),
			'show_data'         => array(
				'type'  => 'bool',
				'value' => false,
			),
		);

		$typesets['debugger'] = array_replace_recursive(
			$typesets['debugger'],
			array( 'cast' => $typeset )
		);

		return $typesets;
	}

	/**
	 * Module Typeset - MODULE.
	 *
	 * @since 1.0.0
	 *
	 * @param array $typesets
	 * @return array
	 */
	public function typeset_module_example( $typesets ) {
		if ( ! isset( $typesets['MODULE'] ) ) {
			$typesets['MODULE'] = array();
		}

		/* vvv --- Encapsulate Outer ---------------------------------------------------------------------------------*/
		$typeset = array(
			/* **__****************************************************************************************************/
			/* _/  \__________________________________________________________________________________________________*/
			'' => array(
				'type'  => '',
				'value' => '',
			),
		);
		/* ^^^ -------------------------------------------------------------------------------------------------------*/


		$typesets['MODULE'] = array_replace_recursive(
			$typesets['MODULE'],
			array( 'cast' => $typeset )
		);

		return $typesets;
	}

	/**
	 * Module Typeset - MODULE.
	 *
	 * @since 1.0.0
	 *
	 * @param array $typesets
	 * @return array
	 */
	public function typeset_module_example_alt( $typesets ) {
		$module_slug = 'MODULE';
		if ( ! isset( $typesets[ $module_slug ] ) ) {
			$typesets[ $module_slug ] = array();
		}

		/* vvv --- Encapsulate Outer ---------------------------------------------------------------------------------*/
		$typeset = array(
			/* **__****************************************************************************************************/
			/* _/  \__________________________________________________________________________________________________*/
			'' => array(
				'type'  => '',
				'value' => '',
			),
		);
		/* ^^^ -------------------------------------------------------------------------------------------------------*/


		$typesets[ $module_slug ] = array_replace_recursive(
			$typesets[ $module_slug ],
			array( 'cast' => $typeset )
		);

		return $typesets;
	}


	/**
	 * Options Defaults.
	 *
	 * @since 1.0.0
	 *
	 * @param string $action Action determines to get the default values, or fill in dynamic values.
	 *                       Accepts 'default', and 'fill'
	 * @return array
	 */
	public function options_defaults( $action = 'default' ) {
		$typesets = $this->get_options_typesets();
		return $this->typesetter->validate_values_with_typeset( $this->typesetter->get_typesets_default_values( $typesets, $action ), $typesets );
	}

	/**
	 * Get Options.
	 *
	 * Gets the Options from WordPress database and returns it. If there is no data,
	 * then set to defaults, save, and return options.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_options() {
		// TODO May want to use json.
		$options = get_option( $this->option_name );

		if ( false === $options ) {
			// Get initial value defaults.
			$options = $this->options_defaults();
			$this->update_options( $options );
		}

		// DO NOT VALIDATE! It will contaminate updates before the updater has had a chance to process any updates.
		// Also, Sanitize & Esc should occur at the latest instance possible.
		// Get dynamically filled defaults.
		$defaults = $this->options_defaults( 'fill' );
		$options = array_replace_recursive( $defaults, $options );
		$options = array_replace( $defaults, $options );

		return $options;
	}

	/**
	 * Get Module Options.
	 *
	 * @since 1.0.0
	 *
	 * @param string $module_slug The module to fetch.
	 * @param string $field_slug  (Optional) The individual variable to fetch.
	 * @return mixed|null Module options/variable. Null on failure.
	 */
	public static function get_module_options( $module_slug, $field_slug = '' ) {
		$gofer_seo_options = self::get_instance();

		if ( isset( $gofer_seo_options->options['modules'][ $module_slug ] ) ) {
			if ( ! empty( $field_slug ) && isset( $gofer_seo_options->options['modules'][ $module_slug ][ $field_slug ] ) ) {
				return $gofer_seo_options->options['modules'][ $module_slug ][ $field_slug ];
			}

			return $gofer_seo_options->options['modules'][ $module_slug ];
		}

		return null;
	}

	/**
	 * Update Options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options
	 * @param array $args
	 * @return boolean
	 */
	public function update_options( $options = array(), $args = array() ) {
		// Options.
		if ( empty( $options ) ) {
			$options = $this->options;
		} elseif ( ! is_array( $options ) ) {
			return false;
		}

		// Validate.
		$options = $this->typesetter->validate_values_with_typeset( $options, $this->get_options_typesets() );

		// Args.
		$args_default = array(
			'update_object' => true,
		);
		$args = wp_parse_args( $args, $args_default );

		// TODO May want to use json.
		update_option( $this->option_name, $options );

		// Keep object in sync with database, unless specified otherwise.
		if ( true === $args['update_object'] ) {
			$this->options = $options;
		}

		return true;
	}
}
