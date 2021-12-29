<?php
/**
 * Module Manager
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Module_Manager.
 *
 * @since 1.0.0
 */
class Gofer_SEO_Module_Loader {

	/**
	 * Module Objects.
	 *
	 * @since 1.0.0
	 *
	 * @var Gofer_SEO_Module[][]
	 */
	protected $modules = array();

	/**
	 * Gofer_SEO_Module_Manager constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->load();
	}

	/**
	 * Load
	 *
	 * @since 1.0.0
	 */
	public function load() {
		$active_modules = array_unique(
			array_merge(
				$this->get_mu_modules(),
				$this->get_active_modules() )
		);
		$this->modules = $this->load_modules( $active_modules );
	}

	/**
	 * Get Registered Modules.
	 *
	 * @since 1.0.0
	 *
	 * @return string[][] slug => classname
	 */
	public static function get_registered_modules() {
		$modules = array(
			'general'      => array( 'core' => 'Gofer_SEO_Module_General' ),
			'social_media' => array( 'core' => 'Gofer_SEO_Module_Social_Media' ),
			'sitemap'      => array( 'core' => 'Gofer_SEO_Module_Sitemap' ),
			'schema_graph' => array( 'core' => 'Gofer_SEO_Module_Schema_Graph' ),
			'crawlers'     => array( 'core' => 'Gofer_SEO_Module_Crawlers' ),
			'advanced'     => array( 'core' => 'Gofer_SEO_Module_Advanced' ),
			'debugger'     => array( 'core' => 'Gofer_SEO_Module_Debugger' ),
		);

		/**
		 * Register Modules.
		 *
		 * @since 1.0.0
		 *
		 * @param array $modules Used to register the classname to the module slug.
		 *                          slug => classname
		 */
		return apply_filters( 'gofer_seo_register_modules', $modules );
	}

	/**
	 * Get MU (Must-Use) Modules.
	 *
	 * @since 1.0.0
	 *
	 * @return string[]
	 */
	public static function get_mu_modules() {
		$mu_modules = array(
			'general',
		);

		/**
		 * Must-Use Modules.
		 *
		 * @since 1.0.0
		 *
		 * @param string[] $mu_modules Used to set module (slugs) for must-use.
		 */
		return apply_filters( 'gofer_seo_mu_modules', $mu_modules );
	}

	/**
	 * Get Active Modules.
	 *
	 * @since 1.0.0
	 *
	 * @return string[]
	 */
	public static function get_active_modules() {
		$gofer_seo_options = Gofer_SEO_Options::get_instance();

		$active_modules = array_filter( $gofer_seo_options->options['enable_modules'] );
		$active_modules = array_keys( $active_modules );

		return $active_modules;
	}

	/**
	 * Load Modules.
	 *
	 * @since 1.0.0
	 *
	 * @param string[] $module_slugs
	 * @return array
	 */
	public function load_modules( $module_slugs ) {
		$module_objects = array();

		foreach ( $module_slugs as $module_slug ) {
			$module_object = $this->load_module( $module_slug );
			if ( false !== $module_object ) {
				$module_objects[ $module_slug ] = $module_object;
			}
		}

		return $module_objects;
	}

	/**
	 * Load Module.
	 *
	 * @since 1.0.0
	 *
	 * @param string $module_slug
	 * @return mixed
	 */
	public function load_module( $module_slug ) {
		// TODO Use this when adding file editor.
		if (
				'file_editor' === $module_slug &&
				(
					( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) ||
					( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) ||
					! is_super_admin()
				)
		) {
			return false;
		}

		$rtn_module = array();
		$module_list = $this->get_registered_modules();
		if ( isset( $this->modules[ $module_slug ] ) ) {
			if ( isset( $module_list[ $module_slug ] ) ) {
				foreach ( $module_list[ $module_slug ] as $origin => $module_classname ) {
					$module_origin = array();
					if ( isset( $this->modules[ $module_slug ][ $origin ] ) ) {
						$module_origin = $this->modules[ $module_slug ][ $origin ];
					} else {
						if ( class_exists( $module_list[ $module_slug ][ $origin ] ) ) {
							$classname = $module_list[ $module_slug ][ $origin ];
							$module_origin = new $classname();
						}
					}

					if ( ! empty( $module_origin ) ) {
						$rtn_module[ $origin ] = $module_origin;
					}
				}
			}
		} else {
			if ( isset( $module_list[ $module_slug ] ) ) {
				foreach ( $module_list[ $module_slug ] as $origin => $module_classname ) {
					$module_origin = false;
					if ( class_exists( $module_list[ $module_slug ][ $origin ] ) ) {
						$classname = $module_list[ $module_slug ][ $origin ];
						$module_origin = new $classname();
					}

					if ( ! empty( $module_origin ) ) {
						$rtn_module[ $origin ] = $module_origin;
					}
				}
			}
		}

		if ( empty( $rtn_module ) ) {
			return false;
		}
		return $rtn_module;
	}

	/**
	 * Get Loaded Module.
	 *
	 * Can get module class by either using the module slug and/or origin,
	 * OR the classname of the module.
	 *
	 * @since 1.0.0
	 *
	 * @param string $module_key The module slug.
	 * @param string $origin     The origin key that added the module.
	 * @return false|mixed
	 */
	public function get_loaded_module( $module_key, $origin = 'core' ) {
		$module_list = $this->get_registered_modules();
		$module_classname = $module_key;
		if ( isset( $module_list[ $module_key ] ) && isset( $module_list[ $module_key ][ $origin ] ) ) {
			$module_classname = $module_list[ $module_key ][ $origin ];
		}

		if ( isset( $this->modules[ $module_key ] ) && isset( $this->modules[ $module_key ][ $origin ] ) ) {
			return $this->modules[ $module_key ][ $origin ];
		} elseif ( isset( $this->modules[ $module_key ] ) ) {
			foreach ( $this->modules[ $module_key ] as $module_object ) {
				if ( get_class( $module_object ) === $module_classname ) {
					return $module_object;
				}
			}
		} else {
			foreach ( $this->modules as $k1_module_key => $v1_module_arr ) {
				foreach ( $v1_module_arr as $module_object ) {
					if ( get_class( $module_object ) === $module_classname ) {
						return $module_object;
					}
				}
			}
		}

		return false;
	}
}
