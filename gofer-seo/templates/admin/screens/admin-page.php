<?php
/**
 * Template Admin Page.
 *
 * Content displayed to the Admin Menu/Submenu Page.
 *
 * @package Gofer SEO
 * @since 1.0.0
 *
 * @see \Gofer_SEO_Screen_Page::display_page()
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
global $post;

// Footer Styles & Scripts.
wp_enqueue_style( 'gofer-seo-screen-page-css' );

wp_enqueue_script( 'postbox' );
wp_enqueue_script( 'gofer-seo-screens-meta-box-js' );

if ( wp_is_mobile() ) {
	wp_enqueue_script( 'jquery-touch-punch' );
}
?>
<div class="wrap gofer-seo-admin-page" >
	<h2><?php esc_html_e( 'Gofer - Settings', 'gofer-seo' ); ?></h2>
	<?php settings_errors(); ?>
	<form id="gofer-seo-settings-form" method="post" action="<?php echo esc_html( admin_url( 'admin.php' ) ); /* echo esc_url( $object['form_action'] ); */ ?>" >
		<input type="hidden" name="action" value="gofer_seo_screens_page_save_<?php echo esc_attr( $object['page_slug'] ); ?>">
		<?php
		wp_nonce_field( 'gofer_seo_screens_page', 'gofer_seo_nonce' );
		// Used to save closed meta boxes and their order.
		wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
		wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
		// Group name from register_settings.
		// settings_fields( 'gofer_seo_settings' );
		?>
		<div id="poststuff" >
			<div id="post-body" class="metabox-holder columns-<?php echo 1 === get_current_screen()->get_columns() ? '1' : '2'; ?>" >
				<?php submit_button( __( 'Save Settings', 'gofer-seo' ), 'primary', 'gofer-seo-button-top' ); ?>
				<div id="post-body-content" >
					<?php do_meta_boxes( $hook_suffix, 'gofer_seo_normal', $object ); ?>
				</div><!-- #post-body-content -->
				<div id="postbox-container-1" class="postbox-container" >
					<?php do_meta_boxes( $hook_suffix, 'gofer_seo_side', $object ); ?>
				</div><!-- #postbox-container-1 -->
				<div id="postbox-container-2" class="postbox-container" >
					<?php do_meta_boxes( $hook_suffix, 'gofer_seo_advanced', $object ); ?>
				</div><!-- #postbox-container-2 -->
				<?php submit_button( __( 'Save Settings', 'gofer-seo' ), 'primary', 'gofer-seo-button-bottom' ); ?>
			</div><!-- #post-body -->
			<br class="clear">
		</div><!-- #poststuff -->
	</form>
</div><!-- .wrap -->
