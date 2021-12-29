<?php
/**
 * Input - Table Form-Table.
 *
 * WP table design used on the admin user editor page.
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

if ( ! empty( $esc ) ) {
	$value = gofer_seo_esc_callbacks( $value, $esc );
}
?>
<table class="gofer-seo-list-table gofer-seo-input-condition-<?php echo esc_attr( $name ); ?> wp-list-table widefat striped">
	<thead>
		<tr>
			<td>*</td>
			<?php foreach ( $input_typeset['wrap_dynamic'] as $wrap_input_key => $wrap_input_typeset ) : ?>
				<th scope="col" class="gofer-seo-input-condition-<?php echo esc_attr( $name . '-th-' . $wrap_input_key ); ?> manage-column column-<?php echo esc_attr( $name . '-' . $wrap_input_key ); ?>"><?php echo esc_html( wp_strip_all_tags( $wrap_input_typeset['title'] ) ); ?></th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $items as $item_slug => $item ) : ?>
			<?php if ( 'th' === $item_slug ) continue; ?>
			<tr class="gofer-seo-input-condition-<?php echo esc_attr( $name . '-' . $item_slug ); ?>">
				<th scope="row">*</th>
				<?php foreach ( $input_typeset['wrap_dynamic'] as $wrap_input_key => $wrap_input_typeset ) : ?>
					<td class="gofer-seo-input-condition-<?php echo esc_attr( $name . '-' . $item_slug . '-' . $wrap_input_key ); ?> column-<?php echo esc_attr( $name . '-' . $wrap_input_key ); ?>">
						<?php
						$args = array(
							'hook_suffix'   => $hook_suffix,
							'input_typeset' => $wrap_input_typeset,
							'name'          => $name . '-' . $item_slug . '-' . $wrap_input_key,
							'id'            => $name . '-' . $item_slug . '-' . $wrap_input_key,
							'value'         => $value[ $item_slug ][ $wrap_input_key ],
							'values'        => $values,
						);
						gofer_seo_do_template( 'admin/inputs/layouts/input-row.php', $args );
						?>
					</td>
				<?php endforeach; ?>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
