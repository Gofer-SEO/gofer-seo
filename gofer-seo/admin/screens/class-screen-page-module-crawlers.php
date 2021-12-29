<?php
/**
 * Admin Screen: Page-Module - Crawlers
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

/**
 * Class Gofer_SEO_Screen_Page_Module_Crawlers
 *
 * @since 1.0.0
 */
class Gofer_SEO_Screen_Page_Module_Crawlers extends Gofer_SEO_Screen_Page_Module {

	/**
	 * Get Module Slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_module_slug() {
		return 'crawlers';
	}

	/**
	 * Get Submenu Slug.
	 *
	 * @since 1.0.0
	 */
	public function get_submenu_slug() {
		return 'gofer_seo_module_crawlers';
	}

	/**
	 * Get Menu Title.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_menu_title() {
		return __( 'Crawlers', 'gofer-seo' );
	}

	/**
	 * The Input Typesets (Params/Configuration)
	 *
	 * @since 1.0.0
	 *
	 * @return array[] See parent method for details.
	 */
	protected function get_input_typesets() {
		$input_typesets = array(
			// Block HTTP Bots.
			'enable_block_user_agent'    => array(
				'title'      => __( 'Block blacklisted User-Agents', 'gofer-seo' ),
				'input_type' => 'checkbox',
			),
			'enable_block_referer'       => array(
				'title'      => __( 'Block Blacklisted Referers', 'gofer-seo' ),
				'input_type' => 'checkbox',
			),
			'enable_log_blocked_bots'    => array(
				'title'      => __( 'Track Blocked Bots', 'gofer-seo' ),
				'input_type' => 'checkbox',
				'conditions' => array(
					'action'                  => 'disable',
					'enable_block_user_agent' => array(
						'operator'    => '!==',
						'right_value' => true,
					),
					'enable_block_referer'    => array(
						'operator'    => '!==',
						'right_value' => true,
					),
				),
			),
			'use_custom_blacklist'       => array(
				'title'      => __( 'Enable Custom Blacklist', 'gofer-seo' ),
				'input_type' => 'checkbox',
				'conditions' => array(
					'action'                  => 'disable',
					'enable_block_user_agent' => array(
						'operator'    => '!==',
						'right_value' => true,
					),
					'enable_block_referer'    => array(
						'operator'    => '!==',
						'right_value' => true,
					),
				),
			),
			'user_agent_blacklist'       => array(
				'title'      => __( 'Bot Agent Blacklisted', 'gofer-seo' ),
				'input_type' => 'textarea',
				'conditions' => array(
					'enable_block_user_agent' => array(
						'operator'    => '===',
						'right_value' => true,
					),
					'use_custom_blacklist'    => array(
						'operator'    => '===',
						'right_value' => true,
					),
				),
				'attrs'      => array(
					'rows' => 6,
				),
			),
			'referer_blacklist'          => array(
				'title'      => __( 'Referrals Blacklisted', 'gofer-seo' ),
				'input_type' => 'textarea',
				'conditions' => array(
					'enable_block_referer' => array(
						'operator'    => '===',
						'right_value' => true,
					),
					'use_custom_blacklist' => array(
						'operator'    => '===',
						'right_value' => true,
					),
				),
				'attrs'      => array(
					'rows' => 6,
				),
			),

			/* **____________******************************************************************************************/
			/* _/ Robots.txt \________________________________________________________________________________________*/
			'enable_override_robots_txt' => array(
				'title'      => __( 'Override Robots.txt', 'gofer-seo' ),
				'input_type' => 'checkbox',
			),
			'robots_txt_rules'           => array(
				'title'      => __( 'Robots.txt Rules', 'gofer-seo' ),
				'input_type' => 'add-field-robots-txt',
				'layout'     => 'input-row',
				'wrap'       => array(
					'user_agents' => array(
						'title'        => __( 'User-Agent Rules', 'gofer-seo' ),
						'wrap_dynamic' => array(
							'user_agent' => array(
								'title' => __( 'User-Agent', 'gofer-seo' ),
								'type'  => 'text',
							),
							'rule_type'  => array(
								'title' => __( 'Rule Type', 'gofer-seo' ),
								'type'  => 'select',
								'items' => array(
									'disallow'    => __( 'Disallow', 'gofer-seo' ),
									'allow'       => __( 'Allow', 'gofer-seo' ),
									'crawl_delay' => __( 'Crawl-Delay', 'gofer-seo' ),
								),
								'attrs' => array(
									'value' => 'disallow',
								),
							),
							'rule_value' => array(
								'title' => __( 'Rule Value', 'gofer-seo' ),
								'type'  => 'text',
								'attrs' => array(
									'value' => '/',
								),
							),

						),
						'items'        => array(),
					),
					'sitemaps'    => array(
						'title'        => __( 'Sitemaps', 'gofer-seo' ),
						'wrap_dynamic' => array(
							'sitemap' => array(
								'title' => __( 'Sitemap URL (Additional/External)', 'gofer-seo' ),
								'type'  => 'text',
							),
						),
						'items'        => array(),
					),
				),
			),
		);

		/**
		 * Crawlers Module Input Typeset.
		 *
		 * @since 1.0.0
		 *
		 * @see \Gofer_SEO_Screen_Page::get_input_typesets()
		 *
		 * @return array See `\Gofer_SEO_Screen_Page::get_input_typesets()` for details.
		 */
		$input_typesets = apply_filters( 'gofer_seo_admin_module_crawlers_input_typesets', $input_typesets );

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
			'block_bots' => array(
				'title'    => __( 'Block HTTP Bots', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'enable_block_user_agent',
					'enable_block_referer',
					'enable_log_blocked_bots',
					'use_custom_blacklist',
					'user_agent_blacklist',
					'referer_blacklist',

				),
			),
			'robots_txt' => array(
				'title'    => __( 'Robot.txt', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'enable_override_robots_txt',
					'robots_txt_rules',
				),
			),
		);

		/**
		 * Crawlers Module Meta Box Typesets.
		 *
		 * @since 1.0.0
		 *
		 * @see \Gofer_SEO_Screen_Page::get_meta_box_typesets()
		 *
		 * @param array $meta_box_typsets See `\Gofer_SEO_Screen_Page::get_meta_box_typesets()` for details.
		 */
		$meta_box_typesets = apply_filters( 'gofer_seo_admin_module_crawlers_meta_box_typesets', $meta_box_typesets );

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
			$this->menu_parent_slug,        // Menu parent slug.
			__( 'Crawlers', 'gofer-seo' ),  // Page title.
			$this->get_menu_title(),        // Menu title.
			'gofer_seo_access',             // Capability.
			$this->submenu_slug,            // Menu slug.
			array( $this, 'display_page' ), // Callback function.
			$this->submenu_order            // Position.
		);

		$this->set_hook_suffixes( array( $hook_suffix ) );
		$this->set_screen_ids( array( $hook_suffix ) );
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
		parent::admin_register_styles( $hook_suffix );
		if ( ! in_array( $hook_suffix, $this->get_hook_suffixes( 'submenus' ), true ) ) {
			return;
		}

		wp_register_style(
			'gofer-seo-input-type-add-field-robots-txt-css',
			GOFER_SEO_URL . 'admin/css/inputs/types/add-field-robots-txt.css',
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
		parent::admin_register_scripts( $hook_suffix );
		if ( ! in_array( $hook_suffix, $this->get_hook_suffixes( 'submenus' ), true ) ) {
			return;
		}

		wp_register_script(
			'gofer-seo-input-type-add-field-robots-txt-js',
			GOFER_SEO_URL . 'admin/js/inputs/types/add-field-robots-txt.js',
			array(),
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
				wp_script_is( 'gofer-seo-input-type-add-field-robots-txt-js', 'enqueued' ) &&
				! wp_script_is( 'gofer-seo-input-type-add-field-robots-txt-js', 'done' ) &&
				in_array( $hook_suffix, $this->get_hook_suffixes( 'submenus' ), true )
		) {
			$input_typesets   = $this->typesetter_admin->validate_input_typesets( $this->get_input_typesets() );

			/**
			 * @var Gofer_SEO_Module_Crawlers $gofer_seo_module_crawlers
			 */
			$gofer_seo_module_crawlers = Gofer_SEO::get_instance()->module_loader->get_loaded_module( 'crawlers' );

			$add_field_robots_txt_l10n = array(
				'input_typesets' => array(
					'robots_txt_rules' => $input_typesets['robots_txt_rules'],
				),
				'original_rules' => $gofer_seo_module_crawlers->parse_robots_rules( $gofer_seo_module_crawlers->get_robots_txt( true ) ),
			);
			wp_localize_script( 'gofer-seo-input-type-add-field-robots-txt-js', 'gofer_seo_l10n_add_field_robots_txt', $add_field_robots_txt_l10n );
		}
	}

	/**
	 * Get Values from Target Source.
	 *
	 * Used by child classes to return the values being used.
	 *
	 * @since 1.0.0
	 *
	 * @return array ${INPUT_SLUG}
	 *
	 */
	protected function get_values() {
		$values = parent::get_values();

		/**
		 * Crawlers Module Get Values.
		 *
		 * @since 1.0.0
		 *
		 * @param array $values The values of the inputs.
		 */
		$values = apply_filters( 'gofer_seo_admin_module_crawlers_get_values', $values );

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
		 * Crawlers Module Update Values.
		 *
		 * @since 1.0.0
		 *
		 * @param array $new_values The new set of input (typeset) values.
		 */
		$new_values = apply_filters( 'gofer_seo_admin_module_crawlers_update_values', $new_values );

		return parent::update_values( $new_values );
	}

}
