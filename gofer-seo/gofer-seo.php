<?php
/**
 * Gofer SEO
 *
 * @package           Gofer SEO
 * @author            EkoJR
 * @copyright         2022 EkoJR
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Gofer SEO
 * Description:       SEO Performance to Gofer.
 * Version:           1.0.1.a01
 * Requires at least: 4.9
 * Requires PHP:      5.3.8
 * Author:            EkoJR
 * Author URI:        https://ekojr.com
 * Text Domain:       gofer-seo
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * == Copyright ==
 * Copyright (C) 2022 EkoJR
 *
 * Gofer SEO is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * Gofer SEO is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Gofer SEO; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA,
 * or visit <http://www.gnu.org/licenses/>.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( ! defined( 'GOFER_SEO_VERSION' ) ) {
	/**
	 * Defines Gofer SEO constants.
	 *
	 * @since 1.0.0
	 */
	function gofer_seo_constants() {
		/*
		 * Get plugin-file-data from gofer-seo.php, and grab
		 * the plugin's meta default_headers.
		 *
		 * @see get_file_data()
		 * @link https://developer.wordpress.org/reference/functions/get_file_data/
		 * @link https://hitchhackerguide.com/2011/02/12/get_plugin_data/
		 */
		$default_headers = array(
			'Name'       => 'Plugin Name',
			'Nicename'   => 'Text Domain',
			'TextDomain' => 'Text Domain',
			'DomainPath' => 'Domain Path',
			'Version'    => 'Version',
		);
		$plugin_data = get_file_data( __FILE__, $default_headers );

		$file = __FILE__;
		if ( strpos( $file, '\\' ) ) {
			$file = wp_normalize_path( $file );
		}

		// WP's `mu-plugins` location.
		$parent_dir = WPMU_PLUGIN_DIR . '/';
		if ( strpos( $parent_dir, '\\' ) ) {
			$parent_dir = wp_normalize_path( $parent_dir );
		}
		$plugin_basename = plugin_basename( $file );
		$plugin_dirname  = dirname( plugin_basename( $file ) );

		// WP's `plugins` location.
		if ( ! file_exists( $parent_dir . $plugin_basename ) ) {
			$parent_dir = WP_PLUGIN_DIR . '/';
			if ( strpos( $parent_dir, '\\' ) ) {
				$parent_dir = wp_normalize_path( $parent_dir );
			}
		}

		// Isolated file location.
		if ( ! file_exists( $parent_dir . $plugin_basename ) ) {
			// Avoid using `plugin_basename()` with situations that don't store the plugin directory in `WP_PLUGIN_DIR`;
			// prime example would be Unit Testing within Travis CI.
			$parent_dir = dirname( dirname( __FILE__ ) ) . '/';
			if ( strpos( $parent_dir, '\\' ) ) {
				$parent_dir = wp_normalize_path( $parent_dir );
			}
			$plugin_basename = str_replace( $parent_dir, '', $file );
			$plugin_dirname  = dirname( plugin_basename( str_replace( $parent_dir, '', $file ) ) );
		}

		/**
		 * Version Number.
		 *
		 * @since 1.0.0
		 *
		 * @var string $GOFER_SEO_VERSION Ex. '1.2.3'.
		 */
		define( 'GOFER_SEO_VERSION', $plugin_data['Version'] );

		/**
		 * Gofer SEO Display Name.
		 *
		 * @since 1.0.0
		 *
		 * @var string $GOFER_SEO_NAME Contains 'Gofer SEO'.
		 */
		define( 'GOFER_SEO_NAME', $plugin_data['Name'] );

		/**
		 * Gofer SEO Nice-name.
		 *
		 * @since 1.0.0
		 *
		 * @var string $GOFER_SEO_SLUG Contains 'gofer-seo'.
		 */
		define( 'GOFER_SEO_NICENAME', $plugin_data['Nicename'] );

		/**
		 * Plugin Basename.
		 *
		 * @since 1.0.0
		 *
		 * @var string GOFER_SEO_PLUGIN_BASENAME Plugin basename on WP platform. Eg. 'gofer-seo/gofer-seo.php`.
		 */
		define( 'GOFER_SEO_PLUGIN_BASENAME', $plugin_basename );

		/**
		 * Gofer SEO Text Domain.
		 *
		 * @since 1.0.0
		 *
		 * @var string $GOFER_SEO_TEXTDOMAIN Contains 'gofer-seo'.
		 */
		define( 'GOFER_SEO_TEXTDOMAIN', $plugin_data['TextDomain'] );

		// Defines constants that haven't been defined.
		// Keep `! defined()` for development purposes to possibly separate plugin development from other plugins.
		if ( ! defined( 'GOFER_SEO_DOMAIN_PATH' ) ) {
			/**
			 * Plugin's Text Domain Path
			 *
			 * @since 1.0.0
			 *
			 * @var string $GOFER_SEO_DOMAIN_PATH Directory for storing languages.
			 */
			define( 'GOFER_SEO_DOMAIN_PATH', $plugin_data['DomainPath'] );
		}

		if ( ! defined( 'GOFER_SEO_URL' ) ) {
			/**
			 * URL Location.
			 *
			 * @since 1.0.0
			 *
			 * @var string $GOFER_SEO_URL Contains 'http://localhost/wp-content/plugins/gofer-seo/'.
			 */
			define( 'GOFER_SEO_URL', plugin_dir_url( __FILE__ ) );
		}

		if ( ! defined( 'GOFER_SEO_IMAGES_URL' ) ) {
			/**
			 * Plugin Images URL
			 *
			 * @since 1.0.0
			 *
			 * @var string $GOFER_SEO_IMAGES_URL URL location for the plugin's image directory. Eg. `http://gofer-seo.test/wp-content/plugins/gofer-seo/images/`
			 */
			define( 'GOFER_SEO_IMAGES_URL', plugin_dir_url( __FILE__ ) . 'public/images/' );
		}

		if ( ! defined( 'GOFER_SEO_ADMIN_IMAGES_URL' ) ) {
			/**
			 * Plugin Images URL
			 *
			 * @since 1.0.0
			 *
			 * @var string $GOFER_SEO_ADMIN_IMAGES_URL URL location for the plugin's image directory. Eg. `http://gofer-seo.test/wp-content/plugins/gofer-seo/images/`
			 */
			define( 'GOFER_SEO_ADMIN_IMAGES_URL', plugin_dir_url( __FILE__ ) . 'admin/images/' );
		}

		if ( ! defined( 'GOFER_SEO_DIR' ) ) {
			/**
			 * Directory Path.
			 *
			 * @since 1.0.0
			 *
			 * @var string $GOFER_SEO_DIR Contains 'C:/WordPress/wp-content/plugins/gofer-seo/'.
			 */
			define( 'GOFER_SEO_DIR', $parent_dir . $plugin_dirname . '/' );
		}

		if ( ! defined( 'GOFER_SEO_PLUGIN_DIRNAME' ) ) {
			/**
			 * Plugin Directory Name
			 *
			 * @since 1.0.0
			 *
			 * @param string $GOFER_SEO_PLUGIN_DIRNAME Plugin folder/directory name. Eg. `gofer-seo`
			 */
			define( 'GOFER_SEO_PLUGIN_DIRNAME', $plugin_dirname );
		}

		if ( ! defined( 'GOFER_SEO_PARENT_DIR' ) ) {
			/**
			 * Directory Path.
			 *
			 * @since 1.0.0
			 *
			 * @var string $GOFER_SEO_DIR Contains 'C:\WordPress\wp-content\plugins\'.
			 */
			define( 'GOFER_SEO_PARENT_DIR', $parent_dir );
		}

		if ( ! defined( 'GOFER_SEO_MEMORY_LIMIT' ) ) {
			/**
			 * Plugin Baseline Memory Limit
			 *
			 * @since 1.0.0
			 *
			 * @var string $GOFER_SEO_MEMORY_LIMIT The memory limit to set the ini config to.
			 */
			define( 'GOFER_SEO_MEMORY_LIMIT', '0' );
		}

		if ( ! defined( 'GOFER_SEO_TEMPLATE_DEBUG_MODE' ) ) {
			/**
			 * Gofer Template Debug
			 *
			 * @since 1.0.0
			 *
			 * @var boolean $GOFER_SEO_TEMPLATE_DEBUG_MODE Used for bypassing child theme customizations when debugging.
			 */
			define( 'GOFER_SEO_TEMPLATE_DEBUG_MODE', false );
		}

		if ( ! defined( 'GOFER_SEO_DO_LOG' ) ) {
			/**
			 * Gofer Error Logging.
			 *
			 * @since 1.0.0
			 *
			 * @var boolean $GOFER_SEO_DO_LOG True to log errors.
			 */
			define( 'GOFER_SEO_DO_LOG', false );
		}
	}
	gofer_seo_constants();
}

if ( ! class_exists( 'Gofer_SEO' ) ) {
	require_once GOFER_SEO_DIR . 'class-gofer-seo.php';
	global $gofer_seo;
	if ( is_null( $gofer_seo ) ) {
		$gofer_seo = Gofer_SEO::get_instance();
	}
}
