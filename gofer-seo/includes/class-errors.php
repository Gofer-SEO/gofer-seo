<?php
/**
 * Gofer SEO - Errors API (Combined Errors)
 *
 * Handle past/multiple errors, and designed to compress errors to reduce size & operations.
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Errors.
 *
 * @since 1.0.0
 */
class Gofer_SEO_Errors {

	/**
	 * List of Errors.
	 *
	 * @since 1.0.0
	 *
	 * @var int[][] $errors[ $code ][ $props_hash ] Error Unix timestamps.
	 */
	public $errors = array();

	/**
	 * Error Properties.
	 *
	 * @since 1.0.0
	 *
	 * @var array $props[ $code ][ $props_hash ] {
	 *     @type int[]  $timestamps Array of unix timestamps
	 *     @type string $type       Error type.
	 *     @type string $message    Error message.
	 *     @type int    $priority   Error priority.
	 * }
	 */
	public $props = array();

	/**
	 * Error Data.
	 *
	 * Additional data passed to error. Stores the most recent.
	 *
	 * @since 1.0.0
	 *
	 * @var array[][] $data[ $code ][ $props_hash ] Additional data added to error.
	 */
	public $data = array();

	/**
	 * Error Functions.
	 *
	 * @since 1.0.0
	 *
	 * @var Gofer_SEO_Error_Functions $functions
	 */
	protected $functions;

	/**
	 * Class Instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Gofer_SEO_Errors|null
	 */
	private static $instance = null;

	/**
	 * Throws error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @ignore
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin\' huh?', 'gofer-seo' ), esc_html( GOFER_SEO_VERSION ) );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @ignore
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin\' huh?', 'gofer-seo' ), esc_html( GOFER_SEO_VERSION ) );
	}

	/**
	 * Get Instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Gofer_SEO_Errors
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Gofer_SEO_Errors constructor.
	 *
	 * Similar to singleton, but uses additional params.
	 *
	 * TODO ? Use *_Error class to create|add-to *_Errors class.
	 *
	 * @since 1.0.0
	 *
	 * @param Gofer_SEO_Error|null $error (Optional) Gofer SEO Error object.
	 * @param array                $args  (Optional) Error operation arguments.
	 */
	public function __construct( Gofer_SEO_Error $error = null, $args = array() ) {
		// Set & point instance to this.
		if ( null !== self::$instance ) {
			$this->errors    = self::$instance->errors;
			$this->props     = self::$instance->props;
			$this->data      = self::$instance->data;
			$this->functions = self::$instance->functions;
		} else {
			$this->add_hooks();
			$this->functions = new Gofer_SEO_Error_Functions();
		}
		self::$instance = $this;

		if ( $error instanceof Gofer_SEO_Error ) {
			$this->add_gofer_seo_error( $error, $args );
		}
	}

	/**
	 * Add Hooks.
	 *
	 * @since 1.0.0
	 */
	protected function add_hooks() {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		// Use class directly, and hook used for extending functionality.
		//add_action( 'gofer_seo_error_added', array( $this, 'add' ), 10, 4 );

		// PHP.
		//set_error_handler( array( $this, 'add_php_error' ) );

		if ( true === $gofer_seo_options->options['modules']['debugger']['enable_wp_errors'] ) {
			// WP_Error::add() - WP 5.6.0.
			add_action( 'wp_error_added', array( $this, 'add_wp_error' ), 10, 4 );

			// wp_die() - WP 3.0.0 - 5.2.0.
			add_filter( 'wp_die_ajax_handler', array( $this, 'at_wp_die_handler_set_callback' ) );
			add_filter( 'wp_die_json_handler', array( $this, 'at_wp_die_handler_set_callback' ) );
			add_filter( 'wp_die_jsonp_handler', array( $this, 'at_wp_die_handler_set_callback' ) );
			add_filter( 'wp_die_xmlrpc_handler', array( $this, 'at_wp_die_handler_set_callback' ) );
			add_filter( 'wp_die_xml_handler', array( $this, 'at_wp_die_handler_set_callback' ) );
			add_filter( 'wp_die_handler', array( $this, 'at_wp_die_handler_set_callback' ) );
		}

		// Save errors on shutdown.
		add_action( 'shutdown', array( 'Gofer_SEO_Errors', 'at_shutdown_save_errors' ) );
	}

	/**
	 * Sets Callback Function for wp_die() Handler(s).
	 *
	 * @since 1.0.0
	 *
	 * @param string|array $callback
	 * @return array
	 */
	public function at_wp_die_handler_set_callback( $callback ) {
		return array( $this, 'add_wp_die' );
	}

	/**
	 * Add Gofer SEO Error.
	 *
	 * @since 1.0.0
	 *
	 * @param Gofer_SEO_Error $error Gofer SEO error object.
	 * @param array           $args  (Optional) Error operation arguments.
	 */
	public function add_gofer_seo_error( Gofer_SEO_Error $error, $args = array() ) {
		foreach ( $error->errors as $code => $error_arr ) {
			foreach ( $error_arr as $props_hash => $timestamps ) {
				// TODO Change Error(s) class to have a single error variable.
				if (
						! isset( $this->errors[ $code ][ $props_hash ] ) ||
						(
							isset( $this->errors[ $code ][ $props_hash ] ) &&
							! count( array_diff( $timestamps, $this->errors[ $code ][ $props_hash ] ) )
						)
				) {
					$data = isset( $error->data[ $code ][ $props_hash ] ) ? $error->data[ $code ][ $props_hash ] : array();
					$this->add( $code, $error->props[ $code ][ $props_hash ], $data );
				}
			}
		}
	}

	/**
	 * Add PHP Error.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $errno      Level of the error raised, as an integer.
	 * @param string $errstr     Error message
	 * @param string $errfile    Filename that the error was raised in
	 * @param int    $errline    Line number where the error was raised
	 * @param array  $errcontext An array that points to the active symbol table at the point the error occurred
	 */
	public function add_php_error( $errno, $errstr, $errfile, $errline, $errcontext ) {}

	/**
	 * WP Error Added (Hook).
	 *
	 * Hook added in WP 5.6.0.
	 *
	 * @since 1.0.0
	 *
	 * @param string|int $code     Error code.
	 * @param string     $message  Error message.
	 * @param mixed      $data     Error data. Might be empty.
	 * @param WP_Error   $wp_error The WP_Error object.
	 */
	public function add_wp_error( $code, $message, $data, $wp_error ) {
		$props = array(
			'type'    => 'wp_error',
			'message' => $message,
		);
		$this->add( $code, $props, $data );
	}

	/**
	 * Add WP Die Error.
	 *
	 * @since 1.0.0
	 *
	 * @param string|WP_Error $message Error Message.
	 * @param string          $title   Error Title.
	 * @param array           $args    Additional arguments.
	 */
	public function add_wp_die( $message, $title, $args ) {
		if ( empty( $message ) || ( is_int( $message ) && 1 > $message ) || $message instanceof WP_Error ) {
			// Remove filters and call wp_die(); rather than shadowing the function.
			remove_filter( 'wp_die_ajax_handler', array( $this, 'at_wp_die_handler_set_callback' ) );
			remove_filter( 'wp_die_json_handler', array( $this, 'at_wp_die_handler_set_callback' ) );
			remove_filter( 'wp_die_jsonp_handler', array( $this, 'at_wp_die_handler_set_callback' ) );
			remove_filter( 'wp_die_xmlrpc_handler', array( $this, 'at_wp_die_handler_set_callback' ) );
			remove_filter( 'wp_die_xml_handler', array( $this, 'at_wp_die_handler_set_callback' ) );
			remove_filter( 'wp_die_handler', array( $this, 'at_wp_die_handler_set_callback' ) );

			// Should already be sanitized since this is a wp_die() hook/callback, but will sanitize anyway.
			// WP_Error can't be sanitized & $args contains mixed array values.
			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			wp_die(
				( $message instanceof WP_Error ) ? $message : esc_html( $message ),
				esc_html( $title ),
				( is_string( $args) ) ? esc_html( $args ) : $args
			);
			// phpcs:enable
		}

		// Get type.
		if ( wp_doing_ajax() ) {
			$type = 'wp_die_ajax';
		} elseif ( wp_is_json_request() ) {
			$type = 'wp_die_json';
		} elseif ( wp_is_jsonp_request() ) {
			$type = 'wp_die_jsonp';
		} elseif ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			$type = 'wp_die_xmlrpc';
		} elseif (
				wp_is_xml_request() ||
				isset( $wp_query ) &&
				(
					function_exists( 'is_feed' ) && is_feed() ||
					function_exists( 'is_comment_feed' ) && is_comment_feed() ||
					function_exists( 'is_trackback' ) && is_trackback()
				)
		) {
			$type = 'wp_die_xml';
		} else {
			$type = 'wp_die';
		}

		$props = array(
			'type'    => $type,
			'message' => $message,
			'title'   => $title,
		);
		// TODO Find a more unique code; instead of `$type`.
		// Could use props_hash.
		$this->add( $type, $props, $args );

		// Remove filters and call wp_die(); rather than shadowing the function.
		remove_filter( 'wp_die_ajax_handler', array( $this, 'at_wp_die_handler_set_callback' ) );
		remove_filter( 'wp_die_json_handler', array( $this, 'at_wp_die_handler_set_callback' ) );
		remove_filter( 'wp_die_jsonp_handler', array( $this, 'at_wp_die_handler_set_callback' ) );
		remove_filter( 'wp_die_xmlrpc_handler', array( $this, 'at_wp_die_handler_set_callback' ) );
		remove_filter( 'wp_die_xml_handler', array( $this, 'at_wp_die_handler_set_callback' ) );
		remove_filter( 'wp_die_handler', array( $this, 'at_wp_die_handler_set_callback' ) );

		// Should already be sanitized since this is a wp_die() hook/callback, but will sanitize anyway.
		// WP_Error can't be sanitized & $args contains mixed array values.
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		wp_die(
			( $message instanceof WP_Error ) ? $message : esc_html( $message ),
			esc_html( $title ),
			( is_string( $args) ) ? esc_html( $args ) : $args
		);
		// phpcs:enable.
	}

	/**
	 * Convert Error Properties to md5 hash.
	 *
	 * @since 1.0.0
	 *
	 * @param array $props Error properties.
	 * @return string
	 */
	public function convert_props_to_hash( $props ) {
		$props_hash = array(
			$props['type'],
			$props['message'],
			$props['priority'],
		);
		$props_hash = md5( wp_json_encode( $props_hash ) );

		return $props_hash;
	}

	/**
	 * Adds an error.
	 *
	 * @since 1.0.0
	 *
	 * @param string|int $code  Error code.
	 * @param array      $props Error properties.
	 * @param mixed      $data  Optional. Error data.
	 * @param array      $args  Error operation arguments.
	 */
	public function add( $code, $props, $data = '', $args = array() ) {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		if ( true !== $gofer_seo_options->options['modules']['debugger']['enable_errors'] ) {
			return;
		}

		$props = wp_parse_args( $props, array(
			'timestamps' => array(),
			'type'       => 'error',
			'message'    => '',
			'priority'   => 10,
		));
		$args = wp_parse_args( $args, array(
			'ignore'     => false,
			'single'     => false,
			'log_error'  => true,
			'call_stack' => false,
		));

		$props_hash = $this->convert_props_to_hash( $props );

		// TODO Function preset
		// vvv (Preset) Error Operations.
		$preset_error_ops = $this->get_preset_operations();
		if ( isset( $preset_error_ops[ $code ] ) ) {
			if (
					(
						isset( $args['override_preset'] ) &&
						$args['override_preset']
					) ||
					$preset_error_ops[ $code ]['override_preset']
			) {
				$args = wp_parse_args(
					$args,
					array(
						$preset_error_ops[ $code ]['ignore'],
						$preset_error_ops[ $code ]['single'],
						$preset_error_ops[ $code ]['log_error'],
						$preset_error_ops[ $code ]['call_stack'],
					)
				);
			} else {
				$args = wp_parse_args(
					array(
						$preset_error_ops[ $code ]['ignore'],
						$preset_error_ops[ $code ]['single'],
						$preset_error_ops[ $code ]['call_stack'],
						$preset_error_ops[ $code ]['log_error'],
					),
					$args
				);
			}
		}
		// vvv TODO Create $this->error_operations()?
		// Ignore.
		if ( $args['ignore'] ) {
			return;
		}
		// Single.
		if ( $args['single'] ) {
			$errors = $this->get_errors_db();

			if ( isset( $this->errors[ $code ] ) ) {
				if ( ! isset( $errors[ $code ] ) ) {
					$errors[ $code ] = array();
				}

				$errors[ $code ] = array_merge_recursive( $errors[ $code ], $this->errors[ $code ] );
				foreach ( $errors[ $code ] as $err_hash => $err_timestamps ) {
					$errors[ $code ][ $err_hash ] = array_unique( $errors[ $code ][ $err_hash ] );
				}
			}

			// vvv TODO Create *_array_flip_recursive();
			$timestamp_hashes = array();
			if ( isset( $errors[ $code ]  ) ) {
				foreach ( $errors[ $code ] as $err_hash => $err_timestamps ) {
					foreach ( $err_timestamps as $err_timestamp ) {
						if ( ! isset( $timestamp_hashes[ $err_timestamp ] ) ) {
							$timestamp_hashes[ $err_timestamp ] = array();
						}

						$timestamp_hashes[ $err_timestamp ][] = $err_hash;
					}
				}
			}
			// ^^^ TODO Create *_array_flip_recursive();

			if ( ! empty( $timestamp_hashes ) ) {
				$props_hash = $timestamp_hashes[ array_key_last( $timestamp_hashes ) ][0];
			}
		}
		// Call Stack.
		$call_stack = '';
		if ( $args['call_stack'] ) {
			// Note: Avoid logging file paths to database.
			$call_stack = $this->functions::get_call_stack();
		}
		// Log Error.
		if ( $args['log_error'] ) {
			$log_message = sprintf(
				'%1$s - %2$s%3$s%4$s',
				$code,
				$props['message'],
				PHP_EOL,
				$call_stack
			);
			$this->functions::log_error( $log_message );
		}
		// ^^^ TODO Create $this->error_operations()?

		$timestamps = $props['timestamps'];
		unset( $props['timestamps'] );
		if ( empty( $timestamps ) ) {
			// $timestamps[] = gmdate('Y-m-d H:i:s');
			// Use Unix time
			$timestamps[] = time();
		}

		// Add Error(s).
		if ( ! isset( $this->errors[ $code ][ $props_hash ] ) ) {
			if ( ! isset( $this->errors[ $code ] ) ) {
				$this->errors[ $code ] = array();
			}
			$this->errors[ $code ][ $props_hash ] = array();
		}

		// Add errors[] timestamps.
		$this->errors[ $code ][ $props_hash ] = array_merge(
			$this->errors[ $code ][ $props_hash ],
			$timestamps
		);

		$this->errors[ $code ][ $props_hash ] = array_unique( $this->errors[ $code ][ $props_hash ] );

		// Add properties.
		$this->add_props( $props, $code, $props_hash );

		// Add data.
		if ( ! empty( $data ) ) {
			$this->add_data( $data, $code, $props_hash );
		}

		/**
		 * Fires when an error is added to a Gofer_SEO_Error object.
		 *
		 * @since 1.0.0
		 *
		 * @param string|int      $code            Error code.
		 * @param array           $props           Error properties.
		 * @param mixed           $data            Error data. Might be empty.
		 * @param Gofer_SEO_Error $gofer_seo_error The Gofer_SEO_Error object.
		 */
		do_action( 'gofer_seo_errors_added', $code, $props, $data, $this );
	}

	/**
	 * Add (Error) Properties to Object.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $props      Error properties.
	 * @param string $code       Error code.
	 * @param string $props_hash Error property hash.
	 */
	public function add_props( $props, $code, $props_hash ) {
		if ( ! isset( $this->props[ $code ][ $props_hash ] ) ) {
			if ( ! isset( $this->props[ $code ] ) ) {
				$this->props[ $code ] = array();
			}

			$this->props[ $code ][ $props_hash ] = array();
		}

		$this->props[ $code ][ $props_hash ] = $props;
	}

	/**
	 * Add Data to Object.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed      $data       Error data.
	 * @param string|int $code       Error code.
	 * @param string     $props_hash Error property hash.
	 */
	public function add_data( $data, $code, $props_hash ) {
		if ( ! isset( $this->data[ $code ][ $props_hash ] ) ) {
			if ( ! isset( $this->data[ $code ] ) ) {
				$this->data[ $code ] = array();
			}

			$this->data[ $code ][ $props_hash ] = array();
		}

		$this->data[ $code ][ $props_hash ][] = $data;
	}

	/**
	 * Verifies if the instance contains errors.
	 *
	 * @since 1.0.0
	 *
	 * @return bool If the instance contains errors.
	 */
	public function has_errors() {
		return ( ! empty( $this->errors ) ) ? true : false;
	}

	/**
	 * Get Errors Stored in Database.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function get_errors_db() {
		$errors = get_option( 'gofer_seo_errors' );

		if ( false === $errors ) {
			$errors = array();
		}

		return $errors;
	}

	/**
	 * Get Error Properties Stored in Database.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function get_error_props_db() {
		$error_props = get_option( 'gofer_seo_error_props' );

		if ( false === $error_props ) {
			$error_props = array();
		}

		return $error_props;
	}

	/**
	 * Get Error Data Stored in Database.
	 *
	 * @since 1.0.0
	 *
	 * @return array|false
	 */
	protected function get_error_data_db() {
		$error_data = get_option( 'gofer_seo_error_data' );

		if ( false === $error_data ) {
			$error_data = array();
		}

		return $error_data;
	}

	/**
	 * Update Errors on Database.
	 *
	 * TODO SQL Table
	 * Set based on
	 *     * error code.
	 *     * property hash; md5() 32 character string.
	 * Store data (changes uniquely & frequently)
	 *     * timestamp
	 * {
	 *     code       varchar(255) index
	 *     props_hash varchar(32)  index
	 *     timestamp  datetime
	 * }
	 *
	 * @since 1.0.0
	 *
	 * @param array $errors See `$this->errors`.
	 * @return false
	 */
	protected function update_errors_db( $errors ) {
		if ( ! is_array( $errors ) ) {
			return false;
		}

		update_option( 'gofer_seo_errors', $errors );
	}

	/**
	 * Update Error Properties on Database.
	 *
	 * TODO SQL Table
	 * {
	 *     code       varchar(255) index
	 *     props_hash varchar(32)  index
	 *     -----------------------------
	 *     prop_key   varchar(255) index
	 *     prop_value longtext
	 *     -----------------------------
	 * }
	 *
	 * @since 1.0.0
	 *
	 * @param array $error_props See `$this->props`.
	 * @return false
	 */
	protected function update_error_props_db( $error_props ) {
		if ( ! is_array( $error_props ) ) {
			return false;
		}

		update_option( 'gofer_seo_error_props', $error_props );
	}

	/**
	 * Update Error Data on Database.
	 *
	 * TODO SQL Table
	 * {
	 *     code       varchar(255) index
	 *     props_hash varchar(32)  index
	 *     -----------------------------
	 *     data_key   varchar(255) index
	 *     data_value longtext
	 *     -----------------------------
	 * }
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $error_data See `$this->data`.
	 */
	protected function update_error_data_db( $error_data ) {
		if ( ! is_array( $error_data ) ) {
			return false;
		}

		update_option( 'gofer_seo_error_data', $error_data );
	}

	/**
	 * Load (Errors) from Database.
	 *
	 * @since 1.0.0
	 */
	public function load_from_db() {
		$tmp_errors      = $this->errors;
		$tmp_error_props = $this->props;
		$tmp_error_data  = $this->data;

		$errors_db      = $this->get_errors_db();
		$error_props_db = $this->get_error_props_db();
		$error_data_db  = $this->get_error_data_db();

		foreach ( $errors_db as $code => $props_arr ) {
			foreach ( $props_arr as $props_hash => $timestamps ) {
				if ( ! isset( $tmp_errors[ $code ] ) ) {
					$tmp_errors[ $code ]      = array();
					$tmp_error_props[ $code ] = array();
				}
				if ( ! isset( $tmp_errors[ $code ][ $props_hash ] ) ) {
					// Props.
					$tmp_errors[ $code ][ $props_hash ]      = array();
					$tmp_error_props[ $code ][ $props_hash ] = $error_props_db[ $code ][ $props_hash ];

					// Data.
					if ( isset( $error_data_db[ $code ][ $props_hash ] ) ) {
						if ( ! isset( $tmp_error_data[ $code ] ) ) {
							$tmp_error_data[ $code ]  = array();
						}
						$tmp_error_data[ $code ][ $props_hash ] = $error_data_db[ $code ][ $props_hash ];
					}
				}

				// Add errors timestamps.
				$tmp_errors[ $code ][ $props_hash ] = array_unique(
					array_merge(
						$tmp_errors[ $code ][ $props_hash ],
						$timestamps
					)
				);
			}
		}

		$this->errors = $tmp_errors;
		$this->props  = $tmp_error_props;
		$this->data   = $tmp_error_data;
	}

	/**
	 * Add (Errors) to Database.
	 *
	 * @since 1.0.0
	 *
	 * @param Gofer_SEO_Errors $errors
	 */
	public function add_to_db( $errors ) {
		if ( ! $errors instanceof Gofer_SEO_Errors ) {
			return false;
		}
		$errors_db = $this->get_errors_db();

		$new_error_props = array();
		$new_error_data  = array();
		foreach ( $errors->errors as $code => $props_arr ) {
			foreach ( $props_arr as $props_hash => $timestamps ) {
				// Add new error properties if none currently exist.
				if ( ! isset( $errors_db[ $code ] ) ) {
					$errors_db[ $code ]       = array();
					$new_error_props[ $code ] = array();
				}
				// New Properties.
				if ( empty( $errors_db[ $code ][ $props_hash ] ) ) {
					$errors_db[ $code ][ $props_hash ] = array();

					// Add properties on new error(s).
					$new_error_props[ $code ][ $props_hash ] = $errors->props[ $code ][ $props_hash ];
				}
				// New Error Data.
				if ( isset( $errors->data[ $code ] ) && ! empty( $errors->data[ $code ][ $props_hash ] ) ) {
					if ( ! isset( $new_error_data[ $code ] ) ) {
						$new_error_data[ $code ] = array();
						if ( ! isset( $new_error_data[ $code ][ $props_hash ] ) ) {
							$new_error_data[ $code ][ $props_hash ] = array();
						}
					}
				}

				// Add errors timestamps.
				$errors_db[ $code ][ $props_hash ] = array_unique(
					array_merge(
						$errors_db[ $code ][ $props_hash ],
						$timestamps
					)
				);
			}
		}

		// If any new error properties, add to error properties database.
		if ( ! empty( $new_error_props ) ) {
			$error_props_db = $this->get_error_props_db();

			foreach ( $new_error_props as $code => $props_arr ) {
				foreach ( $props_arr as $props_hash => $props ) {
					if ( ! isset( $error_props_db[ $code ] ) ) {
						$error_props_db[ $code ] = array();
					}
					if ( empty( $error_props_db[ $code ][ $props_hash ] ) ) {
						$error_props_db[ $code ][ $props_hash ] = $props;
					}
				}
			}

			$this->update_error_props_db( $error_props_db );
		}

		if ( ! empty( $new_error_data ) ) {
			$error_data_db = $this->get_error_data_db();

			foreach ( $new_error_data as $code => $data_arr ) {
				foreach ( $data_arr as $props_hash => $data ) {
					if ( ! isset( $error_data_db[ $code ] ) ) {
						$error_data_db[ $code ] = array();
					}
					if ( empty( $error_data_db[ $code ][ $props_hash ] ) ) {
						$error_data_db[ $code ][ $props_hash ] = $data;
					}
				}
			}

			$this->update_error_data_db( $error_data_db );
		}

		$this->update_errors_db( $errors_db );

		return true;
	}

	/**
	 * Remove (Error) from Database.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code       Error code.
	 * @param string $props_hash Error property hash.
	 * @return void
	 */
	public function remove_from_db( $code, $props_hash = '' ) {
		$errors_db      = $this->get_errors_db();
		$error_props_db = $this->get_error_props_db();


		if ( isset( $error_db[ $code ] ) ) {
			if ( ! empty( $props_hash ) && isset( $errors_db[ $code ][ $props_hash ] ) ) {
				unset( $errors_db[ $code ][ $props_hash ] );
				unset( $error_props_db[ $code ][ $props_hash ] );
			} else {
				unset( $errors_db[ $code ] );
				unset( $error_props_db[ $code ] );
			}
		}

		$this->update_errors_db( $errors_db );
		$this->update_error_props_db( $error_props_db );
	}

	/**
	 * Delete Database.
	 *
	 * Deletes all errors.
	 *
	 * @since 1.0.0
	 */
	public function delete_db() {
		delete_option( 'gofer_seo_errors' );
		delete_option( 'gofer_seo_error_props' );
		delete_option( 'gofer_seo_error_data' );
	}

	/**
	 * At Shutdown Hook - Save Errors.
	 *
	 * If any errors occur and stored on this object/class, then add to database.
	 *
	 * @since 1.0.0
	 */
	public static function at_shutdown_save_errors() {
		$errors = new Gofer_SEO_Errors();
		if ( $errors->has_errors() ) {
			$errors->add_to_db( $errors );
		}
	}

	/**
	 * Get Default Preset Operations.
	 *
	 * @since 1.0.0
	 *
	 * @return false[] {
	 *     @type boolean $ignore          Ignore error.
	 *     @type boolean $single          Set/use a single (`$props_hash`) error properties.
	 *     @type boolean $override_preset Overrides the Preset Operations with `$args` param passed.
	 *     @type boolean $call_stack      Add call stack to log.
	 *     @type boolean $log_error       Logs the error.
	 * }
	 */
	public function get_default_preset_operations() {
		return array(
			'ignore'          => false,
			'single'          => false,
			'override_preset' => false,
			// Args.
			'call_stack'      => false,
			'log_call_stack'  => false,
			'log'             => false,
			'log_error'       => false,
		);
	}

	/**
	 * Get Preset Error Operations.
	 *
	 * Used for the additional operations with errors; ignore, log error, call stack.
	 *
	 * @since 1.0.0
	 *
	 * @return false[][]
	 */
	public function get_preset_operations() {
		// Example.
		$presets = array(
			'error_code_here'                    => array(
				'ignore'          => false,
				'single'          => false,
				'override_preset' => false,

				// Args.
				'call_stack'      => false,
				'log_error'       => false,
			),
			'gofer_seo_context_no_wp_obj'        => array(
				'call_stack' => true,
				'log_error'  => true,
			),
			'gofer_seo_module_general_title_rewrite_conflict' => array(
				'log_error' => true,
			),
			'gofer_seo_module_general_title_rewrite_handlers' => array(
				'log_error' => true,
			),
			'gofer_seo_module_sitemap_ping_1'    => array(
				'log_error' => true,
			),
			'gofer_seo_module_sitemap_ping_2'    => array(
				'log_error' => true,
			),
			'gofer_seo_module_sitemap_log_stats' => array(
				'log_error' => true,
			),
		);

		// TODO Gofer SEO Options.

		/**
		 * Preset operations used when an error occurs.
		 *
		 * @since 1.0.0
		 *
		 * @param false[][] $presets {
		 *     @type array $$error_code {
		 *         @type boolean $ignore          Ignore error.
		 *         @type boolean $single          Set/use a single (`$props_hash`) error properties.
		 *         @type boolean $override_preset Overrides the Preset Operations with `$args` param passed.
		 *         @type boolean $call_stack      Add call stack to log.
		 *         @type boolean $log_error       Logs the error.
		 *     }
		 * }
		 */
		$presets = apply_filters( 'gofer_seo_error_preset_operations', $presets );

		foreach ( $presets as $error_code => $preset ) {
			$presets[ $error_code ] = wp_parse_args( $preset, $this->get_default_preset_operations() );
		}

		return $presets;
	}

	/**
	 * Get Ignored Errors.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_ignored_errors() {
		$ignore_errors = array();

		return $ignore_errors;
	}

}
