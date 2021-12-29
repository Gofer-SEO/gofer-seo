<?php
/**
 * Variable Typesetter & Validator.
 *
 * Methods used by typesets. Handles most of the validation & sanitization.
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

/**
 * Class Gofer_SEO_Typesetter_Data.
 *
 * @since 1.0.0
 */
class Gofer_SEO_Typesetter_Data {

	/**
	 * Get Typeset Default Values.
	 *
	 * Recursive.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $typesets The typesets to get the values from.
	 * @param string $action   Action determines to get the default values, or fill in dynamic values.
	 *                         Accepts 'default', and 'fill'
	 * @return array Default values
	 */
	public function get_typesets_default_values( $typesets, $action = 'default' ) {
		$default_values = array();

		foreach ( $typesets as $typeset_key => $typeset ) {
			$typeset = $this->validate_typeset( $typeset );
			if ( ! $typeset ) {
				continue;
			}

			foreach ( $typeset['type'] as $type ) {
				switch ( $type ) {
					case 'cast':
						$default_values[ $typeset_key ] = $this->get_typesets_default_values( $typeset['cast'], $action );

						continue 2;
					case 'cast_dynamic':
						// Can just use value since it should already be set.
						if ( 'fill' === $action ) {
							$default_values[ $typeset_key ] = array();
							foreach ( $typeset['items'] as $item ) {
								if ( isset( $typeset['value'][ $item ] ) ) {
									// Add existing values, and fill in any defaults missing.
									$default_values[ $typeset_key ][ $item ] = array_replace(
										$this->get_typesets_default_values( $typeset['cast_dynamic'], $action ),
										$typeset['value'][ $item ]
									);
								} else {
									// Add default values.
									$default_values[ $typeset_key ][ $item ] = array();
									$default_values[ $typeset_key ][ $item ] = $this->get_typesets_default_values( $typeset['cast_dynamic'], $action );
								}
							}
						} else {
							$default_values[ $typeset_key ] = $typeset['value'];
						}

						continue 2;
					default:
						$default_values[ $typeset_key ] = $typeset['value'];
				}
			}
		}

		return $default_values;
	}

	/**
	 * Validate Options.
	 *
	 * Recursive.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options
	 * @param array[] $typesets {
	 *     @type string|array $type         Identifies the variable type.
	 *     @type array        $cast         Additional typesets to cast the variable as.
	 *     @type array        $cast_dynamic Additional (dynamic) typesets to cast the variable as.
	 *     @type mixed        $value        The (default) value.
	 *     @type string|array $sanitize     Sanitize function/method to use.
	 * }
	 * @return mixed
	 */
	public function validate_values_with_typeset( $options, $typesets ) {
		$options_clean = array();
		foreach ( $typesets as $typeset_key => $typeset ) {
			$typeset = $this->validate_typeset( $typeset );
			if ( ! $typeset ) {
				continue;
			}

			$value = null;
			$type_used = '';
			foreach ( $typeset['type'] as $type ) {
				// Typecast.
				// cast || cast_dynamic
				// (int), (integer)
				// (bool), (boolean)
				// (float), (double), (?real?)
				// (string)
				// (array)
				// (object)
				// (unset)
				$type_used = $type;
				switch ( $type ) {
					case 'cast':
						if ( is_array( $options[ $typeset_key ] ) ) {
							$value = $this->validate_values_with_typeset( $options[ $typeset_key ], $typeset['cast'] );
							break 2;
						}

						break;
					case 'cast_dynamic':
						if ( is_array( $options[ $typeset_key ] ) ) {
							$value = array();
							foreach ( $options[ $typeset_key ] as $option_index => $option ) {
								$value[ $option_index ] = $this->validate_values_with_typeset( $option, $typeset['cast_dynamic'] );
							}
							break 2;
						}

						break;
					case 'object':
					case 'array':
						if ( is_array( $options[ $typeset_key ] ) ) {
							$value = (array) $options[ $typeset_key ];
							break 2;
						}

						break;
					case 'string[]':
						if ( is_array( $options[ $typeset_key ] ) ) {
							$value = array_combine(
								array_keys( $options[ $typeset_key ] ),
								array_map( 'strval', $options[ $typeset_key ] )
							);

							break 2;
						}

						break;
					case 'int[]':
						if ( is_array( $options[ $typeset_key ] ) ) {
							$value = array_combine(
								array_keys( $options[ $typeset_key ] ),
								array_map( 'intval', $options[ $typeset_key ] )
							);

							break 2;
						}

						break;
					case 'bool[]':
						if ( is_array( $options[ $typeset_key ] ) ) {
							$value = array_combine(
								array_keys( $options[ $typeset_key ] ),
								array_map( 'boolval', $options[ $typeset_key ] )
							);

							break 2;
						}

						break;
					case 'int':
					case 'integer':
						if ( is_numeric( $options[ $typeset_key ] ) ) {
							$value = (int) $options[ $typeset_key ];
							break 2;
						}

						break;
					case 'bool':
					case 'boolean':
						if ( is_bool( $options[ $typeset_key ] ) ) {
							$value = (bool) $options[ $typeset_key ];
							break 2;
						}

						break;
					case 'double':
					case 'float':
					case 'real' :
						if ( is_numeric( $options[ $typeset_key ] ) ) {
							$value = (float) $options[ $typeset_key ];
							break 2;
						}

						break;
					case 'string':
						if ( is_string( $options[ $typeset_key ] ) ) {
							$value = (string) $options[ $typeset_key ];
							break 2;
						}

						break;
					case 'unset':
						if ( isset( $options[ $typeset_key ] ) ) {
							unset( $options[ $typeset_key ] );
							break 2;
						}

						break;
				}
			}
			if ( null === $value ) {
				if ( isset( $typeset['cast'] ) ) {
					$value = $this->validate_values_with_typeset(
						$this->get_typesets_default_values( $typeset['cast'], 'fill' ),
						$typeset['cast']
					);

				} elseif ( isset( $typeset['cast_dynamic'] ) ) {
					$value = $this->validate_values_with_typeset(
						$this->get_typesets_default_values( $typeset['cast_dynamic'], 'fill' ),
						$typeset['cast_dynamic']
					);
				} else if ( isset( $typeset['value'] ) ) {
					$value = $typeset['value'];
				}
			}
			$options_clean[ $typeset_key ] = $value;

			// Sanitize.
			if ( ! empty( $type_used ) && isset( $typeset['sanitize'][ $type_used ] ) ) {
				$options_clean[ $typeset_key ] = gofer_seo_sanitize_callbacks( $options_clean[ $typeset_key ], $typeset['sanitize'][ $type_used ] );
			}
		}

		return $options_clean;
	}

	// TODO Create validate_typesets() function for just typesets (as admin classes do). In turn, would be used for PHP Unit Testing.

	/**
	 * Validate Typeset['type'].
	 *
	 * Corrects any shorthands used, or removes any invalid variables, and logs any errors that occur.
	 *
	 * @since 1.0.0
	 *
	 * @param array $typeset
	 * @return array|bool {
	 *     @type string|array $type         Identifies the variable type.
	 *     @type array        $cast         Additional typesets to cast the variable as.
	 *     @type array        $cast_dynamic Additional (dynamic) typesets to cast the variable as.
	 *     @type mixed        $value        The (default) value.
	 *     @type array        $sanitize {
	 *         Sanitize function/method to use.
	 *
	 *         @type array {TYPE} {
	 *             @type array {
	 *                 @type string|array $callback
	 *                 @type array        $args
	 *             }
	 *         }
	 *     }
	 * }
	 */
	public function validate_typeset( $typeset ) {
		// Correct known shorthands & typos with typeset array.
		$convert_typeset_keys = array(
			'input_type' => 'type',
			'dynamic'    => 'cast_dynamic',
			'default'    => 'value', // Shouldn't be used, but catches it anyways.
		);
		foreach ( $convert_typeset_keys as $old_key => $new_key ) {
			if ( isset( $typeset[ $old_key ] ) ) {
				// TODO Log Error?
				$typeset[ $new_key ] = $typeset[ $old_key ];
				unset( $typeset[ $old_key ] );
			}
		}

		if ( empty( $typeset['type'] ) ) {
			if ( ! empty( $typeset['cast'] ) ) {
				$typeset['type'] = array( 'cast' );
			} elseif ( ! empty( $typeset['cast_dynamic'] ) ) {
				$typeset['type'] = array( 'cast_dynamic' );
			} else {
				// TODO Add log_error. Missing variable `$typeset['type']`.
				return false;
			}
		}

		if ( ! is_array( $typeset['type'] ) ) {
			if ( is_string( $typeset['type'] ) ) {
				$typeset['type'] = array( $typeset['type'] );
			} else {
				return false;
			}
		}

		if ( in_array( 'cast', $typeset['type'], true ) ) {
			if ( empty( $typeset['cast'] ) ) {
				// TODO Add log_error. Missing variable `$typeset['cast']` with 'cast' === `$typeset['type']`.
				return false;
			}
		}
		if ( in_array( 'cast_dynamic', $typeset['type'], true ) ) {
			if ( empty( $typeset['cast_dynamic'] ) ) {
				// TODO Add log_error. Missing variable `$typeset['cast_dynamic']` with 'cast_dynamic' === `$typeset['type']`.
				return false;
			}
			if ( ! isset( $typeset['items'] ) ) {
				// TODO Add log_error. Missing variable `$typeset['items']` with 'cast_dynamic' === `$typeset['type']`.
				return false;
			}
		}

		if ( ! isset( $typeset['value'] ) && ! in_array( 'cast', $typeset['type'], true ) ) {
			// TODO Add log_error. Missing variable `$typeset['value']`.
			return false;
		}

		// Validate 'sanitize' callbacks.
		if ( ! isset( $typeset['sanitize'] ) ) {
			$typeset['sanitize'] = array();
		}

		// Loop through type callbacks.
		$tmp_sanitize_callbacks = array();
		foreach ( $typeset['type'] as $v1_type ) {
			$tmp2_sanitize_callbacks = array();
			if ( isset( $typeset['sanitize'][ $v1_type ] ) ) {
				if ( is_array( $typeset['sanitize'][ $v1_type ] ) ) {
					foreach ( $typeset['sanitize'][ $v1_type ] as $k2_index => $v2_callback ) {
						if (
								is_array( $v2_callback ) &&
								isset( $v2_callback[0] ) &&
								(
									is_string( $v2_callback[0] ) ||
									is_array( $v2_callback[0] ) ||
									is_callable( $v2_callback[0] )
								)
						) {
							$tmp2_sanitize_callbacks[] = $v2_callback;
						}
					}
				}

				unset( $typeset['sanitize'][ $v1_type ] );
			}
			if ( ! empty( $tmp2_sanitize_callbacks ) ) {
				$tmp_sanitize_callbacks[ $v1_type ] = $tmp2_sanitize_callbacks;
			}
		}

		// Loop though any remaining callbacks to used with all type callbacks.
		if ( ! empty( $typeset['sanitize'] ) ) {
			foreach ( $typeset['type'] as $typeset_type ) {
				foreach ( $typeset['sanitize'] as $k2_index => $v2_callback ) {
					if (
							is_array( $v2_callback ) &&
							isset( $v2_callback[0] ) &&
							(
								is_string( $v2_callback[0] ) ||
								is_array( $v2_callback[0] ) ||
								is_callable( $v2_callback[0] )
							)
					) {
						if ( empty( $tmp_sanitize_callbacks[ $typeset_type ] ) ) {
							$tmp_sanitize_callbacks[ $typeset_type ] = array();
						}

						$tmp_sanitize_callbacks[ $typeset_type ][] = $v2_callback;
					}
				}
			}
		}
		$typeset['sanitize'] = $tmp_sanitize_callbacks;

		return $typeset;
	}
}
