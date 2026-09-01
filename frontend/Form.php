<?php
/**
 * Front-end testimonial submission form.
 *
 * Shortcode: [at_form]
 * Attributes: title, success, group
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial\Frontend;

use AdvancedTestimonial\Admin\CPT;
use AdvancedTestimonial\Admin\Settings;
use AdvancedTestimonial\Admin\Taxonomy;
use AdvancedTestimonial\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Renders the submission form and processes POST requests.
 */
final class Form {

	/**
	 * Nonce action name.
	 */
	const NONCE_ACTION = 'at_form_submit';

	/**
	 * Nonce hidden field name.
	 */
	const NONCE_FIELD = '_at_nonce';

	/**
	 * Trigger field name — present on every AT form POST.
	 */
	const ACTION_FIELD = 'at_form_action';

	/**
	 * Transient key prefix for rate-limiting (per IP).
	 */
	const RATE_PREFIX = 'at_rate_';

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_shortcode( 'at_form', array( $this, 'render' ) );
		add_action( 'template_redirect', array( $this, 'handle' ) );
	}

	/**
	 * Render the form shortcode.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string
	 */
	public function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'title'   => '',
				'success' => '',
				'group'   => '',
			),
			(array) $atts,
			'at_form'
		);

		$settings = Settings::get_settings();

		if ( empty( $settings['form_enabled'] ) ) {
			return '';
		}

		Assets::register_style();
		if ( Settings::get( 'enable_css', 1 ) ) {
			wp_enqueue_style( Assets::STYLE );
		}

		// PRG success state.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['at_submitted'] ) && '1' === $_GET['at_submitted'] ) {
			$msg = '' !== $atts['success']
				? $atts['success']
				: ( ! empty( $settings['form_success_message'] )
					? $settings['form_success_message']
					: __( 'Thank you! Your testimonial has been submitted and is awaiting review.', 'advanced-testimonial' ) );
			return '<div class="at-form-notice at-form-success" role="alert">' . esc_html( $msg ) . '</div>';
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$error = isset( $_GET['at_form_error'] ) ? sanitize_key( $_GET['at_form_error'] ) : '';

		$loader = new TemplateLoader();

		return $loader->render(
			$loader->locate( 'form' ),
			array(
				'at_form_atts'     => $atts,
				'at_form_settings' => $settings,
				'at_form_error'    => $error,
			)
		);
	}

	/**
	 * Process the form POST on template_redirect.
	 *
	 * @return void
	 */
	public function handle() {
		if ( empty( $_POST[ self::ACTION_FIELD ] ) ) {
			return;
		}

		$settings = Settings::get_settings();

		// Enforced here as well as in render(): a page carrying the form can
		// sit in a page cache, and its nonce stays valid for up to 24 hours,
		// so switching the form off would otherwise keep accepting
		// submissions until every cached nonce had expired.
		if ( empty( $settings['form_enabled'] ) ) {
			return;
		}

		// Nonce.
		if ( ! wp_verify_nonce(
			isset( $_POST[ self::NONCE_FIELD ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD ] ) ) : '',
			self::NONCE_ACTION
		) ) {
			// Nearly always an expired nonce on a cached page rather than an
			// attack, so send the visitor back with a message they can act on.
			$this->redirect_error( 'expired' );
		}

		// Honeypot — bots fill hidden fields that real users never see.
		if ( ! empty( $_POST['at_url_confirm'] ) ) {
			$this->redirect_success(); // Pretend success so bots don't know they failed.
		}

		// Rate limit: 1 successful submission per IP per 5 minutes.
		$rate_key = self::RATE_PREFIX . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
		if ( get_transient( $rate_key ) ) {
			$this->redirect_error( 'rate_limit' );
		}

		// Required fields.
		$name    = sanitize_text_field( wp_unslash( isset( $_POST['at_name'] ) ? $_POST['at_name'] : '' ) );
		$content = sanitize_textarea_field( wp_unslash( isset( $_POST['at_content'] ) ? $_POST['at_content'] : '' ) );

		if ( '' === $name || '' === $content ) {
			$this->redirect_error( 'required' );
		}

		// Cap the free-text fields so a scripted POST cannot store megabytes.
		if ( mb_strlen( $name ) > 200 || mb_strlen( $content ) > 5000 ) {
			$this->redirect_error( 'too_long' );
		}

		// Rating.
		$rating = min( 5, max( 0, (int) sanitize_text_field( wp_unslash( isset( $_POST['at_rating'] ) ? $_POST['at_rating'] : '0' ) ) ) );
		if ( ! empty( $settings['form_require_rating'] ) && $rating < 1 ) {
			$this->redirect_error( 'rating_required' );
		}

		// Optional fields.
		$headline    = sanitize_text_field( wp_unslash( isset( $_POST['at_headline'] ) ? $_POST['at_headline'] : '' ) );
		$company     = sanitize_text_field( wp_unslash( isset( $_POST['at_company'] ) ? $_POST['at_company'] : '' ) );
		$designation = sanitize_text_field( wp_unslash( isset( $_POST['at_designation'] ) ? $_POST['at_designation'] : '' ) );
		$location    = sanitize_text_field( wp_unslash( isset( $_POST['at_location'] ) ? $_POST['at_location'] : '' ) );
		$email       = sanitize_email( wp_unslash( isset( $_POST['at_email'] ) ? $_POST['at_email'] : '' ) );
		$website     = esc_url_raw( wp_unslash( isset( $_POST['at_website'] ) ? $_POST['at_website'] : '' ) );
		$group       = sanitize_text_field( wp_unslash( isset( $_POST['at_group'] ) ? $_POST['at_group'] : '' ) );

		// Insert post.
		$status  = ! empty( $settings['form_auto_publish'] ) ? 'publish' : 'pending';
		$post_id = wp_insert_post(
			array(
				'post_type'    => CPT::POST_TYPE,
				'post_title'   => $name,
				'post_content' => $content,
				'post_status'  => $status,
			)
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			$this->redirect_error( 'server' );
		}

		// Save meta.
		$meta_map = array(
			'headline'    => $headline,
			'company'     => $company,
			'designation' => $designation,
			'location'    => $location,
			'website'     => $website,
		);
		foreach ( $meta_map as $key => $val ) {
			if ( '' !== $val ) {
				update_post_meta( $post_id, Helpers::meta_key( $key ), $val );
			}
		}
		if ( $rating > 0 ) {
			update_post_meta( $post_id, Helpers::meta_key( 'rating' ), (string) $rating );
		}
		if ( '' !== $email ) {
			update_post_meta( $post_id, Helpers::meta_key( 'email' ), $email );
		}

		// Assign taxonomy group.
		if ( '' !== $group ) {
			$term = get_term_by( 'slug', $group, Taxonomy::TAXONOMY );
			if ( $term ) {
				wp_set_object_terms( $post_id, array( $term->term_id ), Taxonomy::TAXONOMY );
			}
		}

		// Set rate limit transient.
		set_transient( $rate_key, 1, 5 * MINUTE_IN_SECONDS );

		// Notify admin.
		$this->notify_admin( $post_id, $name, $content, $email, $settings );

		$this->redirect_success();
	}

	/**
	 * Send an admin notification email.
	 *
	 * @param int    $post_id  Newly inserted post ID.
	 * @param string $name     Submitter name.
	 * @param string $content  Review text.
	 * @param string $email    Submitter email (may be empty).
	 * @param array  $settings Plugin settings.
	 * @return void
	 */
	private function notify_admin( $post_id, $name, $content, $email, $settings ) {
		// Fall back to the site admin if the configured address is not valid,
		// so a typo in Settings can never silently drop notifications.
		$to      = ! empty( $settings['form_notify_email'] ) && is_email( $settings['form_notify_email'] )
			? $settings['form_notify_email']
			: get_option( 'admin_email' );
		$site    = get_bloginfo( 'name' );
		$subject = sprintf(
			/* translators: %s: site name */
			__( '[%s] New testimonial submitted', 'advanced-testimonial' ),
			$site
		);
		$edit_url = admin_url( 'post.php?post=' . $post_id . '&action=edit' );
		$body     = sprintf(
			/* translators: 1: author name, 2: author email, 3: review text, 4: edit URL */
			__( "Author: %1\$s\nEmail:  %2\$s\n\nReview:\n%3\$s\n\nReview it here:\n%4\$s", 'advanced-testimonial' ),
			$name,
			'' !== $email ? $email : '—',
			$content,
			$edit_url
		);
		wp_mail( $to, $subject, $body );
	}

	/**
	 * Redirect to the current URL with ?at_submitted=1.
	 *
	 * @return void
	 */
	private function redirect_success() {
		wp_safe_redirect(
			add_query_arg( 'at_submitted', '1', remove_query_arg( array( 'at_submitted', 'at_form_error' ) ) )
		);
		exit;
	}

	/**
	 * Redirect to the current URL with ?at_form_error={code}.
	 *
	 * @param string $code Error code.
	 * @return void
	 */
	private function redirect_error( $code ) {
		wp_safe_redirect(
			add_query_arg( 'at_form_error', $code, remove_query_arg( array( 'at_submitted', 'at_form_error' ) ) )
		);
		exit;
	}
}
