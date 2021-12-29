<?php
/**
 * Check PHP Version Notice
 *
 * @package Gofer SEO
 * @subpackage Gofer_SEO_Notices
 */

/**
 * Notice - Check PHP Version
 *
 * @since 1.0.0
 *
 * @return array Notice configuration.
 */
function gofer_seo_notice_check_php_version() {
	$message = sprintf(
		/* translators: %s with the plugin name. */
		__( 'Your site is running an outdated version of PHP that is no longer supported and may cause issues with %s.', 'gofer-seo' ),
		GOFER_SEO_NAME
	);
	return array(
		'slug'        => 'check_php_version',
		'delay_time'  => 0,
		'message'     => $message,
		'target'      => 'user',
		'screens'     => array(),
		'class'       => 'notice-error',
		'dismissible' => false,
	);
}
add_filter( 'gofer_seo_admin_notice-check_php_version', 'gofer_seo_notice_check_php_version' );
