<?php
/**
 * Gofer SEO - Module General
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Module_General
 *
 * @since 1.0.0
 */
class Gofer_SEO_Module_General extends Gofer_SEO_Module {

	/**
	 * Max Description Length
	 *
	 * Max numbers of chars in auto-generated description.
	 *
	 * @since 1.0.0
	 *
	 * @var int $maximum_description_length
	 */
	var $maximum_description_length = 320;

	/**
	 * Min Description Length
	 *
	 * Minimum number of chars an excerpt should be so that it can be used as description.
	 *
	 * @since 1.0.0
	 *
	 * @var int $minimum_description_length
	 */
	var $minimum_description_length = 1;

	/**
	 * OB Start Detected
	 *
	 * Whether output buffering is already being used during forced title rewrites.
	 *
	 * @since 1.0.0
	 *
	 * @var bool $ob_start_detected
	 */
	var $ob_start_detected = false;

	/**
	 * Gofer_SEO_Module_General constructor.
	 *
	 * @since 1.0.0
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Load.
	 *
	 * @since 1.0.0
	 */
	public function load() {
		parent::load();

		add_action( 'template_redirect', array( $this, 'template_redirect_attachment' ) );
		add_action( 'split_shared_term', array( $this, 'split_shared_term' ), 10, 4 );

		add_filter( 'gofer_seo_filter_title', array( $this, 'filter_title' ) );
		add_filter( 'gofer_seo_filter_description', array( $this, 'filter_description' ), 10, 3 );

		if ( ! is_admin() ) {
			// Frontend only. DO NOT load in Admin side.
			add_action( 'gofer_seo_wp_head', array( $this, 'google_analytics' ) );
			add_action( 'gofer_seo_wp_head', array( $this, 'wp_head' ), 3 );
			add_action( 'template_redirect', array( $this, 'template_redirect' ), 0 );

			$gofer_seo_options = Gofer_SEO_Options::get_instance();
			if ( $gofer_seo_options->options['modules']['general']['enable_canonical'] ) {
				remove_action( 'wp_head', 'rel_canonical' );
				add_action( 'gofer_seo_wp_head', array( $this, 'rel_canonical' ) );
			}
		}
	}

	/**
	 * Initialize Module.
	 *
	 * Mainly used for adding action/filter hooks.
	 * There may be some function/method calls, but avoid adding code with operations/processes.
	 *
	 * @since 1.0.0
	 */
	function init() {
		parent::init();

		if ( ! is_admin() ) {
			add_action( 'amp_post_template_head', array( $this, 'amp_head' ), 11 );
		}
	}

	/**
	 * AMP Head
	 *
	 * Adds meta description to AMP pages.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function amp_head() {
		if ( ! $this->is_enabled_post_type() ) {
			return;
		}

		// Get the description.
		if ( ! is_front_page() && ! is_paged() ) {
			$post = $this->get_queried_object();
			$description = $this->get_the_description( $post );
			$description = $this->apply_filter_description( $description );

			/**
			 * Gofer SEO AMP Description.
			 *
			 * To disable AMP meta description just __return_false on the gofer_seo_amp_description filter.
			 *
			 * @since 1.0.0
			 *
			 * @param string $description
			 */
			$description = apply_filters( 'gofer_seo_amp_description', $description );

			if ( false !== $description && ! empty( $description ) && $this->minimum_description_length < Gofer_SEO_PHP_Functions::strlen( $description ) ) {
				$description = gofer_seo_trim_str( $description );

				$desc_attr   = '';
				$desc_attr   = apply_filters( 'gofer_seo_amp_description_attributes', $desc_attr );

				$meta_string  = sprintf( '<meta name="description" %1$s content="%2$s" />', $desc_attr, $description );
				$meta_string .= PHP_EOL;
				echo gofer_seo_esc_head( $meta_string );
			}
		}
	}

	/**
	 * WP Head
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function wp_head() {
		// Check if we're in the main query to support bad themes and plugins.
		global $wp_query;
		$old_wp_query = null;
		if ( ! $wp_query->is_main_query() ) {
			$old_wp_query = $wp_query;
			wp_reset_postdata();
		}

		if ( ! $this->is_seo_enabled() ) {

			$robots_meta = $this->get_robots_meta_tag();

			if ( ! empty( $robots_meta ) ) {
				echo gofer_seo_esc_head( $robots_meta );
			}

			if ( ! $this->analytics_excluded() ) {
				remove_action( 'gofer_seo_wp_head', array( $this, 'google_analytics' ) );
				add_action( 'wp_head', array( $this, 'google_analytics' ) );
			}

			if ( ! empty( $old_wp_query ) ) {
				// Restores the query back after operations are finished.
				// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				$GLOBALS['wp_query'] = $old_wp_query;
				unset( $old_wp_query );
			}

			return;
		}

		if ( ! $this->is_enabled_post_type() ) {
			return;
		}

		global $posts;
		static $dup_counter = 0;
		$dup_counter ++;
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		if ( $dup_counter > 1 ) {
			/* translators: %1$s is the plugin name. %2$s is the current filter running the method. %3$s is an integer counter. */
			$html = sprintf(
				/* translators: %1$s is the plugin name, %2$s is the hook name, and %3$s is the an integer count. */
				__( 'Debug Warning: %1$s meta data was included again from %2$s filter. Called %3$s times!', 'gofer-seo' ),
				GOFER_SEO_NAME,
				current_filter(),
				$dup_counter
			);
			echo sprintf( '%1$s<!-- %2$s -->%1$s', esc_html( $html ), PHP_EOL );

			if ( ! empty( $old_wp_query ) ) {
				// Restores the query back after operations are finished.
				// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				$GLOBALS['wp_query'] = $old_wp_query;
				unset( $old_wp_query );
			}

			return;
		}
		if ( is_home() && ! is_front_page() ) {
			$post = Gofer_SEO_Methods::get_blog_page();
		} else {
			$post = $this->get_queried_object();
		}

		$meta_string = '';
		$description = '';
		// Logging - rewrite handler check for output buffering.
		$this->check_rewrite_handler();


		if ( $this->ob_start_detected ) {
			// TODO Log notice message.
//			echo 'ob_start_detected ';
		}

		if ( function_exists( 'wc_get_page_id' ) ) {
			$wc_shop_id = wc_get_page_id( 'shop' );
			$gofer_seo_options = Gofer_SEO_Options::get_instance();

			if ( is_post_type_archive( 'product' ) ) {
				if ( $wc_shop_id ) {
					$wc_post = get_post( $wc_shop_id );

					if ( get_option( 'page_on_front' ) === $wc_shop_id && $gofer_seo_options->options['modules']['general']['use_static_homepage'] ) {
						$post = $wc_post;
					} elseif ( get_option( 'page_on_front' ) !== $wc_shop_id ) {
						$post = get_post( $wc_shop_id );
					}
				}
			}
		}

		// Handle the description format.
		// We are not going to mandate that post description needs to be present because the content
		// could be derived from a custom field too.
		if ( ! ( is_front_page() && is_paged() ) ) {
			if ( ! isset( $meta_string ) ) {
				$meta_string = '';
			}

			$description = $this->get_the_description( $post );
			// TODO Generate Description if still empty.

			$screen             = is_admin() ? get_current_screen() : null;
			$ignore_php_version = $screen && isset( $screen->id ) && 'post' === $screen->id;
			$description        = $this->apply_filter_description( $description, true, $ignore_php_version );

			$desc_attr   = '';
			/**
			 * Meta Description Attributes.
			 *
			 * @since 1.0.0
			 *
			 * @param string $desc_attr An element attribute string to add to the meta element.
			 */
			$desc_attr   = apply_filters( 'gofer_seo_description_attributes', $desc_attr );
			if ( ! empty( $description ) ) {
				$meta_string .= sprintf(
					'<meta name="description" %s content="%s" />%s',
					$desc_attr,
					$description,
					PHP_EOL
				);
			}

			if ( $post instanceof WP_Post ) {
				$args_author = array(
					'context_type' => 'WP_User',
					'context_key'  => $post->post_author,
				);
				$author_context = Gofer_SEO_Context::get_instance( $args_author );

				$author_attr = apply_filters( 'gofer_seo_general_author_attributes', '' );
				$meta_string .= sprintf(
					'<link rel="author" %1$s href="%2$s" />%3$s',
					$author_attr,
					$author_context->get_url(),
					PHP_EOL
				);
			}

			if ( $post instanceof WP_Post ) {
				$args_site = array(
					'context_type' => 'WP_Site',
				);
				$site_context = Gofer_SEO_Context::get_instance( $args_site );

				$publisher_attr = apply_filters( 'gofer_seo_general_publisher_attributes', '' );
				$meta_string .= sprintf(
					'<link rel="publisher" %1$s href="%2$s" />%3$s',
					$publisher_attr,
					$site_context->get_url(),
					PHP_EOL
				);
			}

			// Get the keywords.
			$keywords = $this->get_the_keywords();
			if ( ! empty( $keywords ) ) {
				if ( isset( $meta_string ) ) {
					$meta_string .= PHP_EOL;
				}

				$keywords     = wp_filter_nohtml_kses( str_replace( '"', '', $keywords ) );
				$key_attr     = apply_filters( 'gofer_seo_general_keywords_attributes', '' );
				$meta_string .= sprintf(
					'<meta name="keywords" %s content="%s" />%s',
					$key_attr,
					$keywords,
					PHP_EOL
				);
			}
		}

		$robots_meta = $this->get_robots_meta_tag();

		if ( ! empty( $robots_meta ) ) {
			$meta_string .= $robots_meta;
		}

		// Handle site verification.
		if ( is_front_page() ) {
			foreach (
				array(
					'google'    => 'google-site-verification',
					'bing'      => 'msvalidate.01',
					'pinterest' => 'p:domain_verify',
					'yandex'    => 'yandex-verification',
					'baidu'     => 'baidu-site-verification',
				) as $k => $v
			) {
				if ( ! empty( $gofer_seo_options->options['modules']['general'][ 'verify_' . $k ] ) ) {
					$meta_string .= sprintf(
						'<meta name="%s" content="%s" />%s',
						$v,
						trim( wp_strip_all_tags( $gofer_seo_options->options['modules']['general'][ 'verify_' . $k ] ) ),
						PHP_EOL
					);
				}
			}
		}


		// Handle extra meta fields.
		if ( is_page() && ! is_front_page() ) {
			$meta_string .= html_entity_decode( stripslashes( $gofer_seo_options->options['modules']['general']['post_type_settings']['page']['custom_meta_tags'] ), ENT_QUOTES, 'UTF-8' );
			$meta_string .= PHP_EOL;
		}
		if ( is_single() ) {
			$meta_string .= html_entity_decode( stripslashes( $gofer_seo_options->options['modules']['general']['post_type_settings']['post']['custom_meta_tags'] ), ENT_QUOTES, 'UTF-8' );
			$meta_string .= PHP_EOL;
		}

		if ( is_front_page() ) {
			$meta_string .= html_entity_decode( stripslashes( $gofer_seo_options->options['modules']['general']['home_meta_tags'] ), ENT_QUOTES, 'UTF-8' );
			$meta_string .= PHP_EOL;
		} elseif ( is_home() ) {
			$meta_string .= html_entity_decode( stripslashes( $gofer_seo_options->options['modules']['general']['posts_page_meta_tags'] ), ENT_QUOTES, 'UTF-8' );
			$meta_string .= PHP_EOL;
		}

		$prev_next = $this->get_prev_next_links( $post );
		$prev      = apply_filters( 'gofer_seo_prev_link', $prev_next['prev'] );
		$next      = apply_filters( 'gofer_seo_next_link', $prev_next['next'] );
		if ( ! empty( $prev ) ) {
			$meta_string .= sprintf(
				'<link rel="prev" href="%s" />%s',
				esc_url( $prev ),
				PHP_EOL
			);
		}
		if ( ! empty( $next ) ) {
			$meta_string .= sprintf(
				'<link rel="next" href="%s" />%s',
				esc_url( $next ),
				PHP_EOL
			);
		}

		if ( null !== $meta_string ) {
			echo gofer_seo_esc_head( $meta_string ) . PHP_EOL;
		}

		if ( ! empty( $old_wp_query ) ) {
			// Restores the query back after operations are finished.
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$GLOBALS['wp_query'] = $old_wp_query;
			unset( $old_wp_query );
		}
	}

	/**
	 * Rel-Canonical.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function rel_canonical() {
		$rel_canonical = $this->get_rel_canonical();
		echo gofer_seo_esc_head( $rel_canonical );
	}

	/**
	 * Get Rel-Canonical.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_rel_canonical() {
		$wp_obj = '';
		if ( is_home() && ! is_front_page() ) {
			$wp_obj = Gofer_SEO_Methods::get_blog_page();
		}

		$rel_canonical = '';
		$url           = '';

		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		$context           = Gofer_SEO_Context::get_instance( $wp_obj );
		$show_page         = false;
		if ( $gofer_seo_options->options['modules']['general']['enable_canonical_paginated'] ) {
			$show_page = true;
		}

		// Custom Canonical Link.
		if ( 'WP_Post' === $context->context_type ) {
			$wp_obj         = Gofer_SEO_Context::get_object( $context->context_type, $context->context_key );
			$gofer_seo_post = new Gofer_SEO_Post( $wp_obj );
			// TODO Change to check Options Canonical Page pagination `$show_page`.

			// Check post-meta custom link.
			if ( ! empty( $gofer_seo_post->meta['modules']['general']['custom_link'] ) && ! is_home() ) {
				$url = $gofer_seo_post->meta['modules']['general']['custom_link'];

				if ( apply_filters( 'gofer_seo_rel_canonical_url_pagination', $show_page ) ) {
					$url = gofer_seo_get_link_paginated( $url, $wp_obj );
				}
			}
		} elseif ( 'WP_Term' === $context->context_type ) {
			$wp_obj         = Gofer_SEO_Context::get_object( $context->context_type, $context->context_key );
			$gofer_seo_term = new Gofer_SEO_Term( $wp_obj );
			// TODO Change to check Options Canonical Term pagination `$show_page`.

			if ( ! empty( $gofer_seo_term->meta['modules']['general']['custom_link'] ) ) {
				$url = $gofer_seo_term->meta['modules']['general']['custom_link'];

				if ( apply_filters( 'gofer_seo_rel_canonical_url_pagination', $show_page ) ) {
					$url = gofer_seo_get_link_paginated( $url, $wp_obj );
				}
			}
		} elseif ( in_array( $context->context_type, array( 'var_date', 'var_date_year', 'var_date_month', 'var_date_day' ), true ) ) {
			// TODO Change to check Options Canonical Date pagination `$show_page`.
		}

		if ( empty( $url ) ) {
			if ( apply_filters( 'gofer_seo_rel_canonical_url_pagination', $show_page ) ) {
				$url = $context->get_canonical_url();
			} else {
				$url = $context->get_url();
			}
		}

		$url = gofer_seo_filter_url_scheme( $url );

		$url = apply_filters( 'gofer_seo_rel_canonical_url', $url );
		if ( ! empty( $url ) ) {
			$rel_canonical = sprintf(
				'<link rel="canonical" href="%s" />%s',
				esc_url( $url ),
				PHP_EOL
			);
		}

		return $rel_canonical;
	}

	/**
	 * Returns the robots meta tag string.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_robots_meta_tag() {
		global $post;

		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		$post_type         = get_post_type();
		$taxonomy          = get_query_var( 'taxonomy' );
		$page_number       = gofer_seo_the_page_number();

		$noindex            = false;
		$nofollow           = false;
		$post_meta_noindex  = '';
		$post_meta_nofollow = '';

		if ( ! get_option( 'blog_public' ) ) {
			return '';
		}

		if ( is_front_page() && 0 === $page_number ) {
			return $this->get_robots_meta_tag_helper( false, false );
		}

		if (
				(
					is_home() &&
					0 !== (int) get_option( 'page_for_posts' )
				) || (
					gofer_seo_is_woocommerce_active() &&
					is_shop()
				)
		) {
			$post_type = 'page';
		}

		if (
				! is_date() &&
				! is_author() &&
				! is_search() &&
				! is_404()
		) {
			$wp_object  = get_queried_object();
			if ( $wp_object instanceof WP_Post ) {
				$gofer_seo_post = new Gofer_SEO_Post( $wp_object );

				$post_meta_noindex  = $gofer_seo_post->meta['modules']['general']['enable_noindex'];
				$post_meta_nofollow = $gofer_seo_post->meta['modules']['general']['enable_nofollow'];

			} elseif ( $wp_object instanceof WP_Term ) {
				$gofer_seo_term = new Gofer_SEO_Term( $wp_object );

				$post_meta_noindex  = $gofer_seo_term->meta['modules']['general']['enable_noindex'];
				$post_meta_nofollow = $gofer_seo_term->meta['modules']['general']['enable_nofollow'];
			}
		}

		if (
				1 === $post_meta_noindex ||
				( $gofer_seo_options->options['modules']['general']['paginate_enable_noindex'] && 1 < $page_number ) ||
				(
					is_singular() &&
					! empty( $post->post_password ) &&
					apply_filters( 'gofer_seo_general_allow_post_with_password', false )
				) ||
				(
					( is_category() && $gofer_seo_options->options['modules']['general']['taxonomy_settings']['category']['enable_noindex'] ) ||
					( is_date() && $gofer_seo_options->options['modules']['general']['archive_date_enable_noindex'] ) ||
					( is_author() && $gofer_seo_options->options['modules']['general']['archive_author_enable_noindex'] ) ||
					( is_tag() && $gofer_seo_options->options['modules']['general']['taxonomy_settings']['post_tag']['enable_noindex'] ) ||
					( is_search() && $gofer_seo_options->options['modules']['general']['search_enable_noindex'] ) ||
					( is_404() && $gofer_seo_options->options['modules']['general']['404_enable_noindex'] ) ||
					(
						is_tax() &&
						$gofer_seo_options->options['modules']['general']['taxonomy_settings'][ $taxonomy ]['enable_noindex']
					)
				) ||
				(
					is_singular() &&
					-1 === $post_meta_noindex &&
					$gofer_seo_options->options['modules']['general']['post_type_settings'][ $post_type ]['enable_noindex']
				)
		) {
			$noindex = true;
		}

		if (
				1 === $post_meta_nofollow ||
				(
					$gofer_seo_options->options['modules']['general']['paginate_enable_nofollow'] &&
					1 < $page_number
				) ||
				(
					is_singular() &&
					-1 === $post_meta_nofollow &&
					$gofer_seo_options->options['modules']['general']['post_type_settings'][ $post_type ]['enable_nofollow']
				)
		) {
			$nofollow = true;
		}

		return $this->get_robots_meta_tag_helper( $noindex, $nofollow );
	}

	/**
	 * Helper function for \Gofer_SEO_Module_General::get_robots_meta_tag().
	 *
	 * @since 1.0.0
	 *
	 * @param bool $noindex
	 * @param bool $nofollow
	 * @return string
	 */
	private function get_robots_meta_tag_helper( $noindex, $nofollow ) {
		if ( ! $noindex && $nofollow ) {
			return '';
		}

		$noindex_str  = $noindex ? 'noindex' : 'index';
		$nofollow_str = $nofollow ? 'nofollow' : 'follow';

		return sprintf( '<meta name="robots" content="%s,%s" />%s', $noindex_str, $nofollow_str, PHP_EOL );
	}

	/**
	 * Check Rewrite Handler
	 *
	 * @since 1.0.0
	 */
	function check_rewrite_handler() {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		if ( $gofer_seo_options->options['modules']['advanced']['enable_title_rewrite'] ) {
			// Make the title rewrite as short as possible.
			if ( function_exists( 'ob_list_handlers' ) ) {
				$active_handlers = ob_list_handlers();
			} else {
				$active_handlers = array();
			}
			if (
				sizeof( $active_handlers ) > 0 &&
				Gofer_SEO_PHP_Functions::strtolower( $active_handlers[ sizeof( $active_handlers ) - 1 ] ) === Gofer_SEO_PHP_Functions::strtolower( 'Gofer_SEO_Module_General::rewrite_title' )
			) {
				ob_end_flush();
			} else {
				new Gofer_SEO_Error( 'gofer_seo_module_general_title_rewrite_conflict', 'A plugin conflict may have occurred. `ob_list_handlers()` is missing `Gofer_SEO_Module_General::rewrite_title()`.' );
				// If we get here there *could* be trouble with another plugin :(.
				$this->ob_start_detected = true;

				// Try alternate method -- pdb.
				add_filter( 'wp_title', array( $this, 'wp_title' ), 20 );
				add_filter( 'pre_get_document_title', array( $this, 'wp_title' ), 20 );

				if ( ! empty( $active_handlers ) ) {
					$message = 'Detected output handler(s): ' . implode( ', ', $active_handlers );
					new Gofer_SEO_Error( 'gofer_seo_module_general_title_rewrite_handlers', $message );
				}
			}
		}
	}

	/**
	 * Template Redirect
	 *
	 * @since 1.0.0
	 */
	function template_redirect() {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		$post = $this->get_queried_object();

		if ( ! $this->is_seo_enabled() ) {
			return;
		}

		if ( $gofer_seo_options->options['modules']['advanced']['enable_title_rewrite'] ) {
			ob_start( array( $this, 'rewrite_title' ) );
		} else {
			add_filter( 'wp_title', array( $this, 'wp_title' ), 20 );
			add_filter( 'pre_get_document_title', array( $this, 'wp_title' ), 20 );
		}
	}

	/**
	 * Redirect Attachment
	 *
	 * Redirect attachment to parent post.
	 *
	 * @since 1.0.0
	 */
	public function template_redirect_attachment() {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		if ( $gofer_seo_options->options['modules']['general']['enable_attachment_redirect_to_parent'] ) {
			return false;
		}

		global $post;
		if (
				is_attachment() &&
				(
					$post instanceof WP_Post &&
					is_numeric( $post->post_parent ) &&
					0 !== $post->post_parent
				)
		) {
			wp_safe_redirect( gofer_seo_decode_url( get_permalink( $post->post_parent ) ), 301 );
			exit;
		}

		return false;
	}

	/**
	 * Rewrite Title
	 *
	 * Used for forcing title rewrites.
	 *
	 * @since 1.0.0
	 *
	 * @param $header
	 * @return mixed|string
	 */
	function rewrite_title( $header ) {

		global $wp_query;
		if ( ! $wp_query ) {
			$header .= "<!-- GOFER_SEO no wp_query found! -->\n";
			return $header;
		}

		// Check if we're in the main query to support bad themes and plugins.
		$old_wp_query = null;
		if ( ! $wp_query->is_main_query() ) {
			$old_wp_query = $wp_query;
			wp_reset_postdata();
		}

		$title = $this->wp_title();
		if ( ! empty( $title ) ) {
			$header = $this->replace_title( $header, $title );
		}

		if ( ! empty( $old_wp_query ) ) {
			// Restores the query back after operations are finished.
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$GLOBALS['wp_query'] = $old_wp_query;
			unset( $old_wp_query );
		}
		return $header;
	}

	/**
	 * Replace Title
	 *
	 * @since 1.0.0
	 *
	 * @param string $content
	 * @param string $title
	 * @return string
	 */
	function replace_title( $content, $title ) {
		$title = trim( wp_strip_all_tags( $title ) );
		$title = preg_replace(
			'/<title([^>]*?)\s*>([^<]*?)<\/title\s*>/is',
			'<title\\1>' . preg_replace( '/(\$|\\\\)(?=\d)/', '\\\\\1', wp_strip_all_tags( $title ) ) . '</title>',
			$content,
			1
		);
		return $title;
	}

	/**
	 * WP Title
	 *
	 * Used to filter wp_title(), get our title.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed|void
	 */
	function wp_title() {
		if ( ! $this->is_enabled_post_type() ) {
			return;
		}

		$title = $this->get_the_title();

		$title = apply_filters( 'gofer_seo_filter_title', $title );

		/**
		 * Gofer SEO - WP Title
		 *
		 * @since 1.0.0
		 *
		 * @param string $title The string title to return.
		 */
		$title = apply_filters( 'gofer_seo_wp_title', $title );

		return $title;
	}

	/**
	 * Get Queried Object
	 *
	 * @since 1.0.0
	 *
	 * @return null|object|WP_Post
	 */
	function get_queried_object() {
		static $p = null;
		global $wp_query, $post;
		if ( null !== $p ) {
			return $p;
		}
		if ( is_object( $post ) ) {
			$p = $post;
		} else {
			if ( ! $wp_query ) {
				return null;
			}
			$p = $wp_query->get_queried_object();
		}

		return $p;
	}

	/**
	 * Internationalize
	 *
	 * TODO Move to i18n.
	 *
	 * @since 1.0.0
	 *
	 * @param $in
	 * @return mixed|void
	 */
	function internationalize( $in ) {
		if ( function_exists( 'langswitch_filter_langs_with_message' ) ) {
			$in = langswitch_filter_langs_with_message( $in );
		}

		if ( function_exists( 'polyglot_filter' ) ) {
			$in = polyglot_filter( $in );
		}

		if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
			$in = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $in );
		} elseif ( function_exists( 'ppqtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
			$in = ppqtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $in );
		} elseif ( function_exists( 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
			$in = qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage( $in );
		}

		return apply_filters( 'gofer_seo_localization', $in );
	}

	/**
	 * Get the Title.
	 *
	 * Sets the format & context, and then returns the converted string. If no format string is available, then
	 * the returned string will default to the appropriate title value.
	 *
	 * @since 1.0.0
	 *
	 * @param object $context WP object or Gofer SEO Context object.
	 * @return string
	 */
	public function get_the_title( $context = '' ) {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		$context      = Gofer_SEO_Context::get_instance( $context );
		$title_format = '';

		// Set the Context & Format.
		switch ( Gofer_SEO_Context::get_is() ) {
			case 'front_page':
				$title_format = '[site_title]';
				if ( ! empty( $gofer_seo_options->options['modules']['general']['site_title_format'] ) ) {
					$title_format = $gofer_seo_options->options['modules']['general']['site_title_format'];
				}
				break;

			case'posts_page':
				$title_format = '[post_title]';
				// get_option( 'show_on_front' );
				// get_option( 'page_for_posts' );

				$post = Gofer_SEO_Context::get_object( 'WP_Post', $context->context_key );
				if ( ! empty( $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post->post_type ]['title_format'] ) ) {
					$title_format = $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post->post_type ]['title_format'];
				} elseif ( ! empty( $gofer_seo_options->options['modules']['general']['site_title_format'] ) ) {
					$title_format = $gofer_seo_options->options['modules']['general']['site_title_format'];
				}
				break;

			case'home':
				if ( $gofer_seo_options->options['modules']['general']['use_static_homepage'] ) {
					$title_format = '[post_title]';

					$context_key = $context->context_key;
					if ( empty( $context_key ) ) {
						$context_key = get_option( 'page_on_front' );
						$args = array(
							'context_type' => 'WP_Post',
							'context_key'  => $context_key,
						);
						$context     = Gofer_SEO_Context::get_instance( $args );
					}

					$post = Gofer_SEO_Context::get_object( 'WP_Post', $context_key );
					if ( ! empty( $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post->post_type ]['title_format'] ) ) {
						$title_format = $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post->post_type ]['title_format'];
					} elseif ( ! empty( $gofer_seo_options->options['modules']['general']['site_title_format'] ) ) {
						$title_format = $gofer_seo_options->options['modules']['general']['site_title_format'];
					}
				} else {
					// Switch to Site context.
					$title_format = '[site_title]';

					$args = array(
						'context_type' => ( is_multisite() ) ? 'WP_Site' : 'var_site',
						'context_key'  => ( is_multisite() ) ? get_current_blog_id() : 0,
					);
					$context = Gofer_SEO_Context::get_instance( $args );

					if ( ! empty( $gofer_seo_options->options['modules']['general']['site_title_format'] ) ) {
						$title_format = $gofer_seo_options->options['modules']['general']['site_title_format'];
					}
				}
				break;

			case'single_post':
			case'single_page':
				$title_format = '[post_title]';

				if ( class_exists( 'BuddyPress' ) && ( bp_is_group() || bp_is_group_create() ) ) {
					// BuddyPress plugin.
					if ( bp_is_group() || bp_is_group_create() ) {
						// BuddyPress - Group Page(s).
						$bp_pages = get_option( 'bp-pages' );
						$args = array(
							'context_type' => 'WP_Post',
							'context_key'  => $bp_pages['groups'],
						);
						$context = Gofer_SEO_Context::get_instance( $args );

						$post = Gofer_SEO_Context::get_object( 'WP_Post', $context->context_key );
						if ( ! empty( $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post->post_type ]['title_format'] ) ) {
							$title_format = $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post->post_type ]['title_format'];
						}
					} elseif ( bp_is_user() ) {
						//  BuddyPress - Member Page.
						$wp_user = wp_get_current_user();
						$args = array(
							'context_type' => 'WP_User',
							'context_key'  => intval( $wp_user->ID ),
						);
						$context = Gofer_SEO_Context::get_instance( $args );
						// FIXME? author_display_name default? what format setting would this use...author archive format?
					}
				} else {
					// Normal Single Post/Page.
					$post = Gofer_SEO_Context::get_object( 'WP_Post', $context->context_key );
					if ( ! empty( $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post->post_type ]['title_format'] ) ) {
						$title_format = $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post->post_type ]['title_format'];
					}
				}
				break;

			case'single_attachment':
			case'attachment':
				$title_format = '[post_title]';

				$post = Gofer_SEO_Context::get_object( 'WP_Post', $context->context_key );
				if ( ! empty( $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post->post_type ]['title_format'] ) ) {
					$title_format = $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post->post_type ]['title_format'];
				}
				break;

			case'post_type_archive':
				$title_format = '[archive_title]';

				if ( is_post_type_archive( 'product' ) && function_exists( 'wc_get_page_id' ) ) {
					// WooCommerce Shop Page.
					$title_format = '[post_title]';
					$post_id = wc_get_page_id( 'shop' );

					$args = array(
						'context_type' => 'WP_Post',
						'context_key'  => $post_id,
					);
					$context = Gofer_SEO_Context::get_instance( $args );

					$post = Gofer_SEO_Context::get_object( 'WP_Post', $context->context_key );
					if ( ! empty( $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post->post_type ]['title_format'] ) ) {
						$title_format = $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post->post_type ]['title_format'];
					}
				} else {
					// Normal Post-Type Archive.
					if ( ! empty( $gofer_seo_options->options['modules']['general']['archive_post_title_format'] ) ) {
						$title_format = $gofer_seo_options->options['modules']['general']['archive_post_title_format'];
					} elseif ( ! empty( $gofer_seo_options->options['modules']['general']['post_type_settings'][ $context->context_key ]['title_format'] ) ) {
						$title_format = $gofer_seo_options->options['modules']['general']['post_type_settings'][ $context->context_key ]['title_format'];
					}
				}
				break;

			case'taxonomy_term_archive':
				$title_format = '[archive_title]';

				if ( ! empty( $gofer_seo_options->options['modules']['general']['archive_taxonomy_term_title_format'] ) ) {
					$title_format = $gofer_seo_options->options['modules']['general']['archive_taxonomy_term_title_format'];
				}
				break;

			case'date_archive':
			case'year_date_archive':
			case'month_date_archive':
			case'day_date_archive':
				$title_format = '[archive_title]';

				if ( ! empty( $gofer_seo_options->options['modules']['general']['archive_date_title_format'] ) ) {
					$title_format = $gofer_seo_options->options['modules']['general']['archive_date_title_format'];
				}
				break;

			case'author_archive':
				$title_format = '[archive_title]';

				if ( ! empty( $gofer_seo_options->options['modules']['general']['archive_author_title_format'] ) ) {
					$title_format = $gofer_seo_options->options['modules']['general']['archive_author_title_format'];
				}
				break;

			case'search':
				$title_format = '[search_value]';

				if ( ! empty( $gofer_seo_options->options['modules']['general']['search_title_format'] ) ) {
					$title_format = $gofer_seo_options->options['modules']['general']['search_title_format'];
				}
				break;

			case'404':
				/* translators: %s is the a shortcode. */
				$title_format = sprintf( __( 'Nothing found for %s', 'gofer-seo' ), '[request_words]' );

				if ( ! empty( $gofer_seo_options->options['modules']['general']['404_title_format'] ) ) {
					$title_format = $gofer_seo_options->options['modules']['general']['404_title_format'];
				}
				break;
		}

		// Add Pagination.
		if ( is_paged() || 1 < gofer_seo_the_page_number() ) {
			$title_format .= ' ' . $gofer_seo_options->options['modules']['general']['paginate_format'];
		}

		$gofer_seo_format_shortcodes = new Gofer_SEO_Format_Shortcodes( $context );
		$title = $gofer_seo_format_shortcodes->format( $title_format );

		// If title is still empty, default/fallback to Site Title.
		if ( empty( $title ) ) {
			$title = $gofer_seo_format_shortcodes->format( '[site_title]' );
		}

		return $title;
	}

	/**
	 * Get the Description.
	 *
	 * Sets the format & context, and then returns the converted string. If no format string is available, then
	 * the returned string will default to the appropriate description value.
	 *
	 * @since 1.0.0
	 *
	 * @param object $context WP object or Gofer SEO Context object.
	 * @return string
	 */
	public function get_the_description( $context = '' ) {
		if ( ! $this->show_paginate_description() ) {
			return '';
		}

		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		$context            = Gofer_SEO_Context::get_instance( $context );
		$description_format = '[site_description]';

		// Set the Context & Format.
		switch ( Gofer_SEO_Context::get_is() ) {
			case 'front_page':
				$description_format = '[site_description]';
				if ( ! empty( $gofer_seo_options->options['modules']['general']['site_description_format'] ) ) {
					$description_format = $gofer_seo_options->options['modules']['general']['site_description_format'];
				}
				break;

			case'posts_page':
				$description_format = '[post_description]';
				// get_option( 'show_on_front' );
				// get_option( 'page_for_posts' );

				$post = Gofer_SEO_Context::get_object( 'WP_Post', $context->context_key );
				if ( ! empty( $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post->post_type ]['description_format'] ) ) {
					$description_format = $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post->post_type ]['description_format'];
				} elseif ( ! empty( $gofer_seo_options->options['modules']['general']['site_description_format'] ) ) {
					$description_format = $gofer_seo_options->options['modules']['general']['site_description_format'];
				}
				break;

			case'home':
				if ( $gofer_seo_options->options['modules']['general']['use_static_homepage'] ) {
					$description_format = '[post_description]';

					$context_key = $context->context_key;
					if ( empty( $context_key ) ) {
						$context_key = get_option( 'page_on_front' );
						$args = array(
							'context_type' => 'WP_Post',
							'context_key'  => $context_key,
						);
						$context     = Gofer_SEO_Context::get_instance( $args );
					}

					$post = Gofer_SEO_Context::get_object( 'WP_Post', $context_key );
					if ( ! empty( $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post->post_type ]['description_format'] ) ) {
						$description_format = $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post->post_type ]['description_format'];
					} elseif ( ! empty( $gofer_seo_options->options['modules']['general']['site_description_format'] ) ) {
						$description_format = $gofer_seo_options->options['modules']['general']['site_description_format'];
					}
				} else {
					// Switch to Site context.
					$description_format = '[site_description]';

					$args = array(
						'context_type' => ( is_multisite() ) ? 'WP_Site' : 'var_site',
						'context_key'  => ( is_multisite() ) ? get_current_blog_id() : 0,
					);
					$context = Gofer_SEO_Context::get_instance( $args );

					if ( ! empty( $gofer_seo_options->options['modules']['general']['site_description_format'] ) ) {
						$description_format = $gofer_seo_options->options['modules']['general']['site_description_format'];
					}
				}
				break;

			case'single_post':
			case'single_page':
				$description_format = '[post_description]';

				if ( class_exists( 'BuddyPress' ) && ( bp_is_group() || bp_is_group_create() ) ) {
					// BuddyPress plugin.
					if ( bp_is_group() || bp_is_group_create() ) {
						// BuddyPress - Group Page(s).
						$bp_pages = get_option( 'bp-pages' );
						$args = array(
							'context_type' => 'WP_Post',
							'context_key'  => $bp_pages['groups'],
						);
						$context = Gofer_SEO_Context::get_instance( $args );

						$post = Gofer_SEO_Context::get_object( 'WP_Post', $context->context_key );
						if ( ! empty( $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post->post_type ]['description_format'] ) ) {
							$description_format = $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post->post_type ]['description_format'];
						}
					} elseif ( bp_is_user() ) {
						//  BuddyPress - Member Page.
						$wp_user = wp_get_current_user();
						$args = array(
							'context_type' => 'WP_User',
							'context_key'  => intval( $wp_user->ID ),
						);
						$context = Gofer_SEO_Context::get_instance( $args );
						// FIXME? author_display_name default? what format setting would this use...author archive format?
					}
				} else {
					// Normal Single Post/Page.
					$post = Gofer_SEO_Context::get_object( 'WP_Post', $context->context_key );
					if ( ! empty( $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post->post_type ]['description_format'] ) ) {
						$description_format = $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post->post_type ]['description_format'];
					}
				}
				break;

			case'single_attachment':
			case'attachment':
				$description_format = '[post_description]';

				$post = Gofer_SEO_Context::get_object( 'WP_Post', $context->context_key );
				if ( ! empty( $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post->post_type ]['description_format'] ) ) {
					$description_format = $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post->post_type ]['description_format'];
				}
				break;

			case'taxonomy_term_archive':
				$description_format = '[term_description]';
				if ( 'WP_Term' !== $context->context_type ) {
					$context = Gofer_SEO_Context::get_instance();
				}

				$term = Gofer_SEO_Context::get_object( 'WP_Term', $context->context_key );
				if ( $gofer_seo_options->options['modules']['general']['taxonomy_settings'][ $term->taxonomy ]['description_format'] ) {
					$description_format = $gofer_seo_options->options['modules']['general']['taxonomy_settings'][ $term->taxonomy ]['description_format'];
				}
				break;

			case'post_type_archive':
				$description_format = '[post_type_description]';

				if ( is_post_type_archive( 'product' ) && function_exists( 'wc_get_page_id' ) ) {
					// WooCommerce Shop Page.
					$description_format = '[post_description]';
					$post_id = wc_get_page_id( 'shop' );

					$args = array(
						'context_type' => 'WP_Post',
						'context_key'  => $post_id,
					);
					$context = Gofer_SEO_Context::get_instance( $args );

					$post = Gofer_SEO_Context::get_object( 'WP_Post', $context->context_key );
					if ( ! empty( $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post->post_type ]['description_format'] ) ) {
						$description_format = $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post->post_type ]['description_format'];
					}
				}
				break;

			case'author_archive':
				// TODO Add author_description shortcode (aka Biographical Info).
				// FIXME Change to format string.
				return get_the_author_meta( 'description' );

			case'date_archive':
			case'year_date_archive':
			case'month_date_archive':
			case'day_date_archive':
			case'search':
			case'404':
		}

		$gofer_seo_format_shortcodes = new Gofer_SEO_Format_Shortcodes( $context );
		$description = $gofer_seo_format_shortcodes->format( $description_format );

		// If description is still empty, default/fallback to Site Description.
		if ( empty( $description ) ) {
			$description = $gofer_seo_format_shortcodes->format( '[site_description]' );
		}

		return $description;
	}

	/**
	 * Generate Description.
	 *
	 * @since 1.0.0
	 *
	 * @param string $context
	 * @return string
	 */
	public function generate_description( $context = '' ) {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		if ( ! $gofer_seo_options->options['modules']['general']['generate_description']['enable_generator'] ) {
			return '';
		}
		$description = '';

		$context = Gofer_SEO_Context::get_instance( $context );
		if ( 'WP_Post' === $context->context_type ) {
			$post = $context::get_object( 'WP_Post', $context->context_key );
			if ( post_password_required( $post ) ) {
				return '';
			}

			if ( $gofer_seo_options->options['modules']['general']['generate_description']['use_excerpt'] ) {
				$description = $post->post_excerpt;
			}

			$use_content = $gofer_seo_options->options['modules']['general']['generate_description']['use_content'];

			/**
			 * Generate Descriptions using the Post Content.
			 *
			 * @since 1.0.0
			 *
			 * @param bool $use_content
			 */
			$use_content = apply_filters( 'gofer_seo_generate_description_use_content', $use_content );
			if ( empty( $description ) && $use_content ) {
				$description = $post->post_content;
				if ( $gofer_seo_options->options['modules']['general']['enable_description_shortcodes'] ) {
					$description = gofer_seo_do_shortcodes( $description );
				}
			}
		}

		return $description;
	}

	/**
	 * Show Page Description
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	function show_paginate_description() {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		if ( ! $gofer_seo_options->options['modules']['general']['show_paginate_descriptions'] ) {
			$page = gofer_seo_the_page_number();
			if ( 1 < $page ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the Keywords.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_the_keywords() {
		if ( is_404() ) {
			return '';
		}

		$keywords_arr = array();
		$post_arr     = array();

		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		$context           = Gofer_SEO_Context::get_instance();

		switch ( Gofer_SEO_Context::get_is() ) {
			case 'front_page':
				$keywords_arr = explode( ',', $gofer_seo_options->options['modules']['general']['site_keywords'] );
				break;

			case 'home':
				if ( $gofer_seo_options->options['modules']['general']['use_static_homepage'] ) {
					$gofer_seo_post = new Gofer_SEO_Post( $context->context_key );
					$keywords_arr   = explode( ',', $gofer_seo_post->meta['modules']['general']['keywords'] );

					// Generate (Post) Keywords.
					$post_arr[] = Gofer_SEO_Context::get_object( 'WP_Post', $context->context_key );
				} else {
					$keywords_arr = explode( ',', $gofer_seo_options->options['modules']['general']['site_keywords'] );
				}
				break;

			case 'posts_page':
				$gofer_seo_post = new Gofer_SEO_Post( $context->context_key );
				$keywords_arr   = explode( ',', $gofer_seo_post->meta['modules']['general']['keywords'] );

				if ( $gofer_seo_options->options['modules']['general']['generate_keywords']['enable_on_static_posts_page'] ) {
					// Generate (Post) Keywords.
					global $posts;
					if ( empty( $posts ) ) {
						$post_arr[] = Gofer_SEO_Context::get_object( 'WP_Post', $context->context_key );
					} else {
						$post_arr = $posts;
					}
				}
				break;

			case 'post_type_archive':
				if ( $gofer_seo_options->options['modules']['general']['generate_keywords']['enable_on_static_posts_page'] ) {
					// Generate (Post) Keywords.
					global $posts;
					if ( ! empty( $posts ) ) {
						$post_arr = $posts;
					}
				}
				break;

			case 'taxonomy_term_archive':
				$gofer_seo_term = new Gofer_SEO_Term( $context->context_key );
				$keywords_arr   = explode( ',', $gofer_seo_term->meta['modules']['general']['keywords'] );
				break;

			case 'single_post':
			case 'single_page':
				$gofer_seo_post = new Gofer_SEO_Post( $context->context_key );
				$keywords_arr   = explode( ',', $gofer_seo_post->meta['modules']['general']['keywords'] );

				// Generate (Post) Keywords.
				$post_arr[] = Gofer_SEO_Context::get_object( 'WP_Post', $context->context_key );
				break;

			case 'attachment':
			case 'single_attachment':
				$post = Gofer_SEO_Context::get_object( 'WP_Post', $context->context_key );

				$gofer_seo_post        = new Gofer_SEO_Post( $context->context_key );
				$gofer_seo_post_parent = new Gofer_SEO_Post( $post->post_parent );

				$keywords_arr = array_merge(
					explode( ',', $gofer_seo_post->meta['modules']['general']['keywords'] ),
					explode( ',', $gofer_seo_post_parent->meta['modules']['general']['keywords'] )
				);

				// Generate (Post) Keywords.
				$post_arr[] = $post;
				$post_arr[] = Gofer_SEO_Context::get_object( 'WP_Post', $post->post_parent );
				break;
		}

		// Generate Keywords from `$post_arr`.
		if ( $gofer_seo_options->options['modules']['general']['generate_keywords']['enable_generator'] ) {
			$keywords_arr = array_merge( $keywords_arr, $this->get_generate_keywords( $post_arr ) );
		}

		// Sanitize.
		foreach ( $keywords_arr as $index => $keyword ) {
			$keywords_arr[ $index ] = $this->internationalize( trim( stripslashes( $keyword ) ) );
		}
		$keywords_arr = array_unique( $keywords_arr );

		// Convert to string.
		$keywords = implode( ',', $keywords_arr );

		/**
		 * General Module Keywords.
		 *
		 * @since 1.0.0
		 *
		 * @param string   $keywords     The string of keywords to return.
		 * @param string[] $keywords_arr An array of keyword strings.
		 */
		$keywords = apply_filters( 'gofer_seo_general_keywords', $keywords, $keywords_arr );

		return $keywords;
	}

	/**
	 * Get Generate Keywords.
	 *
	 * @since 1.0.0
	 *
	 * @param $post_arr
	 * @return array
	 */
	public function get_generate_keywords( $post_arr ) {
		if ( ! is_array( $post_arr ) ) {
			if ( ! $post_arr instanceof WP_Post ) {
				return array();
			}
			$post_arr = array( $post_arr );
		}

		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		$generate_use_taxonomies = array_keys( array_filter( $gofer_seo_options->options['modules']['general']['generate_keywords']['use_taxonomies'] ) );

		$keywords_arr = array();
		foreach ( $post_arr as $post ) {
			if ( ! $post instanceof WP_Post ) {
				continue;
			}

			foreach ( $generate_use_taxonomies as $use_taxonomy ) {
				$terms = get_the_terms( $post, $use_taxonomy );
				if ( ! empty( $terms ) && is_array( $terms ) ) {
					foreach ( $terms as $term ) {
						$keywords_arr[] = trim( Gofer_SEO_PHP_Functions::strtolower( $term->name ) );
					}
				}
			}
		}

		$keywords_arr = array_unique( $keywords_arr );

		return $keywords_arr;
	}

	/**
	 * The is_page_included() function.
	 *
	 * Checks whether Gofer SEO is enabled for this page.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	function is_seo_enabled() {
		if ( is_feed() ) {
			return false;
		}

		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		$post              = $this->get_queried_object();

		$enabled_post_types = array_keys( array_filter( $gofer_seo_options->options['modules']['general']['enable_post_types'] ) );
		if ( empty( $enabled_post_types ) ) {
			$enabled_post_types = array();
		}
		$enabled_taxonomies = array_keys( array_filter( $gofer_seo_options->options['modules']['general']['enable_taxonomies'] ) );
		if ( empty( $enabled_taxonomies ) ) {
			$enabled_taxonomies = array();
		}

		if ( is_single() || is_singular() ) {
			$gofer_seo_post    = new Gofer_SEO_Post( $post );

			if ( $gofer_seo_post->meta['modules']['general']['enable_force_disable'] ) {
				return false;
			} elseif ( ! in_array( $post->post_type, $enabled_post_types, true ) ) {
				return false;
			}
		} elseif ( is_tax() || is_category() || is_tag() ) {
			$queried_object     = get_queried_object();
			$gofer_seo_term     = new Gofer_SEO_Term( $queried_object->term_id );

			if ( $gofer_seo_term->meta['modules']['general']['enable_force_disable'] ) {
				return false;
			} elseif ( is_tax() ) {
				if ( empty( $enabled_taxonomies ) || ! is_tax( $enabled_taxonomies ) ) {
					return false;
				}
			} elseif ( is_category() ) {
				if ( empty( $enabled_taxonomies ) || ! in_array( 'category', $enabled_taxonomies, true ) ) {
					return false;
				}
			} elseif ( is_tag() ) {
				if ( empty( $enabled_taxonomies ) || ! in_array( 'post_tag', $enabled_taxonomies, true ) ) {
					return false;
				}
			}
		} elseif ( is_archive() ) {
			if ( ! is_post_type_archive( $enabled_post_types ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Is SEO Enabled for CPT
	 *
	 * Checks whether the current CPT should show the SEO tags.
	 *
	 * @since 1.0.0
	 *
	 * @param string $post_type The post type slug.
	 * @return bool
	 */
	private function is_enabled_post_type( $post_type = '' ) {
		if ( empty( $post_type ) ) {
			$post_type = get_post_type();
		}
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		$enabled_post_types = array_keys( array_filter( $gofer_seo_options->options['modules']['general']['enable_post_types'] ) );
		return in_array( $post_type, $enabled_post_types, true );
	}

	/**
	 * Checks to see if Google Analytics should be excluded from the current page.
	 *
	 * Looks at both the individual post settings and the General Settings.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	function analytics_excluded() {
		$gofer_seo_options       = Gofer_SEO_Options::get_instance();
		$queried_object          = get_queried_object();
		$disable_analytics = false;
		if ( $queried_object instanceof WP_Post ) {
			$gofer_seo_post = new Gofer_SEO_Post( $queried_object );

			$disable_analytics = $gofer_seo_post->meta['modules']['general']['disable_analytics'];
		} elseif ( $queried_object instanceof WP_Term ) {
			$gofer_seo_term = new Gofer_SEO_Term( $queried_object );

			$disable_analytics = $gofer_seo_term->meta['modules']['general']['disable_analytics'];
		}

		if ( $disable_analytics || empty( $gofer_seo_options->options['modules']['general']['google_analytics']['ua_id'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Split Share Term.
	 *
	 * Fires after a previously shared taxonomy term is split into two separate terms.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $term_id          ID of the formerly shared term.
	 * @param int    $new_term_id      ID of the new term created for the $term_taxonomy_id.
	 * @param int    $term_taxonomy_id ID for the term_taxonomy row affected by the split.
	 * @param string $taxonomy         Taxonomy for the split term.
	 */
	public function split_shared_term( $term_id, $new_term_id, $term_taxonomy_id = 0, $taxonomy = '' ) {
		$gofer_seo_term     = new Gofer_SEO_Term( $term_id );
		$new_gofer_seo_term = new Gofer_SEO_Term( $new_term_id );

		$new_gofer_seo_term->meta = array_replace( $new_gofer_seo_term->meta, $gofer_seo_term->meta );
		$new_gofer_seo_term->update_meta();
	}

	/**
	 * Get Previous/Next Links
	 *
	 * @since 1.0.0
	 *
	 * @param null $post
	 * @return array
	 */
	function get_prev_next_links( $post = null ) {
		$prev = '';
		$next = '';
		$page = gofer_seo_the_page_number();
		if ( is_home() || is_archive() || is_paged() ) {
			global $wp_query;
			$max_page = $wp_query->max_num_pages;
			if ( $page > 1 ) {
				$prev = get_previous_posts_page_link();
			}
			if ( $page < $max_page ) {
				$paged = $GLOBALS['paged'];
				if ( ! is_single() ) {
					if ( ! $paged ) {
						$paged = 1;
					}
					$nextpage = intval( $paged ) + 1;
					if ( ! $max_page || $max_page >= $nextpage ) {
						$next = get_pagenum_link( $nextpage );
					}
				}
			}
		} elseif ( is_page() || is_single() ) {
			$numpages  = 1;
			$multipage = 0;
			$page      = get_query_var( 'page' );
			if ( ! $page ) {
				$page = 1;
			}
			if ( is_single() || is_page() || is_feed() ) {
				$more = 1;
			}
			$content = $post->post_content;
			if ( false !== strpos( $content, '<!--nextpage-->', 0 ) ) {
				if ( $page > 1 ) {
					$more = 1;
				}
				$content = str_replace( "\n<!--nextpage-->\n", '<!--nextpage-->', $content );
				$content = str_replace( "\n<!--nextpage-->", '<!--nextpage-->', $content );
				$content = str_replace( "<!--nextpage-->\n", '<!--nextpage-->', $content );
				// Ignore nextpage at the beginning of the content.
				if ( 0 === strpos( $content, '<!--nextpage-->', 0 ) ) {
					$content = substr( $content, 15 );
				}
				$pages    = explode( '<!--nextpage-->', $content );
				$numpages = count( $pages );
				if ( $numpages > 1 ) {
					$multipage = 1;
				}
			} else {
				$page = null;
			}
			if ( ! empty( $page ) ) {
				if ( $page > 1 ) {
					// Cannot use `wp_link_page()` since it is for rendering purposes and has no control over the page number.
					// TODO Investigate alternate wp concept. If none is found, keep private function in case of any future WP changes.
					$prev = _wp_link_page( $page - 1 );
				}
				if ( $page + 1 <= $numpages ) {
					// Cannot use `wp_link_page()` since it is for rendering purposes and has no control over the page number.
					// TODO Investigate alternate wp concept. If none is found, keep private function in case of any future WP changes.
					$next = _wp_link_page( $page + 1 );
				}
			}

			if ( ! empty( $prev ) ) {
				$dom = new DOMDocument();
				$dom->loadHTML( $prev );
				$prev = $dom->getElementsByTagName( 'a' )->item( 0 )->getAttribute( 'href' );
			}
			if ( ! empty( $next ) ) {
				$dom = new DOMDocument();
				$dom->loadHTML( $next );
				$next = $dom->getElementsByTagName( 'a' )->item( 0 )->getAttribute( 'href' );
			}
		}

		return array(
			'prev' => $prev,
			'next' => $next,
		);
	}

	/**
	 * Google Analytics.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function google_analytics() {
		new Gofer_SEO_Google_Analytics();
	}

	/**
	 * Apply Filter - Description.
	 *
	 * @since 1.0.0
	 *
	 * @see \Gofer_SEO_Module_General::filter_description()
	 *
	 * @param string $description        Value to filter.
	 * @param bool   $truncate           Flag that indicates if value should be truncated/cropped.
	 * @param bool   $ignore_php_version Flag that indicates if the php version check should be ignored.
	 * @return string
	 */
	public function apply_filter_description( $description, $truncate = false, $ignore_php_version = false ) {
		/**
		 * Filter Description.
		 *
		 * @since 1.0.0
		 *
		 * @param string $value              Value to filter.
		 * @param bool   $truncate           Flag that indicates if value should be truncated/cropped.
		 * @param bool   $ignore_php_version Flag that indicates if the php version check should be ignored.
		 */
		return apply_filters( 'gofer_seo_filter_description', $description, $truncate, $ignore_php_version );
	}

	/**
	 * Filter Title.
	 *
	 * Filters title and meta titles and applies cleanup.
	 * - Decode HTML entities.
	 * - Encodes to SEO ready HTML entities.
	 * Returns cleaned value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value Value to filter.
	 * @return string
	 */
	public function filter_title( $value ) {
		// Decode entities.
		$value = gofer_seo_html_entity_decode( $value );
		// Encode to valid SEO html entities.
		return gofer_seo_html_entity_encode( $value );
	}

	/**
	 * Filter Description.
	 *
	 * Filters meta value and applies generic cleanup.
	 * - Decode HTML entities.
	 * - Removal of urls.
	 * - Internal trim.
	 * - External trim.
	 * - Strips HTML except anchor texts.
	 * - Returns cleaned value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value              Value to filter.
	 * @param bool   $truncate           Flag that indicates if value should be truncated/cropped.
	 * @param bool   $ignore_php_version Flag that indicates if the php version check should be ignored.
	 * @return string
	 */
	public function filter_description( $value, $truncate = false, $ignore_php_version = false ) {
		// TODO: change preg_match to version_compare someday when the reason for this condition is understood better.
		if ( $ignore_php_version || preg_match( '/5.2[\s\S]+/', PHP_VERSION ) ) {
			$value = htmlspecialchars( wp_strip_all_tags( htmlspecialchars_decode( $value ) ), ENT_COMPAT, 'UTF-8' );
		}
		// Decode entities.
		$value = gofer_seo_html_entity_decode( $value );
		$value = preg_replace(
			array(
				'#<a.*?>([^>]*)</a>#i', // Remove link but keep anchor text.
				'@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', // Remove URLs.
			),
			array(
				'$1', // Replacement link's anchor text.
				'', // Replacement URLs.
			),
			$value
		);
		// Strip html.
		$value = wp_strip_all_tags( $value );
		// External trim.
		$value = trim( $value );
		// Internal whitespace trim.
		$value = preg_replace( '/\s\s+/u', ' ', $value );

		// Truncate / crop.
		if ( ! empty( $truncate ) && $truncate ) {
			$value = $this->trim_excerpt_without_filters( $value );
		}

		// Encode to valid SEO html entities.
		return gofer_seo_html_entity_encode( $value );
	}

	/**
	 * Trim Excerpt without Filters
	 *
	 * @since 1.0.0
	 *
	 * @param string $text The text to trim.
	 * @param int    $max  Max number of characters.
	 * @return string
	 */
	function trim_excerpt_without_filters( $text, $max = 0 ) {
		$text = str_replace( ']]>', ']]&gt;', $text );
		$text = strip_shortcodes( $text );
		$text = wp_strip_all_tags( $text );
		// Treat other common word-break characters like a space.
		$text2 = preg_replace( '/[,._\-=+&!\?;:*]/s', ' ', $text );
		if ( ! $max ) {
			$max = $this->maximum_description_length;
		}
		$max_orig = $max;
		$len      = Gofer_SEO_PHP_Functions::strlen( $text2 );
		if ( $max < $len ) {
			if ( function_exists( 'mb_strrpos' ) ) {
				$pos = mb_strrpos( $text2, ' ', - ( $len - $max ), 'UTF-8' );
				if ( false === $pos ) {
					$pos = $max;
				}
				if ( $pos > $this->minimum_description_length ) {
					$max = $pos;
				} else {
					$max = $this->minimum_description_length;
				}
			} else {
				while ( ' ' !== $text2[ $max ] && $max > $this->minimum_description_length ) {
					$max --;
				}
			}

			// Probably no valid chars to break on?
			if ( $len > $max_orig && $max < intval( $max_orig / 2 ) ) {
				$max = $max_orig;
			}
		}
		$text = Gofer_SEO_PHP_Functions::substr( $text, 0, $max );

		return trim( $text );
	}

}
