<?php
/**
 * Main fallback template (blog index and anything without a more specific template).
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( ! samadi_elementor_location( 'archive' ) ) {
	get_template_part( 'template-parts/archive/posts' );
}

get_footer();
