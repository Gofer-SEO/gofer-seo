<?php
/**
 * Template for User Editor.
 *
 * Additional content displayed on the User editor.
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
 * @var array $input_typesets The typesets used for displaying inputs/html.
 * @var array $values         Values of all inputs.
 */

if ( ! defined( 'ABSPATH' ) || empty( $gofer_seo_template ) ) {
	// Direct access not allowed.
	die( '-1' );
}

global $hook_suffix;
wp_nonce_field( 'gofer_seo_screens_page', 'gofer_seo_nonce' );
?>
<div class="gofer-seo-user-editor gofer-seo-container">
	<?php foreach ( $input_typesets as $input_typeset ) :
		$args = array(
			'hook_suffix'   => $hook_suffix,
			'input_typeset' => $input_typeset,
			'name'          => $input_typeset['slug'],
			'id'            => $input_typeset['slug'],
			'value'         => $values[ $input_typeset['slug'] ],
			'values'        => $values,
		);
		gofer_seo_do_template( 'admin/inputs/layouts/' . sanitize_file_name( $input_typeset['layout'] ) . '.php', $args );
		?>
	<?php endforeach; ?>
</div>
