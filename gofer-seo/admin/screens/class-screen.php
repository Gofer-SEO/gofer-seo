<?php
/**
 * Admin Screen: Base
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

/**
 * Class Gofer_SEO_Screen
 *
 * @since 1.0.0
 */
abstract class Gofer_SEO_Screen {
	// TODO Create Typeset class to handle validation and setting/registering.

	/**
	 * Hook Suffixes.
	 *
	 * @since 1.0.0
	 *
	 * @var string[] $hook_suffixes The hook suffixes created from adding menus & submenus.
	 */
	protected $hook_suffixes = array();

	/**
	 * Screen IDs
	 *
	 * @since 1.0.0
	 *
	 * @var array $screen_ids The screen ids for individual pages; from adding submenus.
	 */
	protected $screen_ids = array();

	/**
	 * Typesetter for Admin.
	 *
	 * @since 1.0.0
	 *
	 * @var Gofer_SEO_Typesetter_Admin $typesetter_admin
	 */
	protected $typesetter_admin;

	/**
	 * Gofer_SEO_Screen constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->typesetter_admin = new Gofer_SEO_Typesetter_Admin();

		add_action( 'current_screen', array( $this, 'current_screen' ) );

		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		if ( true === $gofer_seo_options->options['modules']['general']['show_admin_bar'] ) {
			add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 9999 );
			add_action( 'admin_bar_menu', array( $this, 'admin_bar_submenu' ), 10000 );
		}
	}

	/**
	 * Sets the Class's $hook_suffixes field.
	 *
	 * @since 1.0.0
	 *
	 * @param string[] $hook_suffixes
	 */
	public function set_hook_suffixes( $hook_suffixes = array() ) {
		if ( ! is_array( $hook_suffixes ) ) {
			if ( ! is_string( $hook_suffixes ) ) {
				return;
			}

			$hook_suffixes = array( $hook_suffixes );
		}

		// Temp. store toplevel for unique array sort.
		$toplevel = '';
		$tmp_hook_suffixes = $this->hook_suffixes;
		if ( isset( $tmp_hook_suffixes['top'] ) ) {
			$toplevel = ( isset( $hook_suffixes['top'] ) ) ? $hook_suffixes['top'] : $tmp_hook_suffixes['top'];
			unset( $tmp_hook_suffixes['top'] );
			unset( $hook_suffixes['top'] );
		}

		$tmp_hook_suffixes = array_unique( array_replace( $tmp_hook_suffixes, $hook_suffixes ) );

		if ( ! empty( $toplevel ) ) {
			$tmp_hook_suffixes['top'] = $toplevel;
		}

		$this->hook_suffixes = $tmp_hook_suffixes;
	}

	/**
	 * Get Class's Hook Suffixes
	 *
	 * Return's the current set of `$hook_suffixes` values returned from `add_menu()` & `add_submenu()`.
	 * Toplevel & submenu.
	 *
	 * @since 1.0.0
	 *
	 * @param string $fields The values to return. Default: 'all'.
	 *                       Accepts 'top', 'menu', 'submenus', 'submenu', and 'all'.
	 * @return string[]
	 */
	public function get_hook_suffixes( $fields = 'all' ) {
		switch ( $fields ) {
			case 'top':
			case 'menu':
				return ( isset( $this->hook_suffixes['top'] ) ) ? array( 'top' => $this->hook_suffixes['top'] ) : array();
			case 'submenus':
			case 'submenu':
				$tmp_hook_suffixes = $this->hook_suffixes;
				unset( $tmp_hook_suffixes['top'] );
				return $tmp_hook_suffixes;
			case 'all':
			default:
				return $this->hook_suffixes;
		}
	}

	/**
	 * Set Screen Ids
	 *
	 * Sets the class's current set of screen ids.
	 *
	 * @since 1.0.0
	 *
	 * @param array $screen_ids
	 */
	public function set_screen_ids( $screen_ids = array() ) {
		if ( ! is_array( $screen_ids ) ) {
			if ( ! is_string( $screen_ids ) ) {
				return;
			}

			$screen_ids = array( $screen_ids );
		}

		// Caused screen ids to be overwritten.
		$this->screen_ids = array_unique( array_merge( $this->screen_ids, $screen_ids ) );
	}

	/**
	 * Get Class's Screen Ids
	 *
	 * Returns the screen ids returned from `add_submenu()`.
	 * Submenu screens only.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_screen_ids() {
		return $this->screen_ids;
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
	 * @param WP_Screen $current_screen
	 */
	public function current_screen( $current_screen ) {
		if ( ! in_array( $current_screen->id, $this->get_screen_ids(), true ) ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_register_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_register_scripts' ) );
	}

	/**
	 * Register/Enqueue Styles.
	 *
	 * @since 1.0.0
	 *
	 * @see 'admin_enqueue_scripts' hook
	 * @link https://developer.wordpress.org/reference/hooks/admin_enqueue_scripts/
	 * @see wp_register_style()
	 * @link https://developer.wordpress.org/reference/functions/wp_register_style/
	 *
	 * @param $hook_suffix
	 */
	public function admin_register_styles( $hook_suffix ) {
		if ( ! in_array( $hook_suffix, $this->get_hook_suffixes(), true ) ) {
			return;
		}

		// Styles that would be used on all screens.
		// Plugin logo, font-icons, etc.
		if ( ! wp_style_is( 'gofer-seo-bootstrap-css', 'registered' ) ) {
			wp_register_style(
				'gofer-seo-bootstrap-css',
				GOFER_SEO_URL . 'admin/css/bootstrap.css',
				array(),
				GOFER_SEO_VERSION,
				'all'
			);
		}

		/*
		 * TODO Add RTL adjustments.
		if ( function_exists( 'is_rtl' ) && is_rtl() ) {
			wp_enqueue_style(
				'gofer-seo-rtl-css',
				GOFER_SEO_URL . 'admin/css/gofer-seo-rtl.css',
				array(),
				GOFER_SEO_VERSION
			);
		}
		*/

		if ( ! in_array( $hook_suffix, $this->get_hook_suffixes( 'submenus' ), true ) ) {
			return;
		}

		wp_register_style(
			'gofer-seo-admin-bar-menu-css',
			GOFER_SEO_URL . 'admin/css/admin-bar-menu.css',
			array(),
			GOFER_SEO_VERSION,
			'all'
		);

		wp_register_style(
			'gofer-seo-input-types-css',
			GOFER_SEO_URL . 'admin/css/inputs/types.css',
			array(),
			GOFER_SEO_VERSION,
			'all'
		);

		wp_register_style(
			'gofer-seo-input-layouts-css',
			GOFER_SEO_URL . 'admin/css/inputs/layouts.css',
			array(),
			GOFER_SEO_VERSION,
			'all'
		);

		wp_register_style(
			'gofer-seo-tooltips-css',
			GOFER_SEO_URL . 'admin/css/inputs/tooltips.css',
			array(),
			GOFER_SEO_VERSION,
			'all'
		);

		wp_register_style(
			'gofer-seo-select2-css',
			GOFER_SEO_URL . 'assets/select2-v4.0.13/select2.css',
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
	 * @see 'admin_enqueue_scripts' hook
	 * @link https://developer.wordpress.org/reference/hooks/admin_enqueue_scripts/
	 *
	 * @param $hook_suffix
	 */
	public function admin_register_scripts( $hook_suffix ) {
		if ( ! in_array( $hook_suffix, $this->get_hook_suffixes(), true ) ) {
			return;
		}

		// WP prints at priority 20.
		add_action( 'admin_print_scripts', array( $this, 'localize_script' ), 15 );
		// WP prints at priority 10.
		add_action( 'admin_print_footer_scripts', array( $this, 'localize_script' ), 6 );

		// Scripts that would be used on all screens.
		// Notifications, top admin bar, etc.

		if ( ! in_array( $hook_suffix, $this->get_hook_suffixes( 'submenus' ), true ) ) {
			return;
		}

		if ( ! wp_script_is( 'gofer-seo-bootstrap-js', 'registered' ) ) {
			wp_register_script(
				'gofer-seo-bootstrap-js',
				GOFER_SEO_URL . 'assets/bootstrap-v5.1.3/bootstrap.js',
				array(
					'jquery',
				),
				GOFER_SEO_VERSION,
				true
			);
		}

		wp_register_script(
			'gofer-seo-screens-meta-box-js',
			GOFER_SEO_URL . 'admin/js/screens/meta-box.js',
			array(
				'jquery',
				'postbox',
			),
			GOFER_SEO_VERSION,
			true
		);

		wp_register_script(
			'gofer-seo-tooltips-js',
			GOFER_SEO_URL . 'admin/js/inputs/tooltips.js',
			array(
				'jquery',
				'jquery-ui-core',
				'jquery-ui-widget',
				'jquery-ui-position',
				'jquery-ui-tooltip',
			),
			GOFER_SEO_VERSION,
			true
		);

		wp_register_script(
			'gofer-seo-inputs-input-conditions-js',
			GOFER_SEO_URL . 'admin/js/inputs/input-conditions.js',
			array(
				'jquery',
			),
			GOFER_SEO_VERSION,
			true
		);

		wp_register_script(
			'gofer-seo-input-type-image-media-js',
			GOFER_SEO_URL . 'admin/js/inputs/types/image-media.js',
			array(
				'jquery',
			),
			GOFER_SEO_VERSION,
			true
		);

		wp_register_script(
			'gofer-seo-input-type-add-field-list-js',
			GOFER_SEO_URL . 'admin/js/inputs/types/add-field-list.js',
			array(),
			GOFER_SEO_VERSION,
			true
		);

		wp_register_script(
			'gofer-seo-select2-js',
			GOFER_SEO_URL . 'assets/select2-v4.0.13/select2.js',
			array(
				'jquery',
			),
			GOFER_SEO_VERSION,
			true
		);

		wp_register_script(
			'gofer-seo-input-type-select2-multi-select-js',
			GOFER_SEO_URL . 'admin/js/inputs/types/select2-multi-select.js',
			array(
				'jquery',
				'gofer-seo-select2-js',
			),
			GOFER_SEO_VERSION,
			true
		);
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
		global $hook_suffix;

		if (
				wp_script_is( 'gofer-seo-inputs-input-conditions-js', 'enqueued' ) &&
				! wp_script_is( 'gofer-seo-inputs-input-conditions-js', 'done' ) &&
				in_array( $hook_suffix, $this->get_hook_suffixes( 'submenus' ), true )
		) {
			$input_typesets   = $this->typesetter_admin->validate_input_typesets( $this->get_input_typesets() );
			$input_conditions = $this->get_inputs_conditions( $input_typesets );

			$screens_input_show_l10n = array(
				'input_conditions' => $input_conditions,
			);
			wp_localize_script( 'gofer-seo-inputs-input-conditions-js', 'gofer_seo_l10n_data', $screens_input_show_l10n );
		}

		if (
				wp_script_is( 'gofer-seo-input-type-add-field-list-js', 'enqueued' ) &&
				! wp_script_is( 'gofer-seo-input-type-add-field-list-js', 'done' ) &&
				in_array( $hook_suffix, $this->get_hook_suffixes( 'submenus' ), true )
		) {
			$input_typesets = ( isset( $input_typesets ) ) ? $input_typesets : $this->typesetter_admin->validate_input_typesets( $this->get_input_typesets() );

			$add_field_list_typesets = $this->typesetter_admin->get_add_field_list_typesets( $input_typesets );
			$add_field_list_l10n = array(
				'input_name_typesets' => $add_field_list_typesets,
			);
			wp_localize_script( 'gofer-seo-input-type-add-field-list-js', 'gofer_seo_l10n_add_field_list', $add_field_list_l10n );
		}
	}

	/**
	 * Admin Bar Menu.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 */
	public function admin_bar_menu( $wp_admin_bar ) {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		if ( false === $gofer_seo_options->options['modules']['general']['show_admin_bar'] ) {
			return;
		}

		wp_enqueue_style( 'gofer-seo-admin-bar-menu-css' );

		if ( ! is_admin() ) {
			$title = '<span class="ab-icon dashicons-before dashicons-rest-api"></span><span class="ab-label">' . __( 'Gofer SEO', 'gofer-seo' ) . '</span>';
			$wp_admin_bar->add_menu(
				array(
					'id'    => GOFER_SEO_NICENAME,
					'title' => $title,
					'href'  => admin_url( 'admin.php?page=gofer_seo' ),
					'meta'  => array(
						'class' => 'gofer-seo-admin-bar-menu',
					),
				)
			);
		} else {
			$title = '<span class="ab-icon dashicons-before dashicons-rest-api"></span><span class="ab-label">' . __( 'SEO Settings', 'gofer-seo' ) . '</span>';
			$wp_admin_bar->add_menu(
				array(
					'id'    => GOFER_SEO_NICENAME . '-settings',
					'title' => $title,
					'href'  => admin_url( 'admin.php?page=gofer_seo' ),
					'meta'  => array(
						'class' => 'gofer-seo-admin-bar-menu',
					),
				)
			);
		}
	}

	/**
	 * Admin Bar (Sub) Menu.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 */
	public function admin_bar_submenu( $wp_admin_bar ) {
		// Child classes add submenus.
		if ( ! is_admin() ) {
			$wp_admin_bar->add_menu(
				array(
					'id'     => GOFER_SEO_NICENAME . '-settings',
					'parent' => GOFER_SEO_NICENAME,
					'title'  => __( 'SEO Settings', 'gofer-seo' ),
					'href'   => admin_url( 'admin.php?page=gofer_seo' ),
					'meta'   => array(
						'class' => 'gofer-seo-admin-bar-submenu',
					),
				)
			);
		}
	}

	/* **__________****************************************************************************************************/
	/* _/ Typesets \__________________________________________________________________________________________________*/
	/*
	 * These could potentially be moved to a class designed to deal with typesets, and would
	 * significantly reduce the number of lines.
	 *
	 * However, it may cause confusion by separating the typeset variables used within a given class.
	 *
	 * Currently, the main typesets are...
	 * - Admin Pages (this).
	 * - Options.
	 * - Post Meta.
	 */

	/**
	 * Get Input's Show Conditions.
	 *
	 * Fetches all the input's nested 'conditions' variable and stores it as a single dimensional array with
	 * the array index associated to the input name.
	 *
	 * Since 1.0.0
	 *
	 * @see Gofer_SEO_Screen_Page::get_input_typesets() For more details on return data.
	 *
	 * @param array  $input_typesets See self::get_input_typesets().
	 * @param string $prefix         Used to prefix the array keys. Primarily used for recursive operations.
	 * @return array ${INPUT_KEY[-$CHILD_KEY[-$CHILD_KEY]]} {
	 *     @type string $action   What action will be taken if true. Default: 'hide'.
	 *                            Accepts 'show', 'hide', and 'disable'.
	 *     @type string $relation Compare relation between each conditions. Default: 'AND'.
	 *                            Accepts 'AND', and 'OR'.
	 *     @type array {
	 *         @type string $left_var    Left variable.
	 *         @type string $operator    Compare operator.
	 *         @type string $right_var   Right variable.
	 *         @type string $right_value Right value.
	 *     }
	 * }
	 */
	protected function get_inputs_conditions( $input_typesets, $prefix = '' ) {
		$input_show = array();
		// Add '-' delimiter for nested/wrap inputs.
		if ( ! empty( $prefix ) ) {
			$prefix .= '-';
		}

		foreach ( $input_typesets as $k1_input_name => $v1_input_typeset ) {
			$name = $prefix . $k1_input_name;

			// Handle wraps with recursive operations.
			switch ( $v1_input_typeset['type'] ) {
				case 'wrap':
				case 'tabs':
				case 'tab':
				case 'table-form-table':
					$wrap_input_show = $this->get_inputs_conditions( $v1_input_typeset['wrap'], $name );
					if ( ! empty( $wrap_input_show ) ) {
//						$input_show = array_merge_recursive( $input_show, $wrap_input_show );
						$input_show = array_replace_recursive( $input_show, $wrap_input_show );
					}

					break;
				case 'wrap_dynamic':
				case 'list-table':
					$tmp_input_typesets = $this->typesetter_admin->validate_input_typesets( $this->get_input_typesets() );
					foreach ( $v1_input_typeset['items'] as $k2_item_slug => $v2_item ) {
						$name_item = $name . '-' . $k2_item_slug;

						if ( ! empty( $v1_input_typeset['item_conditions'][ $k2_item_slug ] ) ) {
							$input_show[ $name_item ] = $v1_input_typeset['item_conditions'][ $k2_item_slug ];
						}

						$wrap_input_show = $this->get_inputs_conditions( $v1_input_typeset['wrap_dynamic'], $name_item );

						// Check if target input exists, or is intended for sibling input.

						foreach ( $wrap_input_show as $k3_html_input_name => $v3_input_conditions ) {
							foreach ( $v3_input_conditions as $k4_target_input_name => $condition ) {
								if ( in_array( $k4_target_input_name, array( 'action', 'relation' ), true ) ) {
									continue;
								}

								$matches1 = array();
								preg_match_all( '/([a-z_]+)(?:-)*/', $k4_target_input_name, $matches1 );
								$tmp1_input_typesets = $tmp_input_typesets;
								foreach ( $matches1[1] as $match1 ) {
									if ( isset( $tmp1_input_typesets[ $match1 ] ) ) {
										// Check if target exists.
										$tmp1_input_typesets = $tmp1_input_typesets[ $match1 ];
									} else {
										// Target doesn't exist.
										unset( $wrap_input_show[ $k3_html_input_name ][ $k4_target_input_name ] );

										// Check if sibling exists.
										$sibling_target_input_name = $name_item . '-' . $k4_target_input_name;
										$tmp2_input_typesets       = $tmp_input_typesets;
										$matches2                  = array();
										preg_match_all( '/([a-z_]+)(?:-)*/', $sibling_target_input_name, $matches2 );
										foreach ( $matches2[1] as $match2 ) {
											if (
												isset( $tmp2_input_typesets['items'] ) &&
												in_array( $match2, array_keys( $tmp2_input_typesets['items'] ), true )
											) {
												if ( isset( $tmp2_input_typesets['wrap'] ) ) {
													$tmp2_input_typesets = $tmp2_input_typesets['wrap'];
												} elseif ( isset( $tmp2_input_typesets['wrap_dynamic'] ) ) {
													$tmp2_input_typesets = $tmp2_input_typesets['wrap_dynamic'];
												}
											} elseif ( isset( $tmp2_input_typesets[ $match2 ] ) ) {
												$tmp2_input_typesets = $tmp2_input_typesets[ $match2 ];
											} else {
												break 2;
											}
										}

										$condition['left_var'] = $sibling_target_input_name;
										$wrap_input_show[ $k3_html_input_name ][ $sibling_target_input_name ] = $condition;

										break;
									}
								}
							}
						}

						if ( ! empty( $wrap_input_show ) ) {
//							$input_show = array_merge_recursive( $input_show, $wrap_input_show );
							$input_show = array_replace_recursive( $input_show, $wrap_input_show );
						}
					}

					break;
			}

			// Store show data.
			if ( isset( $v1_input_typeset['conditions'] ) ) {
				$input_show[ $name ] = $v1_input_typeset['conditions'];
			}
		}

		return $input_show;
	}

	/* **___________***************************************************************************************************/
	/* _/ Functions \_________________________________________________________________________________________________*/

	/**
	 * Get input values from $_POST.
	 *
	 * Recursive.
	 *
	 * @since 1.0.0
	 *
	 * @see Gofer_SEO_Screen_Page::get_input_typesets() For more details on `$input_typesets`.
	 *
	 * @param array  $input_typesets    Typesets for casting inputs.
	 * @param string $input_prefix_name Adds an input prefix. Used by recursive call.
	 * @return array
	 */
	protected function get_input_post_values( $input_typesets, $input_prefix_name = '' ) {
		$values = array();
		if ( ! empty( $input_prefix_name ) ) {
			$input_prefix_name .= '-';
		}
		foreach ( $input_typesets as $input_slug => $input_typeset ) {
			$input_name = $input_prefix_name . $input_slug;

			switch ( $input_typeset['type'] ) {
				case 'wrap':
				case 'tabs':
				case 'tab':
				case 'table-form-table':
					$values[ $input_slug ] = $this->get_input_post_values( $input_typeset['wrap'], $input_name );
					break;
				case 'wrap_dynamic':
					$values[ $input_slug ] = array();
					foreach ( $input_typeset['items'] as $item_slug => $item ) {
						$input_name_item = $input_name . '-' . $item_slug;
						$values[ $input_slug ][ $item_slug ] = $this->get_input_post_values( $input_typeset['wrap_dynamic'], $input_name_item );
					}
					break;
				default:
					$values[ $input_slug ] = $this->get_input_post_value( $input_name, $input_typeset );
			}
		}

		return $values;
	}

	/**
	 * Get input value from $_POST.
	 *
	 * @since 1.0.0
	 *
	 * @see Gofer_SEO_Screen_Page::get_input_typesets() For more details on `$input_typeset`.
	 *
	 * @param string $input_name    The input name
	 * @param array  $input_typeset Typeset for casting inputs.
	 * @return mixed
	 */
	protected function get_input_post_value( $input_name, $input_typeset ) {
		$value = '';
		check_admin_referer( 'gofer_seo_screens_page', 'gofer_seo_nonce' );

		switch ( $input_typeset['type'] ) {
			case 'multi-checkbox':
				$value = array();
				if ( isset( $_POST[ $input_name ] ) ) {
					if ( defined( 'GOFER_SEO_UNIT_TESTING' ) && true === GOFER_SEO_UNIT_TESTING ) {
						$tmp_value_arr = sanitize_text_field( wp_unslash( $_POST[ $input_name ] ) );
					} else {
						$tmp_value_arr = filter_input( INPUT_POST, $input_name, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
						$tmp_value_arr = array_combine(
							array_map( 'strval', $tmp_value_arr ),
							array_map(
								function( $value ) {
									return ( false === $value ) ? false : true;
								},
								$tmp_value_arr
							)
						);
					}
				}

				foreach ( $input_typeset['items'] as $item_slug => $item_title ) {
					$tmp_value = false;
					if ( isset( $tmp_value_arr[ $item_slug ] ) ) {
						$tmp_value = $tmp_value_arr[ $item_slug ];
					}

					// Check for boolean values, else keep the value in checkbox input (multi-checkbox).
					if ( 'on' === $tmp_value || 'true' === $tmp_value ) {
						$tmp_value = true;
					} elseif ( empty( $tmp_value ) || 'off' === $tmp_value ) {
						$tmp_value = false;
					}

					$value[ $item_slug ] = $tmp_value;
				}
				break;
			case 'checkbox':
				$value = false;
				if ( isset( $_POST[ $input_name ] ) ) {
					if ( defined( 'GOFER_SEO_UNIT_TESTING' ) && true === GOFER_SEO_UNIT_TESTING ) {
						$value = sanitize_text_field( wp_unslash( $_POST[ $input_name ] ) );
					} else {
						$value = filter_input( INPUT_POST, $input_name, FILTER_SANITIZE_STRING );
					}
				}

				// Check for boolean values, else keep the value in checkbox input (multi-checkbox).
				if ( 'on' === $value || 'true' === $value ) {
					$value = true;
				} elseif ( empty( $value ) || 'off' === $value ) {
					$value = false;
				}
				break;
			case 'add-field-list':
			case 'add-field-robots-txt':
				if ( isset( $_POST[ $input_name ] ) ) {
					// When PHPUnit is unable to use filter_input.
					if ( defined( 'GOFER_SEO_UNIT_TESTING' ) && true === GOFER_SEO_UNIT_TESTING ) {
						$value = sanitize_text_field( wp_unslash( $_POST[ $input_name ] ) );
					} else {
						$value = filter_input( INPUT_POST, $input_name, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES );
						//$value = filter_input( INPUT_POST, $input_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES );
					}

					$value = json_decode( $value, true );
				}
				break;
			case 'select2-multi-select':
				$tmp_value_arr = filter_input( INPUT_POST, $input_name, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

				$value = array();
				if ( ! is_null( $tmp_value_arr ) ) {
					$value = array_combine( $tmp_value_arr, $tmp_value_arr );
				}
				break;
			default:
				if ( isset( $_POST[ $input_name ] ) ) {
					// When PHPUnit is unable to use filter_input.
					if ( defined( 'GOFER_SEO_UNIT_TESTING' ) && true === GOFER_SEO_UNIT_TESTING ) {
						return sanitize_text_field( wp_unslash( $_POST[ $input_name ] ) );
					}

					switch ( $input_typeset['type'] ) {
						case 'url':
							$value = filter_input( INPUT_POST, $input_name, FILTER_SANITIZE_URL );
							break;
						case 'number':
							$value = filter_input( INPUT_POST, $input_name, FILTER_SANITIZE_NUMBER_INT );
							break;
						default:
							$value = filter_input( INPUT_POST, $input_name, FILTER_SANITIZE_STRING );
					}
				}
		}

		return $value;
	}
}
