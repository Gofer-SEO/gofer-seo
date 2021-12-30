<?php
/**
 * Google Analytics
 *
 * @package Gofer SEO
 */

// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript

/**
 * Class Gofer_SEO_Google_Analytics
 *
 * @since 1.0.0
 */
class Gofer_SEO_Google_Analytics {

	/**
	 * Constructor
	 *
	 * Default module constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->output_google_analytics();
	}

	/**
	 * Google Analytics
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function output_google_analytics() {
		echo gofer_seo_esc_head( $this->google_analytics() );
		do_action( 'gofer_seo_after_google_analytics' );
	}

	/**
	 * Google Analytics
	 *
	 * @since 1.0.0
	 *
	 * @link https://github.com/googleanalytics/autotrack
	 *
	 * @global WP_User $current_user Current logged in WP user.
	 *
	 * @return string
	 */
	public function google_analytics() {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		if ( empty( $gofer_seo_options->options['modules']['general']['google_analytics']['ua_id'] ) ) {
			return '';
		}

		if (
			$gofer_seo_options->options['modules']['general']['google_analytics']['enable_advanced_settings'] &&
			is_user_logged_in()
		) {
			global $current_user;
			$user = $current_user;
			if ( empty( $current_user ) ) {
				$user = wp_get_current_user();
			}

			$exclude_roles = array_keys( array_filter( $gofer_seo_options->options['modules']['general']['google_analytics']['exclude_user_roles'] ) );
			if ( ! empty( $exclude_roles ) ) {
				$matched_roles = array_intersect( $user->roles, $exclude_roles );
				if ( ! empty( $matched_roles ) ) {
					return '';
				}
			}
		}

		$enable_autotrack = false;
		if (
				$gofer_seo_options->options['modules']['general']['google_analytics']['enable_advanced_settings'] &&
				$gofer_seo_options->options['modules']['general']['google_analytics']['enable_track_outbound_links']
		) {
			$enable_autotrack = true;
		}
		$enable_autotrack = apply_filters( 'gofer_seo_ga_enable_autotrack', $enable_autotrack );

		$output = $this->universal_analytics();
		if ( $enable_autotrack ) {
			$autotrack = apply_filters( 'gofer_seo_google_autotrack', GOFER_SEO_URL . 'assets/google-analytics-autotrack-v2.4.1/autotrack.js' );
			$output .= sprintf( '<script async src="%s"></script>', $autotrack );
		}

		return apply_filters( 'gofer_seo_google_analytics', $output );
	}

	/**
	 * Universal Analytics
	 *
	 * Adds analytics.
	 *
	 * @since 1.0.0
	 *
	 * @link https://developers.google.com/analytics/devguides/collection/analyticsjs/field-reference
	 *
	 * @return false|string
	 */
	public function universal_analytics() {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();
		$allow_linker  = '';
		$domain_list   = '';

		$cookie_domain = ', \'auto\'';
		if ( $gofer_seo_options->options['modules']['general']['google_analytics']['enable_advanced_settings'] ) {
			if ( ! empty( $gofer_seo_options->options['modules']['general']['google_analytics']['track_domain'] ) ) {
				$track_domain = gofer_seo_sanitize_domain( $gofer_seo_options->options['modules']['general']['google_analytics']['track_domain'] );
				$track_domain = esc_js( $track_domain );
				if ( ! empty( $track_domain ) ) {
					$cookie_domain = '\'cookieDomain\': \'' . $track_domain . '\'';
				}
			}
		}

		if (
				$gofer_seo_options->options['modules']['general']['google_analytics']['enable_advanced_settings'] &&
				$gofer_seo_options->options['modules']['general']['google_analytics']['enable_track_multi_domains']
		) {
			$allow_linker = '\'allowLinker\': true';
			if ( ! empty( $gofer_seo_options->options['modules']['general']['google_analytics']['track_multi_domains'] ) ) {
				$addl_domains = trim( $gofer_seo_options->options['modules']['general']['google_analytics']['track_multi_domains'] );
				$addl_domains = preg_split( '/[\s,]+/', $addl_domains );
				if ( ! empty( $addl_domains ) ) {
					foreach ( $addl_domains as $d ) {
						$d = gofer_seo_sanitize_domain( $d );
						if ( ! empty( $d ) ) {
							if ( ! empty( $domain_list ) ) {
								$domain_list .= ', ';
							}
							$domain_list .= '\'' . $d . '\'';
						}
					}
				}
			}
		}
		$extra_options = array();
		if ( ! empty( $domain_list ) ) {
			$extra_options[] = 'ga(\'require\', \'linker\');';
			$extra_options[] = 'ga(\'linker:autoLink\', [' . $domain_list . '] );';
		}

		if ( $gofer_seo_options->options['modules']['general']['google_analytics']['enable_advanced_settings'] ) {
			if ( $gofer_seo_options->options['modules']['general']['google_analytics']['enable_advertising_features'] ) {
				$extra_options[] = 'ga(\'require\', \'displayfeatures\');';
			}
			if ( $gofer_seo_options->options['modules']['general']['google_analytics']['enable_enhance_ecommerce'] ) {
				$extra_options[] = 'ga(\'require\', \'ec\');';
			}
			if ( $gofer_seo_options->options['modules']['general']['google_analytics']['enable_enhance_link_attributes'] ) {
				$extra_options[] = 'ga(\'require\', \'linkid\', \'linkid.js\');';
			}
			if ( $gofer_seo_options->options['modules']['general']['google_analytics']['enable_anonymize_ip'] ) {
				$extra_options[] = 'ga(\'set\', \'anonymizeIp\', true);';
			}
			if ( $gofer_seo_options->options['modules']['general']['google_analytics']['enable_track_outbound_links'] ) {
				$extra_options[] = 'ga(\'require\', \'outboundLinkTracker\');';
			}
		}
		$extra_options = apply_filters( 'gofer_seo_ga_extra_options', $extra_options );

		/**
		 * Internal filter. Don't output certain GA features if Google Tag Manager is active.
		 *
		 * @since 1.0.0
		 */
		if ( apply_filters( 'gofer_seo_gtm_enabled', __return_false() ) ) {
			$options_to_remove = array(
				"ga('require', 'ec');",
				"ga('require', 'outboundLinkTracker');",
				"ga('require', 'outboundFormTracker');",
				"ga('require', 'eventTracker');",
				"ga('require', 'urlChangeTracker');",
				"ga('require', 'pageVisibilityTracker');",
				"ga('require', 'mediaQueryTracker');",
				"ga('require', 'impressionTracker');",
				"ga('require', 'maxScrollTracker');",
				"ga('require', 'socialWidgetTracker');",
				"ga('require', 'cleanUrlTracker');",
			);
			foreach ( $options_to_remove as $option ) {
				$index = array_search( $option, $extra_options, true );
				if ( $index ) {
					unset( $extra_options[ $index ] );
				}
				continue;
			}
		}

		$js_options = array();
		foreach ( array( 'cookie_domain', 'allow_linker' ) as $opts ) {
			if ( ! empty( $$opts ) ) {
				$js_options[] = $$opts;
			}
		}
		$js_options = empty( $js_options )
			? ''
			: ', { ' . implode( ',', $js_options ) . ' } ';
		// Prepare analytics.
		$analytics_id = esc_js( $gofer_seo_options->options['modules']['general']['google_analytics']['ua_id'] );

		$output_extra_options = '';
		foreach ( $extra_options as $option ) {
			$output_extra_options .= $option;
		}

		$output = sprintf(
			'<script type="text/javascript">
			window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
			ga("create", "%s" %s %s);
			%s
			ga("send", "pageview");
			</script>
			<script async src="https://www.google-analytics.com/analytics.js"></script>',
			$analytics_id,
			$cookie_domain,
			$js_options,
			$output_extra_options
		);

		return $output;
	}

}
// phpcs:enable
