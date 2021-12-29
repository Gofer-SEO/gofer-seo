<?php
/**
 * Input Handler.
 *
 * Handles which input to display.
 *
 * @package Gofer SEO
 * @since 1.0.0
 *
 * @see templates/admin/inputs/layouts/input-row.php
 * @see templates/admin/inputs/layouts/label-input-row.php
 * @see templates/admin/inputs/layouts/h2-input-column.php
 * @see templates/admin/inputs/types/add-field-list.php
 * @see templates/admin/inputs/types/tabs.php
 *
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
 * @var string $name        The HTML element name. Uses parent wrap as prefix.
 * @var string $id          The HTML element id. Uses parent wrap as prefix.
 * @var mixed  $value       Value(s) used within element(s).
 * @var array  $values      Values of all inputs.
 */

if ( ! defined( 'ABSPATH' ) || empty( $gofer_seo_template ) ) {
	// Direct access not allowed.
	return;
}

$args = array(
	'hook_suffix'   => $hook_suffix,
	'input_typeset' => $input_typeset,
	'type'          => $input_typeset['type'],
	'name'          => $name,
	'id'            => $id,
	'title'         => $input_typeset['title'],
	'attrs'         => $input_typeset['attrs'],
	'esc'           => $input_typeset['esc'],
	'value'         => $value,
	'values'        => $values,
);
?>
<div class="gofer-seo-input-wrap gofer-seo-input-type-<?php echo esc_attr( $input_typeset['type'] ); ?> gofer-seo-input-name-<?php echo esc_attr( $name ); ?>">
	<?php
	// DO NOT refactor with variable paths.
	// Keep file paths for better code tracking.
	switch ( $input_typeset['type'] ) {
		case 'wrap':
			$args['wrap_input_typesets'] = $input_typeset['wrap'];
			gofer_seo_do_template( 'admin/inputs/types/wrap.php', $args );
			break;
		case 'wrap_dynamic':
			$args['dynamic_input_typesets'] = $input_typeset['wrap_dynamic'];
			$args['items']                  = $input_typeset['items'];
			gofer_seo_do_template( 'admin/inputs/types/wrap-dynamic.php', $args );
			break;
		case 'tabs':
			$args['tabs_wrap_input_typesets'] = $input_typeset['wrap'];
			gofer_seo_do_template( 'admin/inputs/types/tabs.php', $args );
			break;
		case 'table-form-table':
			$args['wrap_input_typesets'] = $input_typeset['wrap'];
			gofer_seo_do_template( 'admin/inputs/types/table-form-table.php', $args );
			break;
		case 'add-field-list':
			gofer_seo_do_template( 'admin/inputs/types/add-field-list.php', $args );
			break;
		case 'add-field-robots-txt':
			gofer_seo_do_template( 'admin/inputs/types/add-field-robots-txt.php', $args );
			break;
		case 'radio':
			$args['items'] = $input_typeset['items'];
			gofer_seo_do_template( 'admin/inputs/types/radio.php', $args );
			break;
		case 'checkbox':
			$args['checked'] = ( $value ) ? 'checked' : '';
			gofer_seo_do_template( 'admin/inputs/types/checkbox.php', $args );
			break;
		case 'button-submit':
			gofer_seo_do_template( 'admin/inputs/types/button-submit.php', $args );
			break;
		case 'multi-checkbox':
			$args['items'] = $input_typeset['items'];
			gofer_seo_do_template( 'admin/inputs/types/multi-checkbox.php', $args );
			break;
		case 'select':
			$args['items'] = $input_typeset['items'];
			gofer_seo_do_template( 'admin/inputs/types/select.php', $args );
			break;
		case 'select2-multi-select':
			$args['items'] = $input_typeset['items'];
			gofer_seo_do_template( 'admin/inputs/types/select2-multi-select.php', $args );
			break;
		case 'range':
			gofer_seo_do_template( 'admin/inputs/types/range.php', $args );
			break;
		case 'textarea':
			gofer_seo_do_template( 'admin/inputs/types/textarea.php', $args );
			break;
		case 'image':
		case 'image-media':
			gofer_seo_do_template( 'admin/inputs/types/image-media.php', $args );
			break;
		case 'list-table':
			$args['items'] = $input_typeset['items'];
			gofer_seo_do_template( 'admin/inputs/types/list-table.php', $args );
			break;
		case 'snippet-default':
			gofer_seo_do_template( 'admin/inputs/types/snippet-default.php', $args );
			break;
		case 'html':
			gofer_seo_do_template( 'admin/inputs/types/html.php', $args );
			break;
		case 'html-text':
			gofer_seo_do_template( 'admin/inputs/types/html-text.php', $args );
			break;
		default:
			gofer_seo_do_template( 'admin/inputs/types/default.php', $args );
	}
	?>
</div>
