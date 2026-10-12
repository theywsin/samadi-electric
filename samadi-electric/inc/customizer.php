<?php
/**
 * Customizer settings (Appearance → Customize → Samadi Electric).
 *
 * All settings are stored as theme_mods with the `samadi_` prefix.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sanitize a checkbox.
 *
 * @param mixed $value Value.
 * @return bool
 */
function samadi_sanitize_checkbox( $value ) {
	return (bool) $value;
}

/**
 * Sanitize a choice against the control's choices.
 *
 * @param string               $value   Value.
 * @param WP_Customize_Setting $setting Setting.
 * @return string
 */
function samadi_sanitize_choice( $value, $setting ) {
	$control = $setting->manager->get_control( $setting->id );
	$choices = $control ? $control->choices : array();

	return array_key_exists( $value, $choices ) ? $value : $setting->default;
}

/**
 * Sanitize a comma-separated list of IDs.
 *
 * @param string|array $value Value.
 * @return string
 */
function samadi_sanitize_id_list( $value ) {
	$ids = is_array( $value ) ? $value : explode( ',', (string) $value );
	$ids = array_filter( array_map( 'absint', $ids ) );

	return implode( ',', array_unique( $ids ) );
}

/**
 * Sanitize a font family name (letters, digits, spaces, hyphens, commas, quotes).
 *
 * @param string $value Value.
 * @return string
 */
function samadi_sanitize_font_family( $value ) {
	return trim( preg_replace( '/[^\p{L}\p{N}\s,\-\'"]/u', '', (string) $value ) );
}

/**
 * Register Customizer controls.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function samadi_customize_register( $wp_customize ) {
	require_once SAMADI_DIR . '/inc/customizer-controls.php';

	$wp_customize->add_panel(
		'samadi',
		array(
			'title'    => __( 'تنظیمات صمدی الکتریک', 'samadi-electric' ),
			'priority' => 30,
		)
	);

	$defaults = samadi_defaults();

	/**
	 * Small helper to register a setting + control pair.
	 */
	$add = static function ( $key, $section, $control_args, $sanitize = 'sanitize_text_field', $transport = 'refresh' ) use ( $wp_customize, $defaults ) {
		$id = 'samadi_' . $key;

		$wp_customize->add_setting(
			$id,
			array(
				'default'           => isset( $defaults[ $key ] ) ? $defaults[ $key ] : '',
				'sanitize_callback' => $sanitize,
				'transport'         => $transport,
			)
		);

		$type = isset( $control_args['type'] ) ? $control_args['type'] : 'text';
		$args = array_merge( array( 'section' => $section ), $control_args );

		switch ( $type ) {
			case 'color':
				unset( $args['type'] );
				$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, $args ) );
				break;
			case 'media':
				unset( $args['type'] );
				$args['mime_type'] = 'image';
				$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, $id, $args ) );
				break;
			case 'term_checklist':
				unset( $args['type'] );
				$wp_customize->add_control( new Samadi_Customize_Term_Checklist_Control( $wp_customize, $id, $args ) );
				break;
			default:
				$wp_customize->add_control( $id, $args );
		}
	};

	/* ----- Colors ----- */
	$wp_customize->add_section( 'samadi_colors', array( 'title' => __( 'رنگ‌های برند', 'samadi-electric' ), 'panel' => 'samadi' ) );

	$colors = array(
		'color_primary' => __( 'سبز تیره اصلی', 'samadi-electric' ),
		'color_brand'   => __( 'سبز برند', 'samadi-electric' ),
		'color_accent'  => __( 'رنگ تأکیدی (لیمویی)', 'samadi-electric' ),
		'color_bg'      => __( 'پس‌زمینه عمومی', 'samadi-electric' ),
		'color_surface' => __( 'پس‌زمینه کارت‌ها', 'samadi-electric' ),
		'color_text'    => __( 'متن اصلی', 'samadi-electric' ),
		'color_muted'   => __( 'متن ثانویه', 'samadi-electric' ),
		'color_border'  => __( 'خطوط و مرزها', 'samadi-electric' ),
		'color_sale'    => __( 'رنگ تخفیف', 'samadi-electric' ),
	);
	foreach ( $colors as $key => $label ) {
		$add( $key, 'samadi_colors', array( 'type' => 'color', 'label' => $label ), 'sanitize_hex_color' );
	}

	/* ----- Typography ----- */
	$wp_customize->add_section( 'samadi_typography', array( 'title' => __( 'تایپوگرافی', 'samadi-electric' ), 'panel' => 'samadi' ) );

	$add(
		'font_family',
		'samadi_typography',
		array(
			'type'    => 'select',
			'label'   => __( 'فونت', 'samadi-electric' ),
			'choices' => array(
				'vazirmatn' => __( 'وزیرمتن (محلی، همراه قالب)', 'samadi-electric' ),
				'system'    => __( 'فونت سیستم', 'samadi-electric' ),
				'custom'    => __( 'فونت دلخواه (بارگذاری‌شده توسط افزونه یا Elementor)', 'samadi-electric' ),
			),
		),
		'samadi_sanitize_choice'
	);
	$add(
		'font_custom_family',
		'samadi_typography',
		array(
			'label'       => __( 'نام فونت دلخواه (font-family)', 'samadi-electric' ),
			'description' => __( 'مثلاً IRANYekanX. فایل فونت باید با مجوز معتبر توسط شما بارگذاری شده باشد (مثلاً از Elementor → فونت‌های سفارشی).', 'samadi-electric' ),
		),
		'samadi_sanitize_font_family'
	);
	$add(
		'font_weight_body',
		'samadi_typography',
		array(
			'type'    => 'select',
			'label'   => __( 'وزن متن', 'samadi-electric' ),
			'choices' => array( '300' => '300', '400' => '400', '500' => '500' ),
		),
		'samadi_sanitize_choice'
	);
	$add(
		'font_weight_heading',
		'samadi_typography',
		array(
			'type'    => 'select',
			'label'   => __( 'وزن عناوین', 'samadi-electric' ),
			'choices' => array( '600' => '600', '700' => '700', '800' => '800', '900' => '900' ),
		),
		'samadi_sanitize_choice'
	);
	$add(
		'font_size_base',
		'samadi_typography',
		array(
			'type'        => 'number',
			'label'       => __( 'اندازه پایه متن (px)', 'samadi-electric' ),
			'input_attrs' => array( 'min' => 12, 'max' => 20, 'step' => 1 ),
		),
		'absint'
	);
	$add(
		'persian_digits',
		'samadi_typography',
		array(
			'type'  => 'checkbox',
			'label' => __( 'نمایش اعداد قیمت‌ها و شمارنده‌ها با ارقام فارسی', 'samadi-electric' ),
		),
		'samadi_sanitize_checkbox'
	);

	$add(
		'force_rtl',
		'samadi_typography',
		array(
			'type'        => 'checkbox',
			'label'       => __( 'سایت همیشه راست‌چین نمایش داده شود', 'samadi-electric' ),
			'description' => __( 'پیشنهاد اصلی: زبان سایت را در «تنظیمات ← عمومی» فارسی قرار دهید. این گزینه برای زمانی است که زبان وردپرس فارسی نیست.', 'samadi-electric' ),
		),
		'samadi_sanitize_checkbox'
	);

	/* ----- Header ----- */
	$wp_customize->add_section(
		'samadi_header',
		array(
			'title'       => __( 'هدر', 'samadi-electric' ),
			'panel'       => 'samadi',
			'description' => __( 'هدر داخلی قالب. اگر در Elementor Pro قالب هدر با شرط نمایش ساخته شود، یا در بخش «Elementor» یک قالب هدر انتخاب شود، جایگزین این هدر می‌شود.', 'samadi-electric' ),
		)
	);
	$add( 'mobile_logo', 'samadi_header', array( 'type' => 'media', 'label' => __( 'لوگوی موبایل', 'samadi-electric' ) ), 'absint' );
	$add( 'header_sticky', 'samadi_header', array( 'type' => 'checkbox', 'label' => __( 'هدر چسبان', 'samadi-electric' ) ), 'samadi_sanitize_checkbox' );
	$add( 'header_show_categories', 'samadi_header', array( 'type' => 'checkbox', 'label' => __( 'نمایش مگامنوی دسته‌بندی محصولات', 'samadi-electric' ) ), 'samadi_sanitize_checkbox' );
	$add( 'header_categories_label', 'samadi_header', array( 'label' => __( 'عنوان دکمه دسته‌بندی‌ها', 'samadi-electric' ) ) );
	$add( 'header_show_search', 'samadi_header', array( 'type' => 'checkbox', 'label' => __( 'نمایش جستجو', 'samadi-electric' ) ), 'samadi_sanitize_checkbox' );
	$add( 'header_show_account', 'samadi_header', array( 'type' => 'checkbox', 'label' => __( 'نمایش حساب کاربری', 'samadi-electric' ) ), 'samadi_sanitize_checkbox' );
	$add( 'header_wholesale_label', 'samadi_header', array( 'label' => __( 'عنوان دکمه خرید عمده (خالی = مخفی)', 'samadi-electric' ) ) );

	/* ----- Contact & social ----- */
	$wp_customize->add_section(
		'samadi_contact',
		array(
			'title'       => __( 'اطلاعات تماس و شبکه‌های اجتماعی', 'samadi-electric' ),
			'panel'       => 'samadi',
			'description' => __( 'فقط اطلاعات واقعی فروشگاه را وارد کنید. موارد خالی در سایت نمایش داده نمی‌شوند.', 'samadi-electric' ),
		)
	);
	$add( 'contact_phone', 'samadi_contact', array( 'label' => __( 'تلفن ثابت', 'samadi-electric' ) ) );
	$add( 'contact_mobile', 'samadi_contact', array( 'label' => __( 'تلفن همراه', 'samadi-electric' ) ) );
	$add( 'contact_email', 'samadi_contact', array( 'type' => 'email', 'label' => __( 'ایمیل', 'samadi-electric' ) ), 'sanitize_email' );
	$add( 'contact_address', 'samadi_contact', array( 'type' => 'textarea', 'label' => __( 'آدرس', 'samadi-electric' ) ), 'sanitize_textarea_field' );
	$add( 'contact_hours', 'samadi_contact', array( 'label' => __( 'ساعات کاری', 'samadi-electric' ) ) );
	$add( 'social_instagram', 'samadi_contact', array( 'type' => 'url', 'label' => __( 'اینستاگرام (لینک کامل)', 'samadi-electric' ) ), 'esc_url_raw' );
	$add( 'social_telegram', 'samadi_contact', array( 'type' => 'url', 'label' => __( 'تلگرام (لینک کامل)', 'samadi-electric' ) ), 'esc_url_raw' );
	$add( 'social_whatsapp', 'samadi_contact', array( 'type' => 'url', 'label' => __( 'واتس‌اپ (لینک کامل wa.me)', 'samadi-electric' ) ), 'esc_url_raw' );
	$add( 'social_linkedin', 'samadi_contact', array( 'type' => 'url', 'label' => __( 'لینکدین (لینک کامل)', 'samadi-electric' ) ), 'esc_url_raw' );

	/* ----- Wholesale ----- */
	$wp_customize->add_section(
		'samadi_wholesale',
		array(
			'title'       => __( 'خرید عمده', 'samadi-electric' ),
			'panel'       => 'samadi',
			'description' => __( 'صفحه‌ای که فرم خرید عمده/استعلام قیمت در آن قرار دارد (شورت‌کد [samadi_quote_form] از افزونه همراه). اگر خالی باشد، دکمه‌های خرید عمده نمایش داده نمی‌شوند.', 'samadi-electric' ),
		)
	);
	$add( 'wholesale_page', 'samadi_wholesale', array( 'type' => 'dropdown-pages', 'label' => __( 'صفحه خرید عمده', 'samadi-electric' ) ), 'absint' );
	$add( 'wholesale_url', 'samadi_wholesale', array( 'type' => 'url', 'label' => __( 'یا لینک دلخواه', 'samadi-electric' ) ), 'esc_url_raw' );

	/* ----- Homepage ----- */
	$wp_customize->add_section(
		'samadi_home',
		array(
			'title'       => __( 'صفحه اصلی (پیش‌فرض قالب)', 'samadi-electric' ),
			'panel'       => 'samadi',
			'description' => __( 'این تنظیمات برای صفحه اصلی داخلی قالب است. اگر در «تنظیمات → خواندن» یک برگه ساخته‌شده با Elementor را به‌عنوان صفحه اصلی انتخاب کنید، همان برگه نمایش داده می‌شود و از Elementor ویرایش می‌شود.', 'samadi-electric' ),
		)
	);
	$sections = array(
		'home_show_hero'        => __( 'نمایش بنر اصلی', 'samadi-electric' ),
		'home_show_benefits'    => __( 'نمایش مزایای خرید', 'samadi-electric' ),
		'home_show_categories'  => __( 'نمایش دسته‌بندی‌های محبوب', 'samadi-electric' ),
		'home_show_sale'        => __( 'نمایش پیشنهادهای ویژه (تخفیف‌دار)', 'samadi-electric' ),
		'home_show_bestsellers' => __( 'نمایش پرفروش‌ترین‌ها', 'samadi-electric' ),
		'home_show_new'         => __( 'نمایش جدیدترین محصولات', 'samadi-electric' ),
		'home_show_brands'      => __( 'نمایش برندها', 'samadi-electric' ),
		'home_show_blog'        => __( 'نمایش مقالات آموزشی', 'samadi-electric' ),
	);
	foreach ( $sections as $key => $label ) {
		$add( $key, 'samadi_home', array( 'type' => 'checkbox', 'label' => $label ), 'samadi_sanitize_checkbox' );
	}
	if ( taxonomy_exists( 'product_cat' ) ) {
		$add(
			'home_categories',
			'samadi_home',
			array(
				'type'        => 'term_checklist',
				'taxonomy'    => 'product_cat',
				'label'       => __( 'دسته‌بندی‌های منتخب', 'samadi-electric' ),
				'description' => __( 'ترتیب نمایش مطابق ترتیب دسته‌ها در «محصولات → دسته‌بندی‌ها» (قابل جابه‌جایی با کشیدن) است. اگر چیزی انتخاب نشود، دسته‌های اصلی نمایش داده می‌شوند.', 'samadi-electric' ),
			),
			'samadi_sanitize_id_list'
		);
	}
	$add( 'home_categories_limit', 'samadi_home', array( 'type' => 'number', 'label' => __( 'حداکثر تعداد دسته‌ها', 'samadi-electric' ), 'input_attrs' => array( 'min' => 1, 'max' => 24 ) ), 'absint' );
	$add( 'home_products_count', 'samadi_home', array( 'type' => 'number', 'label' => __( 'تعداد محصولات هر بخش', 'samadi-electric' ), 'input_attrs' => array( 'min' => 1, 'max' => 24 ) ), 'absint' );
	$add( 'home_posts_count', 'samadi_home', array( 'type' => 'number', 'label' => __( 'تعداد مقالات', 'samadi-electric' ), 'input_attrs' => array( 'min' => 1, 'max' => 12 ) ), 'absint' );
	$add( 'hero_image', 'samadi_home', array( 'type' => 'media', 'label' => __( 'تصویر بنر اصلی', 'samadi-electric' ) ), 'absint' );
	$add( 'hero_title', 'samadi_home', array( 'label' => __( 'عنوان بنر', 'samadi-electric' ) ) );
	$add( 'hero_text', 'samadi_home', array( 'type' => 'textarea', 'label' => __( 'متن بنر', 'samadi-electric' ) ), 'sanitize_textarea_field' );
	$add( 'hero_btn1_text', 'samadi_home', array( 'label' => __( 'متن دکمه اول', 'samadi-electric' ) ) );
	$add( 'hero_btn1_url', 'samadi_home', array( 'type' => 'url', 'label' => __( 'لینک دکمه اول (خالی = فروشگاه)', 'samadi-electric' ) ), 'esc_url_raw' );
	$add( 'hero_btn2_text', 'samadi_home', array( 'label' => __( 'متن دکمه دوم (به صفحه خرید عمده لینک می‌شود)', 'samadi-electric' ) ) );
	$add(
		'hero_align',
		'samadi_home',
		array(
			'type'    => 'select',
			'label'   => __( 'موقعیت متن بنر', 'samadi-electric' ),
			'choices' => array(
				'start'  => __( 'راست', 'samadi-electric' ),
				'center' => __( 'وسط', 'samadi-electric' ),
				'end'    => __( 'چپ', 'samadi-electric' ),
			),
		),
		'samadi_sanitize_choice'
	);

	/* ----- Benefits ----- */
	$wp_customize->add_section( 'samadi_benefits', array( 'title' => __( 'مزایای فروشگاه', 'samadi-electric' ), 'panel' => 'samadi' ) );
	$add( 'benefits_show', 'samadi_benefits', array( 'type' => 'checkbox', 'label' => __( 'نمایش نوار مزایا', 'samadi-electric' ) ), 'samadi_sanitize_checkbox' );
	$icon_choices = samadi_icon_choices();
	for ( $i = 1; $i <= 5; $i++ ) {
		/* translators: %d: benefit number. */
		$add( "benefit_{$i}_icon", 'samadi_benefits', array( 'type' => 'select', 'label' => sprintf( __( 'آیکون مورد %d', 'samadi-electric' ), $i ), 'choices' => $icon_choices ), 'samadi_sanitize_choice' );
		/* translators: %d: benefit number. */
		$add( "benefit_{$i}_title", 'samadi_benefits', array( 'label' => sprintf( __( 'عنوان مورد %d (خالی = مخفی)', 'samadi-electric' ), $i ) ) );
		/* translators: %d: benefit number. */
		$add( "benefit_{$i}_text", 'samadi_benefits', array( 'label' => sprintf( __( 'توضیح مورد %d', 'samadi-electric' ), $i ) ) );
	}

	/* ----- Shop ----- */
	$wp_customize->add_section( 'samadi_shop', array( 'title' => __( 'فروشگاه و کارت محصول', 'samadi-electric' ), 'panel' => 'samadi' ) );
	$add(
		'card_style',
		'samadi_shop',
		array(
			'type'    => 'select',
			'label'   => __( 'حالت نمایش کارت محصول', 'samadi-electric' ),
			'choices' => array(
				'default' => __( 'استاندارد', 'samadi-electric' ),
				'compact' => __( 'فشرده', 'samadi-electric' ),
				'bordered' => __( 'با حاشیه', 'samadi-electric' ),
			),
		),
		'samadi_sanitize_choice'
	);
	$add( 'card_show_sku', 'samadi_shop', array( 'type' => 'checkbox', 'label' => __( 'نمایش کد کالا در کارت', 'samadi-electric' ) ), 'samadi_sanitize_checkbox' );
	$add( 'card_show_brand', 'samadi_shop', array( 'type' => 'checkbox', 'label' => __( 'نمایش برند در کارت', 'samadi-electric' ) ), 'samadi_sanitize_checkbox' );
	$add( 'card_show_stock', 'samadi_shop', array( 'type' => 'checkbox', 'label' => __( 'نمایش وضعیت موجودی در کارت', 'samadi-electric' ) ), 'samadi_sanitize_checkbox' );
	$add( 'card_show_rating', 'samadi_shop', array( 'type' => 'checkbox', 'label' => __( 'نمایش امتیاز در کارت', 'samadi-electric' ) ), 'samadi_sanitize_checkbox' );
	$add( 'shop_columns', 'samadi_shop', array( 'type' => 'number', 'label' => __( 'تعداد ستون محصولات (دسکتاپ)', 'samadi-electric' ), 'input_attrs' => array( 'min' => 2, 'max' => 5 ) ), 'absint' );
	$add( 'shop_per_page', 'samadi_shop', array( 'type' => 'number', 'label' => __( 'تعداد محصول در هر صفحه', 'samadi-electric' ), 'input_attrs' => array( 'min' => 4, 'max' => 60 ) ), 'absint' );
	$add( 'shop_sidebar', 'samadi_shop', array( 'type' => 'checkbox', 'label' => __( 'نمایش ستون فیلترها در آرشیو', 'samadi-electric' ) ), 'samadi_sanitize_checkbox' );
	$add( 'product_show_sku', 'samadi_shop', array( 'type' => 'checkbox', 'label' => __( 'نمایش کد کالا در صفحه محصول', 'samadi-electric' ) ), 'samadi_sanitize_checkbox' );
	$add( 'product_show_brand', 'samadi_shop', array( 'type' => 'checkbox', 'label' => __( 'نمایش برند در صفحه محصول', 'samadi-electric' ) ), 'samadi_sanitize_checkbox' );
	$add( 'product_benefits', 'samadi_shop', array( 'type' => 'checkbox', 'label' => __( 'نمایش مزایای فروشگاه در صفحه محصول', 'samadi-electric' ) ), 'samadi_sanitize_checkbox' );

	/* ----- Breadcrumb ----- */
	$wp_customize->add_section( 'samadi_breadcrumb', array( 'title' => __( 'مسیر راهنما (Breadcrumb)', 'samadi-electric' ), 'panel' => 'samadi' ) );
	$add( 'breadcrumb_enable', 'samadi_breadcrumb', array( 'type' => 'checkbox', 'label' => __( 'نمایش مسیر راهنما', 'samadi-electric' ) ), 'samadi_sanitize_checkbox' );
	$add(
		'breadcrumb_source',
		'samadi_breadcrumb',
		array(
			'type'        => 'select',
			'label'       => __( 'منبع', 'samadi-electric' ),
			'description' => __( 'حالت خودکار در صورت فعال بودن Yoast SEO یا Rank Math از Breadcrumb همان افزونه (همراه با Schema آن) استفاده می‌کند.', 'samadi-electric' ),
			'choices'     => array(
				'auto'        => __( 'خودکار', 'samadi-electric' ),
				'woocommerce' => __( 'ووکامرس / قالب', 'samadi-electric' ),
			),
		),
		'samadi_sanitize_choice'
	);

	/* ----- Mobile ----- */
	$wp_customize->add_section( 'samadi_mobile', array( 'title' => __( 'موبایل', 'samadi-electric' ), 'panel' => 'samadi' ) );
	$add( 'mobile_bottom_nav', 'samadi_mobile', array( 'type' => 'checkbox', 'label' => __( 'نوار ناوبری پایین موبایل', 'samadi-electric' ) ), 'samadi_sanitize_checkbox' );
	$add( 'mobile_nav_wishlist', 'samadi_mobile', array( 'type' => 'checkbox', 'label' => __( 'نمایش علاقه‌مندی‌ها در نوار پایین (در صورت نصب افزونه علاقه‌مندی)', 'samadi-electric' ) ), 'samadi_sanitize_checkbox' );
	$add( 'mobile_sticky_cart', 'samadi_mobile', array( 'type' => 'checkbox', 'label' => __( 'نوار خرید چسبان در صفحه محصول', 'samadi-electric' ) ), 'samadi_sanitize_checkbox' );

	/* ----- Footer ----- */
	$wp_customize->add_section( 'samadi_footer', array( 'title' => __( 'فوتر', 'samadi-electric' ), 'panel' => 'samadi' ) );
	$add( 'footer_about', 'samadi_footer', array( 'type' => 'textarea', 'label' => __( 'درباره فروشگاه', 'samadi-electric' ) ), 'wp_kses_post' );
	$add( 'footer_copyright', 'samadi_footer', array( 'label' => __( 'متن کپی‌رایت (خالی = پیش‌فرض)', 'samadi-electric' ) ), 'wp_kses_post' );
}
add_action( 'customize_register', 'samadi_customize_register' );
