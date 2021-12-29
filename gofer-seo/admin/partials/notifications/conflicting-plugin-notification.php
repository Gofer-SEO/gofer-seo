<?php
/**
 * Conflicting Plugin Notice
 *
 * @package Gofer SEO
 * @subpackage Gofer_SEO_Notices
 */

/**
 * Notice - Conflicting Plugin.
 *
 * Returns the default values for our conflicting plugin notice.
 *
 * @since 1.0.0
 *
 * @return array
 */
function gofer_seo_conflicting_plugin_notice() {
	return array(
		'slug'           => 'conflicting_plugin',
		'delay_time'     => 0,
		'message'        => '',
		'target'         => 'user',
		'screens'        => array(),
		'class'          => 'notice-error',
		'action_options' => array(
			array(
				'time'    => 0,
				'link'    => '#',
				'new_tab' => false,
				'text'    => __( 'Deactivate plugins', 'gofer-seo' ),
				'dismiss' => false,
				'class'   => 'button-primary',
			),
			array(
				'time'    => 172800,  // 48H
				'text'    => 'Remind me later',
				'link'    => '',
				'dismiss' => false,
				'class'   => 'button-secondary',
			),
		),
	);
}
add_filter( 'gofer_seo_admin_notice-conflicting_plugin', 'gofer_seo_conflicting_plugin_notice' );
