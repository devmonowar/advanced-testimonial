<?php
/**
 * Runs on plugin deactivation.
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial;

defined( 'ABSPATH' ) || exit;

/**
 * Deactivation routine.
 */
final class Deactivator {

	/**
	 * Fired by register_deactivation_hook().
	 *
	 * @return void
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}
}
