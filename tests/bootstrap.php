<?php
/**
 * PHPUnit bootstrap: the Helpers under test are pure PHP except for one
 * WordPress wrapper, which is stubbed here so the suite runs without WP.
 */

define( 'ABSPATH', __DIR__ . '/../' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- ABSPATH is WordPress core's own constant; the stub only mirrors it.

if ( ! function_exists( 'wp_parse_url' ) ) {
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- stub mirrors the core function Helpers calls.
	function wp_parse_url( $url, $component = -1 ) {
		return parse_url( $url, $component );
	}
}

require_once __DIR__ . '/../includes/Helpers.php';
