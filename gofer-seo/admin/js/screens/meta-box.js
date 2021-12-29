/**
 * Admin Meta Box JavaScript
 *
 * @summary  Admin JavaScript / jQuery for UI design and conditional logic.
 *
 * @package  Gofer SEO
 * @since    1.0.0
 * @requires jQuery
 */

/* globals postboxes, pagenow */
( function($) {
	/**
	 * WordPress MetaBox Workaround (toggle).
	 *
	 * @since 1.0.0
	 */
	$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
	postboxes.add_postbox_toggles( pagenow ); // jshint ignore:line
}(jQuery));
