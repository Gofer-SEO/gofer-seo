<?php
/**
 * Gofer SEO Admin
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Admin
 *
 * @since 1.0.0
 */
class Gofer_SEO_Admin {

	/**
	 * Admin Loader.
	 *
	 * @since 1.0.0
	 *
	 * @var Gofer_SEO_Admin_Loader $admin_loader
	 */
	public $admin_loader;

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
	 * @return Gofer_SEO_Admin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Gofer_SEO_Admin constructor.
	 *
	 * Initializes Core Operations.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( ! current_user_can( 'gofer_seo_access' ) ) {
			return;
		}

		// Initialize plugin.
		if ( current_user_can( 'gofer_seo_access' ) ) {
			add_action( 'plugins_loaded', array( $this, 'pre_load' ), 6 );
			add_action( 'plugins_loaded', array( $this, 'load' ), 9 );
		}

	}

	/**
	 * Pre-Load.
	 *
	 * @since 1.0.0
	 */
	public function pre_load() {
		$this->_requires();
	}

	/**
	 * Load Admin.
	 *
	 * @since 1.0.0
	 */
	public function load() {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		add_action( 'wp_enqueue_scripts', array( $this, 'front_enqueue_styles' ) );

		if ( is_admin() ) {
			add_filter( 'plugin_action_links_' . GOFER_SEO_PLUGIN_BASENAME, array( $this, 'add_action_links' ), 10, 4 );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );

			// add_action( 'admin_init', array( $this, 'review_plugin_notice' ) );

			add_action( 'admin_init', array( $this, 'check_php_version' ) );
			add_action( 'admin_init', array( $this, 'visibility_warning' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles_all' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'front_enqueue_styles' ) );

			if (
					$gofer_seo_options->options['enable_modules']['advanced'] &&
					$gofer_seo_options->options['modules']['advanced']['enable_unprotect_post_meta']
			) {
				add_filter( 'is_protected_meta', array( $this, 'unprotect_meta' ), 10, 3 );
			}
		} else {
			// Frontend only. DO NOT load in Admin side.
		}

		$this->admin_loader = new Gofer_SEO_Admin_Loader();
		$gofer_seo_notifications = Gofer_SEO_Notifications::get_instance();
	}

	/**
	 * Require Files.
	 *
	 * @since 1.0.0
	 */
	private function _requires() {
		// ADMIN.

		// Screens.
		require_once GOFER_SEO_DIR . 'includes/typesetters/class-typesetter-admin.php';
		require_once GOFER_SEO_DIR . 'admin/screens/class-screen.php';
		require_once GOFER_SEO_DIR . 'admin/screens/class-screen-page.php';
		require_once GOFER_SEO_DIR . 'admin/screens/class-screen-page-module.php';
		require_once GOFER_SEO_DIR . 'admin/screens/class-screen-page-module-general.php';
		require_once GOFER_SEO_DIR . 'admin/screens/class-screen-page-module-social-media.php';
		require_once GOFER_SEO_DIR . 'admin/screens/class-screen-page-module-sitemap.php';
		require_once GOFER_SEO_DIR . 'admin/screens/class-screen-page-module-schema-graph.php';
		require_once GOFER_SEO_DIR . 'admin/screens/class-screen-page-module-crawlers.php';
		require_once GOFER_SEO_DIR . 'admin/screens/class-screen-page-module-advanced.php';
		require_once GOFER_SEO_DIR . 'admin/screens/class-screen-page-module-debugger.php';
		require_once GOFER_SEO_DIR . 'admin/screens/class-screen-edit.php';
		require_once GOFER_SEO_DIR . 'admin/screens/class-screen-edit-post.php';
		require_once GOFER_SEO_DIR . 'admin/screens/class-screen-edit-term.php';
		require_once GOFER_SEO_DIR . 'admin/screens/class-screen-edit-user.php';

		// Module Page Screen.
		require_once GOFER_SEO_DIR . 'admin/screens/module-page/class-screen-module-page.php';

		// Post Editor Screen.
		require_once GOFER_SEO_DIR . 'admin/screens/post-edit/class-screen-post-editor.php';
		require_once GOFER_SEO_DIR . 'admin/screens/post-edit/class-screen-post-editor-general.php';
		require_once GOFER_SEO_DIR . 'admin/screens/post-edit/class-screen-post-editor-social-media.php';
		require_once GOFER_SEO_DIR . 'admin/screens/post-edit/class-screen-post-editor-sitemap.php';

		// Term Editor Screen.
		require_once GOFER_SEO_DIR . 'admin/screens/term-edit/class-screen-term-editor.php';
		require_once GOFER_SEO_DIR . 'admin/screens/term-edit/class-screen-term-editor-general.php';
		require_once GOFER_SEO_DIR . 'admin/screens/term-edit/class-screen-term-editor-social-media.php';
		require_once GOFER_SEO_DIR . 'admin/screens/term-edit/class-screen-term-editor-sitemap.php';

		// Term Editor Screen.
		require_once GOFER_SEO_DIR . 'admin/screens/user-edit/class-screen-user-editor.php';
		require_once GOFER_SEO_DIR . 'admin/screens/user-edit/class-screen-user-editor-general.php';
		require_once GOFER_SEO_DIR . 'admin/screens/user-edit/class-screen-user-editor-sitemap.php';

		// Admin (Screen) Loader.
		require_once GOFER_SEO_DIR . 'admin/class-admin-loader.php';

		// Partials.
		require_once GOFER_SEO_DIR . 'admin/partials/class-notifications.php';
		require_once GOFER_SEO_DIR . 'admin/partials/class-tooltips.php';
	}

	/**
	 * Add Action Links
	 *
	 * Adds additional links to the plugin on the admin Plugins page.
	 *
	 * @since 1.0.0
	 *
	 * @see `plugin_action_links_{$plugin_file}` hook.
	 * @link https://developer.wordpress.org/reference/hooks/plugin_action_links_plugin_file/
	 *
	 * @param string[] $actions     An array of plugin action links. By default this can include 'activate',
	 *                              'deactivate', and 'delete'. With Multisite active this can also include
	 *                              'network_active' and 'network_only' items.
	 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
	 * @param array    $plugin_data An array of plugin data. See `get_plugin_data()`.
	 * @param string   $context     The plugin context. By default this can include 'all', 'active', 'inactive',
	 *                              'recently_activated', 'upgrade', 'mustuse', 'dropins', and 'search'.
	 * @return array
	 */
	public function add_action_links( $actions, $plugin_file, $plugin_data, $context ) {
		if ( GOFER_SEO_PLUGIN_BASENAME !== $plugin_file ) {
			return $actions;
		} elseif ( ! is_array( $actions ) ) {
			return $actions;
		}

		$action_links           = array(
			'settings' => array(
				/* translators: This is an action link users can click to open the General Settings menu. */
				'label' => __( 'SEO Settings', 'gofer-seo' ),
				'url'   => get_admin_url( null, 'admin.php?page=gofer_seo' ),
			),
		);

		return $this->merge_action_links( $actions, $action_links, 'before' );
	}

	/**
	 * Plugin Row Meta
	 *
	 * @since 1.0.0
	 *
	 * @uses `plugin_row_meta` hook.
	 * @link https://developer.wordpress.org/reference/hooks/plugin_row_meta/
	 *
	 * @param string[] $plugin_meta An array of the plugin's metadata,
	 *                              including the version, author,
	 *                              author URI, and plugin URI.
	 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
	 * @param array    $plugin_data An array of plugin data.
	 * @param string   $status      Status of the plugin. Defaults are 'All', 'Active',
	 *                              'Inactive', 'Recently Activated', 'Upgrade', 'Must-Use',
	 *                              'Drop-ins', 'Search', 'Paused'.
	 * @return array
	 */
	public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
		if ( GOFER_SEO_PLUGIN_BASENAME !== $plugin_file ) {
			return $plugin_meta;
		}

		$action_links = array(
			'rate_plugin' => array(
				/* translators: This is an action link users can click to open a feature request/bug report on GitHub. */
				'label' => __( 'Rate this plugin.', 'gofer-seo' ),
				'url'   => 'https://wordpress.org/support/plugin/gofer-seo/reviews/?filter=5#new-post',
			),

		);

		return $this->merge_action_links( $plugin_meta, $action_links, 'after' );
	}

	/**
	 * Merge Action Links.
	 *
	 * @since 1.0.0
	 *
	 * @param string[][] $actions
	 * @param string[][] $action_links
	 * @param string $position
	 * @return array
	 */
	public function merge_action_links( $actions, $action_links = array(), $position = 'after' ) {
		foreach ( $action_links as $key => $value ) {
			$link = array(
				$key => '<a href="' . $value['url'] . '">' . $value['label'] . '</a>',
			);
			if ( 'after' === $position ) {
				$actions = array_merge( $actions, $link );
			} else {
				$actions = array_merge( $link, $actions );
			}
		}

		return $actions;
	}

	/**
	 * Check the current PHP version and display a notice if on unsupported PHP.
	 *
	 * @since 1.0.0
	 *
	 * @global Gofer_SEO_Notifications $gofer_seo_notifications
	 */
	function check_php_version() {
		$gofer_seo_notifications = Gofer_SEO_Notifications::get_instance();
		$gofer_seo_notifications->deactivate_notice( 'check_php_version' );

		// Display for PHP below 5.6
		if ( version_compare( PHP_VERSION, '5.4', '>=' ) ) {
			return;
		}

		// Display for admins only.
		if ( ! is_super_admin() ) {
			return;
		}

		// Display on Dashboard page only.
		if ( isset( $GLOBALS['pagenow'] ) && 'index.php' !== $GLOBALS['pagenow'] ) {
			return;
		}

		$gofer_seo_notifications->reset_notice( 'check_php_version' );
		$gofer_seo_notifications->activate_notice( 'check_php_version' );
	}

	/**
	 * Visibility Warning
	 *
	 * Checks if 'Search Engine Visibility' is enabled in Settings > Reading.
	 *
	 * @todo Change to earlier hook. Before `admin_enqueue` if possible.
	 *
	 * @since 1.0.0
	 *
	 * @see `self::constructor()` with 'all_admin_notices' Filter Hook
	 */
	function visibility_warning() {
		$gofer_seo_notifications = Gofer_SEO_Notifications::get_instance();
		if ( '0' === get_option( 'blog_public' ) ) {
			$gofer_seo_notifications->activate_notice( 'blog_public_disabled' );
		} elseif ( '1' === get_option( 'blog_public' ) ) {
			$gofer_seo_notifications->deactivate_notice( 'blog_public_disabled' );
		}
	}

	/**
	 * Review Plugin Notice
	 *
	 * Activates the review notice.
	 *
	 * @since 1.0.0
	 */
	public function review_plugin_notice() {
		// $gofer_seo_notifications = Gofer_SEO_Notifications::get_instance();
		// $gofer_seo_notifications->activate_notice( 'review_plugin' );
	}

	/**
	 * Admin Enqueue Styles All (Screens)
	 *
	 * Enqueue style on all admin screens.
	 *
	 * @since 1.0.0
	 *
	 * @param $hook_suffix
	 */
	public function admin_enqueue_styles_all( $hook_suffix ) {
		$file_ext = gofer_seo_is_min_enabled() ? 'min.css' : 'css';
		wp_enqueue_style(
			'gofer-seo-css',
			GOFER_SEO_URL . 'admin/css/gofer-seo.' . $file_ext,
			array(),
			GOFER_SEO_VERSION
		);
	}

	/**
	 * Enqueues stylesheets used on the frontend.
	 *
	 * @since 1.0.0
	 */
	function front_enqueue_styles() {
		if ( ! is_user_logged_in() ) {
			return;
		}
	}

	/**
	 * Enqueues stylesheets used in the admin area.
	 *
	 * @since 1.0.0
	 *
	 * @param   string  $hook_suffix
	 * @return  void
	 */
	function admin_enqueue_styles( $hook_suffix ) {
		if ( ! is_admin() ) {
			return;
		}
	}

	/**
	 * Unprotect Meta.
	 *
	 * @since 1.0.0
	 *
	 * @see `is_protected_meta` hook.
	 * @link https://developer.wordpress.org/reference/hooks/is_protected_meta/
	 *
	 * @param bool   $protected Whether the key is considered protected.
	 * @param string $meta_key  Metadata key.
	 * @param string $meta_type Type of object metadata is for. Accepts 'post', 'comment', 'term', 'user',
	 *                          or any other object type with an associated meta table.
	 * @return bool
	 */
	public function unprotect_meta( $protected, $meta_key, $meta_type ) {
		// TODO Change to _gofer_seo_
		if ( isset( $meta_key ) && ( substr( $meta_key, 0, 9 ) === '_gofer_seo_' ) ) {
			return false;
		}

		return $protected;
	}

}
