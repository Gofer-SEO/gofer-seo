<?php
/**
 * Static WP Functions Compatibility
 *
 * Provides reverse compatibility with older versions of WordPress that are missing newer function
 * additions or changes.
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_WP_Functions.
 *
 * @since 1.0.0
 */
class Gofer_SEO_WP_Functions {

	/**
	 * Converts a number of special characters into their HTML entities.
	 *
	 * Specifically deals with: &, <, >, ", and '.
	 *
	 * $quote_style can be set to ENT_COMPAT to encode " to
	 * &quot;, or ENT_QUOTES to do both. Default is ENT_NOQUOTES where no quotes are encoded.
	 *
	 * @since 1.0.0
	 *
	 * @since WP 1.2.2
	 * @since WP 5.5.0 `$quote_style` also accepts `ENT_XML1`.
	 * @access private
	 *
	 * @param string       $string        The text which is to be encoded.
	 * @param int|string   $quote_style   Optional. Converts double quotes if set to ENT_COMPAT,
	 *                                    both single and double if set to ENT_QUOTES or none if set to ENT_NOQUOTES.
	 *                                    Converts single and double quotes, as well as converting HTML
	 *                                    named entities (that are not also XML named entities) to their
	 *                                    code points if set to ENT_XML1. Also compatible with old values;
	 *                                    converting single quotes if set to 'single',
	 *                                    double if set to 'double' or both if otherwise set.
	 *                                    Default is ENT_NOQUOTES.
	 * @param false|string $charset       Optional. The character encoding of the string. Default false.
	 * @param bool         $double_encode Optional. Whether to encode existing HTML entities. Default false.
	 * @return string The encoded text with HTML entities.
	 */
	public static function _wp_specialchars( $string, $quote_style = ENT_NOQUOTES, $charset = false, $double_encode = false ) {
		global $wp_version;
		if ( version_compare( '5.5.0', $wp_version, '<=' ) ) {
			return _wp_specialchars( $string, $quote_style, $charset, $double_encode );
		}

		$string = (string) $string;

		if ( 0 === strlen( $string ) ) {
			return '';
		}

		// Don't bother if there are no specialchars - saves some processing.
		if ( ! preg_match( '/[&<>"\']/', $string ) ) {
			return $string;
		}

		// Account for the previous behaviour of the function when the $quote_style is not an accepted value.
		if ( empty( $quote_style ) ) {
			$quote_style = ENT_NOQUOTES;
		} elseif ( ENT_XML1 === $quote_style ) {
			$quote_style = ENT_QUOTES | ENT_XML1;
		} elseif ( ! in_array( $quote_style, array( ENT_NOQUOTES, ENT_COMPAT, ENT_QUOTES, 'single', 'double' ), true ) ) {
			$quote_style = ENT_QUOTES;
		}

		// Store the site charset as a static to avoid multiple calls to wp_load_alloptions().
		if ( ! $charset ) {
			static $_charset = null;
			if ( ! isset( $_charset ) ) {
				$alloptions = wp_load_alloptions();
				$_charset   = isset( $alloptions['blog_charset'] ) ? $alloptions['blog_charset'] : '';
			}
			$charset = $_charset;
		}

		if ( in_array( $charset, array( 'utf8', 'utf-8', 'UTF8' ), true ) ) {
			$charset = 'UTF-8';
		}

		$_quote_style = $quote_style;

		if ( 'double' === $quote_style ) {
			$quote_style  = ENT_COMPAT;
			$_quote_style = ENT_COMPAT;
		} elseif ( 'single' === $quote_style ) {
			$quote_style = ENT_NOQUOTES;
		}

		if ( ! $double_encode ) {
			// Guarantee every &entity; is valid, convert &garbage; into &amp;garbage;
			// This is required for PHP < 5.4.0 because ENT_HTML401 flag is unavailable.
			$string = Gofer_SEO_WP_Functions::wp_kses_normalize_entities( $string, ( $quote_style & ENT_XML1 ) ? 'xml' : 'html' );
		}

		$string = htmlspecialchars( $string, $quote_style, $charset, $double_encode );

		// Back-compat.
		if ( 'single' === $_quote_style ) {
			$string = str_replace( "'", '&#039;', $string );
		}

		return $string;
	}

	/**
	 * Converts and fixes HTML entities.
	 *
	 * This function normalizes HTML entities. It will convert `AT&T` to the correct
	 * `AT&amp;T`, `&#00058;` to `&#058;`, `&#XYZZY;` to `&amp;#XYZZY;` and so on.
	 *
	 * When `$context` is set to 'xml', HTML entities are converted to their code points.  For
	 * example, `AT&T&hellip;&#XYZZY;` is converted to `AT&amp;T…&amp;#XYZZY;`.
	 *
	 * @since 1.0.0
	 * @since 5.5.0 Added `$context` parameter.
	 *
	 * @param string $string  Content to normalize entities.
	 * @param string $context Context for normalization. Can be either 'html' or 'xml'.
	 *                        Default 'html'.
	 * @return string Content with normalized entities.
	 */
	public static function wp_kses_normalize_entities( $string, $context = 'html' ) {
		global $wp_version;
		if ( version_compare( '5.5.0', $wp_version, '<=' ) ) {
			return wp_kses_normalize_entities( $string, $context );
		}

		// Disarm all entities by converting & to &amp;
		$string = str_replace( '&', '&amp;', $string );

		// Change back the allowed entities in our list of allowed entities.
		if ( 'xml' === $context ) {
			$string = preg_replace_callback( '/&amp;([A-Za-z]{2,8}[0-9]{0,2});/', 'wp_kses_xml_named_entities', $string );
		} else {
			$string = preg_replace_callback( '/&amp;([A-Za-z]{2,8}[0-9]{0,2});/', 'wp_kses_named_entities', $string );
		}
		$string = preg_replace_callback( '/&amp;#(0*[0-9]{1,7});/', 'wp_kses_normalize_entities2', $string );
		$string = preg_replace_callback( '/&amp;#[Xx](0*[0-9A-Fa-f]{1,6});/', 'wp_kses_normalize_entities3', $string );

		return $string;
	}

}
