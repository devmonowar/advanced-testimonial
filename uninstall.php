<?php
/**
 * Uninstall handler — runs when the plugin is deleted from the WordPress admin.
 *
 * Only plugin options are removed here. Testimonial posts and groups are user
 * content and are intentionally preserved.
 *
 * @package AdvancedTestimonial
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$advanced_testimonial_settings = get_option( 'advanced_testimonial_settings' );

// Only remove plugin data when the user opted in via the Advanced settings.
if ( is_array( $advanced_testimonial_settings ) && ! empty( $advanced_testimonial_settings['delete_data'] ) ) {
	delete_option( 'advanced_testimonial_version' );
	delete_option( 'advanced_testimonial_settings' );
}
