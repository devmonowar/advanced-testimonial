<?php
/**
 * Admin asset loading (scoped to testimonial screens only).
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Enqueues admin CSS/JS only where the plugin needs it.
 */
final class Assets {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Enqueue assets on testimonial edit/list screens.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue( $hook ) {
		$screen = get_current_screen();

		if ( ! $screen || CPT::POST_TYPE !== $screen->post_type ) {
			return;
		}

		$is_editor = in_array( $hook, array( 'post.php', 'post-new.php' ), true );
		$is_list   = ( 'edit.php' === $hook );

		if ( ! $is_editor && ! $is_list ) {
			return;
		}

		wp_enqueue_style(
			'advanced-testimonial-admin',
			ADVANCED_TESTIMONIAL_URL . 'assets/css/admin.css',
			array(),
			ADVANCED_TESTIMONIAL_VERSION
		);

		if ( $is_editor ) {
			wp_enqueue_media();

			wp_enqueue_script(
				'advanced-testimonial-admin',
				ADVANCED_TESTIMONIAL_URL . 'assets/js/admin.js',
				array( 'media-editor' ),
				ADVANCED_TESTIMONIAL_VERSION,
				true
			);

			wp_localize_script(
				'advanced-testimonial-admin',
				'advancedTestimonialAdmin',
				array(
					'mediaTitle'  => __( 'Select Company Logo', 'advanced-testimonial' ),
					'mediaButton' => __( 'Use this logo', 'advanced-testimonial' ),
				)
			);
		}
	}
}
