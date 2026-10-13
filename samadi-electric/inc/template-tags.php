<?php
/**
 * Template tags used across templates.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

/**
 * Site logo (custom logo, optional mobile logo, or text fallback).
 *
 * @param string $context 'header' | 'footer'.
 */
function samadi_site_logo( $context = 'header' ) {
	$name        = get_bloginfo( 'name', 'display' );
	$home        = home_url( '/' );
	$logo_id     = (int) get_theme_mod( 'custom_logo' );
	$mobile_id   = (int) samadi_option( 'mobile_logo' );
	$is_home_h1  = 'header' === $context && is_front_page() && ! is_paged();
	$tag         = $is_home_h1 ? 'h1' : 'div';

	printf( '<%s class="se-logo se-logo--%s">', esc_attr( $tag ), esc_attr( $context ) );
	printf( '<a class="se-logo__link" href="%s" rel="home">', esc_url( $home ) );

	if ( $logo_id ) {
		echo wp_get_attachment_image(
			$logo_id,
			'full',
			false,
			array(
				'class'         => 'se-logo__img' . ( $mobile_id ? ' se-logo__img--desktop' : '' ),
				'alt'           => $name,
				'loading'       => false,
				'fetchpriority' => 'header' === $context ? 'high' : 'auto',
			)
		);
		if ( $mobile_id && 'header' === $context ) {
			echo wp_get_attachment_image(
				$mobile_id,
				'full',
				false,
				array(
					'class'   => 'se-logo__img se-logo__img--mobile',
					'alt'     => $name,
					'loading' => false,
				)
			);
		}
	} else {
		// Built-in brand mark (inline so the arcs follow the context colour) + site name text.
		echo '<svg class="se-logo__mark" viewBox="0 0 36 40" width="36" height="40" aria-hidden="true" focusable="false">'
			. '<path fill="currentColor" d="M31.92.78C6.3-3.47-3.14 10.35.9 23.1l10.79-7.44C8.99 8.22 22.48 5.04 31.92 7.16Z"/>'
			. '<path fill="currentColor" d="M2.25 39.04C26.52 44.35 40.01 26.29 33.27 16.72L22.48 25.23C25.18 32.67 11.69 36.92 2.25 32.67Z"/>'
			. '<path fill="#D9EE8C" d="M29 1 17 15h8l-3 11 14-15h-8l1-10Z"/>'
			. '</svg>';
		echo '<span class="se-logo__text"><span class="se-logo__name">' . esc_html( $name ) . '</span>';
		$tagline = get_bloginfo( 'description', 'display' );
		if ( $tagline ) {
			echo '<span class="se-logo__tagline">' . esc_html( $tagline ) . '</span>';
		}
		echo '</span>';
	}

	echo '</a>';
	printf( '</%s>', esc_attr( $tag ) );
}

/**
 * Breadcrumb, delegating to the active SEO plugin when available to avoid duplicate schema.
 */
function samadi_breadcrumb() {
	if ( ! samadi_option( 'breadcrumb_enable' ) || is_front_page() ) {
		return;
	}

	$source = samadi_option( 'breadcrumb_source' );

	if ( 'auto' === $source ) {
		if ( function_exists( 'yoast_breadcrumb' ) && class_exists( 'WPSEO_Options' ) && WPSEO_Options::get( 'breadcrumbs-enable' ) ) {
			yoast_breadcrumb( '<nav class="se-breadcrumb" aria-label="' . esc_attr__( 'مسیر راهنما', 'samadi-electric' ) . '">', '</nav>' );
			return;
		}
		if ( function_exists( 'rank_math_the_breadcrumbs' ) && class_exists( '\RankMath\Helper' ) && \RankMath\Helper::is_breadcrumbs_enabled() ) {
			echo '<div class="se-breadcrumb">';
			rank_math_the_breadcrumbs();
			echo '</div>';
			return;
		}
	}

	if ( samadi_is_wc() && function_exists( 'woocommerce_breadcrumb' ) ) {
		woocommerce_breadcrumb( samadi_wc_breadcrumb_args() );
		return;
	}

	samadi_simple_breadcrumb();
}

/**
 * WooCommerce breadcrumb markup arguments.
 *
 * @return array
 */
function samadi_wc_breadcrumb_args() {
	return array(
		'delimiter'   => '<span class="se-breadcrumb__sep" aria-hidden="true">/</span>',
		'wrap_before' => '<nav class="se-breadcrumb woocommerce-breadcrumb" aria-label="' . esc_attr__( 'مسیر راهنما', 'samadi-electric' ) . '">',
		'wrap_after'  => '</nav>',
		'before'      => '<span class="se-breadcrumb__item">',
		'after'       => '</span>',
		'home'        => _x( 'خانه', 'breadcrumb', 'samadi-electric' ),
	);
}

/**
 * Minimal breadcrumb (no structured data — schema is left to WooCommerce / SEO plugins).
 */
function samadi_simple_breadcrumb() {
	$items   = array();
	$items[] = array( _x( 'خانه', 'breadcrumb', 'samadi-electric' ), home_url( '/' ) );

	if ( is_singular( 'post' ) ) {
		$page_for_posts = (int) get_option( 'page_for_posts' );
		if ( $page_for_posts ) {
			$items[] = array( get_the_title( $page_for_posts ), get_permalink( $page_for_posts ) );
		}
		$cats = get_the_category();
		if ( $cats ) {
			$items[] = array( $cats[0]->name, get_category_link( $cats[0] ) );
		}
		$items[] = array( get_the_title(), '' );
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $ancestor ) {
			$items[] = array( get_the_title( $ancestor ), get_permalink( $ancestor ) );
		}
		$items[] = array( get_the_title(), '' );
	} elseif ( is_search() ) {
		/* translators: %s: search query. */
		$items[] = array( sprintf( __( 'نتایج جستجو برای «%s»', 'samadi-electric' ), get_search_query() ), '' );
	} elseif ( is_archive() ) {
		$items[] = array( wp_strip_all_tags( get_the_archive_title() ), '' );
	} elseif ( is_home() ) {
		$items[] = array( single_post_title( '', false ), '' );
	} elseif ( is_404() ) {
		$items[] = array( __( 'صفحه پیدا نشد', 'samadi-electric' ), '' );
	}

	echo '<nav class="se-breadcrumb" aria-label="' . esc_attr__( 'مسیر راهنما', 'samadi-electric' ) . '"><ol class="se-breadcrumb__list">';
	$last = count( $items ) - 1;
	foreach ( $items as $i => $item ) {
		echo '<li class="se-breadcrumb__item">';
		if ( $item[1] && $i !== $last ) {
			printf( '<a href="%s">%s</a>', esc_url( $item[1] ), esc_html( $item[0] ) );
		} else {
			printf( '<span aria-current="page">%s</span>', esc_html( $item[0] ) );
		}
		echo '</li>';
	}
	echo '</ol></nav>';
}

/**
 * Pagination for post archives.
 */
function samadi_pagination() {
	the_posts_pagination(
		array(
			'mid_size'           => 1,
			'prev_text'          => samadi_get_icon( 'chevron-right', array( 'size' => 18 ) ) . '<span class="screen-reader-text">' . esc_html__( 'صفحه قبل', 'samadi-electric' ) . '</span>',
			'next_text'          => '<span class="screen-reader-text">' . esc_html__( 'صفحه بعد', 'samadi-electric' ) . '</span>' . samadi_get_icon( 'chevron-left', array( 'size' => 18 ) ),
			'before_page_number' => '<span class="screen-reader-text">' . esc_html__( 'صفحه', 'samadi-electric' ) . ' </span>',
			'class'              => 'se-pagination',
		)
	);
}

/**
 * Post meta line (date, author, categories).
 */
function samadi_post_meta() {
	echo '<div class="se-post-meta">';
	printf(
		'<time class="se-post-meta__date" datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() )
	);
	printf(
		'<span class="se-post-meta__author">%s</span>',
		esc_html( get_the_author() )
	);
	$cats = get_the_category_list( '، ' );
	if ( $cats ) {
		echo '<span class="se-post-meta__cats">' . wp_kses_post( $cats ) . '</span>';
	}
	echo '</div>';
}

/**
 * Estimated reading time of the current post in minutes (≈200 words per minute).
 *
 * @param int|WP_Post|null $post Post.
 * @return int
 */
function samadi_reading_time( $post = null ) {
	$text  = wp_strip_all_tags( (string) get_post_field( 'post_content', $post ) );
	$words = preg_split( '/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY );

	return max( 1, (int) round( count( (array) $words ) / 200 ) );
}

/**
 * "N دقیقه مطالعه" label.
 *
 * @param int|WP_Post|null $post Post.
 * @return string
 */
function samadi_reading_time_label( $post = null ) {
	/* translators: %s: reading time in minutes. */
	return sprintf( __( '%s دقیقه مطالعه', 'samadi-electric' ), samadi_number( samadi_reading_time( $post ) ) );
}

/**
 * Section header with optional "view all" link.
 *
 * @param string $title     Title.
 * @param string $link      URL.
 * @param string $link_text Link text.
 * @param string $tag       Heading tag.
 */
function samadi_section_head( $title, $link = '', $link_text = '', $tag = 'h2' ) {
	$tag = in_array( $tag, array( 'h2', 'h3', 'h4' ), true ) ? $tag : 'h2';
	echo '<div class="se-section-head">';
	printf( '<%1$s class="se-section-head__title">%2$s</%1$s>', esc_attr( $tag ), esc_html( $title ) );
	if ( $link ) {
		printf(
			'<a class="se-section-head__link" href="%s">%s%s</a>',
			esc_url( $link ),
			esc_html( $link_text ? $link_text : __( 'مشاهده همه', 'samadi-electric' ) ),
			samadi_get_icon( 'arrow-left', array( 'size' => 14 ) ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}
	echo '</div>';
}

/**
 * Configured benefit items.
 *
 * @return array<int, array{icon:string,title:string,text:string}>
 */
function samadi_benefit_items() {
	$items = array();
	for ( $i = 1; $i <= 5; $i++ ) {
		$title = trim( (string) samadi_option( "benefit_{$i}_title" ) );
		if ( '' === $title ) {
			continue;
		}
		$items[] = array(
			'icon'  => (string) samadi_option( "benefit_{$i}_icon" ),
			'title' => $title,
			'text'  => (string) samadi_option( "benefit_{$i}_text" ),
		);
	}

	return $items;
}

/**
 * Social links configured in the Customizer.
 *
 * @return array<string, array{url:string,label:string,icon:string}>
 */
function samadi_social_links() {
	$networks = array(
		'instagram' => array( __( 'اینستاگرام', 'samadi-electric' ), 'instagram' ),
		'telegram'  => array( __( 'تلگرام', 'samadi-electric' ), 'send' ),
		'whatsapp'  => array( __( 'واتس‌اپ', 'samadi-electric' ), 'message-circle' ),
		'linkedin'  => array( __( 'لینکدین', 'samadi-electric' ), 'linkedin' ),
	);

	$links = array();
	foreach ( $networks as $key => $meta ) {
		$url = (string) samadi_option( 'social_' . $key );
		if ( $url ) {
			$links[ $key ] = array(
				'url'   => $url,
				'label' => $meta[0],
				'icon'  => $meta[1],
			);
		}
	}

	return $links;
}

/**
 * Whether the content of the current singular post is built with Elementor.
 *
 * @param int|null $post_id Post ID.
 * @return bool
 */
function samadi_is_built_with_elementor( $post_id = null ) {
	if ( ! samadi_is_elementor() || ! class_exists( '\Elementor\Plugin' ) ) {
		return false;
	}
	$post_id  = $post_id ? $post_id : get_the_ID();
	$document = $post_id ? \Elementor\Plugin::$instance->documents->get( $post_id ) : null;

	return $document && $document->is_built_with_elementor();
}
