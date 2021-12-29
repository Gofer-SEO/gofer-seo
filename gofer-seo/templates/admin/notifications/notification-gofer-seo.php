<?php
/**
 * Gofer SEO Screen Notice Template.
 *
 * @since 1.0.0
 *
 * @see Gofer_SEO_Notifications::display_notice_gofer_seo();
 * @uses $notice in Gofer_SEO_Notifications::notices
 * @package Gofer SEO
 */

// $notice       = $this->get_notice[ $a_notice_slug ];
$notice_class = 'notice-info';
if ( isset( $notice['class'] ) && ! empty( $notice['class'] ) ) {
	$notice_class = $notice['class'];
}

//add_filter( 'safe_style_css', 'gofer_seo_filter_styles' );

$dismissible = ! isset( $notice['dismissible'] ) || $notice['dismissible'] ? ' is-dismissible' : '';

?>
<div class="notice <?php echo esc_attr( $notice_class ); ?><?php echo esc_html( $dismissible ); ?> gofer-seo-notice-container gofer-seo-notice-<?php echo esc_attr( $notice['slug'] ); ?>">
	<?php if ( ! empty( $notice['html'] ) ) : ?>
		<?php
		echo wp_kses(
			$notice['html'],
			array(
				'br'     => array(),
				'div'    => array(
					'class' => true,
					'style' => true,
				),
				'p'      => array(),
				'strong' => array(),
				'a'      => array(
					'href'   => true,
					'class'  => true,
					'data-*' => true,
					'target' => true,
					'rel'    => true,
				),
				'style'  => array(),
				'script' => array(
					'type' => true,
				),
				'ul'     => array(
					'class' => true,
				),
				'li'     => array(),
			)
		);
		?>
	<?php else : ?>
		<p><?php echo esc_html( $notice['message'] ); ?></p>
	<?php endif; ?>
	<p class="gofer-seo-action-buttons">
		<?php foreach ( $notice['action_options'] as $key => $action_option ) : ?>
			<?php
			$link   = $action_option['link'];
			$id     = 'gofer-seo-notice-delay-' . $notice['slug'] . '-' . $key;
			$class  = '';
			$class .= 'gofer-seo-delay-' . $key;
			$class .= ' ' . $action_option['class'];
			?>
			<a 
				href="<?php echo esc_url( $link ); ?>" 
				id="<?php echo esc_attr( $id ); ?>" 
				class="gofer-seo-notice-delay <?php echo esc_attr( $class ); ?>"
				<?php
				if ( $action_option['new_tab'] ) {
					echo 'target="_blank" rel="noopener"';}
				?>
				>
				<?php echo esc_textarea( $action_option['text'] ); ?>
			</a>
		<?php endforeach; ?>
	</p>
</div>
