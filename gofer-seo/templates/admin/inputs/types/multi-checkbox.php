<?php
/**
 * Input - Multi-Checkbox.
 *
 * Used for selecting multiple checkboxes.
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
 *     @type array  $wrap            Nested Typeset. Same as Input Typeset.
 *     @type array  $wrap_dynamic    Nested Typeset for dynamic variables. Same as Input Typeset.
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
 * @var string $type        The typeset type of element input(s).
 * @var string $name        The HTML element name. Uses parent wrap as prefix.
 * @var string $id          The HTML element id. Uses parent wrap as prefix.
 * @var string $title       Title of input/label typeset.
 * @var array  $attrs       (Optional) Attributes to add to input element.
 * @var array  $esc         (Optional) The esc callback functions to use instead of the default esc_* function.
 * @var mixed  $value       Value(s) used within element(s).
 * @var array  $values      Values of all inputs.
 * @var array  $items       An array of `$key => $label` to use when generating multiple wraps.
 */

if ( ! defined( 'ABSPATH' ) || empty( $gofer_seo_template ) ) {
	// Direct access not allowed.
	return;
}

foreach ( $items as $item_slug => $item_label ) : ?>
	<div class="gofer-seo-multi-checkbox-item">
		<?php
		$args = array(
			'hook_suffix'   => $hook_suffix,
			'input_typeset' => $input_typeset,
			'type'          => 'checkbox',
			'name'          => $name . '[]',
			'id'            => $name . '-'. $item_slug,
			'esc'           => $esc,
			'attrs'         => $attrs,
			'value'         => $item_slug,
			'values'        => $values,
			'checked'       => ( true === $value[ $item_slug ] ) ? 'checked' : '',
		);
		gofer_seo_do_template( 'admin/inputs/types/checkbox.php', $args );
		?>
		<label for="<?php echo esc_attr( $name . '-'. $item_slug ); ?>" >
			<?php echo esc_html( $item_label ); ?>
		</label>
	</div>
<?php
endforeach;
