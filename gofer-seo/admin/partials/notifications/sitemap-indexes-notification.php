<?php
/**
 * Sitemap Index Notice
 *
 * @package Gofer SEO
 * @subpackage Gofer_SEO_Notices
 */

/**
 * Notice - Sitemap Indexes
 *
 * @since 1.0.0
 *
 * @return array
 */
function gofer_seo_notifications_sitemap_indexes() {
	return array(
		'slug'           => 'sitemap_max_warning',
		'delay_time'     => 0,
		'message'        => __( 'Notice: To avoid problems with your XML Sitemap, we strongly recommend you set the Maximum Posts per Sitemap Page to 1,000.', 'gofer-seo' ),
		'class'          => 'notice-warning',
		'target'         => 'user',
		'screens'        => array(),
		'action_options' => array(
			array(
				'time'    => 0,
				'text'    => __( 'Update Sitemap Settings', 'gofer-seo' ),
				'link'    => esc_url( get_admin_url( null, 'admin.php?page=gofer_seo_module_sitemap.php' ) ),
				'dismiss' => false,
				'class'   => 'button-primary',
			),
			array(
				'time'    => 86400, // 24 hours.
				'text'    => __( 'Remind me later', 'gofer-seo' ),
				'link'    => '',
				'dismiss' => false,
				'class'   => 'button-secondary',
			),

		),
	);
}
add_filter( 'gofer_seo_admin_notice-sitemap_max_warning', 'gofer_seo_notifications_sitemap_indexes' );
