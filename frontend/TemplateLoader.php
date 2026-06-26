<?php
/**
 * Locates and renders templates with theme-override support.
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Resolves template files, preferring theme overrides.
 *
 * Override location (child or parent theme):
 *   theme/advanced-testimonial/{name}.php
 */
final class TemplateLoader {

	/**
	 * Theme sub-directory used for overrides.
	 */
	const THEME_DIR = 'advanced-testimonial';

	/**
	 * Locate a template file by name.
	 *
	 * @param string $name Template name without extension.
	 * @return string Absolute path, or '' if not found.
	 */
	public function locate( $name ) {
		$name     = sanitize_file_name( $name );
		$relative = self::THEME_DIR . '/' . $name . '.php';

		$theme_template = locate_template( array( $relative ) );
		if ( $theme_template ) {
			$located = $theme_template;
		} else {
			$located = ADVANCED_TESTIMONIAL_DIR . 'templates/' . $name . '.php';
		}

		/**
		 * Filter the resolved template path.
		 *
		 * @param string $located Absolute template path.
		 * @param string $name    Template name.
		 */
		$located = apply_filters( 'advanced_testimonial_template', $located, $name );

		return is_readable( $located ) ? $located : '';
	}

	/**
	 * Render a template to a string.
	 *
	 * @param string $template Absolute template path.
	 * @param array  $data     Variables made available to the template.
	 * @return string
	 */
	public function render( $template, array $data ) {
		if ( ! $template || ! is_readable( $template ) ) {
			return '';
		}

		// Variables consumed by the template files.
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- controlled, internal data only.
		extract( $data, EXTR_SKIP );

		ob_start();
		include $template;

		return (string) ob_get_clean();
	}
}
