<?php
/**
 * Admin Screen: Editor - Post
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

/**
 * Class Gofer_SEO_Screen_Edit_Post
 *
 * @since 1.0.0
 */
class Gofer_SEO_Screen_Edit_Post extends Gofer_SEO_Screen_Edit {

	/**
	 * Gofer_SEO_Screen_Edit_Post constructor.
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
	 * @link https://developer.wordpress.org/reference/hooks/current_screen/
	 *
	 * @param WP_Screen $current_screen Current WP_Screen object.
	 */
	public function current_screen( $current_screen ) {
		parent::current_screen( $current_screen );
		if ( ! in_array( $current_screen->id, $this->get_screen_ids(), true ) ) {
			return;
		}

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 3, 2 );
		add_action( 'transition_post_status', array( $this, 'transition_post' ), 10, 3 );
		add_action( 'add_attachment', array( $this, 'save_attachment' ) );
		add_action( 'edit_attachment', array( $this, 'save_attachment' ) );
	}

	/**
	 * Post Transition.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       Post object.
	 */
	public function transition_post( $new_status, $old_status, $post ) {
		if ( 'inherit' === $new_status || 'auto-draft' === $new_status ) {
			return;
		}

		add_action( 'save_post', array( $this, 'save_post' ), 10, 3 );
	}

	/**
	 * Initialize Hook Suffixes.
	 *
	 * @since 1.0.0
	 */
	public function init_hook_suffixes() {
		$hook_suffixes = array(
			'edit.php',
			'post.php',
			'post-new.php',
		);

		/**
		 * Post Hook Suffixes.
		 *
		 * @since 1.0.0
		 *
		 * @param string[] $hook_suffixes List of hook suffixes.
		 */
		$hook_suffixes = apply_filters( 'gofer_seo_admin_post_hook_suffixes', $hook_suffixes );

		$this->set_hook_suffixes( $hook_suffixes );
	}

	/**
	 * Initialize Screen IDs.
	 *
	 * @since 1.0.0
	 */
	public function init_screen_ids() {
		$screen_ids = array();

		/**
		 * Post Screen IDs.
		 *
		 * @since 1.0.0
		 *
		 * @param string[] $screen_ids List of screen IDs.
		 */
		$screen_ids = apply_filters( 'gofer_seo_admin_post_screen_ids', $screen_ids );

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
		 * Post Input Typesets.
		 *
		 * @since 1.0.0
		 *
		 * @param array
		 */
		$input_typesets = apply_filters( 'gofer_seo_admin_post_input_typesets', array() );

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
		 * Post Metabox Typesets.
		 *
		 * @since 1.0.0
		 *
		 * @param array
		 */
		$meta_box_typesets = apply_filters( 'gofer_seo_admin_post_meta_box_typesets', array() );

		return $meta_box_typesets;
	}

	/**
	 * Get Values from Target Source.
	 *
	 * Used by child classes to return the values being edited.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $post_id The post ID.
	 * @param array $args    Additional args.
	 * @return mixed[] ${INPUT_SLUG}
	 */
	protected function get_values( $post_id, $args = array() ) {
		/**
		 * Post Get Values.
		 *
		 * @since 1.0.0
		 *
		 * @param array
		 * @param int   $post_id The post ID.
		 * @param array $args    Additional args.
		 */
		$values = apply_filters( 'gofer_seo_admin_post_get_values', array(), $post_id, $args );

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
	 * @param int   $post_id    The object id.
	 * @param array $data       Additional values that may have been passed when saving.
	 *                          Used by `\Gofer_SEO_Screen_Edit_Term::save_term()`.
	 */
	protected function update_values( $new_values, $post_id, $data = array() ) {
		/**
		 * Post Update Values.
		 *
		 * @since 1.0.0
		 *
		 * @param array $new_values The new set of input (typeset) values.
		 * @param int   $post_id    Post ID.
		 */
		do_action( 'gofer_seo_admin_post_update_values', $new_values, $post_id );
	}

	/**
	 * Save Post.
	 *
	 * @since 1.0.0
	 *
	 * @param int     $post_ID Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Whether this is an existing post being updated or not.
	 */
	public function save_post( $post_ID, $post, $update ) {
		check_admin_referer( 'gofer_seo_screens_page', 'gofer_seo_nonce' );

		$values            = $this->get_values( $post_ID );
		$input_typesets    = $this->typesetter_admin->validate_input_typesets( $this->get_input_typesets() );
		$form_input_values = $this->get_input_post_values( $input_typesets );

		$values = array_replace( $values, $form_input_values );

		$this->update_values( $values, $post_ID );
	}

	/**
	 * Save Attachment.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_attachment( $post_id ) {
		$values            = $this->get_values( $post_id );
		$input_typesets    = $this->typesetter_admin->validate_input_typesets( $this->get_input_typesets() );
		$form_input_values = $this->get_input_post_values( $input_typesets );

		$values = array_replace( $values, $form_input_values );

		$this->update_values( $values, $post_id );
	}
}
