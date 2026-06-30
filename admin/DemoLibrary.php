<?php
/**
 * Remote Demo Library: fetches a manifest of ready-made testimonial sets from a
 * GitHub Pages endpoint and imports them (with their images) on one click.
 * Falls back to the bundled starter demo when offline.
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial\Admin;

use AdvancedTestimonial\Demo;
use AdvancedTestimonial\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Admin screen + import handler for the remote demo library.
 */
final class DemoLibrary {

	const PAGE      = 'advanced-testimonial-demos';
	const ACTION    = 'advanced_testimonial_demo_import';
	const TRANSIENT = 'advanced_testimonial_demo_manifest';
	const CACHE_TTL = 6 * HOUR_IN_SECONDS;

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_post_' . self::ACTION, array( $this, 'import' ) );
		add_action( 'admin_notices', array( $this, 'notice' ) );
	}

	/**
	 * Add the "Demo Library" submenu under Testimonials.
	 *
	 * @return void
	 */
	public function menu() {
		$hook = add_submenu_page(
			'edit.php?post_type=' . CPT::POST_TYPE,
			__( 'Demo Library', 'advanced-testimonial' ),
			__( 'Demo Library', 'advanced-testimonial' ),
			'manage_options',
			self::PAGE,
			array( $this, 'page' )
		);

		if ( $hook ) {
			add_action( 'admin_print_styles-' . $hook, array( $this, 'styles' ) );
		}
	}

	/**
	 * Load the admin stylesheet on the Demo Library screen.
	 *
	 * @return void
	 */
	public function styles() {
		wp_enqueue_style(
			'advanced-testimonial-admin',
			ADVANCED_TESTIMONIAL_URL . 'assets/css/admin.css',
			array(),
			Helpers::asset_version( 'assets/css/admin.css' )
		);
	}

	/**
	 * The resolved manifest URL (filterable for dev / staging).
	 *
	 * @return string
	 */
	public static function manifest_url() {
		return (string) apply_filters( 'advanced_testimonial_demo_library_url', ADVANCED_TESTIMONIAL_DEMO_LIBRARY_URL );
	}

	/**
	 * Fetch and validate the manifest, cached in a transient.
	 *
	 * @param bool $force Bypass the cache.
	 * @return array|\WP_Error
	 */
	public static function get_manifest( $force = false ) {
		if ( ! $force ) {
			$cached = get_transient( self::TRANSIENT );
			if ( is_array( $cached ) ) {
				return $cached;
			}
		}

		$response = wp_remote_get(
			self::manifest_url(),
			array(
				'timeout' => 10,
				'headers' => array( 'Accept' => 'application/json' ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}
		if ( 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return new \WP_Error( 'at_http', __( 'The demo library could not be reached.', 'advanced-testimonial' ) );
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		$data = self::validate_manifest( $data );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		set_transient( self::TRANSIENT, $data, self::CACHE_TTL );
		return $data;
	}

	/**
	 * Validate the raw manifest and normalise its demos.
	 *
	 * @param mixed $data Decoded JSON.
	 * @return array|\WP_Error
	 */
	private static function validate_manifest( $data ) {
		if ( ! is_array( $data ) || empty( $data['demos'] ) || ! is_array( $data['demos'] ) ) {
			return new \WP_Error( 'at_manifest', __( 'The demo library response was not valid.', 'advanced-testimonial' ) );
		}
		if ( isset( $data['plugin'] ) && 'advanced-testimonial' !== $data['plugin'] ) {
			return new \WP_Error( 'at_manifest', __( 'The demo library is for a different plugin.', 'advanced-testimonial' ) );
		}

		$demos = array();
		foreach ( $data['demos'] as $demo ) {
			if ( ! is_array( $demo ) || empty( $demo['id'] ) || empty( $demo['file'] ) ) {
				continue;
			}
			$file = esc_url_raw( $demo['file'] );
			if ( ! $file || ! in_array( wp_parse_url( $file, PHP_URL_SCHEME ), array( 'http', 'https' ), true ) ) {
				continue;
			}
			$demos[] = array(
				'id'          => sanitize_key( $demo['id'] ),
				'name'        => isset( $demo['name'] ) ? sanitize_text_field( $demo['name'] ) : $demo['id'],
				'description' => isset( $demo['description'] ) ? sanitize_text_field( $demo['description'] ) : '',
				'version'     => isset( $demo['version'] ) ? sanitize_text_field( $demo['version'] ) : '',
				'requires'    => isset( $demo['requires'] ) ? sanitize_text_field( $demo['requires'] ) : '',
				'category'    => isset( $demo['category'] ) ? sanitize_text_field( $demo['category'] ) : '',
				'tags'        => isset( $demo['tags'] ) && is_array( $demo['tags'] ) ? array_map( 'sanitize_text_field', $demo['tags'] ) : array(),
				'featured'    => ! empty( $demo['featured'] ),
				'is_new'      => ! empty( $demo['new'] ),
				'preview'     => isset( $demo['preview'] ) ? esc_url_raw( $demo['preview'] ) : '',
				'file'        => $file,
			);
		}

		if ( ! $demos ) {
			return new \WP_Error( 'at_manifest', __( 'The demo library is empty right now.', 'advanced-testimonial' ) );
		}

		return array(
			'schema_version' => isset( $data['schema_version'] ) ? absint( $data['schema_version'] ) : 1,
			'demos'          => $demos,
		);
	}

	/**
	 * Render the Demo Library page.
	 *
	 * @return void
	 */
	public function page() {
		// A nonce-protected "Refresh" link bypasses the 6h manifest cache.
		$refresh  = isset( $_GET['at_refresh'], $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'at_demo_refresh' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce verified inline.
		$manifest = self::get_manifest( $refresh );

		$refresh_url = wp_nonce_url(
			add_query_arg(
				array(
					'post_type'  => CPT::POST_TYPE,
					'page'       => self::PAGE,
					'at_refresh' => 1,
				),
				admin_url( 'edit.php' )
			),
			'at_demo_refresh'
		);
		?>
		<div class="wrap at-demo-library">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Demo Library', 'advanced-testimonial' ); ?></h1>
			<a href="<?php echo esc_url( $refresh_url ); ?>" class="page-title-action"><?php esc_html_e( 'Refresh', 'advanced-testimonial' ); ?></a>
			<hr class="wp-header-end" />
			<?php if ( $refresh && ! is_wp_error( $manifest ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Demo list refreshed from the library.', 'advanced-testimonial' ); ?></p></div>
			<?php endif; ?>
			<p><?php esc_html_e( 'Import a ready-made set of testimonials with one click. Images are downloaded into your Media Library automatically.', 'advanced-testimonial' ); ?></p>

			<?php if ( is_wp_error( $manifest ) ) : ?>
				<div class="notice notice-warning">
					<p><strong><?php esc_html_e( 'Unable to load the online demo library.', 'advanced-testimonial' ); ?></strong> <?php echo esc_html( $manifest->get_error_message() ); ?></p>
				</div>
				<p><?php esc_html_e( 'You can still import the bundled starter demo below.', 'advanced-testimonial' ); ?></p>
				<div class="at-demo-grid">
					<?php $this->bundled_card(); ?>
				</div>
			<?php else : ?>
				<div class="at-demo-grid">
					<?php
					foreach ( $manifest['demos'] as $demo ) {
						$this->card( $demo );
					}
					?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render a single remote demo card.
	 *
	 * @param array $demo Normalised demo entry.
	 * @return void
	 */
	private function card( $demo ) {
		$can_import = '' === $demo['requires'] || version_compare( ADVANCED_TESTIMONIAL_VERSION, $demo['requires'], '>=' );
		?>
		<div class="at-demo-card">
			<div class="at-demo-card__preview">
				<?php if ( $demo['preview'] ) : ?>
					<img src="<?php echo esc_url( $demo['preview'] ); ?>" alt="<?php echo esc_attr( $demo['name'] ); ?>" loading="lazy" />
				<?php endif; ?>
				<?php if ( $demo['featured'] ) : ?>
					<span class="at-demo-badge at-demo-badge--featured"><?php esc_html_e( 'Featured', 'advanced-testimonial' ); ?></span>
				<?php elseif ( $demo['is_new'] ) : ?>
					<span class="at-demo-badge at-demo-badge--new"><?php esc_html_e( 'New', 'advanced-testimonial' ); ?></span>
				<?php endif; ?>
			</div>
			<div class="at-demo-card__body">
				<h3 class="at-demo-card__title"><?php echo esc_html( $demo['name'] ); ?></h3>
				<?php if ( $demo['description'] ) : ?>
					<p class="at-demo-card__desc"><?php echo esc_html( $demo['description'] ); ?></p>
				<?php endif; ?>
				<?php if ( $demo['category'] ) : ?>
					<p class="at-demo-card__cat"><?php echo esc_html( $demo['category'] ); ?></p>
				<?php endif; ?>
			</div>
			<div class="at-demo-card__actions">
				<?php if ( $can_import ) : ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>" />
						<input type="hidden" name="demo_id" value="<?php echo esc_attr( $demo['id'] ); ?>" />
						<?php wp_nonce_field( self::ACTION . '_' . $demo['id'] ); ?>
						<?php submit_button( __( 'Import Demo', 'advanced-testimonial' ), 'primary', 'submit', false ); ?>
					</form>
				<?php else : ?>
					<button type="button" class="button" disabled><?php esc_html_e( 'Import Demo', 'advanced-testimonial' ); ?></button>
					<p class="at-demo-card__requires">
						<?php
						/* translators: %s: required plugin version. */
						printf( esc_html__( 'Requires Advanced Testimonial %s+', 'advanced-testimonial' ), esc_html( $demo['requires'] ) );
						?>
					</p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the bundled starter demo as a card (offline fallback).
	 *
	 * @return void
	 */
	private function bundled_card() {
		?>
		<div class="at-demo-card">
			<div class="at-demo-card__body">
				<h3 class="at-demo-card__title"><?php esc_html_e( 'Starter Demo', 'advanced-testimonial' ); ?></h3>
				<p class="at-demo-card__desc"><?php esc_html_e( 'Six sample testimonials with ratings, companies and images, bundled with the plugin.', 'advanced-testimonial' ); ?></p>
			</div>
			<div class="at-demo-card__actions">
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>" />
					<input type="hidden" name="at_bundled" value="1" />
					<?php wp_nonce_field( self::ACTION . '_bundled' ); ?>
					<?php submit_button( __( 'Import Demo', 'advanced-testimonial' ), 'primary', 'submit', false ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle a one-click import (remote demo or bundled fallback).
	 *
	 * @return void
	 */
	public function import() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'advanced-testimonial' ) );
		}

		if ( isset( $_POST['at_bundled'] ) ) {
			check_admin_referer( self::ACTION . '_bundled' );
			$result = Demo::import_starter();
		} else {
			$demo_id = isset( $_POST['demo_id'] ) ? sanitize_key( wp_unslash( $_POST['demo_id'] ) ) : '';
			check_admin_referer( self::ACTION . '_' . $demo_id );
			$result = $demo_id ? $this->import_demo( $demo_id ) : new \WP_Error( 'at_demo', __( 'Unknown demo.', 'advanced-testimonial' ) );
		}

		$args = array( 'post_type' => CPT::POST_TYPE );
		if ( is_wp_error( $result ) || empty( $result['testimonials'] ) ) {
			$args['at_demo_lib'] = 'fail';
		} else {
			$args['at_demo_lib'] = 'ok';
			$args['at_t']        = (int) $result['testimonials'];
		}

		wp_safe_redirect( add_query_arg( $args, admin_url( 'edit.php' ) ) );
		exit;
	}

	/**
	 * Look up a demo in the manifest, fetch its file and import it.
	 *
	 * @param string $demo_id Demo id.
	 * @return array|\WP_Error Import counts or error.
	 */
	private function import_demo( $demo_id ) {
		$manifest = self::get_manifest();
		if ( is_wp_error( $manifest ) ) {
			return $manifest;
		}

		// Resolve the demo entry from the manifest (never trust a posted URL).
		$entry = null;
		foreach ( $manifest['demos'] as $demo ) {
			if ( $demo['id'] === $demo_id ) {
				$entry = $demo;
				break;
			}
		}
		if ( ! $entry ) {
			return new \WP_Error( 'at_demo', __( 'That demo is no longer available.', 'advanced-testimonial' ) );
		}

		// Re-check the version requirement server-side.
		if ( '' !== $entry['requires'] && ! version_compare( ADVANCED_TESTIMONIAL_VERSION, $entry['requires'], '>=' ) ) {
			return new \WP_Error( 'at_demo', __( 'This demo needs a newer plugin version.', 'advanced-testimonial' ) );
		}

		$response = wp_remote_get( $entry['file'], array( 'timeout' => 15 ) );
		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return new \WP_Error( 'at_demo', __( 'The demo could not be downloaded.', 'advanced-testimonial' ) );
		}
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $data ) || empty( $data['testimonials'] ) ) {
			return new \WP_Error( 'at_demo', __( 'The demo data was invalid.', 'advanced-testimonial' ) );
		}

		// Imported testimonials are published; images are sideloaded from their URLs.
		return Demo::import( $data, 'append' );
	}

	/**
	 * Show the import result notice on the testimonial screens.
	 *
	 * @return void
	 */
	public function notice() {
		if ( ! isset( $_GET['at_demo_lib'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		$status = sanitize_key( wp_unslash( $_GET['at_demo_lib'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 'ok' === $status ) {
			$count = isset( $_GET['at_t'] ) ? absint( wp_unslash( $_GET['at_t'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			echo '<div class="notice notice-success is-dismissible"><p>' . sprintf(
				/* translators: %d: number of imported testimonials. */
				esc_html__( 'Demo imported — %d testimonials added. Display them with the block or the [advanced_testimonial] shortcode.', 'advanced-testimonial' ),
				(int) $count
			) . '</p></div>';
		} elseif ( 'fail' === $status ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Sorry, that demo could not be imported. Please try again.', 'advanced-testimonial' ) . '</p></div>';
		}
	}
}
