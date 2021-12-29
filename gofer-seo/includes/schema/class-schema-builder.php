<?php
/**
 * Schema Builder Class
 *
 * Creates the schema to be displayed on frontend.
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Schema_Builder
 * 
 * @since 1.0.0
 */
class Gofer_SEO_Schema_Builder {

	/**
	 * Graph Classes.
	 *
	 * @since 1.0.0
	 *
	 * @var array $graphs
	 */
	public $graphs = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->graphs = $this->get_graphs();
	}

	/**
	 * Register Graphs
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_graphs() {
		require_once GOFER_SEO_DIR . 'includes/schema/graphs/graph.php';
		require_once GOFER_SEO_DIR . 'includes/schema/graphs/graph-organization.php';
		require_once GOFER_SEO_DIR . 'includes/schema/graphs/graph-person.php';

		require_once GOFER_SEO_DIR . 'includes/schema/graphs/graph-itemlist.php';
		require_once GOFER_SEO_DIR . 'includes/schema/graphs/graph-breadcrumblist.php';

		require_once GOFER_SEO_DIR . 'includes/schema/graphs/graph-creativework.php';
		require_once GOFER_SEO_DIR . 'includes/schema/graphs/graph-article.php';
		require_once GOFER_SEO_DIR . 'includes/schema/graphs/graph-website.php';

		require_once GOFER_SEO_DIR . 'includes/schema/graphs/graph-webpage.php';
		require_once GOFER_SEO_DIR . 'includes/schema/graphs/graph-collectionpage.php';
		require_once GOFER_SEO_DIR . 'includes/schema/graphs/graph-profilepage.php';
		require_once GOFER_SEO_DIR . 'includes/schema/graphs/graph-searchresultspage.php';

		require_once GOFER_SEO_DIR . 'includes/class-context.php';

		$graphs = array(
			// Keys/Slugs follow Schema's @type format.
			'Article'           => new Gofer_SEO_Graph_Article(),
			'BreadcrumbList'    => new Gofer_SEO_Graph_BreadcrumbList(),
			'CollectionPage'    => new Gofer_SEO_Graph_CollectionPage(),
			'Organization'      => new Gofer_SEO_Graph_Organization(),
			'Person'            => new Gofer_SEO_Graph_Person(),
			'ProfilePage'       => new Gofer_SEO_Graph_ProfilePage(),
			'SearchResultsPage' => new Gofer_SEO_Graph_SearchResultsPage(),
			'Website'           => new Gofer_SEO_Graph_WebSite(),
			'Webpage'           => new Gofer_SEO_Graph_WebPage(),
		);

		/**
		 * Register Schema Objects
		 *
		 * @since 1.0.0
		 *
		 * @param $graphs array containing schema objects that are currently active.
		 */
		$graphs = apply_filters( 'gofer_seo_register_schema_objects', $graphs );

		// TODO Could add operation here to loop through objects to *::add_hooks(). Rather than schema __constructor executing add_hooks().
		// That would allow some schema objects to be completely replaced without interfering.

		return $graphs;
	}

	/**
	 * Get Layout
	 *
	 * Presets the schema layout to be generated.
	 *
	 * This concept is intended to allow...
	 *
	 * * Better dynamics with configurable layout settings.
	 * * Unnecessarily generating data where some instances remove it.
	 *
	 * @since 1.0.0
	 *
	 * @uses WP's Template Hierarchy
	 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
	 */
	public function get_layout() {
		$layout = array(
			'@context' => 'https://schema.org',
			'@graph'   => array(
				'[gofer_seo_schema_organization]',
				'[gofer_seo_schema_website]',
			),
		);

		// TODO Add layout customizations to settings.
		if (
				'single_page' === Gofer_SEO_Context::get_is() &&
				function_exists( 'bp_is_user' ) &&
				bp_is_user()
		) {
			// Correct issue with BuddyPress when viewing a member page.
			array_push( $layout['@graph'], '[gofer_seo_schema_profilepage]' );
			array_push( $layout['@graph'], '[gofer_seo_schema_person]' );
			array_push( $layout['@graph'], '[gofer_seo_schema_breadcrumblist]' );
		} elseif ( is_front_page() || is_home() ) {
			array_push( $layout['@graph'], '[gofer_seo_schema_webpage]' );
			array_push( $layout['@graph'], '[gofer_seo_schema_breadcrumblist]' );
		} elseif ( is_archive() ) {
			if ( is_author() ) {
				array_push( $layout['@graph'], '[gofer_seo_schema_profilepage]' );
				array_push( $layout['@graph'], '[gofer_seo_schema_person]' );
				array_push( $layout['@graph'], '[gofer_seo_schema_breadcrumblist]' );
			} elseif ( is_post_type_archive() ) {
				array_push( $layout['@graph'], '[gofer_seo_schema_collectionpage]' );
				array_push( $layout['@graph'], '[gofer_seo_schema_breadcrumblist]' );
			} elseif ( is_tax() || is_category() || is_tag() ) {
				array_push( $layout['@graph'], '[gofer_seo_schema_collectionpage]' );
				array_push( $layout['@graph'], '[gofer_seo_schema_breadcrumblist]' );
				// Remove when Custom Taxonomies is supported.
				if ( is_tax() ) {
					$layout = array();
				}
			} elseif ( is_date() ) {
				array_push( $layout['@graph'], '[gofer_seo_schema_collectionpage]' );
				array_push( $layout['@graph'], '[gofer_seo_schema_breadcrumblist]' );
			}
		} elseif ( is_singular() || is_single() ) {
			global $post;

			array_push( $layout['@graph'], '[gofer_seo_schema_webpage]' );
			if ( ! is_post_type_hierarchical( $post->post_type ) ) {
				// TODO Add custom setting for individual posts.

				array_push( $layout['@graph'], '[gofer_seo_schema_article]' );
				array_push( $layout['@graph'], '[gofer_seo_schema_person]' );
			}
			array_push( $layout['@graph'], '[gofer_seo_schema_breadcrumblist]' );

			// Remove when CPT is supported.
			if ( ! in_array( get_post_type( $post ), array( 'post', 'page' ), true ) ) {
				$layout = array();
			}
		} elseif ( is_search() ) {
			array_push( $layout['@graph'], '[gofer_seo_schema_searchresultspage]' );
			array_push( $layout['@graph'], '[gofer_seo_schema_breadcrumblist]' );
		} elseif ( is_404() ) {
			// Do 404 page.
		}

		/**
		 * Schema Layout
		 *
		 * Pre-formats the schema array shortcode layout.
		 *
		 * @since 1.0.0
		 *
		 * @param array $layout Schema array/object containing shortcodes.
		 */
		$layout = apply_filters( 'gofer_seo_schema_layout', $layout );

		// Encode to json string, and remove string type around shortcodes.
		if ( version_compare( PHP_VERSION, '5.4', '>=' ) ) {
			$layout = wp_json_encode( (object) $layout, JSON_UNESCAPED_SLASHES ); // phpcs:ignore PHPCompatibility.Constants.NewConstants.json_unescaped_slashesFound
		} else {
			// PHP <= 5.3 compatibility.
			$layout = wp_json_encode( (object) $layout );
			$layout = str_replace( '\/', '/', $layout );
		}

		$layout = str_replace( '"[', '[', $layout );
		$layout = str_replace( ']"', ']', $layout );

		return $layout;
	}

	/**
	 * Display JSON LD Script
	 *
	 * @since 1.0.0
	 */
	public function display_json_ld_head_script() {
		// do stuff.

		$layout = $this->get_layout();

		do_action( 'gofer_seo_schema_internal_shortcodes_on' );
		$schema_content = do_shortcode( $layout );
		do_action( 'gofer_seo_schema_internal_shortcodes_off' );

		echo '<script type="application/ld+json" class="gofer-seo-schema">' . gofer_seo_esc_json( $schema_content, true ) . '</script>';
		echo "\n";
	}

	/**
	 * Display JSON LD Script
	 *
	 * Intended for data that isn't readily available during `wp_head`.
	 *
	 * This should be avoided if possible. If an instance requires data to be loaded later,
	 * then use transient data to load in next instance within `wp_head`.
	 *
	 * @since 1.0.0
	 */
	public function display_json_ld_body_script() {
		// do stuff.
	}

}
