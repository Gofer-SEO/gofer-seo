<?php
/**
 * Gofer SEO Module: Base class
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Module.
 *
 * Module base class.
 *
 * @since 1.0.0
 */
class Gofer_SEO_Module {

	/**
	 * Gofer_SEO_Module constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load' ), 4 );
		add_action( 'init', array( $this, 'init' ), 3 );
	}

	/**
	 * Load.
	 *
	 * @since 1.0.0
	 */
	public function load() {}

	/**
	 * Initialize Module.
	 *
	 * Mainly used for adding action/filter hooks.
	 * There may be some function/method calls, but avoid adding code with operations/processes.
	 *
	 * @since 1.0.0
	 */
	public function init() {}

}
