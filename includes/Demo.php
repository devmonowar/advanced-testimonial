<?php
/**
 * Demo import / export engine.
 *
 * Imports and exports testimonials, groups and (optionally) settings as a
 * portable JSON structure. Used by the Tools tab and the one-click starter demo.
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial;

use AdvancedTestimonial\Admin\CPT;
use AdvancedTestimonial\Admin\MetaBoxes;
use AdvancedTestimonial\Admin\Settings;
use AdvancedTestimonial\Admin\Taxonomy;
use AdvancedTestimonial\Frontend\Query;

defined( 'ABSPATH' ) || exit;

/**
 * Stateless demo import/export helpers.
 */
final class Demo {

	/**
	 * Schema marker stored in exported files.
	 */
	const SCHEMA = 'advanced-testimonial-demo';

	/**
	 * Build an export data array.
	 *
	 * @param array $include_parts Which parts to include: testimonials, groups, settings.
	 * @return array
	 */
	public static function export( $include_parts ) {
		$data = array(
			'schema'       => self::SCHEMA,
			'plugin'       => 'advanced-testimonial',
			'version'      => ADVANCED_TESTIMONIAL_VERSION,
			'groups'       => array(),
			'testimonials' => array(),
		);

		if ( ! empty( $include_parts['groups'] ) ) {
			$terms = get_terms(
				array(
					'taxonomy'   => Taxonomy::TAXONOMY,
					'hide_empty' => false,
				)
			);
			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$data['groups'][] = array(
						'name' => $term->name,
						'slug' => $term->slug,
					);
				}
			}
		}

		if ( ! empty( $include_parts['testimonials'] ) ) {
			$posts = get_posts(
				array(
					'post_type'   => CPT::POST_TYPE,
					'post_status' => 'publish',
					'numberposts' => -1,
				)
			);

			foreach ( $posts as $post ) {
				$meta = array();
				foreach ( array_keys( MetaBoxes::fields() ) as $key ) {
					$value = get_post_meta( $post->ID, Helpers::meta_key( $key ), true );
					if ( '' !== $value ) {
						$meta[ $key ] = $value;
					}
				}

				$groups = wp_get_object_terms( $post->ID, Taxonomy::TAXONOMY, array( 'fields' => 'slugs' ) );
				$image  = '';
				$thumb  = get_post_thumbnail_id( $post->ID );
				if ( $thumb ) {
					$image = (string) wp_get_attachment_url( $thumb );
				}

				$data['testimonials'][] = array(
					'title'   => $post->post_title,
					'content' => $post->post_content,
					'meta'    => $meta,
					'groups'  => is_wp_error( $groups ) ? array() : $groups,
					'image'   => $image,
				);
			}
		}

		if ( ! empty( $include_parts['settings'] ) ) {
			$data['settings'] = Settings::get_settings();
		}

		return $data;
	}

	/**
	 * Import a demo data array.
	 *
	 * @param array  $data        Decoded demo data.
	 * @param string $strategy    How to handle existing testimonials: append|skip|replace.
	 * @param string $bundled_dir Optional local dir to resolve non-URL image filenames.
	 * @return array Counts: groups, testimonials, images, skipped.
	 */
	public static function import( array $data, $strategy = 'append', $bundled_dir = '' ) {
		$result   = array(
			'groups'       => 0,
			'testimonials' => 0,
			'images'       => 0,
			'skipped'      => 0,
		);
		$strategy = in_array( $strategy, array( 'append', 'skip', 'replace' ), true ) ? $strategy : 'append';

		$group_map = array();
		if ( ! empty( $data['groups'] ) && is_array( $data['groups'] ) ) {
			foreach ( $data['groups'] as $group ) {
				$name = isset( $group['name'] ) ? sanitize_text_field( $group['name'] ) : '';
				$slug = isset( $group['slug'] ) ? sanitize_title( $group['slug'] ) : sanitize_title( $name );
				if ( '' === $slug ) {
					continue;
				}
				$term = get_term_by( 'slug', $slug, Taxonomy::TAXONOMY );
				if ( $term ) {
					$group_map[ $slug ] = (int) $term->term_id;
				} else {
					$new = wp_insert_term( '' !== $name ? $name : $slug, Taxonomy::TAXONOMY, array( 'slug' => $slug ) );
					if ( ! is_wp_error( $new ) ) {
						$group_map[ $slug ] = (int) $new['term_id'];
						++$result['groups'];
					}
				}
			}
		}

		$meta_fields = MetaBoxes::fields();

		if ( ! empty( $data['testimonials'] ) && is_array( $data['testimonials'] ) ) {
			foreach ( $data['testimonials'] as $item ) {
				$title = isset( $item['title'] ) ? sanitize_text_field( $item['title'] ) : '';
				if ( '' === $title ) {
					continue;
				}

				$existing = self::find_by_title( $title );
				if ( $existing && 'skip' === $strategy ) {
					++$result['skipped'];
					continue;
				}

				$postarr = array(
					'post_type'    => CPT::POST_TYPE,
					'post_status'  => 'publish',
					'post_title'   => $title,
					'post_content' => isset( $item['content'] ) ? wp_kses_post( $item['content'] ) : '',
				);

				if ( $existing && 'replace' === $strategy ) {
					$postarr['ID'] = $existing;
					$post_id       = wp_update_post( $postarr, true );
				} else {
					$post_id = wp_insert_post( $postarr, true );
				}

				if ( ! $post_id || is_wp_error( $post_id ) ) {
					continue;
				}
				++$result['testimonials'];

				if ( ! empty( $item['meta'] ) && is_array( $item['meta'] ) ) {
					foreach ( $item['meta'] as $key => $value ) {
						if ( ! isset( $meta_fields[ $key ] ) ) {
							continue;
						}
						update_post_meta( $post_id, Helpers::meta_key( $key ), self::sanitize_meta( $meta_fields[ $key ]['type'], $value ) );
					}
				}

				if ( ! empty( $item['groups'] ) && is_array( $item['groups'] ) ) {
					$ids = array();
					foreach ( $item['groups'] as $gslug ) {
						$gslug = sanitize_title( $gslug );
						if ( isset( $group_map[ $gslug ] ) ) {
							$ids[] = $group_map[ $gslug ];
						} else {
							$term = get_term_by( 'slug', $gslug, Taxonomy::TAXONOMY );
							if ( $term ) {
								$ids[] = (int) $term->term_id;
							}
						}
					}
					if ( $ids ) {
						wp_set_object_terms( $post_id, $ids, Taxonomy::TAXONOMY );
					}
				}

				if ( ! empty( $item['image'] ) ) {
					$attachment = self::sideload( (string) $item['image'], $post_id, $bundled_dir );
					if ( $attachment ) {
						set_post_thumbnail( $post_id, $attachment );
						++$result['images'];
					}
				}
			}
		}

		if ( ! empty( $data['settings'] ) && is_array( $data['settings'] ) ) {
			update_option( Settings::OPTION, ( new Settings() )->sanitize( $data['settings'] ) );
		}

		Query::bust_cache();

		return $result;
	}

	/**
	 * Import the bundled starter demo (idempotent — skips existing titles).
	 *
	 * @return array|\WP_Error
	 */
	public static function import_starter() {
		$file = ADVANCED_TESTIMONIAL_DIR . 'demo/starter.json';
		if ( ! is_readable( $file ) ) {
			return new \WP_Error( 'missing', __( 'Starter demo file is missing.', 'advanced-testimonial' ) );
		}

		$data = wp_json_file_decode( $file, array( 'associative' => true ) );
		if ( ! is_array( $data ) ) {
			return new \WP_Error( 'invalid', __( 'Starter demo file is invalid.', 'advanced-testimonial' ) );
		}

		return self::import( $data, 'skip', ADVANCED_TESTIMONIAL_DIR . 'demo/images' );
	}

	/**
	 * Find a testimonial by exact title.
	 *
	 * @param string $title Title.
	 * @return int Post ID or 0.
	 */
	private static function find_by_title( $title ) {
		$query = new \WP_Query(
			array(
				'post_type'      => CPT::POST_TYPE,
				'title'          => $title,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);

		return $query->posts ? (int) $query->posts[0] : 0;
	}

	/**
	 * Sanitize a meta value by field type (mirrors the meta box rules).
	 *
	 * @param string $type  Field type.
	 * @param mixed  $value Raw value.
	 * @return mixed
	 */
	private static function sanitize_meta( $type, $value ) {
		switch ( $type ) {
			case 'rating':
				return Helpers::clamp_rating( $value );
			case 'media':
				return absint( $value );
			case 'checkbox':
				return ( '1' === (string) $value || true === $value ) ? '1' : '';
			case 'url':
				return esc_url_raw( (string) $value );
			case 'email':
				return sanitize_email( (string) $value );
			case 'date':
				$date = sanitize_text_field( (string) $value );
				return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ? $date : '';
			default:
				return sanitize_text_field( (string) $value );
		}
	}

	/**
	 * Sideload an image (remote URL or bundled local filename) onto a post.
	 *
	 * @param string $image       URL or filename.
	 * @param int    $post_id     Attach to this post.
	 * @param string $bundled_dir Local dir for non-URL filenames.
	 * @return int Attachment ID or 0.
	 */
	private static function sideload( $image, $post_id, $bundled_dir ) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		if ( preg_match( '#^https?://#i', $image ) ) {
			$tmp = download_url( $image );
			if ( is_wp_error( $tmp ) ) {
				return 0;
			}
			$name = basename( (string) wp_parse_url( $image, PHP_URL_PATH ) );
		} else {
			$source = $bundled_dir ? trailingslashit( $bundled_dir ) . basename( $image ) : '';
			if ( '' === $source || ! file_exists( $source ) ) {
				return 0;
			}
			$tmp = wp_tempnam( basename( $image ) );
			if ( ! $tmp || ! @copy( $source, $tmp ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- best-effort copy of a bundled file.
				return 0;
			}
			$name = basename( $image );
		}

		$file       = array(
			'name'     => $name,
			'tmp_name' => $tmp,
		);
		$attachment = media_handle_sideload( $file, $post_id );

		if ( is_wp_error( $attachment ) ) {
			if ( file_exists( $tmp ) ) {
				wp_delete_file( $tmp );
			}
			return 0;
		}

		return (int) $attachment;
	}
}
