<?php
/**
 * Carousel layout — lightweight vanilla-JS slider.
 *
 * Override: copy to your theme as advanced-testimonial/carousel.php
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
	<div class="at-carousel" data-at-carousel data-columns="<?php echo esc_attr( $atts['columns'] ); ?>" data-autoplay="<?php echo esc_attr( $atts['autoplay'] ); ?>" role="group" aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Customer testimonials', 'advanced-testimonial' ); ?>">
		<div class="at-carousel__viewport">
			<div class="at-carousel__track">
				<?php foreach ( $testimonials as $item ) : ?>
					<div class="at-carousel__slide" role="group" aria-roledescription="<?php esc_attr_e( 'slide', 'advanced-testimonial' ); ?>">
						<?php include $item_template; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<button type="button" class="at-carousel__nav at-carousel__prev" aria-label="<?php esc_attr_e( 'Previous testimonials', 'advanced-testimonial' ); ?>">
			<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M15 18l-6-6 6-6"/></svg>
		</button>
		<button type="button" class="at-carousel__nav at-carousel__next" aria-label="<?php esc_attr_e( 'Next testimonials', 'advanced-testimonial' ); ?>">
			<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 6l6 6-6 6"/></svg>
		</button>
		<div class="at-carousel__dots" role="group" aria-label="<?php esc_attr_e( 'Choose testimonial', 'advanced-testimonial' ); ?>"></div>
	</div>
</div>
