<?php
/**
 * Gofer Templating
 *
 * Main functions for retrieving & outputting templates.
 *
 * @package Gofer SEO
 */

/**
 * Get Template HTML/Content.
 *
 * Similar to `gofer_seo_do_template()`, but returns a value.
 *
 * @since 1.0.0
 *
 * @see gofer_seo_do_template().
 *
 * @param string $template_path Template (path/)file-name
 * @param array  $args          Params to pass to the template.
 * @param string $theme_sub_dir Theme sub-directory to templates folder.
 * @param string $dir           Directory.
 * @return false|string
 */
function gofer_seo_get_template_html( $template_path, $args = array(), $theme_sub_dir = '', $dir = '' ) {
	ob_start();
	gofer_seo_do_template( $template_path, $args, $theme_sub_dir, $dir );
	return ob_get_clean();
}

/**
 * Do Template.
 *
 * Locates & displays templates, and can have params passed with `$args`.
 *
 * @since 1.0.0
 *
 * @param string $template_path Template (path/)file-name
 * @param array  $args          Params to pass to the template.
 * @param string $theme_sub_dir Theme sub-directory to templates folder.
 * @param string $dir           Directory.
 * @return void
 */
function gofer_seo_do_template( $template_path, $args = array(), $theme_sub_dir = '', $dir = '' ) {
	if ( ! is_array( $args ) ) {
		$args = array( $args );
	}

	/**
	 * Template Arguments.
	 *
	 * Can be used to add a hook prior to using locate_template filter.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $args          Params to pass to the template.
	 * @param string $template_path Template (path/)file-name
	 * @param string $theme_sub_dir Theme sub-directory to templates folder.
	 * @param string $dir           Directory.
	 */
	$args = apply_filters( 'gofer_seo_template_args', $args, $template_path, $theme_sub_dir, $dir );

	$template_file = gofer_seo_locate_template( $template_path, $theme_sub_dir, $dir );

	if ( ! $template_file ) {
		/* translators: %s template name */
		_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( 'Template %s does not exist.', 'gofer-seo' ), '<code>' . esc_html( $template_file ) . '</code>' ), esc_html( GOFER_SEO_VERSION ) );
		return;
	}
	if ( isset( $args['gofer_seo_template'] ) ) {
		_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( 'gofer_seo_template in $args param is a reserved key.', 'gofer-seo' ), '<code>' . esc_html( $template_file ) . '</code>' ), esc_html( GOFER_SEO_VERSION ) );
		unset( $args['gofer_seo_template'] );
	}

	$gofer_seo_template = array(
		'template_file' => $template_file,
		'template_path' => $template_path,
		'theme_sub_dir' => $theme_sub_dir,
		'dir'           => $dir,
		'args'          => $args,
	);

	unset( $template_file );
	unset( $template_path );
	unset( $theme_sub_dir );
	unset( $dir );
	unset( $args );

	// EXTRACT - Similar to extract() with EXTR_PREFIX_INVALID flag. However, this also checks for
	// any invalid and/or restricted variable names.
	// The foreach variable names are complex to reduce the chance of duplicates.
	foreach ( $gofer_seo_template['args'] as $gofer_seo_template_arg_key => $gofer_seo_template_arg_value ) {
		// Skip if (variable) key is invalid.
		$gofer_seo_template_arg_key = gofer_seo_sanitize_slug( $gofer_seo_template_arg_key );
		if (
				empty( $gofer_seo_template_arg_key ) ||
				in_array(
					$gofer_seo_template_arg_key,
					array(
						'gofer_seo_template', // Above there is already a PHP Warning & unset().
						'gofer_seo_template_arg_key',
						'gofer_seo_template_arg_value',
					),
					true
				)
		) {
			continue;
		}

		if ( ! is_numeric( $gofer_seo_template_arg_key ) ) {
			$$gofer_seo_template_arg_key = $gofer_seo_template_arg_value;
		} else {
			$gofer_seo_template_arg_key  = 'arg_' . $gofer_seo_template_arg_key;
			$$gofer_seo_template_arg_key = $gofer_seo_template_arg_value;
		}
	}
	unset( $gofer_seo_template_arg_key );
	unset( $gofer_seo_template_arg_value );

	/**
	 * Before including Template.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template_path Template (path/)file-name.
	 * @param string $theme_sub_dir Theme sub-directory to templates folder.
	 * @param string $dir           Directory.
	 * @param string $template_file The template's located file.
	 * @param array  $args          Params to pass to the template.
	 */
	do_action( 'gofer_seo_before_template', $gofer_seo_template['template_path'], $gofer_seo_template['theme_sub_dir'], $gofer_seo_template['dir'], $gofer_seo_template['template_file'], $gofer_seo_template['args'] );

	include $gofer_seo_template['template_file'];

	/**
	 * After including Template.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template_path Template (path/)file-name.
	 * @param string $theme_sub_dir Theme sub-directory to templates folder.
	 * @param string $dir           Directory.
	 * @param string $template_file The template's located file.
	 * @param array  $args          Params to pass to the template.
	 */
	do_action( 'gofer_seo_after_template', $gofer_seo_template['template_path'], $gofer_seo_template['theme_sub_dir'], $gofer_seo_template['dir'], $gofer_seo_template['template_file'], $gofer_seo_template['args'] );
}

/**
 * Locate a template and return the file path.
 *
 * Similar concept to WooCommerce with some alterations.
 * Themes takes priority, and then the (plugin) directory.
 *
 * This is the load order:
 * - your-theme/{$theme_sub_dir}/{$template_path}
 * - your-theme/{$template_path}
 * - {$dir}/{$template_path}
 * - gofer-seo/templates/{$template_path}
 *
 * @since 1.0.0
 *
 * @param string $template_path Template (path/)file-name.
 * @param string $theme_sub_dir Theme sub-directory to templates folder.
 * @param string $dir           Directory path. Used for paths other than the theme; like (mu-)plugins, uploads/media, etc..
 * @return string
 */
function gofer_seo_locate_template( $template_path, $theme_sub_dir = '', $dir = '' ) {
	static $s_templates;
	if ( is_null( $s_templates ) ) {
		$s_templates = array();
	}
	$theme_sub_dir = ! empty( $theme_sub_dir ) ? $theme_sub_dir : 'gofer-seo/';
	$dir           = ! empty( $dir ) ? $dir : GOFER_SEO_DIR . 'templates/';

	$cache_group = implode( '-', array_filter( array( 'template', $template_path, $theme_sub_dir, $dir, GOFER_SEO_VERSION ) ) );
	$cache_group = sanitize_key( $cache_group );

	if ( isset( $s_templates[ $cache_group ] ) ) {
		return $s_templates[ $cache_group ];
	}

	$template  = wp_cache_get( 'gofer_seo_locate_template', $cache_group  );
	if ( ! empty( $template ) && is_string( $template ) ) {
		$s_templates[ $cache_group ] = $template;
		return $template;
	}

	/**
	 * Used to override templates.
	 *
	 * TODO Change $dir to an array for multiple other directories.
	 *
	 * @ignore This may be redundant, unless it is changed to register commonly used directories (array); rather than repetitive checks.
	 * @since 1.0.0
	 *
	 * @param string $template_path Template (path/)file-name
	 * @param string $dir           Directory.
	 */
	$filtered_dir = apply_filters( 'gofer_seo_template_dir', $dir, $template_path );

	// Check within theme paths.
	// your-theme/{$theme_sub_dir}/{$template_path}.
	// your-theme/{$template_path}.
	$template = locate_template(
		array(
			trailingslashit( $theme_sub_dir ) . $template_path,
			$template_path,
		)
	);

	// Check within (plugin) dir.
	if ( ! $template || GOFER_SEO_TEMPLATE_DEBUG_MODE ) {
		if ( file_exists( $filtered_dir . $template_path ) && ! GOFER_SEO_TEMPLATE_DEBUG_MODE ) {
			// {$dir}/{$template_path}.
			$template = $filtered_dir . $template_path;
		} elseif ( file_exists( $dir . $template_path ) ) {
			// gofer-seo/templates/{$template_path}.
			$template = $dir . $template_path;
		}
	}

	if ( false !== has_filter( 'gofer_seo_locate_template' ) ) {
		/**
		 * Filter the located template.
		 *
		 * @since 1.0.0
		 *
		 * @param string $template      The located template filepath.
		 * @param string $template_path Template (path/)file-name
		 * @param string $theme_sub_dir Theme sub-directory to templates folder.
		 * @param string $dir           Directory.
		 */
		$filtered_template = apply_filters( 'gofer_seo_locate_template', $template, $template_path, $theme_sub_dir, $dir );
		if ( $template !== $filtered_template && file_exists( $filtered_template ) ) {
			$template = $filtered_template;
		}
	}

	wp_cache_set( 'gofer_seo_locate_template', $template, $cache_group, DAY_IN_SECONDS );
	$s_templates[ $cache_group ] = $template;

	return $template;
}
