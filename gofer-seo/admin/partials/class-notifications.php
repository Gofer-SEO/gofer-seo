<?php
/**
 * Gofer_SEO Notice API: Gofer SEO Notice Class
 *
 * Handles adding, updating, and removing notices. Then handles activating or
 * deactivating those notices site-wide or user based.
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Notifications.
 *
 * @since 1.0.0
 */
class Gofer_SEO_Notifications {

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
	 * Collection of notices to display.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var array $notices {
	 *     @type array $slug {
	 *         -- Server Variables --
	 *         @type string $slug        Required. Notice unique ID.
	 *         @type int    $time_start  The time the notice was added to the object.
	 *         @type int    $time_set    Set when AJAX/Action_Option was last used to delay time. Primarily for PHPUnit tests.
	 *
	 *         -- Filter Function Variables --
	 *         @type int    $delay_time  Amount of time to begin showing message.
	 *         @type string $message     Content message to display in the container.
	 *         @type array  $action_option {
	 *         Show options for users to click on. Default: See self::action_option_defaults().
	 *             @type array {
	 *                 @type int     $time    Optional. The amount of time to delay. Zero immediately displays Default: 0.
	 *                 @type string  $text    Optional. Button/Link HTML text to display. Default: ''.
	 *                 @type string  $class   Optional. Class names to add to the link/button for styling. Default: ''.
	 *                 @type string  $link    Optional. The elements href source/link. Default: '#'.
	 *                 @type boolean $dismiss Optional. Variable for AJAX to dismiss showing a notice.
	 *             }
	 *         }
	 *         @type string $class       The class notice used by WP, or a custom CSS class.
	 *                                   Ex. notice-error, notice-warning, notice-success, notice-info.
	 *         @type string $target      Shows based on site-wide or user notice data.
	 *         @todo string $perms       Displays based on user-role/permissions.
	 *         @type array  $screens     Which screens to exclusively display the notice on. Default: array().
	 *                                   array()            = all,
	 *                                   array('gofer_seo') = $this->gofer_seo_screens,
	 *                                   array('CUSTOM')    = specific screen(s).
	 *     }
	 * }
	 */
	public $notices = array();

	/**
	 * List of notice slugs that are currently active.
	 * NOTE: Amount is reduced by 1 second in order to display at exactly X amount of time.
	 *
	 * @todo Change name to $display_times for consistency both conceptually and with usermeta structure.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var array $active_notices {
	 *     @type string|int $slug => $display_time Contains the current active notices
	 *                                             that are scheduled to be displayed.
	 * }
	 */
	public $active_notices = array();

	/**
	 * Dismissed Notices
	 *
	 * Stores notices that have been dismissed sitewide. Users are stored in usermeta data 'gofer_seo_notice_dismissed_{$slug}'.
	 *
	 * @since 1.0.0
	 *
	 * @var array $dismissed_notices {
	 *     @type boolean $notice_slug => $is_dismissed True if dismissed.
	 * }
	 */
	public $dismissed_notices = array();

	/**
	 * The default dismiss time. An anti-nag setting.
	 *
	 * @var int $default_dismiss_delay
	 */
	private $default_dismiss_delay = 315569260; // 10 years

	/**
	 * List of Screens used in Gofer SEO.
	 *
	 * @since 1.0.0
	 *
	 * @var array $gofer_seo_screens {
	 *     @type string Screen ID.
	 * }
	 */
	private $gofer_seo_screens = array();

	/**
	 * List of screens that should be excluded.
	 *
	 * @var array
	 *
	 * @since 1.0.0
	 */
	private $excluded_screens = array();

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
	 * @return Gofer_SEO_Notifications
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	// Used for unit-testing, and uses same syntax.
	// phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
	/**
	 * PHPUnit tearDown.
	 *
	 * Used to unset the singleton instance.
	 *
	 * @ignore
	 * @since 1.0.0
	 */
	public static function tearDown() {
		static::$instance = NULL;
	}
	// phpcs:enable

	/**
	 * Gofer_SEO_Notifications constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->_requires();
		$this->obj_load_options();

		if ( current_user_can( 'gofer_seo_access' ) ) {
			add_action( 'admin_init', array( $this, 'init' ) );
			add_action( 'current_screen', array( $this, 'admin_screen' ) );
		}
	}

	/**
	 * _Requires
	 *
	 * Internal use only. Additional files required.
	 *
	 * @since 1.0.0
	 */
	private function _requires() {
		$this->autoload_notice_files();
	}

	/**
	 * Autoload Notice Files
	 *
	 * @since 1.0.0
	 *
	 * @see DirectoryIterator class
	 * @link https://php.net/manual/en/class.directoryiterator.php
	 * @see StackOverflow for getting all filenamess in a directory.
	 * @link https://stackoverflow.com/a/25988433/1376780
	 */
	private function autoload_notice_files() {
		foreach ( new DirectoryIterator( GOFER_SEO_DIR . 'admin/partials/notifications/' ) as $file ) {
			$extension = pathinfo( $file->getFilename(), PATHINFO_EXTENSION );
			if ( $file->isFile() && 'php' === $extension ) {
				$filename = $file->getFilename();

				// Qualified file pattern; "*-notice.php".
				// Prevents any malicious files that may have spreaded.
				if ( array_search( 'notification', explode( '-', str_replace( '.php', '', $filename ) ), true ) ) {
					include_once GOFER_SEO_DIR . 'admin/partials/notifications/' . $filename;
				}
			}
		}
	}

	/**
	 * Early operations required by the plugin.
	 *
	 * AJAX requires being added early before screens have been loaded.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		add_action( 'wp_ajax_gofer_seo_notice', array( $this, 'ajax_notice_action' ) );
	}

	/**
	 * Setup/Init Admin Screen
	 *
	 * Adds the initial actions to WP based on the Admin Screen being loaded.
	 * The Plugin and Other Screens have separate methods that are used, and
	 * additional screens can be made exclusive/unique.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Screen $current_screen The current screen object being loaded.
	 */
	public function admin_screen( $current_screen ) {
		$gofer_seo_admin         = Gofer_SEO_Admin::get_instance();
		$this->gofer_seo_screens = $gofer_seo_admin->admin_loader->get_screen_ids();

		$this->deregister_scripts();
		if ( isset( $current_screen->id ) && in_array( $current_screen->id, $this->gofer_seo_screens, true ) ) {
			// Plugin Notice Content.
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'all_admin_notices', array( $this, 'display_notice_gofer_seo' ) );
		} elseif ( isset( $current_screen->id ) ) {
			// Default WP Notice.
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'all_admin_notices', array( $this, 'display_notice_default' ) );
		}
	}

	/**
	 * Load Object Options
	 *
	 * Gets the options for class object to set its variables to.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @see self::notices
	 * @see self::active_notices
	 */
	private function obj_load_options() {
		$notices_options = $this->obj_get_options();

		$this->notices           = $notices_options['notices'];
		$this->active_notices    = $notices_options['active_notices'];
		$this->dismissed_notices = $notices_options['dismissed_notices'];
	}

	/**
	 * Get Object Options
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return array
	 */
	private function obj_get_options() {
		$defaults = array(
			'notices'           => array(),
			'active_notices'    => array(),
			'dismissed_notices' => array(),
		);

		// Prevent old data from being loaded instead.
		// Some notices are instant notifications.
		wp_cache_delete( 'gofer_seo_notices', 'options' );
		$notices_options = get_option( 'gofer_seo_notices' );
		if ( false === $notices_options ) {
			return $defaults;
		}

		return wp_parse_args( $notices_options, $defaults );
	}

	/**
	 * Update Notice Options
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return boolean True if successful, using update_option() return value.
	 */
	private function obj_update_options() {
		$notices_options     = array(
			'notices'           => $this->notices,
			'active_notices'    => $this->active_notices,
			'dismissed_notices' => $this->dismissed_notices,
		);
		$old_notices_options = $this->obj_get_options();
		$notices_options     = wp_parse_args( $notices_options, $old_notices_options );

		// Prevent old data from being loaded instead.
		// Some notices are instant notifications.
		wp_cache_delete( 'gofer_seo_notices', 'options' );
		return update_option( 'gofer_seo_notices', $notices_options, false );
	}

	/**
	 * Notice Default Values
	 *
	 * Returns the default value for a variable to be used in self::notices[].
	 *
	 * @since 1.0.0
	 *
	 * @see Gofer_SEO_Notifications::notices Array variable that stores the collection of notices.
	 *
	 * @return array Notice variable in self::notices.
	 */
	public function notice_defaults() {
		return array_merge(
			$this->notice_defaults_server(),
			$this->notice_defaults_file()
		);
	}

	/**
	 * Notice Defaults Server
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function notice_defaults_server() {
		return array(
			'slug'       => '',
			'time_start' => time(),
			'time_set'   => time(),
		);
	}

	/**
	 * Notice Defaults File
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function notice_defaults_file() {
		return array(
			'slug'           => '',
			'delay_time'     => 0,
			'message'        => '',
			'action_options' => array(),
			'class'          => 'notice-info',
			'target'         => 'site',
			'screens'        => array(),
		);
	}

	/**
	 * Action Options Default Values
	 *
	 * Returns the default value for action_options in self::notices[$slug]['action_options'].
	 *
	 * @since 1.0.0
	 *
	 * @return array Action_Options variable in self::notices[$slug]['action_options'].
	 */
	public function action_options_defaults() {
		return array(
			'time'    => 0,
			'text'    => __( 'Dismiss', 'gofer-seo' ),
			'link'    => '#',
			'new_tab' => true,
			'dismiss' => true,
			'class'   => '',
		);
	}

	/**
	 * Add Notice
	 *
	 * Takes notice and adds it to object and saves to database.
	 *
	 * @since 1.0.0
	 *
	 * @param array $notice See self::notices for more info.
	 * @return boolean True on success.
	 */
	public function add_notice( $notice = array() ) {
		if ( empty( $notice['slug'] ) || isset( $this->notices[ $notice['slug'] ] ) ) {
			return false;
		}

		$this->notices[ $notice['slug'] ] = $this->prepare_notice( $notice );

		return true;
	}

	/**
	 * Prepare Insert/Undate Notice
	 *
	 * @since 1.0.0
	 *
	 * @param array $notice The notice to prepare with the database.
	 * @return array
	 */
	public function prepare_notice( $notice = array() ) {
		$notice_default = $this->notice_defaults_server();
		$notice         = wp_parse_args( $notice, $notice_default );

		$new_notice = array();
		foreach ( $notice_default as $key => $value ) {
			$new_notice[ $key ] = $notice[ $key ];
		}

		return $new_notice;
	}

	/**
	 * Used strictly for any notices that are deprecated/obsolete. To stop notices,
	 * use notice_deactivate().
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Unique notice slug.
	 * @return boolean True if successfully removed.
	 */
	public function remove_notice( $slug ) {
		if ( isset( $this->notices[ $slug ] ) ) {
			$this->deactivate_notice( $slug );
			unset( $this->notices[ $slug ] );
			$this->obj_update_options();
			return true;
		}

		return false;
	}

	/**
	 * Activate Notice
	 *
	 * Activates a notice, or Re-activates with a new display time. Used after
	 * updating a notice that requires a hard reset.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Notice slug.
	 * @return boolean
	 */
	public function activate_notice( $slug ) {
		if ( empty( $slug ) ) {
			return false;
		}
		$notice = $this->get_notice( $slug );
		if ( 'site' === $notice['target'] && isset( $this->active_notices[ $slug ] ) ) {
			return true;
		} elseif ( 'user' === $notice['target'] && get_user_meta( get_current_user_id(), 'gofer_seo_notice_display_time_' . $slug, true ) ) {
			return true;
		}

		if ( ! isset( $this->notices[ $slug ] ) ) {
			$this->add_notice( $notice );
		}

		$this->set_notice_delay( $slug, $notice['delay_time'] );

		$this->obj_update_options();

		return true;
	}

	/**
	 * Deactivate Notice
	 *
	 * Deactivates a notice set as active and completely removes it from the
	 * list of active notices. Used to prevent conflicting notices that may be
	 * active at any given point in time.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Notice slug.
	 * @return boolean
	 */
	public function deactivate_notice( $slug ) {
		if ( ! isset( $this->active_notices[ $slug ] ) ) {
			return false;
		} elseif ( ! isset( $this->notices[ $slug ] ) ) {
			return false;
		}

		delete_metadata(
			'user',
			0,
			'gofer_seo_notice_display_time_' . $slug,
			'',
			true
		);
		unset( $this->active_notices[ $slug ] );
		$this->obj_update_options();

		return true;
	}

	/**
	 * Reset Notice
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The notice's slug.
	 * @return bool
	 */
	public function reset_notice( $slug ) {
		if (
				empty( $slug ) ||
				(
						! isset( $this->notices[ $slug ] ) &&
						! get_user_meta( get_current_user_id(), 'gofer_seo_notice_display_time_' . $slug, true ) &&
						! get_user_meta( get_current_user_id(), 'gofer_seo_notice_dismissed_' . $slug, true )
				)
		) {
			return false;
		}

		$notice = $this->get_notice( $slug );

		unset( $this->active_notices[ $slug ] );
		unset( $this->dismissed_notices[ $slug ] );
		delete_metadata(
			'user',
			0,
			'gofer_seo_notice_time_set_' . $slug,
			'',
			true
		);
		delete_metadata(
			'user',
			0,
			'gofer_seo_notice_display_time_' . $slug,
			'',
			true
		);
		delete_metadata(
			'user',
			0,
			'gofer_seo_notice_dismissed_' . $slug,
			'',
			true
		);

		$this->set_notice_delay( $slug, $notice['delay_time'] );

		$this->obj_update_options();

		return true;
	}

	/**
	 * Set Notice Delay
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug       The notice's slug.
	 * @param int    $delay_time Amount of time to delay.
	 * @return boolean
	 */
	public function set_notice_delay( $slug, $delay_time ) {
		if ( empty( $slug ) ) {
			return false;
		}
		$time_set = time();

		// Display at exactly X time, not (X + 1) time.
		$display_time = $time_set + $delay_time - 1;
		$notice       = $this->get_notice( $slug );
		if ( 'user' === $notice['target'] ) {
			$current_user_id = get_current_user_id();

			update_user_meta( $current_user_id, 'gofer_seo_notice_time_set_' . $slug, $time_set );
			update_user_meta( $current_user_id, 'gofer_seo_notice_display_time_' . $slug, $display_time );
		}

		$this->notices[ $slug ]['time_set']   = $time_set;
		$this->notices[ $slug ]['time_start'] = $display_time;
		$this->active_notices[ $slug ]        = $display_time;

		return true;
	}

	/**
	 * Set Notice Dismiss
	 *
	 * @since 1.0.0
	 *
	 * @param string  $slug    The notice's slug.
	 * @param boolean $dismiss Sets to dismiss a notice.
	 */
	public function set_notice_dismiss( $slug, $dismiss ) {
		$notice = $this->get_notice( $slug );
		if ( 'site' === $notice['target'] ) {
			$this->dismissed_notices[ $slug ] = $dismiss;
		} elseif ( 'user' === $notice['target'] ) {
			$current_user_id = get_current_user_id();

			update_user_meta( $current_user_id, 'gofer_seo_notice_dismissed_' . $slug, $dismiss );
		}
	}

	/**
	 * Get Notice
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug The notice's slug.
	 * @return array
	 */
	public function get_notice( $slug ) {
		// Set defaults for notice.
		$rtn_notice = $this->notice_defaults();

		if ( isset( $this->notices[ $slug ] ) ) {
			// Get minimized (database) data.
			$rtn_notice = array_merge( $rtn_notice, $this->notices[ $slug ] );
		}

		/**
		 * Admin Notice {$slug}
		 *
		 * Applies the notice data values for a given notice slug.
		 * `gofer_seo_admin_notice-{$slug}` with the slug being the individual notice.
		 *
		 * @since 1.0.0
		 *
		 * @params array $notice_data See `\Gofer_SEO_Notices::$notices` for structural documentation.
		 */
		$notice_data = apply_filters( 'gofer_seo_admin_notice-' . $slug, array() ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

		if ( ! empty( $notice_data ) ) {
			$rtn_notice = array_merge( $rtn_notice, $notice_data );

			foreach ( $rtn_notice['action_options'] as &$action_option ) {
				// Set defaults for `$notice['action_options']`.
				$action_option = array_merge( $this->action_options_defaults(), $action_option );
			}
		}

		return $rtn_notice;
	}

	/*** DISPLAY Methods **************************************************/
	/**
	 * Deregister Scripts
	 *
	 * Initial Admin Screen action to remove Gofer SEO script(s) from all screens;
	 * which will be registered if executed on screen.
	 * NOTE: As of 3.0, most of it is default layout, styling, & scripting
	 * that is loaded on all pages. Which can later be different.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @see self::admin_screen()
	 */
	private function deregister_scripts() {
		wp_deregister_script( 'gofer-seo-notice-js' );
		wp_deregister_style( 'gofer-seo-notice-css' );
	}

	/**
	 * (Register) Enqueue Scripts
	 *
	 * Used to register, enqueue, and localize any JS data. Styles can later be added.
	 *
	 * @since 1.0.0
	 */
	public function admin_enqueue_scripts() {
		$file_ext_js  = gofer_seo_is_min_enabled() ? 'min.js' : 'js';
		$file_ext_css = gofer_seo_is_min_enabled() ? 'min.css' : 'css';
		// Register.
		wp_register_script(
			'gofer-seo-notice-js',
			GOFER_SEO_URL . 'admin/js/notice.' . $file_ext_js,
			array( 'jquery' ),
			GOFER_SEO_VERSION,
			true
		);

		// Localization.
		$notice_actions = array();
		foreach ( $this->active_notices as $notice_slug => $notice_display_time ) {
			$notice = $this->get_notice( $notice_slug );
			foreach ( $notice['action_options'] as $action_index => $action_arr ) {
				$notice_actions[ $notice_slug ][] = $action_index;
			}
		}

		$admin_notice_localize = array(
			'notice_nonce'   => wp_create_nonce( 'gofer_seo_ajax_notice' ),
			'notice_actions' => $notice_actions,
		);
		wp_localize_script( 'gofer-seo-notice-js', 'gofer_seo_notice_data', $admin_notice_localize );

		// Enqueue.
		wp_enqueue_script( 'gofer-seo-notice-js' );

		wp_enqueue_style(
			'gofer-seo-notice-css',
			GOFER_SEO_URL . 'admin/css/notice.' . $file_ext_css,
			false,
			GOFER_SEO_VERSION,
			false
		);
	}

	/**
	 * Display Notice as Default
	 *
	 * Method for default WP Admin notices.
	 * NOTE: display_notice_default() & display_notice_gofer_seo()
	 * have the same functionality, but serves as a future development concept.
	 *
	 * @since 1.0.0
	 *
	 * @uses GOFER_SEO_DIR . 'templates/admin/notifications/notification-default.php' Template for default notices.
	 *
	 * @return void
	 */
	public function display_notice_default() {
		$this->display_notice( 'default' );
	}

	/**
	 * Display Notice as Gofer_SEO Screens
	 *
	 * Method for Admin notices exclusive to Gofer SEO screens.
	 * NOTE: display_notice_default() & display_notice_gofer_seo()
	 * have the same functionality, but serves as a future development concept.
	 *
	 * @since 1.0.0
	 *
	 * @uses GOFER_SEO_DIR . 'admin/display/notification-gofer-seo.php' Template for notices.
	 *
	 * @return void
	 */
	public function display_notice_gofer_seo() {
		$this->display_notice( 'gofer-seo' );
	}

	/**
	 * Display Notice
	 *
	 * @since 1.0.0
	 *
	 * @param string $template Slug name for template.
	 */
	public function display_notice( $template ) {
		if ( ! wp_script_is( 'gofer-seo-notice-js', 'enqueued' ) || ! wp_style_is( 'gofer-seo-notice-css', 'enqueued' ) ) {
			return;
		} elseif ( 'default' !== $template && 'gofer-seo' !== $template ) {
			return;
		} elseif ( ! current_user_can( 'gofer_seo_access' ) ) {
			return;
		}

		$current_screen  = get_current_screen();
		$current_user_id = get_current_user_id();
		foreach ( $this->active_notices as $a_notice_slug => $a_notice_time_display ) {
			$notice_show = true;
			$notice      = $this->get_notice( $a_notice_slug );

			// If we have no message or static HTML, this is a bad notice.
			if ( empty( $notice['message'] ) && empty( $notice['html'] ) ) {
				$this->remove_notice( $a_notice_slug );
				continue;
			}

			// Screen Restriction.
			if ( ! empty( $notice['screens'] ) ) {

				if ( in_array( 'gofer_seo', $notice['screens'], true ) ) {
					unset( $notice['screens']['gofer_seo'] );
					$notice['screens'] = array_merge( $notice['screens'], $this->gofer_seo_screens );
				}

				if ( ! in_array( $current_screen->id, $notice['screens'], true ) ) {
					continue;
				}
			}

			if ( in_array( $current_screen->id, $this->excluded_screens, true ) ) {
				continue;
			}

			if ( isset( $this->dismissed_notices[ $a_notice_slug ] ) && $this->dismissed_notices[ $a_notice_slug ] ) {
				$notice_show = false;
			}

			// User Settings.
			if ( 'user' === $notice['target'] ) {
				$user_dismissed = get_user_meta( $current_user_id, 'gofer_seo_notice_dismissed_' . $a_notice_slug, true );
				if ( ! $user_dismissed ) {
					$user_notice_time_display = get_user_meta( $current_user_id, 'gofer_seo_notice_display_time_' . $a_notice_slug, true );
					if ( ! empty( $user_notice_time_display ) ) {
						$a_notice_time_display = intval( $user_notice_time_display );
					}
				} else {
					$notice_show = false;
				}
			}

			// Display/Render.
			$important_admin_notices = array(
				'notice-error',
				'notice-warning',
				'notice-do-nag',
			);
			if ( defined( 'DISABLE_NAG_NOTICES' ) && true === DISABLE_NAG_NOTICES && ( ! in_array( $notice['class'], $important_admin_notices, true ) ) ) {
				// Skip if `DISABLE_NAG_NOTICES` is implemented (as true).
				// Important notices, WP's CSS `notice-error` & `notice-warning`, are still rendered.
				continue;
			} elseif ( time() > $a_notice_time_display && $notice_show ) {
				$template_args = array(
					'notice' => $notice,
				);
				gofer_seo_do_template( 'admin/notifications/notification-' . $template . '.php', $template_args );
			}
		}
	}

	/**
	 * AJAX Notice Action
	 *
	 * Fires when a Action_Option is clicked and sent via AJAX. Also includes
	 * WP Default Dismiss (rendered as a clickable button on upper-right).
	 *
	 * @since 1.0.0
	 *
	 * @see GOFER_SEO_DIR . 'admin/js/notice.js'
	 */
	public function ajax_notice_action() {
		check_ajax_referer( 'gofer_seo_ajax_notice' );
		if ( ! current_user_can( 'gofer_seo_access' ) ) {
			/* translators: %1$s: WordPress User Role slug. */
			wp_send_json_error( sprintf( __( 'User doesn\'t have `%1$s` capabilities.', 'gofer-seo' ), 'gofer_seo_access' ) );
		}
		// Notice (Slug) => (Action_Options) Index.
		$notice_slug  = null;
		$action_index = null;
		if ( isset( $_POST['notice_slug'] ) ) {
			$notice_slug = filter_input( INPUT_POST, 'notice_slug', FILTER_SANITIZE_STRING );

			// When PHPUnit is unable to use filter_input.
			if ( defined( 'GOFER_SEO_UNIT_TESTING' ) && null === $notice_slug && ! empty( $_POST['notice_slug'] ) ) {
				$notice_slug = sanitize_html_class( wp_unslash( $_POST['notice_slug'] ) );
			}
		}
		if ( isset( $_POST['action_index'] ) ) {
			$action_index = filter_input( INPUT_POST, 'action_index', FILTER_SANITIZE_STRING );

			// When PHPUnit is unable to use filter_input.
			if ( defined( 'GOFER_SEO_UNIT_TESTING' ) && null === $action_index && ( ! empty( $_POST['action_index'] ) || 0 === $_POST['action_index'] ) ) {
				$action_index = sanitize_html_class( wp_unslash( $_POST['action_index'] ) );
			}
		}
		// phpcs:enable
		if ( empty( $notice_slug ) ) {
			/* Translators: Displays the hardcoded slug that is missing. */
			wp_send_json_error( sprintf( __( 'Missing values from `%s`.', 'gofer-seo' ), 'notice_slug' ) );
		} elseif ( empty( $action_index ) && 0 !== (int) $action_index ) {
			/* Translators: Displays the hardcoded action index that is missing. */
			wp_send_json_error( sprintf( __( 'Missing values from `%s`.', 'gofer-seo' ), 'action_index' ) );
		}

		$action_options            = $this->action_options_defaults();
		$action_options['time']    = $this->default_dismiss_delay;
		$action_options['dismiss'] = false;

		$notice = $this->get_notice( $notice_slug );

		if ( isset( $notice['action_options'][ $action_index ] ) ) {
			$action_options = array_merge( $action_options, $notice['action_options'][ $action_index ] );
		}

		if ( $action_options['time'] ) {
			$this->set_notice_delay( $notice_slug, $action_options['time'] );
		}
		if ( $action_options['dismiss'] ) {
			$this->set_notice_dismiss( $notice_slug, $action_options['dismiss'] );
		}

		$this->obj_update_options();
		wp_send_json_success( __( 'Notice updated successfully.', 'gofer-seo' ) );
	}

}
