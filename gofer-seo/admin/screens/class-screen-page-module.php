<?php
/**
 * Admin Screen: Page-Module - Base
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

/**
 * Class Gofer_SEO_Screen_Page_Module
 *
 * @since 1.0.0
 */
abstract class Gofer_SEO_Screen_Page_Module extends Gofer_SEO_Screen_Page {

	/**
	 * Gofer_SEO_Screen_Page_Module constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		$gofer_seo_option = Gofer_SEO_Options::get_instance();
		$this->menu_order = (int) $gofer_seo_option->options['modules']['general']['admin_menu_order'];
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
	 * Get Values from Target Source.
	 *
	 * Used by child classes to return the values being used.
	 * If multiple sources are used, and may use similar slugs, then handle those
	 * operations here, and ensure the inputs_typeset array key matches the values array key.
	 *
	 * NOTE: Avoid nesting variables unless it is a wrap/cast or *_dynamic.
	 *
	 * @inheritDoc
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	protected function get_values() {
		$options = Gofer_SEO_Options::get_instance()->options;

		$values = $options['modules'][ $this->get_module_slug() ];

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
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		$gofer_seo_options->options['modules'][ $this->get_module_slug() ] = $new_values;

		return $gofer_seo_options->update_options();
	}

	/**
	 * Register/Enqueue Styles.
	 *
	 * @since 1.0.0
	 *
	 * @param $hook_suffix
	 */
	public function admin_register_styles( $hook_suffix ) {
		parent::admin_register_styles( $hook_suffix );
		if ( ! in_array( $hook_suffix, $this->get_hook_suffixes( 'submenus' ), true ) ) {
			return;
		}

		// Styles that would be used on module screens.
		// Plugin logo, font-icons, etc.
	}

	/**
	 * Register/Enqueue Scripts.
	 *
	 * @since 1.0.0
	 *
	 * @param $hook_suffix
	 */
	public function admin_register_scripts( $hook_suffix ) {
		parent::admin_register_scripts( $hook_suffix );
		if ( ! in_array( $hook_suffix, $this->get_hook_suffixes( 'submenus' ), true ) ) {
			return;
		}

		// Scripts that would be used on module screens.
		// Count chars, show-condition, text-editor, etc.
	}

	/**
	 * Admin Bar (Sub) Menu.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 */
	public function admin_bar_submenu( $wp_admin_bar ) {
		parent::admin_bar_submenu( $wp_admin_bar );

		$title = '<span class="ab-icon"></span><span class="ab-label">' . $this->menu_title . '</span>';
		$wp_admin_bar->add_menu(
			array(
				'id'     => $this->submenu_slug,
				'parent' => GOFER_SEO_NICENAME . '-settings',
				'title'  => $this->menu_title,
				'href'   => admin_url( 'admin.php?page=' . $this->submenu_slug ),

				'meta'   => array(
					'class' => 'gofer-seo-admin-bar-submenu',
				),
			)
		);
	}
}
