<?php
/**
 * Context Handler
 *
 * @package Gofer SEO
 */

/**
 * Website Context Handler.
 *
 * Handles data from multiple WordPress classes which contain content, structure, and properties.
 *
 * Multiton (Multi-Singleton)
 *
 * @since 1.0.0
 */
class Gofer_SEO_Context {

	/**
	 * Multi-Instances
	 *
	 * @since 1.0.0
	 *
	 * @var array $type {
	 *     @type array $key {
	 *         @type Gofer_SEO_Context
	 *     }
	 * }
	 */
	protected static $instances = array();

	/**
	 * Context Key (ID|slug).
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $context_type = '';

	/**
	 * Unique key for WP Objects
	 *
	 * Could be a numeric ID or a string Slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $context_key = '';

	/**
	 * WP Class Properties
	 *
	 * Stores essential properties to query by or potentially reduce querying.
	 *
	 * These properties can also be used with `$context` param to query by.
	 *
	 * @since 1.0.0
	 *
	 * @var array {
	 *     @type string       $taxonomy    (Optional) Terms limited to those matching `taxonomy`.
	 *                                     Used with: WP_Terms.
	 *     @type array|string $object_type Name(s) of the post type(s) the taxonomy object is registered for.
	 *                                     Used with: WP_Taxonomy.
	 *     @type string       $user_login  Username.
	 *                                     Used with: WP_User.
	 *     @type int          $site_id     Site ID.
	 *                                     Used with: WP_User.
	 * }
	 */
	public $wp_props = array();

	/**
	 * Get Instance
	 *
	 * @since 1.0.0
	 *
	 * @param string|array|Gofer_SEO_Context|WP_Site|WP_Post|WP_Post_Type|WP_Taxonomy|WP_Term|WP_User $context
	 * @return Gofer_SEO_Context
	 */
	public static function get_instance( $context = '' ) {
		$type  = self::get_context_type( $context );
		$key   = self::get_context_key( $context, $type );
		$props = self::get_wp_props( $context, $type, $key );

		if ( ! isset( self::$instances[ $type ] ) ) {
			self::$instances[ $type ] = array();
		}
		if ( ! isset( self::$instances[ $type ][ $key ] ) ) {
			if ( ! $context instanceof $type ) {
				$context = self::get_object( $type, $key, $props );
			}

			self::$instances[ $type ][ $key ] = new self( $context );
		}

		return self::$instances[ $type ][ $key ];
	}

	/**
	 * Gofer_SEO_Context constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $context
	 */
	protected function __construct( $context = '' ) {
		$type               = self::get_context_type( $context );
		$this->context_type = $type;
		$key                = self::get_context_key( $context, $type );
		$this->context_key  = $key;
		$props              = self::get_wp_props( $context, $type, $key );
		$this->wp_props     = $props;
	}

	/**
	 * Internationalize
	 *
	 * Dev Note: Could refactor this & \Gofer_SEO_Module_General::internationalize() to a static class.
	 *
	 * @since 1.0.0
	 *
	 * @param string $text
	 * @return mixed|string
	 */
	public function internationalize( $text ) {
		if ( function_exists( 'langswitch_filter_langs_with_message' ) ) {
			$text = langswitch_filter_langs_with_message( $text );
		}

		if ( function_exists( 'polyglot_filter' ) ) {
			$text = polyglot_filter( $text );
		}

		if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
			$text = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $text );
		} elseif ( function_exists( 'ppqtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
			$text = ppqtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $text );
		} elseif ( function_exists( 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
			$text = qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage( $text );
		}

		return $text;
	}

	/**
	 * Get current is_*() state.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function get_is() {
		$state_is = '';
		if ( is_front_page() || is_home() ) {
			global $wp_query;

			if ( $wp_query->is_front_page() ) {
				$state_is = 'front_page';
			} elseif ( $wp_query->is_posts_page ) {
				$state_is = 'posts_page';
			} else {
				// is_page().
				$state_is = 'home'; // Static front/home page.
			}
		} elseif ( is_archive() ) {
			if ( is_date() ) {
				$state_is = 'date_archive';
				if ( is_year() ) {
					$state_is = 'year_date_archive';
				} elseif ( is_month() ) {
					$state_is = 'month_date_archive';
				} elseif ( is_day() ) {
					$state_is = 'day_date_archive';
				}
			} elseif ( is_author() ) {
				$state_is = 'author_archive';
			} elseif ( is_post_type_archive() ) {
				// WooCommerce - `is_shop()`.
				$state_is = 'post_type_archive';
			} elseif ( is_tax() || is_category() || is_tag() ) {
				$state_is = 'taxonomy_term_archive';
			}
		} elseif ( is_singular() || is_single() ) {
			$post = get_post();

			$state_is = 'single_post';
			if ( is_post_type_hierarchical( $post->post_type ) ) {
				// BuddyPress - `bp_is_group()`, `bp_is_group_create()`, & `bp_is_user()`.
				$state_is = 'single_page';
			} elseif ( is_attachment() ) {
				$state_is = 'single_attachment';
			}
		} elseif ( is_search() ) {
			$state_is = 'search';
		} elseif ( is_attachment() ) {
			$state_is = 'attachment';
		} elseif ( is_404() ) {
			$state_is = '404';
		}

		return $state_is;
	}

	/**
	 * Get Object Type of Context
	 *
	 * @since 1.0.0
	 *
	 * @param string $context
	 * @return string
	 */
	public static function get_context_type( $context = '' ) {
		if ( is_array( $context ) && isset( $context['context_type'] ) ) {
			if ( 'WP_Site' === $context['context_type'] && ! class_exists( 'WP_Site' ) ) {
				$context['context_type'] = 'var_site';
			}
			return $context['context_type'];
		} elseif ( $context instanceof Gofer_SEO_Context || ! empty( $context->context_type ) ) {
			if ( 'WP_Site' === $context->context_type && ! class_exists( 'WP_Site' ) ) {
				$context->context_type = 'var_site';
			}
			return $context->context_type;
		}

		$obj_type = '';
		if ( $context instanceof WP_Network ) {
			$obj_type = 'WP_Site';
		} elseif ( $context instanceof WP_Site ) {
			$obj_type = 'WP_Site';
		} elseif ( $context instanceof WP_Post_Type ) {
			$obj_type = 'WP_Post_Type';
		} elseif ( $context instanceof WP_Taxonomy ) {
			$obj_type = 'WP_Taxonomy';
		} elseif ( $context instanceof WP_Term ) {
			$obj_type = 'WP_Term';
		} elseif ( $context instanceof WP_Post ) {
			$obj_type = 'WP_Post';
		} elseif ( $context instanceof WP_User ) {
			$obj_type = 'WP_User';
		}

		// If context isn't a WP object, or is empty, then set by current is_*() condition.
		if ( empty( $obj_type ) ) {
			$current_is = self::get_is();

			switch ( $current_is ) {
				case 'front_page':
					if ( is_multisite() ) {
						$obj_type = 'WP_Site';
					} else {
						$obj_type = 'var_site';
					}
					break;

				case 'home':
				case 'posts_page':
				case 'single_page':
				case 'single_post':
				case 'single_attachment':
				case 'attachment':
					$obj_type = 'WP_Post';
					break;

				case 'post_type_archive':
					$obj_type = 'WP_Post_Type';
					break;

				case 'taxonomy_term_archive':
					$obj_type = 'WP_Term';
					break;

				case 'date_archive':
					$obj_type = 'var_date';
					break;

				case 'year_date_archive':
					$obj_type = 'var_date_year';
					break;

				case 'month_date_archive':
					$obj_type = 'var_date_month';
					break;

				case 'day_date_archive':
					$obj_type = 'var_date_day';
					break;

				case 'author_archive':
					$obj_type = 'WP_User';
					break;

				case 'search':
					$obj_type = 'var_search';
					break;

				case '404':
					// TODO Find current object.
					break;
			}
		}

		return $obj_type;
	}

	/**
	 * Get (WP) Object ID
	 *
	 * Searches for an object's ID, if there is not an ID then the current ID available is fetched.
	 * This would also contain majority of the query operations for (individual) objects since this
	 * is a Unique Key for a given class type; wp_props is also used to refine a query.
	 *
	 * @since 1.0.0
	 *
	 * @param        $context
	 * @param string $type
	 * @return int
	 */
	public static function get_context_key( $context, $type = '' ) {
		if ( is_array( $context ) && isset( $context['context_key'] ) ) {
			return $context['context_key'];
		} elseif ( $context instanceof Gofer_SEO_Context || ! empty( $context->context_key ) ) {
			return $context->context_key;
		}

		$key = 0;
		if ( empty( $type ) ) {
			$type = self::get_context_type( $context );
		}
		switch ( $type ) {
			case 'var_site':
				$key = 0;
				break;

			case 'WP_Site':
				if ( $context instanceof WP_Site ) {
					$key = $context->blog_id;
				} else {
					$key = get_current_blog_id();
				}
				break;

			case 'WP_Post':
				if ( ! $context instanceof WP_Post ) {
					if ( 'posts_page' === self::get_is() ) {
						$context = get_queried_object();
					} else {
						global $post;
						$context = $post;
					}
				}
				if ( $context instanceof WP_Post ) {
					$key = $context->ID;
					if ( empty( $key ) ) {
						$key = get_queried_object_id();
					}
				}

				break;

			case 'WP_Post_Type':
				if ( ! $context instanceof WP_Post_Type ) {
					$context = get_queried_object();
				}
				$key = $context->name;
				break;

			case 'WP_Taxonomy':
				if ( ! $context instanceof WP_Taxonomy ) {
					$context = get_queried_object();
				}
				$key = $context->name;
				break;

			case 'WP_Term':
				if ( ! $context instanceof WP_Term ) {
					$context = get_queried_object();
				}
				$key = $context->term_id;
				break;

			case 'WP_User':
				if ( $context instanceof WP_User ) {
					$key = $context->ID;
					break;
				}

				if ( is_array( $context ) && is_array( $context['wp_props'] ) ) {
					if ( ! empty( $context['wp_props']['user_nicename'] ) ) {
						$context = get_user_by( 'slug', $context['wp_props']['user_nicename'] );
					} elseif ( ! empty( $context['wp_props']['user_email'] ) ) {
						$context = get_user_by( 'email', $context['wp_props']['user_email'] );
					} elseif ( ! empty( $context['wp_props']['user_login'] ) ) {
						$context = get_user_by( 'login', $context['wp_props']['user_login'] );
					}
				}

				if ( $context instanceof WP_User ) {
					$key = $context->ID;
				} else {
					// Current author/user page on frontend.
					$key = get_the_author_meta( 'ID' );
				}
				break;

			default:
				// Do stuff.
		}

		return $key;
	}

	/**
	 * Get (Required/Requested) WP Object Fields
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $context
	 * @param string $type
	 * @param string $key
	 * @return array|mixed
	 */
	public static function get_wp_props( $context, $type = '', $key = '' ) {
		$wp_props = array();
		if ( empty( $type ) ) {
			$type = self::get_context_type( $context );
		}
		if ( empty( $key ) && 0 !== $key ) {
			$key = self::get_context_key( $context, $type );
		}

		if ( is_array( $context ) && isset( $context['wp_props'] ) ) {
			$wp_props = $context['wp_props'];
		} elseif ( $context instanceof Gofer_SEO_Context || ! empty( $context->wp_props ) ) {
			$wp_props = $context->wp_props;
		}

		$object = new stdClass();
		switch ( $type ) {
			case 'WP_Term':
				// $object = self::get_object( $type, $key, $wp_props );
				// $wp_props['taxonomy'] = $object->taxonomy;
				break;
			case 'WP_User':
				$object = self::get_object( $type, $key, $wp_props );
				// $wp_props['user_login'] = $object->user_login;
				$wp_props['site_id'] = $object->site_id;
				break;
		}

		// Also get only the object properties that match in $context['wp_props'] | $context->wp_props.
		foreach ( $wp_props as $key => $value ) {
			if ( isset( $object->$key ) ) {
				$wp_props[ $key ] = $object->$key;
			}
		}

		return $wp_props;
	}

	/**
	 * Get Object
	 *
	 * @since 1.0.0
	 *
	 * @param string $type WP object type.
	 * @param string $key  Integer or slug.
	 * @param array  $args
	 * @return false|WP_Site|WP_Post|WP_Post_Type|WP_Taxonomy|WP_Term|WP_User
	 */
	public static function get_object( $type, $key, $args = array() ) {
		$object = false;
		switch ( $type ) {
			case 'var_site':
			case 'var_search':
			case 'var_date':
			case 'var_date_year':
			case 'var_date_month':
			case 'var_date_day':
				$object = array(
					'context_type' => $type,
					'context_key'  => $key,
				);
				break;

			case 'WP_Site':
				/*
				 * PHP 5.2 conflict. Could merge WP_Site & WP_Post together after WP 5.1 becomes the required version.
				 *
				 * Change to...
				 * $object = $type::get_instance( $key );
				 */

				$object = WP_Site::get_instance( $key );
				break;

			case 'WP_Post':
				$object = WP_Post::get_instance( $key );
				break;

			case 'WP_Post_Type':
				$object = get_post_type_object( $key );
				if ( is_null( $object ) ) {
					$object = false;
				}
				break;

			case 'WP_Taxonomy':
				$object = get_taxonomy( $key );
				break;

			case 'WP_Term':
				$taxonomy = isset( $args['taxonomy'] ) ? $args['taxonomy'] : null;
				$object   = WP_Term::get_instance( $key, $taxonomy );
				break;

			case 'WP_User':
				$name    = isset( $args['user_login'] ) ? $args['user_login'] : '';
				$site_id = isset( $args['site_id'] ) ? $args['site_id'] : '';
				$object  = new WP_User( $key, $name, $site_id );
				break;
		}

		return $object;
	}

	/**
	 * Get Slug
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_slug() {
		$slug   = '';
		$wp_obj = self::get_object( $this->context_type, $this->context_key, $this->wp_props );
		if ( ! $wp_obj ) {
			new Gofer_SEO_Error( 'gofer_seo_context_no_wp_obj', 'No WP object found.' );
			return $slug;
		}
		switch ( $this->context_type ) {
			case 'var_site':
				break;
			case 'WP_Post':
				$slug = $wp_obj->post_name;
				break;
			case 'WP_Post_Type':
				$slug = $wp_obj->name;
				break;
			case 'WP_Taxonomy':
				$slug = $wp_obj->name;
				break;
			case 'WP_Term':
				$slug = $wp_obj->slug;
				break;
			case 'WP_User':
				$slug = $wp_obj->user_login;
				break;
		}

		return $slug;
	}

	public function get_nicename() {}

	/**
	 * Get Display Name
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_display_name() {
		$display_name = '';
		switch ( $this->context_type ) {
			case 'var_site':
				$display_name = get_bloginfo( 'name' );
				break;

			case 'WP_Site':
				$wp_obj       = self::get_object( $this->context_type, $this->context_key );
				$display_name = $wp_obj->blogname;
				break;

			case 'WP_Post':
				$wp_obj       = self::get_object( $this->context_type, $this->context_key );
				if ( ! $wp_obj ) {
					new Gofer_SEO_Error( 'gofer_seo_context_no_wp_obj', 'No WP object found.' );
					return $display_name;
				}
				$display_name = $wp_obj->post_title;
				break;

			case 'WP_Post_Type':
				$wp_obj       = self::get_object( $this->context_type, $this->context_key );
				$display_name = $wp_obj->label;
				break;

			case 'WP_Taxonomy':
				$wp_obj       = self::get_object( $this->context_type, $this->context_key );
				$display_name = $wp_obj->label;
				break;

			case 'WP_Term':
				$wp_obj       = self::get_object( $this->context_type, $this->context_key, $this->wp_props );
				$display_name = $wp_obj->name;
				break;

			case 'WP_User':
				$wp_obj       = self::get_object( $this->context_type, $this->context_key, $this->wp_props );
				$display_name = $wp_obj->display_name;
				break;

			case 'var_search':
				/* translators: %s is replaces with the visitor's current search string. */
				$display_name = sprintf( __( 'Search results for \'%s\'', 'gofer-seo' ), esc_html( get_search_query() ) );
				break;

			case 'var_date_year':
				/* translators: %s is replaced with the Year. */
				$display_name = sprintf( __( 'Year: %s', 'gofer-seo' ), get_the_date( 'Y' ) );
				break;

			case 'var_date_month':
				/* translators: %s is replaced with the Month Year */
				$display_name = sprintf( __( 'Month: %s', 'gofer-seo' ), get_the_date( 'F Y' ) );
				break;

			case 'var_date_day':
			case 'var_date':
				/* translators: %s is replaced with the Month Day, Year. */
				$display_name = sprintf( __( 'Day: %s', 'gofer-seo' ), get_the_date( 'F j, Y' ) );
				break;
		}

		return $display_name;
	}

	/**
	 * Get URL (Page)
	 *
	 * Uses a static variable for performance faulty operations; only use with heavy operations.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_url() {
		static $s_url;
		if ( is_null( $s_url ) ) {
			$s_url = array();
		}
		if ( ! isset( $s_url[ $this->context_type ] ) || ! is_array( $s_url[ $this->context_type ] ) ) {
			$s_url[ $this->context_type ] = array();
		}
		if ( ! empty( $s_url[ $this->context_type ][ $this->context_key ] ) ) {
			return $s_url[ $this->context_type ][ $this->context_key ];
		}

		$url = '';
		switch ( $this->context_type ) {
			case 'var_site':
				$url = home_url();
				break;

			case 'WP_Site':
				$url = get_site_url( $this->context_key );
				break;

			case 'WP_Post':
				$wp_obj = self::get_object( $this->context_type, $this->context_key );
				if ( ! $wp_obj ) {
					$s_url[ $this->context_type ][ $this->context_key ] = $url;
					new Gofer_SEO_Error( 'gofer_seo_context_no_wp_obj', 'No WP object found.' );
					return $url;
				}

				if ( 'attachment' === $wp_obj->post_type ) {
					// Source URL.
					// May need to check setting for attachment redirect.
					// Use $this->get_images() to get attachment link.
					// $url = wp_get_attachment_url( $wp_obj->ID );
					// (Attachment) Post URL.
					$url = get_permalink( $wp_obj );
				} else {
					$url = get_permalink( $wp_obj );
				}

				if ( false === $url ) {
					$url = '';
				}

				$s_url[ $this->context_type ][ $this->context_key ] = $url;
				break;

			case 'WP_Post_Type':
				$url    = get_post_type_archive_link( $this->context_key );
				break;

			case 'WP_Taxonomy':
				// Does not exist.
				break;

			case 'WP_Term':
				$wp_obj   = self::get_object( $this->context_type, $this->context_key );
				$taxonomy = isset( $this->wp_props['taxonomy'] ) ? $this->wp_props['taxonomy'] : '';
				$url      = get_term_link( $wp_obj, $taxonomy );

				$s_url[ $this->context_type ][ $this->context_key ] = $url;
				break;
			case 'WP_User':
				$url = get_author_posts_url( $this->context_key );
				break;

			case 'var_search':
				$url = get_search_link();
				break;

			case 'var_date_year':
				$url = get_year_link( get_query_var( 'year' ) );

				$queried_post_type = get_query_var( 'post_type' );
				if ( ! empty( $queried_post_type ) ) {
					$url = add_query_arg( 'post_type', $queried_post_type, $url );
				}
				break;

			case 'var_date_month':
				$url = get_month_link( get_query_var( 'year' ), get_query_var( 'monthnum' ) );

				$queried_post_type = get_query_var( 'post_type' );
				if ( ! empty( $queried_post_type ) ) {
					$url = add_query_arg( 'post_type', $queried_post_type, $url );
				}
				break;

			case 'var_date_day':
			case 'var_date':
				$url = get_day_link( get_query_var( 'year' ), get_query_var( 'monthnum' ), get_query_var( 'day' ) );

				$queried_post_type = get_query_var( 'post_type' );
				if ( ! empty( $queried_post_type ) ) {
					$url = add_query_arg( 'post_type', $queried_post_type, $url );
				}
				break;
		}

		return $url;
	}

	/**
	 * Get Canonical URL.
	 *
	 * Uses a static variable for URLs (`$s_canonical_url[][]`)
	 * that cause performance faulty operations; only use with heavy operations.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_canonical_url() {
		static $s_canonical_url;
		if ( is_null( $s_canonical_url ) ) {
			$s_canonical_url = array();
		}
		if ( ! isset( $s_canonical_url[ $this->context_type ] ) || ! is_array( $s_canonical_url[ $this->context_type ] ) ) {
			$s_canonical_url[ $this->context_type ] = array();
		}
		if ( ! empty( $s_canonical_url[ $this->context_type ][ $this->context_key ] ) ) {
			return $s_canonical_url[ $this->context_type ][ $this->context_key ];
		}

		$canonical_url = '';
		switch ( $this->context_type ) {
			case 'WP_Post':
				$wp_obj = self::get_object( $this->context_type, $this->context_key );
				if ( ! $wp_obj ) {
					$s_canonical_url[ $this->context_type ][ $this->context_key ] = $canonical_url;
					new Gofer_SEO_Error( 'gofer_seo_context_no_wp_obj', 'No WP object found.' );
					return $canonical_url;
				}

				if ( 'attachment' === $wp_obj->post_type ) {
					// Source URL.
					// May need to check setting for attachment redirect.
					// Use $this->get_images() to get attachment link.
					// $canonical_url = wp_get_attachment_url( $wp_obj->ID );
					// (Attachment) Post URL.
					$canonical_url = $this->get_url();
				} else {
					$canonical_url = get_permalink( $wp_obj );
//					$canonical_url = $this->get_url();

					if ( get_queried_object_id() === $wp_obj->ID ) {
						$page_number = get_query_var( 'page', 0 );
						if ( empty( $page_number ) ) {
							$page_number = get_query_var( 'paged', 0 );
						}

						if ( 1 < $page_number ) {
							if ( get_query_var( 'page' ) === $page_number ) {
								if ( ! get_option( 'permalink_structure' ) ) {
									// Non-pretty urls.
									$canonical_url = add_query_arg( 'page', $page_number, $canonical_url );
								} else {
									$canonical_url = trailingslashit( $canonical_url ) . user_trailingslashit( $page_number, 'single_paged' );
								}
							} else {
//								if ( get_query_var( 'p' ) || get_query_var( 'name' ) ) {
								if ( ! get_option( 'permalink_structure' ) ) {
									// Non-pretty urls.
									$canonical_url = add_query_arg( 'page', $page_number, $canonical_url );
								} else {
									$canonical_url = trailingslashit( $canonical_url ) . user_trailingslashit( $page_number, 'single_paged' );
								}
							}
						}

						$comment_page_number = get_query_var( 'cpage', 0 );
						if ( $comment_page_number ) {
							$canonical_url = get_comments_pagenum_link( $comment_page_number );
						}
					}
				}

				if ( false === $canonical_url ) {
					$canonical_url = '';
				}

				$s_canonical_url[ $this->context_type ][ $this->context_key ] = $canonical_url;
				break;

			case 'WP_Term':
				$wp_obj = self::get_object( $this->context_type, $this->context_key );
				if ( ! $wp_obj ) {
					$s_canonical_url[ $this->context_type ][ $this->context_key ] = $canonical_url;
					new Gofer_SEO_Error( 'gofer_seo_context_no_wp_obj', 'No WP object found.' );
					return $canonical_url;
				}

				$canonical_url = $this->get_url();

				$context_type = self::get_context_type();
				$context_key  = self::get_context_key( '', $context_type );
				if ( $context_type === $this->context_type && $context_key === $this->context_key ) {
					$page_number = get_query_var( 'page', 0 );
					if ( empty( $page_number ) ) {
						$page_number = get_query_var( 'paged', 0 );
					}

					if ( 1 < $page_number ) {
						$pagination_base_name = 'page';
						if ( ! empty( $wp_rewrite ) && ! empty( $wp_rewrite->pagination_base ) ) {
							$pagination_base_name = $wp_rewrite->pagination_base;
						}

						if ( get_query_var( 'paged' ) === $page_number ) {
							if ( ! get_option( 'permalink_structure' ) ) {
								// Non-pretty urls.
								$canonical_url = add_query_arg( 'paged', $page_number, $canonical_url );
							} else {
								$canonical_url = trailingslashit( $canonical_url ) . user_trailingslashit( trailingslashit( $pagination_base_name ) . $page_number, 'paged' );
							}
						} else {
//							$taxonomy = get_query_var( 'taxonomy', '' );
//							if (
//									get_query_var( 'cat' ) ||
//									get_query_var( 'tag_id' ) ||
//									(
//										get_query_var( 'taxonomy' ) &&
//										get_query_var( 'term' ) &&
//										get_query_var( $taxonomy )
//									)
//							) {
							if ( ! get_option( 'permalink_structure' ) ) {
								// Non-pretty urls.
								$canonical_url = add_query_arg( 'paged', $page_number, $canonical_url );
							} else {
								$canonical_url = trailingslashit( $canonical_url ) . user_trailingslashit( trailingslashit( $pagination_base_name ) . $page_number, 'paged' );
							}
						}
					}
				}

				if ( false === $canonical_url ) {
					$canonical_url = '';
				}

				$s_canonical_url[ $this->context_type ][ $this->context_key ] = $canonical_url;
				break;

			case 'var_date':
			case 'var_date_year':
			case 'var_date_month':
			case 'var_date_day':
				$wp_obj = self::get_object( $this->context_type, $this->context_key );
				if ( ! $wp_obj ) {
					$s_canonical_url[ $this->context_type ][ $this->context_key ] = $canonical_url;
					new Gofer_SEO_Error( 'gofer_seo_context_no_wp_obj', 'No WP object found.' );
					return $canonical_url;
				}

				$canonical_url = $this->get_url();

				$context_type = self::get_context_type();
				$context_key  = self::get_context_key( '', $context_type );
				if ( $context_type === $this->context_type && $context_key === $this->context_key ) {
					$page_number = get_query_var( 'page', 0 );
					if ( empty( $page_number ) ) {
						$page_number = get_query_var( 'paged', 0 );
					}

					if ( 1 < $page_number ) {
						$pagination_base_name = 'page';
						if ( ! empty( $wp_rewrite ) && ! empty( $wp_rewrite->pagination_base ) ) {
							$pagination_base_name = $wp_rewrite->pagination_base;
						}

						if ( get_query_var( 'paged' ) === $page_number ) {
							if ( ! get_option( 'permalink_structure' ) ) {
								// Non-pretty urls.
								$canonical_url = add_query_arg( 'paged', $page_number, $canonical_url );
							} else {
								$canonical_url = trailingslashit( $canonical_url ) . user_trailingslashit( trailingslashit( $pagination_base_name ) . $page_number, 'paged' );
							}
						} else {
//							if (
//									get_query_var( 'year' ) ||
//									get_query_var( 'monthnum' ) ||
//									get_query_var( 'day' ) ||
//									get_query_var( 'm' )
//							) {
							if ( ! get_option( 'permalink_structure' ) ) {
								// Non-pretty urls.
								$canonical_url = add_query_arg( 'paged', $page_number, $canonical_url );
							} else {
								$canonical_url = trailingslashit( $canonical_url ) . user_trailingslashit( trailingslashit( $pagination_base_name ) . $page_number, 'paged' );
							}
						}
					}

					$canonical_url = user_trailingslashit( $canonical_url, 'paged' );
				}

				if ( false === $canonical_url ) {
					$canonical_url = '';
				}

				$s_canonical_url[ $this->context_type ][ $this->context_key ] = $canonical_url;
				break;

			case 'var_site':
			case 'WP_Site':
			case 'WP_Post_Type':
			case 'WP_Taxonomy':
			case 'WP_User':
			case 'var_search':
				$canonical_url = $this->get_url();
				break;

		}

		return $canonical_url;
	}

	/**
	 * Get Description
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_description() {
		$desc = '';

		switch ( $this->context_type ) {
			case 'var_site':
				$desc = get_bloginfo( 'description' );
				break;

			case 'WP_Site':
				$desc = get_blog_details( array( 'blog_id' => $this->context_key ) );
				if ( empty( $desc ) ) {
					$desc = get_bloginfo( 'description' );
				}
				break;

			case 'WP_Post':
				$wp_obj = self::get_object( $this->context_type, $this->context_key );
				if ( ! $wp_obj ) {
					new Gofer_SEO_Error( 'gofer_seo_context_no_wp_obj', 'No WP object found.' );
					return $desc;
				}

				$post_description = '';
				if (
						! $post_description &&
						! post_password_required( $wp_obj ) &&
						! empty( $wp_obj->post_excerpt )
				) {
					$post_description = $wp_obj->post_excerpt;
				}

				if ( ! empty( $post_description ) && is_string( $post_description ) ) {
					$desc = $post_description;
				}
				break;

			case 'WP_Post_Type':
				$wp_obj = self::get_object( $this->context_type, $this->context_key );
				$desc   = $wp_obj->description;
				break;

			case 'WP_Taxonomy':
				$wp_obj = self::get_object( $this->context_type, $this->context_key, $this->wp_props );
				$desc = $wp_obj->description;
				break;

			case 'WP_Term':
				$wp_obj = self::get_object( $this->context_type, $this->context_key, $this->wp_props );
				$desc   = $wp_obj->description;
				break;

			case 'WP_User':
				break;
			case 'var_search':
				break;
			case 'var_date_year':
				break;
			case 'var_date_month':
				break;
			case 'var_date_day':
				break;
			case 'var_date':
				break;

		}

		return $desc;
	}

	/**
	 * Get Image Context
	 *
	 * Returns Image ID (Context Key) if possible, and Image URL.
	 *
	 * This is used to get the Image WP_Post object via $context.
	 *
	 * attachment post parent.
	 * registered images to post.
	 * post content.
	 *
	 * @param string|array
	 * @return array {
	 *     @type int|string $id
	 *     @type string     $url
	 * }
	 */
	public function get_images( $sources = 'all' ) {
		$image = array();
		switch ( $this->context_type ) {
			case 'WP_Post':
				$wp_obj = self::get_object( $this->context_type, $this->context_key );
				if ( ! $wp_obj ) {
					new Gofer_SEO_Error( 'gofer_seo_context_no_wp_obj', 'No WP object found.' );
					return $image;
				}

				if ( 'attachment' === $wp_obj->post_type ) {
					$images['attachments'][] = array(
						'id'  => $wp_obj->ID,
						'url' => wp_get_attachment_url( $wp_obj->ID ),
					);
				}

				$media_list = get_attached_media( 'image', $wp_obj );

				break;
		}
	}

	/**
	 * Get Breadcrumb
	 *
	 * @since 1.0.0
	 *
	 * @return array {
	 *     @type array $index {
	 *         @type int    $position
	 *         @type string $title
	 *         @type string $url
	 *     }
	 * }
	 */
	public function get_breadcrumb() {
		$rtn_list = array();
		// WP_Post & WP_Terms could be merged once a parent_id() method is created.
		$context = $this;
		switch ( $this->context_type ) {
			case 'var_site':
			case 'WP_Site':
				// Site data added at last.
				break;

			case 'WP_Post':
				$object = self::get_object( $this->context_type, $this->context_key );
				if ( ! $object ) {
					new Gofer_SEO_Error( 'gofer_seo_context_no_wp_obj', 'No WP object found.' );
					break;
				}

				do {
					array_unshift(
						$rtn_list,
						array(
							'name' => $context->get_display_name(),
							'url'  => $context->get_canonical_url(),
						)
					);

					$object  = self::get_object( $context->context_type, $object->post_parent );
					$context = self::get_instance( $object );
				} while ( $object );
				break;

			case 'WP_Post_Type':
				array_unshift(
					$rtn_list,
					array(
						'name' => $context->get_display_name(),
						'url'  => $context->get_canonical_url(),
					)
				);
				break;

			case 'WP_Taxonomy':
				// No URL destination exists to trigger this.
				break;

			case 'WP_Term':
				$object = self::get_object( $context->context_type, $context->context_key, $context->wp_props );
				do {
					array_unshift(
						$rtn_list,
						array(
							'name' => $context->get_display_name(),
							'url'  => $context->get_canonical_url(),
						)
					);

					$object  = self::get_object( $context->context_type, $object->parent, $context->wp_props );
					$context = self::get_instance( $context );
				} while ( $object );
				break;

			case 'var_date':
			case 'var_date_day':
				array_unshift(
					$rtn_list,
					array(
						'name' => $context->get_display_name(),
						'url'  => $context->get_canonical_url(),
					)
				);
				$context = array(
					'context_type' => 'var_date_month',
					'context_key'  => 0,
				);
				$context = Gofer_SEO_Context::get_instance( $context );
				// Fall through.
			case 'var_date_month':
				array_unshift(
					$rtn_list,
					array(
						'name' => $context->get_display_name(),
						'url'  => $context->get_canonical_url(),
					)
				);
				$context = array(
					'context_type' => 'var_date_year',
					'context_key'  => 0,
				);
				$context = Gofer_SEO_Context::get_instance( $context );
				// Fall through.
			case 'var_date_year':
			case 'WP_User':
			case 'var_search':
				array_unshift(
					$rtn_list,
					array(
						'name' => $context->get_display_name(),
						'url'  => $context->get_canonical_url(),
					)
				);
				break;
		}

		// Add Homepage as root/base.
		$site_context = array();
		if ( is_multisite() ) {
			$site_context['context_type'] = 'WP_Site';
			$site_context['context_key']  = get_current_blog_id();
		} else {
			$site_context['context_type'] = 'var_site';
			$site_context['context_key']  = 0;
		}
		$site_context = self::get_instance( $site_context );

		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		$title = $gofer_seo_options->options['modules']['general']['site_title'];
		if ( empty( $title ) ) {
			$title = $site_context->get_display_name();
		}
		if ( empty( $title ) ) {
			preg_match(
				'/^(?:https|http)(?:\:\/\/)(?:www\.)?([a-zA-Z0-9-]+\.)?([a-zA-Z0-9-]+)(?:\.[a-z]+)(?:\/)?(?:[a-z]+\/?)?$/',
				$site_context->get_url(),
				$matches
			);
			$title = $matches[1] . $matches[2];
		}

		array_unshift(
			$rtn_list,
			array(
				'name' => $title,
				'url'  => $site_context->get_url() . '/',
			)
		);

		// Add position values.
		foreach ( $rtn_list as $index => &$item ) {
			$item['position'] = $index + 1;
		}

		return $rtn_list;
	}

}
