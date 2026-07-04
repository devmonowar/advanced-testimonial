<?php
/**
 * Custom admin list columns for testimonials.
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial\Admin;

use AdvancedTestimonial\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Adds photo, company and rating columns to the testimonial list table.
 */
final class Columns {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'manage_' . CPT::POST_TYPE . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . CPT::POST_TYPE . '_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
	}

	/**
	 * Define the columns and their order.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function columns( $columns ) {
		$new = array();

		foreach ( $columns as $key => $label ) {
			if ( 'title' === $key ) {
				$new['at_photo'] = __( 'Photo', 'advanced-testimonial' );
			}

			$new[ $key ] = $label;

			if ( 'title' === $key ) {
				$new['at_company'] = __( 'Company', 'advanced-testimonial' );
				$new['at_rating']  = __( 'Rating', 'advanced-testimonial' );
			}
		}

		return $new;
	}

	/**
	 * Render the content of a custom column.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_column( $column, $post_id ) {
		switch ( $column ) {
			case 'at_photo':
				if ( has_post_thumbnail( $post_id ) ) {
					echo get_the_post_thumbnail( $post_id, array( 48, 48 ), array( 'class' => 'at-col-photo' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core returns safe markup.
				} else {
					echo '<span class="at-col-photo at-col-photo--empty" aria-hidden="true">&#9786;</span>';
				}
				break;

			case 'at_company':
				$company = get_post_meta( $post_id, Helpers::meta_key( 'company' ), true );
				echo $company ? esc_html( $company ) : '<span aria-hidden="true">&mdash;</span>';
				break;

			case 'at_rating':
				$rating = Helpers::clamp_rating( get_post_meta( $post_id, Helpers::meta_key( 'rating' ), true ) );
				$stars  = Helpers::stars_text( $rating );

				if ( '' === $stars ) {
					echo '<span aria-hidden="true">&mdash;</span>';
				} else {
					$rating_disp = rtrim( rtrim( number_format( $rating, 1 ), '0' ), '.' );
					/* translators: %s: rating out of 5, e.g. 4.5. */
					echo '<span class="at-col-rating" title="' . esc_attr( sprintf( __( '%s out of 5', 'advanced-testimonial' ), $rating_disp ) ) . '">' . esc_html( $stars ) . '</span>';
				}
				break;
		}
	}
}
