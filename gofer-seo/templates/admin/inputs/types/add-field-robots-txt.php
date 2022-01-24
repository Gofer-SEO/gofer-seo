<?php
/**
 * Input - Add Field - Robots.txt.
 *
 * Add an values to the robots.txt rules list.
 *
 * TODO Move to sub-folder `mixed|compound|combo`.
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

$tooltips = new Gofer_SEO_Tooltips( $hook_suffix );

wp_enqueue_style( 'gofer-seo-input-type-add-field-robots-txt-css' );
wp_enqueue_script( 'gofer-seo-input-type-add-field-robots-txt-js' );
?>
<div class="gofer-seo-add-field-robots-txt gofer-seo-input-condition-<?php echo esc_attr( $name ); ?>">
	<div class="gofer-seo-row">
		<div class="gofer-seo-col-12 gofer-seo-add-field-robots-txt-user-agents">
			<!-- User-Agents -->
			<div class="gofer-seo-row">
				<?php
				$user_agent_input_typeset = $input_typeset['wrap']['user_agents']['wrap_dynamic']['user_agent'];
				$rule_type_input_typeset  = $input_typeset['wrap']['user_agents']['wrap_dynamic']['rule_type'];
				$rule_value_input_typeset = $input_typeset['wrap']['user_agents']['wrap_dynamic']['rule_value'];
				?>
				<div class="gofer-seo-col-6 ">
					<div class="gofer-seo-row">
						<div class="gofer-seo-col-4 ">
							<label for="<?php echo esc_attr( $name ) . '-user_agents-user_agent'; ?>"
							><?php echo esc_html( $user_agent_input_typeset['title'] ); ?></label>
							<a tabindex="0" class="gofer-seo-tooltip" style="cursor: help;" title="<?php echo esc_html( $tooltips->get_tooltip_html( $id . '-user_agents-user_agent' ) ); ?>"></a>
						</div>
						<div class="gofer-seo-col-4 ">
							<label for="<?php echo esc_attr( $name ) . '-user_agents-rule_type'; ?>"
							><?php echo esc_html( $rule_type_input_typeset['title'] ); ?></label>
							<a tabindex="0" class="gofer-seo-tooltip" style="cursor: help;" title="<?php echo esc_html( $tooltips->get_tooltip_html( $id . '-user_agents-rule_type' ) ); ?>"></a>
						</div>
						<div class="gofer-seo-col-4 ">
							<label for="<?php echo esc_attr( $name ) . '-user_agents-rule_value'; ?>"
							><?php echo esc_html( $rule_value_input_typeset['title'] ); ?></label>
							<a tabindex="0" class="gofer-seo-tooltip" style="cursor: help;" title="<?php echo esc_html( $tooltips->get_tooltip_html( $id . '-user_agents-rule_value' ) ); ?>"></a>
						</div>
					</div>
				</div>
			</div>
			<div class="gofer-seo-row">
				<div class="gofer-seo-col-6 ">
					<div class="gofer-seo-row">
						<div class="gofer-seo-col-4 ">
							<?php
							$input_value = ( isset( $user_agent_input_typeset['attrs']['value'] ) ) ? $user_agent_input_typeset['attrs']['value'] : '';
							$args = array(
								'hook_suffix'   => $hook_suffix,
								'input_typeset' => $user_agent_input_typeset,
								'name'          => $name . '-user_agents-user_agent',
								'id'            => $name . '-user_agents-user_agent',
								'values'        => $values,
								'value'         => $input_value,
							);
							gofer_seo_do_template( 'admin/inputs/input.php', $args );
							?>
						</div>
						<div class="gofer-seo-col-4 ">
							<?php
							$rule_type_input_typeset['attrs']['style'] = 'width: 100%;';
							$input_value = ( isset( $rule_type_input_typeset['attrs']['value'] ) ) ? $rule_type_input_typeset['attrs']['value'] : '';
							$args = array(
								'hook_suffix'   => $hook_suffix,
								'input_typeset' => $rule_type_input_typeset,
								'name'          => $name . '-user_agents-rule_type',
								'id'            => $name . '-user_agents-rule_type',
								'values'        => $values,
								'value'         => $input_value,
							);
							gofer_seo_do_template( 'admin/inputs/input.php', $args );
							?>
						</div>
						<div class="gofer-seo-col-4 ">
							<?php
							$input_value = ( isset( $rule_value_input_typeset['attrs']['value'] ) ) ? $rule_value_input_typeset['attrs']['value'] : '';
							$args = array(
								'hook_suffix'   => $hook_suffix,
								'input_typeset' => $rule_value_input_typeset,
								'name'          => $name . '-user_agents-rule_value',
								'id'            => $name . '-user_agents-rule_value',
								'values'        => $values,
								'value'         => $input_value,
							);
							gofer_seo_do_template( 'admin/inputs/input.php', $args );
							?>
						</div>
					</div>

				</div>
				<input type="submit" class="gofer-seo-add-item-robots-txt-user-agent-button button button-secondary" value="<?php echo esc_html__( 'Add Item', 'gofer-seo' ); ?>">
			</div>
			<div class="gofer-seo-row">
				<table class="gofer-seo-add-field-robots-txt-table gofer-seo-add-field-robots_txt_rules-user_agents-table">
					<thead>
					<tr>
						<th style="width: auto;"></th>
						<th style="width: 25%;"><?php echo esc_html( $user_agent_input_typeset['title'] ); ?></th>
						<th style="width: 10%;"><?php echo esc_html( $rule_type_input_typeset['title'] ); ?></th>
						<th style="width: 65%;"><?php echo esc_html( $rule_value_input_typeset['title'] ); ?></th>
					</tr>
					</thead>
					<tbody>
						<?php foreach ( $value['user_agents'] as $v1_agent_values ) : ?>

							<?php if ( ! empty( $v1_agent_values['crawl_delay'] ) ) : ?>
								<!-- Crawl-Delay -->
								<tr>
									<td></td>
									<td><?php echo esc_html( $v1_agent_values['user_agent'] ); ?></td>
									<td><?php echo esc_html( $rule_type_input_typeset['items']['crawl_delay'] ); ?></td>
									<td><?php echo esc_html( $v1_agent_values['crawl_delay'] ); ?></td>
								</tr>
							<?php endif; ?>

							<?php foreach ( $v1_agent_values['path_rules'] as $k2_path => $v2_path_rule ) : ?>
								<!-- Path Rules (Allow/Disallow) -->
								<tr>
									<td></td>
									<td><?php echo esc_html( $v1_agent_values['user_agent'] ); ?></td>
									<td><?php echo esc_html( $rule_type_input_typeset['items'][ $v2_path_rule ] ); ?></td>
									<td><?php echo esc_html( $k2_path ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="gofer-seo-row">
		<div class="gofer-seo-col-12 gofer-seo-add-field-robots-txt-sitemaps">
			<!-- Sitemaps -->
			<div class="gofer-seo-row">
				<?php
				$sitemaps_input_typeset = $input_typeset['wrap']['sitemaps'];
				$sitemap_input_typeset  = $input_typeset['wrap']['sitemaps']['wrap_dynamic']['sitemap'];
				?>
				<div class="gofer-seo-col-6">
					<label for="<?php echo esc_attr( $name ) . '-sitemaps-sitemap'; ?>"
					><?php echo esc_html( $sitemap_input_typeset['title'] ); ?></label>
					<a tabindex="0" class="gofer-seo-tooltip" style="cursor: help;" title="<?php echo esc_html( $tooltips->get_tooltip_html( $id . '-sitemaps-sitemap' ) ); ?>"></a>
				</div>
			</div>
			<div class="gofer-seo-row">
				<div class="gofer-seo-col-6">
					<?php
					$input_value = ( isset( $sitemap_input_typeset['attrs']['value'] ) ) ? $sitemap_input_typeset['attrs']['value'] : '';
					$args        = array(
						'hook_suffix'   => $hook_suffix,
						'input_typeset' => $sitemap_input_typeset,
						'name'          => $name . '-sitemaps-sitemap',
						'id'            => $name . '-sitemaps-sitemap',
						'values'        => $values,
						'value'         => $input_value,
					);
					gofer_seo_do_template( 'admin/inputs/input.php', $args );
					?>
				</div>
				<input type="submit" class="gofer-seo-add-item-robots-txt-sitemap-button button button-secondary" value="<?php echo esc_html__( 'Add Item', 'gofer-seo' ); ?>">
			</div>
			<div class="gofer-seo-row">
				<table class="gofer-seo-add-field-robots-txt-table gofer-seo-add-field-robots_txt_rules-sitemaps-table">
					<thead>
						<tr>
							<th style="width: auto;"></th>
							<th style="width: 100%;"><?php echo esc_html( $sitemap_input_typeset['title'] ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ( $value['sitemaps'] as $v1_sitemap ) : ?>
						<tr>
							<td></td>
							<td><?php echo esc_html( $v1_sitemap ); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="gofer-seo-row">
		<div class="gofer-seo-col-12">
			<!-- Preview -->
			<?php
			$textarea_typeset = array(
				'title'  => '',
				'type'   => 'textarea',
				'slug'   => $name . '-preview',
				'layout' => 'label-input-row',
				'attrs'  => array(
					'readonly' => 'readonly',
					'rows'     => 18,
					'style'    => 'color: #444444; background: #F6F6F6;',
				),
				'esc'    => array(),
			);
			$args = array(
				'hook_suffix'   => $hook_suffix,
				'input_typeset' => $textarea_typeset,
				'name'          => $name . '-preview',
				'id'            => $name . '-preview',
				'values'        => $values,
				'value'         => '',
			);
			gofer_seo_do_template( 'admin/inputs/input.php', $args );
			?>
		</div>
	</div>
	<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( wp_json_encode( $value ) ); ?>" />
</div>
