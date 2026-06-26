<?php
/**
 * Lightweight PSR-4 autoloader (fallback when Composer is not installed).
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial;

defined( 'ABSPATH' ) || exit;

/**
 * Maps the AdvancedTestimonial namespace onto the plugin's directories.
 */
final class Loader {

	/**
	 * Sub-namespace => directory map. Anything not listed lives in includes/.
	 *
	 * @var array<string,string>
	 */
	private static $map = array(
		'Admin'     => 'admin/',
		'Frontend'  => 'frontend/',
		'Shortcode' => 'shortcode/',
		'Blocks'    => 'blocks/',
	);

	/**
	 * Register the autoloader with SPL.
	 *
	 * @return void
	 */
	public static function register() {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * Resolve a fully-qualified class name to a file and require it.
	 *
	 * @param string $class Fully-qualified class name.
	 * @return void
	 */
	public static function autoload( $class ) {
		$prefix = 'AdvancedTestimonial\\';

		if ( 0 !== strpos( $class, $prefix ) ) {
			return;
		}

		$relative = substr( $class, strlen( $prefix ) );
		$parts    = explode( '\\', $relative );
		$base     = 'includes/';

		if ( count( $parts ) > 1 && isset( self::$map[ $parts[0] ] ) ) {
			$base = self::$map[ array_shift( $parts ) ];
		}

		$path = ADVANCED_TESTIMONIAL_DIR . $base . implode( '/', $parts ) . '.php';

		if ( is_readable( $path ) ) {
			require $path;
		}
	}
}
