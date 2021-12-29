<?php
/**
 * WP Functions Compatibility
 *
 * Provides reverse compatibility with older versions of WordPress that are missing newer function
 * additions or changes.
 *
 * @package Gofer SEO
 */


if ( false && ! function_exists( 'get_home_path' ) ) {

	/**
	 * Get the absolute filesystem path to the root of the WordPress installation.
	 *
	 * If we're in wp-admin, use the WordPress function, otherwise we user our own version here.
	 * This only applies to static sitemaps.
	 *
	 * @since 1.0.0
	 *
	 * @see get_home_path()
	 *
	 * @return string Full filesystem path to the root of the WordPress installation
	 */
	function get_home_path() {
		$home_path = '';
		$home      = set_url_scheme( get_option( 'home' ), 'http' );
		$site_url   = set_url_scheme( get_option( 'siteurl' ), 'http' );
		if ( ! empty( $home ) && 0 !== strcasecmp( $home, $site_url ) ) {
			if ( isset( $_SERVER['SCRIPT_FILENAME'] ) ) {
				$script_filename = sanitize_file_name( wp_unslash( $_SERVER['SCRIPT_FILENAME'] ) );

				/* $siteurl - $home */
				$wp_path_rel_to_home = str_ireplace( $home, '', $site_url );
				$pos                 = strripos( str_replace( '\\', '/', $script_filename ), trailingslashit( $wp_path_rel_to_home ) );
				$home_path           = substr( $script_filename, 0, $pos );
				$home_path           = trailingslashit( $home_path );
			}
		} else {
			$home_path = ABSPATH;
		}

		return str_replace( '\\', '/', $home_path );
	}
}

if ( ! function_exists( 'esc_xml' ) ) {

	/**
	 * Escaping for XML blocks.
	 *
	 * @since 1.0.0
	 *
	 * @since WP 5.5.0
	 *
	 * @param string $text Text to escape.
	 * @return string Escaped text.
	 */
	function esc_xml( $text ) {
		$safe_text = wp_check_invalid_utf8( $text );

		$cdata_regex = '\<\!\[CDATA\[.*?\]\]\>';
		$regex       = <<<EOF
/
	(?=.*?{$cdata_regex})                 # lookahead that will match anything followed by a CDATA Section
	(?<non_cdata_followed_by_cdata>(.*?)) # the "anything" matched by the lookahead
	(?<cdata>({$cdata_regex}))            # the CDATA Section matched by the lookahead

|	                                      # alternative

	(?<non_cdata>(.*))                    # non-CDATA Section
/sx
EOF;

		$safe_text = (string) preg_replace_callback(
			$regex,
			static function( $matches ) {
				if ( ! $matches[0] ) {
					return '';
				}

				if ( ! empty( $matches['non_cdata'] ) ) {
					// escape HTML entities in the non-CDATA Section.
					return Gofer_SEO_WP_Functions::_wp_specialchars( $matches['non_cdata'], ENT_XML1 );
				}

				// Return the CDATA Section unchanged, escape HTML entities in the rest.
				return Gofer_SEO_WP_Functions::_wp_specialchars( $matches['non_cdata_followed_by_cdata'], ENT_XML1 ) . $matches['cdata'];
			},
			$safe_text
		);

		/**
		 * Filters a string cleaned and escaped for output in XML.
		 *
		 * Text passed to esc_xml() is stripped of invalid or special characters
		 * before output. HTML named character references are converted to their
		 * equivalent code points.
		 *
		 * @since 1.0.0
		 *
		 * @since WP 5.5.0
		 *
		 * @param string $safe_text The text after it has been escaped.
		 * @param string $text      The text prior to being escaped.
		 */
		return apply_filters( 'esc_xml', $safe_text, $text );
	}
}

if ( ! function_exists( 'wp_kses_xml_named_entities' ) ) {

	/**
	 * Callback for `wp_kses_normalize_entities()` regular expression.
	 *
	 * This function only accepts valid named entity references, which are finite,
	 * case-sensitive, and highly scrutinized by XML validators.  HTML named entity
	 * references are converted to their code points.
	 *
	 * @since 1.0.0
	 *
	 * @since WP 5.5.0
	 *
	 * @global array $allowedentitynames
	 * @global array $allowedxmlnamedentities
	 *
	 * @param array $matches preg_replace_callback() matches array.
	 * @return string Correctly encoded entity.
	 */
	function wp_kses_xml_named_entities( $matches ) {
		global $allowedentitynames, $allowedxmlnamedentities;

		if ( empty( $matches[1] ) ) {
			return '';
		}

		$i = $matches[1];

		if ( in_array( $i, $allowedxmlnamedentities, true ) ) {
			return "&$i;";
		} elseif ( in_array( $i, $allowedentitynames, true ) ) {
			return html_entity_decode( "&$i;", ENT_HTML5 );
		}

		return "&amp;$i;";
	}
}
