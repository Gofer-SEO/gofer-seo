<?php
/**
 * Gofer SEO Notices Testcase
 *
 * @package Gofer SEO
 * @subpackage Gofer_SEO_Notifications
 *
 * @group Gofer_SEO_Notifications
 * @group Admin
 * @group Notices
 */

/**
 * Class Gofer_SEO_Notifications_TestCase
 *
 * @since 1.0.0
 */
class Gofer_SEO_Notifications_TestCase extends WP_UnitTestCase {

	/**
	 * Old Gofer SEO Notices
	 *
	 * @var null $old_gofer_seo_notices
	 */
	public $old_gofer_seo_notices = null;

	/**
	 * Old Gofer SEO Notices Options
	 *
	 * @var $old_gofer_seo_notices_options
	 */
	public $old_gofer_seo_notices_options;

	/**
	 * Gofer_SEO_Notifications_TestCase constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param null $name
	 * @param array $data
	 * @param string $data_name
	 */
	public function __construct( $name = null, array $data = array(), $data_name = '' ) {
		if ( ! defined( 'WP_DEVELOP_DIR' ) ) {
			$this->define_wp_develop_dir();
		}

		parent::__construct( $name, $data, $data_name );
	}

	/**
	 * Define WP_DEVELOP_DIR
	 *
	 * @since 1.0.0
	 */
	public function define_wp_develop_dir() {
		if ( defined( 'WP_DEVELOP_DIR' ) ) {
			return;
		}

		global $_tests_dir;
		global $config_file_path;

		self::assertNotEmpty( $_tests_dir );

		$wp_develop_dir = '';
		if ( ! empty( $_tests_dir ) ) {
			$wp_develop_dir = $_tests_dir;
			$wp_develop_dir = str_replace( '/test/phpunit', '', $wp_develop_dir );
			$wp_develop_dir = str_replace( '\test\phpunit', '', $wp_develop_dir );
		} elseif ( ! empty( $config_file_path ) ) {
			$wp_develop_dir = $config_file_path;
			$wp_develop_dir = str_replace( '/wp-tests-config.php', '', $wp_develop_dir );
			$wp_develop_dir = str_replace( '\wp-tests-config.php', '', $wp_develop_dir );
		}

		if ( ! empty( $wp_develop_dir ) ) {
			define( 'WP_DEVELOP_DIR', $wp_develop_dir );
		}
	}

	/**
	 * PHPUnit Fixture - setUp()
	 *
	 * @since 1.0.0
	 *
	 * @link https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/#shared-setup-between-related-tests
	 */
	public function setUp() {
		parent::setUp();

		//require_once GOFER_SEO_DIR . 'admin/partials/class-notifications.php';

		wp_set_current_user( 1 );

//		$gofer_seo_notifications = Gofer_SEO_Notifications::get_instance();
//		if ( isset( $gofer_seo_notifications ) && ! empty( $gofer_seo_notifications ) ) {
//			$this->old_gofer_seo_notices = $gofer_seo_notifications;
//		}
		$this->old_gofer_seo_notices_options = get_option( 'gofer_seo_notices' );

		$this->clean_gofer_seo_notices();
	}

	/**
	 * PHPUnit Fixture - tearDown()
	 *
	 * @since 1.0.0
	 *
	 * @link https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/#shared-setup-between-related-tests
	 */
	public function tearDown() {
		$this->clean_gofer_seo_notices();

//		$gofer_seo_notifications = Gofer_SEO_Notifications::get_instance();
//		if ( isset( $this->old_gofer_seo_notices ) && ! empty( $this->old_gofer_seo_notices ) ) {
//			$gofer_seo_notifications            = $this->old_gofer_seo_notices;
//			$GLOBALS['gofer_seo_notices'] = $this->old_gofer_seo_notices;
//		}

		$gofer_seo_notifications = Gofer_SEO_Notifications::get_instance();
		Gofer_SEO_Notifications::tearDown();

		if ( $this->old_gofer_seo_notices_options ) {
			update_option( 'gofer_seo_notices', $this->old_gofer_seo_notices_options );
		}

		parent::tearDown();
	}

	/**
	 * Clean Options Gofer SEO Notices
	 *
	 * @since 1.0.0
	 *
	 * @return boolean True if deleted, and false if it doesn't exist.
	 */
	public function clean_gofer_seo_notices() {
		$gofer_seo_notifications = Gofer_SEO_Notifications::get_instance();
		if ( isset( $gofer_seo_notifications ) && ! empty( $gofer_seo_notifications ) ) {
			$gofer_seo_notifications = null;
			unset( $GLOBALS['gofer_seo_notices'] );
		}

		return delete_option( 'gofer_seo_notices' );
	}

	/**
	 * Clean Gofer SEO Notices
	 *
	 * @since 1.0.0
	 *
	 * @param string $notice_slug Target notice to delete.
	 * @return boolean True if deleted.
	 */
	public function clean_gofer_seo_notice( $notice_slug ) {
		$notices_options = get_option( 'gofer_seo_notices' );
		if ( false === $notices_options ) {
			return false;
		} elseif ( ! isset( $notices_options['notices'][ $notice_slug ] ) || ! isset( $notices_options['active_notices'][ $notice_slug ] ) ) {
			return false;
		}

		unset( $notices_options['notices'][ $notice_slug ] );
		unset( $notices_options['active_notices'][ $notice_slug ] );

		return true;
	}

	/**
	 * Validate Global Gofer_SEO_Notifications object.
	 *
	 * @since 1.0.0
	 *
	 * @param Gofer_SEO_Notifications $gofer_seo_notifications The current object to test for.
	 */
	protected function validate_class_gofer_seo_notices( $gofer_seo_notifications ) {
		$this->assertInstanceOf( 'Gofer_SEO_Notifications', $gofer_seo_notifications, 'Not an instance of Gofer_SEO_Notifications.' );

		$class_attrs = array(
			'notices'               => 'array',
			'active_notices'        => 'array',
			'default_dismiss_delay' => 'int',
			'gofer_seo_screens'     => 'array',
		);

		// Loop through each variable, and check if isset and value type (type-case).
		foreach ( $class_attrs as $attr_name => $attr_type ) {
			$this->assertObjectHasAttribute( $attr_name, $gofer_seo_notifications, 'Variable is not set.' );
			$this->assertAttributeInternalType( $attr_type, $attr_name, $gofer_seo_notifications, 'Error with Type casting.' );
			if ( 'notices' === $attr_name ) {
				$this->validate_attr_notices( $gofer_seo_notifications->$attr_name );
			}
		}
	}

	/**
	 * Validates Gofer_SEO_Notifications::notices
	 *
	 * Checks to see if variables are correctly set.
	 *
	 * @since 1.0.0
	 *
	 * @param array $notices Class variable `Gofer_SEO_Notifications::notices`.
	 */
	protected function validate_attr_notices( $notices ) {
		foreach ( $notices as $notice ) {
			$this->validate_attr_notice( $notice );
		}
	}

	/**
	 * Validates notice in Gofer_SEO_Notifications::notices
	 *
	 * Checks to see if the array variables are correctly set.
	 *
	 * @since 1.0.0
	 *
	 * @param array $notice Class variable `Gofer_SEO_Notifications::notices`.
	 */
	protected function validate_attr_notice( $notice ) {
		$notices_attrs = array(
			'slug'       => 'string',
			'time_set'   => 'int',
			'time_start' => 'int',
		);

		foreach ( $notices_attrs as $attr_name => $attr_type ) {
			$this->assertArrayHasKey( $attr_name, $notice, 'Index/Key not found in Notice Array.' );
			$this->assertInternalType( $attr_type, $notice[ $attr_name ], 'Invalid value type (' . $attr_type . ') in ' . $attr_name );
		}
	}

	/**
	 * Validates notice in Gofer_SEO_Notifications::notices
	 *
	 * Checks to see if the array variables are correctly set.
	 *
	 * @since 1.0.0
	 *
	 * @param array $notice Class variable `Gofer_SEO_Notifications::notices`.
	 */
	protected function validate_notice( $notice ) {
		$notices_attrs = array(
			'slug'           => 'string',
			'delay_time'     => 'int',
			'message'        => 'string',
			'action_options' => 'array',
			'class'          => 'string',
			'target'         => 'string',
			'screens'        => 'array',
			'time_set'       => 'int',
			'time_start'     => 'int',
		);

		$action_option_attrs = array(
			'time'    => 'int',
			'text'    => 'string',
			'class'   => 'string',
			'link'    => 'string',
			'dismiss' => 'boolean',
		);

		foreach ( $notices_attrs as $attr_name => $attr_type ) {
			$this->assertArrayHasKey( $attr_name, $notice, 'Index/Key not found in Notice Array.' );
			$this->assertInternalType( $attr_type, $notice[ $attr_name ], 'Invalid value type (' . $attr_type . ') in ' . $attr_name );

			if ( 'action_option' === $attr_name ) {
				foreach ( $action_option_attrs as $action_attr_name => $action_attr_type ) {
					$this->assertArrayHasKey( $action_attr_name, $notice[ $attr_name ], 'Index/Key not found.' );
					$this->assertInternalType( $action_attr_type, $notice[ $attr_name ][ $action_attr_name ], 'Invalid value type.' );
				}
			}
		}
	}

	/**
	 * Mock Single Notice
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function mock_notice() {
		return array(
			'slug'           => 'notice_slug_1',
			'delay_time'     => 3600, // 1 Hour.
			'message'        => __( 'Admin Sample Message.', 'gofer-seo' ),
			'action_options' => array(
				array(
					'time'    => 0,
					'text'    => __( 'Link and close', 'gofer-seo' ),
					'link'    => 'https://wordpress.org/support/plugin/gofer-seo',
					'dismiss' => false,
					'class'   => '',
				),
				array(
					'text'    => 'Delay',
					'time'    => 432000,
					'dismiss' => false,
					'class'   => '',
				),
				array(
					'time'    => 0,
					'text'    => 'Dismiss',
					'dismiss' => true,
					'class'   => '',
				),
			),
			'target'         => 'site',
			'screens'        => array(),
		);
	}

	/**
	 * Add Notice
	 *
	 * Adds and validates the a (child test) notice being tested.
	 *
	 * @since 1.0.0
	 *
	 * @param array $notice Value from `$gofer_seo_notifications`.
	 */
	protected function add_notice( $notice = array() ) {
		$gofer_seo_notifications = Gofer_SEO_Notifications::get_instance();
		if ( null === $gofer_seo_notifications ) {
			$gofer_seo_notifications = Gofer_SEO_Notifications::get_instance();
		}
		$this->validate_class_gofer_seo_notices( $gofer_seo_notifications );
		if ( empty( $notice ) ) {
			$notice = $this->mock_notice();
		}

		// Insert Successful and activated.
		add_filter( 'gofer_seo_admin_notice-' . $notice['slug'], array( $this, 'mock_notice' ) );
		$this->assertTrue( $gofer_seo_notifications->activate_notice( $notice['slug'] ) );
		$this->assertTrue( in_array( $notice['slug'], $notice, true ) );

		$this->assertTrue( isset( $gofer_seo_notifications->active_notices[ $notice['slug'] ] ) );
		$this->assertNotNull( $gofer_seo_notifications->active_notices[ $notice['slug'] ] );

		// Validates the global $gofer_seo_notifications instance and variable types.
		$this->validate_class_gofer_seo_notices( $gofer_seo_notifications );
	}

	/**
	 * Add Notice
	 *
	 * Function: Inserts, and Updates, a single notice into wp_options.
	 * Expected: If no notice exists, it shouldn't be operational, and new notices should insert instead of update. Then
	 *           should be able to update without effecting the active notices.
	 * Actual: As expected; no current issue.
	 * Result: Inserts and Updates successfully to the database (wp_options).
	 *
	 * @since 1.0.0
	 *
	 * @param array $notice Single notice to add to object/database.
	 */
	public function test_add_notice( $notice = array() ) {
		$gofer_seo_notifications = Gofer_SEO_Notifications::get_instance();
		if ( null === $gofer_seo_notifications ) {
			$gofer_seo_notifications = Gofer_SEO_Notifications::get_instance();
		}
		if ( empty( $notice ) ) {
			$notice = $this->mock_notice();
		}

		// Slug should be set.
		$this->assertTrue( in_array( $notice['slug'], $notice, true ) );
		$this->assertFalse( empty( $notice['slug'] ) );

		// Shouldn't exist yet.
		$this->assertFalse( $gofer_seo_notifications->deactivate_notice( $notice['slug'] ) );
		$this->assertFalse( $gofer_seo_notifications->reset_notice( $notice['slug'] ) );

		// Expexted fail - Cannot load notice.
		$this->assertFalse( $gofer_seo_notifications->activate_notice( '' ) );

		// Insert Successful and activated.
		add_filter( 'gofer_seo_admin_notice-' . $notice['slug'], array( $this, 'mock_notice' ) );
		$this->assertTrue( $gofer_seo_notifications->activate_notice( $notice['slug'] ) );
		$this->assertTrue( isset( $gofer_seo_notifications->active_notices[ $notice['slug'] ] ) );

		// Deactivate.
		$this->assertTrue( $gofer_seo_notifications->deactivate_notice( $notice['slug'] ) );
		$this->assertFalse( isset( $gofer_seo_notifications->active_notices[ $notice['slug'] ] ) );
		$this->assertFalse( $gofer_seo_notifications->deactivate_notice( $notice['slug'] ) );
		$this->assertFalse( isset( $gofer_seo_notifications->active_notices[ $notice['slug'] ] ) );

		// Notice should still exist.
		$this->assertTrue( isset( $gofer_seo_notifications->notices[ $notice['slug'] ] ) );

		// Activate.
		$this->assertTrue( $gofer_seo_notifications->activate_notice( $notice['slug'] ) );
		$this->assertTrue( isset( $gofer_seo_notifications->active_notices[ $notice['slug'] ] ) );

		// Remove.
		$this->assertTrue( $gofer_seo_notifications->remove_notice( $notice['slug'] ) );
	}

	/**
	 * Test enqueue scripts on screens.
	 *
	 * Function: Enqueue Scripts and Styles with the WP Enqueue hook.
	 * Expected: Registered and enqueue scripts on target screens; provided by data_screens.
	 * Actual: As expected; no current issue.
	 * Result: Scripts are ready to be printed via enqueue.
	 *
	 * * should not enqueue if before delayed amount of time.
	 * * -notices with screen restrictions should be true only on set screens
	 * * (Test Render) Should not display content if script doesn't enqueue; also should send a Debug notice.
	 *
	 * @since 1.0.0
	 *
	 * @dataProvider data_screens
	 *
	 * @param string $screen_id
	 * @param string $url
	 * @param string $dir
	 */
	public function test_enqueue_scripts_on_screens( $screen_id, $url, $dir ) {

		$gofer_seo_notifications = Gofer_SEO_Notifications::get_instance();
		if ( null === $gofer_seo_notifications ) {
			$gofer_seo_notifications = Gofer_SEO_Notifications::get_instance();
		}
		$this->validate_class_gofer_seo_notices( $gofer_seo_notifications );

		// Should be empty.
		$this->assertTrue( empty( $gofer_seo_notifications->active_notices ) );

		$notice = $this->mock_notice();

		// Insert Successful and activated.
		add_filter( 'gofer_seo_admin_notice-notice_slug_1', array( $this, 'mock_notice' ) );
		$this->assertTrue( $gofer_seo_notifications->activate_notice( $notice['slug'] ) );
		$this->assertTrue( in_array( $notice['slug'], $notice, true ) );

		$this->assertTrue( isset( $gofer_seo_notifications->active_notices[ $notice['slug'] ] ) );
		$this->assertNotNull( $gofer_seo_notifications->active_notices[ $notice['slug'] ] );

		$this->validate_class_gofer_seo_notices( $gofer_seo_notifications );

		wp_deregister_script( 'gofer-seo-notice-js' );
		wp_deregister_style( 'gofer-seo-notice-css' );
		$this->assertFalse( wp_script_is( 'gofer-seo-notice-js', 'registered' ), 'Screen: ' . $screen_id );

		set_current_screen( $screen_id );
		$this->go_to( $url );

		$gofer_seo_notifications = Gofer_SEO_Notifications::get_instance();

		set_current_screen( $screen_id );

		$this->assertFalse( wp_script_is( 'gofer-seo-notice-js', 'registered' ), 'Screen: ' . $screen_id );

		do_action( 'admin_enqueue_scripts' );

		$this->assertTrue( wp_script_is( 'gofer-seo-notice-js', 'registered' ), 'Screen: ' . $screen_id );
		$this->assertTrue( wp_script_is( 'gofer-seo-notice-js', 'enqueued' ) );
	}

	/**
	 * DataProvider for Screens
	 *
	 * TODO Create a restricted screens array to test for false.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function data_screens() {
		$notice = $this->mock_notice();

		$screens = array();
		if ( in_array( 'gofer_seo', $notice['screens'] ) ) {
			$screens = $this->data_screens_gofer_seo();
		} elseif ( ! empty( $notice['screens'] ) ) {
			$all_screens = array_merge( $this->data_screens_wp(), $this->data_screens_gofer_seo() );

			foreach ( $notice['screens'] as $n_screen ) {
				foreach ( $all_screens as $screen ) {
					if ( $n_screen === $screen['screen_id'] ) {
						$screens[] = $screen;
					}
				}
			}
		}

		if ( empty( $screens ) ) {
			$screens = array_merge( $this->data_screens_wp(), $this->data_screens_gofer_seo() );
		}

		return $screens;
	}

	/**
	 * Data (Provider) for Default/WP Screens
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function data_screens_wp() {
		return array(
			array(
				'screen_id' => 'dashboard',
				'url'       => site_url() . '/wp-admin/index.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/index.php',
			),
			array(
				'screen_id' => 'update-core',
				'url'       => site_url() . '/wp-admin/update-core.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/update-core.php',
			),

			array(
				'screen_id' => 'edit-post',
				'url'       => site_url() . '/wp-admin/edit.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/edit.php',
			),
			array(
				'screen_id' => 'post',
				'url'       => site_url() . '/wp-admin/post-new.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/post-new.php',
			),
			/*
			array(
				'screen_id' => 'post',
				'url'       => site_url() . '/wp-admin/post.php?post=###&action=edit',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/post.php?post=###&action=edit',
			),
			*/
			array(
				'screen_id' => 'edit-category',
				'url'       => site_url() . '/wp-admin/edit-tags.php?taxonomy=category',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/edit-tags.php?taxonomy=category',
			),
			/*
			array(
				'screen_id' => 'edit-category',
				'url'       => site_url() . '/wp-admin/edit-tags.php?action=edit&taxonomy=category&tag_ID=###&post_type=post',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/edit-tags.php?action=edit&taxonomy=category&tag_ID=###&post_type=post',
			),
			*/
			array(
				'screen_id' => 'edit-post_tag',
				'url'       => site_url() . '/wp-admin/edit-tags.php?taxonomy=post_tag',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/edit-tags.php?taxonomy=post_tag',
			),
			/*
			array(
				'screen_id' => 'edit-post_tag',
				'url'       => site_url() . '/wp-admin/edit-tags.php?action=edit&taxonomy=post_tag&tag_ID=###&post_type=post',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/edit-tags.php?action=edit&taxonomy=post_tag&tag_ID=###&post_type=post',
			),
			*/
			// Custom Post Types.
			// Custom Taxonomies.
			array(
				'screen_id' => 'upload',
				'url'       => site_url() . '/wp-admin/upload.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/upload.php',
			),
			array(
				'screen_id' => 'media',
				'url'       => site_url() . '/wp-admin/media-new.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/media-new.php',
			),
			/*
			array(
				'screen_id' => 'attachment',
				'url'       => site_url() . '/wp-admin/post.php?post=###&action=edit',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/post.php?post=###&action=edit',
			),
			*/
			array(
				'screen_id' => 'edit-page',
				'url'       => site_url() . '/wp-admin/edit.php?post_type=page',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/edit.php?post_type=page',
			),
			array(
				'screen_id' => 'page',
				'url'       => site_url() . '/wp-admin/post-new.php?post_type=page',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/post-new.php?post_type=page',
			),
			/*
			array(
				'screen_id' => 'page',
				'url'       => site_url() . '/wp-admin/post.php?post=###&action=edit',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/post.php?post=###&action=edit',
			),
			*/
			array(
				'screen_id' => 'edit-comments',
				'url'       => site_url() . '/wp-admin/edit-comments.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/edit-comments.php',
			),
			/*
			array(
				'screen_id' => 'comment',
				'url'       => site_url() . '/wp-admin/comment.php?action=editcomment&c=###',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/comment.php?action=editcomment&c=###',
			),
			*/
			array(
				'screen_id' => 'themes',
				'url'       => site_url() . '/wp-admin/themes.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/themes.php',
			),
			array(
				'screen_id' => 'widgets',
				'url'       => site_url() . '/wp-admin/widgets.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/widgets.php',
			),
			array(
				'screen_id' => 'nav-menus',
				'url'       => site_url() . '/wp-admin/nav-menus.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/nav-menus.php',
			),
			array(
				'screen_id' => 'theme-editor',
				'url'       => site_url() . '/wp-admin/theme-editor.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/theme-editor.php',
			),
			/*
			array(
				'screen_id' => 'appearance_page_{page}',
				'url'       => site_url() . '/wp-admin/themes.php?page={page}',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/themes.php?page={page}',
			),
			*/
			array(
				'screen_id' => 'plugins',
				'url'       => site_url() . '/wp-admin/plugins.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/plugins.php',
			),
			array(
				'screen_id' => 'plugin-install',
				'url'       => site_url() . '/wp-admin/plugin-install.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/plugin-install.php',
			),
			array(
				'screen_id' => 'plugin-editor',
				'url'       => site_url() . '/wp-admin/plugin-editor.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/plugin-editor.php',
			),

			array(
				'screen_id' => 'users',
				'url'       => site_url() . '/wp-admin/users.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/users.php',
			),
			array(
				'screen_id' => 'user-new',
				'url'       => site_url() . '/wp-admin/user-new.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/user-new.php',
			),
			/*
			array(
				'screen_id' => 'user-edit',
				'url'       => site_url() . '/wp-admin/user-edit.php?user_id=###',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/user-edit.php?user_id=###',
			),
			*/
			array(
				'screen_id' => 'profile',
				'url'       => site_url() . '/wp-admin/profile.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/profile.php',
			),

			array(
				'screen_id' => 'tools',
				'url'       => site_url() . '/wp-admin/tools.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/tools.php',
			),
			array(
				'screen_id' => 'import',
				'url'       => site_url() . '/wp-admin/import.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/import.php',
			),
			array(
				'screen_id' => 'export',
				'url'       => site_url() . '/wp-admin/export.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/export.php',
			),

			array(
				'screen_id' => 'options-general',
				'url'       => site_url() . '/wp-admin/options-general.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/options-general.php',
			),
			array(
				'screen_id' => 'options-writing',
				'url'       => site_url() . '/wp-admin/options-writing.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/options-writing.php',
			),
			array(
				'screen_id' => 'options-reading',
				'url'       => site_url() . '/wp-admin/options-reading.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/options-reading.php',
			),
			array(
				'screen_id' => 'options-discussion',
				'url'       => site_url() . '/wp-admin/options-discussion.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/options-discussion.php',
			),
			array(
				'screen_id' => 'options-media',
				'url'       => site_url() . '/wp-admin/options-media.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/options-media.php',
			),
			array(
				'screen_id' => 'options-permalink',
				'url'       => site_url() . '/wp-admin/options-permalink.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/options-permalink.php',
			),

		);
	}

	/**
	 * Data (Provider) for Gofer SEO Screens
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function data_screens_gofer_seo() {
		return array(
			array(
				'screen_id' => 'toplevel_page_gofer_seo_module_general',
				'url'       => site_url() . '/wp-admin/admin.php?page=gofer_seo',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/admin.php?page=gofer_seo',
			),
			array(
				'screen_id' => 'gofer_seo_module_performance',
				'url'       => site_url() . '/wp-admin/admin.php?page=gofer_seo_module_performance.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/admin.php?page=gofer_seo_module_performance.php',
			),
			array(
				'screen_id' => 'gofer_seo_module_sitemap',
				'url'       => site_url() . '/wp-admin/admin.php?page=gofer_seo_module_sitemap.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/admin.php?page=gofer_seo_module_sitemap.php',
			),
			array(
				'screen_id' => 'gofer_seo_module_open_graph',
				'url'       => site_url() . '/wp-admin/admin.php?page=gofer_seo_module_open_graph',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/admin.php?page=gofer_seo_module_open_graph',
			),
			array(
				'screen_id' => 'gofer_seo_module_robots_generator',
				'url'       => site_url() . '/wp-admin/admin.php?page=gofer_seo_module_robots_generator',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/admin.php?page=gofer_seo_module_robots_generator',
			),
			array(
				'screen_id' => 'gofer_seo_module_file_editor',
				'url'       => site_url() . '/wp-admin/admin.php?page=gofer_seo_module_file_editor.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/admin.php?page=gofer_seo_module_file_editor.php',
			),
			array(
				'screen_id' => 'gofer_seo_module_importer_exporter',
				'url'       => site_url() . '/wp-admin/admin.php?page=gofer_seo_module_importer_exporter.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/admin.php?page=gofer_seo_module_importer_exporter.php',
			),
			array(
				'screen_id' => 'gofer_seo_module_bad_robots',
				'url'       => site_url() . '/wp-admin/admin.php?page=gofer_seo_module_bad_robots.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/admin.php?page=gofer_seo_module_bad_robots.php',
			),
			array(
				'screen_id' => 'gofer_seo_module_feature_manager',
				'url'       => site_url() . '/wp-admin/admin.php?page=gofer_seo_module_feature_manager.php',
				'dir'       => WP_DEVELOP_DIR . '/src/wp-admin/admin.php?page=gofer_seo_module_feature_manager.php',
			),
		);

	}
}
