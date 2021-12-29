<?php
/**
 * Input - Range.
 *
 * Used to display a range slider & number input.
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
 *     @type array  $items           An array of `$key => $label` to use when generating multiple wraps.
 *                                   Required if 'type' is 'wrap_dynamic', 'multi-checkbox', 'radio',
 *                                   'select', & 'multi-select'.
 *     @type array  $conditions      (Optional) The `$conditions_typeset` for listen to an input,
 *                                   and apply an action when condition(s) are met.
 *     @type array  $item_conditions (Optional) The `$conditions_typeset(s)` for listen to an input (or siblings),
 *                                   and apply an action when condition(s) are met.
 *     @type array  $attrs           (Optional) Attributes to add to input element.
 *     @type array  $esc             (Optional) The esc callback functions to use instead of the default esc_* function.
 * }
 * @var string $hook_suffix The global `$hook_suffix` of the current admin page.
 * @var string $name        The HTML element name. Uses parent wrap as prefix.
 * @var string $id          The HTML element id. Uses parent wrap as prefix.
 * @var mixed  $value       Value(s) used within element(s).
 * @var array  $values      Values of all inputs.
 * @var array  $items       An array of `$key => $label` to use when generating multiple wraps.
 */

if ( ! defined( 'ABSPATH' ) || empty( $gofer_seo_template ) ) {
	// Direct access not allowed.
	return;
}

if ( ! empty( $esc ) ) {
	$value = gofer_seo_esc_callbacks( $value, $esc );
} else {
	$value = esc_html( $value );
}
?>
<?php
/*
<input
	type="range"
	name="<?php echo esc_attr( $name ); ?>_range"
	value="<?php echo $value; ?>"
	oninput="this.form.<?php echo esc_attr( $name ); ?>.value=this.value"
	<?php echo gofer_seo_esc_attrs( $attrs ); ?>
>
<input
	type="number"
	name="<?php echo esc_attr( $name ); ?>"
	id="<?php echo esc_attr( $id ); ?>"
	value="<?php echo $value; ?>"
	oninput="this.form.<?php echo esc_attr( $name ) . '_range'; ?>.value=this.value"
	<?php echo gofer_seo_esc_attrs( $attrs ); ?>
>
 */
?>
<input
	type="range"
	name="<?php echo esc_attr( $name ); ?>_range"
	value="<?php echo esc_attr( $value ); ?>"
	onchange="this.nextElementSibling.value=this.value"
	oninput="this.nextElementSibling.value=this.value"
	<?php echo gofer_seo_esc_attrs( $attrs ); ?>
>
<input
	type="number"
	name="<?php echo esc_attr( $name ); ?>"
	id="<?php echo esc_attr( $id ); ?>"
	value="<?php echo esc_attr( $value ); ?>"
	onchange="this.previousElementSibling.value=this.value"
	oninput="this.previousElementSibling.value=this.value"
	<?php echo gofer_seo_esc_attrs( $attrs ); ?>
>

