<?php
/**
 * Input - Select - Multi-Select.
 *
 * Displays Select2 input with multi-select enabled.
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
 *                                   Required if 'type' is 'wrap_dynamic', 'list-table', 'multi-checkbox', 'radio',
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

$class = 'gofer-seo-select2-multi-select';
if ( ! empty( $attrs['class'] ) ) {
	$class .= ' ' . $attrs['class'];
	unset( $attrs['class'] );
}

wp_enqueue_style( 'gofer-seo-select2-css' );
wp_enqueue_script( 'gofer-seo-input-type-select2-multi-select-js' );
?>
<select
	name="<?php echo esc_attr( $name ); ?>[]"
	id="<?php echo esc_attr( $name ); ?>"
	class="<?php echo esc_attr( $class ); ?>"
	style="width: 100%; height: auto;"
	multiple="multiple"
	<?php echo gofer_seo_esc_attrs( $attrs ); ?>
>
	<?php foreach ( $items as $k1_item_slug => $v1_item ) : ?>
		<?php if ( is_array( $v1_item ) ) :
			$optgroup_label = ( isset( $v1_item['optgroup_label'] ) ) ? $v1_item['optgroup_label'] : ucwords( preg_replace( '/(-|_)+/', ' ', $k1_item_slug ) );
			?>
			<optgroup label="<?php echo esc_attr( $optgroup_label ); ?>">
				<?php foreach ( $v1_item as $k2_item_slug => $v2_item ) : ?>
					<?php
					if ( 'optgroup_label' === $k2_item_slug ) {
						continue;
					}
					$selected = ( in_array( $k2_item_slug, $value, true ) ) ? 'selected' : '';
					?>
					<option value="<?php echo esc_attr( $k2_item_slug ); ?>" <?php echo esc_html( $selected ); ?>><?php echo esc_html( wp_strip_all_tags( $v2_item ) ); ?></option>
				<?php endforeach; ?>
			</optgroup>
		<?php else : ?>
			<?php
			$selected = ( in_array( $k1_item_slug, $value, true ) ) ? 'selected' : '';
			?>
			<option value="<?php echo esc_attr( $k1_item_slug ); ?>" <?php echo esc_html( $selected ); ?>><?php echo esc_html( wp_strip_all_tags( $v1_item ) ); ?></option>
		<?php endif; ?>
	<?php endforeach; ?>
</select>
