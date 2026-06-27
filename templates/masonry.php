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
	<div class="at-masonry">
		<?php
		foreach ( $testimonials as $item ) {
			echo '<div class="at-masonry__item">';
			include $item_template;
			echo '</div>';
		}
		?>
	</div>
</div>
