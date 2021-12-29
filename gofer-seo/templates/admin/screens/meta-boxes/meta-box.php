<?php
/**
 * Admin Page - Meta Box.
 *
 * Meta Box content/container displayed to within a registered Meta Box.
 *
 * @package Gofer SEO
 * @since 1.0.0
 *
 * @see \Gofer_SEO_Typesetter_Admin::display_meta_box()
 *
 * @var array $object {
 *     Object array that is typically used with `do_meta_boxes()` functionality.
 *
 *     @type array  $input_typesets    The typesets used for displaying inputs/html.
 *     @type array  $meta_box_typesets Used for creating meta boxes and which inputs to include.
 *     @type string $page_slug         Current Submenu slug.
 * }
 * @var array $box {
 *     Array used with `add_meta_box()` functionality.
 *
 *     @type string $hook_suffix             The global `$hook_suffix` of the current admin page.
 *     @type array  $meta_box_inputs         The input slugs used in the meta box.
 *     @type array  $meta_box_input_typesets Input typesets used to generate HTML.
 *     @type array  $values                  Values of inputs.
 * }
 */

if ( ! defined( 'ABSPATH' ) || empty( $gofer_seo_template ) ) {
	// Direct access not allowed.
	return;
}

if ( null === $object || null === $box ) {
	// TODO Log error - Missing meta-box template params.
	return;
}

$hook_suffix     = isset( $box['args']['hook_suffix'] ) ? (string) $box['args']['hook_suffix'] : '';
$meta_box_inputs = isset( $box['args']['meta_box_inputs'] ) ? (array) $box['args']['meta_box_inputs'] : array();
$input_typesets  = isset( $box['args']['meta_box_input_typesets'] ) ? (array) $box['args']['meta_box_input_typesets'] : array();
$values          = isset( $box['args']['values'] ) ? (array) $box['args']['values'] : array();

wp_enqueue_style( 'gofer-seo-bootstrap-css' );
wp_enqueue_style( 'gofer-seo-input-layouts-css' );
wp_enqueue_style( 'gofer-seo-input-types-css' );

wp_enqueue_script( 'gofer-seo-bootstrap-js' );
wp_enqueue_script( 'gofer-seo-inputs-input-conditions-js' );
?>
<div class="gofer-seo-meta-box gofer-seo-container">
	<?php wp_nonce_field( 'gofer_seo_screens_page', 'gofer_seo_nonce' ); ?>
	<?php foreach ( $meta_box_inputs as $meta_box_input ) :
		$input_typeset = $input_typesets[ $meta_box_input ];
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
