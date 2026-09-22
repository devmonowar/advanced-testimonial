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

		// Existing installs already have testimonial URLs in the wild (and
		// possibly indexed), so keep their single pages on. Fresh installs get
		// the schema default (off) — no thin pages from day one.
		if ( false !== get_option( 'advanced_testimonial_version', false ) ) {
			$saved = get_option( Admin\Settings::OPTION, array() );

			if ( is_array( $saved ) && ! array_key_exists( 'single_pages', $saved ) ) {
				$saved['single_pages'] = 1;
				update_option( Admin\Settings::OPTION, $saved );
			}
		}

		update_option( 'advanced_testimonial_version', ADVANCED_TESTIMONIAL_VERSION );

		flush_rewrite_rules();
	}

	/**
	 * One-time migration for updates (activation hooks do not run on update).
	 *
	 * Runs on init, after the CPT is registered: existing installs get their
	 * single testimonial pages preserved, then rewrite rules are rebuilt once
	 * for the 2.1.0 registration changes. Fresh installs no-op — the version
	 * option is already current from activate().
	 *
	 * @return void
	 */
	public static function maybe_migrate() {
		$stored = get_option( 'advanced_testimonial_version', false );

		if ( false !== $stored && version_compare( (string) $stored, ADVANCED_TESTIMONIAL_VERSION, '>=' ) ) {
			return;
		}

		$saved = get_option( Admin\Settings::OPTION, array() );

		if ( is_array( $saved ) && ! array_key_exists( 'single_pages', $saved ) && ( false !== $stored || ! empty( $saved ) ) ) {
			$saved['single_pages'] = 1;
			update_option( Admin\Settings::OPTION, $saved );
		}

		update_option( 'advanced_testimonial_version', ADVANCED_TESTIMONIAL_VERSION );

		flush_rewrite_rules();
	}
}
