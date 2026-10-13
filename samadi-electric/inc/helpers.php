<?php
/**
 * Generic helpers: options, icons, digits and plugin detection.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default values for every theme option (Customizer theme_mods).
 *
 * @return array<string, mixed>
 */
function samadi_defaults() {
	static $defaults = null;

	if ( null !== $defaults ) {
		return $defaults;
	}

	$defaults = array(
		// Colors.
		'color_primary'          => '#123f35',
		'color_brand'            => '#123f35',
		'color_accent'           => '#d9ee8c',
		'color_bg'               => '#f6f7f4',
		'color_surface'          => '#FFFFFF',
		'color_text'             => '#202b27',
		'color_muted'            => '#7a837e',
		'color_border'           => '#e6eae6',
		'color_sale'             => '#db6550',

		// Typography.
		'font_family'            => 'vazirmatn',
		'font_custom_family'     => '',
		'font_weight_body'       => 400,
		'font_weight_heading'    => 700,
		'font_size_base'         => 15,
		'persian_digits'         => true,
		'force_rtl'              => true,

		// Header.
		'mobile_logo'            => 0,
		'header_sticky'          => true,
		'header_show_categories' => true,
		'header_categories_label' => __( 'دسته‌بندی محصولات', 'samadi-electric' ),
		'header_announcement'    => __( 'ارسال رایگان سفارش‌های بالای ۳ میلیون تومان  •  خرید مطمئن، روشنایی ماندگار', 'samadi-electric' ),
		'header_phone_label'     => __( 'مشاوره خرید:', 'samadi-electric' ),
		'header_show_search'     => true,
		'header_show_account'    => true,
		'header_wholesale_label' => __( 'خرید عمده', 'samadi-electric' ),

		// Contact & social.
		'contact_phone'          => '۰۲۱–۳۳۹۵۲۴۸۰',
		'contact_mobile'         => '',
		'contact_email'          => '',
		'contact_address'        => __( 'تهران، لاله‌زار جنوبی، خیابان منوچهری، پلاک ۱۲۴', 'samadi-electric' ),
		'contact_hours'          => __( 'شنبه تا پنجشنبه، ۹ تا ۱۸', 'samadi-electric' ),
		'social_instagram'       => '',
		'social_telegram'        => '',
		'social_whatsapp'        => '',
		'social_linkedin'        => '',

		// Wholesale.
		'wholesale_page'         => 0,
		'wholesale_url'          => '',

		// Homepage (theme fallback homepage, used when no static Elementor page is set).
		'home_show_hero'         => true,
		'home_show_benefits'     => true,
		'home_show_categories'   => true,
		'home_show_sale'         => true,
		'home_show_bestsellers'  => true,
		'home_show_new'          => false,
		'home_show_campaign'     => true,
		'home_show_consult'      => true,
		'home_show_newsletter'   => true,
		'home_sale_title'        => __( 'پیشنهادهای ویژه امروز', 'samadi-electric' ),
		'home_sale_ends'         => '',
		'home_bestsellers_title' => __( 'پرفروش‌های صمدی', 'samadi-electric' ),
		'campaign_image'         => 0,
		'campaign_title'         => __( 'خانه‌ای روشن، با انتخابی زیبا', 'samadi-electric' ),
		'campaign_text'          => __( 'مجموعه چراغ‌های آویز و روشنایی مدرن؛ نور را به سبک خودتان انتخاب کنید.', 'samadi-electric' ),
		'campaign_btn_text'      => __( 'مشاهده چراغ‌های دکوراتیو', 'samadi-electric' ),
		'campaign_btn_url'       => '',
		'consult_title'          => __( 'برای پروژه‌تان، روی تجربه ما حساب کنید', 'samadi-electric' ),
		'consult_text'           => __( 'بیش از سه دهه تجربه در تأمین تجهیزات برق ساختمان؛ مشاوره تخصصی، قیمت همکاری و تأمین یکجای سفارش‌های پروژه‌ای.', 'samadi-electric' ),
		'consult_badge'          => __( '۳۰+', 'samadi-electric' ),
		'consult_badge_text'     => __( 'سال تجربه', 'samadi-electric' ),
		'consult_btn_text'       => __( 'دریافت مشاوره تخصصی', 'samadi-electric' ),
		'consult_btn_url'        => '',
		'newsletter_title'       => __( 'از پیشنهادهای روشن ما باخبر شوید', 'samadi-electric' ),
		'newsletter_text'        => __( 'تخفیف‌های ویژه و محصولات جدید را زودتر از همه ببینید.', 'samadi-electric' ),
		'newsletter_shortcode'   => '',
		'home_show_brands'       => true,
		'home_show_blog'         => true,
		'home_categories'        => '',
		'home_categories_limit'  => 6,
		'home_products_count'    => 4,
		'home_posts_count'       => 3,
		'hero_image'             => 0,
		'hero_badge'             => __( 'تجهیزات مطمئن، انتخاب هوشمند', 'samadi-electric' ),
		'hero_title'             => __( 'تجهیزات الکتریکی؛ با کیفیت، برای زندگی روشن', 'samadi-electric' ),
		'hero_text'              => __( 'از کلید و پریز تا نورپردازی خانه و محل کار؛ هر آنچه برای یک انتخاب مطمئن نیاز دارید، در صمدی الکتریک.', 'samadi-electric' ),
		'hero_note'              => __( 'بیش از ۳۰ سال همراه برق‌کاران و سازندگان', 'samadi-electric' ),
		'hero_btn1_text'         => __( 'خرید کلید و پریز', 'samadi-electric' ),
		'hero_btn1_url'          => '',
		'hero_btn2_text'         => __( 'مشاوره خرید', 'samadi-electric' ),
		'hero_btn2_url'          => '',
		'hero_align'             => 'end',

		// Benefits bar.
		'benefits_show'          => true,
		'benefit_1_icon'         => 'truck',
		'benefit_1_title'        => __( 'ارسال سریع به سراسر ایران', 'samadi-electric' ),
		'benefit_1_text'         => '',
		'benefit_2_icon'         => 'shield-check',
		'benefit_2_title'        => __( 'تضمین اصالت کالا', 'samadi-electric' ),
		'benefit_2_text'         => '',
		'benefit_3_icon'         => 'package-check',
		'benefit_3_title'        => __( '۷ روز ضمانت بازگشت', 'samadi-electric' ),
		'benefit_3_text'         => '',
		'benefit_4_icon'         => 'headset',
		'benefit_4_title'        => __( 'مشاوره تخصصی خرید', 'samadi-electric' ),
		'benefit_4_text'         => '',
		'benefit_5_icon'         => 'message-circle',
		'benefit_5_title'        => '',
		'benefit_5_text'         => '',

		// Shop & product card.
		'card_style'             => 'default',
		'card_show_sku'          => false,
		'card_show_brand'        => true,
		'card_meta_prefix'       => __( 'گارانتی اصالت کالا', 'samadi-electric' ),
		'card_show_stock'        => true,
		'card_show_rating'       => false,
		'shop_columns'           => 4,
		'shop_per_page'          => 16,
		'shop_sidebar'           => true,
		'product_show_sku'       => true,
		'product_show_brand'     => true,
		'product_benefits'       => true,

		// Breadcrumb.
		'breadcrumb_enable'      => true,
		'breadcrumb_source'      => 'auto',

		// Mobile.
		'mobile_bottom_nav'      => true,
		'mobile_nav_wishlist'    => true,
		'mobile_sticky_cart'     => true,

		// Footer.
		'footer_about'           => __( 'همراه شما در انتخاب تجهیزات برق و روشنایی؛ از یک کلید ساده تا تأمین یک پروژه کامل.', 'samadi-electric' ),
		'footer_trust_title'     => __( 'خرید امن و مطمئن', 'samadi-electric' ),
		'footer_trust_text'      => __( 'پرداخت امن با درگاه بانکی', 'samadi-electric' ),
		'footer_copyright'       => '',
		'footer_back_to_top'     => true,
	);

	/**
	 * Filter theme option defaults.
	 *
	 * @param array $defaults Defaults.
	 */
	$defaults = apply_filters( 'samadi_option_defaults', $defaults );

	return $defaults;
}

/**
 * Read a theme option with its default.
 *
 * @param string $key Option key.
 * @return mixed
 */
function samadi_option( $key ) {
	$defaults = samadi_defaults();
	$default  = array_key_exists( $key, $defaults ) ? $defaults[ $key ] : null;

	return get_theme_mod( 'samadi_' . $key, $default );
}

/**
 * Inline SVG icon markup.
 *
 * @param string $name  Icon name.
 * @param array  $args  { size, class, label }.
 * @return string
 */
function samadi_get_icon( $name, $args = array() ) {
	static $icons = null;

	if ( null === $icons ) {
		$icons = require SAMADI_DIR . '/inc/icons-data.php';
	}

	if ( empty( $icons[ $name ] ) ) {
		return '';
	}

	$args = wp_parse_args(
		$args,
		array(
			'size'  => 24,
			'class' => '',
			'label' => '',
		)
	);

	$a11y = $args['label']
		? sprintf( ' role="img" aria-label="%s"', esc_attr( $args['label'] ) )
		: ' aria-hidden="true" focusable="false"';

	return sprintf(
		'<svg class="se-icon se-icon--%1$s %2$s" width="%3$d" height="%3$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"%4$s>%5$s</svg>',
		esc_attr( $name ),
		esc_attr( $args['class'] ),
		absint( $args['size'] ),
		$a11y,
		$icons[ $name ] // Static, trusted markup shipped with the theme.
	);
}

/**
 * Echo an icon.
 *
 * @param string $name Icon name.
 * @param array  $args Args.
 */
function samadi_icon( $name, $args = array() ) {
	echo samadi_get_icon( $name, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from static, trusted SVG data with escaped attributes.
}

/**
 * Icon names available for options.
 *
 * @return array<string, string>
 */
function samadi_icon_choices() {
	$icons = require SAMADI_DIR . '/inc/icons-data.php';
	$keys  = array_keys( $icons );

	return array_combine( $keys, $keys );
}

/**
 * Convert Latin digits to Persian digits in text nodes only (never inside tags/attributes).
 *
 * @param string $html HTML or text.
 * @return string
 */
function samadi_fa_digits( $html ) {
	if ( ! is_string( $html ) || '' === $html || ! preg_match( '/\d/', $html ) ) {
		return $html;
	}

	$map   = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
	$fa    = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' );
	$parts = preg_split( '/(<[^>]*>)/u', $html, -1, PREG_SPLIT_DELIM_CAPTURE );

	if ( false === $parts ) {
		return $html;
	}

	foreach ( $parts as $i => $part ) {
		if ( '' === $part || '<' === $part[0] ) {
			continue;
		}
		// Leave HTML entities such as &#36; untouched.
		$parts[ $i ] = preg_replace_callback(
			'/&#?[a-z0-9]+;|\d/i',
			static function ( $m ) use ( $map, $fa ) {
				return 1 === strlen( $m[0] ) ? str_replace( $map, $fa, $m[0] ) : $m[0];
			},
			$part
		);
	}

	return implode( '', $parts );
}

/**
 * Format a number for display, honoring the Persian digits option.
 *
 * @param int|float|string $number Number.
 * @return string
 */
function samadi_number( $number ) {
	$out = is_numeric( $number ) ? number_format_i18n( (float) $number ) : (string) $number;

	return samadi_option( 'persian_digits' ) ? samadi_fa_digits( $out ) : $out;
}

/**
 * Current year in the site's calendar: Solar Hijri for Persian sites (when the intl extension is available).
 *
 * @return string
 */
function samadi_local_year() {
	if ( 0 === strpos( get_locale(), 'fa' ) && class_exists( 'IntlDateFormatter' ) ) {
		$formatter = new IntlDateFormatter( 'en_US@calendar=persian', IntlDateFormatter::NONE, IntlDateFormatter::NONE, wp_timezone_string(), IntlDateFormatter::TRADITIONAL, 'y' );
		$year      = $formatter->format( time() );
		if ( $year ) {
			return (string) $year;
		}
	}

	return wp_date( 'Y' );
}

/**
 * Whether WooCommerce is active.
 *
 * @return bool
 */
function samadi_is_wc() {
	return class_exists( 'WooCommerce' );
}

/**
 * Whether Elementor is active.
 *
 * @return bool
 */
function samadi_is_elementor() {
	return did_action( 'elementor/loaded' ) > 0;
}

/**
 * Whether Elementor Pro is active.
 *
 * @return bool
 */
function samadi_is_elementor_pro() {
	return defined( 'ELEMENTOR_PRO_VERSION' );
}

/**
 * Detected wishlist integration.
 *
 * Supports YITH WooCommerce Wishlist and TI WooCommerce Wishlist. The theme never
 * fakes a wishlist: if neither is active, wishlist UI is not rendered.
 *
 * @return string '' | 'yith' | 'ti'
 */
function samadi_wishlist_provider() {
	if ( defined( 'YITH_WCWL' ) && function_exists( 'yith_wcwl_get_wishlist_url' ) ) {
		return 'yith';
	}
	if ( defined( 'TINVWL_FVERSION' ) || defined( 'TINVWL_VERSION' ) || function_exists( 'tinv_url_wishlist_default' ) ) {
		return 'ti';
	}

	/**
	 * Allow other wishlist plugins to register themselves.
	 *
	 * @param string $provider Provider slug.
	 */
	return (string) apply_filters( 'samadi_wishlist_provider', '' );
}

/**
 * Wishlist page URL, or empty string.
 *
 * @return string
 */
function samadi_wishlist_url() {
	switch ( samadi_wishlist_provider() ) {
		case 'yith':
			return (string) yith_wcwl_get_wishlist_url();
		case 'ti':
			return function_exists( 'tinv_url_wishlist_default' ) ? (string) tinv_url_wishlist_default() : '';
	}

	return (string) apply_filters( 'samadi_wishlist_url', '' );
}

/**
 * Wishlist button markup for a product, rendered by the active wishlist plugin.
 *
 * @param int $product_id Product ID.
 * @return string
 */
function samadi_wishlist_button( $product_id ) {
	$html = '';

	switch ( samadi_wishlist_provider() ) {
		case 'yith':
			$html = do_shortcode( sprintf( '[yith_wcwl_add_to_wishlist product_id="%d" show_count="no" label="" browse_wishlist_text="" already_in_wishslist_text="" product_added_text=""]', absint( $product_id ) ) );
			break;
		case 'ti':
			$html = do_shortcode( sprintf( '[ti_wishlists_addtocart product_id="%d" loop="yes"]', absint( $product_id ) ) );
			break;
	}

	return (string) apply_filters( 'samadi_wishlist_button', $html, $product_id );
}

/**
 * Wholesale / bulk purchase URL (page or custom URL from settings).
 *
 * @return string Empty when not configured — callers must hide the link then.
 */
function samadi_wholesale_url() {
	$page = absint( samadi_option( 'wholesale_page' ) );

	if ( $page && 'publish' === get_post_status( $page ) ) {
		return get_permalink( $page );
	}

	return esc_url_raw( (string) samadi_option( 'wholesale_url' ) );
}

/**
 * Link for "consultation" style buttons: the given URL, else the wholesale/quote page, else a tel: link.
 *
 * @param string $url Explicit URL (may be empty).
 * @return string Empty when nothing is configured — callers must hide the button then.
 */
function samadi_contact_url( $url = '' ) {
	if ( $url ) {
		return esc_url_raw( $url );
	}

	$wholesale = samadi_wholesale_url();
	if ( $wholesale ) {
		return $wholesale;
	}

	$phone = samadi_tel( (string) samadi_option( 'contact_phone' ) );

	return $phone ? 'tel:' . $phone : '';
}

/**
 * Shop page URL with safe fallback.
 *
 * @return string
 */
function samadi_shop_url() {
	if ( samadi_is_wc() ) {
		$url = wc_get_page_permalink( 'shop' );
		if ( $url ) {
			return $url;
		}
	}

	return home_url( '/' );
}

/**
 * Normalize a phone number for tel: links.
 *
 * @param string $phone Phone.
 * @return string
 */
function samadi_tel( $phone ) {
	$phone = strtr( (string) $phone, array_combine( array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ), range( 0, 9 ) ) );

	return preg_replace( '/[^0-9+]/', '', $phone );
}

/**
 * Brand taxonomy slug if one exists (WooCommerce core Brands since 9.6, or a compatible plugin).
 *
 * @return string
 */
function samadi_brand_taxonomy() {
	$candidates = apply_filters( 'samadi_brand_taxonomies', array( 'product_brand', 'pwb-brand', 'yith_product_brand' ) );

	foreach ( $candidates as $taxonomy ) {
		if ( taxonomy_exists( $taxonomy ) ) {
			return $taxonomy;
		}
	}

	return '';
}

/**
 * First brand term of a product, or null.
 *
 * @param int $product_id Product ID.
 * @return WP_Term|null
 */
function samadi_product_brand( $product_id ) {
	$taxonomy = samadi_brand_taxonomy();

	if ( ! $taxonomy ) {
		return null;
	}

	$terms = get_the_terms( $product_id, $taxonomy );

	return ( $terms && ! is_wp_error( $terms ) ) ? reset( $terms ) : null;
}
