<?php
/**
 * Gofer SEO Core Class
 *
 * Handles all the core operations required to run on a WordPress platform.
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

/**
 * Class Gofer_SEO_Core
 *
 * @since 1.0.0
 */
class Gofer_SEO {

	/**
	 * @var Gofer_SEO_Module_Loader $module_loader
	 */
	public $module_loader;

	/**
	 * Singleton Instance.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var null $instance Singleton Class Instance.
	 */
	private static $instance = null;

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
	 * @access private
	 * @return Gofer_SEO
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Gofer_SEO_Core constructor.
	 *
	 * Set plugin's globals, constants, and initialization hook.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		// Initialize plugin.
		add_action( 'plugin_loaded', array( $this, 'pre_load' ), 3 );
		add_action( 'mu_plugin_loaded', array( $this, 'pre_load' ), 3 );
		add_action( 'network_plugin_loaded', array( $this, 'pre_load' ), 3 );
		add_action( 'plugins_loaded', array( $this, 'load' ), 3 );
	}

	/**
	 * Handles require_once files.
	 *
	 * @ignore
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function _requires() {
		// INCLUDES.
		require_once GOFER_SEO_DIR . 'includes/static-class-error-functions.php';
		require_once GOFER_SEO_DIR . 'includes/class-error.php';
		require_once GOFER_SEO_DIR . 'includes/class-errors.php';
		require_once GOFER_SEO_DIR . 'includes/compatibility/class-compat.php';
		require_once GOFER_SEO_DIR . 'includes/functions.php';
		require_once GOFER_SEO_DIR . 'includes/query.php';
		require_once GOFER_SEO_DIR . 'includes/static-class-methods.php';
		require_once GOFER_SEO_DIR . 'includes/typesetters/class-typesetter-data.php';
		require_once GOFER_SEO_DIR . 'includes/class-options.php';
		require_once GOFER_SEO_DIR . 'includes/class-post.php';
		require_once GOFER_SEO_DIR . 'includes/class-term.php';
		require_once GOFER_SEO_DIR . 'includes/class-user.php';
		require_once GOFER_SEO_DIR . 'includes/template.php';
		require_once GOFER_SEO_DIR . 'includes/formatting.php';
		require_once GOFER_SEO_DIR . 'includes/class-format-shortcodes.php';
		require_once GOFER_SEO_DIR . 'includes/class-context.php';
		require_once GOFER_SEO_DIR . 'includes/class-filesystem.php';
		// Modules.
		require_once GOFER_SEO_DIR . 'includes/class-module-loader.php';
		require_once GOFER_SEO_DIR . 'includes/modules/class-module.php';
		require_once GOFER_SEO_DIR . 'includes/modules/class-module-general.php';
		require_once GOFER_SEO_DIR . 'includes/modules/class-module-social-media.php';
		require_once GOFER_SEO_DIR . 'includes/modules/class-module-sitemap.php';
		require_once GOFER_SEO_DIR . 'includes/modules/class-module-schema-graph.php';
		require_once GOFER_SEO_DIR . 'includes/modules/class-module-crawlers.php';
		require_once GOFER_SEO_DIR . 'includes/modules/class-module-advanced.php';
		require_once GOFER_SEO_DIR . 'includes/modules/class-module-debugger.php';

		require_once GOFER_SEO_DIR . 'includes/schema/class-schema-builder.php';

		// Plugin Updater.
		require_once GOFER_SEO_DIR . 'includes/class-updater.php';

		// PUBLIC.
		require_once GOFER_SEO_DIR . 'public/class-google-analytics.php';

		require_once GOFER_SEO_DIR . 'admin/class-gofer-seo-admin.php';
	}

	/**
	 * Pre-Load.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin Full path to the plugin's main file.
	 */
	public function pre_load( $plugin ) {
		if ( GOFER_SEO_DIR . 'gofer-seo.php' !== wp_normalize_path( $plugin ) ) {
			return;
		}

		$this->_requires();

		$this->set_ini();

		remove_action( 'plugin_loaded', array( $this, 'pre_load' ), 3 );
	}

	/**
	 * Initialize plugin.
	 *
	 * TODO Refactor method on lines marked `TODO`.
	 *
	 * @since 1.0.0
	 */
	public function load() {
		$this->check_version_updates();
		$this->add_capabilities();

		load_plugin_textdomain( 'gofer-seo', false, dirname( GOFER_SEO_PLUGIN_BASENAME ) . '/languages/' );

		$file_dir = GOFER_SEO_DIR . 'gofer-seo.php';
		register_activation_hook( $file_dir, array( 'Gofer_SEO', 'activate' ) );
		register_deactivation_hook( $file_dir, array( 'Gofer_SEO', 'deactivate' ) );

		$this->module_loader = new Gofer_SEO_Module_Loader();
		new Gofer_SEO_Compat();

		add_action( 'init', array( $this, 'init' ), 3 );

		add_action( 'template_redirect', array( $this, 'template_redirect_rss_robots_meta' ) );

		add_action( 'wp_head', array( $this, 'wp_head' ) );

		if ( current_user_can( 'gofer_seo_access' ) ) {
			Gofer_SEO_Admin::get_instance();

			//add_action( 'admin_init', array( $this, 'scan_post_header' ) );
		}
	}

	/**
	 * Initialize Plugin.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		// Do stuff that can't be done in load().
	}

	/**
	 * Check for Version Updates.
	 *
	 * @since 1.0.0
	 */
	public function check_version_updates() {
		$updater = new Gofer_SEO_Updater();
		if ( $updater->needs_update ) {
			do_action( 'gofer_seo_before_doing_updates' );
			$updater->do_updates();
			do_action( 'gofer_seo_after_doing_updates' );
		}
	}

	/**
	 * Set ini configs.
	 *
	 * Set by the Advanced performance settings to adjust the memory limit & max execution time on the system ini config.
	 *
	 * @since 1.0.0
	 */
	private function set_ini() {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		// Set Memory Limit based on settings. Default 256M.
		add_filter( 'gofer_seo_memory_limit', array( $this, 'memory_limit' ) );
		wp_raise_memory_limit( 'gofer_seo' );
		remove_filter( 'gofer_seo_memory_limit', array( $this, 'memory_limit' ) );

		// Set Execution Time.
		if ( -1 < $gofer_seo_options->options['modules']['advanced']['php_max_execution_time'] ) {
			set_time_limit( intval( $gofer_seo_options->options['modules']['advanced']['php_max_execution_time'] ) );
		}
	}

	/**
	 * Filters the memory limit allocated for arbitrary contexts.
	 *
	 * The dynamic portion of the hook name, `$context`, refers to an arbitrary
	 * context passed on calling the function. This allows for plugins to define
	 * their own contexts for raising the memory limit.
	 *
	 * @since 1.0.0
	 *
	 * @param int|string $limit Maximum memory limit to allocate for images.
	 *                          Default '256M' or the original php.ini `memory_limit`,
	 *                          whichever is higher. Accepts an integer (bytes), or a
	 *                          shorthand string notation, such as '256M'.
	 * @return string
	 */
	public function memory_limit( $limit ) {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		if ( 0 < intval( GOFER_SEO_MEMORY_LIMIT ) ) {
			return gofer_seo_convert_bytestring( GOFER_SEO_MEMORY_LIMIT );
		}

		// Sets the memory limit based on settings. Default 256M.
		if ( $gofer_seo_options->options['enable_modules']['advanced'] ) {
			if ( 0 < $gofer_seo_options->options['modules']['advanced']['php_memory_limit'] ) {
				return gofer_seo_convert_bytestring( $gofer_seo_options->options['modules']['advanced']['php_memory_limit'] );
			}
		}

		return $limit;
	}

	/**
	 * Plugin Activation.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		// require_once GOFER_SEO_DIR . 'admin/partials/class-notifications.php';
		// $gofer_seo_notifications = Gofer_SEO_Notifications::get_instance();
		// $gofer_seo_notifications->reset_notice( 'review_plugin' );

		flush_rewrite_rules();
	}

	/**
	 * Plugin Deactivation.
	 * 
	 * @since 1.0.0
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Add Capabilities
	 *
	 * @since 1.0.0
	 */
	public function add_capabilities() {
		$role = get_role( 'administrator' );
		if ( is_object( $role ) ) {
			$role->add_cap( 'gofer_seo_access' );

			// TODO Add to settings to control user perms.
			// Currently unused.
			$role->add_cap( 'gofer_seo_access_post_editor' );
			$role->add_cap( 'gofer_seo_access_settings' );
			$role->add_cap( 'gofer_seo_access_settings_advanced' );
		}
	}

	/**
	 * The noindex_follow_rss() function.
	 *
	 * Adds "noindex,follow" as HTTP header for RSS feeds.
	 *
	 * TODO Possibly move to Sitemap module.
	 *
	 * @since 1.0.0
	 */
	public function template_redirect_rss_robots_meta() {
		if ( is_feed() && headers_sent() === false ) {
			/**
			 * The NoIndex RSS filter hook.
			 *
			 * Filter whether RSS feeds should or shouldn't have HTTP noindex header.
			 *
			 * @since 1.0.0
			 *
			 * @param bool
			 */
			$noindex = apply_filters( 'gofer_seo_noindex_rss', true );
			if ( $noindex ) {
				header( 'X-Robots-Tag: noindex, follow', true );
			}
		}
	}

	/**
	 * WP Head Hook.
	 *
	 * @since 1.0.0
	 *
	 * @see 'wp_head' hook.
	 * @link https://developer.wordpress.org/reference/hooks/wp_head/
	 */
	public function wp_head() {
		if ( has_action( 'gofer_seo_wp_head' ) ) {
			printf( '%1$s<!-- %2$s - %3$s -->%1$s', "\n", esc_html( GOFER_SEO_NAME ), esc_html( GOFER_SEO_VERSION ) );

			/**
			 * Gofer SEO - WP Head
			 *
			 * @since 1.0.0
			 */
			do_action( 'gofer_seo_wp_head' );

			printf( '%1$s<!-- %2$s -->%1$s', "\n", esc_html( GOFER_SEO_NAME ) );
		}
	}

}
