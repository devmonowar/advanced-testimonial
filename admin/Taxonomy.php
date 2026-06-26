<?php
/**
 * Registers the testimonial group taxonomy.
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * The hierarchical "testimonial_group" taxonomy.
 */
final class Taxonomy {

	/**
	 * Taxonomy key.
	 */
	const TAXONOMY = 'testimonial_group';

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_taxonomy' ) );
	}

	/**
	 * Register the taxonomy.
	 *
	 * @return void
	 */
	public function register_taxonomy() {
		$labels = array(
			'name'              => _x( 'Groups', 'Taxonomy general name', 'advanced-testimonial' ),
			'singular_name'     => _x( 'Group', 'Taxonomy singular name', 'advanced-testimonial' ),
			'menu_name'         => __( 'Groups', 'advanced-testimonial' ),
			'all_items'         => __( 'All Groups', 'advanced-testimonial' ),
			'edit_item'         => __( 'Edit Group', 'advanced-testimonial' ),
			'view_item'         => __( 'View Group', 'advanced-testimonial' ),
			'update_item'       => __( 'Update Group', 'advanced-testimonial' ),
			'add_new_item'      => __( 'Add New Group', 'advanced-testimonial' ),
			'new_item_name'     => __( 'New Group Name', 'advanced-testimonial' ),
			'parent_item'       => __( 'Parent Group', 'advanced-testimonial' ),
			'parent_item_colon' => __( 'Parent Group:', 'advanced-testimonial' ),
			'search_items'      => __( 'Search Groups', 'advanced-testimonial' ),
			'not_found'         => __( 'No groups found.', 'advanced-testimonial' ),
		);

		$args = array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array(
				'slug'       => 'testimonial-group',
				'with_front' => false,
			),
		);

		register_taxonomy( self::TAXONOMY, array( CPT::POST_TYPE ), $args );
	}
}
