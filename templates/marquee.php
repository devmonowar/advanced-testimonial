<?php
/**
 * Marquee layout — continuous, smooth right-to-left auto-scroll.
 *
 * The item set is rendered twice so the CSS animation can loop seamlessly;
 * the second (cloned) set is hidden from assistive tech and carries no
 * schema.org markup to avoid duplicate Review data. When motion is reduced
 * the track becomes a normal horizontally-scrollable strip (see front.css).
 *
 * Override: copy to your theme as advanced-testimonial/marquee.php
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
	<?php
	$at_marquee_class = 'at-marquee';
	if ( ! empty( $atts['fade'] ) ) {
		$at_marquee_class .= ' at-marquee--fade';
	}
	if ( 'right' === $atts['direction'] ) {
		$at_marquee_class .= ' at-marquee--reverse';
	}
	?>
	<div class="<?php echo esc_attr( $at_marquee_class ); ?>" data-at-marquee data-speed="<?php echo esc_attr( $atts['marquee_speed'] ); ?>" style="--at-marquee-item:<?php echo esc_attr( $atts['marquee_item'] ); ?>px" role="group" aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Customer testimonials', 'advanced-testimonial' ); ?>">
		<div class="at-marquee__viewport">
			<div class="at-marquee__track">
				<?php foreach ( $testimonials as $item ) : ?>
					<div class="at-marquee__slide">
						<?php include $item_template; ?>
					</div>
				<?php endforeach; ?>
				<?php
				// Cloned set for the seamless loop: hidden from screen readers and
				// rendered without schema markup so Review data is not duplicated.
				$schema = false;
				?>
				<?php foreach ( $testimonials as $item ) : ?>
					<div class="at-marquee__slide" aria-hidden="true">
						<?php include $item_template; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>
