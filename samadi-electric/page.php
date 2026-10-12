<?php
/**
 * Pages. Elementor-built pages render through the_content() inside the theme container
 * (use Elementor's "Full Width"/"Canvas" page layouts for edge-to-edge designs).
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( ! samadi_elementor_location( 'single' ) ) {
	get_template_part( 'template-parts/content/page' );
}

get_footer();
