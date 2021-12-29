<?php
/**
 * Class Gofer_SEO_Notifications_TestCase
 *
 * @package Gofer SEO
 * @subpackage Gofer_SEO_Notifications
 * @since 1.0.0
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
 * Class Test Notice - Blog Visibility
 *
 * @since 1.0.0
 *
 * @package Gofer SEO
 */
class Test_Notice_BlogVisibility extends Gofer_SEO_Notifications_TestCase {

	/**
	 * Mock Single Notice
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function mock_notice() {
		return gofer_seo_notice_blog_visibility();
	}
}
