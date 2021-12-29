<?php
/**
 * Template for Term Editor.
 *
 * Additional content displayed on the Term editor.
 *
 * @package Gofer SEO
 * @since 1.0.0
 *
 * @see \Gofer_SEO_Screen_Edit_Term::display_term_editor()
 * @link https://developer.wordpress.org/reference/functions/do_meta_boxes/
 *
 * @var array $object {
 *     Object array that is typically used with `do_meta_boxes()` functionality.
 *
 *     @type array  $input_typesets    The typesets used for displaying inputs/html.
 *     @type array  $meta_box_typesets Used for creating meta boxes and which inputs to include.
 *     @type string $page_slug         Current Submenu slug.
 * }
 */

if ( ! defined( 'ABSPATH' ) || empty( $gofer_seo_template ) ) {
	// Direct access not allowed.
	die( '-1' );
}

global $hook_suffix;

wp_enqueue_script( 'postbox' );
wp_enqueue_script( 'gofer-seo-screens-meta-box-js' );

if ( wp_is_mobile() ) {
	wp_enqueue_script( 'jquery-touch-punch' );
}

wp_nonce_field( 'gofer_seo_screens_page', 'gofer_seo_nonce' );
// Used to save closed meta boxes and their order.
wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
?>
<div class="metabox-location-normal gofer-seo-term-editor" >
	<div id="poststuff" >
		<div class="postbox-container" >
			<?php do_meta_boxes( get_current_screen(), 'gofer_seo_normal', $object ); ?>
		</div><!-- .postbox-container -->
	</div>
</div>
<div style="clear: both;"></div>
