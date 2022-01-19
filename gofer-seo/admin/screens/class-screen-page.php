<?php
/**
 * Admin Screen: Page
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

/**
 * Class Gofer_SEO_Screen_Page
 *
 * @since 1.0.0
 */
abstract class Gofer_SEO_Screen_Page extends Gofer_SEO_Screen {

	/**
	 * Parent Menu Slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string $menu_parent_slug A unique slug to set the parent menu id as.
	 */
	protected $menu_parent_slug = '';

	/**
	 * Submenu Slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string $submenu_slug A unique slug to set the submenu id as.
	 */
	protected $submenu_slug = '';

	/**
	 * Menu Title.
	 *
	 * @var string
	 */
	protected $menu_title;

	/**
	 * (Top/Parent) Menu Order.
	 *
	 * @since 1.0.0
	 *
	 * @var int $menu_order Top/Parent menu order.
	 */
	protected $menu_order = 4;

	/**
	 * Submenu Order.
	 *
	 * @since 1.0.0
	 *
	 * @var int $submenu_order Submenu order.
	 */
	protected $submenu_order = 10;

	/**
	 * Gofer_SEO_Screen_Page constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();
		$this->menu_parent_slug = (string) $this->get_menu_parent_slug();
		$this->submenu_slug     = (string) $this->get_submenu_slug();
		$this->menu_title       = (string) $this->get_menu_title();

		add_action( 'admin_menu', array( $this, 'add_menu' ), 3 );
		add_action( 'admin_menu', array( $this, 'add_submenu' ), 6 );

		// Uses admin-post.php instead. Was used to resolve a past issue with file uploads for importing data.
		//add_action( 'admin_post_gofer_seo_screens_page_save', array( $this, 'save_page_settings' ) );
		add_action( 'admin_action_gofer_seo_screens_page_save_' . $this->get_submenu_slug(), array( $this, 'save_page_settings' ) );
	}

	/**
	 * Menu (Parent) Slug.
	 *
	 * Used to register the `admin_menu_page()`, and for submenu's to use as the `$parent_slug`.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_menu_parent_slug() {
		return 'gofer_seo';
	}

	/**
	 * Get Submenu Slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	abstract public function get_submenu_slug();

	/**
	 * Get Menu Title.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	abstract public function get_menu_title();

	/**
	 * The Input Typesets (Params/Configuration)
	 *
	 * @since 1.0.0
	 *
	 * @return array[] ${INPUT_SLUG} {
	 *     @type string   $slug            (Optional) The input slug for this array variable. Automatically set.
	 *     @type string   $title           The title/label of the input.
	 *     @type string   $type            The type of input to display.
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
	 * @return mixed[] ${INPUT_SLUG}
	 *
	 */
	abstract protected function get_values();

	/**
	 * Update Values to Target Source.
	 *
	 * Used by other classes to handle operations differently.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed[] $new_values ${INPUT_SLUG}
	 * @return bool True on success.
	 */
	abstract protected function update_values( $new_values );

	/**
	 * Add Admin Menu to WP Admin.
	 *
	 * @since 1.0.0
	 *
	 * @link https://developer.wordpress.org/reference/functions/add_menu_page/
	 */
	public function add_menu() {
		global $menu;

		$is_menu_set = false;
		foreach ( $menu as $child_menu ) {
			if (
					isset( $child_menu[5] ) &&
					'toplevel_page_' . $this->menu_parent_slug === $child_menu[5]
			) {
				$is_menu_set = true;
				$this->set_hook_suffixes( array( 'top' => $child_menu[5] ) );
			}
		}
		if ( ! $is_menu_set ) {
			$hook_suffix = add_menu_page(
				GOFER_SEO_NAME . __( ' Settings', 'gofer-seo' ), // Page title.
				GOFER_SEO_NAME,                                  // Menu title.
				'gofer_seo_access',                              // Capability.
				$this->menu_parent_slug,                         // Menu slug.
				//array( $this, 'display_page' ),                // Callback function (if dashboard is added).
				null,                                            // Replaced by submenu.
				'dashicons-rest-api',                            // Menu icon.
				$this->menu_order                                // Menu position.
			);

			$this->set_hook_suffixes( array( 'top' => $hook_suffix ) );
		}
	}

	/**
	 * Add Submenu to Admin Menu.
	 *
	 * @since 1.0.0
	 *
	 * @link https://developer.wordpress.org/reference/functions/add_submenu_page/
	 */
	abstract public function add_submenu();

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
		// Inside toplevel & shared between class submenus.
		if ( ! in_array( $current_screen->id, $this->get_hook_suffixes(), true ) ) {
			return;
		}

		// Inside class submenu(s) only.
		if ( ! in_array( $current_screen->id, $this->get_hook_suffixes( 'submenus' ), true ) ) {
			return;
		}

		global $post;
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 3, 2 );
		do_action( 'add_meta_boxes', $current_screen->id, $post );

		$screen_args = array(
			'max'     => 2,
			'default' => 2,
		);
		add_screen_option( 'layout_columns', $screen_args );
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

		$file_ext = gofer_seo_is_min_enabled() ? 'min.css' : 'css';
		// Styles that would be used on module screens.
		wp_register_style(
			'gofer-seo-screen-page-css',
			GOFER_SEO_URL . 'admin/css/screens/screen-page.' . $file_ext,
			array(),
			GOFER_SEO_VERSION,
			'all'
		);
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
		// Head scripts enqueue here.

		// Footer Scripts.

	}

	/**
	 * Localize Script Data.
	 *
	 * Localizes data after scripts have been registered and possibly enqueue.
	 * Localizing data is wrapped in wp_script_is() to reduce unnecessary processes/operations.
	 *
	 * @since 1.0.0
	 */
	public function localize_script() {
		parent::localize_script();
	}

	/**
	 * Add Meta Boxes.
	 *
	 * @since 1.0.0
	 *
	 * @link https://developer.wordpress.org/reference/functions/add_meta_box/
	 *
	 * @param mixed        $hook_suffix
	 * @param WP_Post|null $post
	 */
	public function add_meta_boxes( $hook_suffix, $post ) {
		$meta_box_typesets = $this->typesetter_admin->validate_meta_box_typesets( $this->get_meta_box_typesets() );
		$input_typesets    = $this->typesetter_admin->validate_input_typesets( $this->get_input_typesets() );
		$values            = $this->get_values();

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
				$this->hook_suffixes,          // Screen(s).
				$meta_box_typeset['context'],  // Context.
				$meta_box_typeset['priority'], // Priority.
				$callback_args                 // Callback Args.
			);
		}
	}

	/**
	 * Display Admin Page Content.
	 *
	 * @since 1.0.0
	 *
	 * @see add_submenu_page()
	 * @link https://developer.wordpress.org/reference/functions/do_meta_boxes/
	 *
	 * @param array $args Additional arguments to pass to template.
	 * @return void
	 */
	public function display_page( $args = array() ) {
		$meta_box_typesets = $this->typesetter_admin->validate_meta_box_typesets( $this->get_meta_box_typesets() );
		$input_typesets    = $this->typesetter_admin->validate_input_typesets( $this->get_input_typesets() );

		$page_args = array(
			'object' => array(
				// Add stuff to pass to 1st param in meta-box callback function.
				'input_typesets'    => $input_typesets, // TODO Check if this is still unused after admin screens are created.
				'meta_box_typesets' => $meta_box_typesets, // TODO Check if this is still unused after admin screens are created.
				'page_slug'         => $this->get_submenu_slug(),
//				'form_action'       => admin_url( 'admin.php?page=' . $this->get_submenu_slug() ),
			),
		);
		$args = wp_parse_args( $args, $page_args );
		gofer_seo_do_template( 'admin/screens/admin-page.php', $args );
	}

	/**
	 * Save Admin Page Settings.
	 *
	 * @since 1.0.0
	 */
	public function save_page_settings() {
		if (
				check_admin_referer( 'gofer_seo_screens_page', 'gofer_seo_nonce' ) &&
				! current_user_can( 'gofer_seo_access' )
		) {
			wp_die();
		}

		$values            = $this->get_values();
		$input_typesets    = $this->typesetter_admin->validate_input_typesets( $this->get_input_typesets() );
		$form_input_values = $this->get_input_post_values( $input_typesets );

//		$values = array_merge_recursive( $values, $form_input_values );
		$values = array_replace( $values, $form_input_values );

		$this->update_values( $values );

		if (
				isset( $_REQUEST['_wp_http_referer'] ) &&
				wp_safe_redirect( wp_unslash( $_REQUEST['_wp_http_referer'] ) )
		) {
			exit();
		} elseif ( wp_safe_redirect( 'admin.php?page=' . $this->get_submenu_slug() ) ) {
			exit;
		}
	}

}
