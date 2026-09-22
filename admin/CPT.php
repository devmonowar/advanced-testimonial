<?php
/**
 * Registers the testimonial custom post type.
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial\Admin;

use AdvancedTestimonial\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * The "testimonial" post type.
 */
final class CPT {

	/**
	 * Post type key.
	 */
	const POST_TYPE = 'testimonial';

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_meta' ) );
		add_filter( 'enter_title_here', array( $this, 'title_placeholder' ), 10, 2 );
		add_filter( 'wp_robots', array( $this, 'noindex_single' ) );
		add_action( 'update_option_' . Settings::OPTION, array( $this, 'flush_on_single_pages_change' ), 10, 2 );
	}

	/**
	 * Register the post type.
	 *
	 * @return void
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Testimonials', 'Post type general name', 'advanced-testimonial' ),
			'singular_name'         => _x( 'Testimonial', 'Post type singular name', 'advanced-testimonial' ),
			'menu_name'             => _x( 'Testimonials', 'Admin Menu text', 'advanced-testimonial' ),
			'name_admin_bar'        => _x( 'Testimonial', 'Add New on Toolbar', 'advanced-testimonial' ),
			'add_new'               => __( 'Add New', 'advanced-testimonial' ),
			'add_new_item'          => __( 'Add New Testimonial', 'advanced-testimonial' ),
			'new_item'              => __( 'New Testimonial', 'advanced-testimonial' ),
			'edit_item'             => __( 'Edit Testimonial', 'advanced-testimonial' ),
			'view_item'             => __( 'View Testimonial', 'advanced-testimonial' ),
			'all_items'             => __( 'All Testimonials', 'advanced-testimonial' ),
			'search_items'          => __( 'Search Testimonials', 'advanced-testimonial' ),
			'not_found'             => __( 'No testimonials found.', 'advanced-testimonial' ),
			'not_found_in_trash'    => __( 'No testimonials found in Trash.', 'advanced-testimonial' ),
			'featured_image'        => __( 'Client Photo', 'advanced-testimonial' ),
			'set_featured_image'    => __( 'Set client photo', 'advanced-testimonial' ),
			'remove_featured_image' => __( 'Remove client photo', 'advanced-testimonial' ),
			'use_featured_image'    => __( 'Use as client photo', 'advanced-testimonial' ),
			'item_published'        => __( 'Testimonial published.', 'advanced-testimonial' ),
			'item_updated'          => __( 'Testimonial updated.', 'advanced-testimonial' ),
		);

		// Standalone testimonial pages are thin duplicates of the text already
		// shown wherever the owner places them — and they leak into site
		// search. `public` stays true so the admin UI and existing permalinks
		// keep working; only the front-end queryability goes when the owner
		// leaves single pages off.
		$single_pages = (bool) Settings::get( 'single_pages', 0 );

		$args = array(
			'labels'              => $labels,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'has_archive'         => false,
			'publicly_queryable'  => $single_pages,
			'exclude_from_search' => ! $single_pages,
			'menu_position'       => 26,
			'menu_icon'           => 'dashicons-testimonial',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author' ),
			'rewrite'             => $single_pages ? array(
				'slug'       => 'testimonial',
				'with_front' => false,
			) : false,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
		);

		register_post_type( self::POST_TYPE, $args );
	}

	/**
	 * Expose the testimonial meta to the block editor and the REST API.
	 *
	 * The post type is show_in_rest, but without this none of the _at_* fields
	 * travel with it — same field list and same sanitizers as the meta boxes,
	 * so the editor and the classic screen can never disagree.
	 *
	 * @return void
	 */
	public function register_meta() {
		$rest_types = array(
			'text'     => 'string',
			'url'      => 'string',
			'email'    => 'string',
			'date'     => 'string',
			'checkbox' => 'string',
			'rating'   => 'number',
			'media'    => 'integer',
		);

		foreach ( MetaBoxes::fields() as $name => $field ) {
			$type = isset( $rest_types[ $field['type'] ] ) ? $rest_types[ $field['type'] ] : 'string';

			register_post_meta(
				self::POST_TYPE,
				Helpers::meta_key( $name ),
				array(
					'show_in_rest'      => true,
					'single'            => true,
					'type'              => $type,
					'sanitize_callback' => function ( $value ) use ( $field ) {
						return MetaBoxes::sanitize_value( $field['type'], $value );
					},
					'auth_callback'     => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}

	/**
	 * Keep single testimonial views out of search results when the owner
	 * keeps them on — they chose the URLs, but the pages are still thin.
	 *
	 * @param array<string,string> $robots Current robots directives.
	 * @return array<string,string>
	 */
	public function noindex_single( $robots ) {
		if ( is_singular( self::POST_TYPE ) && Settings::get( 'single_pages', 0 ) ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
		}

		return $robots;
	}

	/**
	 * Toggling single pages changes rewrite rules — flush once on change.
	 *
	 * @param mixed $old_value Previous settings array.
	 * @param mixed $value     New settings array.
	 * @return void
	 */
	public function flush_on_single_pages_change( $old_value, $value ) {
		$old = is_array( $old_value ) && ! empty( $old_value['single_pages'] );
		$new = is_array( $value ) && ! empty( $value['single_pages'] );

		if ( $old !== $new ) {
			flush_rewrite_rules();
		}
	}

	/**
	 * Use a testimonial-friendly placeholder for the title field.
	 *
	 * @param string   $text Default placeholder text.
	 * @param \WP_Post $post Current post.
	 * @return string
	 */
	public function title_placeholder( $text, $post ) {
		if ( isset( $post->post_type ) && self::POST_TYPE === $post->post_type ) {
			return __( 'Client name', 'advanced-testimonial' );
		}

		return $text;
	}
}
