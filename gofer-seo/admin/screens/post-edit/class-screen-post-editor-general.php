<?php
/**
 * Admin Screen: Post Editor - General
 *
 * @package Gofer SEO
 * @since   1.0.0
 */

/**
 * Class Gofer_SEO_Screen_Post_Editor_General
 *
 * @since 1.0.0
 */
class Gofer_SEO_Screen_Post_Editor_General extends Gofer_SEO_Screen_Post_Editor {

	/**
	 * Gofer_SEO_Screen_Post_Editor_General constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'wp_ajax_gofer_seo_post_editor_quick_edit', array( $this, 'ajax_quick_edit_save' ) );

		add_action( 'admin_init', array( $this, 'admin_init_manage_custom_columns' ) );
	}

	/**
	 * Admin Init - Manage Custom Columns Hooks.
	 *
	 * @since 1.0.0
	 */
	public function admin_init_manage_custom_columns() {
		global $pagenow;

		// Get Post Type.
		$post_type = '';
		if ( 'admin-ajax.php' === $pagenow ) {
			if ( isset( $_REQUEST['action'] ) ) {
				if ( 'gofer_seo_post_editor_quick_edit' === $_REQUEST['action'] ) {
					check_ajax_referer( 'gofer_seo_quick_edit' );

					$post_id = 0;
					if ( isset( $_REQUEST['post_id'] ) ) {
						$post_id = filter_var( wp_unslash( $_REQUEST['post_id'] ), FILTER_SANITIZE_NUMBER_INT );
					}
					$wp_obj = Gofer_SEO_Context::get_object( 'WP_Post', $post_id );

					$post_type = $wp_obj->post_type;
				} elseif ( 'inline-save' === $_REQUEST['action'] ) {
					$post_id = 0;
					if ( isset( $_REQUEST['post_ID'] ) ) {
						$post_id = filter_var( wp_unslash( $_REQUEST['post_ID'] ), FILTER_SANITIZE_NUMBER_INT );
					}
					$wp_obj = Gofer_SEO_Context::get_object( 'WP_Post', $post_id );

					$post_type = $wp_obj->post_type;
				}
			}
		} elseif ( 'edit.php' === $pagenow ) {
			$post_type = 'post';
		} elseif ( 'upload.php' === $pagenow ) {
			$post_type = 'attachment';
		}

		// Check enabled show on post types.
		if ( ! in_array( $post_type, $this->get_active_post_types(), true ) ) {
			return;
		}

		switch ( $post_type ) {
			case 'attachment':
				add_filter( 'manage_media_columns', array( $this, 'posts_columns' ) );
				add_action( 'manage_media_custom_column', array( $this, 'custom_column' ), 10, 2 );
				break;
			case 'page':
				add_filter( 'manage_pages_columns', array( $this, 'posts_columns' ) );
				add_action( 'manage_pages_custom_column', array( $this, 'custom_column' ), 10, 2 );
				break;
			default:
				add_filter( 'manage_posts_columns', array( $this, 'posts_columns' ) );
				if ( is_post_type_hierarchical( $post_type ) ) {
					add_action( 'manage_pages_custom_column', array( $this, 'custom_column' ), 10, 2 );
				} else {
					add_action( 'manage_posts_custom_column', array( $this, 'custom_column' ), 10, 2 );
				}
		}
	}

	/**
	 * Action - Current Screen
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Screen $current_screen
	 */
	public function current_screen( $current_screen ) {
		parent::current_screen( $current_screen );
		if ( ! in_array( $current_screen->post_type, $this->get_active_post_types(), true ) ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_register_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_register_scripts' ) );
	}

	/**
	 * Get Active Post-Types/Screens.
	 *
	 * This checks if the post type is enabled, and if show meta-box is enabled, and
	 * returns an array of post type slugs.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_active_post_types() {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		$active_post_types = array_filter( $gofer_seo_options->options['modules']['general']['enable_post_types'] );
		$active_post_types = array_keys( $active_post_types );

		foreach ( $active_post_types as $index => $post_type ) {
			if ( false === $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post_type ]['enable_editor_meta_box'] ) {
				unset( $active_post_types[ $index ] );
			}
		}
		$active_post_types = array_values( $active_post_types );

		return $active_post_types;
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
	public function get_input_typesets( $input_typesets ) {
		global $post_type;
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		$default_noindex_value = '';
		if ( isset( $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post_type ]['enable_noindex'] ) ) {
			if ( false === $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post_type ]['enable_noindex'] ) {
				$default_noindex_value = ' (disabled)';
			} elseif ( true === $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post_type ]['enable_noindex'] ) {
				$default_noindex_value = ' (enabled)';
			}
		}
		$default_nofollow_value = '';
		if ( isset( $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post_type ]['enable_nofollow'] ) ) {
			if ( false === $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post_type ]['enable_nofollow'] ) {
				$default_nofollow_value = ' (disabled)';
			} elseif ( true === $gofer_seo_options->options['modules']['general']['post_type_settings'][ $post_type ]['enable_nofollow'] ) {
				$default_nofollow_value = ' (enabled)';
			}
		}


		$general_enabled_post_types = $this->get_active_post_types();
		$module_typeset             = array(
			'title'      => __( 'General', 'gofer-seo' ),
			'type'       => 'tab',
			'wrap'       => array(
				'snippet'              => array(
					'title'      => __( 'Snippet', 'gofer-seo' ),
					'input_type' => 'snippet-default',
					'layout'     => 'input-row',
				),
				'title'                => array(
					'title'      => __( 'Title', 'gofer-seo' ),
					'input_type' => 'text',
				),
				'description'          => array(
					'title'      => __( 'Description', 'gofer-seo' ),
					'input_type' => 'textarea',
					'attrs'      => array(
						'rows' => 3,
					),
				),
				'keywords'             => array(
					'title'      => __( 'Keywords', 'gofer-seo' ),
					'input_type' => 'text',
				),
				'custom_link'          => array(
					'title'      => __( 'Custom Link', 'gofer-seo' ),
					'input_type' => 'text',
				),
				'enable_noindex'       => array(
					'title' => __( 'NoIndex', 'gofer-seo' ),
					'type'  => 'select',
					'items' => array(
						-1 => __( 'Use Default', 'gofer-seo' ) . $default_noindex_value,
						0  => __( 'Disable', 'gofer-seo' ),
						1  => __( 'Enable', 'gofer-seo' ),
					),
					'esc'   => array(
						array( 'intval' ),
					),
				),
				'enable_nofollow'      => array(
					'title' => __( 'NoFollow', 'gofer-seo' ),
					'type'  => 'select',
					'items' => array(
						-1 => __( 'Use Default', 'gofer-seo' ) . $default_nofollow_value,
						0  => __( 'Disable', 'gofer-seo' ),
						1  => __( 'Enable', 'gofer-seo' ),
					),
					'esc'   => array(
						array( 'intval' ),
					),
				),
				'disable_analytics'    => array(
					'title' => __( 'Disable Analytics', 'gofer-seo' ),
					'type'  => 'checkbox',
				),
				'enable_force_disable' => array(
					'title' => __( 'Force Disable SEO', 'gofer-seo' ),
					'type'  => 'checkbox',
				),
			),
			'conditions' => array(
				'post_type' => array(
					'operator'    => '===',
					'right_value' => $general_enabled_post_types,
				),
			),
		);

		// Set Module Tabs if not yet set.
		if ( ! isset( $input_typesets['gofer_seo_modules'] ) ) {
			$input_typesets['gofer_seo_modules'] = array(
				'title'      => __( 'Modules', 'gofer-seo' ),
				'type'       => 'tabs',
				'wrap'       => array(),
				'conditions' => array(),
				'layout'     => 'input-row',
			);
		}

		if ( isset( $input_typesets['gofer_seo_modules']['wrap']['general'] ) ) {
			$input_typesets['gofer_seo_modules']['wrap']['general'] = array_replace( $input_typesets['gofer_seo_modules']['wrap']['general'], $module_typeset );
		} else {
			$input_typesets['gofer_seo_modules']['wrap']['general'] = $module_typeset;
		}

		return $input_typesets;
	}

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
	public function get_meta_box_typesets( $meta_box_typesets ) {
		$general_enabled_post_types = $this->get_active_post_types();
		if ( ! isset( $meta_box_typesets['gofer_seo'] ) ) {
			$meta_box_typesets['gofer_seo'] = array(
				'title'    => __( 'Gofer SEO', 'gofer-seo' ),
				'context'  => 'normal',
				'priority' => 'default',
				'screens'  => $general_enabled_post_types,
				'inputs'   => array(
					'gofer_seo_modules',
				),
			);
		} else {
			$meta_box_typesets['gofer_seo']['screens'] = array_replace(
				$meta_box_typesets['gofer_seo']['screens'],
				$general_enabled_post_types
			);
		}

		if ( ! in_array( 'gofer_seo_modules', $meta_box_typesets['gofer_seo']['inputs'], true ) ) {
			$meta_box_typesets['gofer_seo']['inputs'][] = 'gofer_seo_modules';
		}

		return $meta_box_typesets;
	}

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
	public function get_values( $values ) {
		global $post;

		$gofer_seo_post = new Gofer_SEO_Post( $post );

		if ( ! isset( $values['gofer_seo_modules'] ) ) {
			$values['gofer_seo_modules'] = array();
		}
		if ( ! isset( $values['gofer_seo_modules']['general'] ) ) {
			$values['gofer_seo_modules']['general'] = array();
		}

		$values['gofer_seo_modules']['general']['snippet'] = '';

		$values['gofer_seo_modules']['general'] = array_replace( $values['gofer_seo_modules']['general'], $gofer_seo_post->meta['modules']['general'] );

		return $values;
	}

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
	public function update_values( $values ) {
		global $post;

		$gofer_seo_post = new Gofer_SEO_Post( $post );

		if ( isset( $values['gofer_seo_modules']['general']['snippet'] ) ) {
			unset( $values['gofer_seo_modules']['general']['snippet'] );
		}

		$gofer_seo_post->meta['modules']['general'] = array_replace_recursive(
			$gofer_seo_post->meta['modules']['general'],
			$values['gofer_seo_modules']['general']
		);

		$results = $gofer_seo_post->update_meta();

		return $values;
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
		wp_register_style(
			'gofer-seo-quick-edit-css',
			GOFER_SEO_URL . 'admin/css/quick-edit.css',
			array(),
			GOFER_SEO_VERSION,
			'all'
		);

		wp_register_style(
			'gofer-seo-input-type-snippet-default-css',
			GOFER_SEO_URL . 'admin/css/inputs/types/snippet-default.css',
			array(),
			GOFER_SEO_VERSION,
			'all'
		);

		wp_enqueue_style( 'gofer-seo-quick-edit-css' );
	}

	/**
	 * Register/Enqueue Scripts.
	 *
	 * @since 1.0.0
	 *
	 * @see 'admin_enqueue_scripts' hook
	 * @link https://developer.wordpress.org/reference/hooks/admin_enqueue_scripts/
	 * @see wp_register_script()
	 * @link https://developer.wordpress.org/reference/functions/wp_register_script/
	 *
	 * @param $hook_suffix
	 */
	public function admin_register_scripts( $hook_suffix ) {
		wp_register_script(
			'gofer-seo-quick-edit-js',
			GOFER_SEO_URL . 'admin/js/quick-edit.js',
			array( 'jquery' ),
			GOFER_SEO_VERSION,
			true
		);

		wp_register_script(
			'gofer-seo-input-type-snippet-default-js',
			GOFER_SEO_URL . 'admin/js/inputs/types/snippet-default.js',
			array(),
			GOFER_SEO_VERSION,
			true
		);

		wp_enqueue_script( 'gofer-seo-quick-edit-js' );

		// WP prints at priority 20.
		add_action( 'admin_print_scripts', array( $this, 'localize_script' ), 15 );
		// WP prints at priority 10.
		add_action( 'admin_print_footer_scripts', array( $this, 'localize_script' ), 6 );


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
		if (
			wp_script_is( 'gofer-seo-quick-edit-js', 'enqueued' ) &&
			! wp_script_is( 'gofer-seo-quick-edit-js', 'done' )
		) {
			$quick_edit_l10n = array(
				'admin_images_URL' => GOFER_SEO_ADMIN_IMAGES_URL,
				'nonce'            => wp_create_nonce( 'gofer_seo_quick_edit' ),
				'i18n'             => array(
					'save'    => __( 'Save', 'gofer-seo' ),
					'cancel'  => __( 'Cancel', 'gofer-seo' ),
					'wait'    => __( 'Please wait...', 'gofer-seo' ),
					'noValue' => __( 'No value', 'gofer-seo' ),
				),
			);

			wp_localize_script( 'gofer-seo-quick-edit-js', 'gofer_seo_l10n_post_editor_general', $quick_edit_l10n );
		}
		if (
			wp_script_is( 'gofer-seo-input-type-snippet-default-js', 'enqueued' ) &&
			! wp_script_is( 'gofer-seo-input-type-snippet-default-js', 'done' )
		) {
			$gofer_seo_options = Gofer_SEO_Options::get_instance();

			$snippet_l10n = array(
				'site_url'                              => site_url(),
				'generate_description_enable_generator' => $gofer_seo_options->options['modules']['general']['generate_description']['enable_generator'],
				'generate_description_use_content'      => $gofer_seo_options->options['modules']['general']['generate_description']['use_content'],
				'generate_description_use_excerpt'      => $gofer_seo_options->options['modules']['general']['generate_description']['use_excerpt'],
				'enable_trim_description'               => $gofer_seo_options->options['modules']['general']['enable_trim_description'],
			);

			wp_localize_script( 'gofer-seo-input-type-snippet-default-js', 'gofer_seo_l10n_snippet', $snippet_l10n );
		}
	}

	/**
	 * Posts Columns.
	 *
	 * @since 1.0.0
	 *
	 * @param string[] $posts_columns An array of columns displayed in the Media list table.
	 * @return mixed
	 */
	public function posts_columns( $posts_columns ) {
		$posts_columns['gofer_seo_title']       = __( 'SEO Title', 'gofer-seo' );
		$posts_columns['gofer_seo_description'] = __( 'SEO Description', 'gofer-seo' );
		$posts_columns['gofer_seo_keywords']    = __( 'SEO Keywords', 'gofer-seo' );

		return $posts_columns;
	}

	/**
	 * Custom Column.
	 *
	 * @since 1.0.0
	 *
	 * @param string $column_name The name of the column to display.
	 * @param int    $post_id     The current post ID.
	 */
	public function custom_column( $column_name, $post_id ) {
		$gofer_seo_columns = array(
			'gofer_seo_title',
			'gofer_seo_description',
			'gofer_seo_keywords',
		);
		if ( ! in_array( $column_name, $gofer_seo_columns, true ) ) {
			return;
		}

		$gofer_seo_post = new Gofer_SEO_Post( $post_id );
		$value = '';
		switch ( $column_name ) {
			case 'gofer_seo_title':
				$value = $gofer_seo_post->meta['modules']['general']['title'];
				break;
			case 'gofer_seo_description':
				$value = $gofer_seo_post->meta['modules']['general']['description'];
				break;
			case 'gofer_seo_keywords':
				$value = $gofer_seo_post->meta['modules']['general']['keywords'];
				break;
		}

		if ( empty( $value ) ) {
			$value = sprintf( '<strong>%s</strong>', __( 'No value', 'gofer-seo' ) );
		}
		?>
		<div
				id="gofer_seo_<?php echo esc_attr( $column_name ) . '_' . esc_attr( $post_id ); ?>"
				class="gofer-seo-quick-edit-wrap"
				data-column-name="<?php echo esc_attr( $column_name ); ?>"
				data-post-id="<?php echo esc_attr( $post_id ); ?>"
		>
			<a
					class="dashicons dashicons-edit gofer-seo-quick-edit-pencil"
					title="<?php esc_attr_e( 'Edit', 'gofer-seo' ); ?>"
			>
			</a>
			<span id='gofer_seo_<?php echo esc_attr( $column_name ) . '_' . esc_attr( $post_id ); ?>_value'><?php echo wp_kses_post( trim( $value ) ); ?></span>
		</div>
		<?php


	}

	/**
	 * AJAX Quick-Edit Save.
	 *
	 * @since 1.0.0
	 */
	public function ajax_quick_edit_save() {
		check_ajax_referer( 'gofer_seo_quick_edit' );
		if ( ! current_user_can( 'gofer_seo_access' ) ) {
			wp_send_json_error( __( 'User doesn\'t have `gofer_seo_access` capabilities.', 'gofer-seo' ) );
		}

		$post_id     = null;
		$column_name = null;
		$value       = null;

		// Get Post ID.
		if ( isset( $_POST['post_id'] ) ) {
			$post_id = filter_input( INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT );
			$post_id = intval( $post_id );
		}
		if ( empty( $post_id ) ) {
			wp_send_json_error( __( 'Quick-Edit AJAX is missing the Post ID.', 'gofer-seo' ) );
		}

		// Check edit_post capabilities.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( __( 'User doesn\'t have `edit_post` capabilities.', 'gofer-seo' ) );
		}

		// Get Column Name.
		if ( isset( $_POST['columnName'] ) ) {
			$column_name = filter_input( INPUT_POST, 'columnName', FILTER_SANITIZE_STRING );
		}
		if ( empty( $column_name ) ) {
			wp_send_json_error( __( 'Quick-Edit AJAX is missing the Column Name.', 'gofer-seo' ) );
		}

		// Get Value.
		if ( isset( $_POST['value'] ) ) {
			$value = filter_input( INPUT_POST, 'value', FILTER_SANITIZE_STRING );
			$value = sanitize_text_field( $value );
		}
		if ( null === $value ) {
			wp_send_json_error( __( 'Quick-Edit AJAX is missing the input Value.', 'gofer-seo' ) );
		}

		$gofer_seo_post = new Gofer_SEO_Post( $post_id );

		switch ( $column_name ) {
			case 'gofer_seo_title':
				$gofer_seo_post->meta['modules']['general']['title'] = $value;
				$gofer_seo_post->update_meta();
				break;
			case 'gofer_seo_description':
				$gofer_seo_post->meta['modules']['general']['description'] = $value;
				$gofer_seo_post->update_meta();
				break;
			case 'gofer_seo_keywords':
				$gofer_seo_post->meta['modules']['general']['keywords'] = $value;
				$gofer_seo_post->update_meta();
				break;
		}

		gofer_seo_ajax_success( 'gofer_seo_post_editor_quick_edit' );
	}

	public function get_preview_snippet() {
		$args = array();

		$html = gofer_seo_get_template_html( 'templates/admin/inputs/types/snippet-default.php' );
	}
}
