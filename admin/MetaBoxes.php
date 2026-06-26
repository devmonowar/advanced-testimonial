<?php
/**
 * Testimonial detail meta box.
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial\Admin;

use AdvancedTestimonial\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Renders and saves the testimonial detail fields.
 */
final class MetaBoxes {

	/**
	 * Nonce action.
	 */
	const NONCE_ACTION = 'advanced_testimonial_save_meta';

	/**
	 * Nonce request field.
	 */
	const NONCE_NAME = 'advanced_testimonial_meta_nonce';

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post_' . CPT::POST_TYPE, array( $this, 'save' ), 10, 2 );
	}

	/**
	 * Field schema. Shared so the renderer and block can reuse it.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function fields() {
		return array(
			'company'         => array(
				'label' => __( 'Company', 'advanced-testimonial' ),
				'type'  => 'text',
			),
			'designation'     => array(
				'label' => __( 'Designation / Job Title', 'advanced-testimonial' ),
				'type'  => 'text',
			),
			'location'        => array(
				'label' => __( 'Location', 'advanced-testimonial' ),
				'type'  => 'text',
			),
			'website'         => array(
				'label' => __( 'Website', 'advanced-testimonial' ),
				'type'  => 'url',
			),
			'email'           => array(
				'label' => __( 'Email (optional)', 'advanced-testimonial' ),
				'type'  => 'email',
			),
			'rating'          => array(
				'label' => __( 'Rating', 'advanced-testimonial' ),
				'type'  => 'rating',
			),
			'review_date'     => array(
				'label' => __( 'Review Date', 'advanced-testimonial' ),
				'type'  => 'date',
			),
			'verified'        => array(
				'label' => __( 'Verified Customer', 'advanced-testimonial' ),
				'type'  => 'checkbox',
			),
			'company_logo'    => array(
				'label' => __( 'Company Logo', 'advanced-testimonial' ),
				'type'  => 'media',
			),
			'social_twitter'  => array(
				'label' => __( 'X / Twitter URL', 'advanced-testimonial' ),
				'type'  => 'url',
			),
			'social_linkedin' => array(
				'label' => __( 'LinkedIn URL', 'advanced-testimonial' ),
				'type'  => 'url',
			),
			'social_facebook' => array(
				'label' => __( 'Facebook URL', 'advanced-testimonial' ),
				'type'  => 'url',
			),
		);
	}

	/**
	 * Register the meta box.
	 *
	 * @return void
	 */
	public function add_meta_box() {
		add_meta_box(
			'advanced-testimonial-details',
			__( 'Testimonial Details', 'advanced-testimonial' ),
			array( $this, 'render' ),
			CPT::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Render the meta box.
	 *
	 * @param \WP_Post $post Current post.
	 * @return void
	 */
	public function render( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		echo '<p class="at-meta-intro description">' . esc_html__( 'The post title is the client name, the editor is the review text, and the featured image is the client photo.', 'advanced-testimonial' ) . '</p>';
		echo '<div class="at-meta-grid">';

		foreach ( self::fields() as $name => $field ) {
			$value = get_post_meta( $post->ID, Helpers::meta_key( $name ), true );
			$id    = 'at-field-' . $name;

			echo '<div class="at-meta-row at-meta-type-' . esc_attr( $field['type'] ) . '">';
			echo '<label class="at-meta-label" for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label>';
			echo '<div class="at-meta-control">';

			$this->render_control( $name, $field, $value, $id );

			echo '</div>';
			echo '</div>';
		}

		echo '</div>';
	}

	/**
	 * Render a single field control.
	 *
	 * @param string $name  Field name.
	 * @param array  $field Field definition.
	 * @param mixed  $value Stored value.
	 * @param string $id    Element id.
	 * @return void
	 */
	private function render_control( $name, $field, $value, $id ) {
		$key = Helpers::meta_key( $name );

		switch ( $field['type'] ) {
			case 'rating':
				echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '">';
				echo '<option value="0"' . selected( (int) $value, 0, false ) . '>' . esc_html__( 'No rating', 'advanced-testimonial' ) . '</option>';
				for ( $i = 1; $i <= 5; $i++ ) {
					echo '<option value="' . esc_attr( $i ) . '"' . selected( (int) $value, $i, false ) . '>' . esc_html( sprintf( /* translators: %d: number of stars. */ _n( '%d star', '%d stars', $i, 'advanced-testimonial' ), $i ) ) . '</option>';
				}
				echo '</select>';
				break;

			case 'checkbox':
				echo '<label class="at-meta-checkbox"><input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="1"' . checked( $value, '1', false ) . ' /> ' . esc_html__( 'Mark this customer as verified', 'advanced-testimonial' ) . '</label>';
				break;

			case 'media':
				$attachment_id = (int) $value;
				$preview       = $attachment_id ? wp_get_attachment_image( $attachment_id, 'thumbnail', false, array( 'class' => 'at-media-preview-img' ) ) : '';
				echo '<div class="at-media-field">';
				echo '<input type="hidden" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $attachment_id ) . '" />';
				echo '<div class="at-media-preview">' . $preview . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() returns safe markup.
				echo '<button type="button" class="button at-media-select">' . esc_html__( 'Select Logo', 'advanced-testimonial' ) . '</button> ';
				echo '<button type="button" class="button-link at-media-remove"' . ( $attachment_id ? '' : ' style="display:none"' ) . '>' . esc_html__( 'Remove', 'advanced-testimonial' ) . '</button>';
				echo '</div>';
				break;

			case 'url':
				echo '<input type="url" class="regular-text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" placeholder="https://" />';
				break;

			case 'email':
				echo '<input type="email" class="regular-text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
				break;

			case 'date':
				echo '<input type="date" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
				break;

			default:
				echo '<input type="text" class="regular-text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
				break;
		}
	}

	/**
	 * Persist the submitted values.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @return void
	 */
	public function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		foreach ( self::fields() as $name => $field ) {
			$key       = Helpers::meta_key( $name );
			$raw       = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized per field type in sanitize_value().
			$sanitized = $this->sanitize_value( $field['type'], $raw );

			if ( '' === $sanitized || ( 'media' === $field['type'] && 0 === $sanitized ) || ( 'checkbox' === $field['type'] && '' === $sanitized ) ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $sanitized );
			}
		}
	}

	/**
	 * Sanitize a value according to its field type.
	 *
	 * @param string $type Field type.
	 * @param mixed  $raw  Raw submitted value.
	 * @return mixed
	 */
	private function sanitize_value( $type, $raw ) {
		switch ( $type ) {
			case 'rating':
				return Helpers::clamp_rating( $raw );

			case 'media':
				return absint( $raw );

			case 'checkbox':
				return ( '1' === (string) $raw ) ? '1' : '';

			case 'url':
				return esc_url_raw( trim( (string) $raw ) );

			case 'email':
				return sanitize_email( (string) $raw );

			case 'date':
				$date = sanitize_text_field( (string) $raw );
				return ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) ? $date : '';

			default:
				return sanitize_text_field( (string) $raw );
		}
	}
}
