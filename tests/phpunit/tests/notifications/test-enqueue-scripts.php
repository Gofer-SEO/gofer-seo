<?php
/**
 * PHPUnit Testing Notice Enqueue Scripts
 *
 * @package Gofer SEO
 * @subpackage Gofer_SEO_Notifications
 *
 * @group Gofer_SEO_Notifications
 * @group Admin
 * @group Notices
 */

/**
 * Notices Testcase
 */
include_once GOFER_SEO_UNIT_TESTING_DIR . '/phpunit/includes/class-gofer-seo-notifications-testcase.php';

/**
 * Class Test_Gofer_SEO_Notifications_AdminEnqueueScripts
 *
 * @since 1.0.0
 */
class Test_Gofer_SEO_Notifications_AdminEnqueueScripts extends Gofer_SEO_Notifications_TestCase {

	/**
	 * Set Up
	 */
	public function setUp() {
		parent::setUp();

		global $gofer_seo_module_general;
		$gofer_seo_notifications = Gofer_SEO_Notifications::get_instance();
		if ( null === $gofer_seo_notifications ) {
			$gofer_seo_notifications = Gofer_SEO_Notifications::get_instance();
		}
		if ( null === $gofer_seo_module_general ) {
			$gofer_seo_module_general = new Gofer_SEO_Module_General();
		}
	}
	/**
	 * Mock Notice
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function mock_notice() {
		$rtn_notice               = parent::mock_notice();
		$rtn_notice['delay_time'] = 0;
		return $rtn_notice;
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
		add_filter( 'gofer_seo_admin_notice-' . $notice['slug'], array( $this, 'mock_notice' ) );
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
}
