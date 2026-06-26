<?php
/**
 * Single testimonial card partial.
 *
 * Override: copy to your theme as advanced-testimonial/item.php
 *
 * @var array $item   Prepared testimonial data.
 * @var array $atts   Display attributes.
 * @var bool  $schema Whether to output schema.org markup.
 *
 * @package AdvancedTestimonial
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables are function-scoped via TemplateLoader::render().
$at_has_rating = ! empty( $atts['show_rating'] ) && $item['rating'] > 0;
$at_review     = $schema && $at_has_rating;

$at_role = array();
if ( ! empty( $atts['show_designation'] ) && '' !== $item['designation'] ) {
	$at_role[] = $item['designation'];
}
if ( ! empty( $atts['show_company'] ) && '' !== $item['company'] ) {
	$at_role[] = $item['company'];
}
?>
<article class="at-card"<?php echo $at_review ? ' itemscope itemtype="https://schema.org/Review"' : ''; ?>>
	<div class="at-card__inner">

		<?php if ( $at_review ) : ?>
			<div itemprop="itemReviewed" itemscope itemtype="https://schema.org/Organization" class="at-visually-hidden">
				<meta itemprop="name" content="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			</div>
		<?php endif; ?>

		<?php if ( $at_has_rating ) : ?>
			<?php if ( $at_review ) : ?>
				<div class="at-card__rating" itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating">
					<meta itemprop="ratingValue" content="<?php echo esc_attr( $item['rating'] ); ?>">
					<meta itemprop="bestRating" content="5">
					<meta itemprop="worstRating" content="1">
					<?php echo $item['stars']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- prepared safe markup. ?>
				</div>
			<?php else : ?>
				<div class="at-card__rating"><?php echo $item['stars']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- prepared safe markup. ?></div>
			<?php endif; ?>
		<?php endif; ?>

		<blockquote class="at-card__review"<?php echo $at_review ? ' itemprop="reviewBody"' : ''; ?>>
			<?php echo wp_kses_post( wpautop( $item['review'] ) ); ?>
		</blockquote>

		<footer class="at-card__author">
			<?php if ( '' !== $item['photo'] ) : ?>
				<div class="at-card__avatar"><?php echo $item['photo']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- prepared safe markup. ?></div>
			<?php endif; ?>

			<div class="at-card__meta">
				<span class="at-card__name"<?php echo $at_review ? ' itemprop="author" itemscope itemtype="https://schema.org/Person"' : ''; ?>>
					<?php
					if ( $at_review ) {
						echo '<span itemprop="name">' . esc_html( $item['name'] ) . '</span>';
					} else {
						echo esc_html( $item['name'] );
					}
					?>
					<?php if ( ! empty( $atts['show_verified'] ) && $item['verified'] ) : ?>
						<span class="at-verified" title="<?php esc_attr_e( 'Verified customer', 'advanced-testimonial' ); ?>" aria-label="<?php esc_attr_e( 'Verified customer', 'advanced-testimonial' ); ?>">
							<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"/></svg>
						</span>
					<?php endif; ?>
				</span>

				<?php if ( ! empty( $at_role ) ) : ?>
					<span class="at-card__role"><?php echo esc_html( implode( ', ', $at_role ) ); ?></span>
				<?php endif; ?>

				<?php if ( ! empty( $atts['show_location'] ) && '' !== $item['location'] ) : ?>
					<span class="at-card__location"><?php echo esc_html( $item['location'] ); ?></span>
				<?php endif; ?>

				<?php if ( ! empty( $atts['show_date'] ) && '' !== $item['date'] ) : ?>
					<span class="at-card__date"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $item['date'] ) ) ); ?></span>
				<?php endif; ?>
			</div>

			<?php if ( '' !== $item['logo'] ) : ?>
				<div class="at-card__logo"><?php echo $item['logo']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- prepared safe markup. ?></div>
			<?php endif; ?>
		</footer>

		<?php if ( ! empty( $atts['show_website'] ) && '' !== $item['website'] ) : ?>
			<div class="at-card__action">
				<a class="at-btn" href="<?php echo esc_url( $item['website'] ); ?>" target="_blank" rel="nofollow noopener"><?php esc_html_e( 'Visit Website', 'advanced-testimonial' ); ?></a>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $item['socials'] ) ) : ?>
			<div class="at-card__socials">
				<?php foreach ( $item['socials'] as $at_network => $at_url ) : ?>
					<a class="at-social at-social--<?php echo esc_attr( $at_network ); ?>" href="<?php echo esc_url( $at_url ); ?>" target="_blank" rel="nofollow noopener" aria-label="<?php echo esc_attr( ucfirst( $at_network ) ); ?>">
						<span aria-hidden="true"><?php echo esc_html( strtoupper( substr( $at_network, 0, 1 ) ) ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div>
</article>
