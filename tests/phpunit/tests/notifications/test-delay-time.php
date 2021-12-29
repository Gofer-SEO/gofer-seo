<?php
/**
 * PHPUnit Testing Gofer SEO Notice Delay Times;
 *
 * @package Gofer SEO
 * @subpackage Gofer_SEO_Notifications
 *
 * @group Gofer_SEO_Notifications
 * @group Admin
 * @group Notices
 */

/**
 * Gofer SEO Notices Testcase
 */
include_once GOFER_SEO_UNIT_TESTING_DIR . '/phpunit/includes/class-gofer-seo-notifications-testcase.php';

/**
 * Class Test_Gofer_SEO_Notifications
 *
 * @since 1.0.0
 *
 * @package Gofer SEO
 */
class Test_Gofer_SEO_Notifications_Delay_Time extends \Gofer_SEO_Notifications_TestCase {

	/**
	 * PHPUnit Fixture - setUp()
	 *
	 * @since 1.0.0
	 *
	 * @link https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/#shared-setup-between-related-tests
	 */
	public function setUp() {
		parent::setUp();
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
			'slug'           => 'notice_delay_delay_time',
			'delay_time'     => 2, // 1 Hour.
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
	 * Test Notice Delay Time
	 *
	 * Function: Displays Notice when the delayed time has been reached.
	 * Expected: Noticed doesn't render before the delay time, and when the delayed time is reach the notice will render.
	 * Actual: Currently works as expected.
	 * Reproduce: Have a notice inserted, and wait for X amount of time to pass.
	 *
	 * @since 1.0.0
	 */
	public function test_notice_delay_time() {
		$gofer_seo_notifications = Gofer_SEO_Notifications::get_instance();
		$this->add_notice();

		set_current_screen( 'dashboard' );

		ob_start();
		$gofer_seo_notifications->display_notice_default();
		$buffer = ob_get_contents();
		ob_end_clean();
		$this->assertEmpty( $buffer );

		sleep( 3 );

		ob_start();
		$gofer_seo_notifications->display_notice_default();
		$buffer = ob_get_contents();
		ob_end_clean();
		$this->assertNotEmpty( $buffer );
	}
}
