<?php
/**
 * Blog Visibility Notice
 *
 * @since 1.0.0
 * @package Gofer SEO
 */

/**
 * Notice - Blog Visibility
 *
 * Displays when blog disables search engines from indexing.
 *
 * @since 1.0.0
 *
 * @return array Notice configuration.
 */
function gofer_seo_notice_blog_visibility() {
	$text_link = '<a href="' . admin_url( 'options-reading.php' ) . '">' . __( 'Reading Settings', 'gofer-seo' ) . '</a>';

	return array(
		'slug'           => 'blog_public_disabled',
		'delay_time'     => 0,
		/* translators: %s is a placeholder, which means that it should not be translated. It will be replaced with the name of the plugin, Gofer SEO. "Settings > Reading" refers to the "Reading" submenu in WordPress Core. */
		'message'        => sprintf( __( 'Warning: %s has detected that you are blocking access to search engines. You can change this in Settings > Reading if this was unintended.', 'gofer-seo' ), GOFER_SEO_NAME ),
		'class'          => 'notice-error',
		'target'         => 'site',
		'screens'        => array(),
		'action_options' => array(
			array(
				'time'    => 0,
				'text'    => __( 'Update Reading Settings', 'gofer-seo' ),
				'link'    => admin_url( 'options-reading.php' ),
				'dismiss' => false,
				'class'   => 'button-primary',
			),
			array(
				'time'    => 604800,
				'text'    => __( 'Remind me later', 'gofer-seo' ),
				'link'    => '',
				'dismiss' => false,
				'class'   => 'button-secondary',
			),
		),
	);
}

/**
 * @uses `gofer_seo_admin_notice-{slug}`
 */
add_filter( 'gofer_seo_admin_notice-blog_public_disabled', 'gofer_seo_notice_blog_visibility' );
