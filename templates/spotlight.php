<?php
/**
 * Spotlight layout — one large testimonial at a time (single-item carousel).
 *
 * Override: copy to your theme as advanced-testimonial/spotlight.php
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
	<div class="at-carousel at-carousel--spotlight" data-at-carousel data-columns="1" data-autoplay="<?php echo esc_attr( $atts['autoplay'] ); ?>">
		<div class="at-carousel__viewport">
			<div class="at-carousel__track">
				<?php foreach ( $testimonials as $item ) : ?>
					<div class="at-carousel__slide">
						<?php include $item_template; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<button type="button" class="at-carousel__nav at-carousel__prev" aria-label="<?php esc_attr_e( 'Previous testimonial', 'advanced-testimonial' ); ?>">
			<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M15 18l-6-6 6-6"/></svg>
		</button>
		<button type="button" class="at-carousel__nav at-carousel__next" aria-label="<?php esc_attr_e( 'Next testimonial', 'advanced-testimonial' ); ?>">
			<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 6l6 6-6 6"/></svg>
		</button>
		<div class="at-carousel__dots" role="tablist" aria-label="<?php esc_attr_e( 'Choose testimonial', 'advanced-testimonial' ); ?>"></div>
	</div>
</div>
