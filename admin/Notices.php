<?php
/**
 * Admin notices (empty-state call to action).
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Shows a friendly empty state when there are no testimonials yet.
 */
final class Notices {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_notices', array( $this, 'empty_state' ) );
	}

	/**
	 * Render the empty-state notice on the testimonial list screen.
	 *
	 * @return void
	 */
	public function empty_state() {
		$screen = get_current_screen();
		if ( ! $screen || 'edit-' . CPT::POST_TYPE !== $screen->id ) {
			return;
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$counts = (array) wp_count_posts( CPT::POST_TYPE );
		$total  = 0;
		foreach ( array( 'publish', 'future', 'draft', 'pending', 'private' ) as $status ) {
			$total += isset( $counts[ $status ] ) ? (int) $counts[ $status ] : 0;
		}
		if ( $total > 0 ) {
			return;
		}

		$add_url = admin_url( 'post-new.php?post_type=' . CPT::POST_TYPE );

		echo '<div class="notice notice-info at-empty-state">';
		echo '<p><strong>' . esc_html__( 'No testimonials yet.', 'advanced-testimonial' ) . '</strong> ' . esc_html__( 'Add your first testimonial, or import the starter demo to see how everything looks.', 'advanced-testimonial' ) . '</p>';
		echo '<p>';
		echo '<a href="' . esc_url( $add_url ) . '" class="button button-primary">' . esc_html__( 'Add New', 'advanced-testimonial' ) . '</a> ';

		if ( current_user_can( 'manage_options' ) ) {
			echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline-block;margin-left:6px;">';
			wp_nonce_field( Tools::NONCE );
			echo '<input type="hidden" name="action" value="advanced_testimonial_tools" />';
			echo '<input type="hidden" name="at_tool" value="starter" />';
			echo '<button type="submit" class="button">' . esc_html__( 'Import Starter Demo', 'advanced-testimonial' ) . '</button>';
			echo '</form>';
		}

		echo '</p>';
		echo '</div>';
	}
}
