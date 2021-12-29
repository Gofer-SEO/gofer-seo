<?php
/**
 * Input Layout - Input Row.
 *
 * Full width row with no label.
 *
 * @package Gofer SEO
 * @since 1.0.0
 *
 * @see /templates/admin/screens/meta-box/meta-box.php
 * @see /templates/admin/inputs/types/table-form-table.php
 *
 * @var array  $input_typeset {
 *     The input's typeset used for rendering HTML content.
 *
 *     @type string $slug            (Optional) The input slug for this array variable. Automatically set.
 *     @type string $title           The title/label of the input.
 *     @type string $layout          The type of layout to display.
 *     @type string $type            The type of input to display.
 *     @type array  $wrap            Nested Typeset. Same as Input Typeset.
 *     @type array  $wrap_dynamic    Nested Typeset for dynamic variables. Same as Input Typeset.
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
 */

if ( ! defined( 'ABSPATH' ) || empty( $gofer_seo_template ) ) {
	// Direct access not allowed.
	return;
}
?>
<div class="gofer-seo-input-condition-<?php echo esc_attr( $name ); ?>">
	<div class="gofer-seo-row">
		<div class="gofer-seo-input-outer gofer-seo-col-12">
			<?php
			$args = array(
				'hook_suffix'   => $hook_suffix,
				'input_typeset' => $input_typeset,
				'name'          => $name,
				'id'            => $id,
				'value'         => $value,
				'values'        => $values,
			);
			gofer_seo_do_template( 'admin/inputs/input.php', $args );
			?>
		</div>
	</div>
</div>
