<?php
/**
 * Post, category, tag, author and date archives.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( ! samadi_elementor_location( 'archive' ) ) {
	get_template_part( 'template-parts/archive/posts' );
}

get_footer();
