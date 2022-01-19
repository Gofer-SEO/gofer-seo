<?php
/**
 * String Formatting.
 *
 * Additional Sanitize & Esc functions
 *
 * @package Gofer SEO
 */

/**
 * Escape/Sanitize Value with an array of Callbacks.
 *
 * An alias to `gofer_seo_sanitize_callbacks()`.
 * Preserved for additional operations to be added.
 *
 * @since 1.0.0
 *
 * @uses gofer_seo_sanitize_callbacks() Currently used as an alias.
 *
 * @param mixed   $value     Value to be sanitized.
 * @param array[] $callbacks Array of callbacks to be executed in order.
 * @return mixed The sanitized value.
 */
function gofer_seo_esc_callbacks( $value, $callbacks ) {
	// TODO Change to similar concept.
	return gofer_seo_sanitize_callbacks( $value, $callbacks );
}

/**
 * Escape/Sanitize Value with a Callback.
 *
 * An alias to `gofer_seo_sanitize_callback()`.
 * Preserved for additional operations to be added.
 *
 * @since 1.0.0
 *
 * @uses gofer_seo_sanitize_callback() Currently used as an alias.
 *
 * @param mixed        $value    Value to be escaped.
 * @param string|array $callback Callback function to use.
 * @param array        $args     Additional `$args` to pass to function.
 * @return mixed The escaped value.
 */
function gofer_seo_esc_callback( $value, $callback, $args = array() ) {
	return gofer_seo_sanitize_callback( $value, $callback, $args );
}

/**
 * Sanitize Value with an array of Callbacks.
 *
 * @since 1.0.0
 *
 * @param mixed   $value     Value to be sanitized.
 * @param array[] $callbacks Array of callbacks to be executed in order.
 * @return mixed The sanitized value.
 */
function gofer_seo_sanitize_callbacks( $value, $callbacks ) {
	foreach ( $callbacks as $callback ) {
		if (
				is_array( $callbacks ) &&
				isset( $callback[0] )
		) {
			if ( isset( $callback[1] ) ) {
				$value = gofer_seo_sanitize_callback( $value, $callback[0], $callback[1] );
			} else {
				$value = gofer_seo_sanitize_callback( $value, $callback[0] );
			}
		}
	}

	return $value;
}

/**
 * Sanitize Value with a Callback.
 *
 * @since 1.0.0
 *
 * @param mixed        $value    Value to be sanitized.
 * @param string|array $callback Callback function to use.
 * @param array        $args     Additional `$args` to pass to function.
 * @return mixed The sanitized value.
 */
function gofer_seo_sanitize_callback( $value, $callback, $args = array() ) {
	if ( is_string( $callback ) ) {
		switch ( $callback ) {
			// TODO Add functions, if any, where value being sanitized isn't the first param.
			default:
				array_unshift( $args, $value );
		}
		if ( function_exists( $callback ) ) {
			$value = call_user_func_array( $callback, $args );
		}
	} elseif ( is_array( $callback ) ) {
		if (
				2 === count( $callback ) &&
				isset( $callback[0] ) &&
				isset( $callback[1] ) &&
				method_exists( $callback[0], $callback[1] )
		) {
			array_unshift( $args, $value );
			$value = call_user_func_array( array( $callback[0], $callback[1] ), $args );
		}
	} elseif ( is_callable( $callback ) ) {
		array_unshift( $args, $value );
		$value = call_user_func_array( $callback, $args );
	}

	return $value;
}

/**
 * Sanitize a string slug.
 *
 * Upper & lowercase characters are allowed
 *
 * @since 1.0.0
 *
 * @param string $slug The string slug.
 * @param array $args {
 *     Used to change operations.
 *
 *     @type bool $strtolower Whether to use the PHP strtolower() function. Default: true.
 * }
 * @return string
 */
function gofer_seo_sanitize_slug( $slug, $args = array() ) {
	static $sanitized_slugs;
	if ( is_null( $sanitized_slugs ) ) {
		$sanitized_slugs = array();
	}
	if ( isset( $sanitized_slugs[ $slug ] ) ) {
		return $sanitized_slugs[ $slug ];
	}

	$default_args = array(
		'strtolower' => true,
	);
	$args = wp_parse_args( $args, $default_args );

	$sanitized_slug = trim( $slug );
	if ( $args['strtolower'] ) {
		$sanitized_slug = strtolower( $sanitized_slug );
	}
	$sanitized_slug = preg_replace( '/([^A-Za-z0-9_])+/', '_', $sanitized_slug );

	/**
	 * Sanitized slug string.
	 *
	 * @since 1.0.0
	 *
	 * @param string $sanitized_slug Sanitized slug.
	 * @param string $slug           The slug prior to sanitization.
	 * @param array  $args           Used to change operations. See: gofer_seo_sanitize_slug( $args )
	 */
	$sanitized_slug = apply_filters( 'gofer_seo_sanitize_slug', $sanitized_slug, $slug, $args );

	$sanitized_slugs[ $slug ] = $sanitized_slug;

	return $sanitized_slug;
}

/**
 * Esc Attributes.
 *
 * Used to esc/sanitize multiple attribute keys & values.
 *
 * TODO (bulky code) Add input_type and a list of accepted attributes for the input_type.
 *
 * @since 1.0.0
 *
 * @param array $attrs   An array of attributes to sanitize.
 * @param string $format The type to return, either as a whole string or keep the array type.
 *                       Accepts 'string', and 'array'.
 * @return string|array
 */
function gofer_seo_esc_attrs( $attrs, $format = 'string' ) {
	foreach ( $attrs as $input_attr_key => $input_attr_value ) {
		switch ( $input_attr_key ) {
			case 'class':
				$input_attr_key = sanitize_key( $input_attr_key );
				$attrs[ $input_attr_key ] = '';

				$classes = explode( ' ', $input_attr_value );
				if ( ! empty( $classes ) ) {
					$attrs[ $input_attr_key ] = implode(
						' ',
						array_map(
							function( $class ) {
								return sanitize_html_class( $class );
							},
							$classes
						)
					);
				}

				break;
			case 'href':
			case 'src':
				$attrs[ sanitize_key( $input_attr_key ) ] = esc_url( $input_attr_value );
				break;
			default:
				$attrs[ sanitize_key( $input_attr_key ) ] = esc_attr( $input_attr_value );
		}
	}

	if ( 'array' === $format ) {
		return $attrs;
	}

	$attrs = implode(
		' ',
		array_map(
			function( $key, $value ) {
				return $key . '="' . $value . '"';
			},
			array_keys( $attrs ),
			$attrs
		)
	);

	return $attrs;
}

/**
 * Sanitize Domain
 *
 * @since 1.0.0
 *
 * @param $domain
 * @return mixed|string
 */
function gofer_seo_sanitize_domain( $domain ) {
	$domain = trim( $domain );
	$domain = Gofer_SEO_PHP_Functions::strtolower( $domain );
	if ( 0 === Gofer_SEO_PHP_Functions::strpos( $domain, 'http://' ) ) {
		$domain = Gofer_SEO_PHP_Functions::substr( $domain, 7 );
	} elseif ( 0 === Gofer_SEO_PHP_Functions::strpos( $domain, 'https://' ) ) {
		$domain = Gofer_SEO_PHP_Functions::substr( $domain, 8 );
	}
	$domain = untrailingslashit( $domain );

	return $domain;
}

/**
 * Sanitize Path.
 *
 * @deprecated
 * @since 1.0.0
 *
 * @param string $path
 * @return string
 */
function gofer_seo_sanitize_path( $path ) {
	// if path does not have a trailing wild card (*) or does not refer to a file (with extension), add trailing slash.
	if ( '*' !== substr( $path, -1 ) && false === strpos( $path, '.' ) ) {
		$path = trailingslashit( $path );
	}

	// if path does not have a leading slash, add it.
	if ( '/' !== substr( $path, 0, 1 ) ) {
		$path = '/' . $path;
	}

	// convert everything to lower case.
	$path = strtolower( $path );

	return $path;
}

/**
 * Get Sanitized File.
 *
 * Returns sanitized imported file.
 *
 * @deprecated
 * @since 1.0.0
 *
 * @param string $filename Path to where the uploaded file is located.
 * @return array Sanitized file as array.
 * @throws Exception
 */
function gofer_seo_get_sanitized_file( $filename ) {
	$file = file( $filename );
	for ( $i = count( $file ) - 1; $i >= 0; -- $i ) {
		// Remove insecured lines.
		if ( preg_match( '/\<(\?php|script)/', $file[ $i ] ) ) {
			throw new Exception(
				sprintf(
					/* translators: %1$s: HTML element. %2$s: HTML element. %3$s: The filename to check. */
					__( '%1$sSecurity warning:%2$s Your file looks compromised. Please check `%3$s` for any script-injection.', 'gofer-seo' ),
					'<b>',
					'</b>',
					$filename
				)
			);
		}
		// Apply security filters.
		$file[ $i ] = wp_strip_all_tags( trim( $file[ $i ] ) );
		// Remove empty lines.
		if ( empty( $file[ $i ] ) ) {
			unset( $file[ $i ] );
		}
	}

	return $file;
}

/**
 * Is URL Valid
 *
 * Check whether a url is valid.
 *
 * @since 1.0.0
 *
 * @param string $url URL to check.
 * @return bool
 */
function gofer_seo_is_url_valid( $url ) {
	return filter_var( filter_var( $url, FILTER_SANITIZE_URL ), FILTER_VALIDATE_URL ) !== false;
}

/**
 * Filter Wrap for URL Scheme.
 *
 * Used whether to force https|http based on the filter return value.
 *
 * @since 1.0.0
 *
 * @param string $url The URL to possibly change.
 * @return string Modified URL.
 */
function gofer_seo_filter_url_scheme( $url ) {

	/**
	 * URL Scheme.
	 *
	 * @since 1.0.0
	 *
	 * @param bool|string $scheme Either 'http' or 'https' to change URL. Defaults to false for no change.
	 * @param string      $url    The URL string.
	 * @return string Either 'http' for 'http://', 'https' for 'https://', or (default) false for no change.
	 */
	$scheme = apply_filters( 'gofer_seo_url_scheme', false, $url );

	if ( 'http' === $scheme ) {
		$url = preg_replace( '/^https:/i', 'http:', $url );
	} elseif ( 'https' === $scheme ) {
		$url = preg_replace( '/^http:/i', 'https:', $url );
	}

	return $url;
}

/**
 * Sitemap Sanitize URL.
 *
 * Cleans the URL so that its acceptable in the sitemap.
 *
 * @since 1.0.0
 *
 * @param string $url The image url.
 * @return string
 */
function gofer_seo_sanitize_sitemap_url( $url ) {
	// remove the query string.
	$url = strtok( $url, '?' );
	// make the url XML-safe.
	$url = htmlspecialchars( $url, ENT_COMPAT, 'UTF-8' );
	// Make the url absolute, if its relative.
	$url = Gofer_SEO_Methods::absolutize_url( $url );
	return apply_filters( 'gofer_seo_sanitize_sitemap_url', $url );
}

/**
 * Returns SEO ready string with encoded HTML entities.
 *
 * @since 1.0.0
 *
 * @param string $value Value to encode.
 * @return string
 */
function gofer_seo_html_entity_encode( $value ) {
	return preg_replace(
		array(
			'/\"|\“|\”|\„/', // Double quotes.
			'/\'|\’|\‘/',   // Apostrophes.
		),
		array(
			'&quot;', // Double quotes.
			'&#039;', // Apostrophes.
		),
		esc_html( $value )
	);
}

/**
 * Returns string with decoded html entities.
 *
 * @since 1.0.0
 *
 * @param string $value Value to decode.
 * @return string
 */
function gofer_seo_html_entity_decode( $value ) {
	// Special conversions.
	$value = preg_replace(
		array(
			// Double quotes.
			'/\“|\”|&#[xX]00022;|&#34;|&[lLrRbB](dquo|DQUO)(?:[rR])?;|&#[xX]0201[dDeE];'
			. '|&[OoCc](pen|lose)[Cc]urly[Dd]ouble[Qq]uote;|&#822[012];|&#[xX]27;/',
			// Apostrophes.
			'/&#039;|&#8217;|&apos;/',
		),
		array(
			// Double quotes.
			'"',
			// Apostrophes.
			'\'',
		),
		$value
	);
	return html_entity_decode( $value, ENT_COMPAT, 'UTF-8' );
}

/**
 * Decode URL.
 *
 * @since 1.0.0
 *
 * @param string $url
 * @return string
 */
function gofer_seo_decode_url( $url ) {
	return urldecode( $url );
}

/**
 * Sanitize URL's Scheme.
 *
 * Check whether a url is relative (does not contain a . before the first /) or absolute and makes it a valid url.
 *
 * @since 1.0.0
 *
 * @param string $url URL to check.
 * @return string
 */
function gofer_seo_sanitize_url_scheme( $url ) {
	$scheme = wp_parse_url( home_url(), PHP_URL_SCHEME );
	if ( 0 !== strpos( $url, 'http' ) ) {
		if ( 0 === strpos( $url, '//' ) ) {
			// For //<host>/resource type urls.
			$url = $scheme . ':' . $url;
		} elseif ( strpos( $url, '.' ) !== false && strpos( $url, '/' ) !== false && strpos( $url, '.' ) < strpos( $url, '/' ) ) {
			// If the . comes before the first / then this is absolute.
			$url = $scheme . '://' . $url;
		} else {
			// For /resource type urls.
			$url = home_url( $url );
		}
	} elseif ( strpos( $url, 'http://' ) === false ) {
		if ( 0 === strpos( $url, 'http:/' ) ) {
			$url = $scheme . '://' . str_replace( 'http:/', '', $url );
		} elseif ( 0 === strpos( $url, 'http:' ) ) {
			$url = $scheme . '://' . str_replace( 'http:', '', $url );
		}
	}

	return $url;
}

/**
 * Escape Head Elements.
 *
 * @sence 1.0.0
 *
 * @param string $content
 * @return string
 */
function gofer_seo_esc_head( $string ) {
	$allowed_html = array(
		'link'   => array(
			'href' => true,
			'rel'  => true,
			'type' => true,
		),
		'meta'   => array(
			'name'     => true,
			'property' => true,
			'content'  => true,
		),
		'script' => array(
			'async'       => true,
			'crossorigin' => true,
			'defer'       => true,
			'src'         => true,
			'text'        => true,
			'type'        => true,
		),
	);

	$string = wp_kses_no_null( $string, array( 'slash_zero' => 'keep' ) );
	$string = wp_kses_normalize_entities( $string );

	return wp_kses_split( $string, $allowed_html, wp_allowed_protocols() );
}

/**
 * Escape JSON.
 *
 * @since 1.0.0
 *
 * @param string $json
 * @param bool   $html
 * @return string
 */
function gofer_seo_esc_json( $json, $html = false ) {
	return _wp_specialchars(
		$json,
		$html ? ENT_NOQUOTES : ENT_QUOTES, // Escape quotes in attribute nodes only.
		'UTF-8',                           // json_encode() outputs UTF-8 (really just ASCII), not the blog's charset.
		true                               // Double escape entities: `&amp;` -> `&amp;amp;`.
	);
}

/**
 * Convert Bytestring.
 *
 * @since 1.0.0
 *
 * @param string $byte_string
 * @return int
 */
function gofer_seo_convert_bytestring( $byte_string ) {
	$num = 0;
	preg_match( '/^\s*([0-9.]+)\s*([KMGTPE])B?\s*$/i', $byte_string, $matches );
	if ( ! empty( $matches ) ) {
		$num = (float) $matches[1];
		switch ( strtoupper( $matches[2] ) ) {
			case 'E':
				$num *= 1024;
			// fall through.
			case 'P':
				$num *= 1024;
			// fall through.
			case 'T':
				$num *= 1024;
			// fall through.
			case 'G':
				$num *= 1024;
			// fall through.
			case 'M':
				$num *= 1024;
			// fall through.
			case 'K':
				$num *= 1024;
		}
	}

	return intval( $num );
}

/**
 * Trim String.
 *
 * Originally used to trim description,
 * and replace quotes & new-lines.
 *
 * @since 1.0.0
 *
 * @param string $str
 * @return string
 */
function gofer_seo_trim_str( $str ) {
	$str = trim( wp_strip_all_tags( $str ) );
	$str = str_replace( '"', '&quot;', $str );
	$str = str_replace( "\r\n", ' ', $str );
	$str = str_replace( "\n", ' ', $str );

	return $str;
}
