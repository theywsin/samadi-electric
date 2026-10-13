<?php
/**
 * Helper functions.
 *
 * @package Samadi_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Option that stores every plugin setting.
 */
const SAMADI_CORE_OPTION = 'samadi_core_settings';

/**
 * Plugin setting defaults.
 *
 * @return array<string, mixed>
 */
function samadi_core_setting_defaults() {
	return array(
		// Modules.
		'module_wishlist'        => true,
		'module_compare'         => true,
		'module_newsletter'      => true,
		'module_jalali'          => true,
		'module_checkout'        => true,
		'module_schema'          => true,
		'module_security'        => true,

		// Wishlist.
		'wishlist_page'          => 0,
		'wishlist_single'        => true,

		// Compare.
		'compare_page'           => 0,
		'compare_max'            => 4,
		'compare_single'         => true,
		'compare_card'           => true,

		// Newsletter.
		'newsletter_placeholder' => 'شماره موبایل خود را وارد کنید',
		'newsletter_button'      => 'عضویت',
		'newsletter_success'     => 'عضویت شما با موفقیت ثبت شد.',
		'newsletter_notify'      => '',

		// Jalali dates.
		'jalali_admin'           => false,
		'jalali_digits'          => true,

		// Checkout.
		'checkout_mobile'        => true,
		'checkout_postcode'      => true,
		'checkout_national_code' => 'off',
		'checkout_hide_company'  => true,
		'checkout_hide_address2' => false,
		'checkout_default_iran'  => true,

		// Schema.
		'schema_store_type'      => 'Store',
		'schema_search'          => true,

		// Security.
		'security_xmlrpc'        => true,
		'security_version'       => true,
		'security_users'         => true,
		'security_login_errors'  => true,

		// Housekeeping.
		'delete_data'            => false,
	);
}

/**
 * All plugin settings merged with their defaults.
 *
 * @return array<string, mixed>
 */
function samadi_core_settings() {
	$settings = get_option( SAMADI_CORE_OPTION, array() );
	$defaults = samadi_core_setting_defaults();

	return is_array( $settings ) ? array_merge( $defaults, array_intersect_key( $settings, $defaults ) ) : $defaults;
}

/**
 * Read one plugin setting.
 *
 * @param string $key Key.
 * @return mixed
 */
function samadi_core_setting( $key ) {
	$settings = samadi_core_settings();

	return array_key_exists( $key, $settings ) ? $settings[ $key ] : null;
}

/**
 * Whether a module is switched on (and its requirements are met).
 *
 * @param string $module wishlist|compare|newsletter|jalali|checkout|schema|security.
 * @return bool
 */
function samadi_core_module_enabled( $module ) {
	$enabled = (bool) samadi_core_setting( 'module_' . $module );

	if ( $enabled && in_array( $module, array( 'wishlist', 'compare', 'checkout' ), true ) && ! class_exists( 'WooCommerce' ) ) {
		$enabled = false;
	}

	/**
	 * Force a module on or off from code.
	 *
	 * @param bool   $enabled Whether the module runs.
	 * @param string $module  Module key.
	 */
	return (bool) apply_filters( 'samadi_core_module_enabled', $enabled, $module );
}

/**
 * Convert Persian (۰-۹) and Arabic-Indic (٠-٩) digits to Latin digits.
 *
 * @param string $value Value.
 * @return string
 */
function samadi_core_en_digits( $value ) {
	return strtr(
		(string) $value,
		array(
			'۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
			'٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
		)
	);
}

/**
 * Convert Latin digits to Persian digits in text nodes only (never inside tags, attributes or entities).
 *
 * @param string $html HTML or text.
 * @return string
 */
function samadi_core_fa_digits( $html ) {
	if ( function_exists( 'samadi_fa_digits' ) ) {
		return samadi_fa_digits( $html );
	}

	if ( ! is_string( $html ) || '' === $html || ! preg_match( '/\d/', $html ) ) {
		return $html;
	}

	$parts = preg_split( '/(<[^>]*>|&#?[a-z0-9]+;)/iu', $html, -1, PREG_SPLIT_DELIM_CAPTURE );

	if ( false === $parts ) {
		return $html;
	}

	foreach ( $parts as $i => $part ) {
		if ( '' === $part || '<' === $part[0] || '&' === $part[0] ) {
			continue;
		}
		$parts[ $i ] = strtr( $part, array( '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹' ) );
	}

	return implode( '', $parts );
}

/**
 * Whether numbers are shown with Persian digits (follows the theme's setting when the theme is active).
 *
 * @return bool
 */
function samadi_core_persian_digits() {
	$on = function_exists( 'samadi_option' ) ? (bool) samadi_option( 'persian_digits' ) : 0 === strpos( get_locale(), 'fa' );

	return (bool) apply_filters( 'samadi_core_persian_digits', $on );
}

/**
 * Format a number for display.
 *
 * @param int|float $number Number.
 * @return string
 */
function samadi_core_number( $number ) {
	$out = number_format_i18n( (float) $number );

	return samadi_core_persian_digits() ? samadi_core_fa_digits( $out ) : $out;
}

/**
 * Normalize an Iranian mobile number to 09XXXXXXXXX.
 *
 * Accepts Persian/Arabic digits, spaces, dashes, parentheses and the +98 / 0098 / 98 prefixes.
 *
 * @param string $mobile Raw input.
 * @return string Normalized number, or the cleaned input when it is not a valid Iranian mobile.
 */
function samadi_core_normalize_mobile( $mobile ) {
	$clean = preg_replace( '/[\s\-\(\)\.]+/u', '', samadi_core_en_digits( trim( (string) $mobile ) ) );
	$clean = (string) $clean;

	if ( preg_match( '/^(?:\+98|0098|98)(9\d{9})$/', $clean, $m ) ) {
		return '0' . $m[1];
	}

	if ( preg_match( '/^9\d{9}$/', $clean ) ) {
		return '0' . $clean;
	}

	return $clean;
}

/**
 * Whether a value is a valid Iranian mobile number (after normalization).
 *
 * @param string $mobile Mobile.
 * @return bool
 */
function samadi_core_is_mobile( $mobile ) {
	return (bool) preg_match( '/^09\d{9}$/', samadi_core_normalize_mobile( $mobile ) );
}

/**
 * Whether a value looks like an Iranian postal code: 10 digits, not starting with 0.
 *
 * @param string $postcode Postcode.
 * @return bool
 */
function samadi_core_is_postcode( $postcode ) {
	$postcode = preg_replace( '/[\s\-]+/u', '', samadi_core_en_digits( (string) $postcode ) );

	return (bool) preg_match( '/^[1-9]\d{9}$/', (string) $postcode );
}

/**
 * Whether a value is a valid Iranian national code (کد ملی), using its check digit.
 *
 * @param string $code Code.
 * @return bool
 */
function samadi_core_is_national_code( $code ) {
	$code = preg_replace( '/[\s\-]+/u', '', samadi_core_en_digits( (string) $code ) );

	if ( ! preg_match( '/^\d{10}$/', (string) $code ) || preg_match( '/^(\d)\1{9}$/', (string) $code ) ) {
		return false;
	}

	$sum = 0;
	for ( $i = 0; $i < 9; $i++ ) {
		$sum += (int) $code[ $i ] * ( 10 - $i );
	}
	$rest  = $sum % 11;
	$check = (int) $code[9];

	return $rest < 2 ? $check === $rest : $check === 11 - $rest;
}

/**
 * Inline SVG icon (Lucide subset, ISC).
 *
 * @param string $name  Icon.
 * @param int    $size  Size in px.
 * @param string $label Accessible label (empty = decorative).
 * @return string
 */
function samadi_core_icon( $name, $size = 20, $label = '' ) {
	static $icons = null;

	if ( null === $icons ) {
		$icons = require SAMADI_CORE_DIR . 'includes/icons-data.php';
	}

	if ( empty( $icons[ $name ] ) ) {
		return '';
	}

	$a11y = $label ? sprintf( ' role="img" aria-label="%s"', esc_attr( $label ) ) : ' aria-hidden="true" focusable="false"';

	return sprintf(
		'<svg class="se-icon se-icon--%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"%3$s>%4$s</svg>',
		esc_attr( $name ),
		absint( $size ),
		$a11y,
		$icons[ $name ]
	);
}

/**
 * Render a plugin template. Themes may override it by copying it to `samadi-core/<name>.php`.
 *
 * @param string $name Template name without extension.
 * @param array  $args Variables for the template.
 * @return string
 */
function samadi_core_template( $name, $args = array() ) {
	$name = sanitize_file_name( $name );
	$file = locate_template( array( 'samadi-core/' . $name . '.php' ) );

	if ( ! $file ) {
		$file = SAMADI_CORE_DIR . 'templates/' . $name . '.php';
	}

	/**
	 * Path of a plugin template.
	 *
	 * @param string $file Absolute path.
	 * @param string $name Template name.
	 */
	$file = (string) apply_filters( 'samadi_core_template', $file, $name );

	if ( ! is_readable( $file ) ) {
		return '';
	}

	ob_start();
	load_template( $file, false, $args );

	return (string) ob_get_clean();
}

/**
 * Read a list of product IDs from a cookie ("12.45.7").
 *
 * @param string $name Cookie name.
 * @param int    $max  Maximum number of IDs.
 * @return int[]
 */
function samadi_core_cookie_ids( $name, $max = 100 ) {
	if ( empty( $_COOKIE[ $name ] ) || ! is_string( $_COOKIE[ $name ] ) ) {
		return array();
	}

	$raw = sanitize_text_field( wp_unslash( $_COOKIE[ $name ] ) );
	$ids = array_values( array_unique( array_filter( array_map( 'absint', explode( '.', $raw ) ) ) ) );

	return array_slice( $ids, 0, max( 1, (int) $max ) );
}

/**
 * Store a list of product IDs in a cookie readable by the front-end script (not HttpOnly).
 *
 * @param string $name Cookie name.
 * @param int[]  $ids  IDs.
 */
function samadi_core_set_cookie_ids( $name, $ids ) {
	$value = implode( '.', array_map( 'absint', $ids ) );

	if ( headers_sent() ) {
		return;
	}

	$expires = $value ? time() + YEAR_IN_SECONDS : time() - HOUR_IN_SECONDS;

	setcookie(
		$name,
		$value,
		array(
			'expires'  => $expires,
			'path'     => COOKIEPATH ? COOKIEPATH : '/',
			'domain'   => COOKIE_DOMAIN ? COOKIE_DOMAIN : '',
			'secure'   => is_ssl(),
			'httponly' => false,
			'samesite' => 'Lax',
		)
	);

	if ( $value ) {
		$_COOKIE[ $name ] = $value;
	} else {
		unset( $_COOKIE[ $name ] );
	}
}

/**
 * Keep a page out of full-page caches (wishlist and compare pages differ per visitor).
 */
function samadi_core_no_page_cache() {
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}
	nocache_headers();
}

/**
 * A product that may be listed to visitors.
 *
 * @param int $product_id Product ID.
 * @return WC_Product|null
 */
function samadi_core_visible_product( $product_id ) {
	$product = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : null;

	if ( ! $product || 'publish' !== $product->get_status() || ! $product->is_visible() ) {
		return null;
	}

	return $product;
}
