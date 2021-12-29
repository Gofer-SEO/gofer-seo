<?php
/**
 * PHPUnit Testing Gofer SEO Notice AJAX
 *
 * @package Gofer SEO
 * @subpackage Gofer_SEO_Notifications
 *
 * @group Gofer_SEO_Notifications
 * @group Admin
 * @group Notices
 */

/**
 * Class Test_Gofer_SEO_Notifications_AJAX
 *
 * @since 1.0.0
 */
class Test_Gofer_SEO_Notifications_AJAX extends WP_Ajax_UnitTestCase {

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
	 * PHPUnit Fixture - setUp()
	 *
	 * @since 1.0.0
	 *
	 * @link https://make.wordpress.org/core/handbook/testing/automated-testing/writing-phpunit-tests/#shared-setup-between-related-tests
	 */
	public function setUp() {
		parent::setUp();

		wp_set_current_user( 1 );

		$gofer_seo_notifications = Gofer_SEO_Notifications::get_instance();
		if ( isset( $gofer_seo_notifications ) && ! empty( $gofer_seo_notifications ) ) {
			$this->old_gofer_seo_notices = $gofer_seo_notifications;
		}
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

		$gofer_seo_notifications = Gofer_SEO_Notifications::get_instance();
		if ( isset( $this->old_gofer_seo_notices ) && ! empty( $this->old_gofer_seo_notices ) ) {
			$gofer_seo_notifications            = $this->old_gofer_seo_notices;
			$GLOBALS['gofer_seo_notices'] = $this->old_gofer_seo_notices;
		}
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
	 * Mock Single Notice
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function mock_notice() {
		return array(
			'slug'           => 'notice_delay_ajax',
			'delay_time'     => 0,
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
					'time'    => 30,
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
	 * Mock Single Notice
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function mock_notice_target_user() {
		return array(
			'slug'           => 'notice_slug_user',
			'delay_time'     => 0,
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
					'time'    => 2,
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

		if ( empty( $notice ) ) {
			$notice = $this->mock_notice();
		}

		// Insert Successful and activated.
		add_filter( 'gofer_seo_admin_notice-notice_delay_ajax', array( $this, 'mock_notice' ) );
		add_filter( 'gofer_seo_admin_notice-notice_slug_user', array( $this, 'mock_notice_target_user' ) );
		$this->assertTrue( $gofer_seo_notifications->activate_notice( $notice['slug'] ) );
		$this->assertTrue( in_array( $notice['slug'], $notice, true ) );

		$this->assertTrue( isset( $gofer_seo_notifications->active_notices[ $notice['slug'] ] ) );
		$this->assertNotNull( $gofer_seo_notifications->active_notices[ $notice['slug'] ] );
	}

	/**
	 * Test Notice Missing wp_nonce Error
	 *
	 * Function: Returns an error contained in JSON status & message. Normally - Sets the active_notices display time, or dismisses the notification.
	 * Expected: Operation is not completed and an error is returned within a JSON string.
	 * Actual: Currently works as expected.
	 * Reproduce: Dev hardcoded  an invalid/missing wp_nonce in `Gofer_SEO_Notifications::admin_enqueue_scripts()`.
	 *
	 * @since 1.0.0
	 */
	public function test_notice_missing_wp_nonce_error() {
		$this->add_notice();

		$ex = null;
		try {
			$this->_handleAjax( 'gofer_seo_notice' );
		} catch ( WPAjaxDieStopException $ex ) {
			// We expected this, do nothing.
			// Fail with missing wp_nonce.
		}

		// Check if an exception was thrown.
		$this->assertInstanceOf( 'WPAjaxDieStopException', $ex, 'Not an instance of WPAjaxDieStopException.' );
		$this->assertTrue( isset( $ex ) );

		// Error message is -1 for failure.
		$this->assertEquals( '-1', $ex->getMessage() );
	}

	/**
	 * Test Notice Missing notice_slug Error
	 *
	 * Function: Returns an error contained in JSON status & message. Normally - Sets the active_notices display time, or dismisses the notification.
	 * Expected: Operation is not completed and an error is returned within a JSON string.
	 * Actual: Currently works as expected.
	 * Reproduce: Dev hardcoded the notice template with an invalid notice_slug in an element.
	 *
	 * @since 1.0.0
	 */
	public function test_notice_missing_notice_slug_error() {
		$this->add_notice();

		// Create the nonce in the POST superglobal.
		$_POST['_wpnonce'] = wp_create_nonce( 'gofer_seo_ajax_notice' );

		$ex = null;
		try {
			$this->_handleAjax( 'gofer_seo_notice' );
		} catch ( WPAjaxDieStopException $ex ) {
			// We did not expected this, do nothing & check assert instance of `WPAjaxDieContinueException`.
		} catch ( WPAjaxDieContinueException $ex ) {
			// We expected this, do nothing.
		}

		// Check if an exception was thrown.
		$this->assertInstanceOf( 'WPAjaxDieContinueException', $ex, 'Not an instance of WPAjaxDieContinueException.' );
		$this->assertTrue( isset( $ex ) );

		// No error message on expected failure.
		$this->assertEquals( '', $ex->getMessage() );

		// Check data returned on expected fail.
		$response = json_decode( $this->_last_response );
		$this->assertInternalType( 'object', $response );
		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertFalse( $response->success );
		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertEquals( 'Missing values from `notice_slug`.', $response->data );
	}

	/**
	 * Test Notice Missing Action Index Error
	 *
	 * Function: Returns an error contained in JSON status & message. Normally - Sets the active_notices display time, or dismisses the notification.
	 * Expected: Operation is not completed and an error is returned within a JSON string.
	 * Actual: Currently works as expected.
	 * Reproduce: Dev hardcoded the notice template with an invalid action_index in the element ID.
	 *
	 * @since 1.0.0
	 */
	public function test_notice_missing_action_index_error() {
		$notice = $this->mock_notice();
		$this->add_notice();

		$_POST['_wpnonce']    = wp_create_nonce( 'gofer_seo_ajax_notice' );
		$_POST['notice_slug'] = $notice['slug'];

		$ex = null;
		try {
			$this->_handleAjax( 'gofer_seo_notice' );
		} catch ( WPAjaxDieStopException $ex ) {
			// We did not expected this, do nothing & check assert instance of `WPAjaxDieContinueException`.
		} catch ( WPAjaxDieContinueException $ex ) {
			// We expected this, do nothing.
		}

		// Check if an exception was thrown.
		$this->assertInstanceOf( 'WPAjaxDieContinueException', $ex, 'Not an instance of WPAjaxDieContinueException.' );
		$this->assertTrue( isset( $ex ) );

		// No error message on expected failure.
		$this->assertEquals( '', $ex->getMessage() );

		// Check data returned on expected fail.
		$response = json_decode( $this->_last_response );
		$this->assertInternalType( 'object', $response );
		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertFalse( $response->success );
		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertEquals( 'Missing values from `action_index`.', $response->data );
	}

	/**
	 * Test Action Delay Time
	 *
	 * Function: Sets the active_notices display time, or dismisses the notification.
	 * Expected: No/zero delay or false dismiss makes no changes, 1+ delay changes the active_notice display time and time_set,
	 *           and true dismiss will remove notice from active_notices but will still remain in Gofer_SEO_Notifications:notices.
	 * Actual: Currently works as expected.
	 * Reproduce: Have a notice added to the database and rendered (after set delay_time). Within the admin notice, there
	 *            would be buttons/links (aka action_options). Clicking on any of them will initiate the AJAX event.
	 *
	 * @since 1.0.0
	 */
	public function test_notice_action_delay_time() {
		$gofer_seo_notifications = Gofer_SEO_Notifications::get_instance();
		$notice = $this->mock_notice();
		$this->add_notice();
		$notice = $gofer_seo_notifications->get_notice( $notice['slug'] );

		/*
		 * Action_Options 0 - No delay, no dismiss.
		 */

		// Create the nonce in the POST superglobal.
		$_POST['_wpnonce'] = wp_create_nonce( 'gofer_seo_ajax_notice' );

		// The slug from `$this->mock_notice()`.
		$_POST['notice_slug'] = $notice['slug'];

		// Key value from action_option array index.
		$_POST['action_index'] = 0;

		try {
			$this->_handleAjax( 'gofer_seo_notice' );
		} catch ( WPAjaxDieStopException $ex ) {
			// We did not expected this, do nothing & check on assertion.
		} catch ( WPAjaxDieContinueException $ex ) {
			// We did not expected this, do nothing & check on assertion.
		}

		$response = json_decode( $this->_last_response );
		$this->assertInternalType( 'object', $response );
		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertTrue( $response->success );
		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertEquals( 'Notice updated successfully.', $response->data );

		// Check if notice is still active.
		$this->assertArrayHasKey( $notice['slug'], $gofer_seo_notifications->notices, 'AJAX Notice should still be added.' );
		$this->assertArrayHasKey( $notice['slug'], $gofer_seo_notifications->active_notices, 'AJAX Notice should still be active.' );
		// Check delay time.
		$expected_time = $gofer_seo_notifications->notices[ $notice['slug'] ]['time_set'] + $notice['action_options']['0']['time'];
		// Add 1 to compensate for exact time display.
		$actual_time = $gofer_seo_notifications->active_notices[ $notice['slug'] ] + 1;

		$this->assertGreaterThanOrEqual( $expected_time, $actual_time );
		$this->assertLessThanOrEqual( ( $expected_time + 1 ), $actual_time );

		/*
		 * Action_Options 1 - 2 sec delay, no dismiss.
		 */

		$this->_last_response = '';
		// Key value from action_option array index.
		$_POST['action_index'] = 1;

		try {
			$this->_handleAjax( 'gofer_seo_notice' );
		} catch ( WPAjaxDieStopException $ex ) {
			// We did not expected this, do nothing & check on assertion.
		} catch ( WPAjaxDieContinueException $ex ) {
			// We did not expected this, do nothing & check on assertion.
		}

		$response = json_decode( $this->_last_response );
		$this->assertInternalType( 'object', $response );
		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertTrue( $response->success );
		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertEquals( 'Notice updated successfully.', $response->data );

		// Check if notice is still active.
		$this->assertArrayHasKey( $notice['slug'], $gofer_seo_notifications->notices, 'AJAX Notice should still be added.' );
		$this->assertArrayHasKey( $notice['slug'], $gofer_seo_notifications->active_notices, 'AJAX Notice should still be active.' );

		// Check delay time.
		$expected_time = $gofer_seo_notifications->notices[ $notice['slug'] ]['time_set'] + $notice['action_options']['1']['time'];
		// Add 1 to compensate for exact time display.
		$actual_time = $gofer_seo_notifications->active_notices[ $notice['slug'] ] + 1;

		$this->assertGreaterThanOrEqual( $expected_time, $actual_time, 'Expected: ' . $expected_time . ' Actual: ' . $actual_time );
		$this->assertLessThanOrEqual( ( $expected_time + 1 ), $actual_time, 'Expected: ' . $expected_time . ' Actual: ' . $actual_time );

		/*
		 * Action_Options 2 - NA delay, dismiss.
		 */

		$this->_last_response = '';
		// Key value from action_option array index.
		$_POST['action_index'] = 2;

		try {
			$this->_handleAjax( 'gofer_seo_notice' );
		} catch ( WPAjaxDieStopException $ex ) {
			// We did not expected this, do nothing & check on assertion.
		} catch ( WPAjaxDieContinueException $ex ) {
			// We did not expected this, do nothing & check on assertion.
		}

		$response = json_decode( $this->_last_response );
		$this->assertInternalType( 'object', $response );
		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertTrue( $response->success );
		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertEquals( 'Notice updated successfully.', $response->data );

		// Check if notice is still active.
		$this->assertArrayHasKey( $notice['slug'], $gofer_seo_notifications->notices, 'AJAX Notice should still be added.' );
		$this->assertArrayHasKey( $notice['slug'], $gofer_seo_notifications->dismissed, 'AJAX Notice should not be active still.' );
		// No delay time to check.
	}

	/**
	 * Test Dismiss Sitewide for All Users
	 *
	 * Function: Dismisses the notice for all admins/gofer_seo_access.
	 * Expected: When dismissed, the notice should not display for the use dismissing and all other users.
	 * Actual: Currently works as expected.
	 * Reproduce: Dev create a notice without `target` being set. then navigate to wp-admin. Repeat with alternate user.
	 *
	 * @since 1.0.0
	 */
	public function test_dismiss_sitewide_all_users() {

		$gofer_seo_notifications = Gofer_SEO_Notifications::get_instance();
		$notice = $this->mock_notice();
		$this->add_notice( $notice );

		// Create the nonce in the POST superglobal.
		$_POST['_wpnonce']     = wp_create_nonce( 'gofer_seo_ajax_notice' );
		$_POST['notice_slug']  = $notice['slug'];
		$_POST['action_index'] = 2; // Within mock_notice, it's the action_option (index) that contains the dismissal.

		set_current_screen( 'dashboard' );
		$gofer_seo_notifications->admin_enqueue_scripts();

		ob_start();
		$gofer_seo_notifications->display_notice_default();
		$buffer = ob_get_contents();
		ob_end_clean();

		$this->assertNotEmpty( $buffer );

		try {
			$this->_handleAjax( 'gofer_seo_notice' );
		} catch ( WPAjaxDieStopException $ex ) {
			// We did not expected this, do nothing & check on assertion.
		} catch ( WPAjaxDieContinueException $ex ) {
			// We did not expected this, do nothing & check on assertion.
		}

		ob_start();
		$gofer_seo_notifications->display_notice_default();
		$buffer = ob_get_contents();
		ob_end_clean();

		$this->assertEmpty( $buffer );

		// Switch user.
		$user_id = $this->factory()->user->create(
			array(
				'user_login'    => 'user_ajax',
				'user_nicename' => 'userajax',
				'user_pass'     => 'password',
				'first_name'    => 'John',
				'last_name'     => 'Doe',
				'display_name'  => 'John Doe',
				'user_email'    => 'placeholder@email.com',
				'user_url'      => 'http://foobar.com',
				'role'          => 'administrator',
				'nickname'      => 'Johnny',
				'description'   => 'I am a WordPress user.',
			)
		);
		wp_set_current_user( $user_id );

		ob_start();
		$gofer_seo_notifications->display_notice_default();
		$buffer = ob_get_contents();
		ob_end_clean();

		$this->assertEmpty( $buffer );
	}

	/**
	 * Test Dismiss Sitewide for All Users
	 *
	 * Function: Dismisses the notice for admin dismissing the notice, but other users the notice is able to display still.
	 * Expected: When dismissed, the notice should not display for the use dismissing but doesn't effect other users.
	 * Actual: Currently works as expected.
	 * Reproduce: Dev create a notice with `target` being set to `user`. then navigate to wp-admin. Repeat with alternate user.
	 *
	 * @since 1.0.0
	 */
	public function test_dismiss_user_single_users() {
		$gofer_seo_notifications = Gofer_SEO_Notifications::get_instance();
		$notice = $this->mock_notice_target_user();
		$this->add_notice( $notice );

		// Create the nonce in the POST superglobal.
		$_POST['_wpnonce']     = wp_create_nonce( 'gofer_seo_ajax_notice' );
		$_POST['notice_slug']  = $notice['slug'];
		$_POST['action_index'] = 2; // Within mock_notice, it's the action_option (index) that contains the dismissal.

		set_current_screen( 'dashboard' );
		$gofer_seo_notifications->admin_enqueue_scripts();

		ob_start();
		$gofer_seo_notifications->display_notice_default();
		$buffer = ob_get_contents();
		ob_end_clean();

		$this->assertNotEmpty( $buffer );

		try {
			$this->_handleAjax( 'gofer_seo_notice' );
		} catch ( WPAjaxDieStopException $ex ) {
			// We did not expected this, do nothing & check on assertion.
		} catch ( WPAjaxDieContinueException $ex ) {
			// We did not expected this, do nothing & check on assertion.
		}

		ob_start();
		$gofer_seo_notifications->display_notice_default();
		$buffer = ob_get_contents();
		ob_end_clean();

		$this->assertEmpty( $buffer );

		// Switch user.
		$user_id = $this->factory()->user->create(
			array(
				'user_login'    => 'user_ajax',
				'user_nicename' => 'userajax',
				'user_pass'     => 'password',
				'first_name'    => 'John',
				'last_name'     => 'Doe',
				'display_name'  => 'John Doe',
				'user_email'    => 'placeholder@email.com',
				'user_url'      => 'http://foobar.com',
				'role'          => 'administrator',
				'nickname'      => 'Johnny',
				'description'   => 'I am a WordPress user.',
			)
		);
		wp_set_current_user( $user_id );

		ob_start();
		$gofer_seo_notifications->display_notice_default();
		$buffer = ob_get_contents();
		ob_end_clean();

		$this->assertNotEmpty( $buffer );
	}
}
