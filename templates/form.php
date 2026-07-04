<?php
/**
 * Front-end testimonial submission form template.
 *
 * Variables injected by Frontend\Form::render():
 *  $at_form_atts     — shortcode attributes (title, success, group)
 *  $at_form_settings — plugin settings array
 *  $at_form_error    — error code string, or empty
 *
 * @package AdvancedTestimonial
 */

defined( 'ABSPATH' ) || exit;

use AdvancedTestimonial\Frontend\Form;

$advanced_testimonial_form_errors = array(
	'required'        => __( 'Please fill in your name and review.', 'advanced-testimonial' ),
	'rating_required' => __( 'Please select a rating before submitting.', 'advanced-testimonial' ),
	'rate_limit'      => __( 'Too many submissions. Please wait a few minutes and try again.', 'advanced-testimonial' ),
	'server'          => __( 'Something went wrong. Please try again.', 'advanced-testimonial' ),
);
?>
<div class="at-form-wrap">
	<?php if ( '' !== $at_form_atts['title'] ) : ?>
		<h3 class="at-form-title"><?php echo esc_html( $at_form_atts['title'] ); ?></h3>
	<?php endif; ?>

	<?php if ( '' !== $at_form_error && isset( $advanced_testimonial_form_errors[ $at_form_error ] ) ) : ?>
		<div class="at-form-notice at-form-error" role="alert">
			<?php echo esc_html( $advanced_testimonial_form_errors[ $at_form_error ] ); ?>
		</div>
	<?php endif; ?>

	<form class="at-form" method="post" novalidate>
		<?php wp_nonce_field( Form::NONCE_ACTION, Form::NONCE_FIELD ); ?>
		<input type="hidden" name="<?php echo esc_attr( Form::ACTION_FIELD ); ?>" value="1">
		<?php if ( '' !== $at_form_atts['group'] ) : ?>
			<input type="hidden" name="at_group" value="<?php echo esc_attr( $at_form_atts['group'] ); ?>">
		<?php endif; ?>

		<?php /* Honeypot — hidden from real users, visible to bots */ ?>
		<div class="at-form__hp" aria-hidden="true" style="position:absolute;left:-9999px;top:auto;height:1px;width:1px;overflow:hidden;">
			<label for="at_url_confirm_field"><?php esc_html_e( 'Leave this blank', 'advanced-testimonial' ); ?></label>
			<input type="text" id="at_url_confirm_field" name="at_url_confirm" tabindex="-1" autocomplete="off">
		</div>

		<?php /* --- Required: Name --- */ ?>
		<div class="at-form__field at-form__field--name">
			<label for="at_name" class="at-form__label">
				<?php esc_html_e( 'Your Name', 'advanced-testimonial' ); ?>
				<span class="at-form__required" aria-hidden="true">*</span>
			</label>
			<input
				type="text"
				id="at_name"
				name="at_name"
				class="at-form__input"
				required
				autocomplete="name"
			>
		</div>

		<?php /* --- Rating stars --- */ ?>
		<div class="at-form__field at-form__field--rating">
			<span class="at-form__label">
				<?php esc_html_e( 'Rating', 'advanced-testimonial' ); ?>
				<?php if ( ! empty( $at_form_settings['form_require_rating'] ) ) : ?>
					<span class="at-form__required" aria-hidden="true">*</span>
				<?php endif; ?>
			</span>
			<div class="at-form__stars" role="group" aria-label="<?php esc_attr_e( 'Select a rating', 'advanced-testimonial' ); ?>">
				<?php
				foreach ( range( 1, 5 ) as $advanced_testimonial_star ) :
					/* translators: %d: number of stars selected (1-5). */
					$advanced_testimonial_star_label = sprintf( _n( '%d star', '%d stars', $advanced_testimonial_star, 'advanced-testimonial' ), $advanced_testimonial_star );
					?>
					<button
						type="button"
						class="at-form__star"
						data-value="<?php echo esc_attr( $advanced_testimonial_star ); ?>"
						aria-label="<?php echo esc_attr( $advanced_testimonial_star_label ); ?>"
					>
						<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
							<polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"/>
						</svg>
					</button>
				<?php endforeach; ?>
				<input type="hidden" name="at_rating" class="at-form__rating-val" value="0">
			</div>
		</div>

		<?php /* --- Required: Review text --- */ ?>
		<div class="at-form__field at-form__field--content">
			<label for="at_content" class="at-form__label">
				<?php esc_html_e( 'Your Review', 'advanced-testimonial' ); ?>
				<span class="at-form__required" aria-hidden="true">*</span>
			</label>
			<textarea
				id="at_content"
				name="at_content"
				class="at-form__textarea"
				rows="5"
				required
			></textarea>
		</div>

		<?php /* --- Optional: Headline --- */ ?>
		<?php if ( ! empty( $at_s['form_show_headline'] ) ) : ?>
		<div class="at-form__field at-form__field--headline">
			<label for="at_headline" class="at-form__label"><?php esc_html_e( 'Review Title', 'advanced-testimonial' ); ?></label>
			<input type="text" id="at_headline" name="at_headline" class="at-form__input">
		</div>
		<?php endif; ?>

		<?php /* --- Optional: Company --- */ ?>
		<?php if ( ! empty( $at_s['form_show_company'] ) ) : ?>
		<div class="at-form__field at-form__field--company">
			<label for="at_company" class="at-form__label"><?php esc_html_e( 'Company', 'advanced-testimonial' ); ?></label>
			<input type="text" id="at_company" name="at_company" class="at-form__input" autocomplete="organization">
		</div>
		<?php endif; ?>

		<?php /* --- Optional: Designation --- */ ?>
		<?php if ( ! empty( $at_s['form_show_designation'] ) ) : ?>
		<div class="at-form__field at-form__field--designation">
			<label for="at_designation" class="at-form__label"><?php esc_html_e( 'Job Title', 'advanced-testimonial' ); ?></label>
			<input type="text" id="at_designation" name="at_designation" class="at-form__input" autocomplete="organization-title">
		</div>
		<?php endif; ?>

		<?php /* --- Optional: Location --- */ ?>
		<?php if ( ! empty( $at_s['form_show_location'] ) ) : ?>
		<div class="at-form__field at-form__field--location">
			<label for="at_location" class="at-form__label"><?php esc_html_e( 'Location', 'advanced-testimonial' ); ?></label>
			<input type="text" id="at_location" name="at_location" class="at-form__input" autocomplete="address-level2">
		</div>
		<?php endif; ?>

		<?php /* --- Optional: Email --- */ ?>
		<?php if ( ! empty( $at_s['form_show_email'] ) ) : ?>
		<div class="at-form__field at-form__field--email">
			<label for="at_email" class="at-form__label"><?php esc_html_e( 'Email Address', 'advanced-testimonial' ); ?></label>
			<input type="email" id="at_email" name="at_email" class="at-form__input" autocomplete="email">
		</div>
		<?php endif; ?>

		<?php /* --- Optional: Website --- */ ?>
		<?php if ( ! empty( $at_s['form_show_website'] ) ) : ?>
		<div class="at-form__field at-form__field--website">
			<label for="at_website" class="at-form__label"><?php esc_html_e( 'Website', 'advanced-testimonial' ); ?></label>
			<input type="url" id="at_website" name="at_website" class="at-form__input" autocomplete="url" placeholder="https://">
		</div>
		<?php endif; ?>

		<div class="at-form__actions">
			<button type="submit" class="at-form__submit">
				<?php esc_html_e( 'Submit Testimonial', 'advanced-testimonial' ); ?>
			</button>
		</div>
	</form>
</div>

<script>
(function () {
	var wrap = document.querySelector('.at-form__stars');
	if (!wrap) return;
	var stars = [].slice.call(wrap.querySelectorAll('.at-form__star'));
	var input = wrap.querySelector('.at-form__rating-val');

	function setActive(n) {
		stars.forEach(function (b, i) {
			b.classList.toggle('is-active', i < n);
		});
		if (input) input.value = n;
	}

	stars.forEach(function (btn) {
		var val = parseInt(btn.getAttribute('data-value'), 10);
		btn.addEventListener('click', function () { setActive(val); });
		btn.addEventListener('mouseenter', function () {
			stars.forEach(function (b, i) { b.classList.toggle('at-form__star--hover', i < val); });
		});
		btn.addEventListener('focus', function () {
			stars.forEach(function (b, i) { b.classList.toggle('at-form__star--hover', i < val); });
		});
	});

	wrap.addEventListener('mouseleave', function () {
		stars.forEach(function (b) { b.classList.remove('at-form__star--hover'); });
	});
	wrap.addEventListener('focusout', function (e) {
		if (!wrap.contains(e.relatedTarget)) {
			stars.forEach(function (b) { b.classList.remove('at-form__star--hover'); });
		}
	});
}());
</script>
