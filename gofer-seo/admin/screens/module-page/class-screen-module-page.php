<?php
/**
 * Admin Screen: Page - Module
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

/**
 * Class Gofer_SEO_Screen_Module_Page.
 *
 * @since 1.0.0
 */
abstract class Gofer_SEO_Screen_Module_Page {

	/**
	 * Gofer_SEO_Screen_Module_Page constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'current_screen', array( $this, 'current_screen' ) );

		add_action( 'admin_action_gofer_seo_screens_page_save_' . $this->get_submenu_slug(), array( $this, 'load' ), 9 );
	}

	/**
	 * Action - Current Screen
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Screen $current_screen
	 */
	public function current_screen( $current_screen ) {
		if (
				'toplevel_page_gofer_seo' === $current_screen->id ||
				'gofer-seo_page_gofer_seo_module_' . $this->get_module_slug() === $current_screen->id
		) {
			$this->load();
		}
	}

	/**
	 * Add Hooks.
	 *
	 * @since 1.0.0
	 */
	public function load() {
		add_filter( 'gofer_seo_admin_module_' . $this->get_module_slug() . '_input_typesets', array( $this, 'get_input_typesets' ) );
		add_filter( 'gofer_seo_admin_module_' . $this->get_module_slug() . '_meta_box_typesets', array( $this, 'get_meta_box_typesets' ) );
		add_filter( 'gofer_seo_admin_module_' . $this->get_module_slug() . '_get_values', array( $this, 'get_values' ) );
		add_filter( 'gofer_seo_admin_module_' . $this->get_module_slug() . '_update_values', array( $this, 'update_values' ) );
	}

	/**
	 * Get Module Slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	abstract protected function get_module_slug();

	/**
	 * Get Submenu Slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	abstract public function get_submenu_slug();

	/**
	 * The Input Typesets (Params/Configuration)
	 *
	 * @since 1.0.0
	 *
	 * @see \Gofer_SEO_Screen_Page::get_input_typesets()
	 *
	 * @param array[] For details, see `\Gofer_SEO_Screen_Page::get_input_typesets()`.
	 * @return array[] For details, see `\Gofer_SEO_Screen_Page::get_input_typesets()`.
	 */
	abstract public function get_input_typesets( $input_typesets );

	/**
	 * The Meta Box Typesets (Params/Configuration).
	 *
	 * @since 1.0.0
	 *
	 * @see \Gofer_SEO_Screen_Page::get_meta_box_typesets()
	 *
	 * @param array[] For details, see `\Gofer_SEO_Screen_Page::get_meta_box_typesets()`.
	 * @return array[] For details, see `\Gofer_SEO_Screen_Page::get_meta_box_typesets()`.
	 */
	abstract public function get_meta_box_typesets( $meta_box_typesets );

	/**
	 * Get Values from Target Source.
	 *
	 * Used by child classes to return the values being edited.
	 *
	 * @since 1.0.0
	 *
	 * @param array $values ${INPUT_SLUG}
	 * @return array ${INPUT_SLUG}
	 */
	abstract public function get_values( $values );

	/**
	 * Update Values to Target Source.
	 *
	 * Used by other classes to handle operations differently.
	 *
	 * @since 1.0.0
	 *
	 * @param array $values ${INPUT_SLUG}
	 * @return array ${INPUT_SLUG}
	 */
	abstract public function update_values( $values );

}
