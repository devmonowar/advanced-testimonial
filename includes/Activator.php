<?php
/**
 * Runs on plugin activation.
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial;

defined( 'ABSPATH' ) || exit;

/**
 * Activation routine.
 */
final class Activator {

	/**
	 * Fired by register_activation_hook().
	 *
	 * Post types and taxonomies are registered before flushing so their
	 * rewrite rules are available immediately. (They are wired in later phases;
	 * the flush here keeps activation forward-compatible.)
	 *
	 * @return void
	 */
	public static function activate() {
		// Register CPT + taxonomy so their rewrite rules exist before flushing.
		( new Admin\CPT() )->register_post_type();
		( new Admin\Taxonomy() )->register_taxonomy();

		update_option( 'advanced_testimonial_version', ADVANCED_TESTIMONIAL_VERSION );

		flush_rewrite_rules();
	}
}
