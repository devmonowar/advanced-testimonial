<?php
/**
 * The [advanced_testimonial] shortcode.
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial\Shortcode;

use AdvancedTestimonial\Frontend\Renderer;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and renders the testimonial shortcode.
 */
final class Shortcode {

	/**
	 * Shortcode tag.
	 */
	const TAG = 'advanced_testimonial';

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_shortcode( self::TAG, array( $this, 'render' ) );
	}

	/**
	 * Render the shortcode.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string
	 */
	public function render( $atts ) {
		$atts = shortcode_atts( Renderer::defaults(), (array) $atts, self::TAG );

		return Renderer::render( $atts );
	}
}
