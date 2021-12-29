<?php
/**
 * Gofer SEO Compatibility: WooCommerce - General Functions
 *
 * @package Gofer SEO
 */

/**
 * Is WooCommerce Active.
 *
 * @since 1.0.0
 *
 * @return bool
 */
function gofer_seo_is_woocommerce_active() {
	return class_exists( 'WooCommerce' );
}
