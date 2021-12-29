<?php
/**
 * Gofer_SEO_PHP_Functions class
 *
 * Alternative PHP functions for improved operations or compatibility with pre-existing functions that had param changes.
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_PHP_Functions
 *
 * Access to these methods is done statically.
 * Adding any additional methods for PHP functions should be reserved only for pre-existing functions.
 * Any non-existing functions in older PHP versions should use `inc/compatibility/php-functions.php`.
 *
 * @since 1.0.0
 */
class Gofer_SEO_PHP_Functions {

	// TODO Create array_replace()
	/**
	 * Convert a string to lower case
	 * Compatible with mb_strtolower(), an UTF-8 friendly replacement for strtolower()
	 *
	 * @since 1.0.0
	 *
	 * @param string $str
	 * @return string
	 */
	public static function strtolower( $str ) {
		return Gofer_SEO_PHP_Functions::convert_case( $str, 'lower' );
	}

	/**
	 * Convert a string to upper case
	 * Compatible with mb_strtoupper(), an UTF-8 friendly replacement for strtoupper()
	 *
	 * @since 1.0.0
	 *
	 * @param string $str
	 * @return string
	 */
	public static function strtoupper( $str ) {
		return Gofer_SEO_PHP_Functions::convert_case( $str, 'upper' );
	}

	/**
	 * Convert a string to title case
	 * Compatible with mb_convert_case(), an UTF-8 friendly replacement for ucwords()
	 *
	 * @since 1.0.0.
	 *
	 * @param string $str
	 * @return string
	 */
	public static function ucwords( $str ) {
		return Gofer_SEO_PHP_Functions::convert_case( $str, 'title' );
	}

	/**
	 * Case conversion; handle non UTF-8 encodings and fallback **
	 *
	 * @since 1.0.0
	 *
	 * @param string $str
	 * @param string $mode
	 * @return string
	 */
	private static function convert_case( $str, $mode = 'upper' ) {
		static $charset = null;
		if ( null === $charset ) {
			$charset = get_bloginfo( 'charset' );
		}
		$str = (string) $str;
		if ( 'title' === $mode ) {
			if ( function_exists( 'mb_convert_case' ) ) {
				return mb_convert_case( $str, MB_CASE_TITLE, $charset );
			} else {
				return ucwords( $str );
			}
		}

		if ( 'UTF-8' === $charset ) {
			include_once GOFER_SEO_DIR . 'public/utf8-tables.php';
			if ( in_array( $mode, array( 'upper', 'lower' ), true ) ) {
				return strtr( $str, gofer_seo_get_utf8_tables( $mode ) );
			}
		}

		if ( 'upper' === $mode ) {
			if ( function_exists( 'mb_strtoupper' ) ) {
				return mb_strtoupper( $str, $charset );
			} else {
				return strtoupper( $str );
			}
		}

		if ( 'lower' === $mode ) {
			if ( function_exists( 'mb_strtolower' ) ) {
				return mb_strtolower( $str, $charset );
			} else {
				return strtolower( $str );
			}
		}

		return $str;
	}

	/**
	 * Wrapper for strlen() - uses mb_strlen() if possible.
	 *
	 * @since 1.0.0.
	 *
	 * @param $string
	 * @return int
	 */
	public static function strlen( $string ) {
		if ( function_exists( 'mb_strlen' ) ) {
			return mb_strlen( $string, 'UTF-8' );
		}

		return strlen( $string );
	}

	/**
	 * Wrapper for substr() - uses mb_substr() if possible.
	 *
	 * @since 1.0.0
	 *
	 * @param     $string
	 * @param int $start
	 * @param int $length
	 * @return mixed
	 */
	public static function substr( $string, $start = 0, $length = 2147483647 ) {
		$args = func_get_args();
		if ( function_exists( 'mb_substr' ) ) {
			return call_user_func_array( 'mb_substr', $args );
		}

		return call_user_func_array( 'substr', $args );
	}

	/**
	 * Wrapper for strpos() - uses mb_strpos() if possible.
	 *
	 * @since 1.0.0
	 *
	 * @param        $haystack
	 * @param string $needle
	 * @param int    $offset
	 * @return bool|int
	 */
	public static function strpos( $haystack, $needle, $offset = 0 ) {
		if ( function_exists( 'mb_strpos' ) ) {
			return mb_strpos( $haystack, $needle, $offset, 'UTF-8' );
		}

		return strpos( $haystack, $needle, $offset );
	}

	/**
	 * Wrapper for strrpos() - uses mb_strrpos() if possible.
	 *
	 * @since 1.0.0
	 *
	 * @param        $haystack
	 * @param string $needle
	 * @param int    $offset
	 * @return bool|int
	 */
	public static function strrpos( $haystack, $needle, $offset = 0 ) {
		if ( function_exists( 'mb_strrpos' ) ) {
			return mb_strrpos( $haystack, $needle, $offset, 'UTF-8' );
		}

		return strrpos( $haystack, $needle, $offset );
	}

	/**
	 * Filters elements of an array using a callback function.
	 *
	 * PHP 5.6.0 Added optional flag parameter and constants `ARRAY_FILTER_USE_KEY` and `ARRAY_FILTER_USE_BOTH`.
	 *
	 * @since 1.0.0
	 *
	 * @link https://www.php.net/array_filter
	 *
	 * @param array         $values     The array to iterate over.
	 * @param callback|null $cb_function The callback function to use. If no callback is supplied, all entries of array equal to FALSE.
	 * @param int           $flag        Flag determining what arguments are sent to callback.
	 *                                       DO NOT USE the PHP Constant, use an int value.
	 *                                       2 = ARRAY_FILTER_USE_KEY - pass key as the only argument to callback instead of the value
	 *                                       1 = ARRAY_FILTER_USE_BOTH - pass both value and key as arguments to callback instead of the value
	 * @return array The filtered array.
	 */
	public static function array_filter( $values, $cb_function = null, $flag = 0 ) {
		if ( version_compare( '5.6.0', PHP_VERSION, '<=' ) ) {
			// phpcs:disable PHPCompatibility.FunctionUse.NewFunctionParameters.array_filter_flagFound
			return array_filter( $values, $cb_function, $flag );
			// phpcs:enable
		} elseif ( empty( $values ) ) {
			return array();
		}

		$return_values = array();
		switch ( $flag ) {
			case 1:
				// ARRAY_FILTER_USE_BOTH = 1
				foreach ( $values as $key => $value ) {
					if ( function_exists( $cb_function ) ) {
						$keep = (bool) $cb_function( $key, $value );
					} else {
						$keep = (bool) $value;
					}

					if ( $keep ) {
						$return_values[ $key ] = $value;
					}
				}
				break;
			case 2:
				// ARRAY_FILTER_USE_KEY = 2
				$values = array_merge( array_keys( $values ), array_keys( $values ) );
				// Fallthrough.
			case 0:
			default:
				$return_values = array_filter( $values, $cb_function );
		}

		return $return_values;
	}

}
