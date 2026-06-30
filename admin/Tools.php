<?php
/**
 * Tools tab — demo import/export, reset, cache, debug, system info.
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial\Admin;

use AdvancedTestimonial\Demo;
use AdvancedTestimonial\Frontend\Query;

defined( 'ABSPATH' ) || exit;

// Every action entry point (handle / handle_export) verifies the nonce up front
// via guard() → check_admin_referer(); PHPCS cannot follow that across methods.
// phpcs:disable WordPress.Security.NonceVerification.Missing

/**
 * Renders the Tools tab and handles its form actions.
 */
final class Tools {

	/**
	 * Nonce action shared by the Tools forms.
	 */
	const NONCE = 'advanced_testimonial_tools';

	/**
	 * Option storing the debug flag.
	 */
	const DEBUG_OPTION = 'advanced_testimonial_debug';

	/**
	 * Transient holding a one-time admin notice after an action.
	 */
	const NOTICE_TRANSIENT = 'advanced_testimonial_tools_notice';

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_post_advanced_testimonial_tools', array( $this, 'handle' ) );
		add_action( 'admin_post_advanced_testimonial_export', array( $this, 'handle_export' ) );
	}

	/**
	 * URL of the Tools tab.
	 *
	 * @return string
	 */
	private function tools_url() {
		return admin_url( 'edit.php?post_type=' . CPT::POST_TYPE . '&page=' . Settings::PAGE . '#tools' );
	}

	/**
	 * Verify capability + nonce or die.
	 *
	 * @return void
	 */
	private function guard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'advanced-testimonial' ) );
		}
		check_admin_referer( self::NONCE );
	}

	/**
	 * Store a one-time notice and redirect back to the Tools tab.
	 *
	 * @param string $type    success|error|warning.
	 * @param string $message Message text.
	 * @return void
	 */
	private function redirect_back( $type, $message ) {
		set_transient(
			self::NOTICE_TRANSIENT,
			array(
				'type'    => $type,
				'message' => $message,
			),
			60
		);
		wp_safe_redirect( $this->tools_url() );
		exit;
	}

	/**
	 * Handle the maintenance/import actions.
	 *
	 * @return void
	 */
	public function handle() {
		$this->guard();

		$tool = isset( $_POST['at_tool'] ) ? sanitize_key( wp_unslash( $_POST['at_tool'] ) ) : '';

		switch ( $tool ) {
			case 'starter':
				$result = Demo::import_starter();
				if ( is_wp_error( $result ) ) {
					$this->redirect_back( 'error', $result->get_error_message() );
				}
				$this->redirect_back(
					'success',
					sprintf(
						/* translators: 1: testimonials count, 2: groups count, 3: images count. */
						__( 'Starter demo imported: %1$d testimonials, %2$d groups, %3$d images.', 'advanced-testimonial' ),
						$result['testimonials'],
						$result['groups'],
						$result['images']
					)
				);
				break;

			case 'import':
				$this->do_import();
				break;

			case 'reset':
				delete_option( Settings::OPTION );
				$this->redirect_back( 'success', __( 'Settings reset to defaults.', 'advanced-testimonial' ) );
				break;

			case 'clear':
				Query::bust_cache();
				delete_transient( DemoLibrary::TRANSIENT );
				$this->redirect_back( 'success', __( 'Caches cleared — testimonial queries and the demo library list.', 'advanced-testimonial' ) );
				break;

			case 'debug':
				$on = ! get_option( self::DEBUG_OPTION );
				update_option( self::DEBUG_OPTION, $on ? 1 : 0, false );
				$this->redirect_back( 'success', $on ? __( 'Debug mode enabled (query cache bypassed).', 'advanced-testimonial' ) : __( 'Debug mode disabled.', 'advanced-testimonial' ) );
				break;

			default:
				$this->redirect_back( 'error', __( 'Unknown action.', 'advanced-testimonial' ) );
		}
	}

	/**
	 * Handle an uploaded demo file import.
	 *
	 * @return void
	 */
	private function do_import() {
		if ( empty( $_FILES['at_import_file']['tmp_name'] ) || ! is_uploaded_file( $_FILES['at_import_file']['tmp_name'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- verifying a PHP upload before reading it.
			$this->redirect_back( 'error', __( 'Please choose a .json file to import.', 'advanced-testimonial' ) );
		}

		$tmp  = sanitize_text_field( wp_unslash( $_FILES['at_import_file']['tmp_name'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$name = isset( $_FILES['at_import_file']['name'] ) ? sanitize_file_name( wp_unslash( $_FILES['at_import_file']['name'] ) ) : '';

		if ( 'json' !== strtolower( pathinfo( $name, PATHINFO_EXTENSION ) ) ) {
			$this->redirect_back( 'error', __( 'The file must be a .json export.', 'advanced-testimonial' ) );
		}

		$data = wp_json_file_decode( $tmp, array( 'associative' => true ) );
		if ( ! is_array( $data ) || empty( $data['testimonials'] ) && empty( $data['groups'] ) ) {
			$this->redirect_back( 'error', __( 'That file is not a valid Advanced Testimonial export.', 'advanced-testimonial' ) );
		}

		$strategy      = isset( $_POST['at_strategy'] ) ? sanitize_key( wp_unslash( $_POST['at_strategy'] ) ) : 'append';
		$with_settings = ! empty( $_POST['at_with_settings'] );
		if ( ! $with_settings ) {
			unset( $data['settings'] );
		}

		$result = Demo::import( $data, $strategy );
		$this->redirect_back(
			'success',
			sprintf(
				/* translators: 1: testimonials, 2: groups, 3: images, 4: skipped. */
				__( 'Import complete: %1$d testimonials, %2$d groups, %3$d images (%4$d skipped).', 'advanced-testimonial' ),
				$result['testimonials'],
				$result['groups'],
				$result['images'],
				$result['skipped']
			)
		);
	}

	/**
	 * Stream a JSON export as a download.
	 *
	 * @return void
	 */
	public function handle_export() {
		$this->guard();

		$include = array(
			'testimonials' => ! empty( $_POST['at_export_testimonials'] ),
			'groups'       => ! empty( $_POST['at_export_groups'] ),
			'settings'     => ! empty( $_POST['at_export_settings'] ),
		);
		if ( ! $include['testimonials'] && ! $include['groups'] && ! $include['settings'] ) {
			$include['testimonials'] = true;
			$include['groups']       = true;
		}

		$data     = Demo::export( $include );
		$filename = 'advanced-testimonial-export-' . gmdate( 'Y-m-d' ) . '.json';

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		echo wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		exit;
	}

	/**
	 * Output any one-time notice (called by the Settings page).
	 *
	 * @return void
	 */
	public static function maybe_notice() {
		$notice = get_transient( self::NOTICE_TRANSIENT );
		if ( ! $notice ) {
			return;
		}
		delete_transient( self::NOTICE_TRANSIENT );

		$class = ( 'error' === $notice['type'] ) ? 'notice-error' : ( 'warning' === $notice['type'] ? 'notice-warning' : 'notice-success' );
		printf(
			'<div class="notice %1$s is-dismissible"><p>%2$s</p></div>',
			esc_attr( $class ),
			esc_html( $notice['message'] )
		);
	}

	/**
	 * Render the Tools tab panel (separate forms, not the settings form).
	 *
	 * @return void
	 */
	public static function render_panel() {
		$post_url = admin_url( 'admin-post.php' );
		$debug_on = (bool) get_option( self::DEBUG_OPTION );

		echo '<div class="at-tools">';

		// Starter demo.
		echo '<div class="at-tools-card"><h3>' . esc_html__( 'Starter Demo', 'advanced-testimonial' ) . '</h3>';
		echo '<p class="description">' . esc_html__( 'One-click sample content: 6 testimonials, 2 groups, ratings, companies and images. Existing testimonials are skipped.', 'advanced-testimonial' ) . '</p>';
		echo '<form method="post" action="' . esc_url( $post_url ) . '">';
		wp_nonce_field( self::NONCE );
		echo '<input type="hidden" name="action" value="advanced_testimonial_tools" />';
		echo '<input type="hidden" name="at_tool" value="starter" />';
		submit_button( __( 'Import Starter Demo', 'advanced-testimonial' ), 'primary', '', false );
		echo '</form></div>';

		// Import.
		echo '<div class="at-tools-card"><h3>' . esc_html__( 'Import', 'advanced-testimonial' ) . '</h3>';
		echo '<form method="post" enctype="multipart/form-data" action="' . esc_url( $post_url ) . '">';
		wp_nonce_field( self::NONCE );
		echo '<input type="hidden" name="action" value="advanced_testimonial_tools" />';
		echo '<input type="hidden" name="at_tool" value="import" />';
		echo '<p><input type="file" name="at_import_file" accept="application/json,.json" required /></p>';
		echo '<p><strong>' . esc_html__( 'Existing testimonials:', 'advanced-testimonial' ) . '</strong></p>';
		echo '<p><label><input type="radio" name="at_strategy" value="append" checked /> ' . esc_html__( 'Append (add all)', 'advanced-testimonial' ) . '</label><br />';
		echo '<label><input type="radio" name="at_strategy" value="skip" /> ' . esc_html__( 'Skip duplicates (by title)', 'advanced-testimonial' ) . '</label><br />';
		echo '<label><input type="radio" name="at_strategy" value="replace" /> ' . esc_html__( 'Replace matches (by title)', 'advanced-testimonial' ) . '</label></p>';
		echo '<p><label><input type="checkbox" name="at_with_settings" value="1" /> ' . esc_html__( 'Also import settings (if present)', 'advanced-testimonial' ) . '</label></p>';
		submit_button( __( 'Import File', 'advanced-testimonial' ), 'secondary', '', false );
		echo '</form></div>';

		// Export.
		echo '<div class="at-tools-card"><h3>' . esc_html__( 'Export', 'advanced-testimonial' ) . '</h3>';
		echo '<form method="post" action="' . esc_url( $post_url ) . '">';
		wp_nonce_field( self::NONCE );
		echo '<input type="hidden" name="action" value="advanced_testimonial_export" />';
		echo '<p><label><input type="checkbox" name="at_export_testimonials" value="1" checked /> ' . esc_html__( 'Testimonials', 'advanced-testimonial' ) . '</label><br />';
		echo '<label><input type="checkbox" name="at_export_groups" value="1" checked /> ' . esc_html__( 'Groups', 'advanced-testimonial' ) . '</label><br />';
		echo '<label><input type="checkbox" name="at_export_settings" value="1" /> ' . esc_html__( 'Plugin settings', 'advanced-testimonial' ) . '</label></p>';
		submit_button( __( 'Export (.json)', 'advanced-testimonial' ), 'secondary', '', false );
		echo '</form></div>';

		// Maintenance.
		echo '<div class="at-tools-card"><h3>' . esc_html__( 'Maintenance', 'advanced-testimonial' ) . '</h3>';
		echo '<p>';
		self::action_button( $post_url, 'clear', __( 'Clear Cache', 'advanced-testimonial' ), 'secondary' );
		self::action_button( $post_url, 'debug', $debug_on ? __( 'Disable Debug Mode', 'advanced-testimonial' ) : __( 'Enable Debug Mode', 'advanced-testimonial' ), 'secondary' );
		self::action_button( $post_url, 'reset', __( 'Reset Settings', 'advanced-testimonial' ), 'delete', true );
		echo '</p>';
		echo '<p class="description">' . ( $debug_on ? esc_html__( 'Debug mode is ON — query cache is bypassed.', 'advanced-testimonial' ) : esc_html__( 'Reset restores default settings. Testimonials are never deleted.', 'advanced-testimonial' ) ) . '</p>';
		echo '</div>';

		// System info.
		echo '<div class="at-tools-card at-tools-card--wide"><h3>' . esc_html__( 'System Info', 'advanced-testimonial' ) . '</h3>';
		echo '<textarea class="large-text code" rows="9" readonly onclick="this.select()">' . esc_textarea( self::system_info() ) . '</textarea>';
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Render a single inline action button (its own mini form).
	 *
	 * @param string $post_url admin-post.php URL.
	 * @param string $tool     Tool key.
	 * @param string $label    Button label.
	 * @param string $type     Button style.
	 * @param bool   $confirm  Whether to confirm before submit.
	 * @return void
	 */
	private static function action_button( $post_url, $tool, $label, $type, $confirm = false ) {
		echo '<form method="post" action="' . esc_url( $post_url ) . '" style="display:inline-block;margin:0 8px 8px 0;"';
		if ( $confirm ) {
			echo ' onsubmit="return confirm(' . "'" . esc_js( __( 'Are you sure?', 'advanced-testimonial' ) ) . "'" . ');"';
		}
		echo '>';
		wp_nonce_field( self::NONCE );
		echo '<input type="hidden" name="action" value="advanced_testimonial_tools" />';
		echo '<input type="hidden" name="at_tool" value="' . esc_attr( $tool ) . '" />';
		submit_button( $label, $type, '', false );
		echo '</form>';
	}

	/**
	 * Build the System Info text.
	 *
	 * @return string
	 */
	private static function system_info() {
		$theme  = wp_get_theme();
		$counts = wp_count_posts( CPT::POST_TYPE );
		$groups = wp_count_terms(
			array(
				'taxonomy'   => Taxonomy::TAXONOMY,
				'hide_empty' => false,
			)
		);

		$lines = array(
			'Plugin version : ' . ADVANCED_TESTIMONIAL_VERSION,
			'WordPress      : ' . get_bloginfo( 'version' ),
			'PHP            : ' . PHP_VERSION,
			'Theme          : ' . $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ),
			'Memory limit   : ' . WP_MEMORY_LIMIT,
			'Testimonials   : ' . ( isset( $counts->publish ) ? (int) $counts->publish : 0 ),
			'Groups         : ' . ( is_wp_error( $groups ) ? 0 : (int) $groups ),
			'Debug mode     : ' . ( get_option( self::DEBUG_OPTION ) ? 'on' : 'off' ),
		);

		return implode( "\n", $lines );
	}
}
