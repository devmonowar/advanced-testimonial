<?php
/**
 * Admin asset loading (scoped to testimonial screens only).
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial\Admin;

use AdvancedTestimonial\Helpers;

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
		$is_groups = ( 'edit-tags.php' === $hook );

		if ( ! $is_editor && ! $is_list && ! $is_groups ) {
			return;
		}

		wp_enqueue_style(
			'advanced-testimonial-admin',
			ADVANCED_TESTIMONIAL_URL . 'assets/css/admin.css',
			array(),
			Helpers::asset_version( 'assets/css/admin.css' )
		);

		if ( $is_list || $is_groups ) {
			wp_enqueue_script(
				'advanced-testimonial-copy',
				ADVANCED_TESTIMONIAL_URL . 'assets/js/copy.js',
				array(),
				Helpers::asset_version( 'assets/js/copy.js' ),
				true
			);
			wp_localize_script(
				'advanced-testimonial-copy',
				'advancedTestimonialCopy',
				array( 'copied' => __( 'Copied!', 'advanced-testimonial' ) )
			);
		}

		if ( $is_editor ) {
			wp_enqueue_media();

			wp_enqueue_script(
				'advanced-testimonial-admin',
				ADVANCED_TESTIMONIAL_URL . 'assets/js/admin.js',
				array( 'media-editor' ),
				Helpers::asset_version( 'assets/js/admin.js' ),
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
