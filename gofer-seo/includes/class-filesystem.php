<?php
/**
 * Gofer SEO - Filesystem.
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Filesystem
 *
 * @since 1.0.0
 */
class Gofer_SEO_Filesystem {

	/**
	 * Singleton Instance.
	 *
	 * @since  1.0.0
	 * @access private
	 *
	 * @var null $instance Singleton Class Instance.
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
	 * @since  1.0.0
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
	 * @since  1.0.0
	 * @access private
	 */
	private function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin\' huh?', 'gofer-seo' ), esc_html( GOFER_SEO_VERSION ) );
	}

	/**
	 * Get Singleton Instance.
	 *
	 * @since  1.0.0
	 *
	 * @return Gofer_SEO_Filesystem
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Gofer_SEO_Filesystem constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->_requires();
		$access_type = get_filesystem_method();
		if ( 'direct' !== $access_type ) {
			// TODO Add Notice.
		}
	}

	/**
	 * Requires.
	 *
	 * @since 1.0.0
	 */
	private function _requires() {
		require_once ABSPATH . 'wp-admin/includes/template.php';
		require_once ABSPATH . 'wp-admin/includes/screen.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}

	/**
	 * Get WP_Filesystem.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_Filesystem_Direct|false
	 */
	public function get_wp_filesystem() {
		if ( ! array_key_exists( 'wp_filesystem', $GLOBALS ) ) {
			$url = site_url() . '/wp-admin/';

			$creds = request_filesystem_credentials( $url, '', false, false, array() );
			if ( ! WP_Filesystem( $creds ) ) {
				// Produce an error.
				request_filesystem_credentials( $url, '', true, false, null );
				return false;
			}
		}

		global $wp_filesystem;

		return $wp_filesystem;
	}

	/**
	 * See if a file exists using WP Filesystem.
	 *
	 * @since 1.0.0
	 *
	 * @param string $filename
	 * @return bool
	 */
	function file_exists( $filename ) {
		$wpfs = $this->get_wp_filesystem();
		if ( is_object( $wpfs ) ) {
			return $wpfs->exists( $filename );
		}

		return $wpfs;
	}

	/**
	 * See if the directory entry is a file using WP Filesystem.
	 *
	 * @since 1.0.0
	 *
	 * @param string $filename
	 * @return bool
	 */
	function is_file( $filename ) {
		$wpfs = $this->get_wp_filesystem();
		if ( is_object( $wpfs ) ) {
			return $wpfs->is_file( $filename );
		}

		return $wpfs;
	}

	/**
	 * List files in a directory using WP Filesystem.
	 *
	 * @since 1.0.0
	 *
	 * @param string $path
	 * @return array|bool
	 */
	function scandir( $path ) {
		$wpfs = $this->get_wp_filesystem();
		if ( is_object( $wpfs ) ) {
			$dirlist = $wpfs->dirlist( $path );
			if ( empty( $dirlist ) ) {
				return $dirlist;
			}

			return array_keys( $dirlist );
		}

		return $wpfs;
	}

	/**
	 * Load multiple files.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $options
	 * @param array  $opts
	 * @param string $prefix
	 * @return mixed
	 */
	function load_files( $options, $opts, $prefix ) {
		foreach ( $opts as $opt => $file ) {
			$opt      = $prefix . $opt;
			$file     = ABSPATH . $file;
			$contents = $this->load_file( $file );
			if ( false !== $contents ) {
				$options[ $opt ] = $contents;
			}
		}

		return $options;
	}

	/**
	 * Load a file through WP Filesystem; implement basic support for offset and maxlen.
	 *
	 * @since 1.0.0
	 *
	 * @param string $filename
	 * @param int    $offset
	 * @param int    $maxlen
	 * @return bool|mixed
	 */
	function load_file( $filename, $offset = -1, $maxlen = -1 ) {
		$wpfs = $this->get_wp_filesystem();
		if ( is_object( $wpfs ) ) {
			if ( ! $wpfs->exists( $filename ) ) {
				return false;
			}
			if ( ( $offset > 0 ) || ( $maxlen >= 0 ) ) {
				if ( 0 === $maxlen ) {
					return '';
				}
				if ( 0 > $offset ) {
					$offset = 0;
				}
				$file = $wpfs->get_contents( $filename );
				if ( ! is_string( $file ) || empty( $file ) ) {
					return $file;
				}
				if ( 0 > $maxlen ) {
					return Gofer_SEO_PHP_Functions::substr( $file, $offset );
				} else {
					return Gofer_SEO_PHP_Functions::substr( $file, $offset, $maxlen );
				}
			} else {
				return $wpfs->get_contents( $filename );
			}
		}

		return false;
	}

	/**
	 * Save multiple files.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $opts
	 * @param string $prefix
	 */
	function save_files( $opts, $prefix ) {
		check_ajax_referer( 'gofer_seo_nonce' );

		foreach ( $opts as $opt => $file ) {
			$opt = $prefix . $opt;
			if ( isset( $_POST[ $opt ] ) ) {
				$output = filter_var( INPUT_POST, $opt, FILTER_REQUIRE_ARRAY );
				$output = stripslashes_deep( $output );
				$file   = ABSPATH . $file;
				$this->save_file( $file, $output );
			}
		}
	}

	/**
	 * Save a file through WP Filesystem.
	 *
	 * @since 1.0.0
	 *
	 * @param string $filename
	 * @param string $contents
	 * @return bool
	 */
	function save_file( $filename, $contents ) {
		/* translators: %s is a placeholder and will be replaced with the name of the relevant file. */
		$failed_str = sprintf( __( 'Failed to write file %s!', 'gofer-seo' ) . "\n", $filename );
		/* translators: %s is a placeholder and will be replaced with the name of the relevant file. */
		$readonly_str = sprintf( __( 'File %s isn\'t writable!', 'gofer-seo' ) . "\n", $filename );

		$wpfs = $this->get_wp_filesystem();
		if ( is_object( $wpfs ) ) {
			$file_exists = $wpfs->exists( $filename );
			if ( ! $file_exists || $wpfs->is_writable( $filename ) ) {
				if ( $wpfs->put_contents( $filename, $contents ) === false ) {
					return $this->output_error( $failed_str );
				}
			} else {
				return $this->output_error( $readonly_str );
			}

			return true;
		}

		return false;
	}

	/**
	 * Delete multiple files.
	 *
	 * @since 1.0.0
	 *
	 * @param array $opts
	 */
	function delete_files( $opts ) {
		foreach ( $opts as $opt => $file ) {
			$file = ABSPATH . $file;
			$this->delete_file( $file );
		}
	}

	/**
	 * Delete a file through WP Filesystem.
	 *
	 * @since 1.0.0
	 *
	 * @param string $filename
	 * @return bool
	 */
	function delete_file( $filename ) {
		$wpfs = $this->get_wp_filesystem();
		if ( is_object( $wpfs ) ) {
			if ( $wpfs->exists( $filename ) ) {
				if ( $wpfs->delete( $filename ) === false ) {
					/* translators: %s is a placeholder and will be replaced with the name of the relevant file. */
					$this->output_error( sprintf( __( 'Failed to delete file %s!', 'gofer-seo' ) . "\n", $filename ) );
				} else {
					return true;
				}
			} else {
				/* translators: %s is a placeholder and will be replaced with the name of the relevant file. */
				$this->output_error( sprintf( __( "File %s doesn't exist!", 'gofer-seo' ) . "\n", $filename ) );
			}
		}

		return false;
	}

	/**
	 * Rename a file through WP Filesystem.
	 *
	 * @since 1.0.0
	 *
	 * @param string $filename
	 * @param string $newname
	 * @return bool
	 */
	function rename_file( $filename, $newname ) {
		$wpfs = $this->get_wp_filesystem();
		if ( is_object( $wpfs ) ) {
			$file_exists    = $wpfs->exists( $filename );
			$newfile_exists = $wpfs->exists( $newname );
			if ( $file_exists && ! $newfile_exists ) {
				if ( $wpfs->move( $filename, $newname ) === false ) {
					/* translators: %s is a placeholder and will be replaced with the name of the relevant file. */
					$this->output_error( sprintf( __( 'Failed to rename file %s!', 'gofer-seo' ) . "\n", $filename ) );
				} else {
					return true;
				}
			} else {
				if ( ! $file_exists ) {
					/* translators: %s is a placeholder and will be replaced with the name of the relevant file. */
					$this->output_error( sprintf( __( "File %s doesn't exist!", 'gofer-seo' ) . "\n", $filename ) );
				} elseif ( $newfile_exists ) {
					/* translators: %s is a placeholder and will be replaced with the name of the relevant file. */
					$this->output_error( sprintf( __( 'File %s already exists!', 'gofer-seo' ) . "\n", $newname ) );
				}
			}
		}

		return false;
	}

}
