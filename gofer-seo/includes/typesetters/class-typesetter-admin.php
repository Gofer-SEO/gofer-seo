<?php
/**
 * Admin Inputs Typesetter & Validator.
 *
 * Methods used by typesets. Handles most of the validation & sanitization.
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

/**
 * Class Gofer_SEO_Typesetter_Admin.
 *
 * @since 1.0.0
 */
class Gofer_SEO_Typesetter_Admin {

	/**
	 * Get Typesets for Add Field List.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $typesets See self::get_input_typesets().
	 * @param string $prefix   Used to prefix the array keys. Primarily used for recursive operations.
	 * @return array
	 */
	function get_add_field_list_typesets( $typesets, $prefix = '' ) {
//		$tmp_typesets = wp_filter_object_list( $input_typesets );
		if ( ! empty( $prefix ) ) {
			$prefix .= '-';
		}
		$add_field_list_typesets = array();

		// Clear out other types.
		$tmp_typesets = array_filter(
			$typesets,
			function ( $value ) {
				$target_types = array(
					'wrap',
					'wrap_dynamic',
					'tab',
					'add-field-list',
				);
				if ( in_array( $value['type'], $target_types, true ) ) {
					return true;
				}
				return false;
			}
		);

		foreach ( $tmp_typesets as $input_name => $tmp_typeset ) {
			$name = $prefix . $input_name;
			switch ( $tmp_typeset['type'] ) {
				case 'wrap':
				case 'tab':
					$wrap_add_field_list = $this->get_add_field_list_typesets( $tmp_typeset['wrap'], $name );
					if ( ! empty( $wrap_add_field_list ) ) {
						$add_field_list_typesets = array_merge_recursive( $add_field_list_typesets, $wrap_add_field_list );
					}
					break;
				case 'wrap_dynamic':
					$wrap_dynamic_add_field_list = $this->get_add_field_list_typesets( $tmp_typeset['wrap_dynamic'], $name );
					if ( ! empty( $wrap_dynamic_add_field_list ) ) {
						$add_field_list_typesets = array_merge_recursive( $add_field_list_typesets, $wrap_dynamic_add_field_list );
					}
					break;
				case 'add-field-list':
					$add_field_list_typesets[ $name ] = $tmp_typeset['wrap_dynamic'];
					break;
			}
		}

		return $add_field_list_typesets;
	}

	/**
	 * Validate Typesets - Multiple Inputs.
	 *
	 * Recursive.
	 *
	 * @since 1.0.0
	 *
	 * @param array[] $typesets
	 * @return array[]
	 */
	public function validate_input_typesets( $typesets ) {
		foreach ( $typesets as $typeset_key => $typeset ) {
			// Set typeset 'slug' with array key.
			if ( empty( $typeset['slug'] ) ) {
				$typeset['slug'] = $typeset_key;
			}
			$typeset = $this->validate_input_typeset( $typeset );
			if ( ! $typeset ) {
				unset( $typesets[ $typeset_key ] );
				continue;
			}

			switch ( $typeset['type'] ) {
				case 'wrap':
				case 'tabs':
				case 'tab':
				case 'add-field-robots-txt':
				case 'table-form-table':
					$typeset['wrap'] = $this->validate_input_typesets( $typeset['wrap'] );
					break;
				case 'wrap_dynamic':
				case 'add-field-list':
				case 'list-table':
					$typeset['wrap_dynamic'] = $this->validate_input_typesets( $typeset['wrap_dynamic'] );
					break;
			}

			$typesets[ $typeset_key ] = $typeset;
		}

		return $typesets;
	}

	/**
	 * Validate Typeset - Input.
	 *
	 * @since 1.0.0
	 *
	 * @param array
	 * @return array|bool
	 */
	public function validate_input_typeset( $typeset ) {
		// TODO Add Cache.

		// Correct known shorthands with typeset array.
		$convert_type_keys = array(
			'input_type'     => 'type',
			'dynamic'        => 'wrap_dynamic',
			'attr'           => 'attrs',
			'input_items'    => 'items',

			'condition'      => 'conditions',
			'cond'           => 'conditions',
			'conds'          => 'conditions',

			'item_condition' => 'item_conditions',
		);
		foreach ( $convert_type_keys as $old_key => $new_key ) {
			if ( isset( $typeset[ $old_key ] ) ) {
				// TODO Log or Display Error?
				$typeset[ $new_key ] = $typeset[ $old_key ];
				unset( $typeset[ $old_key ] );
			}
		}

		if ( empty( $typeset['type'] ) ) {
			if ( ! empty( $typeset['wrap'] ) ) {
				$typeset['type'] = 'wrap';
			} elseif ( ! empty( $typeset['wrap_dynamic'] ) ) {
				$typeset['type'] = 'wrap_dynamic';
			} else {
				// TODO Display Error.
				return false;
			}
		}

		// Correct known 'type' shorthands.
		$convert_type_values = array(
			'dynamic'       => 'wrap_dynamic',
			'multicheckbox' => 'multi-checkbox',
			'checkboxes'    => 'multi-checkbox',
			'multiselect'   => 'multi-select',
		);
		foreach ( $convert_type_values as $old_value => $new_value ) {
			if ( $typeset['type'] === $old_value ) {
				$typeset['type'] = $new_value;
			}
		}

		if (
				(
					'wrap' === $typeset['type'] ||
					'tabs' === $typeset['type'] ||
					'tab' === $typeset['type'] ||
					'add-field-robots-txt' === $typeset['type'] ||
					'table-form-table' === $typeset['type']
				) &&
				! isset( $typeset['wrap'] )
		) {
			// TODO Display Error.
			return false;
		}
		if (
				(
					'wrap_dynamic' === $typeset['type'] ||
					'add-field-list' === $typeset['type'] ||
					'list-table' === $typeset['type']
				) &&
				! isset( $typeset['wrap_dynamic'] )
		) {
			// TODO Display Error.
			return false;
		}

		// Check if items is set for inputs that require it.
		if ( ! isset ( $typeset['items'] ) ) {
			$inputs_types_required = array(
				'wrap_dynamic',
				'multi-checkbox',
				'radio',
				'select',
				'multi-select',
				'select2-multi-select',
				'list-table',
			);
			if ( in_array( $typeset['type'], $inputs_types_required, true ) ) {
				// Items variable is required for multiple inputs.
				// TODO Display Error.
				return false;
			}
		}

		// Validate `conditions` & `item_conditions`.
		if ( isset( $typeset['conditions'] ) ) {
			$typeset['conditions'] = $this->validate_input_conditions_typeset( $typeset['conditions'] );

			if ( empty( $typeset['conditions'] ) ) {
				// TODO Display Error.
				unset( $typeset['conditions'] );
			}
		}

		if ( isset( $typeset['item_conditions'] ) ) {
			foreach ( $typeset['item_conditions'] as $item_condition_key => $item_condition_arr ) {
				$typeset['item_conditions'][ $item_condition_key ] = $this->validate_input_conditions_typeset( $item_condition_arr );

				if ( empty( $typeset['item_conditions'][ $item_condition_key ] ) ) {
					// TODO Display Error.
					unset( $typeset['item_conditions'][ $item_condition_key ] );
				}
			}
		}

		// Set & validate layout.
		if ( ! isset( $typeset['layout'] ) ) {
			$typeset['layout'] = 'label-input-row';
		}
		$valid_layouts = array(
			'label-input-row',
			'input-row',
			'h2-input-column',
		);
		if ( ! in_array( $typeset['layout'], $valid_layouts, true ) ) {
			$typeset['layout'] = 'label-input-row';
		}

		// Sets the attributes to empty if not already set.
		if ( ! isset( $typeset['attrs'] ) ) {
			$typeset['attrs'] = array();
		}

		// Set & validate esc callbacks.
		// TODO Some basic inputs may need default esc_* functions; however, some nested inputs could conflict.
		if ( ! isset( $typeset['esc'] ) ) {
			$typeset['esc'] = array();
		}
		$tmp_esc_callbacks = array();
		foreach ( $typeset['esc'] as $index => $callback ) {
			if (
				is_array( $callback ) &&
				isset( $callback[0] ) &&
				(
					is_string( $callback[0] ) ||
					is_array( $callback[0] )
				)
			) {
				$tmp_esc_callbacks[] = $callback;
			} else {
				// TODO Display Error.
			}
		}
		$typeset['esc'] = $tmp_esc_callbacks;

		return $typeset;
	}

	/**
	 * Validate Typeset - Input['conditions'].
	 *
	 * @since 1.0.0
	 *
	 * @param array $condition_typeset
	 * @return array {
	 *     The valid `$conditions_typeset` with corrections to any shorthands.
	 *
	 *     @type string $action   What action will be taken if true. Default: 'show'.
	 *                            Accepts 'show', 'hide', 'enable', 'disable', and 'readonly'.
	 *     @type string $relation Compare relation between each conditions. Default: 'AND'.
	 *                            Accepts 'AND', and 'OR'.
	 *     @type array ${INPUT_KEY} {
	 *         @type string $left_var    Left variable.
	 *         @type string $operator    Compare operator.
	 *         @type string $right_var   (Optional) Right variable.
	 *         @type string $right_value (Optional) Right value.
	 *     }
	 * }
	 */
	public function validate_input_conditions_typeset( $condition_typeset ) {
		// Sanitize 'relation'.
		if ( ! isset( $condition_typeset['relation'] ) ) {
			// TODO Display Error.
			$condition_typeset['relation'] = 'AND';
		}
		$condition_typeset['relation'] = strtoupper( $condition_typeset['relation'] );
		if ( 'OR' !== $condition_typeset['relation'] && 'AND' !== $condition_typeset['relation'] ) {
			// TODO Display Error.
			$condition_typeset['relation'] = 'AND';
		}

		// Sanitize 'action'.
		if ( ! isset( $condition_typeset['action'] ) ) {
			$condition_typeset['action'] = 'show';
		}
		$condition_typeset['action'] = strtolower( $condition_typeset['action'] );

		$valid_actions = array(
			'show',
			'hide',
			'enable',
			'disable',
			'readonly',
		);
		if ( ! in_array( $condition_typeset['action'], $valid_actions, true ) ) {
			// TODO Display Error.
			$condition_typeset['action'] = 'show';
		}

		foreach ( $condition_typeset as $condition_key => $condition_arr ) {
			if (
					'action' === $condition_key ||
					'relation' === $condition_key
			) {
				continue;
			}

			// Convert known shorthands.
			$convert_show_keys = array(
				'left'      => 'left_var',
				'op'        => 'operator',
				'right'     => 'right_var',
				'value'     => 'right_value',
				'right_val' => 'right_value',
			);
			foreach ( $convert_show_keys as $old_key => $new_key ) {
				if ( isset( $condition_arr[ $old_key ] ) ) {
					// TODO Display Error.
					$condition_arr[ $new_key ] = $condition_arr[ $old_key ];
					unset( $condition_arr[ $old_key ] );
				}
			}

			// Convert/Fill index/key & left_var.
			if ( ! is_string( $condition_key ) && is_numeric( $condition_key ) ) {
				unset( $condition_typeset[ $condition_key ] );
				if ( ! isset( $condition_arr['left_var'] ) ) {
					// TODO Display Error.
					continue;
				}
			}
			if ( ! isset( $condition_arr['left_var'] ) ) {
				$condition_arr['left_var'] = $condition_key;
			}

			if ( ! isset( $condition_arr['operator'] ) ) {
				// TODO Display Error.
				unset( $condition_typeset[ $condition_key ] );
				continue;
			}
			$allowed_operators = array(
				'==',
				'===',
				'!=',
				'!==',
				'<',
				'>',
				'<=',
				'>=',
				'TRUE',
				'FALSE',
				'AND',
				'OR',
				'regex',
				'match',
				'inArray',
				'checked',
				'selected',
			);
			if ( ! in_array( $condition_arr['operator'], $allowed_operators, true ) ) {
				// TODO Display Error.
				unset( $condition_typeset[ $condition_key ] );
				continue;
			}

			if ( ! isset( $condition_arr['right_var'] ) && ! isset( $condition_arr['right_value'] ) ) {
				// TODO Display Error.
				unset( $condition_typeset[ $condition_key ] );
				continue;
			}

			$condition_typeset[ $condition_arr['left_var'] ] = $condition_arr;
		}

		// Remove if invalid or no input check conditions.
		if (
				isset( $condition_typeset['relation'] ) &&
				isset( $condition_typeset['action'] ) &&
				3 > count( $condition_typeset )
		) {
			return array();
		} elseif (
				! isset( $condition_typeset['relation'] ) ||
				! isset( $condition_typeset['action'] )
		) {
			return array();
		}

		return $condition_typeset;
	}

	/**
	 * Validate Typesets - Multiple Meta Boxes.
	 *
	 * @since 1.0.0
	 *
	 * @param array $meta_box_typesets
	 * @return array
	 */
	public function validate_meta_box_typesets( $meta_box_typesets ) {
		foreach ( $meta_box_typesets as $meta_box_key => $meta_box_typeset ) {
			$meta_box_typeset = $this->validate_meta_box_typeset( $meta_box_typeset );
			if ( ! $meta_box_typeset ) {
				unset( $meta_box_typesets[ $meta_box_key ] );
				continue;
			}

			$meta_box_typesets[ $meta_box_key ] = $meta_box_typeset;
		}

		return $meta_box_typesets;
	}

	/**
	 * Validate Typeset - Meta Box.
	 *
	 * Makes any corrections, or returns false if invalid.
	 *
	 * TODO This may need to be moved to Screen_Page & Screen_Editor. Other screens may be different.
	 *
	 * @since 1.0.0
	 *
	 * @param array $meta_box_typeset
	 * @return array|bool
	 */
	public function validate_meta_box_typeset( $meta_box_typeset ) {
		// Title.
		if ( ! isset( $meta_box_typeset['title'] ) ) {
			$meta_box_typeset['title'] = '';
		}

		// Context.
		if ( empty( $meta_box_typeset['context'] ) ) {
			$meta_box_typeset['context'] = 'gofer_seo_normal';
		}

		// Priority.
		if ( empty( $meta_box_typeset['priority'] ) ) {
			$meta_box_typeset['priority'] = 'default';
		}

		// Inputs.
		if ( ! isset( $meta_box_typeset['inputs'] ) ) {
			// $meta_box_typeset = array();
			// Should probably keep as return false, since any filters used should have already added/registered inputs,
			// and as a result wouldn't render anything in the meta box.
			// TODO Display Error.
			return false;
		}

		// Callbacks & Callback_Args.
		if ( isset( $meta_box_typeset['callback'] ) ) {
			if ( is_array( $meta_box_typeset['callback'] ) ) {
				if (
						! isset( $meta_box_typeset['callback'][0] ) ||
						! isset( $meta_box_typeset['callback'][1] ) ||
						! method_exists( $meta_box_typeset['callback'][0], $meta_box_typeset['callback'][1] )
				) {
					unset( $meta_box_typeset['callback'] );
					unset( $meta_box_typeset['callback_args'] );
				}
			} elseif ( is_string( $meta_box_typeset['callback'] ) ) {
				if ( ! function_exists( $meta_box_typeset['callback'] ) ) {
					unset( $meta_box_typeset['callback'] );
					unset( $meta_box_typeset['callback_args'] );
				}
			} else {
				unset( $meta_box_typeset['callback'] );
				unset( $meta_box_typeset['callback_args'] );
			}
		}
		if ( ! isset( $meta_box_typeset['callback'] ) ) {
			/**
			 * @uses \Gofer_SEO_Typesetter_Admin::display_meta_box()
			 */
			$meta_box_typeset['callback'] = array( $this, 'display_meta_box' );
		}

		// Callback args.
		if ( empty( $meta_box_typeset['callback_args'] ) ) {
			$meta_box_typeset['callback_args'] = array();
		} elseif ( ! is_array( $meta_box_typeset['callback_args'] ) ) {
			$meta_box_typeset['callback_args'] = array( $meta_box_typeset['callback_args'] );
		}

		return $meta_box_typeset;
	}

	/**
	 * Display Meta Box Content.
	 *
	 * @since 1.0.0
	 *
	 * @param array $object Sent from `do_meta_boxes()` (3rd param).
	 * @param array $box    Sent from `add_meta_box()` (6th param).
	 */
	public function display_meta_box( $object, $box ) {
		$args = array(
			'object' => $object,
			'box'    => $box,
		);
		gofer_seo_do_template( 'admin/screens/meta-boxes/meta-box.php', $args );
	}

}
