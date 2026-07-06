<?php
/**
 * Frontend renderer — turns attributes into testimonial markup.
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial\Frontend;

use AdvancedTestimonial\Admin\Settings;
use AdvancedTestimonial\Admin\Taxonomy;
use AdvancedTestimonial\Admin\Tools;
use AdvancedTestimonial\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Builds the testimonial output for shortcodes and blocks.
 */
final class Renderer {

	/**
	 * Allowed layouts.
	 */
	const LAYOUTS = array( 'grid', 'list', 'card', 'carousel', 'marquee', 'masonry', 'spotlight' );

	/**
	 * Running instance counter for unique ids.
	 *
	 * @var int
	 */
	private static $instances = 0;

	/**
	 * Default display attributes.
	 *
	 * @return array<string,mixed>
	 */
	public static function defaults() {
		return array(
			'layout'           => 'grid',
			'title'            => '',
			'width'            => '',
			'columns'          => 3,
			'limit'            => 9,
			'group'            => '',
			'ids'              => '',
			'order'            => 'desc',
			'orderby'          => 'date',
			'verified'         => false,
			'show_rating'      => true,
			'show_image'       => true,
			'show_company'     => true,
			'show_designation' => true,
			'show_location'    => true,
			'show_date'        => false,
			'show_verified'    => true,
			'show_website'     => true,
			'show_headline'    => true,
			'show_video'       => true,
			'show_filter'      => false,
			'read_more'        => false,
			'load_more'        => false,
			'autoplay'         => 0,
			'speed'            => '',
			'card_width'       => 0,
			'fade'             => true,
			'direction'        => '',
		);
	}

	/**
	 * Render testimonials and return the HTML string.
	 *
	 * @param array $atts Raw display attributes.
	 * @return string
	 */
	public static function render( $atts ) {
		$atts = self::normalize( (array) $atts );

		$query = ( new Query() )->get( $atts );

		if ( ! $query->have_posts() ) {
			return self::empty_message( $atts );
		}

		$items = array();
		foreach ( $query->posts as $post ) {
			$items[] = self::build_item( $post, $atts );
		}

		$loader          = new TemplateLoader();
		$layout_template = $loader->locate( $atts['layout'] );
		if ( '' === $layout_template ) {
			$layout_template = $loader->locate( 'grid' );
		}
		$item_template = $loader->locate( 'item' );

		Assets::enqueue_front( $atts );

		// Video lightbox script: only when this output actually contains one.
		foreach ( $items as $it ) {
			if ( ! empty( $it['video'] ) ) {
				Assets::enqueue_video();
				break;
			}
		}

		++self::$instances;

		$data = array(
			'testimonials'  => $items,
			'atts'          => $atts,
			'schema'        => (bool) Settings::get( 'enable_schema' ),
			'item_template' => $item_template,
			'instance'      => 'at-' . self::$instances,
			'wrapper_class' => self::wrapper_class( $atts ),
			'wrapper_style' => self::wrapper_style( $atts ),
			'filter_bar'    => self::filter_bar_html( $items, $atts ),
			'load_more_bar' => self::load_more_html( $items, $atts ),
		);

		$html = $loader->render( $layout_template, $data );

		/**
		 * Filter the final testimonial HTML.
		 *
		 * @param string $html Rendered markup.
		 * @param array  $atts Display attributes.
		 */
		return apply_filters( 'advanced_testimonial_output', $html, $atts );
	}

	/**
	 * Normalize raw attributes into a clean, typed array.
	 *
	 * @param array $atts Raw attributes.
	 * @return array
	 */
	public static function normalize( array $atts ) {
		$atts = wp_parse_args( $atts, self::defaults() );

		$atts['layout'] = in_array( $atts['layout'], self::LAYOUTS, true ) ? $atts['layout'] : 'grid';

		$atts['title'] = sanitize_text_field( (string) $atts['title'] );

		$width         = strtolower( (string) $atts['width'] );
		$atts['width'] = in_array( $width, array( 'wide', 'full' ), true ) ? $width : '';

		$atts['columns'] = (int) $atts['columns'];
		if ( $atts['columns'] < 1 ) {
			$atts['columns'] = 1;
		}
		if ( $atts['columns'] > 6 ) {
			$atts['columns'] = 6;
		}

		$atts['limit'] = (int) $atts['limit'];
		if ( 0 === $atts['limit'] ) {
			$atts['limit'] = -1;
		}

		$order           = strtolower( (string) $atts['order'] );
		$atts['order']   = in_array( $order, array( 'asc', 'desc', 'random' ), true ) ? $order : 'desc';
		$orderby         = strtolower( (string) $atts['orderby'] );
		$atts['orderby'] = in_array( $orderby, array( 'date', 'title', 'menu_order', 'rand', 'rating' ), true ) ? $orderby : 'date';

		$atts['group'] = sanitize_text_field( (string) $atts['group'] );

		if ( ! empty( $atts['ids'] ) && ! is_array( $atts['ids'] ) ) {
			$atts['ids'] = array_filter( array_map( 'absint', explode( ',', (string) $atts['ids'] ) ) );
		} elseif ( empty( $atts['ids'] ) ) {
			$atts['ids'] = array();
		}

		foreach ( array( 'show_rating', 'show_image', 'show_company', 'show_designation', 'show_location', 'show_date', 'show_verified', 'show_website', 'show_headline', 'show_video', 'show_filter', 'fade', 'read_more', 'load_more', 'verified' ) as $flag ) {
			$atts[ $flag ] = self::to_bool( $atts[ $flag ] );
		}

		$atts['autoplay'] = absint( $atts['autoplay'] );

		// Marquee scroll speed and card width: a per-instance value overrides the
		// global setting; an empty/zero value inherits the Styles defaults.
		$speed_map    = array(
			'slow'   => 25,
			'normal' => 45,
			'fast'   => 75,
		);
		$speed_preset = strtolower( (string) $atts['speed'] );
		if ( ! isset( $speed_map[ $speed_preset ] ) ) {
			$speed_preset = (string) Settings::get( 'marquee_speed', 'normal' );
		}
		$atts['marquee_speed'] = isset( $speed_map[ $speed_preset ] ) ? $speed_map[ $speed_preset ] : 45;

		$card_width = (int) $atts['card_width'];
		if ( $card_width <= 0 ) {
			$card_width = (int) Settings::get( 'marquee_width', 340 );
		}
		$atts['marquee_item'] = max( 200, min( 600, $card_width ) );

		$direction = strtolower( (string) $atts['direction'] );
		if ( ! in_array( $direction, array( 'left', 'right' ), true ) ) {
			$direction = strtolower( (string) Settings::get( 'marquee_direction', 'left' ) );
		}
		$atts['direction'] = in_array( $direction, array( 'left', 'right' ), true ) ? $direction : 'left';

		// Load more: how many items to show initially / reveal per click.
		$atts['_lm_initial'] = ! empty( $atts['load_more'] ) ? max( 1, (int) Settings::get( 'load_more_count', 6 ) ) : 0;

		$atts['cache'] = (bool) Settings::get( 'cache_queries' ) && ! get_option( Tools::DEBUG_OPTION );

		return $atts;
	}

	/**
	 * Cast a loose value to boolean.
	 *
	 * @param mixed $value Value.
	 * @return bool
	 */
	private static function to_bool( $value ) {
		if ( is_bool( $value ) ) {
			return $value;
		}

		return in_array( strtolower( (string) $value ), array( '1', 'true', 'yes', 'on' ), true );
	}

	/**
	 * Build a normalized data array for one testimonial.
	 *
	 * @param \WP_Post $post Post object.
	 * @param array    $atts Display attributes.
	 * @return array
	 */
	private static function build_item( $post, array $atts ) {
		$id   = $post->ID;
		$meta = function ( $field ) use ( $id ) {
			return get_post_meta( $id, Helpers::meta_key( $field ), true );
		};

		$rating = Helpers::clamp_rating( $meta( 'rating' ) );
		if ( $rating <= 0 ) {
			$rating = Helpers::clamp_rating( Settings::get( 'default_rating', 0 ) );
		}

		$socials = array_filter(
			array(
				'twitter'  => $meta( 'social_twitter' ),
				'linkedin' => $meta( 'social_linkedin' ),
				'facebook' => $meta( 'social_facebook' ),
			)
		);

		$item = array(
			'id'          => $id,
			'name'        => get_the_title( $id ),
			'headline'    => (string) $meta( 'headline' ),
			'review'      => $post->post_content,
			'rating'      => $rating,
			'company'     => (string) $meta( 'company' ),
			'designation' => (string) $meta( 'designation' ),
			'location'    => (string) $meta( 'location' ),
			'website'     => (string) $meta( 'website' ),
			'email'       => (string) $meta( 'email' ),
			'verified'    => '1' === $meta( 'verified' ),
			'date'        => (string) $meta( 'review_date' ),
			'photo'       => $atts['show_image'] ? self::avatar_html( $post, $atts ) : '',
			'logo'        => self::logo_html( (int) $meta( 'company_logo' ) ),
			'stars'       => self::stars_html( $rating ),
			'socials'     => $socials,
			'groups'      => self::item_groups( $id ),
			'video'       => $atts['show_video'] ? self::video_html( $post, (string) $meta( 'video' ) ) : '',
		);

		/**
		 * Filter a single testimonial's prepared data.
		 *
		 * @param array    $item Prepared data.
		 * @param \WP_Post $post Post object.
		 * @param array    $atts Display attributes.
		 */
		return apply_filters( 'advanced_testimonial_item_data', $item, $post, $atts );
	}

	/**
	 * Groups (taxonomy terms) a testimonial belongs to, as slug => name.
	 *
	 * @param int $id Post ID.
	 * @return array<string,string>
	 */
	private static function item_groups( $id ) {
		$terms = get_the_terms( $id, Taxonomy::TAXONOMY );
		$out   = array();

		if ( is_array( $terms ) ) {
			foreach ( $terms as $term ) {
				$out[ $term->slug ] = $term->name;
			}
		}

		return $out;
	}

	/**
	 * Build the group-filter tab bar. Returns an empty string unless filtering
	 * is enabled, the layout is a non-slider grid type, and the displayed items
	 * span at least two distinct groups (otherwise there is nothing to filter).
	 *
	 * @param array $items Prepared testimonial items.
	 * @param array $atts  Display attributes.
	 * @return string
	 */
	private static function filter_bar_html( array $items, array $atts ) {
		$filterable = array( 'grid', 'list', 'card', 'masonry' );

		if ( empty( $atts['show_filter'] ) || ! in_array( $atts['layout'], $filterable, true ) ) {
			return '';
		}

		$groups = array();
		foreach ( $items as $item ) {
			if ( empty( $item['groups'] ) ) {
				continue;
			}
			foreach ( $item['groups'] as $slug => $name ) {
				if ( ! isset( $groups[ $slug ] ) ) {
					$groups[ $slug ] = $name;
				}
			}
		}

		if ( count( $groups ) < 2 ) {
			return '';
		}

		$out  = '<div class="at-filter" role="group" aria-label="' . esc_attr__( 'Filter testimonials by group', 'advanced-testimonial' ) . '">';
		$out .= '<button type="button" class="at-filter__btn is-active" data-at-filter="*" aria-pressed="true">' . esc_html__( 'All', 'advanced-testimonial' ) . '</button>';
		foreach ( $groups as $slug => $name ) {
			$out .= '<button type="button" class="at-filter__btn" data-at-filter="' . esc_attr( $slug ) . '" aria-pressed="false">' . esc_html( $name ) . '</button>';
		}
		$out .= '</div>';

		return $out;
	}

	/**
	 * Build the "Load more" button. Returns an empty string unless load-more is
	 * enabled, the layout is a non-slider grid type, and there are more items
	 * than the initial batch.
	 *
	 * @param array $items Prepared testimonial items.
	 * @param array $atts  Display attributes.
	 * @return string
	 */
	private static function load_more_html( array $items, array $atts ) {
		$ok      = array( 'grid', 'list', 'card', 'masonry' );
		$initial = (int) $atts['_lm_initial'];

		if ( empty( $atts['load_more'] ) || ! in_array( $atts['layout'], $ok, true ) || $initial < 1 || count( $items ) <= $initial ) {
			return '';
		}

		return '<div class="at-loadmore-wrap"><button type="button" class="at-loadmore" data-at-loadmore data-batch="' . esc_attr( $initial ) . '">' . esc_html__( 'Load more', 'advanced-testimonial' ) . '</button></div>';
	}

	/**
	 * Build the client avatar markup (featured image or initial fallback).
	 *
	 * @param \WP_Post $post Post object.
	 * @param array    $atts Display attributes.
	 * @return string
	 */
	private static function avatar_html( $post, array $atts ) {
		$size = (string) Settings::get( 'image_size', 'medium' );
		$lazy = (bool) Settings::get( 'lazy_load', 1 );

		if ( has_post_thumbnail( $post ) ) {
			return get_the_post_thumbnail(
				$post,
				$size,
				array(
					'class'   => 'at-avatar__img',
					'alt'     => esc_attr( get_the_title( $post ) ),
					'loading' => $lazy ? 'lazy' : 'eager',
				)
			);
		}

		$initial = function_exists( 'mb_substr' ) ? mb_substr( get_the_title( $post ), 0, 1 ) : substr( get_the_title( $post ), 0, 1 );

		return '<span class="at-avatar__fallback" aria-hidden="true">' . esc_html( $initial ) . '</span>';
	}

	/**
	 * Build the company logo markup.
	 *
	 * @param int $logo_id Attachment ID.
	 * @return string
	 */
	private static function logo_html( $logo_id ) {
		if ( ! $logo_id ) {
			return '';
		}

		return wp_get_attachment_image(
			$logo_id,
			'medium',
			false,
			array(
				'class'   => 'at-logo__img',
				'loading' => 'lazy',
				'alt'     => '',
			)
		);
	}

	/**
	 * Build the video thumbnail + play button markup.
	 *
	 * The actual player (iframe / <video>) is only created by video.js when
	 * the visitor clicks play, so nothing external loads on page render.
	 *
	 * @param \WP_Post $post Post object.
	 * @param string   $url  Raw video URL from post meta.
	 * @return string Empty string when there is no (valid) video.
	 */
	private static function video_html( $post, $url ) {
		$video = Helpers::parse_video( $url );

		if ( null === $video ) {
			return '';
		}

		// Thumbnail: the parser's own (YouTube) or the client photo as fallback.
		$thumb = $video['thumbnail'];
		if ( '' === $thumb && has_post_thumbnail( $post ) ) {
			$thumb = (string) wp_get_attachment_image_url( get_post_thumbnail_id( $post ), 'large' );
		}

		$thumb_html = $thumb
			? '<img class="at-video__thumb" src="' . esc_url( $thumb ) . '" alt="" loading="lazy" />'
			: '<span class="at-video__thumb at-video__thumb--placeholder"></span>';

		/* translators: %s: client name. */
		$label = sprintf( __( 'Play video testimonial from %s', 'advanced-testimonial' ), get_the_title( $post ) );

		return '<div class="at-video" data-at-video-type="' . esc_attr( $video['type'] ) . '" data-at-video-src="' . esc_url( $video['embed'] ) . '">'
			. '<button type="button" class="at-video__play" aria-label="' . esc_attr( $label ) . '">'
			. $thumb_html
			. '<span class="at-video__btn" aria-hidden="true"><svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M8 5.14v13.72c0 .8.87 1.3 1.56.88l10.54-6.86a1.05 1.05 0 0 0 0-1.76L9.56 4.26A1.04 1.04 0 0 0 8 5.14Z"/></svg></span>'
			. '</button>'
			. '</div>';
	}

	/**
	 * Build accessible star-rating markup (supports half stars, e.g. 4.5).
	 *
	 * @param mixed $rating Rating 0-5 (0.5 steps).
	 * @return string
	 */
	public static function stars_html( $rating ) {
		$rating = Helpers::clamp_rating( $rating );

		if ( $rating <= 0 ) {
			return '';
		}

		$display = rtrim( rtrim( number_format( $rating, 1 ), '0' ), '.' );
		/* translators: %s: rating value out of five, e.g. 4.5. */
		$label = sprintf( __( 'Rated %s out of 5', 'advanced-testimonial' ), $display );

		$icons = array(
			'star'  => 'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z',
			'heart' => 'M12 21s-7.55-4.74-10.05-9.2C.43 8.5 2.02 5 5.5 5c2.04 0 3.3 1.18 4.5 2.6C11.2 6.18 12.46 5 14.5 5c3.48 0 5.07 3.5 3.55 6.8C19.55 16.26 12 21 12 21z',
		);
		$icon  = (string) Settings::get( 'star_icon', 'star' );
		$path  = isset( $icons[ $icon ] ) ? $icons[ $icon ] : $icons['star'];

		$svg = static function ( $cls ) use ( $path ) {
			return '<svg class="' . esc_attr( $cls ) . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="' . esc_attr( $path ) . '"/></svg>';
		};

		$full = (int) floor( $rating );
		$half = ( $rating - $full ) >= 0.5;

		$out = '<div class="at-stars" role="img" aria-label="' . esc_attr( $label ) . '">';
		for ( $i = 1; $i <= 5; $i++ ) {
			if ( $i <= $full ) {
				$out .= $svg( 'at-star is-filled' );
			} elseif ( $half && $i === $full + 1 ) {
				$out .= '<span class="at-star at-star--half">' . $svg( 'at-star__bg' ) . $svg( 'at-star__fg' ) . '</span>';
			} else {
				$out .= $svg( 'at-star is-empty' );
			}
		}
		$out .= '</div>';

		return $out;
	}

	/**
	 * CSS classes for the outer wrapper.
	 *
	 * @param array $atts Display attributes.
	 * @return string
	 */
	private static function wrapper_class( array $atts ) {
		$classes = array(
			'at-wrapper',
			'at-layout-' . $atts['layout'],
			'at-shadow-' . sanitize_html_class( (string) Settings::get( 'card_shadow', 'soft' ) ),
			'at-btn-' . sanitize_html_class( (string) Settings::get( 'button_style', 'filled' ) ),
			'at-avatar-' . sanitize_html_class( (string) Settings::get( 'avatar_shape', 'circle' ) ),
		);

		if ( '' !== $atts['width'] ) {
			$classes[] = 'at-width-' . $atts['width'];
		}

		if ( Settings::get( 'enable_rtl' ) || is_rtl() ) {
			$classes[] = 'at-rtl';
		}

		return implode( ' ', $classes );
	}

	/**
	 * Inline CSS custom properties built from the style settings.
	 *
	 * @param array $atts Display attributes.
	 * @return string
	 */
	private static function wrapper_style( array $atts ) {
		$shadows    = array(
			'none'   => 'none',
			'soft'   => '0 2px 10px rgba(0,0,0,.06)',
			'medium' => '0 6px 20px rgba(0,0,0,.10)',
			'strong' => '0 12px 30px rgba(0,0,0,.16)',
		);
		$shadow_key = (string) Settings::get( 'card_shadow', 'soft' );

		$vars = array(
			'--at-primary' => (string) Settings::get( 'primary_color', '#2563eb' ),
			'--at-accent'  => (string) Settings::get( 'accent_color', '#f59e0b' ),
			'--at-text'    => (string) Settings::get( 'text_color', '#1f2937' ),
			'--at-radius'  => (int) Settings::get( 'border_radius', 12 ) . 'px',
			'--at-gap'     => (int) Settings::get( 'spacing', 24 ) . 'px',
			'--at-columns' => (int) $atts['columns'],
			'--at-shadow'  => isset( $shadows[ $shadow_key ] ) ? $shadows[ $shadow_key ] : $shadows['soft'],
			'--at-clamp'   => max( 2, (int) Settings::get( 'readmore_lines', 4 ) ),
		);

		$style = '';
		foreach ( $vars as $name => $value ) {
			$style .= $name . ':' . $value . ';';
		}

		return $style;
	}

	/**
	 * Markup shown when there are no testimonials to display.
	 *
	 * @param array $atts Display attributes.
	 * @return string
	 */
	private static function empty_message( array $atts ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return '';
		}

		return '<p class="at-empty">' . esc_html__( 'No testimonials found for the selected options.', 'advanced-testimonial' ) . '</p>';
	}
}
