<?php
/**
 * Uninstall handler — runs only when the plugin is DELETED from wp-admin.
 *
 * Deactivating never deletes anything. When (and only when) the user has
 * enabled "Remove all plugin data when deleting the plugin" in
 * Settings → Advanced, this removes every trace of the plugin:
 * testimonials, groups, post meta, options and transients.
 *
 * @package AdvancedTestimonial
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$advanced_testimonial_settings = get_option( 'advanced_testimonial_settings' );

// Safe default: do nothing unless the user opted in.
if ( ! is_array( $advanced_testimonial_settings ) || empty( $advanced_testimonial_settings['delete_data'] ) ) {
	return;
}

// Register minimally so the post/term APIs work during uninstall (the plugin's
// own init hooks do not run at this point).
register_post_type( 'testimonial' );
register_taxonomy( 'testimonial_group', 'testimonial' );

// 1. Delete all testimonials. Forcing the delete also removes their post meta.
$advanced_testimonial_ids = get_posts(
	array(
		'post_type'              => 'testimonial',
		'post_status'            => 'any',
		'numberposts'            => -1,
		'fields'                 => 'ids',
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	)
);

foreach ( $advanced_testimonial_ids as $advanced_testimonial_id ) {
	wp_delete_post( $advanced_testimonial_id, true );
}

// 2. Delete all groups (terms).
$advanced_testimonial_terms = get_terms(
	array(
		'taxonomy'   => 'testimonial_group',
		'hide_empty' => false,
		'fields'     => 'ids',
	)
);

if ( is_array( $advanced_testimonial_terms ) ) {
	foreach ( $advanced_testimonial_terms as $advanced_testimonial_term_id ) {
		wp_delete_term( $advanced_testimonial_term_id, 'testimonial_group' );
	}
}

// 3. Delete options.
delete_option( 'advanced_testimonial_version' );
delete_option( 'advanced_testimonial_settings' );
delete_option( 'advanced_testimonial_debug' );
delete_option( 'advanced_testimonial_cache_ver' );

// 4. Delete transients (query cache + one-time tools notices).
global $wpdb;
$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- one-off cleanup on uninstall; transients are not cacheable here.
	"DELETE FROM {$wpdb->options}
	 WHERE option_name LIKE '\_transient\_at\_q\_%'
	    OR option_name LIKE '\_transient\_timeout\_at\_q\_%'
	    OR option_name LIKE '\_transient\_advanced\_testimonial\_%'
	    OR option_name LIKE '\_transient\_timeout\_advanced\_testimonial\_%'"
);
