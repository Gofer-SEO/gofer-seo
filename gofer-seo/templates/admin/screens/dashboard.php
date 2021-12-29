<?php
/**
 * Template - Dashboard.
 *
 * Content displayed to the Admin Menu/Submenu Page.
 *
 * @package Gofer SEO
 * @since 1.0.0
 *
 * @var array $object {
 *     Object array that is typically used with `do_meta_boxes()` functionality.
 *
 *     @type array  $input_typesets    The typesets used for displaying inputs/html.
 *     @type array  $meta_box_typesets Used for creating meta boxes and which inputs to include.
 *     @type string $page_slug         Current Submenu slug.
 * }
 */

wp_enqueue_script( 'dashboard' );
add_thickbox();

if ( wp_is_mobile() ) {
	wp_enqueue_script( 'jquery-touch-punch' );
};

$title = __( 'Dashboard', 'gofer-seo' );


$screen      = get_current_screen();
$columns     = absint( $screen->get_columns() );
$columns_css = '';
if ( $columns ) {
	$columns_css = " columns-$columns";
}
?>
<div class="wrap">
	<h1><?php echo esc_html( $title ); ?></h1>
	<div id="dashboard-widgets-wrap">
		<div id="dashboard-widgets" class="metabox-holder<?php echo esc_attr( $columns_css ); ?>">
			<div id="postbox-container-1" class="postbox-container">
				<?php do_meta_boxes( $screen->id, 'gofer_seo_normal', $object ); ?>
			</div>
			<div id="postbox-container-2" class="postbox-container">
				<?php do_meta_boxes( $screen->id, 'gofer_seo_column2', $object ); ?>
			</div>
			<div id="postbox-container-3" class="postbox-container">
				<?php do_meta_boxes( $screen->id, 'gofer_seo_column3', $object ); ?>
			</div>
			<div id="postbox-container-4" class="postbox-container">
				<?php do_meta_boxes( $screen->id, 'gofer_seo_column4', $object ); ?>
			</div>
		</div>
		<div class="clear"></div>
	</div><!-- dashboard-widgets-wrap -->
</div>
<?php

wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
