<?php
/**
 * Input - Image.
 *
 * Used for adding an image.
 *
 * @package Gofer SEO
 * @since 1.0.0
 *
 * @var array  $input_typeset {
 *     The input's typeset used for rendering HTML content.
 *
 *     @type string $slug            (Optional) The input slug for this array variable. Automatically set.
 *     @type string $title           The title/label of the input.
 *     @type string $layout          The type of layout to display.
 *     @type string $type            The type of input to display.
 *     @type array  $conditions      (Optional) The `$conditions_typeset` for listen to an input,
 *                                   and apply an action when condition(s) are met.
 *     @type array  $attrs           (Optional) Attributes to add to input element.
 *     @type array  $esc             (Optional) The esc callback functions to use instead of the default esc_* function.
 * }
 * @var string $hook_suffix The global `$hook_suffix` of the current admin page.
 * @var string $type        The typeset type of element input(s).
 * @var string $name        The HTML element name. Uses parent wrap as prefix.
 * @var string $id          The HTML element id. Uses parent wrap as prefix.
 * @var string $title       Title of input/label typeset.
 * @var array  $attrs       (Optional) Attributes to add to input element.
 * @var array  $esc         (Optional) The esc callback functions to use instead of the default esc_* function.
 * @var mixed  $value       Value(s) used within element(s).
 * @var array  $values      Values of all inputs.
 */

if ( ! defined( 'ABSPATH' ) || empty( $gofer_seo_template ) ) {
	// Direct access not allowed.
	return;
}

if ( ! empty( $esc ) ) {
	$value = gofer_seo_esc_callbacks( $value, $esc );
} elseif ( is_numeric( $value ) ) {
	$value = (int) $value;
} else {
	$value = esc_url( $value );
}

wp_enqueue_media();
wp_enqueue_script( 'gofer-seo-input-type-image-media-js' );
?>
<input
		type="button"
		class="gofer-seo-image-media-button button button-secondary"
		value="<?php esc_attr_e( 'Select an image', 'gofer-seo' ); ?>"
>
<input
		type="text"
		name="<?php echo esc_attr( $name ); ?>"
		id="<?php echo esc_attr( $id ); ?>"
		class="gofer-seo-image-media-text"
		value="<?php echo esc_html( $value ); ?>"
		<?php echo gofer_seo_esc_attrs( $attrs ); ?>
>
<?php
/*
 * Concept to add sample image.
 *
<div class="gofer-seo-row">
	<div class="gofer-seo-col-12">
		<input
			type="text"
			name="<?php echo esc_attr( $name ); ?>"
			id="<?php echo esc_attr( $id ); ?>"
			class="gofer-seo-image-media-text"
			value="<?php echo esc_url( $value ); ?>"
		>
	</div>
</div>
<div class="gofer-seo-row" style="padding-right: 4%;">
	<div class="gofer-seo-col-4">
		<input
			type="button"
			class="gofer-seo-image-media-button button-primary"
			value="<?php _e( 'Select an image', 'gofer-seo' ); ?>"
		>
	</div>
	<div class="gofer-seo-col-8">
		<img class="gofer-seo-image-media-img" src="">
	</div>
</div>
*/
?>



