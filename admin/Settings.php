<?php
/**
 * Settings page (single option, tabbed UI, Settings API).
 *
 * @package AdvancedTestimonial
 */

namespace AdvancedTestimonial\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and renders the plugin settings screen.
 */
final class Settings {

	/**
	 * Option name.
	 */
	const OPTION = 'advanced_testimonial_settings';

	/**
	 * Settings group (option_page).
	 */
	const GROUP = 'advanced_testimonial_settings_group';

	/**
	 * Page slug.
	 */
	const PAGE = 'advanced-testimonial-settings';

	/**
	 * Admin page hook suffix (used to scope assets).
	 *
	 * @var string
	 */
	private $hook_suffix = '';

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_init', array( $this, 'register_setting' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Settings schema grouped by tab.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function schema() {
		return array(
			'general'     => array(
				'title'  => __( 'General', 'advanced-testimonial' ),
				'fields' => array(
					'image_size'         => array(
						'type'    => 'select',
						'label'   => __( 'Client Image Size', 'advanced-testimonial' ),
						'default' => 'medium',
						'options' => array(
							'thumbnail' => __( 'Thumbnail', 'advanced-testimonial' ),
							'medium'    => __( 'Medium', 'advanced-testimonial' ),
							'large'     => __( 'Large', 'advanced-testimonial' ),
							'full'      => __( 'Full', 'advanced-testimonial' ),
						),
					),
					'lazy_load'          => array(
						'type'    => 'checkbox',
						'label'   => __( 'Lazy Load Images', 'advanced-testimonial' ),
						'default' => 1,
					),
					'enable_css'         => array(
						'type'    => 'checkbox',
						'label'   => __( 'Enable Frontend CSS', 'advanced-testimonial' ),
						'default' => 1,
					),
					'enable_js'          => array(
						'type'    => 'checkbox',
						'label'   => __( 'Enable Frontend JS', 'advanced-testimonial' ),
						'default' => 1,
					),
					'enable_schema'      => array(
						'type'        => 'checkbox',
						'label'       => __( 'Enable Schema.org Markup', 'advanced-testimonial' ),
						'default'     => 1,
						'description' => __( 'Adds Review/Rating structured data for richer search results.', 'advanced-testimonial' ),
					),
					'enable_rtl'         => array(
						'type'    => 'checkbox',
						'label'   => __( 'Enable RTL Styles', 'advanced-testimonial' ),
						'default' => 0,
					),
					'enable_fontawesome' => array(
						'type'        => 'checkbox',
						'label'       => __( 'Use Font Awesome Icons', 'advanced-testimonial' ),
						'default'     => 0,
						'description' => __( 'Only if your theme already loads Font Awesome. Otherwise built-in SVG icons are used.', 'advanced-testimonial' ),
					),
				),
			),
			'styles'      => array(
				'title'  => __( 'Styles', 'advanced-testimonial' ),
				'fields' => array(
					'primary_color' => array(
						'type'    => 'color',
						'label'   => __( 'Primary Color', 'advanced-testimonial' ),
						'default' => '#2563eb',
					),
					'accent_color'  => array(
						'type'        => 'color',
						'label'       => __( 'Secondary / Accent Color', 'advanced-testimonial' ),
						'default'     => '#f59e0b',
						'description' => __( 'Used for rating stars and accents.', 'advanced-testimonial' ),
					),
					'text_color'    => array(
						'type'    => 'color',
						'label'   => __( 'Text Color', 'advanced-testimonial' ),
						'default' => '#1f2937',
					),
					'border_radius' => array(
						'type'    => 'number',
						'label'   => __( 'Border Radius (px)', 'advanced-testimonial' ),
						'default' => 12,
						'min'     => 0,
						'max'     => 50,
						'step'    => 1,
					),
					'spacing'       => array(
						'type'    => 'number',
						'label'   => __( 'Spacing / Gap (px)', 'advanced-testimonial' ),
						'default' => 24,
						'min'     => 0,
						'max'     => 80,
						'step'    => 1,
					),
					'card_shadow'   => array(
						'type'    => 'select',
						'label'   => __( 'Card Shadow', 'advanced-testimonial' ),
						'default' => 'soft',
						'options' => array(
							'none'   => __( 'None', 'advanced-testimonial' ),
							'soft'   => __( 'Soft', 'advanced-testimonial' ),
							'medium' => __( 'Medium', 'advanced-testimonial' ),
							'strong' => __( 'Strong', 'advanced-testimonial' ),
						),
					),
					'button_style'  => array(
						'type'    => 'select',
						'label'   => __( 'Button Style', 'advanced-testimonial' ),
						'default' => 'filled',
						'options' => array(
							'filled'  => __( 'Filled', 'advanced-testimonial' ),
							'outline' => __( 'Outline', 'advanced-testimonial' ),
						),
					),
				),
			),
			'performance' => array(
				'title'  => __( 'Performance', 'advanced-testimonial' ),
				'fields' => array(
					'conditional_assets' => array(
						'type'        => 'checkbox',
						'label'       => __( 'Load Assets Only When Needed', 'advanced-testimonial' ),
						'default'     => 1,
						'description' => __( 'Only enqueue CSS/JS on pages that actually render testimonials.', 'advanced-testimonial' ),
					),
					'cache_queries'      => array(
						'type'        => 'checkbox',
						'label'       => __( 'Cache Queries', 'advanced-testimonial' ),
						'default'     => 1,
						'description' => __( 'Cache testimonial queries for faster repeat loads.', 'advanced-testimonial' ),
					),
					'use_minified'       => array(
						'type'        => 'checkbox',
						'label'       => __( 'Use Minified Assets', 'advanced-testimonial' ),
						'default'     => 0,
						'description' => __( 'Load .min versions of CSS/JS when available.', 'advanced-testimonial' ),
					),
				),
			),
			'advanced'    => array(
				'title'  => __( 'Advanced', 'advanced-testimonial' ),
				'fields' => array(
					'custom_css'  => array(
						'type'        => 'textarea',
						'label'       => __( 'Custom CSS', 'advanced-testimonial' ),
						'default'     => '',
						'description' => __( 'Output on the frontend after the plugin styles.', 'advanced-testimonial' ),
					),
					'delete_data' => array(
						'type'        => 'checkbox',
						'label'       => __( 'Delete Data on Uninstall', 'advanced-testimonial' ),
						'default'     => 0,
						'description' => __( 'Remove plugin settings when the plugin is deleted. Testimonials are always kept.', 'advanced-testimonial' ),
					),
				),
			),
		);
	}

	/**
	 * Default values derived from the schema.
	 *
	 * @return array<string,mixed>
	 */
	public static function defaults() {
		$defaults = array();

		foreach ( self::schema() as $tab ) {
			foreach ( $tab['fields'] as $key => $field ) {
				$defaults[ $key ] = $field['default'];
			}
		}

		return $defaults;
	}

	/**
	 * Get all settings merged with defaults.
	 *
	 * @return array<string,mixed>
	 */
	public static function get_settings() {
		$saved = get_option( self::OPTION, array() );

		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		return wp_parse_args( $saved, self::defaults() );
	}

	/**
	 * Get a single setting value.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Fallback when not set.
	 * @return mixed
	 */
	public static function get( $key, $default = null ) {
		$settings = self::get_settings();

		return array_key_exists( $key, $settings ) ? $settings[ $key ] : $default;
	}

	/**
	 * Register the submenu page.
	 *
	 * @return void
	 */
	public function add_page() {
		$this->hook_suffix = (string) add_submenu_page(
			'edit.php?post_type=' . CPT::POST_TYPE,
			__( 'Advanced Testimonial Settings', 'advanced-testimonial' ),
			__( 'Settings', 'advanced-testimonial' ),
			'manage_options',
			self::PAGE,
			array( $this, 'render' )
		);
	}

	/**
	 * Register the option and its sanitizer.
	 *
	 * @return void
	 */
	public function register_setting() {
		register_setting(
			self::GROUP,
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
	}

	/**
	 * Enqueue settings page assets.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue( $hook ) {
		if ( $hook !== $this->hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'advanced-testimonial-admin',
			ADVANCED_TESTIMONIAL_URL . 'assets/css/admin.css',
			array(),
			ADVANCED_TESTIMONIAL_VERSION
		);

		wp_enqueue_script(
			'advanced-testimonial-settings',
			ADVANCED_TESTIMONIAL_URL . 'assets/js/settings.js',
			array(),
			ADVANCED_TESTIMONIAL_VERSION,
			true
		);
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$schema   = self::schema();
		$settings = self::get_settings();
		$tab_keys = array_keys( $schema );
		$first    = reset( $tab_keys );

		echo '<div class="wrap advanced-testimonial-settings">';
		echo '<h1>' . esc_html__( 'Advanced Testimonial Settings', 'advanced-testimonial' ) . '</h1>';

		echo '<form method="post" action="options.php">';
		settings_fields( self::GROUP );

		// Tab navigation.
		echo '<h2 class="nav-tab-wrapper at-settings-tabs">';
		foreach ( $schema as $tab_key => $tab ) {
			printf(
				'<a href="#%1$s" class="nav-tab%2$s" data-tab="%1$s">%3$s</a>',
				esc_attr( $tab_key ),
				( $tab_key === $first ) ? ' nav-tab-active' : '',
				esc_html( $tab['title'] )
			);
		}
		echo '</h2>';

		// Tab panels.
		foreach ( $schema as $tab_key => $tab ) {
			$hidden = ( $tab_key === $first ) ? '' : ' style="display:none"';
			echo '<div class="at-settings-panel" id="at-panel-' . esc_attr( $tab_key ) . '" data-panel="' . esc_attr( $tab_key ) . '"' . $hidden . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup above is safe.
			echo '<table class="form-table" role="presentation"><tbody>';

			foreach ( $tab['fields'] as $key => $field ) {
				$value = isset( $settings[ $key ] ) ? $settings[ $key ] : $field['default'];
				echo '<tr>';
				echo '<th scope="row"><label for="at-set-' . esc_attr( $key ) . '">' . esc_html( $field['label'] ) . '</label></th>';
				echo '<td>';
				$this->render_field( $key, $field, $value );
				if ( ! empty( $field['description'] ) ) {
					echo '<p class="description">' . esc_html( $field['description'] ) . '</p>';
				}
				echo '</td>';
				echo '</tr>';
			}

			echo '</tbody></table>';
			echo '</div>';
		}

		submit_button();
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Render a single settings field.
	 *
	 * @param string $key   Field key.
	 * @param array  $field Field definition.
	 * @param mixed  $value Current value.
	 * @return void
	 */
	private function render_field( $key, $field, $value ) {
		$name = self::OPTION . '[' . $key . ']';
		$id   = 'at-set-' . $key;

		switch ( $field['type'] ) {
			case 'checkbox':
				echo '<label><input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="1"' . checked( (int) $value, 1, false ) . ' /> ' . esc_html__( 'Enable', 'advanced-testimonial' ) . '</label>';
				break;

			case 'select':
				echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';
				foreach ( $field['options'] as $opt_value => $opt_label ) {
					echo '<option value="' . esc_attr( $opt_value ) . '"' . selected( (string) $value, (string) $opt_value, false ) . '>' . esc_html( $opt_label ) . '</option>';
				}
				echo '</select>';
				break;

			case 'color':
				echo '<input type="text" class="at-color-field" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" placeholder="#000000" />';
				echo '<input type="color" class="at-color-swatch" value="' . esc_attr( $value ) . '" data-target="' . esc_attr( $id ) . '" aria-hidden="true" />';
				break;

			case 'number':
				$min  = isset( $field['min'] ) ? ' min="' . esc_attr( $field['min'] ) . '"' : '';
				$max  = isset( $field['max'] ) ? ' max="' . esc_attr( $field['max'] ) . '"' : '';
				$step = isset( $field['step'] ) ? ' step="' . esc_attr( $field['step'] ) . '"' : '';
				echo '<input type="number" class="small-text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '"' . $min . $max . $step . ' />'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- attributes escaped above.
				break;

			case 'textarea':
				echo '<textarea class="large-text code" rows="8" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">' . esc_textarea( $value ) . '</textarea>';
				break;

			default:
				echo '<input type="text" class="regular-text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" />';
				break;
		}
	}

	/**
	 * Sanitize submitted settings.
	 *
	 * @param mixed $input Raw input.
	 * @return array<string,mixed>
	 */
	public function sanitize( $input ) {
		if ( ! is_array( $input ) ) {
			$input = array();
		}

		$clean = array();

		foreach ( self::schema() as $tab ) {
			foreach ( $tab['fields'] as $key => $field ) {
				$raw = isset( $input[ $key ] ) ? $input[ $key ] : null;

				switch ( $field['type'] ) {
					case 'checkbox':
						$clean[ $key ] = ( '1' === (string) $raw ) ? 1 : 0;
						break;

					case 'select':
						$clean[ $key ] = array_key_exists( (string) $raw, $field['options'] ) ? (string) $raw : $field['default'];
						break;

					case 'color':
						$color         = sanitize_hex_color( (string) $raw );
						$clean[ $key ] = $color ? $color : $field['default'];
						break;

					case 'number':
						$num = is_numeric( $raw ) ? (int) $raw : (int) $field['default'];
						if ( isset( $field['min'] ) && $num < $field['min'] ) {
							$num = (int) $field['min'];
						}
						if ( isset( $field['max'] ) && $num > $field['max'] ) {
							$num = (int) $field['max'];
						}
						$clean[ $key ] = $num;
						break;

					case 'textarea':
						$clean[ $key ] = wp_strip_all_tags( (string) $raw );
						break;

					default:
						$clean[ $key ] = sanitize_text_field( (string) $raw );
						break;
				}
			}
		}

		return $clean;
	}
}
