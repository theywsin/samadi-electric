<?php
/**
 * Search results. Product searches (post_type=product) are handled by WooCommerce's product archive.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( ! samadi_elementor_location( 'archive' ) ) {
	get_template_part( 'template-parts/archive/posts', null, array( 'search' => true ) );
}

get_footer();
