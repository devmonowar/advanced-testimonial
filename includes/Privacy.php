<?php
/**
 * Privacy compliance for the front-end submission form.
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial;

use AdvancedTestimonial\Admin\CPT;

defined( 'ABSPATH' ) || exit;

/**
 * Exposes the submitter data stored by [at_form] (name in the post title,
 * email in post meta) to WordPress's privacy tools, and documents it on the
 * privacy policy guide page. Without this, an erasure request has no way to
 * reach testimonial data.
 */
final class Privacy {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_exporter' ) );
		add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_eraser' ) );
		add_action( 'admin_init', array( $this, 'policy_content' ) );
	}

	/**
	 * Register the exporter.
	 *
	 * @param array $exporters Existing exporters.
	 * @return array
	 */
	public function register_exporter( $exporters ) {
		$exporters['advanced-testimonial'] = array(
			'exporter_friendly_name' => __( 'Testimonials', 'advanced-testimonial' ),
			'callback'               => array( $this, 'export' ),
		);

		return $exporters;
	}

	/**
	 * Register the eraser.
	 *
	 * @param array $erasers Existing erasers.
	 * @return array
	 */
	public function register_eraser( $erasers ) {
		$erasers['advanced-testimonial'] = array(
			'eraser_friendly_name' => __( 'Testimonials', 'advanced-testimonial' ),
			'callback'             => array( $this, 'erase' ),
		);

		return $erasers;
	}

	/**
	 * Find testimonials submitted with an email address.
	 *
	 * @param string $email_address Requester's email.
	 * @return \WP_Post[]
	 */
	private function find_by_email( $email_address ) {
		if ( '' === $email_address ) {
			return array();
		}

		return get_posts(
			array(
				'post_type'   => CPT::POST_TYPE,
				'post_status' => 'any',
				'numberposts' => -1,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- privacy requests are rare, correctness beats speed here.
				'meta_query'  => array(
					array(
						'key'   => Helpers::meta_key( 'email' ),
						'value' => $email_address,
					),
				),
			)
		);
	}

	/**
	 * Export a submitter's testimonials.
	 *
	 * @param string $email_address Requester's email.
	 * @return array Response with export data and done flag.
	 */
	public function export( $email_address ) {
		$data = array();

		foreach ( $this->find_by_email( $email_address ) as $post ) {
			$data[] = array(
				'group_id'    => 'advanced-testimonial',
				'group_label' => __( 'Testimonials', 'advanced-testimonial' ),
				'item_id'     => 'testimonial-' . $post->ID,
				'data'        => array(
					array(
						'name'  => __( 'Name', 'advanced-testimonial' ),
						'value' => $post->post_title,
					),
					array(
						'name'  => __( 'Email', 'advanced-testimonial' ),
						'value' => (string) get_post_meta( $post->ID, Helpers::meta_key( 'email' ), true ),
					),
					array(
						'name'  => __( 'Review', 'advanced-testimonial' ),
						'value' => $post->post_content,
					),
					array(
						'name'  => __( 'Submitted', 'advanced-testimonial' ),
						'value' => $post->post_date,
					),
				),
			);
		}

		return array(
			'data' => $data,
			'done' => true,
		);
	}

	/**
	 * Anonymize a submitter's testimonials (name + email removed, review kept).
	 *
	 * @param string $email_address Requester's email.
	 * @return array Response with removal counts and done flag.
	 */
	public function erase( $email_address ) {
		$removed  = 0;
		$retained = 0;

		foreach ( $this->find_by_email( $email_address ) as $post ) {
			// The review text itself is the site's content; only the identity
			// attached to it is personal data.
			delete_post_meta( $post->ID, Helpers::meta_key( 'email' ) );

			wp_update_post(
				array(
					'ID'         => $post->ID,
					'post_title' => __( 'Anonymous', 'advanced-testimonial' ),
				)
			);

			++$removed;
		}

		return array(
			'items_removed'  => $removed,
			'items_retained' => $retained,
			'messages'       => array(),
			'done'           => true,
		);
	}

	/**
	 * Suggest policy text describing what the form stores.
	 *
	 * @return void
	 */
	public function policy_content() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}

		wp_add_privacy_policy_content(
			'Advanced Testimonial',
			wp_kses_post(
				__( 'When you submit a testimonial through our form, your name, email address (optional) and review text are stored as a testimonial on this site until you ask us to remove them. The email is never displayed publicly; it is only used to verify your submission.', 'advanced-testimonial' )
			)
		);
	}
}
