<?php
/**
 * Module - Advanced
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Module_Advanced.
 *
 * @since 1.0.0
 */
class Gofer_SEO_Module_Advanced extends Gofer_SEO_Module {

	/**
	 * Gofer_SEO_Module_Advanced constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Load.
	 *
	 * @since 1.0.0
	 */
	public function load() {

	}

	/**
	 * Initialize.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		if ( $gofer_seo_options->options['modules']['advanced']['enable_stop_heartbeat'] ) {
			$this->stop_heartbeat();
		}
	}

	/**
	 * Stop (WP) Heartbeat.
	 *
	 * This will prevent WP's Heartbeat JavaScript file from enqueueing
	 * and making calls to the website in intervals.
	 *
	 * @since 1.0.0
	 */
	public function stop_heartbeat() {
		wp_deregister_script('heartbeat');
	}
}
