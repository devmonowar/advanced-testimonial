<?php
/**
 * Plugin Name:       Advanced Testimonial
 * Plugin URI:        https://wordpress.org/plugins/advanced-testimonial/
 * Description:       Showcase customer testimonials and reviews in beautiful grids, carousels, cards and more. Lightweight, block-ready and built for social proof.
 * Version:           2.0.4
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            devmonowar
 * Author URI:        https://profiles.wordpress.org/devmonowar/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       advanced-testimonial
 * Domain Path:       /languages
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial;

defined( 'ABSPATH' ) || exit;

define( 'ADVANCED_TESTIMONIAL_VERSION', '2.0.4' );
define( 'ADVANCED_TESTIMONIAL_FILE', __FILE__ );
define( 'ADVANCED_TESTIMONIAL_DIR', plugin_dir_path( __FILE__ ) );
define( 'ADVANCED_TESTIMONIAL_URL', plugin_dir_url( __FILE__ ) );
define( 'ADVANCED_TESTIMONIAL_BASENAME', plugin_basename( __FILE__ ) );

/*
 * Remote Demo Library manifest URL (GitHub Pages). Treated as a frozen public
 * API — released sites cache and depend on it. Filterable for dev/staging via
 * the `advanced_testimonial_demo_library_url` filter.
 */
if ( ! defined( 'ADVANCED_TESTIMONIAL_DEMO_LIBRARY_URL' ) ) {
	define( 'ADVANCED_TESTIMONIAL_DEMO_LIBRARY_URL', 'https://devmonowar.github.io/wp-plugin-demo-library/advanced-testimonial/demo-library.json' );
}

/*
 * Autoloader: prefer the Composer-generated PSR-4 autoloader when present,
 * otherwise fall back to the bundled lightweight PSR-4 loader so the plugin
 * works even without running `composer install`.
 */
if ( is_readable( ADVANCED_TESTIMONIAL_DIR . 'vendor/autoload.php' ) ) {
	require ADVANCED_TESTIMONIAL_DIR . 'vendor/autoload.php';
} else {
	require ADVANCED_TESTIMONIAL_DIR . 'includes/Loader.php';
	Loader::register();
}

register_activation_hook( __FILE__, array( Activator::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( Deactivator::class, 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function () {
		Plugin::instance()->run();
	}
);
