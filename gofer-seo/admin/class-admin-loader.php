<?php
/**
 * Admin Loader
 *
 * @package Gofer SEO
 */

/**
 * Class Gofer_SEO_Admin_Loader.
 *
 * @since 1.0.0
 */
class Gofer_SEO_Admin_Loader {

	/**
	 * Screen Objects.
	 *
	 * @since 1.0.0
	 *
	 * @var array                 $screen_objects {
	 *     @type array            $page_module {
	 *         @type Gofer_SEO_Screen ${module_slug}
	 *     }
	 *     @type Gofer_SEO_Screen $edit_post
	 *     @type Gofer_SEO_Screen $edit_term
	 *     @type Gofer_SEO_Screen $edit_user
	 * }
	 */
	public $screen_objects = array(
		//'dashboard'   => null,
		//'page' => array(
		//	'module' => null,
		//),
		'page_module' => array(),
		/* 'edit_post'   => null, */
		/* 'edit_term'   => null, */
		/* 'edit_user'   => null, */
	);

	/**
	 * Extended Screen Objects.
	 *
	 * @since 1.0.0
	 *
	 * @var array[] $extended_screen_objects {
	 *     @type array $module_page {
	 *         @type array ${module_slug} {
	 *             @type string[] ${module_slug}
	 *         }
	 *     }
	 *     @type array $post_edit {
	 *         @type string[] ${module_slug}
	 *     }
	 *     @type array $term_edit {
	 *         @type string[] ${module_slug}
	 *     }
	 *     @type array $user_edit {
	 *         @type string[] ${module_slug}
	 *     }
	 * }
	 */
	public $extended_screen_objects = array(
		//'module_page' => array(
		//),
		'post_edit' => array(),
		'term_edit' => array(),
		'user_edit' => array(),
	);

	/**
	 * Gofer_SEO_Admin_Module_Loader constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->load();
	}

	/**
	 * Load.
	 *
	 * @since 1.0.0
	 */
	public function load() {
		$active_modules = array_merge( $this->get_mu_modules(), Gofer_SEO_Module_Loader::get_active_modules() );
		$this->load_modules( $active_modules );
	}

	/**
	 * Load Modules.
	 *
	 * @since 1.0.0
	 *
	 * @param string[] $module_slugs
	 */
	public function load_modules( array $module_slugs ) {
		foreach ( $module_slugs as $module_slug ) {
			$ext_screen_objects_tmp        = $this->load_ext_screen_module( $module_slug );
			$this->extended_screen_objects = array_merge_recursive( $ext_screen_objects_tmp, $this->extended_screen_objects );

			$screen_objects_tmp   = $this->load_screen_module( $module_slug );
			if ( ! empty( $screen_objects_tmp['page_module'] ) ) {
				$this->screen_objects['page_module'] = array_merge( $this->screen_objects['page_module'], $screen_objects_tmp['page_module'] );
			} else {
				// TODO Log that module doesn't exist.
			}
			$this->screen_objects = array_merge( $screen_objects_tmp, $this->screen_objects );
		}
	}

	/**
	 * Get Registered Screens.
	 *
	 * @since 1.0.0
	 *
	 * @return array screen_type => classname|array
	 */
	private function get_registered_screens() {
		$admin_screens = array(
			'page_module' => $this->get_registered_module_screens(),
			'edit_post'   => 'Gofer_SEO_Screen_Edit_Post',
			'edit_term'   => 'Gofer_SEO_Screen_Edit_Term',
			'edit_user'   => 'Gofer_SEO_Screen_Edit_User',
		);

		/**
		 * Register Admin Screens.
		 *
		 * @since 1.0.0
		 *
		 * @param array $admin_screens Used to register the classname to the module slug.
		 *                             slug => classname
		 */
		return apply_filters( 'gofer_seo_register_screens', $admin_screens );
	}

	/**
	 * Get Registered Module Screens.
	 *
	 * @since 1.0.0
	 *
	 * @return string[] slug => classname
	 */
	private function get_registered_module_screens() {
		$module_screens = array(
			'general'      => 'Gofer_SEO_Screen_Page_Module_General',
			'social_media' => 'Gofer_SEO_Screen_Page_Module_Social_Media',
			'sitemap'      => 'Gofer_SEO_Screen_Page_Module_Sitemap',
			'schema_graph' => 'Gofer_SEO_Screen_Page_Module_Schema_Graph',
			'crawlers'     => 'Gofer_SEO_Screen_Page_Module_Crawlers',
			'advanced'     => 'Gofer_SEO_Screen_Page_Module_Advanced',
			'debugger'     => 'Gofer_SEO_Screen_Page_Module_Debugger',
		);

		/**
		 * Register Admin Module Screens.
		 *
		 * @since 1.0.0
		 *
		 * @param array $admin_screens Used to register the classname to the module slug.
		 *                             slug => classname
		 */
		return apply_filters( 'gofer_seo_register_module_screens', $module_screens );
	}

	/**
	 * Get Registered Extended Screens.
	 *
	 * @since 1.0.0
	 *
	 * @return array[]
	 */
	private function get_registered_extended_screens() {
		return array(
			'module_page' => $this->get_registered_module_page_ext_screen_modules(),
			'post_edit'   => $this->get_registered_post_edit_ext_screen_modules(),
			'term_edit'   => $this->get_registered_term_edit_ext_screen_modules(),
			'user_edit'   => $this->get_registered_user_edit_ext_screen_modules(),
		);
	}

	/**
	 * Get Registered Module-Page Ext Screen Modules.
	 *
	 * @since 1.0.0
	 *
	 * @return array slug => classname
	 */
	private function get_registered_module_page_ext_screen_modules() {
		$ext_modules = array();

		/**
		 * Register Admin Post Editor Modules.
		 *
		 * @since 1.0.0
		 *
		 * @param array $ext_modules Used to register the classname to the module slug.
		 *                           slug => classname
		 */
		return apply_filters( 'gofer_seo_register_ext_screen_modules', $ext_modules );
	}

	/**
	 * Get Registered Post Edit Ext Modules.
	 *
	 * @since 1.0.0
	 *
	 * @return array slug => classname
	 */
	private function get_registered_post_edit_ext_screen_modules() {
		$post_edit_modules = array(
			'general'      => array(
				'Gofer_SEO_Screen_Post_Editor_General',
			),
			'social_media' => array(
				'Gofer_SEO_Screen_Post_Editor_Social_Media',
			),
			'sitemap'      => array(
				'Gofer_SEO_Screen_Post_Editor_Sitemap',
			),
		);

		/**
		 * Register Admin Post Editor Modules.
		 *
		 * @since 1.0.0
		 *
		 * @param array $post_edit_modules Used to register the classname to the module slug.
		 *                                 slug => classname
		 */
		return apply_filters( 'gofer_seo_register_ext_screen_post_edit_modules', $post_edit_modules );
	}

	/**
	 * Get Registered Term Edit Ext Screen Modules.
	 *
	 * @since 1.0.0
	 *
	 * @return array slug => classname
	 */
	private function get_registered_term_edit_ext_screen_modules() {
		$term_editor_modules = array(
			'general'      => array(
				'Gofer_SEO_Screen_Term_Editor_General',
			),
			'social_media' => array(
				'Gofer_SEO_Screen_Term_Editor_Social_Media',
			),
			'sitemap'      => array(
				'Gofer_SEO_Screen_Term_Editor_Sitemap',
			),
		);

		/**
		 * Register Admin Post Editor Modules.
		 *
		 * @since 1.0.0
		 *
		 * @param array $term_editor_modules Used to register the classname to the module slug.
		 *                                   slug => classname
		 */
		return apply_filters( 'gofer_seo_register_ext_screen_term_edit_modules', $term_editor_modules );
	}

	/**
	 * Get Registered User Edit Ext Screen Modules.
	 *
	 * @since 1.0.0
	 *
	 * @return array slug => classname
	 */
	private function get_registered_user_edit_ext_screen_modules() {
		$user_editor_modules = array(
			'general' => array(
				'Gofer_SEO_Screen_User_Editor_General',
			),
			'sitemap' => array(
				'Gofer_SEO_Screen_User_Editor_Sitemap',
			),
		);

		/**
		 * Register Admin User Editor Modules.
		 *
		 * @since 1.0.0
		 *
		 * @param array $user_editor_modules Used to register the classname to the module slug.
		 *                                   slug => classname
		 */
		return apply_filters( 'gofer_seo_register_ext_screen_user_edit_modules', $user_editor_modules );
	}

	/**
	 * Load Screen Module.
	 *
	 * @since 1.0.0
	 *
	 * @param $module_slug
	 * @return mixed
	 */
	private function load_screen_module( $module_slug ) {
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

		$registered_screens = $this->get_registered_screens();
		$screen_objects     = array();
		foreach ( $registered_screens as $screen_type => $registered_screens_arr ) {
			if ( 'page_module' === $screen_type ) {
				if ( isset( $registered_screens_arr[ $module_slug ] ) ) {
					if ( isset( $this->screen_objects[ $screen_type ][ $module_slug ] ) ) {
						if ( ! isset( $screen_objects[ $screen_type ] ) ) {
							$screen_objects[ $screen_type ] = array();
						}
						$screen_objects[ $screen_type ][ $module_slug ] = $this->screen_objects[ $screen_type ][ $module_slug ];
					} elseif ( class_exists( $registered_screens_arr[ $module_slug ] ) ) {
						if ( ! isset( $screen_objects[ $screen_type ] ) ) {
							$screen_objects[ $screen_type ] = array();
						}
						$classname = $registered_screens_arr[ $module_slug ];
						$screen_objects[ $screen_type ][ $module_slug ] = new $classname();
					}
				}
			} else {
				if ( isset( $this->screen_objects[ $screen_type ] ) ) {
					$screen_objects[ $screen_type ] = $this->screen_objects[ $screen_type ];
				} elseif ( is_string( $registered_screens_arr ) && class_exists( $registered_screens_arr ) ) {
					if ( ! isset( $screen_objects[ $screen_type ] ) ) {
						$screen_objects[ $screen_type ] = array();
					}
					$classname = $registered_screens_arr;
					$screen_objects[ $screen_type ] = new $classname();
				}
			}
		}

		return $screen_objects;
	}

	/**
	 * Load Ext Screen Module.
	 *
	 * @since 1.0.0
	 *
	 * @param string $module_slug
	 * @return array
	 */
	private function load_ext_screen_module( $module_slug ) {
		// TODO Use this if/when adding file editor.
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

		$registered_ext_screens = $this->get_registered_extended_screens();
		$ext_screen_objects     = array();
		foreach ( $registered_ext_screens as $screen_type => $registered_ext_screens_arr ) {
			if ( isset( $registered_ext_screens_arr[ $module_slug ] ) ) {
				foreach ( $registered_ext_screens_arr[ $module_slug ] as $classname ) {
					if ( class_exists( $classname ) ) {
						if ( ! isset( $ext_screen_objects[ $screen_type ] ) ) {
							$ext_screen_objects[ $screen_type ] = array();
						}
						if ( ! isset( $ext_screen_objects[ $screen_type ][ $module_slug ] ) ) {
							$ext_screen_objects[ $screen_type ][ $module_slug ] = array();
						}
					}

					if ( isset( $this->extended_screen_objects[ $screen_type ][ $module_slug ][ $classname ] ) ) {
						$ext_screen_objects[ $screen_type ][ $module_slug ][ $classname ] = $this->extended_screen_objects[ $screen_type ][ $module_slug ][ $classname ];
					} elseif ( class_exists( $classname ) ) {
						$ext_screen_objects[ $screen_type ][ $module_slug ][ $classname ] = new $classname();
					}
				}
			}
		}

		return $ext_screen_objects;
	}

	/* **________******************************************************************************************************/
	/* _/ COMMON \____________________________________________________________________________________________________*/

	/**
	 * Get MU (Must-Use) Modules.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_mu_modules() {
		return Gofer_SEO_Module_Loader::get_mu_modules();
	}

	/**
	 * Get Modules.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_modules() {
		$registered_modules = Gofer_SEO_Module_Loader::get_registered_modules();

		return array_keys( $registered_modules );
	}

	/**
	 * Get Screen IDs.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_screen_ids() {
		$screen_ids = array();
		foreach ( $this->screen_objects as $screen_type => $object_type_screens ) {
			foreach ( $object_type_screens as $module_slug => $screen_object ) {
				/**
				 * @var Gofer_SEO_Screen $screen_object
				 */
				$screen_ids = array_merge( $screen_ids, $screen_object->get_screen_ids() );
			}
		}

		$screen_ids = array_unique( $screen_ids );

		return $screen_ids;
	}

}
