<?php
/**
 * Shared helper utilities.
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial;

defined( 'ABSPATH' ) || exit;

/**
 * Stateless helper methods used across admin and frontend.
 */
final class Helpers {

	/**
	 * Meta key prefix for all testimonial fields.
	 */
	const META_PREFIX = '_at_';

	/**
	 * Build the full meta key for a field.
	 *
	 * @param string $field Field name without prefix.
	 * @return string
	 */
	public static function meta_key( $field ) {
		return self::META_PREFIX . $field;
	}

	/**
	 * Clamp a value to a valid 0-5 rating.
	 *
	 * @param mixed $value Raw rating value.
	 * @return int Rating between 0 and 5 (0 means "no rating").
	 */
	public static function clamp_rating( $value ) {
		$rating = (int) $value;

		if ( $rating < 0 ) {
			$rating = 0;
		}

		if ( $rating > 5 ) {
			$rating = 5;
		}

		return $rating;
	}

	/**
	 * Render a compact star string for admin/list contexts.
	 *
	 * @param int $rating Rating from 0 to 5.
	 * @return string Filled/empty star glyphs, already safe for output.
	 */
	public static function stars_text( $rating ) {
		$rating = self::clamp_rating( $rating );

		if ( 0 === $rating ) {
			return '';
		}

		return str_repeat( "\xE2\x98\x85", $rating ) . str_repeat( "\xE2\x98\x86", 5 - $rating );
	}
}
