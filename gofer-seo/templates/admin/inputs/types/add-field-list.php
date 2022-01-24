<?php
/**
 * Input - Add Field - List.
 *
 * Add an item to the list based on a set of inputs.
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
 *     @type array  $items           Required if 'type' is 'wrap_dynamic', 'multi-checkbox', 'radio',
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
 * @var string $title       Title of input typeset.
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
}

wp_enqueue_script( 'gofer-seo-input-type-add-field-list-js' );
?>
<div class="gofer-seo-add-field-list gofer-seo-input-condition-<?php echo esc_attr( $name ); ?>">
	<div class="gofer-seo-row">
		<div class="gofer-seo-col-12">
			<?php foreach ( $input_typeset['wrap_dynamic'] as $wrap_input_key => $wrap_input_typeset ) : ?>
				<div class="gofer-seo-input-condition-<?php echo esc_attr( $name . '-' . $wrap_input_key ); ?>">
					<div class="gofer-seo-row">
						<div class="gofer-seo-label-outer gofer-seo-col-3">
							<label
								for="<?php echo esc_attr( $name . '-' . $wrap_input_key ); ?>"
							><?php echo esc_html( wp_strip_all_tags( $wrap_input_typeset['title'] ) ); ?></label>
						</div>
						<div class="gofer-seo-input-outer gofer-seo-col-9">
							<?php
							$input_value = ( isset( $wrap_input_typeset['attrs']['value'] ) ) ? $wrap_input_typeset['attrs']['value'] : '';
							$args = array(
								'hook_suffix'   => $hook_suffix,
								'input_typeset' => $wrap_input_typeset,
								'name'          => $name . '-' . $wrap_input_key,
								'id'            => $name . '-' . $wrap_input_key,
								'values'        => $values,
								'value'         => $input_value,
							);
							gofer_seo_do_template( 'admin/inputs/input.php', $args );
							?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
			<input type="submit" class="gofer-seo-add-item-list-button button button-secondary" value="Add Item">
		</div>
	</div>
	<div class="gofer-seo-row">
		<table class="gofer-seo-add-field-list-table" style="display: none;">
			<thead>
				<tr>
					<th colspan="1"></th>
					<?php foreach ( $input_typeset['wrap_dynamic'] as $wrap_typeset ) : ?>
						<th colspan="1"><?php echo esc_html( $wrap_typeset['title'] ); ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $value as $k1_index => $v1_item_value ) : ?>
					<tr id="<?php echo esc_attr( $name . '-' . $k1_index ); ?>">
						<td><a class="dashicons dashicons-trash"></a></td>
						<?php foreach ( $input_typeset['wrap_dynamic'] as $v2_wrap_typeset ) : ?>
							<td><?php echo esc_html( $v1_item_value[ $v2_wrap_typeset['slug'] ] ); ?></td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( wp_json_encode( $value ) ); ?>" />
	</div>
</div>
