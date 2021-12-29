<?php
/**
 * Gofer SEO Update - Version 0.0.0 (Example/Placeholder)
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

/**
 * Class Gofer_SEO_Update_0_0_0
 */
class Gofer_SEO_Update_Options_0_0_0 extends Gofer_SEO_Update_Options {

	/**
	 * The version the update occurs at.
	 *
	 * REQUIRED.
	 *
	 * @since 1.0.0
	 *
	 * @return string Version number.
	 */
	public function get_version_number() {
		return '0.0.0';
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
	public function update( $old_items ) {
		// TODO: Implement do_update() method.
		$updated_items = $old_items;

		return $updated_items;
	}

}
