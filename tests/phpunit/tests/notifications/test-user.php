<?php
/**
 * PHPUnit Testing Gofer SEO Notices User Perms/Restrictions
 *
 * @package Gofer SEO
 * @subpackage Gofer_SEO_Notifications
 *
 * @group Gofer_SEO_Notifications
 * @group Admin
 * @group Notices
 */

/**
 * Test for a User Notices.
 *
 * - Should not show to other users.
 * - Should be handled seperately ( delayed/dismissed seperately ).
 */
include_once GOFER_SEO_UNIT_TESTING_DIR . '/phpunit/includes/class-gofer-seo-notifications-testcase.php';
/**
 * Class Test_Gofer_SEO_Notifications
 *
 * @since 1.0.0
 *
 * @package Gofer SEO
 */
class Test_Gofer_SEO_Notifications_User extends Gofer_SEO_Notifications_TestCase {

	/**
	 * PHPUnit Fixture - setUp()
	 *
	 * @since 1.0.0
	 *
	 * @link https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/#shared-setup-between-related-tests
	 */

	public function setUp() {
		parent::setUp();

		set_current_screen( 'dashboard' );
	}

	/**
	 * PHPUnit Fixture - tearDown()
	 *
	 * @since 1.0.0
	 *
	 * @link https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/#shared-setup-between-related-tests
	 */
	public function tearDown() {
		parent::tearDown();

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
			'slug'           => 'notice_slug_user',
			'delay_time'     => 0, // 1 Hour.
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
			'target'         => 'user',
			'screens'        => array(),
		);
	}

	/**
	 * Test Enqueue Scripts on Screens
	 *
	 * Override and skip.
	 *
	 * @since 1.0.0
	 *
	 * @dataProvider data_screens
	 */
	public function test_enqueue_scripts_on_screens( $screen_id, $url, $dir ) {
		$this->markTestIncomplete( 'Skip' );
	}

	/**
	 * Test for notices showing to only admins, or gofer_seo_access perms.
	 *
	 * @since 1.0.0
	 *
	 * @dataProvider data_user_roles
	 */
	public function test_notice_admin_perms( $role, $expect_display ) {
		global $gofer_seo_notifications_test;
		$this->add_notice();

		$user_id = $this->factory()->user->create(
			array(
				'user_login'    => 'user_' . $role,
				'user_nicename' => 'user' . $role,
				'user_pass'     => 'password',
				'first_name'    => 'John',
				'last_name'     => 'Doe',
				'display_name'  => 'John Doe',
				'user_email'    => 'placeholder@email.com',
				'user_url'      => 'http://example.com',
				'role'          => $role,
				'nickname'      => 'Johnny',
				'description'   => 'I am a WordPress user.',
			)
		);

		wp_set_current_user( $user_id );
		set_current_screen( 'dashboard' );

		// Test User Perms.
		$user_can = current_user_can( 'gofer_seo_access' );
		if ( $expect_display ) {
			$this->assertTrue( $user_can );
		} else {
			$this->assertFalse( $user_can );
		}

		// After construction, check hooks added only for users with `gofer_seo_access`.
		$gofer_seo_notifications_test = Gofer_SEO_Notifications::get_instance();

		if ( $expect_display ) {
			$this->assertTrue( has_action( 'admin_init', array( $gofer_seo_notifications_test, 'init' ) ) ? true : false );
			$this->assertTrue( has_action( 'current_screen', array( $gofer_seo_notifications_test, 'admin_screen' ) ) ? true : false );
		} else {
			$this->assertFalse( has_action( 'admin_init', array( $gofer_seo_notifications_test, 'init' ) ) ? true : false );
			$this->assertFalse( has_action( 'current_screen', array( $gofer_seo_notifications_test, 'admin_screen' ) ) ? true : false );
		}

		// After `current_screen` action hook, check for hooks added.
		set_current_screen( 'dashboard' );

		if ( $expect_display ) {
			$this->assertTrue( has_action( 'admin_enqueue_scripts', array( $gofer_seo_notifications_test, 'admin_enqueue_scripts' ) ) ? true : false );
			$this->assertTrue( has_action( 'all_admin_notices', array( $gofer_seo_notifications_test, 'display_notice_default' ) ) ? true : false );
		} else {
			$this->assertFalse( has_action( 'admin_enqueue_scripts', array( $gofer_seo_notifications_test, 'admin_enqueue_scripts' ) ) ? true : false );
			$this->assertFalse( has_action( 'all_admin_notices', array( $gofer_seo_notifications_test, 'display_notice_default' ) ) ? true : false );
		}

		ob_start();
		$gofer_seo_notifications_test->display_notice_default();
		$buffer = ob_get_contents();
		ob_end_clean();

		if ( $expect_display ) {
			$this->assertNotEmpty( $buffer );
		} else {
			$this->assertEmpty( $buffer );
		}
	}

	/**
	 * Data User Roles
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function data_user_roles() {
		return array(
			array(
				'role'           => 'administrator',
				'expect_display' => true,
			),
			array(
				'role'           => 'editor',
				'expect_display' => false,
			),
			array(
				'role'           => 'author',
				'expect_display' => false,
			),
			array(
				'role'           => 'contributor',
				'expect_display' => false,
			),
			array(
				'role'           => 'subscriber',
				'expect_display' => false,
			),
		);
	}

}
