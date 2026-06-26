<?php
/**
 * Builds (and optionally caches) the testimonial query.
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial\Frontend;

use AdvancedTestimonial\Admin\CPT;
use AdvancedTestimonial\Admin\Taxonomy;
use AdvancedTestimonial\Admin\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Translates display attributes into a WP_Query.
 */
final class Query {

	/**
	 * Option key holding the cache-busting version counter.
	 */
	const CACHE_VERSION_OPTION = 'advanced_testimonial_cache_ver';

	/**
	 * Build a WP_Query for the given attributes.
	 *
	 * @param array $atts Normalized display attributes.
	 * @return \WP_Query
	 */
	public function get( array $atts ) {
		$args = $this->build_args( $atts );

		/**
		 * Filter the testimonial query arguments.
		 *
		 * @param array $args WP_Query args.
		 * @param array $atts Display attributes.
		 */
		$args = apply_filters( 'advanced_testimonial_query_args', $args, $atts );

		if ( empty( $atts['ids'] ) && ! empty( $atts['cache'] ) ) {
			return $this->cached_query( $args, $atts );
		}

		return new \WP_Query( $args );
	}

	/**
	 * Assemble the base query arguments.
	 *
	 * @param array $atts Normalized attributes.
	 * @return array
	 */
	private function build_args( array $atts ) {
		$args = array(
			'post_type'           => CPT::POST_TYPE,
			'post_status'         => 'publish',
			'posts_per_page'      => (int) $atts['limit'],
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		);

		if ( 'random' === $atts['order'] ) {
			$args['orderby'] = 'rand';
		} else {
			$args['orderby'] = $atts['orderby'];
			$args['order']   = ( 'asc' === $atts['order'] ) ? 'ASC' : 'DESC';
		}

		if ( ! empty( $atts['ids'] ) ) {
			$args['post__in'] = $atts['ids'];
			if ( 'random' !== $atts['order'] ) {
				$args['orderby'] = 'post__in';
			}
			$args['posts_per_page'] = count( $atts['ids'] );
		}

		if ( ! empty( $atts['group'] ) ) {
			$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- intentional, scoped display.
				array(
					'taxonomy' => Taxonomy::TAXONOMY,
					'field'    => is_numeric( $atts['group'] ) ? 'term_id' : 'slug',
					'terms'    => $atts['group'],
				),
			);
		}

		return $args;
	}

	/**
	 * Run the query through a transient cache (post IDs only).
	 *
	 * @param array $args Base query args.
	 * @param array $atts Display attributes.
	 * @return \WP_Query
	 */
	private function cached_query( array $args, array $atts ) {
		$version = (int) get_option( self::CACHE_VERSION_OPTION, 1 );
		$key     = 'at_q_' . $version . '_' . md5( (string) wp_json_encode( $args ) );
		$ids     = get_transient( $key );

		if ( false === $ids ) {
			$id_args           = $args;
			$id_args['fields'] = 'ids';
			$id_query          = new \WP_Query( $id_args );
			$ids               = array_map( 'intval', $id_query->posts );
			set_transient( $key, $ids, HOUR_IN_SECONDS );
		}

		if ( empty( $ids ) ) {
			return new \WP_Query(
				array(
					'post_type' => CPT::POST_TYPE,
					'post__in'  => array( 0 ),
				)
			);
		}

		return new \WP_Query(
			array(
				'post_type'           => CPT::POST_TYPE,
				'post_status'         => 'publish',
				'post__in'            => $ids,
				'orderby'             => ( 'random' === $atts['order'] ) ? 'rand' : 'post__in',
				'posts_per_page'      => count( $ids ),
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
			)
		);
	}

	/**
	 * Invalidate all cached testimonial queries by bumping the version counter.
	 *
	 * @return void
	 */
	public static function bust_cache() {
		$version = (int) get_option( self::CACHE_VERSION_OPTION, 1 );
		update_option( self::CACHE_VERSION_OPTION, $version + 1, false );
	}
}
