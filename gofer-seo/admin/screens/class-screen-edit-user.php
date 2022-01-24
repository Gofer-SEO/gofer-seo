<?php
/**
 * Admin Screen: Editor - User
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

/**
 * Class Gofer_SEO_Screen_Edit_User
 *
 * @since 1.0.0
 */
class Gofer_SEO_Screen_Edit_User extends Gofer_SEO_Screen_Edit {

	/**
	 * Gofer_SEO_Screen_Edit_User constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'admin_menu', array( $this, 'init_hook_suffixes' ), 9 );
		add_action( 'admin_menu', array( $this, 'init_screen_ids' ), 9 );
	}

	/**
	 * WP Hook - Current Screen.
	 *
	 * Triggers after `admin_menu` & `admin_init`.
	 * Useful for adding enqueue scripts, post transitions, etc.
	 * CANNOT be used to add wp_ajax_*, or adding menus.
	 *
	 * @since 1.0.0
	 *
	 * @uses "{$taxonomy}_edit_form" hook.
	 * @link https://developer.wordpress.org/reference/hooks/taxonomy_edit_form/
	 * @link https://developer.wordpress.org/reference/hooks/current_screen/
	 *
	 * @param WP_Screen $current_screen Current WP_Screen object.
	 */
	public function current_screen( $current_screen ) {
		parent::current_screen( $current_screen );
		if ( ! in_array( $current_screen->id, $this->get_screen_ids(), true ) ) {
			return;
		}

		// Display.
		add_action( 'show_user_profile', array( $this, 'display_user_editor' ) );
		add_action( 'edit_user_profile', array( $this, 'display_user_editor' ) );

		// Update/Save.
		add_action( 'personal_options_update', array( $this, 'save_user' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_user' ) );
	}

	/**
	 * Register/Enqueue Scripts.
	 *
	 * @since 1.0.0
	 *
	 * @see 'admin_enqueue_scripts' hook
	 * @link https://developer.wordpress.org/reference/hooks/admin_enqueue_scripts/
	 *
	 * @param $hook_suffix
	 */
	public function admin_register_scripts( $hook_suffix ) {
		parent::admin_register_scripts( $hook_suffix );
		if ( ! in_array( $hook_suffix, $this->get_hook_suffixes(), true ) ) {
			return;
		}

		wp_enqueue_script( 'gofer-seo-bootstrap-js' );
		wp_enqueue_script( 'gofer-seo-inputs-input-conditions-js' );
	}

	/**
	 * Initialize Hook Suffixes.
	 *
	 * @since 1.0.0
	 */
	public function init_hook_suffixes() {
		$hook_suffixes = array(
			'users.php',
			'profile.php',
			'user-edit.php',
		);

		/**
		 * Hook Suffixes.
		 *
		 * @since 1.0.0
		 *
		 * @param string[] $hook_suffixes List of hook suffixes.
		 */
		$hook_suffixes = apply_filters( 'gofer_seo_admin_user_hook_suffixes', $hook_suffixes );

		$this->set_hook_suffixes( $hook_suffixes );
	}

	/**
	 * Initialize Screen IDs.
	 *
	 * @since 1.0.0
	 */
	public function init_screen_ids() {
		$screen_ids = array(
			'users',
			'profile',
			'user-edit',
		);

		/**
		 * Screen IDs.
		 *
		 * @since 1.0.0
		 *
		 * @param string[] $screen_ids List of screen IDs.
		 */
		$screen_ids = apply_filters( 'gofer_seo_admin_user_screen_ids', $screen_ids );

		$this->set_screen_ids( $screen_ids );
	}

	/**
	 * The Input Typesets (Params/Configuration)
	 *
	 * @since 1.0.0
	 *
	 * @return array[] See parent method for details.
	 */
	protected function get_input_typesets() {
		/**
		 * Input Typesets.
		 *
		 * @since 1.0.0
		 *
		 * @param array
		 */
		$input_typesets = apply_filters( 'gofer_seo_admin_user_input_typesets', array() );

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
		/**
		 * Metabox Typesets.
		 *
		 * @since 1.0.0
		 *
		 * @param array
		 */
		$meta_box_typesets = apply_filters( 'gofer_seo_admin_user_meta_box_typesets', array() );

		return $meta_box_typesets;
	}

	/**
	 * Get Values from Target Source.
	 *
	 * Used by child classes to return the values being edited.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $user_id The user ID.
	 * @param array $args    Additional args.
	 * @return mixed[] ${INPUT_SLUG}
	 */
	protected function get_values( $user_id, $args = array() ) {
		/**
		 * Post Get Values.
		 *
		 * @since 1.0.0
		 *
		 * @param array
		 * @param int   $user_id The user ID.
		 * @param array $args    Additional args.
		 */
		$values = apply_filters( 'gofer_seo_admin_user_get_values', array(), $user_id, $args );

		return $values;
	}

	/**
	 * Update Values to Target Source.
	 *
	 * Used by other classes to handle operations differently.
	 *
	 * @since 1.0.0
	 *
	 * @param array $new_values ${INPUT_SLUG}
	 * @param int   $user_id    The object id.
	 * @param array $data       Additional values that may have been passed when saving.
	 *                          Used by `\Gofer_SEO_Screen_Edit_Term::save_term()`.
	 */
	protected function update_values( $new_values, $user_id, $data = array() ) {
		/**
		 * Post Update Values.
		 *
		 * @since 1.0.0
		 *
		 * @param array $new_values The new set of input (typeset) values.
		 * @param int   $user_id    The user id.
		 */
		do_action( 'gofer_seo_admin_user_update_values', $new_values, $user_id );
	}

	/**
	 * Display User Editor.
	 *
	 * Adds additional fields to the User Editor.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_User $profileuser The current WP_User object.
	 */
	public function display_user_editor( $profileuser ) {
		$input_typesets    = $this->typesetter_admin->validate_input_typesets( $this->get_input_typesets() );
		$values            = $this->get_values( $profileuser->ID );

		$args = array(
			'object'         => $profileuser,
			'input_typesets' => $input_typesets,
			'values'         => $values,
		);
		gofer_seo_do_template( 'admin/screens/user-edit.php', $args );
	}

	/**
	 * Save User.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id The user ID.
	 */
	public function save_user( $user_id ) {
		$values            = $this->get_values( $user_id );
		$input_typesets    = $this->typesetter_admin->validate_input_typesets( $this->get_input_typesets() );
		$form_input_values = $this->get_input_post_values( $input_typesets );

		$values = array_replace( $values, $form_input_values );

		$this->update_values( $values, $user_id );
	}
}
