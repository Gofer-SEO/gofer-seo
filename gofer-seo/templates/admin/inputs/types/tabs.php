<?php
/**
 * Input - Tabs.
 *
 * Displays tab(s) that wrap inputs.
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
 * @var array  $tabs_wrap_input_typesets Nested Typeset. Same as Input Typeset.
 */

if ( ! defined( 'ABSPATH' ) || empty( $gofer_seo_template ) ) {
	// Direct access not allowed.
	return;
}

if ( ! empty( $esc ) ) {
	$value = gofer_seo_esc_callbacks( $value, $esc );
} else {
//	$value = esc_attr( $value );
}

$tooltips = new Gofer_SEO_Tooltips( $hook_suffix );

wp_enqueue_script( 'gofer-seo-tooltips-js' );
wp_enqueue_style( 'gofer-seo-tooltips-css' );
?>
<div class="gofer-seo-tabs" >
	<nav>
		<div class="nav nav-tabs" id="nav-tab" role="tablist">
			<?php
			$selected = true;
			?>
			<?php foreach ( $tabs_wrap_input_typesets as $tab_wrap_input_key => $tab_wrap_input_typeset ) : ?>
				<button
					class="nav-link<?php echo $selected ? ' active' : ''; ?> gofer-seo-input-condition-<?php echo esc_attr( $name . '-' . $tab_wrap_input_key ); ?>"
					id="nav-<?php echo esc_attr( $name . '-' . $tab_wrap_input_key ); ?>-tab"
					data-bs-toggle="tab"
					data-bs-target="#nav-<?php echo esc_attr( $name . '-' . $tab_wrap_input_key ); ?>"
					type="button"
					role="tab"
					aria-controls="nav-<?php echo esc_attr( $name . '-' . $tab_wrap_input_key ); ?>"
					aria-selected="<?php echo $selected ? ' true' : 'false'; ?>"
				>
					<?php echo esc_html( $tab_wrap_input_typeset['title'] ); ?>
				</button>
				<?php
				$selected = false;
				?>
			<?php endforeach; ?>
		</div>
	</nav>
	<div class="tab-content" id="nav-tabContent">
		<?php
		$selected = true;
		?>
		<?php foreach ( $tabs_wrap_input_typesets as $tab_wrap_input_key => $tab_wrap_input_typeset ) : ?>
			<div
					class="tab-pane fade<?php echo $selected ? ' show active' : ''; ?> gofer-seo-input-condition-<?php echo esc_attr( $name . '-' . $tab_wrap_input_key ); ?>"
					id="nav-<?php echo esc_attr( $name . '-' . $tab_wrap_input_key ); ?>"
					role="tabpanel"
					aria-labelledby="nav-<?php echo esc_attr( $name . '-' . $tab_wrap_input_key ); ?>-tab"
			>
				<?php foreach ( $tab_wrap_input_typeset['wrap'] as $wrap_input_key => $wrap_input_typeset ) : ?>
					<div class="gofer-seo-input-condition-<?php echo esc_attr( $name . '-' . $tab_wrap_input_key . '-' . $wrap_input_key ); ?>">
						<div class="gofer-seo-row">
							<div class="gofer-seo-label-outer gofer-seo-col-3">
								<a tabindex="0" class="gofer-seo-tooltip" style="cursor: help;" title="<?php echo esc_html( $tooltips->get_tooltip_html( $name . '-' . $tab_wrap_input_key . '-' . $wrap_input_key ) ); ?>"></a>
								<label
										for="<?php echo esc_attr( $name . '-' . $tab_wrap_input_key . '-' . $wrap_input_key ); ?>"
								><?php echo esc_html( wp_strip_all_tags( $wrap_input_typeset['title'] ) ); ?></label>
							</div>
							<div class="gofer-seo-input-outer gofer-seo-col-9">
								<?php
								$args = array(
									'hook_suffix'   => $hook_suffix,
									'input_typeset' => $wrap_input_typeset,
									'name'          => $name . '-' . $tab_wrap_input_key . '-' . $wrap_input_key,
									'id'            => $name . '-' . $tab_wrap_input_key . '-' . $wrap_input_key,
									'values'        => $values,
									'value'         => $value[ $tab_wrap_input_key ][ $wrap_input_key ],
								);
								gofer_seo_do_template( 'admin/inputs/input.php', $args );
								?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php
			$selected = false;
			?>
		<?php endforeach; ?>
	</div>
</div>
