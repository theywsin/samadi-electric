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
		// Built-in brand mark + site name text.
		printf(
			'<img class="se-logo__mark" src="%s" width="44" height="44" alt="" aria-hidden="true">',
			esc_url( SAMADI_URI . '/assets/images/logo-mark.svg' )
		);
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
			samadi_get_icon( 'chevron-left', array( 'size' => 16 ) ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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

/**
 * Render a flat list of footer links.
 *
 * @param array<int, array{url:string,label:string}> $links Links.
 * @return string Escaped HTML.
 */
function samadi_footer_links_html( $links ) {
	if ( ! $links ) {
		return '';
	}

	$html = '<ul class="se-footer__links">';
	foreach ( $links as $link ) {
		$html .= sprintf( '<li><a href="%s">%s</a></li>', esc_url( $link['url'] ), esc_html( $link['label'] ) );
	}

	return $html . '</ul>';
}

/**
 * Footer link columns (between the brand column and the contact column).
 *
 * Every column is built only from real data: an assigned menu, product categories,
 * WooCommerce account pages or published policy pages. Empty columns are skipped.
 *
 * @return array<string, array{title:string,html:string}>
 */
function samadi_footer_link_columns() {
	$columns = array();
	$menu    = static function ( $location ) {
		return (string) wp_nav_menu(
			array(
				'theme_location' => $location,
				'container'      => false,
				'menu_class'     => 'se-footer__links',
				'depth'          => 1,
				'fallback_cb'    => false,
				'echo'           => false,
			)
		);
	};

	// Shop: assigned menu, or the top-level product categories.
	if ( has_nav_menu( 'footer_shop' ) ) {
		$columns['shop'] = array(
			'title' => __( 'دسته‌بندی‌های اصلی', 'samadi-electric' ),
			'html'  => $menu( 'footer_shop' ),
		);
	} elseif ( samadi_is_wc() ) {
		$links = array();
		foreach ( array_slice( samadi_mega_menu_categories(), 0, 6 ) as $cat ) {
			$url = get_term_link( $cat['term'] );
			if ( ! is_wp_error( $url ) ) {
				$links[] = array(
					'url'   => $url,
					'label' => $cat['term']->name,
				);
			}
		}
		$columns['shop'] = array(
			'title' => __( 'دسته‌بندی‌های اصلی', 'samadi-electric' ),
			'html'  => samadi_footer_links_html( $links ),
		);
	}

	// Customer: WooCommerce account, orders, cart, wishlist and wholesale.
	if ( samadi_is_wc() ) {
		$links = array(
			array(
				'url'   => wc_get_page_permalink( 'myaccount' ),
				'label' => __( 'حساب کاربری من', 'samadi-electric' ),
			),
			array(
				'url'   => wc_get_account_endpoint_url( 'orders' ),
				'label' => __( 'پیگیری سفارش‌ها', 'samadi-electric' ),
			),
			array(
				'url'   => wc_get_cart_url(),
				'label' => __( 'سبد خرید', 'samadi-electric' ),
			),
		);
		if ( samadi_wishlist_url() ) {
			$links[] = array(
				'url'   => samadi_wishlist_url(),
				'label' => __( 'علاقه‌مندی‌ها', 'samadi-electric' ),
			);
		}
		if ( samadi_wholesale_url() ) {
			$links[] = array(
				'url'   => samadi_wholesale_url(),
				'label' => (string) samadi_option( 'header_wholesale_label' ),
			);
		}
		$columns['customer'] = array(
			'title' => __( 'خدمات مشتریان', 'samadi-electric' ),
			'html'  => samadi_footer_links_html( $links ),
		);
	}

	// Help: assigned menu, or the published privacy / terms pages.
	if ( has_nav_menu( 'footer_help' ) ) {
		$columns['help'] = array(
			'title' => wp_get_nav_menu_name( 'footer_help' ),
			'html'  => $menu( 'footer_help' ),
		);
	} else {
		$links = array();
		$pages = array( (int) get_option( 'wp_page_for_privacy_policy' ) );
		if ( samadi_is_wc() ) {
			$pages[] = (int) wc_terms_and_conditions_page_id();
		}
		foreach ( array_unique( array_filter( $pages ) ) as $page_id ) {
			if ( 'publish' === get_post_status( $page_id ) ) {
				$links[] = array(
					'url'   => get_permalink( $page_id ),
					'label' => get_the_title( $page_id ),
				);
			}
		}
		if ( $links ) {
			$columns['help'] = array(
				'title' => __( 'راهنما و قوانین', 'samadi-electric' ),
				'html'  => samadi_footer_links_html( $links ),
			);
		}
	}

	$columns = array_filter(
		$columns,
		static function ( $column ) {
			return '' !== trim( $column['html'] );
		}
	);

	/**
	 * Filter footer link columns.
	 *
	 * @param array $columns Columns keyed by slug: { title, html (escaped) }.
	 */
	return apply_filters( 'samadi_footer_link_columns', $columns );
}
