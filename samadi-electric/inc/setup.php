<?php
/**
 * Theme supports, menus, widget areas and image sizes.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme features.
 */
function samadi_setup() {
	load_theme_textdomain( 'samadi-electric', SAMADI_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 64,
			'width'       => 220,
			'flex-height' => true,
			'flex-width'  => true,
			'unlink-homepage-logo' => false,
		)
	);

	// WooCommerce.
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 400,
			'single_image_width'    => 800,
			'product_grid'          => array(
				'default_rows'    => 4,
				'min_rows'        => 1,
				'default_columns' => 4,
				'min_columns'     => 2,
				'max_columns'     => 5,
			),
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	add_editor_style( array( 'assets/css/editor.css' ) );

	register_nav_menus(
		array(
			'primary'      => __( 'منوی اصلی (هدر)', 'samadi-electric' ),
			'mobile'       => __( 'منوی موبایل', 'samadi-electric' ),
			'footer_shop'  => __( 'فوتر: دسترسی سریع فروشگاه', 'samadi-electric' ),
			'footer_help'  => __( 'فوتر: راهنما و قوانین', 'samadi-electric' ),
			'footer_legal' => __( 'فوتر: لینک‌های نوار پایین (قوانین، حریم خصوصی)', 'samadi-electric' ),
		)
	);

	add_image_size( 'samadi-card', 400, 400, false );
	add_image_size( 'samadi-banner', 1600, 700, true );
	add_image_size( 'samadi-category', 320, 320, false );
}
add_action( 'after_setup_theme', 'samadi_setup' );

/**
 * Keep the storefront right-to-left even when the WordPress locale is not an RTL language.
 * The admin area keeps the site language's direction.
 */
function samadi_force_rtl() {
	global $wp_locale;

	if ( is_admin() || ! samadi_option( 'force_rtl' ) || ! $wp_locale instanceof WP_Locale ) {
		return;
	}

	$wp_locale->text_direction = 'rtl';
}
add_action( 'init', 'samadi_force_rtl', 0 );

/**
 * Content width for embeds and Elementor's default container width.
 */
function samadi_content_width() {
	$GLOBALS['content_width'] = (int) apply_filters( 'samadi_content_width', 1280 );
}
add_action( 'after_setup_theme', 'samadi_content_width', 0 );

/**
 * Widget areas.
 */
function samadi_widgets_init() {
	$common = array(
		'before_widget' => '<section id="%1$s" class="se-widget widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="se-widget__title widget-title">',
		'after_title'   => '</h2>',
	);

	register_sidebar(
		array_merge(
			$common,
			array(
				'id'          => 'sidebar-blog',
				'name'        => __( 'سایدبار وبلاگ', 'samadi-electric' ),
				'description' => __( 'در صفحات نوشته و آرشیو وبلاگ نمایش داده می‌شود.', 'samadi-electric' ),
			)
		)
	);

	register_sidebar(
		array_merge(
			$common,
			array(
				'id'          => 'sidebar-shop',
				'name'        => __( 'فیلترهای فروشگاه', 'samadi-electric' ),
				'description' => __( 'در آرشیو محصولات (دسکتاپ: ستون کناری، موبایل: پنل کشویی) نمایش داده می‌شود. ابزارک «فیلتر محصولات صمدی» یا ابزارک‌های فیلتر ووکامرس را اینجا قرار دهید.', 'samadi-electric' ),
			)
		)
	);

	register_sidebar(
		array_merge(
			$common,
			array(
				'id'            => 'footer-trust',
				'name'          => __( 'فوتر: نمادهای اعتماد', 'samadi-electric' ),
				'description'   => __( 'کد رسمی نماد اعتماد الکترونیکی یا ساماندهی را با ابزارک «HTML سفارشی» اینجا قرار دهید. فقط نمادهای معتبر و متعلق به همین فروشگاه.', 'samadi-electric' ),
				'before_widget' => '<div id="%1$s" class="se-trust__item %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<span class="screen-reader-text">',
				'after_title'   => '</span>',
			)
		)
	);
}
add_action( 'widgets_init', 'samadi_widgets_init' );

/**
 * Body classes.
 *
 * @param string[] $classes Classes.
 * @return string[]
 */
function samadi_body_classes( $classes ) {
	$classes[] = 'samadi-theme';

	if ( samadi_option( 'header_sticky' ) ) {
		$classes[] = 'se-has-sticky-header';
	}

	if ( samadi_show_bottom_nav() ) {
		$classes[] = 'se-has-bottom-nav';
	}

	if ( is_active_sidebar( 'sidebar-shop' ) && samadi_option( 'shop_sidebar' ) ) {
		$classes[] = 'se-has-shop-sidebar';
	}

	return $classes;
}
add_filter( 'body_class', 'samadi_body_classes' );

/**
 * Whether the mobile bottom navigation bar should render on the current view.
 *
 * @return bool
 */
function samadi_show_bottom_nav() {
	$show = (bool) samadi_option( 'mobile_bottom_nav' );

	// Not on the checkout: it competes with the place-order button.
	if ( $show && samadi_is_wc() && ( is_checkout() || is_wc_endpoint_url( 'order-received' ) ) ) {
		$show = false;
	}

	if ( is_page_template( 'elementor_canvas' ) ) {
		$show = false;
	}

	return (bool) apply_filters( 'samadi_show_bottom_nav', $show );
}

/**
 * Add a pingback header for singular views with open pings.
 */
function samadi_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">' . "\n", esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'samadi_pingback_header' );
