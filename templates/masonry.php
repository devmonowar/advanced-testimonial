<?php
/**
 * Masonry layout — CSS columns, no JavaScript required.
 *
 * Override: copy to your theme as advanced-testimonial/masonry.php
 *
 * @var array  $testimonials  Prepared testimonial items.
 * @var array  $atts          Display attributes.
 * @var bool   $schema        Schema flag.
 * @var string $item_template Path to the item partial.
 * @var string $instance      Unique instance id.
 * @var string $wrapper_class Outer CSS classes.
 * @var string $wrapper_style Inline CSS custom properties.
 *
 * @package AdvancedTestimonial
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are function-scoped via TemplateLoader::render().
?>
<div class="<?php echo esc_attr( $wrapper_class ); ?>" style="<?php echo esc_attr( $wrapper_style ); ?>" id="<?php echo esc_attr( $instance ); ?>">
	<?php if ( '' !== $atts['title'] ) : ?>
		<h3 class="at-title"><?php echo esc_html( $atts['title'] ); ?></h3>
	<?php endif; ?>
	<?php echo $filter_bar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built and escaped in Renderer::filter_bar_html(). ?>
	<div class="at-masonry">
		<?php
		$at_lm_initial = ! empty( $atts['load_more'] ) ? (int) $atts['_lm_initial'] : 0;
		foreach ( $testimonials as $at_lm_index => $item ) {
			$at_lm_cls = ( $at_lm_initial > 0 && $at_lm_index >= $at_lm_initial ) ? ' at-lm-collapsed' : '';
			echo '<div class="at-masonry__item' . esc_attr( $at_lm_cls ) . '">';
			include $item_template;
			echo '</div>';
		}
		?>
	</div>
	<?php echo $load_more_bar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built and escaped in Renderer::load_more_html(). ?>
</div>
