<?php
/**
 * Plugin Review Notice
 *
 * @package Gofer SEO
 * @subpackage Gofer_SEO_Notices
 */

/**
 * Notice - Review Plugin.
 *
 * @since 1.0.0
 *
 * @return array Notice configuration.
 */
function gofer_seo_notice_plugin_review() {
	$message = sprintf(
		/* translators: %s with the plugin name. */
		__( 'Looks like you\'ve been using %s for awhile, and that\'s awesome! By helping with a 5-star review, it also helps to reach out to more people.', 'gofer-seo' ),
		GOFER_SEO_NAME
	);

	return array(
		'slug'           => 'review_plugin',
		'delay_time'     => 12 * DAY_IN_SECONDS,
		'message'        => $message,
		'class'          => 'notice-info',
		'target'         => 'user',
		'screens'        => array(),
		'action_options' => array(
			array(
				'time'    => 0,
				'text'    => __( 'Yes, absolutely!', 'gofer-seo' ),
				'link'    => 'https://wordpress.org/support/plugin/gofer-seo/reviews?rate=5#new-post',
				'dismiss' => false,
				'class'   => 'button-secondary',
			),
			array(
				'text'    => __( 'Maybe, give me a Week.', 'gofer-seo' ),
				'time'    => 7 * DAY_IN_SECONDS,
				'dismiss' => false,
				'class'   => 'button-secondary',
			),
			array(
				'time'    => 0,
				'text'    => __( 'Already did. Dismiss.', 'gofer-seo' ),
				'dismiss' => true,
				'class'   => 'button-secondary',
			),
		),
	);
}
add_filter( 'gofer_seo_admin_notice-review_plugin', 'gofer_seo_notice_plugin_review' );
