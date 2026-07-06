<?php
/**
 * Elementor widget for Advanced Testimonial.
 *
 * Loaded only from Elementor::register_widget() (i.e. when Elementor is active),
 * so the `extends \Elementor\Widget_Base` never fatals on sites without it.
 * Controls mirror the block/shortcode; render() hands off to the shared Renderer.
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial;

use AdvancedTestimonial\Admin\Taxonomy;
use AdvancedTestimonial\Frontend\Assets;
use AdvancedTestimonial\Frontend\Renderer;
use Elementor\Controls_Manager;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

/**
 * Advanced Testimonial Elementor widget.
 */
class Elementor_Widget extends Widget_Base {

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'advanced-testimonial';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Advanced Testimonial', 'advanced-testimonial' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-testimonial';
	}

	/**
	 * Widget categories.
	 *
	 * @return array<int,string>
	 */
	public function get_categories() {
		return array( 'advanced-testimonial' );
	}

	/**
	 * Search keywords.
	 *
	 * @return array<int,string>
	 */
	public function get_keywords() {
		return array( 'testimonial', 'review', 'carousel', 'slider', 'quote', 'social proof' );
	}

	/**
	 * Frontend/editor style dependencies.
	 *
	 * @return array<int,string>
	 */
	public function get_style_depends() {
		Assets::register_style();
		return array( Assets::STYLE );
	}

	/**
	 * Frontend/editor script dependencies.
	 *
	 * @return array<int,string>
	 */
	public function get_script_depends() {
		Assets::register_script();
		return array( Assets::SCRIPT_CAROUSEL );
	}

	/**
	 * Register widget controls.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_layout',
			array(
				'label' => __( 'Layout', 'advanced-testimonial' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Heading (optional)', 'advanced-testimonial' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
			)
		);

		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'advanced-testimonial' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => array(
					'grid'      => __( 'Grid', 'advanced-testimonial' ),
					'list'      => __( 'List', 'advanced-testimonial' ),
					'card'      => __( 'Card', 'advanced-testimonial' ),
					'carousel'  => __( 'Carousel', 'advanced-testimonial' ),
					'marquee'   => __( 'Marquee', 'advanced-testimonial' ),
					'masonry'   => __( 'Masonry', 'advanced-testimonial' ),
					'spotlight' => __( 'Spotlight', 'advanced-testimonial' ),
				),
			)
		);

		$this->add_control(
			'group',
			array(
				'label'   => __( 'Group', 'advanced-testimonial' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => $this->group_options(),
			)
		);

		$this->add_control(
			'columns',
			array(
				'label'   => __( 'Columns', 'advanced-testimonial' ),
				'type'    => Controls_Manager::SLIDER,
				'range'   => array(
					'px' => array(
						'min' => 1,
						'max' => 6,
					),
				),
				'default' => array( 'size' => 3 ),
			)
		);

		$this->add_control(
			'limit',
			array(
				'label'   => __( 'Number to show', 'advanced-testimonial' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 9,
				'min'     => 1,
				'max'     => 100,
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => __( 'Order by', 'advanced-testimonial' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'date',
				'options' => array(
					'date'   => __( 'Date', 'advanced-testimonial' ),
					'rating' => __( 'Rating', 'advanced-testimonial' ),
					'title'  => __( 'Title', 'advanced-testimonial' ),
				),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'   => __( 'Order', 'advanced-testimonial' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'desc',
				'options' => array(
					'desc'   => __( 'Descending (newest / highest first)', 'advanced-testimonial' ),
					'asc'    => __( 'Ascending (oldest / lowest first)', 'advanced-testimonial' ),
					'random' => __( 'Random', 'advanced-testimonial' ),
				),
			)
		);

		$this->add_control(
			'verified_only',
			array(
				'label'        => __( 'Verified only', 'advanced-testimonial' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'autoplay',
			array(
				'label'       => __( 'Autoplay seconds (0 = off)', 'advanced-testimonial' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 0,
				'min'         => 0,
				'max'         => 15,
				'description' => __( 'Carousel and Spotlight only.', 'advanced-testimonial' ),
				'condition'   => array( 'layout' => array( 'carousel', 'spotlight' ) ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_marquee',
			array(
				'label'     => __( 'Marquee', 'advanced-testimonial' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array( 'layout' => 'marquee' ),
			)
		);

		$this->add_control(
			'speed',
			array(
				'label'   => __( 'Scroll speed', 'advanced-testimonial' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''       => __( 'Default (from settings)', 'advanced-testimonial' ),
					'slow'   => __( 'Slow', 'advanced-testimonial' ),
					'normal' => __( 'Normal', 'advanced-testimonial' ),
					'fast'   => __( 'Fast', 'advanced-testimonial' ),
				),
			)
		);

		$this->add_control(
			'card_width',
			array(
				'label'   => __( 'Card width (px, 0 = default)', 'advanced-testimonial' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 0,
				'min'     => 0,
				'max'     => 600,
			)
		);

		$this->add_control(
			'direction',
			array(
				'label'   => __( 'Direction', 'advanced-testimonial' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''      => __( 'Default (from settings)', 'advanced-testimonial' ),
					'left'  => __( 'Right to left', 'advanced-testimonial' ),
					'right' => __( 'Left to right', 'advanced-testimonial' ),
				),
			)
		);

		$this->add_control(
			'fade',
			array(
				'label'        => __( 'Edge fade', 'advanced-testimonial' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_display',
			array(
				'label' => __( 'Display', 'advanced-testimonial' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$toggles = array(
			'show_rating'      => __( 'Show rating', 'advanced-testimonial' ),
			'show_image'       => __( 'Show client photo', 'advanced-testimonial' ),
			'show_company'     => __( 'Show company', 'advanced-testimonial' ),
			'show_designation' => __( 'Show designation', 'advanced-testimonial' ),
			'show_location'    => __( 'Show location', 'advanced-testimonial' ),
			'show_date'        => __( 'Show date', 'advanced-testimonial' ),
			'show_verified'    => __( 'Show verified badge', 'advanced-testimonial' ),
			'show_website'     => __( 'Show website button', 'advanced-testimonial' ),
			'show_headline'    => __( 'Show headline', 'advanced-testimonial' ),
		);

		foreach ( $toggles as $key => $label ) {
			$this->add_control(
				$key,
				array(
					'label'        => $label,
					'type'         => Controls_Manager::SWITCHER,
					'default'      => ( 'show_date' === $key ) ? '' : 'yes',
					'return_value' => 'yes',
				)
			);
		}

		$this->add_control(
			'show_filter',
			array(
				'label'        => __( 'Group filter tabs', 'advanced-testimonial' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'read_more',
			array(
				'label'        => __( 'Truncate long reviews (Read more)', 'advanced-testimonial' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'load_more',
			array(
				'label'        => __( 'Reveal in batches (Load more)', 'advanced-testimonial' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Group options for the select control.
	 *
	 * @return array<string,string>
	 */
	private function group_options() {
		$options = array( '' => __( 'All groups', 'advanced-testimonial' ) );

		$terms = get_terms(
			array(
				'taxonomy'   => Taxonomy::TAXONOMY,
				'hide_empty' => false,
			)
		);

		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->slug ] = $term->name;
			}
		}

		return $options;
	}

	/**
	 * Render the widget on the frontend and in the editor.
	 *
	 * @return void
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		$is_on = static function ( $key, $fallback ) use ( $s ) {
			return isset( $s[ $key ] ) ? ( 'yes' === $s[ $key ] ) : $fallback;
		};

		$columns = isset( $s['columns']['size'] ) ? (int) $s['columns']['size'] : 3;

		$atts = array(
			'title'            => isset( $s['title'] ) ? $s['title'] : '',
			'layout'           => isset( $s['layout'] ) ? $s['layout'] : 'grid',
			'group'            => isset( $s['group'] ) ? $s['group'] : '',
			'columns'          => $columns,
			'limit'            => isset( $s['limit'] ) ? (int) $s['limit'] : 9,
			'order'            => isset( $s['order'] ) ? $s['order'] : 'desc',
			'orderby'          => isset( $s['orderby'] ) ? $s['orderby'] : 'date',
			'verified'         => $is_on( 'verified_only', false ),
			'autoplay'         => isset( $s['autoplay'] ) ? (int) $s['autoplay'] : 0,
			'speed'            => isset( $s['speed'] ) ? $s['speed'] : '',
			'card_width'       => isset( $s['card_width'] ) ? (int) $s['card_width'] : 0,
			'direction'        => isset( $s['direction'] ) ? $s['direction'] : '',
			'fade'             => $is_on( 'fade', true ),
			'show_rating'      => $is_on( 'show_rating', true ),
			'show_image'       => $is_on( 'show_image', true ),
			'show_company'     => $is_on( 'show_company', true ),
			'show_designation' => $is_on( 'show_designation', true ),
			'show_location'    => $is_on( 'show_location', true ),
			'show_date'        => $is_on( 'show_date', false ),
			'show_verified'    => $is_on( 'show_verified', true ),
			'show_website'     => $is_on( 'show_website', true ),
			'show_headline'    => $is_on( 'show_headline', true ),
			'show_filter'      => $is_on( 'show_filter', false ),
			'read_more'        => $is_on( 'read_more', false ),
			'load_more'        => $is_on( 'load_more', false ),
		);

		echo Renderer::render( $atts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Renderer returns prepared, escaped markup.
	}
}
