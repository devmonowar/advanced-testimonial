<?php
/**
 * Frontend asset registration and on-demand enqueueing.
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial\Frontend;

use AdvancedTestimonial\Admin\Settings;
use AdvancedTestimonial\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Registers frontend CSS/JS and enqueues them only where testimonials render.
 */
final class Assets {

	/**
	 * Stylesheet handle.
	 */
	const STYLE = 'advanced-testimonial';

	/**
	 * Carousel script handle.
	 */
	const SCRIPT_CAROUSEL = 'advanced-testimonial-carousel';

	/**
	 * Video lightbox script handle.
	 */
	const SCRIPT_VIDEO = 'advanced-testimonial-video';

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'wp_enqueue_scripts', array( $this, 'on_enqueue_scripts' ) );
	}

	/**
	 * Register the stylesheet handle (idempotent).
	 *
	 * @return void
	 */
	public static function register_style() {
		if ( wp_style_is( self::STYLE, 'registered' ) ) {
			return;
		}

		$min = self::min_suffix( 'assets/css/front.css' );

		wp_register_style(
			self::STYLE,
			ADVANCED_TESTIMONIAL_URL . 'assets/css/front' . $min . '.css',
			array(),
			Helpers::asset_version( 'assets/css/front' . $min . '.css' )
		);
	}

	/**
	 * Resolve the ".min" suffix for an asset, honouring the "Use Minified
	 * Assets" setting only when the minified file actually exists — otherwise
	 * it falls back to the full file so enabling the option never 404s.
	 *
	 * @param string $relative Path to the full asset, e.g. assets/css/front.css.
	 * @return string Either ".min" or an empty string.
	 */
	private static function min_suffix( $relative ) {
		if ( ! Settings::get( 'use_minified' ) ) {
			return '';
		}

		$min = preg_replace( '/\.(css|js)$/', '.min.$1', $relative );

		return ( $min && is_readable( ADVANCED_TESTIMONIAL_DIR . $min ) ) ? '.min' : '';
	}

	/**
	 * Register the carousel script handle (idempotent).
	 *
	 * @return void
	 */
	public static function register_script() {
		if ( wp_script_is( self::SCRIPT_CAROUSEL, 'registered' ) ) {
			return;
		}

		$min = self::min_suffix( 'assets/js/carousel.js' );

		wp_register_script(
			self::SCRIPT_CAROUSEL,
			ADVANCED_TESTIMONIAL_URL . 'assets/js/carousel' . $min . '.js',
			array(),
			Helpers::asset_version( 'assets/js/carousel' . $min . '.js' ),
			true
		);
	}

	/**
	 * Register the video lightbox script handle (idempotent).
	 *
	 * @return void
	 */
	public static function register_script_video() {
		if ( wp_script_is( self::SCRIPT_VIDEO, 'registered' ) ) {
			return;
		}

		$min = self::min_suffix( 'assets/js/video.js' );

		wp_register_script(
			self::SCRIPT_VIDEO,
			ADVANCED_TESTIMONIAL_URL . 'assets/js/video' . $min . '.js',
			array(),
			Helpers::asset_version( 'assets/js/video' . $min . '.js' ),
			true
		);
	}

	/**
	 * Enqueue the video lightbox script. Called by the renderer only when a
	 * testimonial in the current output actually has a video.
	 *
	 * @return void
	 */
	public static function enqueue_video() {
		self::register_script_video();

		if ( Settings::get( 'enable_js', 1 ) ) {
			wp_enqueue_script( self::SCRIPT_VIDEO );
		}
	}

	/**
	 * Runs on wp_enqueue_scripts: register handles, attach custom CSS, and
	 * optionally load the stylesheet globally when conditional loading is off.
	 *
	 * @return void
	 */
	public function on_enqueue_scripts() {
		self::register_style();
		self::register_script();
		self::register_script_video();

		$custom = trim( (string) Settings::get( 'custom_css', '' ) );
		if ( '' !== $custom ) {
			wp_add_inline_style( self::STYLE, $custom );
		}

		if ( ! Settings::get( 'conditional_assets', 1 ) && Settings::get( 'enable_css', 1 ) ) {
			wp_enqueue_style( self::STYLE );
		}
	}

	/**
	 * Enqueue the assets needed for a specific render.
	 *
	 * @param array $atts Display attributes.
	 * @return void
	 */
	public static function enqueue_front( array $atts ) {
		self::register_style();
		self::register_script();

		if ( Settings::get( 'enable_css', 1 ) ) {
			wp_enqueue_style( self::STYLE );
		}

		$needs_js = in_array( $atts['layout'], array( 'carousel', 'spotlight', 'marquee' ), true ) || ! empty( $atts['show_filter'] ) || ! empty( $atts['read_more'] ) || ! empty( $atts['load_more'] );
		if ( $needs_js && Settings::get( 'enable_js', 1 ) ) {
			wp_enqueue_script( self::SCRIPT_CAROUSEL );
		}
	}
}
