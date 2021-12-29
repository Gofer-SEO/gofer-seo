<?php
/**
 * Module - Schema Graph
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Module_Schema_Graph.
 *
 * @since 1.0.0
 */
class Gofer_SEO_Module_Schema_Graph extends Gofer_SEO_Module {

	/**
	 * Gofer_SEO_Module_Schema_Graph constructor.
	 *
	 * @since 1.0.0
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
		parent::load();

		add_action( 'gofer_seo_wp_head', array( $this, 'gofer_seo_head' ), 3 );
	}

	/**
	 * Initialize Module.
	 *
	 * Mainly used for adding action/filter hooks.
	 * There may be some function/method calls, but avoid adding code with operations/processes.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		parent::init();

		if ( ! is_admin() ) {
			// Add Gofer SEO's output to AMP.
			add_action( 'amp_post_template_head', array( $this, 'amp_head' ), 11 );

			/**
			 * AMP Schema Enable/Disable
			 *
			 * Allows or prevents the use of schema on AMP generated posts/pages.
			 *
			 * @since 1.0.0
			 *
			 * @param bool $var True to enable, and false to disable.
			 */
			$use_schema = apply_filters( 'gofer_seo_amp_schema', true );
			if ( $use_schema ) {
				// Removes AMP's Schema data to prevent any conflicts/duplications.
				add_action( 'amp_post_template_head', array( $this, 'remove_amp_schema_hook' ), 9 );
			}
		}
	}

	/**
	 * Remove Hooks with AMP's Schema.
	 *
	 * Remove AMP Schema hook used for outputting data.
	 *
	 * @since 1.0.0
	 */
	public function remove_amp_schema_hook() {
		remove_action( 'amp_post_template_head', 'amp_print_schemaorg_metadata' );
	}

	/**
	 * Gofer SEO Head.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function gofer_seo_head() {
		$gofer_seo_schema = new Gofer_SEO_Schema_Builder();
		$gofer_seo_schema->display_json_ld_head_script();
	}

	/**
	 * AMP Head
	 *
	 * Adds meta description to AMP pages.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function amp_head() {
		/**
		 * AMP Schema Enable/Disable
		 *
		 * Allows or prevents the use of schema on AMP generated posts/pages. Use __return_false to disable.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $var True to enable, and false to disable.
		 */
		$use_schema = apply_filters( 'gofer_seo_amp_schema', true );
		if ( $use_schema ) {
			$gofer_seo_schema = new Gofer_SEO_Schema_Builder();
			$gofer_seo_schema->display_json_ld_head_script();
		}
	}

}
