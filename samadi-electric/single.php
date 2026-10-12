<?php
/**
 * Single post.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( ! samadi_elementor_location( 'single' ) ) {
	get_template_part( 'template-parts/content/single' );
}

get_footer();
