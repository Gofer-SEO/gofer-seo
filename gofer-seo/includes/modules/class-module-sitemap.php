<?php
/**
 * Gofer SEO Module: Sitemap class
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Module_Sitemap
 *
 * @since 1.0.0
 */
class Gofer_SEO_Module_Sitemap extends Gofer_SEO_Module {

	/**
	 * Start Memory Usage
	 *
	 * Is set by PHP's `memory_get_peak_usage()`.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $start_memory_usage = 0;

	/**
	 * Sitemap Data Providers.
	 *
	 * @since 1.0.0
	 *
	 * @var Gofer_SEO_Sitemaps_Provider[] $providers
	 */
	private $providers;

	/**
	 * Sitemaps Renderer.
	 *
	 * @since 1.0.0
	 *
	 * @var Gofer_SEO_Sitemaps_Renderer $renderer
	 */
	private $renderer;

	/**
	 * News Sitemap Data Providers.
	 *
	 * @since 1.0.0
	 *
	 * @var Gofer_SEO_News_Sitemaps_Provider[] $news_providers
	 */
	private $news_providers;

	/**
	 * News Sitemaps Renderer.
	 *
	 * @since 1.0.0
	 *
	 * @var Gofer_SEO_News_Sitemaps_Renderer $news_renderer
	 */
	private $news_renderer;

	/**
	 * RSS Sitemap Data Providers.
	 *
	 * @since 1.0.0
	 *
	 * @var Gofer_SEO_RSS_Sitemaps_Provider[] $rss_providers
	 */
	private $rss_providers;

	/**
	 * RSS Sitemaps Renderer.
	 *
	 * @since 1.0.0
	 *
	 * @var Gofer_SEO_RSS_Sitemaps_Renderer $rss_renderer
	 */
	private $rss_renderer;

	/**
	 * Gofer_SEO_Module_Sitemap constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Required Files.
	 *
	 * @since 1.0.0
	 */
	protected function _requires() {
		// Sitemaps Data/Provider.
		// Standard Sitemaps.
		require_once GOFER_SEO_DIR . 'includes/sitemap/providers/class-sitemaps-provider.php';
		require_once GOFER_SEO_DIR . 'includes/sitemap/providers/class-sitemaps-provider-posts.php';
		require_once GOFER_SEO_DIR . 'includes/sitemap/providers/class-sitemaps-provider-taxonomies.php';
		require_once GOFER_SEO_DIR . 'includes/sitemap/providers/class-sitemaps-provider-users.php';
		require_once GOFER_SEO_DIR . 'includes/sitemap/providers/class-sitemaps-provider-dates.php';
		require_once GOFER_SEO_DIR . 'includes/sitemap/class-sitemaps-renderer.php';
		require_once GOFER_SEO_DIR . 'includes/sitemap/class-sitemaps-stylesheet.php';

		// News Sitemaps.
		require_once GOFER_SEO_DIR . 'includes/sitemap/providers/class-news-sitemaps-provider.php';
		require_once GOFER_SEO_DIR . 'includes/sitemap/providers/class-news-sitemaps-provider-posts.php';
		require_once GOFER_SEO_DIR . 'includes/sitemap/class-news-sitemaps-renderer.php';
		require_once GOFER_SEO_DIR . 'includes/sitemap/class-news-sitemaps-stylesheet.php';

		// RSS Feed.
		require_once GOFER_SEO_DIR . 'includes/sitemap/providers/class-rss-sitemaps-provider.php';
		require_once GOFER_SEO_DIR . 'includes/sitemap/providers/class-rss-sitemaps-provider-posts.php';
		require_once GOFER_SEO_DIR . 'includes/sitemap/class-rss-sitemaps-renderer.php';
		require_once GOFER_SEO_DIR . 'includes/sitemap/class-rss-sitemaps-stylesheet.php';
	}

	/**
	 * Load.
	 *
	 * @since 1.0.0
	 */
	public function load() {
		parent::load();
		$this->_requires();
		$this->validate_options();

		// TODO is this required for dynamic sitemap?
		add_action( 'transition_post_status', array( $this, 'transition_post_status' ), 10, 3 );
		add_action( 'admin_init', array( $this, 'sitemap_notices' ) );
		add_filter( 'home_url', array( $this, 'home_url_sitemap' ), 10, 2 );
		add_filter( 'user_trailingslashit', array( $this, 'user_trailingslashit' ), 10, 2 );

		/**
		 * Filters whether to display the URL to the XML Sitemap on our virtual robots.txt file.
		 *
		 * Defaults to true. Return __return_false in order to not display the URL.
		 *
		 * @since 1.0.0
		 *
		 * @param boolean Defaults to true.
		 */
		if ( apply_filters( 'gofer_seo_robots_txt_sitemaps', true ) ) {
			add_action( 'do_robots', array( $this, 'do_robots' ), 9 );
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
	public function init() {
		parent::init();
		global $wp_version;
		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		if ( version_compare( '5.5.0', $wp_version, '<=' ) ) {
			$this->register_rewrites_sitemap();
			add_action( 'template_redirect', array( $this, 'render_sitemaps' ) );
			$this->register_sitemaps();
			$this->renderer = new Gofer_SEO_Sitemaps_Renderer();

			$gofer_seo_options = Gofer_SEO_Options::get_instance();
			if ( $gofer_seo_options->options['modules']['sitemap']['enable_news_sitemap'] ) {
				$this->register_rewrites_news_sitemap();
				add_action( 'template_redirect', array( $this, 'render_news_sitemaps' ) );
				$this->register_news_sitemaps();
				$this->news_renderer = new Gofer_SEO_News_Sitemaps_Renderer();
			}
			if ( $gofer_seo_options->options['modules']['sitemap']['enable_rss_sitemap'] ) {
				$this->register_rewrites_rss_sitemap();
				add_action( 'template_redirect', array( $this, 'render_rss_sitemaps' ) );
				$this->register_rss_sitemaps();
				$this->rss_renderer = new Gofer_SEO_RSS_Sitemaps_Renderer();
			}
		}
	}

	/**
	 * User Trailing Slash It (Hook).
	 *
	 * Removes trailing slash from any sitemap.xml as it represents a file; not a directory.
	 *
	 * @since 1.0.0
	 *
	 * @param string $string      URL with or without a trailing slash.
	 * @param string $type_of_url Optional. The type of URL being considered (e.g. single, category, etc)
	 *                            for use in the filter. Default empty string.
	 * @return string The URL with the trailing slash appended or stripped.
	 */
	function user_trailingslashit( $string, $type_of_url ) {
		$regex_patterns = array(
			'/^\/sitemap\.xml\/$/',
			'/^\/sitemap-([a-z]+?)-([a-z\d_-]+?)-(\d+?)\.xml\/$/',
			'/^\/sitemap-([a-z]+?)-(\d+?)\.xml\/$/',
			'/^\/sitemap\.xsl\/$/',
			'/^\/sitemap-index\.xsl\/$/',
			'/^\/news-sitemap\.xml\/$/',
			'/^\/news-sitemap-([a-z]+?)-([a-z\d_-]+?)-(\d+?)\.xml\/$/',
			'/^\/news-sitemap-([a-z]+?)-(\d+?)\.xml\/$/',
			'/^\/news-sitemap\.xsl\/$/',
			'/^\/news-sitemap-index\.xsl\/$/',
			'/^\/rss-sitemap\.xml\/$/',
			'/^\/rss-sitemap-([a-z]+?)-([a-z\d_-]+?)-(\d+?)\.xml\/$/',
			'/^\/rss-sitemap-([a-z]+?)-(\d+?)\.xml\/$/',
			'/^\/rss-sitemap\.xsl\/$/',
			'/^\/rss-sitemap-index\.xsl\/$/',
		);
		foreach ( $regex_patterns as $pattern ) {
			if ( preg_match( $pattern, $string ) ) {
				$string = untrailingslashit( $string );
			}
		}

		return $string;
	}

	/**
	 * Load Sitemap Options
	 *
	 * Initialize options, after constructor.
	 *
	 * @since 1.0.0
	 */
	public function validate_options() {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		if (
				0 >= $gofer_seo_options->options['modules']['sitemap']['posts_per_sitemap'] ||
				50000 < $gofer_seo_options->options['modules']['sitemap']['posts_per_sitemap']
		) {
			$gofer_seo_options->options['modules']['sitemap']['posts_per_sitemap'] = 50000;
			$gofer_seo_options->update_options();
		}
	}

	/**
	 * Sitemap Notices
	 *
	 * @todo Move admin notice functions. Possibly to where it is first saved & loaded (`validate_options`).
	 *
	 * @global Gofer_SEO_Notifications $gofer_seo_notifications
	 *
	 * @since 1.0.0
	 */
	public function sitemap_notices() {
		if ( ! current_user_can( 'gofer_seo_access' ) ) {
			return;
		}
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		$gofer_seo_notifications = Gofer_SEO_Notifications::get_instance();

		if (
				! $gofer_seo_options->options['modules']['sitemap']['enable_indexes'] ||
				(
					$gofer_seo_options->options['modules']['sitemap']['enable_indexes'] &&
					1000 < $gofer_seo_options->options['modules']['sitemap']['posts_per_sitemap']
				)
		) {
			$num_terms   = 0;
			$enabled_post_types = gofer_seo_get_enabled_post_types( 'sitemap' );
			$post_counts = $this->get_total_post_count(
				array(
					'post_type'   => $enabled_post_types,
					'post_status' => 'publish',
				)
			);

			$enabled_taxonomies = gofer_seo_get_enabled_taxonomies( 'sitemap' );
			$term_counts        = $this->get_all_term_counts( array( 'taxonomy' => $enabled_taxonomies ) );
			if ( isset( $term_counts ) && is_array( $term_counts ) ) {
				$num_terms = array_sum( $term_counts );
			}

			$sitemap_urls = $post_counts + $num_terms;

			if ( 1000 < $sitemap_urls ) {
				$gofer_seo_notifications->activate_notice( 'sitemap_max_warning' );
			} else {
				$gofer_seo_notifications->deactivate_notice( 'sitemap_max_warning' );
			}
		} else {
			$gofer_seo_notifications->deactivate_notice( 'sitemap_max_warning' );
		}
	}

	/**
	 * Update Sitemap from Posts
	 *
	 * Triggers the do_sitemaps scan when a post is saved/transitioned.
	 *
	 * @since 1.0.0
	 *
	 * @param $new_status
	 * @param $old_status
	 * @param $post
	 */
	public function transition_post_status( $new_status, $old_status, $post ) {
		// Ignore WP API requests.
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}

		$enabled_post_types = gofer_seo_get_enabled_post_types( 'sitemap' );
		if ( ! in_array( $post->post_type, $enabled_post_types, true ) ) {
			return;
		}

		$statuses_for_updating = array( 'new', 'publish', 'trash' );
		if ( ! in_array( $new_status, $statuses_for_updating, true ) ) {
			return;
		}

		$this->do_ping();
	}

	/**
	 * Get Filename
	 *
	 * Get the filename prefix for the sitemap file.
	 * If a value was provided when this prefix was configurable from the settings page, return that instead of the default.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_filename() {
		$filename = 'sitemap';
		if ( $this instanceof Gofer_SEO_Pro_Module_Video_Sitemap ) {
			$filename = 'video-sitemap';
		}
		/**
		 * Filters the filename: sitemap OR video_sitemap filename.
		 *
		 * @param string $filename The file name.
		 */
		return apply_filters( 'gofer_seo_sitemap_filename', $filename );
	}

	/**
	 * Log Start
	 *
	 * Start timing and get initial memory usage for debug info.
	 *
	 * @since 1.0.0
	 */
	public function log_stats_start() {
		$this->start_memory_usage = memory_get_peak_usage();
		timer_start();
	}

	/**
	 * Log Stats
	 *
	 * Stop timing and log memory usage for debug info.
	 *
	 * @since 1.0.0
	 */
	public function log_stats_end() {
		$time                 = timer_stop();
		$end_memory_usage     = memory_get_peak_usage();
		$sitemap_memory_usage = $end_memory_usage - $this->start_memory_usage;
		$end_memory_usage     = $end_memory_usage / 1024.0 / 1024.0;
		$sitemap_memory_usage = $sitemap_memory_usage / 1024.0 / 1024.0;

		$error_message = sprintf(
			'%01.2f MB memory used generating the sitemap in %01.3f seconds, %01.2f MB total memory used.',
			$sitemap_memory_usage,
			$time,
			$end_memory_usage
		);
		new Gofer_SEO_Error( 'gofer_seo_module_sitemap_log_stats', $error_message );
	}

	/**
	 * Home URL (Hook) - Sitemap (URL).
	 *
	 * Used to replace/redirect WP's sitemap URL from `/wp-sitemap.xml` to `/sitemap.xml`.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url  The complete home URL including scheme and path.
	 * @param string $path Path relative to the home URL. Blank string if no path is specified.
	 * @return string
	 */
	public function home_url_sitemap( $url, $path ) {
		if ( '/wp-sitemap.xml' === $path ) {
			$url = $this->get_sitemap_url();
		}

		return $url;
	}

	/**
	 * Get Sitemap URL
	 *
	 * Build a url to the sitemap.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_sitemap_url() {
		global $wp_rewrite;

		if ( ! $wp_rewrite->using_permalinks() ) {
			return home_url( '/?' . $this->get_filename() . '=index' );
		}

		return home_url( '/' . $this->get_filename() . '.xml' );
	}

	/**
	 * Do Notify
	 *
	 * Notify search engines, do logging.
	 *
	 * @since 1.0.0
	 */
	public function do_ping() {
		if ( '0' === get_option( 'blog_public' ) ) {
			// Don't ping search engines if blog is set to not public.
			return;
		}

		if ( apply_filters( 'gofer_seo_sitemap_ping', true ) === false ) {
			// API filter hook to disable sending sitemaps to search engines.
			return;
		}

		$notify_url = array(
			'google' => 'https://www.google.com/ping?sitemap=',
			'bing'   => 'https://www.bing.com/ping?sitemap=',
		);

		$notify_url = apply_filters( 'gofer_seo_sitemap_ping_urls', $notify_url );

		$url = $this->get_sitemap_url();
		if ( ! empty( $url ) ) {
			foreach ( $notify_url as $k => $v ) {
				// TODO Change urlencode() to rawurlencode().
				// @link ( http://php.net/manual/en/function.rawurlencode.php ).
				// @link ( http://www.faqs.org/rfcs/rfc3986.html ).
				$response = wp_remote_get( $notify_url[ $k ] . rawurlencode( $url ) );
				if ( is_array( $response ) && ! empty( $response['response'] ) && ! empty( $response['response']['code'] ) ) {
					if ( 200 !== intval( $response['response']['code'] ) ) {
						$error_message = sprintf(
							/* translators: %1$s is the search engine, %2$s is a URL, and %3$s is the error code. */
							__( 'Failed to notify %1$s about changes to your sitemap at %2$s, error code %3$s.', 'gofer-seo' ),
							$k,
							$url,
							$response['response']['code']
						);
						new Gofer_SEO_Error( 'gofer_seo_module_sitemap_ping_1', $error_message );
					}
				} else {
					/* translators: Notifies the admin which sitemaps failed to notify with which search engine(s). */
					$error_message = sprintf(
					/* translators: %1$s is the search engine, and %2$s is a URL. */
					__( 'Failed to notify %1$s about changes to your sitemap at %2$s, unable to access via wp_remote_get().', 'gofer-seo' ),
						$k,
						$url
					);
					new Gofer_SEO_Error( 'gofer_seo_module_sitemap_ping_2', $error_message );
				}
			}
		}
	}

	/**
	 * Do Robots
	 *
	 * Add Sitemap parameter to virtual robots.txt file.
	 *
	 * @since 1.0.0
	 */
	public function do_robots() {
		$url = $this->get_sitemap_url();
		echo sprintf(
			'%2$sSitemap: %1$s%2$s',
			esc_url( $url ),
			PHP_EOL
		);
	}


	/* **________**************************************************************************************************/
	/* _/ COUNTS \________________________________________________________________________________________________*/


	/**
	 * Get All Terms Counts
	 *
	 * Return term counts using wp_count_terms().
	 *
	 * @since 1.0.0
	 *
	 * @param $args
	 * @return array|int|mixed|null|WP_Error
	 */
	public function get_all_term_counts( $args ) {
		$term_counts = null;
		if ( ! empty( $args ) && ! empty( $args['taxonomy'] ) ) {
			// TODO Add `true` in 3rd argument with in_array(); which changes it to a strict comparison.
			if ( ! is_array( $args['taxonomy'] ) || ( count( $args['taxonomy'] ) === 1 ) ) {
				if ( is_array( $args['taxonomy'] ) ) {
					$args['taxonomy'] = array_shift( $args['taxonomy'] );
				}
				$term_counts = wp_count_terms( $args['taxonomy'], array( 'hide_empty' => true ) );
			} else {
				foreach ( $args['taxonomy'] as $taxonomy ) {
					if ( 'all' === $taxonomy ) {
						continue;
					}
//					$term_counts[ $taxonomy ] = wp_count_terms( $this->show_or_hide_taxonomy( $taxonomy ), array( 'hide_empty' => true ) );
					$term_counts[ $taxonomy ] = wp_count_terms( $taxonomy, array( 'hide_empty' => true ) );
				}
			}
		}
		$term_counts = apply_filters( 'gofer_seo_sitemap_term_counts', $term_counts, $args );

		return $term_counts;
	}

	/**
	 * Get All Post Counts
	 *
	 * Return post counts.
	 *
	 * @since 1.0.0
	 *
	 * @param $args
	 * @return array
	 */
	public function get_all_post_counts( $args ) {
		$post_counts = array();
		$status      = 'inherit';
		if ( ! empty( $args['post_status'] ) ) {
			$status = $args['post_status'];
		}
		if ( ! empty( $args ) && ! empty( $args['post_type'] ) ) {
			// #884: removed hard-to-understand code here which suspected $args['post_type'] to NOT be an array. Do not see any case in which this is likely to happen.
			foreach ( $args['post_type'] as $post_type ) {
				if ( 'all' === $post_type ) {
					continue;
				}

				$post_type_count = (array) wp_count_posts( $post_type );
				$post_counts[ $post_type ] = 0;
				if ( 'attachment' === $post_type ) {
					$post_counts[ $post_type ] = $post_type_count['inherit'];
				} elseif ( ! empty( $post_type_count[ $status ] ) ) {
					$post_counts[ $post_type ] = $post_type_count[ $status ];
				}
			}
		}
		$post_counts = apply_filters( 'gofer_seo_sitemap_post_counts', $post_counts, $args );

		return $post_counts;
	}

	/**
	 * Get total post count.
	 *
	 * @since 1.0.0
	 *
	 * @param $args
	 * @return int
	 */
	public function get_total_post_count( $args ) {
		$total  = 0;
		$counts = $this->get_all_post_counts( $args );
		if ( ! empty( $counts ) ) {
			foreach ( $counts as $count ) {
				$total += $count;
			}
		}

		return $total;
	}


	/* **_________*****************************************************************************************************/
	/* _/ SITEMAP \___________________________________________________________________________________________________*/


	/**
	 * Registers and sets up the functionality for all supported sitemaps.
	 *
	 * @since 1.0.0
	 */
	public function register_sitemaps() {
		$providers = array(
			'posts'      => new Gofer_SEO_Sitemaps_Provider_Posts(),
			'taxonomies' => new Gofer_SEO_Sitemaps_Provider_Taxonomies(),
			'users'      => new Gofer_SEO_Sitemaps_Provider_Users(),
			'dates'      => new Gofer_SEO_Sitemaps_Provider_Dates(),
		);

		global $wp_version;
		if ( version_compare( '5.5.0', $wp_version, '>' ) || ! function_exists( 'wp_sitemaps_get_server' ) ) {
			$this->providers = $providers;
		} else {
			$wp_sitemaps = wp_sitemaps_get_server();
			foreach ( $providers as $name => $provider ) {
				$wp_sitemaps->registry->add_provider( $name, $provider );
				$this->providers = $providers;
			}
		}
	}

	/**
	 * Registers and sets up the functionality for all supported sitemaps.
	 *
	 * @since 1.0.0
	 */
	public function register_news_sitemaps() {
		$this->news_providers = array(
			'posts' => new Gofer_SEO_News_Sitemaps_Provider_Posts(),
		);
	}

	/**
	 * Registers and sets up the functionality for all supported sitemaps.
	 *
	 * @since 1.0.0
	 */
	public function register_rss_sitemaps() {
		$this->rss_providers = array(
			'posts' => new Gofer_SEO_RSS_Sitemaps_Provider_Posts(),
		);
	}

	/**
	 * Registers sitemap rewrite tags and routing rules.
	 *
	 * @since 1.0.0
	 */
	public function register_rewrites_sitemap() {
		// Add rewrite tags.
		add_rewrite_tag( '%sitemap%', '([^?]+)' );
		add_rewrite_tag( '%sitemap-subtype%', '([^?]+)' );

		// Register index route.
		add_rewrite_rule(
			'^sitemap\.xml$',
			'index.php?sitemap=index',
			'top'
		);

		// Register rewrites for the XSL stylesheet.
		add_rewrite_tag( '%sitemap-stylesheet%', '([^?]+)' );
		add_rewrite_rule( '^sitemap\.xsl$', 'index.php?sitemap-stylesheet=sitemap', 'top' );
		add_rewrite_rule( '^sitemap-index\.xsl$', 'index.php?sitemap-stylesheet=index', 'top' );

		// Register routes for providers.
		add_rewrite_rule(
			'^sitemap-([a-z]+?)-([a-z\d_-]+?)-(\d+?)\.xml$',
			'index.php?sitemap=$matches[1]&sitemap-subtype=$matches[2]&paged=$matches[3]',
			'top'
		);
		add_rewrite_rule(
			'^sitemap-([a-z]+?)-(\d+?)\.xml$',
			'index.php?sitemap=$matches[1]&paged=$matches[2]',
			'top'
		);
	}

	/**
	 * Registers sitemap rewrite tags and routing rules.
	 *
	 * @since 1.0.0
	 */
	public function register_rewrites_news_sitemap() {
		// Add rewrite tags.
		add_rewrite_tag( '%news-sitemap%', '([^?]+)' );
		add_rewrite_tag( '%news-sitemap-subtype%', '([^?]+)' );

		// Register index route.
		add_rewrite_rule(
			'^news-sitemap\.xml$',
			'index.php?news-sitemap=index',
			'top'
		);

		// Register rewrites for the XSL stylesheet.
		add_rewrite_tag( '%news-sitemap-stylesheet%', '([^?]+)' );
		add_rewrite_rule( '^news-sitemap\.xsl$', 'index.php?news-sitemap-stylesheet=sitemap', 'top' );
		add_rewrite_rule( '^news-sitemap-index\.xsl$', 'index.php?news-sitemap-stylesheet=index', 'top' );

		// Register routes for providers.
		add_rewrite_rule(
			'^news-sitemap-([a-z]+?)-([a-z\d_-]+?)-(\d+?)\.xml$',
			'index.php?news-sitemap=$matches[1]&news-sitemap-subtype=$matches[2]&paged=$matches[3]',
			'top'
		);
		add_rewrite_rule(
			'^news-sitemap-([a-z]+?)-(\d+?)\.xml$',
			'index.php?news-sitemap=$matches[1]&paged=$matches[2]',
			'top'
		);
	}

	/**
	 * Registers sitemap rewrite tags and routing rules.
	 *
	 * @since 1.0.0
	 */
	public function register_rewrites_rss_sitemap() {
		// Add rewrite tags.
		add_rewrite_tag( '%rss-sitemap%', '([^?]+)' );
		add_rewrite_tag( '%rss-sitemap-subtype%', '([^?]+)' );

		// Register index route.
		add_rewrite_rule(
			'^rss-sitemap\.xml$',
			'index.php?rss-sitemap=index',
			'top'
		);

		// Register rewrites for the XSL stylesheet.
		add_rewrite_tag( '%rss-sitemap-stylesheet%', '([^?]+)' );
		add_rewrite_rule( '^rss-sitemap\.xsl$', 'index.php?rss-sitemap-stylesheet=sitemap', 'top' );
		add_rewrite_rule( '^rss-sitemap-index\.xsl$', 'index.php?rss-sitemap-stylesheet=index', 'top' );

		// Register routes for providers.
		add_rewrite_rule(
			'^rss-sitemap-([a-z]+?)-([a-z\d_-]+?)-(\d+?)\.xml$',
			'index.php?rss-sitemap=$matches[1]&rss-sitemap-subtype=$matches[2]&paged=$matches[3]',
			'top'
		);
		add_rewrite_rule(
			'^rss-sitemap-([a-z]+?)-(\d+?)\.xml$',
			'index.php?rss-sitemap=$matches[1]&paged=$matches[2]',
			'top'
		);
	}

	/**
	 * Renders sitemap templates based on rewrite rules.
	 *
	 * @since 1.0.0
	 *
	 * @global WP_Query $wp_query WordPress Query object.
	 */
	public function render_sitemaps() {
		global $wp_query;

		$sitemap         = sanitize_text_field( get_query_var( 'sitemap' ) );
		$object_subtype  = sanitize_text_field( get_query_var( 'sitemap-subtype' ) );
		$stylesheet_type = sanitize_text_field( get_query_var( 'sitemap-stylesheet' ) );
		$paged           = absint( get_query_var( 'paged' ) );

		if ( ! ( $sitemap || $stylesheet_type ) ) {
			return;
		}

		$gofer_seo_option = Gofer_SEO_Options::get_instance();


		// Render stylesheet if this is stylesheet route.
		if ( $stylesheet_type ) {
			$stylesheet = new Gofer_SEO_Sitemaps_Stylesheet();
			$stylesheet->render_stylesheet( $stylesheet_type );

			exit;
		}

		$this->log_stats_start();
		// Render the index.
		if ( 'index' === $sitemap ) {
			if ( $gofer_seo_option->options['modules']['sitemap']['enable_indexes'] ) {
				$sitemap_list = array();
				foreach ( $this->providers as $type => $provider ) {
					$tmp_sitemap_list = $provider->get_sitemap_list();
					foreach ( $tmp_sitemap_list as $tmp_sitemap_item ) {
						$sitemap_list[] = $tmp_sitemap_item;
					}
				}

				$this->renderer->render_index( $sitemap_list );

				exit;
			} else {
				$url_list = array();
				foreach ( $this->providers as $type => $provider ) {
					$tmp_url_list = $provider->get_url_list( 1 );
					foreach ( $tmp_url_list as $tmp_url_item ) {
						$url_list[] = $tmp_url_item;
					}
				}

				usort( $url_list, function( $a, $b ) {
					return strtotime( $a['lastmod'] ) - strtotime( $b['lastmod'] );
				} );
				array_splice( $url_list, 1000 );
			}
		} else {
			$provider = false;
			if ( isset( $this->providers[ $sitemap ] ) ) {
				$provider = $this->providers[ $sitemap ];
			}

			if ( ! $provider ) {
				return;
			}

			if ( empty( $paged ) ) {
				$paged = 1;
			}

			$url_list = $provider->get_url_list( $paged, $object_subtype );
		}

		// Force a 404 and bail early if no URLs are present.
		if ( empty( $url_list ) ) {
			$wp_query->set_404();
			status_header( 404 );

			// header( "Content-Type: text/html; charset=$blog_charset", true );
			nocache_headers();
			// include( get_404_template() );
			// exit;

			return;
		}

		$this->renderer->render_sitemap( $url_list );
		$this->log_stats_end();

		exit;
	}

	/**
	 * Renders sitemap templates based on rewrite rules.
	 *
	 * @since 1.0.0
	 *
	 * @global WP_Query $wp_query WordPress Query object.
	 */
	public function render_news_sitemaps() {
		global $wp_query;

		$sitemap         = sanitize_text_field( get_query_var( 'news-sitemap' ) );
		$object_subtype  = sanitize_text_field( get_query_var( 'news-sitemap-subtype' ) );
		$stylesheet_type = sanitize_text_field( get_query_var( 'news-sitemap-stylesheet' ) );
		$paged           = absint( get_query_var( 'paged' ) );

		if ( ! ( $sitemap || $stylesheet_type ) ) {
			return;
		}

		$gofer_seo_option = Gofer_SEO_Options::get_instance();


		// Render stylesheet if this is stylesheet route.
		if ( $stylesheet_type ) {
			$stylesheet = new Gofer_SEO_News_Sitemaps_Stylesheet();
			$stylesheet->render_stylesheet( $stylesheet_type );

			exit;
		}

		$this->log_stats_start();
		// Render the index.
		if ( 'index' === $sitemap ) {
			if ( $gofer_seo_option->options['modules']['sitemap']['enable_indexes'] ) {
				$sitemap_list = array();
				foreach ( $this->news_providers as $type => $provider ) {
					$tmp_sitemap_list = $provider->get_sitemap_list();
					foreach ( $tmp_sitemap_list as $tmp_sitemap_item ) {
						$sitemap_list[] = $tmp_sitemap_item;
					}
				}

				$this->news_renderer->render_index( $sitemap_list );

				exit;
			} else {
				$url_list = array();
				foreach ( $this->news_providers as $type => $provider ) {
					$tmp_url_list = $provider->get_url_list( 1 );
					foreach ( $tmp_url_list as $tmp_url_item ) {
						$url_list[] = $tmp_url_item;
					}
				}

				usort( $url_list, function( $a, $b ) {
					return strtotime( $a['lastmod'] ) - strtotime( $b['lastmod'] );
				} );
				array_splice( $url_list, 1000 );
			}
		} else {
			$provider = false;
			if ( isset( $this->news_providers[ $sitemap ] ) ) {
				$provider = $this->news_providers[ $sitemap ];
			}

			if ( ! $provider ) {
				return;
			}

			if ( empty( $paged ) ) {
				$paged = 1;
			}

			$url_list = $provider->get_url_list( $paged, $object_subtype );
		}

		// Force a 404 and bail early if no URLs are present.
		if ( empty( $url_list ) ) {
			$wp_query->set_404();
			status_header( 404 );

			// header( "Content-Type: text/html; charset=$blog_charset", true );
			nocache_headers();
			// include( get_404_template() );
			// exit;

			return;
		}

		$this->news_renderer->render_sitemap( $url_list );
		$this->log_stats_end();

		exit;
	}

	/**
	 * Renders sitemap templates based on rewrite rules.
	 *
	 * @since 1.0.0
	 *
	 * @global WP_Query $wp_query WordPress Query object.
	 */
	public function render_rss_sitemaps() {
		global $wp_query;

		$sitemap         = sanitize_text_field( get_query_var( 'rss-sitemap' ) );
		$object_subtype  = sanitize_text_field( get_query_var( 'rss-sitemap-subtype' ) );
		$stylesheet_type = sanitize_text_field( get_query_var( 'rss-sitemap-stylesheet' ) );
		$paged           = absint( get_query_var( 'paged' ) );

		if ( ! ( $sitemap || $stylesheet_type ) ) {
			return;
		}

		// Render stylesheet if this is stylesheet route.
		if ( $stylesheet_type ) {
			$stylesheet = new Gofer_SEO_RSS_Sitemaps_Stylesheet();
			$stylesheet->render_stylesheet( $stylesheet_type );

			exit;
		}

		$this->log_stats_start();
		// Render the index.
		if ( 'index' === $sitemap ) {
			$url_list = array();
			foreach ( $this->rss_providers as $type => $provider ) {
				$tmp_url_list = $provider->get_url_list( 1 );
				foreach ( $tmp_url_list as $tmp_url_item ) {
					$url_list[] = $tmp_url_item;
				}
			}

			usort( $url_list, function( $a, $b ) {
				return strtotime( $a['pubDate'] ) - strtotime( $b['pubDate'] );
			} );
			array_splice( $url_list, 1000 );
		} else {
			$provider = false;
			if ( isset( $this->rss_providers[ $sitemap ] ) ) {
				$provider = $this->rss_providers[ $sitemap ];
			}

			if ( ! $provider ) {
				return;
			}

			if ( empty( $paged ) ) {
				$paged = 1;
			}

			$url_list = $provider->get_url_list( $paged, $object_subtype );
		}

		// Force a 404 and bail early if no URLs are present.
		if ( empty( $url_list ) ) {
			$wp_query->set_404();
			status_header( 404 );

			// header( "Content-Type: text/html; charset=$blog_charset", true );
			nocache_headers();
			// include( get_404_template() );
			// exit;

			return;
		}

		$this->rss_renderer->render_sitemap( $url_list );
		$this->log_stats_end();

		exit;
	}

}
