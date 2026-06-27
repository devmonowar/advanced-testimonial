<?php
/**
 * Click-to-copy shortcode helpers in the admin list tables.
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Adds copyable shortcodes to the Groups list and the testimonial row actions.
 */
final class ShortcodeHelper {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'manage_edit-' . Taxonomy::TAXONOMY . '_columns', array( $this, 'group_columns' ) );
		add_filter( 'manage_' . Taxonomy::TAXONOMY . '_custom_column', array( $this, 'group_column' ), 10, 3 );
		add_filter( 'post_row_actions', array( $this, 'row_action' ), 10, 2 );
	}

	/**
	 * Add a Shortcode column to the Groups list.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function group_columns( $columns ) {
		$ordered = array();

		if ( isset( $columns['cb'] ) ) {
			$ordered['cb'] = $columns['cb'];
		}
		if ( isset( $columns['name'] ) ) {
			$ordered['name'] = $columns['name'];
		}
		$ordered['at_shortcode'] = __( 'Shortcode', 'advanced-testimonial' );
		if ( isset( $columns['posts'] ) ) {
			$ordered['posts'] = $columns['posts'];
		}
		if ( isset( $columns['slug'] ) ) {
			$ordered['slug'] = $columns['slug'];
		}
		if ( isset( $columns['description'] ) ) {
			$ordered['description'] = $columns['description'];
		}

		// Preserve any other columns added by third parties.
		foreach ( $columns as $key => $label ) {
			if ( ! isset( $ordered[ $key ] ) ) {
				$ordered[ $key ] = $label;
			}
		}

		return $ordered;
	}

	/**
	 * Render the group shortcode column.
	 *
	 * @param string $content Column HTML.
	 * @param string $column  Column key.
	 * @param int    $term_id Term ID.
	 * @return string
	 */
	public function group_column( $content, $column, $term_id ) {
		if ( 'at_shortcode' !== $column ) {
			return $content;
		}

		$term = get_term( $term_id, Taxonomy::TAXONOMY );
		if ( $term && ! is_wp_error( $term ) ) {
			$content = self::copy_code( '[advanced_testimonial group="' . $term->slug . '"]' );
		}

		return $content;
	}

	/**
	 * Add a "Copy shortcode" row action to testimonials (single-item shortcode).
	 *
	 * @param array    $actions Existing actions.
	 * @param \WP_Post $post    Post.
	 * @return array
	 */
	public function row_action( $actions, $post ) {
		if ( CPT::POST_TYPE === $post->post_type ) {
			$shortcode          = '[advanced_testimonial ids="' . (int) $post->ID . '"]';
			$actions['at_copy'] = '<a href="#" class="at-copy" data-clipboard="' . esc_attr( $shortcode ) . '">' . esc_html__( 'Copy shortcode', 'advanced-testimonial' ) . '</a>';
		}

		return $actions;
	}

	/**
	 * Build a click-to-copy <code> element.
	 *
	 * @param string $shortcode Shortcode text.
	 * @return string
	 */
	public static function copy_code( $shortcode ) {
		return '<code class="at-copy" data-clipboard="' . esc_attr( $shortcode ) . '" title="' . esc_attr__( 'Click to copy', 'advanced-testimonial' ) . '">' . esc_html( $shortcode ) . '</code>';
	}
}
