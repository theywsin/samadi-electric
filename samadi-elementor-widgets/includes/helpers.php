<?php
/**
 * Helper functions.
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugin setting defaults.
 *
 * @return array<string, mixed>
 */
function samadi_ew_setting_defaults() {
	return array(
		'quote_page'             => 0,
		'quote_email'            => '',
		'quote_button'           => 'all',
		'quote_button_label'     => 'استعلام قیمت',
		'price_on_request_empty' => true,
		'live_search'            => true,
		'search_sku'             => true,
		'filter_noindex'         => true,
		'recently_viewed'        => true,
		'recently_viewed_limit'  => 8,
	);
}

/**
 * Read a plugin setting.
 *
 * @param string $key Key.
 * @return mixed
 */
function samadi_ew_setting( $key ) {
	$settings = get_option( 'samadi_ew_settings', array() );
	$defaults = samadi_ew_setting_defaults();
	$settings = is_array( $settings ) ? array_merge( $defaults, $settings ) : $defaults;

	return array_key_exists( $key, $settings ) ? $settings[ $key ] : null;
}

/**
 * Inline SVG icon (Lucide subset, ISC).
 *
 * @param string $name Icon.
 * @param int    $size Size.
 * @param string $label Accessible label (empty = decorative).
 * @return string
 */
function samadi_ew_icon( $name, $size = 24, $label = '' ) {
	static $icons = null;

	if ( null === $icons ) {
		$icons = require SAMADI_EW_DIR . 'includes/icons-data.php';
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
 * Icon choices for Elementor controls.
 *
 * @return array<string, string>
 */
function samadi_ew_icon_choices() {
	$icons = require SAMADI_EW_DIR . 'includes/icons-data.php';
	$keys  = array_keys( $icons );

	return array_combine( $keys, $keys );
}

/**
 * Brand taxonomy (WooCommerce core Brands, or a compatible plugin).
 *
 * @return string
 */
function samadi_ew_brand_taxonomy() {
	if ( function_exists( 'samadi_brand_taxonomy' ) ) {
		return samadi_brand_taxonomy();
	}

	foreach ( array( 'product_brand', 'pwb-brand', 'yith_product_brand' ) as $taxonomy ) {
		if ( taxonomy_exists( $taxonomy ) ) {
			return $taxonomy;
		}
	}

	return '';
}

/**
 * Persian digits in text nodes (delegates to the theme when active).
 *
 * @param string $html HTML.
 * @return string
 */
function samadi_ew_digits( $html ) {
	if ( function_exists( 'samadi_fa_digits' ) && function_exists( 'samadi_option' ) ) {
		return samadi_option( 'persian_digits' ) ? samadi_fa_digits( $html ) : $html;
	}

	return $html;
}

/**
 * Format a number honoring the theme's Persian digits option.
 *
 * @param int|float $number Number.
 * @return string
 */
function samadi_ew_number( $number ) {
	return samadi_ew_digits( number_format_i18n( (float) $number ) );
}

/**
 * Quote / wholesale page URL, optionally for a product.
 *
 * @param int $product_id Product ID.
 * @return string Empty when no quote page is configured.
 */
function samadi_ew_quote_url( $product_id = 0 ) {
	$page = absint( samadi_ew_setting( 'quote_page' ) );

	if ( ! $page && function_exists( 'samadi_option' ) ) {
		$page = absint( samadi_option( 'wholesale_page' ) );
	}

	if ( ! $page || 'publish' !== get_post_status( $page ) ) {
		return '';
	}

	$url = get_permalink( $page );

	if ( $product_id ) {
		$url = add_query_arg( 'product_id', absint( $product_id ), $url );
	}

	return $url . '#se-quote-form';
}

/**
 * Render product cards for a list of product IDs using the active theme's WooCommerce loop template.
 *
 * @param int[] $ids     Product IDs (order preserved).
 * @param int   $columns Columns.
 * @return string
 */
function samadi_ew_render_product_ids( $ids, $columns = 4 ) {
	$ids = array_values( array_filter( array_map( 'absint', (array) $ids ) ) );

	if ( ! $ids || ! function_exists( 'wc_get_products' ) ) {
		return '';
	}

	return do_shortcode(
		sprintf(
			'[products ids="%s" orderby="post__in" columns="%d" limit="%d"]',
			esc_attr( implode( ',', $ids ) ),
			absint( $columns ),
			count( $ids )
		)
	);
}

/**
 * Kses rules for Elementor/term rich text descriptions (safe subset).
 *
 * @return array
 */
function samadi_ew_kses_inline() {
	return array(
		'br'     => array(),
		'strong' => array(),
		'b'      => array(),
		'em'     => array(),
		'span'   => array( 'class' => true ),
		'a'      => array(
			'href'   => true,
			'target' => true,
			'rel'    => true,
		),
	);
}
