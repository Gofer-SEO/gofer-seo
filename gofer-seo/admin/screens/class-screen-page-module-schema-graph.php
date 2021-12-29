<?php
/**
 * Admin Screen: Page-Module - Schema Graph
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

/**
 * Class Gofer_SEO_Screen_Page_Module_Schema_Graph
 *
 * @since 1.0.0
 */
class Gofer_SEO_Screen_Page_Module_Schema_Graph extends Gofer_SEO_Screen_Page_Module {

	/**
	 * Get Module Slug.
	 *
	 * @inheritDoc
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected function get_module_slug() {
		return 'schema_graph';
	}

	/**
	 * Get Submenu Slug.
	 *
	 * @inheritDoc
	 *
	 * @since 1.0.0
	 */
	public function get_submenu_slug() {
		return 'gofer_seo_module_schema_graph';
	}

	/**
	 * Get Menu Title.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_menu_title() {
		return 'Schema Graph';
	}

	/**
	 * The Input Typesets (Params/Configuration)
	 *
	 * @inheritDoc
	 *
	 * @since 1.0.0
	 *
	 * @return array[] See parent method for details.
	 */
	protected function get_input_typesets() {
		$user_args = array(
			'role__in' => array(
				'administrator',
				'editor',
				'author',
			),
			'orderby'  => 'nicename',
		);
		$users      = gofer_seo_get_users( $user_args );
		$users_list = array_combine(
			wp_list_pluck( $users, 'ID' ),
			array_map(
				function( $user ) {
					return $user->data->user_nicename . ' (' . $user->data->display_name . ')';
				},
				$users
			)
		);
		$users_list = array_replace( array( -1 => 'Manually Enter' ), $users_list );

		$input_typesets = array(
			'site_represents'          => array(
				'title' => __( 'Site Represents', 'gofer-seo' ),
				'type'  => 'radio',
				'items' => array(
					'organization' => 'Organization',
					'person'       => 'Person',
				),
			),
			'person_user_id'           => array(
				'title'      => __( 'User Represented', 'gofer-seo' ),
				'type'       => 'select',
				'items'      => $users_list,
				'conditions' => array(
					'site_represents' => array(
						'operator'    => '===',
						'right_value' => 'person',
					),
				),
				'esc'        => array(
					array( 'intval' ),
				),
			),
			'person_custom_name'       => array(
				'title'      => __( 'Person\'s Name', 'gofer-seo' ),
				'type'       => 'text',
				'conditions' => array(
					'site_represents' => array(
						'operator'    => '===',
						'right_value' => 'person',
					),
					'person_user_id'  => array(
						'operator'    => '===',
						'right_value' => '-1',
					),
				),
				'attrs'      => array(
					'placeholder' => 'First (Middle) Last',
				),
			),
			'person_custom_image'      => array(
				'title'      => __( 'Person\'s Image/Avatar', 'gofer-seo' ),
				'type'       => 'image-media',
				'conditions' => array(
					'site_represents' => array(
						'operator'    => '===',
						'right_value' => 'person',
					),
					'person_user_id'  => array(
						'operator'    => '===',
						'right_value' => '-1',
					),
				),
			),
			'organization_name'        => array(
				'title'      => __( 'Name of Organization', 'gofer-seo' ),
				'type'       => 'text',
				'conditions' => array(
					'site_represents' => array(
						'operator'    => '===',
						'right_value' => 'organization',
					),
				),
			),
			'organization_logo'        => array(
				'title'      => __( 'Organization Logo', 'gofer-seo' ),
				'type'       => 'image-media',
				'conditions' => array(
					'site_represents' => array(
						'operator'    => '===',
						'right_value' => 'organization',
					),
				),
			),
			'phone_contact_type'       => array(
				'title'      => __( 'Contact Type', 'gofer-seo' ),
				'type'       => 'select',
				'items'      => array(
					/* translators: %1$s is replaced with dashes. */
					''                    => sprintf( __( '%1$s Select %1$s', 'gofer-seo' ), '--' ),
					'customer_support'    => 'Customer Support',
					'tech_support'        => 'Technical Support',
					'billing_support'     => 'Billing Support',
					'bill_payment'        => 'Bill Payment',
					'sales'               => 'Sales',
					'reservations'        => 'Reservations',
					'credit_card_support' => 'Credit Card Support',
					'emergency'           => 'Emergency',
					'baggage_tracking'    => 'Baggage Tracking',
					'roadside_assistance' => 'Roadside Assistance',
					'package_tracking'    => 'Package Tracking',
				),
				'conditions' => array(
					'site_represents' => array(
						'operator'    => '===',
						'right_value' => 'organization',
					),
				),
			),
			'phone_number'             => array(
				'title'      => __( 'Phone Number', 'gofer-seo' ),
				'type'       => 'tel',
				'conditions' => array(
					'site_represents' => array(
						'operator'    => '===',
						'right_value' => 'organization',
					),
				),
				'attrs'      => array(
					'placeholder' => 'ex. 999-555-1234',
				),
			),
			'social_profile_urls'      => array(
				'title' => __( 'Social Profile Links', 'gofer-seo' ),
				'type'  => 'textarea',
				'attrs' => array(
					'rows' => 3,
				),
			),
			'show_search_results_page' => array(
				'title' => __( 'Include Search Results Page', 'gofer-seo' ),
				'type'  => 'checkbox',
			),
		);

		/**
		 * Schema Graph Module Input Typeset.
		 *
		 * @since 1.0.0
		 *
		 * @see \Gofer_SEO_Screen_Page::get_input_typesets()
		 *
		 * @return array See `\Gofer_SEO_Screen_Page::get_input_typesets()` for details.
		 */
		$input_typesets = apply_filters( 'gofer_seo_admin_module_schema_graph_input_typesets', $input_typesets );

		return $input_typesets;
	}

	/**
	 * The Meta Box Typesets (Params/Configuration).
	 *
	 * @inheritDoc
	 *
	 * @since 1.0.0
	 *
	 * @return array[] See parent method for details.
	 */
	protected function get_meta_box_typesets() {
		$meta_box_typesets = array(
			'general' => array(
				'title'    => __( 'General Settings', 'gofer-seo' ),
				'context'  => 'gofer_seo_normal',
				'priority' => 'default',
				'inputs'   => array(
					'site_represents',
					'person_user_id',
					'person_custom_name',
					'person_custom_image',
					'organization_name',
					'organization_logo',
					'phone_contact_type',
					'phone_number',
					'social_profile_urls',
					'show_search_results_page',
				),
			),
		);

		/**
		 * Schema Graph Module Meta Box Typesets.
		 *
		 * @since 1.0.0
		 *
		 * @see \Gofer_SEO_Screen_Page::get_meta_box_typesets()
		 *
		 * @param array $meta_box_typsets See `\Gofer_SEO_Screen_Page::get_meta_box_typesets()` for details.
		 */
		$meta_box_typesets = apply_filters( 'gofer_seo_admin_module_schema_graph_meta_box_typesets', $meta_box_typesets );

		return $meta_box_typesets;
	}

	/**
	 * Add Submenu to Admin Menu.
	 *
	 * @inheritDoc
	 *
	 * @since 1.0.0
	 *
	 * @link  https://developer.wordpress.org/reference/functions/add_submenu_page/
	 */
	public function add_submenu() {
		$hook_suffix = add_submenu_page(
			$this->menu_parent_slug,               // Menu parent slug.
			__( 'Schema Graph', 'gofer-seo' ), // Page title.
			__( 'Schema Graph', 'gofer-seo' ),          // Menu title.
			'gofer_seo_access',                    // Capability.
			$this->submenu_slug,                   // Menu slug.
			array( $this, 'display_page' ),        // Callback function.
			$this->submenu_order                   // Position.
		);

		$this->set_hook_suffixes( array( $hook_suffix ) );
		$this->set_screen_ids( array( $hook_suffix ) );
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
		 * Schema Graph Module Get Values.
		 *
		 * @since 1.0.0
		 *
		 * @param array $values The values of the inputs.
		 */
		$values = apply_filters( 'gofer_seo_admin_module_schema_graph_get_values', $values );

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
		 * Schema Graph Module Update Values.
		 *
		 * @since 1.0.0
		 *
		 * @param array $new_values The new set of input (typeset) values.
		 */
		$new_values = apply_filters( 'gofer_seo_admin_module_schema_graph_update_values', $new_values );

		return parent::update_values( $new_values );
	}

}
