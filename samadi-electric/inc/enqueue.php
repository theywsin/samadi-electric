<?php
/**
 * Styles and scripts.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

/**
 * Asset version: file modification time in development, theme version otherwise.
 *
 * @param string $relative Path relative to theme root.
 * @return string
 */
function samadi_asset_version( $relative ) {
	if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
		$file = SAMADI_DIR . '/' . $relative;
		if ( file_exists( $file ) ) {
			return (string) filemtime( $file );
		}
	}

	return SAMADI_VERSION;
}

/**
 * Whether the current request renders WooCommerce templates.
 *
 * @return bool
 */
function samadi_is_wc_view() {
	if ( ! samadi_is_wc() ) {
		return false;
	}

	return is_woocommerce() || is_cart() || is_checkout() || is_account_page();
}

/**
 * Font stack according to settings.
 *
 * @return string
 */
function samadi_font_stack() {
	$system = 'Tahoma, "Segoe UI", system-ui, -apple-system, sans-serif';

	switch ( samadi_option( 'font_family' ) ) {
		case 'system':
			return $system;
		case 'custom':
			$custom = samadi_sanitize_font_family( samadi_option( 'font_custom_family' ) );
			if ( $custom ) {
				return '"' . trim( $custom, '"\'' ) . '", ' . $system;
			}
			return $system;
		default:
			return '"Vazirmatn", ' . $system;
	}
}

/**
 * Inline CSS: local @font-face + design tokens from settings.
 *
 * @return string
 */
function samadi_inline_tokens_css() {
	$css = '';

	if ( 'vazirmatn' === samadi_option( 'font_family' ) ) {
		$base = SAMADI_URI . '/assets/fonts/vazirmatn/';
		$css .= '@font-face{font-family:"Vazirmatn";font-style:normal;font-weight:100 900;font-display:swap;src:url("' . esc_url( $base . 'vazirmatn-arabic-wght-normal.woff2' ) . '") format("woff2");unicode-range:U+0600-06FF,U+0750-077F,U+0870-088E,U+0890-0891,U+0897-08E1,U+08E3-08FF,U+200C-200E,U+2010-2011,U+204F,U+2E41,U+FB50-FDFF,U+FE70-FE74,U+FE76-FEFC;}';
		$css .= '@font-face{font-family:"Vazirmatn";font-style:normal;font-weight:100 900;font-display:swap;src:url("' . esc_url( $base . 'vazirmatn-latin-wght-normal.woff2' ) . '") format("woff2");unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD;}';
	}

	$map = array(
		'--se-primary' => 'color_primary',
		'--se-brand'   => 'color_brand',
		'--se-accent'  => 'color_accent',
		'--se-bg'      => 'color_bg',
		'--se-surface' => 'color_surface',
		'--se-text'    => 'color_text',
		'--se-muted'   => 'color_muted',
		'--se-border'  => 'color_border',
		'--se-sale'    => 'color_sale',
	);

	$vars = array();
	foreach ( $map as $var => $key ) {
		$color = sanitize_hex_color( samadi_option( $key ) );
		if ( $color ) {
			$vars[] = $var . ':' . $color;
		}
	}

	$vars[] = '--se-font:' . samadi_font_stack();
	$vars[] = '--se-fw-body:' . absint( samadi_option( 'font_weight_body' ) );
	$vars[] = '--se-fw-heading:' . absint( samadi_option( 'font_weight_heading' ) );
	$vars[] = '--se-fs-base:' . max( 12, min( 20, absint( samadi_option( 'font_size_base' ) ) ) ) . 'px';
	$vars[] = '--se-shop-cols:' . max( 2, min( 5, absint( samadi_option( 'shop_columns' ) ) ) );

	$css .= ':root{' . implode( ';', $vars ) . '}';

	return $css;
}

/**
 * Preload the Persian font file (used above the fold on every page).
 */
function samadi_preload_font() {
	if ( 'vazirmatn' !== samadi_option( 'font_family' ) ) {
		return;
	}
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( SAMADI_URI . '/assets/fonts/vazirmatn/vazirmatn-arabic-wght-normal.woff2' )
	);
}
add_action( 'wp_head', 'samadi_preload_font', 2 );

/**
 * Front-end assets.
 */
function samadi_enqueue_assets() {
	$css = array(
		'samadi-base'       => 'assets/css/base.css',
		'samadi-layout'     => 'assets/css/layout.css',
		'samadi-components' => 'assets/css/components.css',
	);

	$deps = array();
	foreach ( $css as $handle => $path ) {
		wp_enqueue_style( $handle, SAMADI_URI . '/' . $path, $deps, samadi_asset_version( $path ) );
		$deps = array( $handle );
	}
	wp_add_inline_style( 'samadi-base', samadi_inline_tokens_css() );

	if ( samadi_is_wc() ) {
		// Product cards can appear anywhere (Elementor widgets, shortcodes), so card styles live in components.css.
		// Full shop/product/cart/checkout styles only load on WooCommerce views.
		if ( samadi_is_wc_view() ) {
			wp_enqueue_style( 'samadi-woocommerce', SAMADI_URI . '/assets/css/woocommerce.css', array( 'samadi-components' ), samadi_asset_version( 'assets/css/woocommerce.css' ) );
		}
		if ( is_account_page() ) {
			wp_enqueue_style( 'samadi-account', SAMADI_URI . '/assets/css/account.css', array( 'samadi-woocommerce' ), samadi_asset_version( 'assets/css/account.css' ) );
		}
	}

	if ( is_singular() || is_home() || is_archive() || is_search() || is_404() ) {
		wp_enqueue_style( 'samadi-content', SAMADI_URI . '/assets/css/content.css', array( 'samadi-components' ), samadi_asset_version( 'assets/css/content.css' ) );
	}

	wp_enqueue_script( 'samadi-navigation', SAMADI_URI . '/assets/js/navigation.js', array(), samadi_asset_version( 'assets/js/navigation.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_enqueue_script( 'samadi-theme', SAMADI_URI . '/assets/js/theme.js', array(), samadi_asset_version( 'assets/js/theme.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) );

	wp_localize_script(
		'samadi-theme',
		'samadiTheme',
		array(
			'faDigits' => (bool) samadi_option( 'persian_digits' ),
			'i18n'     => array(
				'addedToCart'  => __( 'محصول به سبد خرید افزوده شد.', 'samadi-electric' ),
				'cartUpdated'  => __( 'سبد خرید به‌روزرسانی شد.', 'samadi-electric' ),
				'menuOpen'     => __( 'باز کردن منو', 'samadi-electric' ),
				'menuClose'    => __( 'بستن منو', 'samadi-electric' ),
				'increase'     => __( 'افزایش تعداد', 'samadi-electric' ),
				'decrease'     => __( 'کاهش تعداد', 'samadi-electric' ),
				'linkCopied'   => __( 'لینک محصول کپی شد.', 'samadi-electric' ),
				'copyLink'     => __( 'لینک را کپی کنید:', 'samadi-electric' ),
				'imageOf'      => __( 'تصویر %1$s از %2$s', 'samadi-electric' ),
			),
			'persianDigits' => (bool) samadi_option( 'persian_digits' ),
		)
	);

	if ( samadi_is_wc() ) {
		// Header cart count relies on cart fragments; WooCommerce no longer loads them everywhere by default.
		wp_enqueue_script( 'wc-cart-fragments' );

		wp_enqueue_script( 'samadi-shop', SAMADI_URI . '/assets/js/shop.js', array(), samadi_asset_version( 'assets/js/shop.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) );
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'samadi_enqueue_assets' );

/**
 * Block editor: make tokens available inside the editor canvas.
 */
function samadi_block_editor_assets() {
	if ( ! is_admin() ) {
		return;
	}
	wp_add_inline_style( 'wp-block-library', samadi_inline_tokens_css() );
}
add_action( 'enqueue_block_assets', 'samadi_block_editor_assets' );
