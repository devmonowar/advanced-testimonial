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

// Register minimally so the post/term APIs work during uninstall (the plugin's
// own init hooks do not run at this point).
register_post_type( 'testimonial' );
register_taxonomy( 'testimonial_group', 'testimonial' );

/**
 * Remove every trace of the plugin from the current site.
 *
 * Each site keeps its own settings, so the opt-in is checked per site: on
 * Multisite, one site opting out must not lose its data because another
 * site opted in.
 *
 * @return void
 */
function advanced_testimonial_uninstall_site() {
	$settings = get_option( 'advanced_testimonial_settings' );

	// Safe default: do nothing unless the user opted in.
	if ( ! is_array( $settings ) || empty( $settings['delete_data'] ) ) {
		return;
	}

	// 1. Delete all testimonials. Forcing the delete also removes their post
	// meta. 'any' deliberately excludes trashed posts, so 'trash' is listed
	// alongside it — otherwise anything in the Trash would survive the wipe.
	$ids = get_posts(
		array(
			'post_type'              => 'testimonial',
			'post_status'            => array( 'any', 'trash' ),
			'numberposts'            => -1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	foreach ( $ids as $id ) {
		wp_delete_post( $id, true );
	}

	// 2. Delete all groups (terms).
	$terms = get_terms(
		array(
			'taxonomy'   => 'testimonial_group',
			'hide_empty' => false,
			'fields'     => 'ids',
		)
	);

	if ( is_array( $terms ) ) {
		foreach ( $terms as $term_id ) {
			wp_delete_term( $term_id, 'testimonial_group' );
		}
	}

	// 3. Delete options.
	delete_option( 'advanced_testimonial_version' );
	delete_option( 'advanced_testimonial_settings' );
	delete_option( 'advanced_testimonial_debug' );
	delete_option( 'advanced_testimonial_cache_ver' );
	delete_option( 'advanced_testimonial_review' );

	// 4. Delete transients (query cache + form rate limits + one-time tools notices).
	global $wpdb;
	$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- one-off cleanup on uninstall; transients are not cacheable here.
		"DELETE FROM {$wpdb->options}
		 WHERE option_name LIKE '\_transient\_at\_q\_%'
		    OR option_name LIKE '\_transient\_timeout\_at\_q\_%'
		    OR option_name LIKE '\_transient\_at\_rate\_%'
		    OR option_name LIKE '\_transient\_timeout\_at\_rate\_%'
		    OR option_name LIKE '\_transient\_advanced\_testimonial\_%'
		    OR option_name LIKE '\_transient\_timeout\_advanced\_testimonial\_%'"
	);
}

// Deleting a plugin on Multisite removes it from the whole network, so every
// site has to be swept — not just the one the deletion was triggered from.
if ( is_multisite() ) {
	$advanced_testimonial_site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);

	foreach ( $advanced_testimonial_site_ids as $advanced_testimonial_site_id ) {
		switch_to_blog( $advanced_testimonial_site_id );
		advanced_testimonial_uninstall_site();
		restore_current_blog();
	}
} else {
	advanced_testimonial_uninstall_site();
}
