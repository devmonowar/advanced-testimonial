<?php
/**
 * Registers the testimonial custom post type.
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial\Admin;

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
		add_filter( 'enter_title_here', array( $this, 'title_placeholder' ), 10, 2 );
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

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_rest'       => true,
			'has_archive'        => false,
			'publicly_queryable' => true,
			'menu_position'      => 26,
			'menu_icon'          => 'dashicons-format-quote',
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author' ),
			'rewrite'            => array(
				'slug'       => 'testimonial',
				'with_front' => false,
			),
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
		);

		register_post_type( self::POST_TYPE, $args );
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
