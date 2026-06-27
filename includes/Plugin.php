<?php
/**
 * Main plugin orchestrator.
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial;

defined( 'ABSPATH' ) || exit;

/**
 * Boots and wires together the plugin's modules.
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Retrieve the shared instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Private constructor — use Plugin::instance().
	 */
	private function __construct() {}

	/**
	 * Load modules and register hooks.
	 *
	 * Modules are added here phase by phase (CPT, meta boxes, settings,
	 * frontend renderer, shortcode, block).
	 *
	 * @return void
	 */
	public function run() {
		// Translations load automatically since WordPress 4.6 (Domain Path
		// header points at /languages), so no load_plugin_textdomain() call.

		// Data layer — registered on both admin and frontend.
		( new Admin\CPT() )->register();
		( new Admin\Taxonomy() )->register();

		// Frontend output layer.
		( new Frontend\Assets() )->register();
		( new Shortcode\Shortcode() )->register();
		( new Blocks\Block() )->register();

		// Invalidate cached queries whenever testimonials change.
		add_action( 'save_post_' . Admin\CPT::POST_TYPE, array( Frontend\Query::class, 'bust_cache' ) );
		add_action( 'deleted_post', array( Frontend\Query::class, 'bust_cache' ) );

		// Admin-only modules.
		if ( is_admin() ) {
			( new Admin\MetaBoxes() )->register();
			( new Admin\Columns() )->register();
			( new Admin\Assets() )->register();
			( new Admin\Settings() )->register();
			( new Admin\Tools() )->register();
			( new Admin\Notices() )->register();
			( new Admin\ShortcodeHelper() )->register();
			( new Admin\DemoLibrary() )->register();
		}
	}
}
