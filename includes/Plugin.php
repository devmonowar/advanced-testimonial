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
		// No load_plugin_textdomain() call: WordPress.org ships translations to
		// wp-content/languages/plugins/ and loads them on demand, and Plugin
		// Check flags the call as discouraged. Note the plugin's own /languages
		// folder holds the .pot only — WordPress does not scan it, so a bundled
		// .mo would need that call back.

		// Data layer — registered on both admin and frontend.
		( new Admin\CPT() )->register();
		( new Admin\Taxonomy() )->register();
		Admin\Settings::register_cache();

		// Update migrations run after the CPT exists (priority 20), so the
		// one-time rewrite flush rebuilds rules from the new registration.
		add_action( 'init', array( Activator::class, 'maybe_migrate' ), 20 );

		// Frontend output layer.
		( new Frontend\Assets() )->register();
		( new Frontend\Form() )->register();
		( new Shortcode\Shortcode() )->register();
		( new Blocks\Block() )->register();

		// Elementor widget (registers only when Elementor is active).
		( new Elementor() )->register();

		// Invalidate cached queries whenever testimonials change.
		add_action( 'save_post_' . Admin\CPT::POST_TYPE, array( Frontend\Query::class, 'bust_cache' ) );
		add_action( 'deleted_post', array( Frontend\Query::class, 'bust_cache' ) );

		// Group edits move testimonials in and out of a cached ID set too.
		add_action( 'created_' . Admin\Taxonomy::TAXONOMY, array( Frontend\Query::class, 'bust_cache' ) );
		add_action( 'edited_' . Admin\Taxonomy::TAXONOMY, array( Frontend\Query::class, 'bust_cache' ) );
		add_action( 'delete_' . Admin\Taxonomy::TAXONOMY, array( Frontend\Query::class, 'bust_cache' ) );

		// Privacy tools must see submitter data (export + erasure + policy).
		( new Privacy() )->register();

		// Admin-only modules.
		if ( is_admin() ) {
			( new Admin\MetaBoxes() )->register();
			( new Admin\Columns() )->register();
			( new Admin\Assets() )->register();
			( new Admin\Settings() )->register();
			( new Admin\Tools() )->register();
			( new Admin\Notices() )->register();
			( new Admin\ReviewNotice() )->register();
			( new Admin\ShortcodeHelper() )->register();
			( new Admin\DemoLibrary() )->register();
		}
	}
}
