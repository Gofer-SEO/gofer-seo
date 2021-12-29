<?php
/**
 * Term Editor Screen Extension.
 *
 * Filter extension to \Gofer_SEO_Screen_Edit_Term class.
 *
 * @package Gofer SEO
 * @since   1.0.0
 */

/**
 * Class Gofer_SEO_Screen_Term_Editor
 *
 * @since 1.0.0
 */
abstract class Gofer_SEO_Screen_Term_Editor {

	/**
	 * Gofer_SEO_Screen_Term_Editor constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'current_screen', array( $this, 'current_screen' ) );
		//add_filter( 'gofer_seo_admin_term_hook_suffixes', array( $this, 'init_hook_suffixes' ) );
		add_filter( 'gofer_seo_admin_term_screen_ids', array( $this, 'init_screen_ids' ) );
	}

	/**
	 * Initialize/Set Hook Suffixes
	 *
	 * @since 1.0.0
	 *
	 * @param string[] $hook_suffixes
	 * @return string[]
	 */
	public function init_hook_suffixes( $hook_suffixes ) {
		$hook_suffixes = array_merge( $hook_suffixes, $this->get_active_taxonomies() );

		return $hook_suffixes;
	}

	/**
	 * Initialize/Set Screen Ids.
	 *
	 * @since 1.0.0
	 *
	 * @param string[] $screen_ids
	 * @return string[]
	 */
	public function init_screen_ids( $screen_ids ) {
		$screen_ids = array_unique( array_merge( $screen_ids, $this->get_active_taxonomies() ) );

		// Add 'edit-*'.
		$screen_ids = array_merge(
			$screen_ids,
			array_map(
				function( $value ) {
					if ( preg_match( '/(edit-)/', $value ) ) {
						return $value;
					}
					return 'edit-' . $value;
				},
				$screen_ids
			)
		);

		return array_unique( $screen_ids );
	}

	/**
	 * Get Active Taxonomies/Screens.
	 *
	 * This checks if the taxonomy is enabled, and if show meta-box is enabled, and
	 * returns an array of taxonomy slugs.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	abstract public function get_active_taxonomies();

	/**
	 * The Input Typesets (Params/Configuration)
	 *
	 * @since 1.0.0
	 *
	 * @see \Gofer_SEO_Screen_Edit::get_input_typesets()
	 *
	 * @param array[] For details, see `\Gofer_SEO_Screen_Edit::get_input_typesets()`.
	 * @return array[] For details, see `\Gofer_SEO_Screen_Edit::get_input_typesets()`.
	 */
	abstract public function get_input_typesets( $input_typesets );

	/**
	 * The Meta Box Typesets (Params/Configuration).
	 *
	 * @since 1.0.0
	 *
	 * @see \Gofer_SEO_Screen_Edit::get_meta_box_typesets()
	 *
	 * @param array[] For details, see `\Gofer_SEO_Screen_Edit::get_meta_box_typesets()`.
	 * @return array[] For details, see `\Gofer_SEO_Screen_Edit::get_meta_box_typesets()`.
	 */
	abstract public function get_meta_box_typesets( $meta_box_typesets );

	/**
	 * Get Values from Target Source.
	 *
	 * Used by child classes to return the values being edited.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed[] $values ${INPUT_SLUG}
	 * @return mixed[] ${INPUT_SLUG}
	 */
	abstract public function get_values( $values );

	/**
	 * Update Values to Target Source.
	 *
	 * Used by other classes to handle operations differently.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed[] $values ${INPUT_SLUG}
	 * @return mixed[] $values ${INPUT_SLUG}
	 */
	abstract public function update_values( $values );

	/**
	 * Action - Current Screen
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Screen $current_screen
	 */
	public function current_screen( $current_screen ) {
		if ( in_array( $current_screen->taxonomy, $this->get_active_taxonomies(), true ) ) {
			add_filter( 'gofer_seo_admin_term_input_typesets', array( $this, 'get_input_typesets' ) );
			add_filter( 'gofer_seo_admin_term_meta_box_typesets', array( $this, 'get_meta_box_typesets' ) );
			add_filter( 'gofer_seo_admin_term_get_values', array( $this, 'get_values' ) );
			add_action( 'gofer_seo_admin_term_update_values', array( $this, 'update_values' ) );
		}
	}
}
