<?php
/**
 * SEO cooperation. The theme does NOT output titles, meta descriptions, canonicals or schema of
 * its own: those are left to WordPress core, WooCommerce and the active SEO plugin (Yoast / Rank Math).
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

/**
 * Archive titles without the "Category:" prefix.
 *
 * @param string $title Title.
 * @return string
 */
function samadi_archive_title( $title ) {
	if ( is_category() || is_tag() || is_tax() ) {
		return single_term_title( '', false );
	}
	if ( is_post_type_archive() ) {
		return post_type_archive_title( '', false );
	}

	return $title;
}
add_filter( 'get_the_archive_title', 'samadi_archive_title' );
