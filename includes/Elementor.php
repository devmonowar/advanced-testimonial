<?php
/**
 * Elementor integration — registers the Advanced Testimonial widget and its
 * category. Everything here is hook-based, so it is inert when Elementor is
 * not installed (the actions simply never fire).
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial;

defined( 'ABSPATH' ) || exit;

/**
 * Wires the plugin into Elementor.
 */
final class Elementor {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'elementor/widgets/register', array( $this, 'register_widget' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );
	}

	/**
	 * Register the widget with Elementor's widgets manager.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 * @return void
	 */
	public function register_widget( $widgets_manager ) {
		// Elementor_Widget extends \Elementor\Widget_Base, so it is only loaded
		// here — when Elementor is guaranteed to be present.
		$widgets_manager->register( new Elementor_Widget() );
	}

	/**
	 * Add a dedicated widget category.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager.
	 * @return void
	 */
	public function register_category( $elements_manager ) {
		$elements_manager->add_category(
			'advanced-testimonial',
			array(
				'title' => __( 'Advanced Testimonial', 'advanced-testimonial' ),
				'icon'  => 'eicon-testimonial',
			)
		);
	}
}
