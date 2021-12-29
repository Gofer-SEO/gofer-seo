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
 *     @type array  $wrap            Nested Typeset. Same as Input Typeset.
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
 * @var array  $wrap_input_typesets Nested Typeset. Same as Input Typeset.
 */

if ( ! defined( 'ABSPATH' ) || empty( $gofer_seo_template ) ) {
	// Direct access not allowed.
	return;
}
?>
<table class="form-table" role="presentation">
	<tbody>
		<?php foreach ( $input_typeset['wrap'] as $wrap_input_key => $wrap_input_typeset ) : ?>
			<tr>
				<th>
					<label for="<?php echo esc_attr( $wrap_input_key ); ?>"><?php echo esc_html( $wrap_input_typeset['title'] ); ?></label>
				</th>
				<td>
					<?php
					$args = array(
						'hook_suffix'   => $hook_suffix,
						'input_typeset' => $wrap_input_typeset,
						'name'          => $name . '-' . $wrap_input_key,
						'id'            => $name . '-' . $wrap_input_key,
						'value'         => $value[ $wrap_input_key ],
						'values'        => $values,
					);
					//		gofer_seo_do_template( 'admin/inputs/layouts/label-input-row.php', $args );
					//gofer_seo_do_template( 'admin/inputs/layouts/' . sanitize_file_name( $wrap_input_typeset['layout'] ) . '.php', $args );
					gofer_seo_do_template( 'admin/inputs/layouts/input-row.php', $args );
					?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
