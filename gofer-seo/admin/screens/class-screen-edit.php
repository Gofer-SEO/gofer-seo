<?php
/**
 * Admin Screen: Editor
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

/**
 * Class Gofer_SEO_Screen_Edit
 *
 * @since 1.0.0
 */
abstract class Gofer_SEO_Screen_Edit extends Gofer_SEO_Screen {

	/**
	 * Gofer_SEO_Screen_Edit constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * The Input Typesets (Params/Configuration)
	 *
	 * @since 1.0.0
	 *
	 * @return array[] ${INPUT_SLUG} {
	 *     @type string   $slug            (Optional) The input slug for this array variable. Automatically set.
	 *     @type string   $title           The title/label of the input.
	 *     @type string   $input_type      The type of input to display.
	 *     @type string   $type            Same as `$input_type`.
	 *     @type string   $layout          The layout of the label & input(s).
	 *     @type array    $wrap            Nested Typeset. Same as Input Typeset (this).
	 *     @type array    $wrap_dynamic    Nested Typeset for dynamic variables. Same as Input Typeset (this).
	 *     @type string[] $items           Required if 'type' is 'wrap_dynamic', 'multi-checkbox', 'radio', 'select', & 'multi-select' {
	 *         @type string ${SLUG} => ${TITLE}
	 *     }
	 *     @type array    $conditions      (Optional) {
	 *         The `$conditions_typeset` for listen to an input, and apply an action when condition(s) are met.
	 *
	 *         @type string $action   What action will be taken if true. Default: 'show'.
	 *                                Accepts 'show', 'hide', 'enable', 'disable', and 'readonly'.
	 *         @type string $relation Compare relation between each conditions. Default: 'AND'.
	 *                                Accepts 'AND', and 'OR'.
	 *         @type array ${INPUT_KEY} {
	 *             @type string $left_var    (Optional) Left variable.
	 *             @type string $operator    Compare operator.
	 *                                       Accepts '==', '===', '!=', '!==', '<', '>', '<=', '>=',
	 *                                       'TRUE', 'FALSE', 'AND', 'OR', 'regex', 'inArray', 'checked', 'selected',
	 *             @type string $right_var   (Optional) Right variable.
	 *             @type string $right_value (Required|Optional) Right value. Required if 'right_var' is not set.
	 *         }
	 *     }
	 *     @type array    $item_conditions (Optional) {
	 *         @type array ${INPUT_NAME|ITEM_SLUG} {
	 *             The `$conditions_typeset(s)` for listen to an input (or siblings), and apply an action
	 *             when condition(s) are met.
	 *
	 *             @type string $action   What action will be taken if true. Default: 'show'.
	 *                                    Accepts 'show', 'hide', 'enable', 'disable', and 'readonly'.
	 *             @type string $relation Compare relation between each conditions. Default: 'AND'.
	 *                                    Accepts 'AND', and 'OR'.
	 *             @type array ${INPUT_KEY} {
	 *                 @type string $left_var    (Optional) Left variable.
	 *                 @type string $operator    Compare operator.
	 *                                           Accepts '==', '===', '!=', '!==', '<', '>', '<=', '>=',
	 *                                           'TRUE', 'FALSE', 'AND', 'OR', 'regex', 'inArray', 'checked', 'selected',
	 *                 @type string $right_var   (Optional) Right variable.
	 *                 @type string $right_value (Required|Optional) Right value. Required if 'right_var' is not set.
	 *             }
	 *         }
	 *     }
	 *     @type string[] $attrs           (Optional) {
	 *         @type string ${ELEMENT_ATTRIBUTE} => ${VALUE} Attributes to add to input element.
	 *     }
	 *     @type array    $esc             (Optional) {
	 *         @type array ${INT_INDEX} {
	 *             @type string|array $callback The esc callback function to use instead of the default esc_* function.
	 *             @type array        $args     Additional arguments/params to pass to callback function.
	 *         }
	 *     }
	 * }
	 */
	abstract protected function get_input_typesets();

	/**
	 * The Meta Box Typesets (Params/Configuration).
	 *
	 * @since 1.0.0
	 *
	 * @return array[] ${SLUG} {
	 *     @type string       $title         The Meta Box Header/Title.
	 *     @type string       $context       The Meta Box Context to use.
	 *                                       Accepts...
	 *                                           Admin Page
	 *                                           - 'gofer_seo_normal'
	 *                                           - 'gofer_seo_side'
	 *                                           - 'gofer_seo_advanced'
	 *                                           Dashboard
	 *                                           - 'gofer_seo_normal'
	 *                                           - 'gofer_seo_column2'
	 *                                           - 'gofer_seo_column3'
	 *                                           - 'gofer_seo_column4'
	 *     @type string       $priority      The Meta Box param Priority to use.
	 *                                       Accepts 'high', 'sorted', 'core', 'default', and 'low'.
	 *     @type string[]     $inputs        The input_typeset names/slugs to add to Meta Box.
	 *     @type string|array $callback      The display meta box callback used to render the content.
	 *     @type array        $callback_args Additional arguments to pass to callback.
	 * }
	 */
	abstract protected function get_meta_box_typesets();

	/**
	 * Get Values from Target Source.
	 *
	 * Used by child classes to return the values being used.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $id   The object ID.
	 * @param array $args Additional args.
	 * @return mixed[] ${INPUT_SLUG}
	 */
	abstract protected function get_values( $id, $args = array() );

	/**
	 * Update Values to Target Source.
	 *
	 * Used by other classes to handle operations differently.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed[] $new_values  ${INPUT_SLUG}
	 * @param int     $id          The object id.
	 * @param array   $data        Additional values that may have been passed when saving.
	 *                             Used by `\Gofer_SEO_Screen_Edit_Term::save_term()`.
	 * @return bool True on success.
	 */
	abstract protected function update_values( $new_values, $id, $data = array() );

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
	}

	/**
	 * Add Meta Boxes.
	 *
	 * @since 1.0.0
	 *
	 * @link https://developer.wordpress.org/reference/functions/add_meta_box/
	 *
	 * @param string               $hook_suffix
	 * @param WP_Post|WP_Term|null $object
	 */
	public function add_meta_boxes( $hook_suffix, $object ) {
		$id = 0;
		if ( $object instanceof WP_Post ) {
			$id = $object->ID;
		} elseif ( $object instanceof WP_Term ) {
			$id = $object->term_id;
		}
		$meta_box_typesets = $this->typesetter_admin->validate_meta_box_typesets( $this->get_meta_box_typesets() );
		$input_typesets    = $this->typesetter_admin->validate_input_typesets( $this->get_input_typesets() );
		$values            = $this->get_values( $id );

		foreach ( $meta_box_typesets as $meta_box_slug => $meta_box_typeset ) {
			$meta_box_input_typesets = array();
			foreach ( $meta_box_typeset['inputs'] as $index => $input_name ) {
				if ( ! isset( $input_typesets[ $input_name ] ) ) {
					// TODO Display error.
					unset( $meta_box_typeset['inputs'][ $index ] );
					continue;
				}
				$meta_box_input_typesets[ $input_name ] = $input_typesets[ $input_name ];
			}

			$callback_args = array(
				'hook_suffix'             => $hook_suffix,
				'meta_box_inputs'         => $meta_box_typeset['inputs'],
				'meta_box_input_typesets' => $meta_box_input_typesets,
				'values'                  => $values,
			);
			$callback_args = wp_parse_args( $callback_args, $meta_box_typeset['callback_args'] );

			add_meta_box(
				$meta_box_slug,                // ID.
				$meta_box_typeset['title'],    // Title.
				$meta_box_typeset['callback'], // Callback.
				$meta_box_typeset['screens'],  // Screen(s).
				$meta_box_typeset['context'],  // Context.
				$meta_box_typeset['priority'], // Priority.
				$callback_args                 // Callback Args.
			);
		}
	}

}
