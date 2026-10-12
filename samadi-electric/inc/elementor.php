<?php
/**
 * Elementor and Elementor Pro (Theme Builder) integration.
 *
 * - With Elementor Pro: all core locations (header, footer, single, archive) are registered, so
 *   Theme Builder templates with Display Conditions replace the theme's templates.
 * - With free Elementor: any saved Elementor template can be assigned as site header/footer from
 *   the Customizer (Samadi Electric → Elementor). Pages are edited with Elementor and rendered
 *   through the_content().
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register Elementor Pro Theme Builder locations.
 *
 * @param \ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $manager Manager.
 */
function samadi_register_elementor_locations( $manager ) {
	$manager->register_all_core_location();
}
add_action( 'elementor/theme/register_locations', 'samadi_register_elementor_locations' );

/**
 * Render an Elementor Pro location if a template is assigned to it.
 *
 * @param string $location Location (header, footer, single, archive).
 * @return bool True when Elementor rendered the location.
 */
function samadi_elementor_location( $location ) {
	if ( function_exists( 'elementor_theme_do_location' ) ) {
		return (bool) elementor_theme_do_location( $location );
	}

	return false;
}

/**
 * Render an Elementor template chosen in the Customizer (works with free Elementor).
 *
 * @param string $part 'header' | 'footer'.
 * @return bool True when a template was rendered.
 */
function samadi_elementor_template_part( $part ) {
	if ( ! samadi_is_elementor() || ! class_exists( '\Elementor\Plugin' ) ) {
		return false;
	}

	$template_id = absint( get_theme_mod( 'samadi_elementor_' . $part . '_template', 0 ) );

	if ( ! $template_id || 'elementor_library' !== get_post_type( $template_id ) || 'publish' !== get_post_status( $template_id ) ) {
		return false;
	}

	$html = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template_id, true );

	if ( '' === trim( (string) $html ) ) {
		return false;
	}

	printf( '<div class="se-elementor-%1$s" data-elementor-part="%1$s">', esc_attr( $part ) );
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor builder output.
	echo '</div>';

	return true;
}

/**
 * Site header: Elementor Pro location → Customizer-assigned Elementor template → theme header.
 */
function samadi_render_header() {
	if ( samadi_elementor_location( 'header' ) ) {
		return;
	}
	if ( samadi_elementor_template_part( 'header' ) ) {
		return;
	}
	get_template_part( 'template-parts/header/site-header' );
}

/**
 * Site footer: Elementor Pro location → Customizer-assigned Elementor template → theme footer.
 */
function samadi_render_footer() {
	if ( samadi_elementor_location( 'footer' ) ) {
		return;
	}
	if ( samadi_elementor_template_part( 'footer' ) ) {
		return;
	}
	get_template_part( 'template-parts/footer/site-footer' );
}

/**
 * Customizer: choose saved Elementor templates as header/footer (free Elementor path).
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function samadi_elementor_customize_register( $wp_customize ) {
	if ( ! samadi_is_elementor() ) {
		return;
	}

	$templates = get_posts(
		array(
			'post_type'      => 'elementor_library',
			'post_status'    => 'publish',
			'posts_per_page' => 100,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		)
	);

	$choices = array( 0 => __( '— قالب داخلی پوسته —', 'samadi-electric' ) );
	foreach ( $templates as $template ) {
		$choices[ $template->ID ] = $template->post_title ? $template->post_title : '#' . $template->ID;
	}

	$wp_customize->add_section(
		'samadi_elementor',
		array(
			'title'       => __( 'Elementor', 'samadi-electric' ),
			'panel'       => 'samadi',
			'description' => samadi_is_elementor_pro()
				? __( 'Elementor Pro فعال است: بهتر است هدر و فوتر را از «قالب‌ها → Theme Builder» با شرط نمایش بسازید. قالب‌های Theme Builder بر این تنظیمات اولویت دارند.', 'samadi-electric' )
				: __( 'با Elementor رایگان، یک قالب (Templates → Saved Templates، نوع Section/Container یا Page) بسازید و اینجا به‌عنوان هدر یا فوتر کل سایت انتخاب کنید.', 'samadi-electric' ),
		)
	);

	foreach ( array( 'header' => __( 'قالب هدر', 'samadi-electric' ), 'footer' => __( 'قالب فوتر', 'samadi-electric' ) ) as $part => $label ) {
		$wp_customize->add_setting(
			'samadi_elementor_' . $part . '_template',
			array(
				'default'           => 0,
				'sanitize_callback' => 'absint',
			)
		);
		$wp_customize->add_control(
			'samadi_elementor_' . $part . '_template',
			array(
				'section' => 'samadi_elementor',
				'type'    => 'select',
				'label'   => $label,
				'choices' => $choices,
			)
		);
	}
}
add_action( 'customize_register', 'samadi_elementor_customize_register', 20 );

/**
 * On theme activation, let the theme typography/colors apply to Elementor widgets by default.
 * Users can re-enable Elementor defaults in Elementor → Settings.
 */
function samadi_elementor_activation_defaults() {
	if ( false === get_option( 'elementor_disable_color_schemes', false ) ) {
		update_option( 'elementor_disable_color_schemes', 'yes' );
	}
	if ( false === get_option( 'elementor_disable_typography_schemes', false ) ) {
		update_option( 'elementor_disable_typography_schemes', 'yes' );
	}
}
add_action( 'after_switch_theme', 'samadi_elementor_activation_defaults' );

/**
 * Light editor-panel styles (RTL polish for the Samadi widget category).
 */
function samadi_elementor_editor_styles() {
	wp_enqueue_style( 'samadi-elementor-editor', SAMADI_URI . '/assets/css/elementor-editor.css', array(), samadi_asset_version( 'assets/css/elementor-editor.css' ) );
}
add_action( 'elementor/editor/after_enqueue_styles', 'samadi_elementor_editor_styles' );

/**
 * Register the locally hosted Persian font with Elementor as a system font, so choosing it in
 * Elementor never triggers a Google Fonts request (the theme already serves it via @font-face).
 *
 * @param array $fonts Additional fonts.
 * @return array
 */
function samadi_elementor_local_fonts( $fonts ) {
	$fonts['Vazirmatn'] = 'system';

	return $fonts;
}
add_filter( 'elementor/fonts/additional_fonts', 'samadi_elementor_local_fonts' );

/**
 * Design tokens exposed as Elementor Global Colors (Site Settings → Global Colors).
 *
 * @return array{system: array, custom: array}
 */
function samadi_elementor_global_colors() {
	$c = static function ( $key ) {
		return strtoupper( (string) sanitize_hex_color( samadi_option( $key ) ) );
	};

	return array(
		'system' => array(
			array( '_id' => 'primary', 'title' => __( 'سبز تیره اصلی', 'samadi-electric' ), 'color' => $c( 'color_primary' ) ),
			array( '_id' => 'secondary', 'title' => __( 'سبز برند', 'samadi-electric' ), 'color' => $c( 'color_brand' ) ),
			array( '_id' => 'text', 'title' => __( 'متن اصلی', 'samadi-electric' ), 'color' => $c( 'color_text' ) ),
			array( '_id' => 'accent', 'title' => __( 'لیمویی تأکیدی', 'samadi-electric' ), 'color' => $c( 'color_accent' ) ),
		),
		'custom' => array(
			array( '_id' => 'se_bg', 'title' => __( 'پس‌زمینه عمومی', 'samadi-electric' ), 'color' => $c( 'color_bg' ) ),
			array( '_id' => 'se_surf', 'title' => __( 'سطح کارت', 'samadi-electric' ), 'color' => $c( 'color_surface' ) ),
			array( '_id' => 'se_muted', 'title' => __( 'متن ثانویه', 'samadi-electric' ), 'color' => $c( 'color_muted' ) ),
			array( '_id' => 'se_line', 'title' => __( 'خطوط', 'samadi-electric' ), 'color' => $c( 'color_border' ) ),
			array( '_id' => 'se_sale', 'title' => __( 'تخفیف', 'samadi-electric' ), 'color' => $c( 'color_sale' ) ),
		),
	);
}

/**
 * Write the theme's colors and font into the active Elementor Kit (Global Colors + Global Fonts),
 * so Elementor widgets, the editor preview and the theme share one design system.
 * Runs on theme activation and whenever Customizer settings are saved.
 */
function samadi_elementor_sync_kit() {
	if ( ! samadi_is_elementor() || ! class_exists( '\Elementor\Plugin' ) || empty( \Elementor\Plugin::$instance->kits_manager ) ) {
		return;
	}

	$kit_id = (int) \Elementor\Plugin::$instance->kits_manager->get_active_id();

	if ( ! $kit_id || ! get_post( $kit_id ) ) {
		return;
	}

	$settings = get_post_meta( $kit_id, '_elementor_page_settings', true );
	$settings = is_array( $settings ) ? $settings : array();
	$colors   = samadi_elementor_global_colors();

	$settings['system_colors'] = $colors['system'];

	// Keep user-defined custom colors; replace only the theme's own (se_*) entries.
	$custom = isset( $settings['custom_colors'] ) && is_array( $settings['custom_colors'] ) ? $settings['custom_colors'] : array();
	$custom = array_values(
		array_filter(
			$custom,
			static function ( $item ) {
				return empty( $item['_id'] ) || 0 !== strpos( (string) $item['_id'], 'se_' );
			}
		)
	);
	$settings['custom_colors'] = array_merge( $colors['custom'], $custom );

	$family = 'vazirmatn' === samadi_option( 'font_family' ) ? 'Vazirmatn' : ( 'custom' === samadi_option( 'font_family' ) ? trim( (string) samadi_option( 'font_custom_family' ), '"\'' ) : '' );
	$weights = array(
		'primary'   => (string) samadi_option( 'font_weight_heading' ),
		'secondary' => '700',
		'text'      => (string) samadi_option( 'font_weight_body' ),
		'accent'    => '700',
	);
	$titles = array(
		'primary'   => __( 'عنوان‌ها', 'samadi-electric' ),
		'secondary' => __( 'زیرعنوان', 'samadi-electric' ),
		'text'      => __( 'متن', 'samadi-electric' ),
		'accent'    => __( 'دکمه و تأکید', 'samadi-electric' ),
	);
	$typography = array();
	foreach ( $weights as $id => $weight ) {
		$item = array(
			'_id'                   => $id,
			'title'                 => $titles[ $id ],
			'typography_typography' => 'custom',
			'typography_font_weight' => $weight,
		);
		if ( $family ) {
			$item['typography_font_family'] = $family;
		}
		$typography[] = $item;
	}
	$settings['system_typography'] = $typography;

	// Content width matches the theme container.
	$settings['container_width'] = array( 'unit' => 'px', 'size' => 1280, 'sizes' => array() );

	update_post_meta( $kit_id, '_elementor_page_settings', $settings );

	if ( isset( \Elementor\Plugin::$instance->files_manager ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
}
add_action( 'after_switch_theme', 'samadi_elementor_sync_kit', 20 );
add_action( 'customize_save_after', 'samadi_elementor_sync_kit' );
add_action( 'admin_init', 'samadi_elementor_maybe_first_sync' );

/**
 * First sync when Elementor is activated after the theme.
 */
function samadi_elementor_maybe_first_sync() {
	if ( ! samadi_is_elementor() || get_option( 'samadi_elementor_kit_synced' ) ) {
		return;
	}
	update_option( 'samadi_elementor_kit_synced', 1, false );
	samadi_elementor_sync_kit();
}
