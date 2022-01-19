<?php
/**
 * Admin Screen: Page-Module - Advanced
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

/**
 * Class Gofer_SEO_Screen_Page_Module_Advanced
 *
 * @since 1.0.0
 */
class Gofer_SEO_Screen_Page_Module_Advanced extends Gofer_SEO_Screen_Page_Module {

	/**
	 * Get Module Slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_module_slug() {
		return 'advanced';
	}

	/**
	 * Get Submenu Slug.
	 *
	 * @since 1.0.0
	 */
	public function get_submenu_slug() {
		return 'gofer_seo_module_advanced';
	}

	/**
	 * Get Menu Title.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_menu_title() {
		return 'Advanced';
	}

	/**
	 * The Input Typesets (Params/Configuration)
	 *
	 * @since 1.0.0
	 *
	 * @return array[] See parent method for details.
	 */
	protected function get_input_typesets() {
		$input_typesets = array(
			'php_memory_limit'           => array(
				'title'      => __( 'Memory Limit', 'gofer-seo' ),
				'input_type' => 'select',
				'items'      => array(
					'-1'   => __( 'System Default', 'gofer-seo' ),
					'32'   => __( '32MB', 'gofer-seo' ),
					'64'   => __( '64MB', 'gofer-seo' ),
					'128'  => __( '128MB', 'gofer-seo' ),
					'256'  => __( '256MB', 'gofer-seo' ),
					'512'  => __( '512MB', 'gofer-seo' ),
					'1024' => __( '1GB', 'gofer-seo' ),
					'2048' => __( '2Gb', 'gofer-seo' ),
				),
				'esc'        => array(
					array( 'intval' ),
				),
			),
			'php_max_execution_time'     => array(
				'title'      => __( 'Execution Time', 'gofer-seo' ),
				'input_type' => 'select',
				'items'      => array(
					'-1'  => 'System Default',
					'0'   => 'No Limit',
					'10'  => '10 Seconds',
					'15'  => '15 Seconds',
					'30'  => '30 Seconds',
					'45'  => '45 Seconds',
					'60'  => '1 Minute ',
					'120' => '2 Minute',
					'300' => '5 Minute',
				),
				'esc'        => array(
					array( 'intval' ),
				),
			),
			'enable_title_rewrite'       => array(
				'title'      => __( 'Force Title Rewrite', 'gofer-seo' ),
				'input_type' => 'checkbox',
			),
			'enable_unprotect_post_meta' => array(
				'title' => __( 'Unprotect Post Meta in Editor', 'gofer-seo' ),
				'type'  => 'checkbox',
			),
			'enable_stop_heartbeat'      => array(
				'title' => __( 'Enable Stop WP Heartbeat', 'gofer-seo' ),
				'type'  => 'checkbox',
			),
			'enable_min_files'           => array(
				'title' => __( 'Use Minified JS & CSS files.', 'gofer-seo' ),
				'type'  => 'checkbox',
			),

			// TODO Create Editor Input
			// OR
			// TODO Add Add Field List (robox.txt)
			//'.htaccess Editor'       => array(),
			//'robots.txt Editor'      => array(),
		);

		/**
		 * Advanced Module Input Typeset.
		 *
		 * @since 1.0.0
		 *
		 * @see \Gofer_SEO_Screen_Page::get_input_typesets()
		 *
		 * @return array See `\Gofer_SEO_Screen_Page::get_input_typesets()` for details.
		 */
		$input_typesets = apply_filters( 'gofer_seo_admin_module_advanced_input_typesets', $input_typesets );

		return $input_typesets;
	}

	/**
	 * The Meta Box Typesets (Params/Configuration).
	 *
	 * @since 1.0.0
	 *
	 * @return array[] See parent method for details.
	 */
	protected function get_meta_box_typesets() {
		$meta_box_typesets = array(
			'system' => array(
				'title'    => __( 'System Settings', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'php_memory_limit',
					'php_max_execution_time',
					'enable_title_rewrite',
					'enable_unprotect_post_meta',
					'enable_stop_heartbeat',
					'enable_min_files',
				),
			),
		);

		/**
		 * Advanced Module Meta Box Typesets.
		 *
		 * @since 1.0.0
		 *
		 * @see \Gofer_SEO_Screen_Page::get_meta_box_typesets()
		 *
		 * @param array $meta_box_typsets See `\Gofer_SEO_Screen_Page::get_meta_box_typesets()` for details.
		 */
		$meta_box_typesets = apply_filters( 'gofer_seo_admin_module_advanced_meta_box_typesets', $meta_box_typesets );

		return $meta_box_typesets;
	}

	/**
	 * Add Submenu to Admin Menu.
	 *
	 * @since 1.0.0
	 *
	 * @link  https://developer.wordpress.org/reference/functions/add_submenu_page/
	 */
	public function add_submenu() {
		$hook_suffix = add_submenu_page(
			$this->menu_parent_slug,                // Menu parent slug.
			__( 'Advanced Settings', 'gofer-seo' ),          // Page title.
			__( 'Advanced', 'gofer-seo' ), // Menu title.
			'gofer_seo_access',                     // Capability.
			$this->submenu_slug,                    // Menu slug.
			array( $this, 'display_page' ),         // Callback function.
			$this->submenu_order                    // Position.
		);

		$this->set_hook_suffixes( array( $hook_suffix ) );
		$this->set_screen_ids( array( $hook_suffix ) );
	}

	/**
	 * Get Values from Target Source.
	 *
	 * Used by child classes to return the values being used.
	 *
	 * @since 1.0.0
	 *
	 * @return array ${INPUT_SLUG}
	 *
	 */
	protected function get_values() {
		$values = parent::get_values();

		/**
		 * Advanced Module Get Values.
		 *
		 * @since 1.0.0
		 *
		 * @param array $values The values of the inputs.
		 */
		$values = apply_filters( 'gofer_seo_admin_module_advanced_get_values', $values );

		return $values;
	}

	/**
	 * Update Values to Target Source.
	 *
	 * Used by other classes to handle operations differently.
	 *
	 * @inheritDoc
	 *
	 * @since 1.0.0
	 *
	 * @return bool True on success.
	 */
	protected function update_values( $new_values ) {

		/**
		 * Advanced Module Update Values.
		 *
		 * @since 1.0.0
		 *
		 * @param array $new_values The new set of input (typeset) values.
		 */
		$new_values = apply_filters( 'gofer_seo_admin_module_advanced_update_values', $new_values );

		return parent::update_values( $new_values );
	}
}
