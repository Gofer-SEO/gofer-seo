<?php
/**
 * Admin Screen: Editor - Term
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

/**
 * Class Gofer_SEO_Screen_Edit_Term
 *
 * @since 1.0.0
 */
class Gofer_SEO_Screen_Edit_Term extends Gofer_SEO_Screen_Edit {

	/**
	 * Gofer_SEO_Screen_Edit_Term constructor.
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

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 3, 2 );
		add_action( $current_screen->taxonomy . '_edit_form', array( $this, 'display_term_editor' ), 9, 2 );
		add_action( 'edited_term', array( $this, 'save_term' ), 10, 3 );
	}

	/**
	 * Initialize Hook Suffixes.
	 *
	 * @since 1.0.0
	 */
	public function init_hook_suffixes() {
		$hook_suffixes = array(
			'edit-tags.php',
			'term.php',
		);

		/**
		 * Hook Suffixes.
		 *
		 * @since 1.0.0
		 *
		 * @param string[] $hook_suffixes List of hook suffixes.
		 */
		$hook_suffixes = apply_filters( 'gofer_seo_admin_term_hook_suffixes', $hook_suffixes );

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
		 * Screen IDs.
		 *
		 * @since 1.0.0
		 *
		 * @param string[] $screen_ids List of screen IDs.
		 */
		$screen_ids = apply_filters( 'gofer_seo_admin_term_screen_ids', $screen_ids );

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
		$input_typesets = apply_filters( 'gofer_seo_admin_term_input_typesets', array() );

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
		$meta_box_typesets = apply_filters( 'gofer_seo_admin_term_meta_box_typesets', array() );

		return $meta_box_typesets;
	}

	/**
	 * Get Values from Target Source.
	 *
	 * Used by child classes to return the values being edited.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $term_id The term ID.
	 * @param array $args    Additional args.
	 * @return mixed[] ${INPUT_SLUG}
	 */
	protected function get_values( $term_id, $args = array() ) {
		/**
		 * Post Get Values.
		 *
		 * @since 1.0.0
		 *
		 * @param array $values  The values of the inputs.
		 * @param int   $term_id The term ID.
		 * @param array $args    Additional args.
		 */
		$values = apply_filters( 'gofer_seo_admin_term_get_values', array(), $term_id, $args );

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
	 * @param int   $term_id    The object id.
	 * @param array $data       Additional values that may have been passed when saving.
	 *                          Used by `\Gofer_SEO_Screen_Edit_Term::save_term()`.
	 */
	protected function update_values( $new_values, $term_id, $data = array() ) {
		/**
		 * Post Update Values.
		 *
		 * @since 1.0.0
		 *
		 * @param array $new_values The new set of input (typeset) values.
		 * @param int   $term_id    The term id.
		 * @param array $data {
		 *     Additional data needed for identifying the term.
		 *
		 *     @type int    $tt_id    Term-Taxonomy ID.
		 *     @type string $taxonomy Taxonomy slug.
		 * }
		 */
		do_action( 'gofer_seo_admin_term_update_values', $new_values, $term_id, $data );
	}

	/**
	 * Display Term Editor.
	 *
	 * Adds additional fields to the Term Editor.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Term $term     Current taxonomy term object.
	 * @param string  $taxonomy Current taxonomy slug.
	 */
	public function display_term_editor( $term, $taxonomy ) {
		$current_screen = get_current_screen();
		do_action( 'add_meta_boxes', $current_screen->id, $term );
		$args = array(
			'object' => $term,
		);
		gofer_seo_do_template( 'admin/screens/term-edit.php', $args );
	}

	/**
	 * Save Term.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $term_id  Term ID.
	 * @param int    $tt_id    Term taxonomy ID.
	 * @param string $taxonomy Taxonomy slug.
	 */
	public function save_term( $term_id, $tt_id, $taxonomy ) {
		check_admin_referer( 'gofer_seo_screens_page', 'gofer_seo_nonce' );

		$values            = $this->get_values( $term_id );
		$input_typesets    = $this->typesetter_admin->validate_input_typesets( $this->get_input_typesets() );
		$form_input_values = $this->get_input_post_values( $input_typesets );

		$values = array_replace( $values, $form_input_values );

		$data = array(
			'tt_id'    => $tt_id,
			'taxonomy' => $taxonomy,
		);
		$this->update_values( $values, $term_id, $data );
	}
}
