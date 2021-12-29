<?php
/**
 * Compatibility functions for PHP.
 *
 * @package Gofer SEO
 */

// Used for backwards-compatibility.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound

if ( ! function_exists( 'array_column' ) ) {
	/**
	 * Array Column (PHP 5 >= 5.5.0, PHP 7).
	 *
	 * Return the values from a single column in the input array.
	 *
	 * Pre-5.5 replacement/drop-in.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $input      A multi-dimensional array or an array of objects from which to pull a column of values from.
	 * @param string $column_key The column of values to return.
	 * @return array Returns an array of values representing a single column from the input array.
	 */
	function array_column( $input, $column_key ) {
		return array_combine( array_keys( $input ), wp_list_pluck( $input, $column_key ) );
	}
}

if ( ! function_exists( 'parse_ini_string' ) ) {
	/**
	 * Parse INI String
	 *
	 * Parse_ini_string() doesn't exist pre PHP 5.3.
	 *
	 * @deprecated No longer needed.
	 *
	 * @since 1.0.0
	 *
	 * @link https://www.php.net/manual/en/function.parse-ini-string.php
	 * @link https://www.php.net/manual/en/function.parse-ini-string.php#113582
	 *
	 * @param string $string
	 * @param bool $process_sections
	 * @return array|bool
	 */
	function parse_ini_string( $string, $process_sections ) {
		if ( ! class_exists( 'parse_ini_filter' ) ) {

			/**
			 * Class parse_ini_filter
			 *
			 * Define our filter class.
			 *
			 * @since 1.0.0
			 */
			class parse_ini_filter extends php_user_filter {

				/**
				 * Buffer
				 *
				 * @since 1.0.0
				 *
				 * @var string $buf
				 */
				static $buf = '';

				/**
				 * The actual filter for parsing.
				 *
				 *
				 * @link https://php.net/manual/en/php-user-filter.filter.php
				 * @param resource $in       Is a resource pointing to a bucket brigade which contains one or more bucket
				 *                           objects containing data to be filtered.
				 * @param resource $out      Is a resource pointing to a second bucket brigade into which your modified
				 *                           buckets should be placed.
				 * @param int      $consumed Which must always be declared by reference, should be incremented by the
				 *                           length of the data which your filter reads in and alters. In most cases this
				 *                           means you will increment consumed by `$bucket->datalen` for each $bucket.
				 * @param bool     $closing  If the stream is in the process of closing (and therefore this is the last
				 *                           pass through the filterchain), the closing parameter will be set to TRUE
				 * @return int
				 */
				function filter( $in, $out, &$consumed, $closing ) {
					// Remove when dropping PHP 5.3 support.
					// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_read_fopen
					$bucket = stream_bucket_new( fopen( 'php://memory', 'wb' ), self::$buf );
					// phpcs:enable
					stream_bucket_append( $out, $bucket );

					return PSFS_PASS_ON;
				}
			}

			// Register our filter with PHP.
			if ( ! stream_filter_register( 'parse_ini', 'parse_ini_filter' ) ) {
				return false;
			}
		}
		parse_ini_filter::$buf = $string;

		return parse_ini_file( 'php://filter/read=parse_ini/resource=php://memory', $process_sections );
	}
}

if ( ! function_exists( 'fnmatch' ) ) {

	/**
	 * Filename Match
	 *
	 * Support for fnmatch() doesn't exist on Windows pre PHP 5.3.
	 *
	 * @since 1.0.0
	 *
	 * @param $pattern
	 * @param $string
	 * @return int
	 */
	function fnmatch( $pattern, $string ) {
		return preg_match(
			'#^' . strtr(
				preg_quote( $pattern, '#' ),
				array(
					'\*' => '.*',
					'\?' => '.',
				)
			) . '$#i',
			$string
		);
	}
}

if ( ! function_exists( 'array_key_last' ) ) {

	/**
	 * Array Key Last (PHP 7 >= 7.3.0, PHP 8).
	 *
	 * @since 1.0.0
	 *
	 * @param array $array
	 * @return int|string|null
	 */
	function array_key_last( $array ) {
		if ( ! is_array( $array ) || empty( $array ) ) {
			return null;
		}

		return array_keys( $array )[ count( $array ) - 1 ];
	}
}

// phpcs:enable


/* **_________________*************************************************************************************************/
/* _/ Extra Functions \_______________________________________________________________________________________________*/


/**
 * Array Merge Recursive Unique.
 *
 * Acts the same as `array_merge_recursive()`, except duplicate values of
 * indexed arrays are reduced to a single value.
 *
 * @since 1.0.0
 *
 * @param array|mixed $array_1
 * @param array|mixed $array_2
 * @return array
 */
function gofer_seo_array_merge_recursive_unique( $array_1, $array_2 ) {
	$array_1 = array_merge( $array_1, $array_2 );
	foreach ( $array_1 as $key => $value ) {
		if ( is_array( $value ) && is_array( $array_2 ) ) {
			$array_1[ $key ] = array_merge_recursive_unique( $array_1, $array_2 );
		}
	}

	$array_1 = array_unique( $array_1 );

	return $array_1;
}
