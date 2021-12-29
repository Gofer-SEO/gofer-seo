<?php
/**
 * Gofer SEO - Error API
 *
 * Designed similar to WP_Error to handle current/individual instances, and is
 * designed to compress errors that repeat.
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Error.
 *
 * @since 1.0.0
 */
class Gofer_SEO_Error {

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
	public $props  = array();

	/**
	 * Error Data.
	 *
	 * Additional data passed to error. Stores the most recent.
	 *
	 * @since 1.0.0
	 *
	 * @var array[][] $data[ $code ][ $props_hash ] Additional data added to error.
	 */
	public $data   = array();

	/**
	 * Class Instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Gofer_SEO_Error|null
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
	 * @return Gofer_SEO_Error
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Gofer_SEO_Error constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code    Error code.
	 * @param string $message Error message.
	 * @param array  $props {
	 *     (Optional) Error properties.
	 *
	 *     @type string $timestamp
	 *     @type string $type
	 *     @type string $message
	 *     @type string $priority
	 * }
	 * @param array  $data (Optional) Additional error data.
	 * @param array  $args (Optional) Error operation arguments.
	 */
	public function __construct( $code = '', $message = '', $props = array(), $data = array(), $args = array() ) {
		// Set & point instance to this.
		if ( null !== self::$instance ) {
			$this->errors = self::$instance->errors;
			$this->props  = self::$instance->props;
			$this->data   = self::$instance->data;
		}
		self::$instance = $this;

		if ( empty( $code ) ) {
			return;
		}

		$args_default = array(
			'single'          => false,
			'override_preset' => false,
			'call_stack'      => false,
			'log_error'       => true,
		);
		$args = wp_parse_args( $args, $args_default );

		$props['message'] = $message;
		$this->add( $code, $props, $data, $args );

		/* Works for using this to also set self.
		self::$instance = $this;
		$this->error = 'apples';
		echo self::$instance->error;
		*/

//		self::$instance = $this->merge_errors();
	}

	/**
	 * Convert Error Properties to md5 hash.
	 *
	 * @since 1.0.0
	 *
	 * @param array $props
	 * @return string
	 */
	public function convert_props_to_hash( $props ) {
		$props_hash = array(
			$props['type'],
			$props['message'],
			$props['priority'],
		);

		return md5( wp_json_encode( $props_hash ) );
	}

	/**
	 * Adds an error.
	 *
	 * @since 1.0.0
	 *
	 * @param string|int $code  Error code.
	 * @param array      $props Error properties.
	 * @param mixed      $data  Optional. Error data.
	 */
	public function add( $code, $props, $data = '', $args = array() ) {
		$props = wp_parse_args( $props, array(
			'timestamps' => array(),
			'type'       => 'notice',
			'message'    => '',
			'priority'   => 10,
		));
		$args = wp_parse_args( $args, array(
			'log_error'  => true,
			'call_stack' => false,
		));

		$props_hash = $this->convert_props_to_hash( $props );

		$timestamps = $props['timestamps'];
		unset( $props['timestamps'] );
		if ( empty( $timestamps ) ) {
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
		 * @param array           $args            Additional arguments to handling the error.
		 * @param Gofer_SEO_Error $gofer_seo_error The Gofer_SEO_Error object.
		 */
		do_action( 'gofer_seo_error_added', $code, $props, $data, $args, $this );

		new Gofer_SEO_Errors( $this );
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
		return ! empty( $this->errors );
	}

	/**
	 * Retrieves all error codes.
	 *
	 * @since 1.0.0
	 *
	 * @return array|int[]|string[] List of error codes, if available.
	 */
	public function get_error_codes() {
		if ( ! $this->has_errors() ) {
			return array();
		}

		return array_keys( $this->errors );
	}

	/**
	 * @param        $code
	 * @param string $props_index
	 * @return mixed
	 */
	public function get_error_props( $code, $props_index = '' ) {
		if ( empty( $props_index ) && ! empty( $this->errors[ $code ] ) ) {
			$props_index = $this->errors[ $code ][0];
		}
		if ( isset( $this->props[ $code ][ $props_index ] ) ) {
			return $this->props[ $code ][ $props_index ];
		}

		return false;
	}

	/**
	 * @param        $code
	 * @param string $props_index
	 * @return mixed
	 */
	public function get_error_data( $code, $props_index = '' ) {
		if ( empty( $props_index ) && ! empty( $this->errors[ $code ] ) ) {
			$props_index = $this->errors[ $code ][0];
		}

		if ( isset( $this->data[ $code ][ $props_index ] ) ) {
			return $this->data[ $code ][ $props_index ];
		}

		return false;
	}

	/**
	 * Remove specified error.
	 *
	 * @since 1.0.0
	 *
	 * @param string|int $code
	 * @param string     $props_index
	 */
	public function remove( $code, $props_index = '' ) {
		if ( isset( $this->errors[ $code ] ) ) {
			if ( ! empty( $props_index ) && isset( $this->errors[ $code ][ $props_index ] ) ) {
				//do_action( 'gofer_seo_error_removed', $code, $props_index );
				unset( $this->errors[ $code ][ $props_index ] );
				unset( $this->props[ $code ][ $props_index ] );
				unset( $this->data[ $code ][ $props_index ] );
			} else {
				//do_action( 'gofer_seo_error_removed', $code, $props_index );
				unset( $this->errors[ $code ] );
				unset( $this->props[ $code ] );
				unset( $this->data[ $code ] );
			}
		}
	}

//	protected function merge_errors( Gofer_SEO_Error $to, Gofer_SEO_Error $from ) {
//
//		return $to;
//	}

}
