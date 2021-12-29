<?php
/**
 * Gofer SEO Update - Base class for other updates.
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

/**
 * Class Gofer_SEO_Update
 *
 * @since 1.0.0
 */
abstract class Gofer_SEO_Update_Options {

	/**
	 * Version.
	 *
	 * @since 1.0.0
	 *
	 * @var string $version The version number the update occurs at.
	 */
	public $version;

	/**
	 * Priority.
	 *
	 * Updates with the same version with priorities set will execute closest to 1 first.
	 *
	 * @since 1.0.0
	 *
	 * @var int $priority The priority between two or more updates.
	 */
	public $priority;

	/**
	 * Gofer_SEO_Update constructor.
	 */
	public function __construct() {
		$this->version  = $this->get_version_number();
		$this->priority = $this->get_priority();
	}

	/**
	 * The version the update occurs at.
	 *
	 * REQUIRED.
	 *
	 * @since 1.0.0
	 *
	 * @return string Version number.
	 */
	abstract public function get_version_number();

	/**
	 * The priority to set the update at.
	 *
	 * For situations an update needs to go before or after a separate update.
	 *
	 * @return int The priority.
	 */
	public function get_priority() {
		return 10;
	}

	/**
	 * Update the options.
	 *
	 * REQUIRED.
	 *
	 * @since 1.0.0
	 *
	 * @param array $old_items An array of items that contain the options array.
	 * @return array The updated items.
	 */
	abstract public function update( $old_items );

}
