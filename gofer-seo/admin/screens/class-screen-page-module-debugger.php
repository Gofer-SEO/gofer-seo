<?php
/**
 * Admin Screen: Page-Module - Debugger
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

/**
 * Class Gofer_SEO_Screen_Page_Module_Debugger
 *
 * @since 1.0.0
 */
class Gofer_SEO_Screen_Page_Module_Debugger extends Gofer_SEO_Screen_Page_Module {

	/**
	 * Gofer_SEO_Screen_Page_Module_Debugger constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'wp_ajax_gofer_seo_module_debugger_delete_errors', array( $this, 'ajax_delete_errors' ) );
		add_action( 'wp_ajax_gofer_seo_module_debugger_clear_cache', array( $this, 'ajax_clear_cache' ) );
	}

	/**
	 * Get Module Slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_module_slug() {
		return 'debugger';
	}

	/**
	 * Get Submenu Slug.
	 *
	 * @since 1.0.0
	 */
	public function get_submenu_slug() {
		return 'gofer_seo_module_debugger';
	}

	/**
	 * Get Menu Title.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_menu_title() {
		return 'Debugger';
	}

	/**
	 * The Input Typesets (Params/Configuration)
	 *
	 * @since 1.0.0
	 *
	 * @return array[] See parent method for details.
	 */
	protected function get_input_typesets() {
		$errors = new Gofer_SEO_Errors();
		$errors->load_from_db();

		$tmp_error_index = array(
			'th' => 'th',
		);
		$count = 0;
		foreach ( $errors->errors as $code => $props_arr ) {
			foreach ( $props_arr as $props_hash => $timestamps ) {
				foreach ( $timestamps as $timestamp ) {
					$tmp_error_index[ $count ] = $count;
					$count++;
				}
			}
		}

		$tmp_error_index = array_reverse( $tmp_error_index );

		$input_typesets = array(
			'clear_cache'       => array(
				'title' => __( 'Clear Cache', 'gofer-seo' ),
				'type'  => 'button-submit',
				'attrs' => array(
					'class' => 'button-secondary',
					'value' => __( 'Clear All', 'gofer-seo' ),
				),
			),

			/*
			 * TODO Add concepts.
			'php_info'          => array(
				'title' => __( 'System Info', 'gofer-seo' ),
				'type'  => 'html|content|custom',
			),
			'updater_revisions' => array(
				'title' => __( 'Plugin Database Revisions', 'gofer-seo' ),
				'type'  => 'html|data-list|custom',
			),
			'clear_cache'          => array(
				'title' => __( 'Clear Plugin Cache', 'gofer-seo' ),
				'type'  => 'button',
			),
			*/

			// Error Settings.
			'enable_errors'     => array(
				'title'      => __( 'Enable Errors', 'gofer-seo' ),
				'input_type' => 'checkbox',
			),
			'enable_wp_errors'  => array(
				'title'      => __( 'Include WP Errors', 'gofer-seo' ),
				'input_type' => 'checkbox',
			),
			'enable_error_logs' => array(
				/* translators: %s: File extension for log files. */
				'title'      => sprintf( __( 'Enable Error %s', 'gofer-seo' ), '.log' ),
				'input_type' => 'checkbox',
			),

			// Error List.
			'show_timestamps'   => array(
				'title' => __( 'Show Timestamps', 'gofer-seo' ),
				'type'  => 'checkbox',
			),
			'show_messages'     => array(
				'title' => __( 'Show Messages', 'gofer-seo' ),
				'type'  => 'checkbox',
			),
			'show_details'      => array(
				'title' => __( 'Show Details', 'gofer-seo' ),
				'type'  => 'checkbox',
			),
			'show_data'         => array(
				'title' => __( 'Show Data', 'gofer-seo' ),
				'type'  => 'checkbox',
			),
			'delete_errors'     => array(
				'title' => __( 'Delete All Errors', 'gofer-seo' ),
				'type'  => 'button-submit',
				'attrs' => array(
					'class' => 'button-secondary',
					'value' => __( 'Delete', 'gofer-seo' ),
				),
			),

			'errors'            => array(
				'title'        => __( 'Errors', 'gofer-seo' ),
				'type'         => 'list-table',
				'layout'       => 'input-row',
				'wrap_dynamic' => array(
					'timestamp' => array(
						'title'      => __( 'Timestamp', 'gofer-seo' ),
						'type'       => 'html',
						'layout'     => 'input-row',

						'conditions' => array(
							'show_timestamps' => array(
								'operator'    => '===',
								'right_value' => true,
							),
						),
					),
					'message'   => array(
						'title'      => __( 'Message', 'gofer-seo' ),
						'type'       => 'html',
						'layout'     => 'input-row',

						'conditions' => array(
							'show_messages' => array(
								'operator'    => '===',
								'right_value' => true,
							),
						),
					),
					'details'   => array(
						'title'      => __( 'Details', 'gofer-seo' ),
						'type'       => 'html',
						'layout'     => 'input-row',

						'conditions' => array(
							'show_details' => array(
								'operator'    => '===',
								'right_value' => true,
							),
						),
					),
					'data'      => array(
						'title'      => __( 'Data', 'gofer-seo' ),
						'type'       => 'html-text',
						'layout'     => 'input-row',

						'conditions' => array(
							'show_data' => array(
								'operator'    => '===',
								'right_value' => true,
							),
						),
					),

				),
				'items'        => $tmp_error_index,
			),
		);

		/**
		 * Debugger Module Input Typeset.
		 *
		 * @since 1.0.0
		 *
		 * @see \Gofer_SEO_Screen_Page::get_input_typesets()
		 *
		 * @return array See `\Gofer_SEO_Screen_Page::get_input_typesets()` for details.
		 */
		$input_typesets = apply_filters( 'gofer_seo_admin_module_debugger_input_typesets', $input_typesets );

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
		$meta_box_typesets = array(
			'general'        => array(
				'title'    => __( 'System Settings', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'clear_cache',
				),
			),
			'error_settings' => array(
				'title'    => __( 'Error Settings', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'enable_errors',
					'enable_wp_errors',
					'enable_error_logs',
				),
			),
			'errors'         => array(
				'title'    => __( 'Errors List', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'low',
				'inputs'   => array(
					'show_timestamps',
					'show_messages',
					'show_details',
					'show_data',
					'delete_errors',
					'errors',
				),
			),
		);

		/**
		 * Debugger Module Meta Box Typesets.
		 *
		 * @since 1.0.0
		 *
		 * @see \Gofer_SEO_Screen_Page::get_meta_box_typesets()
		 *
		 * @param array $meta_box_typsets See `\Gofer_SEO_Screen_Page::get_meta_box_typesets()` for details.
		 */
		$meta_box_typesets = apply_filters( 'gofer_seo_admin_module_debugger_meta_box_typesets', $meta_box_typesets );

		return $meta_box_typesets;
	}

	/**
	 * Add Submenu to Admin Menu.
	 *
	 * @since 1.0.0
	 *
	 * @link  https://developer.wordpress.org/reference/functions/add_submenu_page/
	 */
	public function add_submenu() {
		$hook_suffix = add_submenu_page(
			$this->menu_parent_slug,             // Menu parent slug.
			__( 'Debug Settings', 'gofer-seo' ),       // Page title.
			__( 'Debugger', 'gofer-seo' ), // Menu title.
			'gofer_seo_access',                  // Capability.
			$this->submenu_slug,                 // Menu slug.
			array( $this, 'display_page' ),      // Callback function.
			$this->submenu_order                 // Position.
		);

		$this->set_hook_suffixes( array( $hook_suffix ) );
		$this->set_screen_ids( array( $hook_suffix ) );
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

		$file_ext = gofer_seo_is_min_enabled() ? 'min.js' : 'js';
		wp_enqueue_script(
			'gofer-seo-screens-page-module-debugger-js',
			GOFER_SEO_URL . 'admin/js/screens/admin-page-module-debugger' . $file_ext,
			array( 'jquery' ),
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
		parent::localize_script();
		global $hook_suffix;

		if (
				wp_script_is( 'gofer-seo-screens-page-module-debugger-js', 'enqueued' ) &&
				! wp_script_is( 'gofer-seo-screens-page-module-debugger-js', 'done' ) &&
				in_array( $hook_suffix, $this->get_hook_suffixes( 'submenus' ), true )
		) {

			$debugger_l10n = array(
				'nonce' => wp_create_nonce( 'gofer_seo_screen_module_debugger' ),
			);
			wp_localize_script( 'gofer-seo-screens-page-module-debugger-js', 'gofer_seo_screen_module_debugger_l10n', $debugger_l10n );
		}
	}

	/**
	 * Get Values from Target Source.
	 *
	 * Used by child classes to return the values being used.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed[] ${INPUT_SLUG}
	 */
	public function get_values() {
		$values = parent::get_values();
		$values['delete_errors'] = '';
		$values['clear_cache']   = '';

		$errors = new Gofer_SEO_Errors();
		$errors->load_from_db();

		$values['errors'] = array();
		foreach ( $errors->errors as $code => $props_arr ) {
			foreach ( $props_arr as $props_hash => $timestamps ) {
				foreach ( $timestamps as $timestamp ) {
					$data = array();
					if ( isset( $errors->data[ $code ] ) && isset( $errors->data[ $code ][ $props_hash ] ) ) {
						$data = $errors->data[ $code ][ $props_hash ];
					}
					$values['errors'][] = array(
						'timestamp' => sprintf(
							'%1$s<br/>%2$s',
							gmdate( 'Y-m-d', $timestamp ),
							gmdate( 'H:i:s', $timestamp )
						),
						'message'   => $errors->props[ $code ][ $props_hash ]['message'],
						'details'   => sprintf(
							'Code: %1$s<br/>Props Hash: %2$s<br/>Type: %3$s',
							$code,
							$props_hash,
							$errors->props[ $code ][ $props_hash ]['type']
						),
						'data'      => wp_json_encode( $data ),
					);
				}
			}
		}

		array_multisort( array_column( $values['errors'], 'timestamp' ), SORT_DESC, $values['errors'] );

		/**
		 * Debugger Module Get Values.
		 *
		 * @since 1.0.0
		 *
		 * @param array $values The values of the inputs.
		 */
		$values = apply_filters( 'gofer_seo_admin_module_debugger_get_values', $values );

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

		/**
		 * Debugger Module Update Values.
		 *
		 * @since 1.0.0
		 *
		 * @param array $new_values The new set of input (typeset) values.
		 */
		$new_values = apply_filters( 'gofer_seo_admin_module_debugger_update_values', $new_values );

		return parent::update_values( $new_values );
	}

	/**
	 * AJAX Delete Errors.
	 *
	 * @since 1.0.0
	 */
	public function ajax_delete_errors() {
		check_ajax_referer( 'gofer_seo_screen_module_debugger' );
		if ( ! current_user_can( 'gofer_seo_access' ) ) {
			/* translators: %1$s: WordPress User Role slug. */
			wp_send_json_error( sprintf( __( 'User doesn\'t have `%1$s` capabilities.', 'gofer-seo' ), 'gofer_seo_access' ) );
		}

		$errors = new Gofer_SEO_Errors();
		$errors->delete_db();

		wp_send_json_success( __( 'Successfully updated.', 'gofer-seo' ) );
	}

	/**
	 * AJAX Clear Cache.
	 *
	 * @since 1.0.0
	 */
	public function ajax_clear_cache() {
		check_ajax_referer( 'gofer_seo_screen_module_debugger' );
		if ( ! current_user_can( 'gofer_seo_access' ) ) {
			/* translators: %1$s: WordPress User Role slug. */
			wp_send_json_error( sprintf( __( 'User doesn\'t have `%1$s` capabilities.', 'gofer-seo' ), 'gofer_seo_access' ) );
		}

		// Query Functions in `query.php`.
		wp_cache_delete( 'gofer_seo_get_post_types' );
		delete_transient( 'gofer_seo_post_type_objects' );

		wp_cache_delete( 'gofer_seo_get_taxonomies' );
		delete_transient( 'gofer_seo_taxonomy_objects' );

		wp_cache_delete( 'gofer_seo_get_terms' );
		wp_cache_delete( 'gofer_seo_get_users' );
		wp_cache_delete( 'gofer_seo_get_dates' );

		// Template in `template.php`.
		wp_cache_delete( 'gofer_seo_locate_template' );

		// \Gofer_SEO_Methods::set_transient_url_postids().
		if ( is_multisite() ) {
			delete_site_transient( 'gofer_seo_multisite_attachment_ids_urls' );
		} else {
			delete_transient( 'gofer_seo_attachment_ids_urls' );
		}
		// \Gofer_SEO_Methods::set_transient_url_postids().
		if ( is_multisite() ) {
			delete_site_transient( 'gofer_seo_multisite_attachment_url_postids' );
		} else {
			delete_transient( 'gofer_seo_attachment_url_postids' );
		}

		// Get Robots.txt in `\Gofer_SEO_Module_Crawlers::get_robots_txt()`.
		delete_transient( 'gofer_seo_module_crawlers_robots_txt_without_filters' );
		delete_transient( 'gofer_seo_module_crawlers_robots_txt_with_filters' );

		wp_send_json_success( __( 'Successfully cleared cache.', 'gofer-seo' ) );
	}

}
