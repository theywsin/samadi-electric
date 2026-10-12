<?php
/**
 * Front page.
 *
 * If a static page is set as the homepage (Settings → Reading), its content is rendered — build it
 * with Elementor and the Samadi widgets. Otherwise the theme's own homepage sections are shown,
 * configured from Customizer → Samadi Electric → Homepage.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( 'page' === get_option( 'show_on_front' ) && get_option( 'page_on_front' ) ) {
	if ( ! samadi_elementor_location( 'single' ) ) {
		get_template_part( 'template-parts/content/page', null, array( 'is_front' => true ) );
	}
} else {
	get_template_part( 'template-parts/home/home' );
}

get_footer();
