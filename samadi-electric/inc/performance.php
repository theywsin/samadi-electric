<?php
/**
 * Front-end performance tweaks.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remove the emoji detection script and styles (not needed for this storefront).
 */
function samadi_disable_emojis() {
	if ( ! apply_filters( 'samadi_disable_emojis', true ) ) {
		return;
	}
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'samadi_disable_emojis' );

/**
 * The first product image on single product pages is the LCP element: never lazy-load it.
 *
 * @param array        $attr       Attributes.
 * @param WP_Post      $attachment Attachment.
 * @param string|int[] $size       Size.
 * @return array
 */
function samadi_lcp_image_attributes( $attr, $attachment, $size ) {
	static $done = false;

	if ( $done || ! samadi_is_wc() || ! is_product() || is_admin() ) {
		return $attr;
	}

	if ( 'woocommerce_single' === $size || ( isset( $attr['class'] ) && false !== strpos( $attr['class'], 'wp-post-image' ) ) ) {
		$attr['loading']       = 'eager';
		$attr['fetchpriority'] = 'high';
		$done                  = true;
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'samadi_lcp_image_attributes', 20, 3 );

/**
 * Do not load WooCommerce block styles on pages that cannot contain blocks content from WooCommerce.
 * Elementor-built pages render widgets, not blocks.
 */
function samadi_trim_block_styles() {
	if ( is_admin() || ! samadi_is_elementor() || ! is_singular() ) {
		return;
	}
	if ( samadi_is_built_with_elementor() && ! ( samadi_is_wc() && ( is_cart() || is_checkout() ) ) ) {
		wp_dequeue_style( 'wc-blocks-style' );
	}
}
add_action( 'wp_enqueue_scripts', 'samadi_trim_block_styles', 100 );
