<?php
/**
 * Gutenberg block registration (no-build, dynamic/server-rendered).
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial\Blocks;

use AdvancedTestimonial\Admin\Taxonomy;
use AdvancedTestimonial\Frontend\Assets;
use AdvancedTestimonial\Frontend\Renderer;
use AdvancedTestimonial\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the Advanced Testimonial block and renders it server-side.
 */
final class Block {

	/**
	 * Editor script handle.
	 */
	const EDITOR_HANDLE = 'advanced-testimonial-editor';

	/**
	 * Block name.
	 */
	const BLOCK = 'advanced-testimonial/testimonials';

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Register the editor script and the block type.
	 *
	 * @return void
	 */
	public function register_block() {
		wp_register_script(
			self::EDITOR_HANDLE,
			ADVANCED_TESTIMONIAL_URL . 'blocks/editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n' ),
			Helpers::asset_version( 'blocks/editor.js' ),
			true
		);

		wp_set_script_translations( self::EDITOR_HANDLE, 'advanced-testimonial' );

		wp_localize_script(
			self::EDITOR_HANDLE,
			'advancedTestimonialBlock',
			array(
				'groups' => $this->group_options(),
			)
		);

		// Register the frontend stylesheet handle so the block's style /
		// editorStyle (referenced in block.json) load on the frontend and
		// inside the editor iframe.
		Assets::register_style();

		register_block_type(
			ADVANCED_TESTIMONIAL_DIR . 'blocks',
			array( 'render_callback' => array( $this, 'render' ) )
		);
	}

	/**
	 * Server-side render callback.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public function render( $attributes ) {
		$attributes = is_array( $attributes ) ? $attributes : array();

		$align = isset( $attributes['align'] ) ? $attributes['align'] : '';
		$width = in_array( $align, array( 'wide', 'full' ), true ) ? $align : '';

		$atts = array(
			'title'            => isset( $attributes['title'] ) ? $attributes['title'] : '',
			'layout'           => isset( $attributes['layout'] ) ? $attributes['layout'] : 'grid',
			'group'            => isset( $attributes['group'] ) ? $attributes['group'] : '',
			'columns'          => isset( $attributes['columns'] ) ? (int) $attributes['columns'] : 3,
			'limit'            => isset( $attributes['limit'] ) ? (int) $attributes['limit'] : 9,
			'order'            => isset( $attributes['order'] ) ? $attributes['order'] : 'desc',
			'orderby'          => isset( $attributes['orderby'] ) ? $attributes['orderby'] : 'date',
			'verified'         => (bool) ( $attributes['verifiedOnly'] ?? false ),
			'autoplay'         => isset( $attributes['autoplay'] ) ? (int) $attributes['autoplay'] : 0,
			'width'            => $width,
			'show_rating'      => (bool) ( $attributes['showRating'] ?? true ),
			'show_image'       => (bool) ( $attributes['showImage'] ?? true ),
			'show_company'     => (bool) ( $attributes['showCompany'] ?? true ),
			'show_designation' => (bool) ( $attributes['showDesignation'] ?? true ),
			'show_location'    => (bool) ( $attributes['showLocation'] ?? true ),
			'show_date'        => (bool) ( $attributes['showDate'] ?? false ),
			'show_verified'    => (bool) ( $attributes['showVerified'] ?? true ),
			'show_website'     => (bool) ( $attributes['showWebsite'] ?? true ),
			'show_headline'    => (bool) ( $attributes['showHeadline'] ?? true ),
			'show_filter'      => (bool) ( $attributes['showFilter'] ?? false ),
			'read_more'        => (bool) ( $attributes['readMore'] ?? false ),
			'load_more'        => (bool) ( $attributes['loadMore'] ?? false ),
			'speed'            => isset( $attributes['speed'] ) ? (string) $attributes['speed'] : '',
			'card_width'       => isset( $attributes['cardWidth'] ) ? (int) $attributes['cardWidth'] : 0,
			'fade'             => (bool) ( $attributes['fade'] ?? true ),
			'direction'        => isset( $attributes['direction'] ) ? (string) $attributes['direction'] : '',
		);

		return Renderer::render( $atts );
	}

	/**
	 * Build group options for the editor select control.
	 *
	 * @return array<int,array<string,string>>
	 */
	private function group_options() {
		$options = array(
			array(
				'value' => '',
				'label' => __( 'All groups', 'advanced-testimonial' ),
			),
		);

		$terms = get_terms(
			array(
				'taxonomy'   => Taxonomy::TAXONOMY,
				'hide_empty' => false,
			)
		);

		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[] = array(
					'value' => $term->slug,
					'label' => $term->name,
				);
			}
		}

		return $options;
	}
}
