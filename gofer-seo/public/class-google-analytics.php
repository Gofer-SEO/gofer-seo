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
		$cookie_domain = 'auto';
		$create_field_objects = array(
			'cookieDomain' => 'none',
			'allowLinker'  => false,
		);
		$extra_commands    = array();

		if ( $gofer_seo_options->options['modules']['general']['google_analytics']['enable_advanced_settings'] ) {
			// Cookie Domain.
			if ( ! empty( $gofer_seo_options->options['modules']['general']['google_analytics']['track_domain'] ) ) {
				$domain = gofer_seo_sanitize_domain( $gofer_seo_options->options['modules']['general']['google_analytics']['track_domain'] );
				$domain = esc_js( $domain );
				if ( ! empty( $cookie_domain ) ) {
					$create_field_objects['cookieDomain'] = $domain;
					$cookie_domain = $domain;
				}
			}

			// Advanced.
			/**
			 * IP Anonymization.
			 *
			 * @see https://developers.google.com/analytics/devguides/collection/analyticsjs/ip-anonymization
			 */
			if ( $gofer_seo_options->options['modules']['general']['google_analytics']['enable_anonymize_ip'] ) {
				$extra_commands[] = "ga('set', 'anonymizeIp', true);";
			}

			// Google Official Plugins.
			/**
			 * Advertising Features.
			 *
			 * @see https://developers.google.com/analytics/devguides/collection/analyticsjs/display-features
			 */
			if ( $gofer_seo_options->options['modules']['general']['google_analytics']['enable_advertising_features'] ) {
				$extra_commands[] = "ga('require', 'displayfeatures');";
			}

			/**
			 * Enhanced Ecommerce.
			 *
			 * @see https://developers.google.com/analytics/devguides/collection/analyticsjs/enhanced-ecommerce
			 */
			if ( $gofer_seo_options->options['modules']['general']['google_analytics']['enable_enhance_ecommerce'] ) {
				$extra_commands[] = "ga('require', 'ec');";
			}

			/**
			 * Enhanced Link Attribution.
			 *
			 * @see https://developers.google.com/analytics/devguides/collection/analyticsjs/enhanced-link-attribution
			 */
			if ( $gofer_seo_options->options['modules']['general']['google_analytics']['enable_enhance_link_attributes'] ) {
				$extra_commands[] = "ga('require', 'linkid', 'linkid.js');";
			}

			/**
			 * Linker.
			 *
			 * Allow Linker & Auto-Link Domains
			 *
			 * @see https://developers.google.com/analytics/devguides/collection/analyticsjs/linker
			 */
			if ( $gofer_seo_options->options['modules']['general']['google_analytics']['enable_track_multi_domains'] ) {
				$create_field_objects['allowLinker'] = true;
				if ( ! empty( $gofer_seo_options->options['modules']['general']['google_analytics']['track_multi_domains'] ) ) {
					$auto_link_domains = array();
					$domains = trim( $gofer_seo_options->options['modules']['general']['google_analytics']['track_multi_domains'] );
					$domains = preg_split( '/[\s,]+/', $domains );
					if ( ! empty( $domains ) ) {
						foreach ( $domains as $domain ) {
							$domain = gofer_seo_sanitize_domain( $domain );
							if ( ! empty( $domain ) ) {
								$auto_link_domains[] = $domain;
							}
						}
					}

					if ( ! empty( $auto_link_domains ) ) {
						$extra_commands[] = "ga('require', 'linker');";
						$extra_commands[] = "ga('linker:autoLink', [" . implode( ',', $auto_link_domains ) . "] );";
					}
				}
			}

			/**
			 * Track Outbound Links.
			 *
			 * Requires AutoTrack.js.
			 *
			 * @see https://github.com/googleanalytics/autotrack#plugins
			 */
			if ( $gofer_seo_options->options['modules']['general']['google_analytics']['enable_clean_url'] ) {
				$extra_commands[] = "ga('require', 'cleanUrlTracker');";
			}
			if ( $gofer_seo_options->options['modules']['general']['google_analytics']['enable_track_events'] ) {
				$extra_commands[] = "ga('require', 'eventTracker');";
			}
			if ( $gofer_seo_options->options['modules']['general']['google_analytics']['enable_track_impressions'] ) {
				$extra_commands[] = "ga('require', 'impressionTracker');";
			}
			if ( $gofer_seo_options->options['modules']['general']['google_analytics']['enable_track_max_scroll'] ) {
				$extra_commands[] = "ga('require', 'maxScrollTracker');";
			}
			if ( $gofer_seo_options->options['modules']['general']['google_analytics']['enable_track_media_query'] ) {
				$extra_commands[] = "ga('require', 'mediaQueryTracker');";
			}
			if ( $gofer_seo_options->options['modules']['general']['google_analytics']['enable_track_outbound_forms'] ) {
				$extra_commands[] = "ga('require', 'outboundFormTracker');";
			}
			if ( $gofer_seo_options->options['modules']['general']['google_analytics']['enable_track_outbound_links'] ) {
				$extra_commands[] = "ga('require', 'outboundLinkTracker');";
			}
			if ( $gofer_seo_options->options['modules']['general']['google_analytics']['enable_track_page_visibility'] ) {
				$extra_commands[] = "ga('require', 'pageVisibilityTracker');";
			}
			if ( $gofer_seo_options->options['modules']['general']['google_analytics']['enable_track_social_media'] ) {
				$extra_commands[] = "ga('require', 'socialWidgetTracker');";
			}
			if ( $gofer_seo_options->options['modules']['general']['google_analytics']['enable_track_url_changes'] ) {
				$extra_commands[] = "ga('require', 'urlChangeTracker');";
			}
		}
		$extra_commands = apply_filters( 'gofer_seo_ga_extra_commands', $extra_commands );

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
				$index = array_search( $option, $extra_commands, true );
				if ( $index ) {
					unset( $extra_commands[ $index ] );
				}
			}
		}

		// Output.
		$output_field_objects = array();
		foreach ( $create_field_objects as $field_name => $field_value ) {
			if ( is_bool( $field_value ) ) {
				$field_value = $field_value ? 'true' : 'false';
			} else {
				$field_value = strval( $field_value );
				$field_value = "'{$field_value}'";
			}

			$output_field_objects[] = "'{$field_name}': {$field_value}";
		}
		if ( empty( $output_field_objects ) ) {
			$output_field_objects = '';
		} else {
			$output_field_objects = ', {' . implode( ',', $output_field_objects ) . '}';
		}

		$output = sprintf(
			'<script type="text/javascript">
			window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
			ga(\'create\', \'%1$s\', \'%2$s\'%3$s);
			%4$s
			ga(\'send\', \'pageview\');
			</script>
			<script async src="https://www.google-analytics.com/analytics.js"></script>',
			esc_js( $gofer_seo_options->options['modules']['general']['google_analytics']['ua_id'] ),
			$cookie_domain,
			$output_field_objects,
			implode( PHP_EOL, $extra_commands )
		);

		return $output;
	}

}
// phpcs:enable
