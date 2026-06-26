<?php
/**
 * Frontend asset registration and on-demand enqueueing.
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial\Frontend;

use AdvancedTestimonial\Admin\Settings;

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

		$min = Settings::get( 'use_minified' ) ? '.min' : '';

		wp_register_style(
			self::STYLE,
			ADVANCED_TESTIMONIAL_URL . 'assets/css/front' . $min . '.css',
			array(),
			ADVANCED_TESTIMONIAL_VERSION
		);
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

		$min = Settings::get( 'use_minified' ) ? '.min' : '';

		wp_register_script(
			self::SCRIPT_CAROUSEL,
			ADVANCED_TESTIMONIAL_URL . 'assets/js/carousel' . $min . '.js',
			array(),
			ADVANCED_TESTIMONIAL_VERSION,
			true
		);
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

		$needs_js = in_array( $atts['layout'], array( 'carousel', 'spotlight' ), true );
		if ( $needs_js && Settings::get( 'enable_js', 1 ) ) {
			wp_enqueue_script( self::SCRIPT_CAROUSEL );
		}
	}
}
