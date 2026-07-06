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
	 * Parse a testimonial video URL into an embeddable descriptor.
	 *
	 * Supports YouTube (watch / youtu.be / shorts / embed), Vimeo and
	 * self-hosted video files (mp4 / webm / ogv).
	 *
	 * @param string $url Raw video URL.
	 * @return array{type:string,embed:string,thumbnail:string}|null Descriptor,
	 *         or null when the URL is empty or not recognised.
	 */
	public static function parse_video( $url ) {
		$url = trim( (string) $url );

		if ( '' === $url ) {
			return null;
		}

		// YouTube: watch?v=ID, youtu.be/ID, /shorts/ID, /embed/ID.
		if ( preg_match( '#(?:youtube\.com/(?:watch\?v=|shorts/|embed/)|youtu\.be/)([A-Za-z0-9_-]{6,20})#', $url, $m ) ) {
			return array(
				'type'      => 'youtube',
				'embed'     => 'https://www.youtube-nocookie.com/embed/' . $m[1] . '?autoplay=1&rel=0',
				'thumbnail' => 'https://img.youtube.com/vi/' . $m[1] . '/hqdefault.jpg',
			);
		}

		// Vimeo: vimeo.com/ID (optionally with an unlisted hash suffix).
		if ( preg_match( '#vimeo\.com/(?:video/)?(\d+)(?:/([a-f0-9]+))?#', $url, $m ) ) {
			$embed = 'https://player.vimeo.com/video/' . $m[1] . '?autoplay=1';
			if ( ! empty( $m[2] ) ) {
				$embed .= '&h=' . $m[2];
			}
			return array(
				'type'      => 'vimeo',
				'embed'     => $embed,
				'thumbnail' => '', // Vimeo thumbnails need an API call; the renderer falls back to the client photo.
			);
		}

		// Self-hosted file.
		$path = (string) wp_parse_url( $url, PHP_URL_PATH );
		if ( preg_match( '/\.(mp4|webm|ogv)$/i', $path ) ) {
			return array(
				'type'      => 'file',
				'embed'     => $url,
				'thumbnail' => '',
			);
		}

		return null;
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
