<?php
/**
 * User Editor Screen Extension.
 *
 * Filter extension to \Gofer_SEO_Screen_Edit_User class.
 *
 * @package Gofer SEO
 * @since   1.0.0
 */

/**
 * Class Gofer_SEO_Screen_User_Editor
 *
 * @since 1.0.0
 */
abstract class Gofer_SEO_Screen_User_Editor {

	/**
	 * Gofer_SEO_Screen_User_Editor constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'current_screen', array( $this, 'current_screen' ) );
	}

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
		$target_screens = array(
			'users',
			'profile',
			'user-edit',
		);
		if ( in_array( $current_screen->id, $target_screens, true ) ) {
			add_filter( 'gofer_seo_admin_user_input_typesets', array( $this, 'get_input_typesets' ) );
			add_filter( 'gofer_seo_admin_user_meta_box_typesets', array( $this, 'get_meta_box_typesets' ) );
			add_filter( 'gofer_seo_admin_user_get_values', array( $this, 'get_values' ) );
			add_action( 'gofer_seo_admin_user_update_values', array( $this, 'update_values' ) );
		}
	}
}
