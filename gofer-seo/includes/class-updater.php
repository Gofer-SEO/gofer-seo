<?php
/**
 * Gofer SEO Updater - Controller/Handler.
 *
 * Prototype for future updates.
 * The class is designed to code for version updates separate, and extendable for other updates that may occur or
 * could be improved by others when dealing with unique environments.
 * The class is also designed to create backups, aka revisions, every time there is a database update, and would
 * make it possible to revert back if something goes wrong.
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

/**
 * Class Gofer_SEO_Updater
 *
 * @since 1.0.0
 */
class Gofer_SEO_Updater {

	/**
	 * Identifies there is an update.
	 *
	 * @since 1.0.0
	 *
	 * @var bool $needs_update
	 */
	public $needs_update = false;

	/**
	 * Old/Current Version Number.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $old_version;

	/**
	 * The items to be updated.
	 *
	 * @since 1.0.0
	 *
	 * @var array {
	 *     @type array $gofer_seo_options Gofer SEO plugin options.
	 *     @type array $post_data TODO
	 *     @type array $term_data TODO
	 *     @type array $user_data TODO
	 * }
	 */
	private $old_items;

	/**
	 * The items that were updated.
	 *
	 * After get_update()|do_update(), this will store the items updated.
	 *
	 * @since 1.0.0
	 *
	 * @var array {
	 *     @see \Gofer_SEO_Updater::$old_items For details.
	 * }
	 */
	public $updated_items;

	/**
	 * Update Classes - Options
	 *
	 * Used for updating wp_options > gofer_seo_options.
	 *
	 * @since 1.0.0
	 *
	 * @var array[] $update_options_classes {
	 *     @type array {
	 *         @type string                   $version
	 *         @type Gofer_SEO_Update_Options $class
	 *         @type int                      $priority
	 *         @type bool                     $admin_permission
	 *     }
	 * }
	 */
	private $update_options_classes = array();

	/**
	 * Gofer_SEO_Updater constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $old_items
	 * @param string $old_version
	 */
	public function __construct( $old_items = array(), $old_version = '' ) {
		if ( empty( $old_items ) ) {
			$old_items   = $this->get_old_items();
			$old_version = $this->get_old_version( $old_items );
		}
		if ( empty( $old_version ) ) {
			// If Gofer SEO options is not passed in `$old_items`, version number will be empty, and should bail.
			// If updating data other than `gofer_seo_options`, then supply a version number.
			$old_version = $this->get_old_version( $old_items );
			if ( empty( $old_version ) ) {
				// Bail if still no version number.
				new WP_Error(
					'gofer_seo_updater',
					sprintf(
						/* translators: %1$s shows the variable name, and %2$s shows this method reference. */
						__( 'Gofer SEO Error: empty `%1$s` is being passed to `%2$s`.', 'gofer-seo' ),
						'$old_version',
						'Gofer_SEO_Updater::__construct()'
					)
				);
			}
		}

		$this->old_version = $old_version;
		$this->old_items   = $old_items;

		// Just check if an update is needed, and leave. Outside operations will either (do|get)_updates.
		if ( version_compare( GOFER_SEO_VERSION, $old_version, '>' ) ) {
			$this->needs_update = true;
			$this->_autoload_files();
		}
	}

	/**
	 * Autoload Files.
	 *
	 * For security, files must be within the directory, with the correct prefix, and with the
	 * correct suffix (file extension). This is meant to avoid any injected files through malicious code.
	 *
	 * @since 1.0.0
	 *
	 * @link https://php.net/manual/en/class.directoryiterator.php
	 * @link https://stackoverflow.com/a/25988433/1376780
	 * @see StackOverflow for getting all filenamess in a directory.
	 * @see DirectoryIterator class
	 */
	private function _autoload_files() {
		include_once GOFER_SEO_DIR . 'includes/updates/class-update-options.php';
		foreach ( new DirectoryIterator( GOFER_SEO_DIR . 'includes/updates/' ) as $file ) {
			$extension = pathinfo( $file->getFilename(), PATHINFO_EXTENSION );
			if ( $file->isFile() && 'php' === $extension ) {
				$filename = $file->getFilename();

				// Qualified file pattern; "class-update-options-{VERSION}.php".
				// Prevents any malicious files that may have spread.
				if ( preg_match( '/^class-update-options-[0-9ab-]+\.php$/', $filename ) ) {
					include_once GOFER_SEO_DIR . 'includes/updates/' . $filename;
				}
			}
		}
	}

	/**
	 * Get items to update.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_old_items() {
		$old_items = array(
			'gofer_seo_options' => Gofer_SEO_Options::get_instance()->options,
		);

		return $old_items;
	}

	/**
	 * Get old version from old items.
	 *
	 * @since 1.0.0
	 *
	 * @param $old_items
	 * @return string
	 */
	private function get_old_version( $old_items ) {
		if ( isset( $old_items['gofer_seo_options'] ) && isset( $old_items['gofer_seo_options']['version'] ) ) {
			return $old_items['gofer_seo_options']['version'];
		}

		return '';
	}

	/**
	 * Get the default values for the updater options.
	 *
	 * @since 1.0.0
	 *
	 * @return array[] {
	 *     @see \Gofer_SEO_Updater::get_updater_options() For details.
	 * }
	 */
	private function get_updater_options_defaults() {
		return array(
			'revisions' => array(),
		);
	}

	/**
	 * Get the database/options for the updater.
	 *
	 * @since 1.0.0
	 *
	 * @return array[] {
	 *     Contains data regarding revisions of past updates.
	 *
	 *     @type array $revisions {
	 *         @type array {
	 *             @type string $version  The version number.
	 *             @type string $time_set The time the data was stored.
	 *             @type array  $contains What the revision contains.
	 *             @type string $key      The slug reference. Used to fetch the backup.
	 *         }
	 *
	 *     }
	 * }
	 */
	public function get_updater_options() {
		$options = get_option( 'gofer_seo_updater' );

		if ( false !== $options ) {
			return wp_parse_args( $options, $this->get_updater_options_defaults() );
		} else {
			return $this->get_updater_options_defaults();
		}
	}

	/**
	 * Update the database/options with new options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $options The Updater Data/Options to save.
	 */
	public function update_updater_options( $options ) {
		$options = wp_parse_args( $options, $this->get_updater_options_defaults() );

		if ( isset( $options ) ) {
			update_option( 'gofer_seo_updater', $options );
		}
	}

	/**
	 * Store Items as a Revision.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $items   Items to save to the database.
	 * @param string $version The version number the items existed in.
	 */
	public function store_as_revision( $items, $version ) {
		$time = time();
		$revision = array(
			'version'  => $version,
			'time_set' => $time,
			'contains' => array_keys( $items ),
			'key'      => str_replace( '.', '_', $version ) . '_' . $time,
		);

		$updater_options = $this->get_updater_options();
		$updater_options['revisions'][ $revision['key'] ] = $revision;
		update_option( 'gofer_seo_revision_' . $revision['key'], $items );
		$this->update_updater_options( $updater_options );
	}

	/**
	 * Get Revision.
	 *
	 * Simple method to fetch the target backup.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The revision > key.
	 * @return array|bool The old items that were backed up. False if it doesn't exist.
	 */
	public function get_revision( $key ) {
		return get_option( $key );
	}

	/**
	 * Load Option Updates.
	 *
	 * Registers any (Gofer_SEO)_Update_Options classes.
	 *
	 * @since 1.0.0
	 */
	private function load_option_updates() {
		// Don't assign an array key since there could be more than 1 update for a version.
		$update_options_classes[] = array(
			// Example update.
			'version'          => '0.0.0',                      // The version number that the update occurs at.
			'class'            => new Gofer_SEO_Update_Options_0_0_0(), // The class to handle an update.
			'priority'         => 10,                           // Lower numbers go first.
			'admin_permission' => false,                        // TODO Idea to halt an update until Admin triggers it.
		);

		/**
		 * TODO phpDoc.
		 */
		$update_options_classes = apply_filters( 'gofer_seo_updater_register_update_options', $update_options_classes );

		// Fills in any missing values required for sorting from newest to oldest.
		foreach ( $update_options_classes as $index => $update_class ) {
			if ( ! isset( $update_class['class'] ) ) {
				new WP_Error(
					'gpfer_seo_updater',
					sprintf(
						/* translators: %1$s is PHP syntax for 'class', and %2$s is the method reference. */
						__( 'Gofer SEO Error: update class missing %1$s in %2$s.', 'gofer-seo' ),
						'\'class\'',
						'`Gofer_SEO_Updater::load_option_updates()`'
					)
				);
				unset( $update_options_classes[ $index ] );
				continue;
			} elseif ( ! in_array( 'Gofer_SEO_Update_Options', class_implements( $update_class['class'] ), true ) ) {
				new WP_Error(
					'gpfer_seo_updater',
					sprintf(
						/* translators: %1$s is PHP syntax for 'extends *', and %2$s is the classname reference. */
						__( 'Gofer SEO Error: update class missing `%1$s` in `%2$s`  class declaration.', 'gofer-seo' ),
						'extends Gofer_SEO_Update_Options',
						get_class( $update_class['class'] )
					)
				);
				unset( $update_options_classes[ $index ] );
				continue;
			}

			if ( empty( $update_class['version'] ) ) {
				$update_class['version'] = $update_class['class']->version;
			}

			if ( ! isset( $update_class['priority'] ) ) {
				$update_class['priority'] = $update_class['class']->priority;
			}
		}

		// Sort oldest to newest
		$this->update_options_classes = $this->sort_version_updates( $update_options_classes );
	}

	/**
	 * Sort Version Updates.
	 *
	 * Sort based on version number & priority.
	 *
	 * @since 1.0.0
	 *
	 * @param array $version_updates An array of version updates to sort.
	 * @return array Sorted array of version updates.
	 */
	private function sort_version_updates( $version_updates ) {
		static $sorted_version_updates;
		if ( null === $sorted_version_updates ) {
			$sorted_version_updates = array();
		} else {
			return $sorted_version_updates;
		}

		$version_numbers = array_combine(
			array_keys( $version_updates ),
			wp_list_pluck( $version_updates, 'version' )
		);

		uasort( $version_numbers, 'version_compare');

		$sorted_version_updates = array();
		while ( ! empty( $version_numbers ) ) {
			foreach ( $version_numbers as $k1_index => $v1_version_number ) {
				unset( $version_numbers[ $k1_index ] );

				$other_version_numbers_keys = array_keys( $version_numbers, $v1_version_number, true );
				if ( empty( $other_version_numbers_keys ) ) {
					$sorted_version_updates[] = $version_updates[ $k1_index ];
				} else {
					// Another update with the same version is detected, and will sort by priority.

					// Gather version updates based on other version numbers detected. Then pluck 'priority'.
					$tmp_version_updates = array();
					foreach ( $other_version_numbers_keys as $other_version_number_key ) {
						$tmp_version_updates[ $other_version_number_key ] = $version_updates[ $other_version_number_key ];
					}
					$version_priorities = array_combine(
						array_keys( $tmp_version_updates ),
						wp_list_pluck( $version_updates, 'priority' )
					);

					uasort( $version_priorities, 'intval' );
					foreach ( $version_priorities as $priority_index => $version_priority ) {
						$sorted_version_updates[] = $version_updates[ $k1_index ];
						unset( $version_numbers[ $priority_index ] );
					}
				}
			}
		}

		return $sorted_version_updates;
	}

	/**
	 * Do the Updates.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if successful.
	 */
	public function do_updates() {
		if ( ! $this->needs_update ) {
			return false;
		}

		$updated_items = $this->get_updates( 'GOFER_SEO' );

		$this->store_as_revision( $this->old_items, $this->old_version );

		// Save updated options if it exists.
		$options = Gofer_SEO_Options::get_instance();
		if ( isset( $updated_items['gofer_seo_options'] ) ) {
			$options->update_options( $updated_items['gofer_seo_options'] );
		}

		$options->options['version'] = GOFER_SEO_VERSION;
		$options->update_options();

		return true;
	}

	/**
	 * Get the Updated Items.
	 *
	 * Returns the updated items either as standard object,
	 * or some as Gofer_SEO classes (if any (post_meta|term_meta|user_meta)).
	 *
	 * @since 1.0.0
	 *
	 * @param string $return_type The type of objects/classes to return.
	 *                                OBJECT    = stdClass().
	 *                                GOFER_SEO = Gofer_SEO_* classes.
	 * @return array|bool The updated items. False if failure.
	 */
	public function get_updates( $return_type = 'OBJECT' ) {
		if ( ! $this->needs_update ) {
			return false;
		}

		$new_items = $this->old_items;
		$this->load_option_updates();

		// Handle updates.
		foreach ( $this->update_options_classes as $update_options_class ) {
			if ( version_compare( $this->old_version, $update_options_class->version, '<' ) ) {
				// Currently pass all items in case there is a correlation between two or more items.
				$new_items = $update_options_class->update( $new_items );
			}
		}

		if ( isset( $new_items['gofer_seo_options'] ) ) {
			$new_items['gofer_seo_options']['version'] = GOFER_SEO_VERSION;
		}

		$this->updated_items = $new_items;

		return $new_items;
	}

}
