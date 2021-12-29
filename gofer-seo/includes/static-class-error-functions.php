<?php
/**
 * Gofer SEO - Static Error Functions
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Error_Functions.
 *
 * @since 1.0.0
 */
class Gofer_SEO_Error_Functions {

	/**
	 * Get Call Stack.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function get_call_stack() {
		$e = new Exception();

		$call_stack = sprintf(
			'[%1$s] %2$s %3$s',
			wp_date( 'Y-m-d H:i:s' ),
			$e->getTraceAsString(),
			PHP_EOL
		);

		return $call_stack;
	}

	/**
	 * Log Error.
	 *
	 * @since 1.0.0
	 *
	 * @param string $message
	 */
	public static function log_error( $message ) {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		if (
				false === GOFER_SEO_DO_LOG &&
				false === $gofer_seo_options->options['modules']['debugger']['enable_error_logs']
		) {
			return;
		}

		$output = sprintf(
			'[%1$s] %2$s %3$s',
			wp_date( 'Y-m-d H:i:s' ),
			$message,
			PHP_EOL
		);
		$log_file = WP_CONTENT_DIR . '/debug-gofer-seo.log';

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( $output, 3, $log_file );
	}

}
