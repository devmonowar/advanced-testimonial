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
	 * Asset version string for cache-busting.
	 *
	 * Uses the file's modification time so any CSS/JS change invalidates caches
	 * even within the same plugin version; falls back to the plugin version.
	 *
	 * @param string $relative Path relative to the plugin root, e.g. assets/css/admin.css.
	 * @return string
	 */
	public static function asset_version( $relative ) {
		$path = ADVANCED_TESTIMONIAL_DIR . ltrim( $relative, '/' );

		if ( is_readable( $path ) ) {
			$mtime = filemtime( $path );
			if ( $mtime ) {
				return (string) $mtime;
			}
		}

		return ADVANCED_TESTIMONIAL_VERSION;
	}

	/**
	 * Clamp a value to a valid 0-5 rating, rounded to the nearest half-star.
	 *
	 * @param mixed $value Raw rating value.
	 * @return float Rating between 0 and 5 in 0.5 steps (0 means "no rating").
	 */
	public static function clamp_rating( $value ) {
		$rating = (float) $value;

		if ( $rating < 0 ) {
			$rating = 0.0;
		}

		if ( $rating > 5 ) {
			$rating = 5.0;
		}

		return round( $rating * 2 ) / 2;
	}

	/**
	 * Render a compact star string for admin/list contexts (supports half stars).
	 *
	 * @param mixed $rating Rating from 0 to 5.
	 * @return string Filled/half/empty star glyphs, already safe for output.
	 */
	public static function stars_text( $rating ) {
		$rating = self::clamp_rating( $rating );

		if ( $rating <= 0 ) {
			return '';
		}

		$full  = (int) floor( $rating );
		$half  = ( $rating - $full ) >= 0.5;
		$empty = 5 - $full - ( $half ? 1 : 0 );

		return str_repeat( "\xE2\x98\x85", $full )
			. ( $half ? "\xC2\xBD" : '' )
			. str_repeat( "\xE2\x98\x86", max( 0, $empty ) );
	}
}
