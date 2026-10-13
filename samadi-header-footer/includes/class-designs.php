<?php
/**
 * The three header and three footer designs, as Elementor element data.
 *
 * Everything is built from Elementor containers, Elementor's free widgets (heading, text,
 * button, icon list, social icons) and this plugin's widgets, so every part is editable.
 *
 * @package Samadi_Header_Footer
 */

defined( 'ABSPATH' ) || exit;

/**
 * Designs.
 */
class SHF_Designs {

	const GREEN  = '#064E3B';
	const BRAND  = '#075E46';
	const LIME   = '#C7F52A';
	const TEXT   = '#152421';
	const MUTED  = '#718078';
	const BORDER = '#E8ECE8';
	const BG     = '#F7F8F6';

	/**
	 * Catalogue.
	 *
	 * @return array<string, array<string, array{name:string,desc:string}>>
	 */
	public static function catalogue() {
		return array(
			'header' => array(
				'header-1' => array(
					'name' => 'هدر فروشگاهی کلاسیک',
					'desc' => 'دو ردیف: لوگو، جستجوی بزرگ، دکمه خرید عمده، حساب و سبد؛ ردیف دوم دسته‌بندی‌ها، منو و تلفن.',
				),
				'header-2' => array(
					'name' => 'هدر مینیمال یک‌ردیفه',
					'desc' => 'یک ردیف سفید و سبک: لوگو، منوی وسط‌چین، جستجوی آیکونی و دکمه «ورود / ثبت‌نام» با سبد.',
				),
				'header-3' => array(
					'name' => 'هدر تیره با نوار اطلاع‌رسانی',
					'desc' => 'نوار پیام بالای صفحه، بخش اصلی سبز تیره با جستجو و آیکون‌های روشن، دکمه دسته‌بندی سبز لیمویی.',
				),
			),
			'footer' => array(
				'footer-1' => array(
					'name' => 'فوتر تیره کامل',
					'desc' => 'نوار مشاوره و تماس، چهار ستون (درباره و شبکه‌های اجتماعی، دسته‌ها، راهنما، تماس) و نوار کپی‌رایت.',
				),
				'footer-2' => array(
					'name' => 'فوتر روشن ستونی',
					'desc' => 'پس‌زمینه روشن، لوگو و اطلاعات تماس، سه ستون لینک، ساعات کاری و نوار پایین با شبکه‌های اجتماعی.',
				),
				'footer-3' => array(
					'name' => 'فوتر مینیمال وسط‌چین',
					'desc' => 'لوگو، لینک‌های افقی، شبکه‌های اجتماعی و کپی‌رایت در یک چیدمان ساده و وسط‌چین.',
				),
			),
		);
	}

	/**
	 * Elements of a design.
	 *
	 * @param string $key Design key.
	 * @return array
	 */
	public static function elements( $key ) {
		$method = str_replace( '-', '_', $key );
		return method_exists( __CLASS__, $method ) ? self::$method() : array();
	}

	/* ===================================================================== builders */

	/**
	 * Element ID.
	 *
	 * @return string
	 */
	private static function eid() {
		return substr( md5( uniqid( '', true ) . wp_rand() ), 0, 7 );
	}

	/**
	 * Padding/margin/radius value.
	 *
	 * @param int|string $t Top.
	 * @param int|string $r Right.
	 * @param int|string $b Bottom.
	 * @param int|string $l Left.
	 * @return array
	 */
	private static function box4( $t, $r = null, $b = null, $l = null ) {
		$r = null === $r ? $t : $r;
		$b = null === $b ? $t : $b;
		$l = null === $l ? $r : $l;
		return array( 'unit' => 'px', 'top' => (string) $t, 'right' => (string) $r, 'bottom' => (string) $b, 'left' => (string) $l, 'isLinked' => ( $t === $r && $r === $b && $b === $l ) );
	}

	/**
	 * Gap value.
	 *
	 * @param int $px Gap.
	 * @return array
	 */
	private static function gap( $px ) {
		return array( 'unit' => 'px', 'size' => $px, 'column' => (string) $px, 'row' => (string) $px );
	}

	/**
	 * Container.
	 *
	 * @param array $elements Children.
	 * @param array $settings Settings.
	 * @param bool  $inner    Inner container.
	 * @return array
	 */
	private static function con( $elements, $settings = array(), $inner = false ) {
		return array(
			'id'       => self::eid(),
			'elType'   => 'container',
			'isInner'  => $inner,
			'settings' => array_merge(
				array(
					'content_width'  => $inner ? 'full' : 'boxed',
					'flex_direction' => 'row',
					'flex_align_items' => 'center',
					'flex_gap'       => self::gap( 20 ),
					'padding'        => $inner ? self::box4( 0 ) : self::box4( 12, 16 ),
				),
				$settings
			),
			'elements' => $elements,
		);
	}

	/**
	 * Column (inner vertical container).
	 *
	 * @param array $elements Children.
	 * @param int   $width    Desktop width (%).
	 * @param array $settings Extra settings.
	 * @return array
	 */
	private static function col( $elements, $width, $settings = array() ) {
		return self::con(
			$elements,
			array_merge(
				array(
					'flex_direction'   => 'column',
					'flex_align_items' => 'stretch',
					'flex_gap'         => self::gap( 12 ),
					'width'            => array( 'unit' => '%', 'size' => $width ),
					'width_tablet'     => array( 'unit' => '%', 'size' => 47 ),
					'width_mobile'     => array( 'unit' => '%', 'size' => 100 ),
				),
				$settings
			),
			true
		);
	}

	/**
	 * Widget.
	 *
	 * @param string $type     Widget type.
	 * @param array  $settings Settings.
	 * @return array
	 */
	private static function w( $type, $settings = array() ) {
		return array(
			'id'         => self::eid(),
			'elType'     => 'widget',
			'widgetType' => $type,
			'settings'   => $settings,
			'elements'   => array(),
		);
	}

	/**
	 * Grow inside a flex row.
	 *
	 * @return array
	 */
	private static function grow() {
		return array( '_flex_size' => 'grow' );
	}

	/**
	 * Hide on devices.
	 *
	 * @param string ...$devices desktop|tablet|mobile.
	 * @return array
	 */
	private static function hide( ...$devices ) {
		$out = array();
		foreach ( $devices as $device ) {
			$out[ 'hide_' . $device ] = 'hidden-' . $device;
		}
		return $out;
	}

	/**
	 * Store option from the Samadi theme or a placeholder.
	 *
	 * @param string $key      Option key (contact_phone, …).
	 * @param string $fallback Placeholder.
	 * @return string
	 */
	private static function opt( $key, $fallback ) {
		$value = function_exists( 'samadi_option' ) ? trim( (string) samadi_option( $key ) ) : '';
		return '' !== $value ? $value : $fallback;
	}

	/**
	 * Phone link URL.
	 *
	 * @param string $phone Phone.
	 * @return string
	 */
	private static function tel( $phone ) {
		$digits = preg_replace( '/[^0-9+]/', '', strtr( $phone, array( '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9' ) ) );
		return 'tel:' . $digits;
	}

	/**
	 * Wholesale / quote URL if known.
	 *
	 * @return string
	 */
	private static function wholesale_url() {
		if ( function_exists( 'samadi_wholesale_url' ) && samadi_wholesale_url() ) {
			return samadi_wholesale_url();
		}
		$page = get_page_by_path( 'wholesale' );
		return $page ? get_permalink( $page ) : '';
	}

	/**
	 * Icon list items.
	 *
	 * @param array $rows [ text, fa icon, url ].
	 * @return array
	 */
	private static function icon_items( $rows ) {
		$items = array();
		foreach ( $rows as $row ) {
			$items[] = array(
				'_id'           => self::eid(),
				'text'          => $row[0],
				'selected_icon' => array( 'value' => $row[1], 'library' => 0 === strpos( $row[1], 'fab' ) ? 'fa-brands' : 'fa-solid' ),
				'link'          => array( 'url' => isset( $row[2] ) ? $row[2] : '', 'is_external' => '', 'nofollow' => '' ),
			);
		}
		return $items;
	}

	/**
	 * Contact icon list.
	 *
	 * @param string $text Text colour.
	 * @param string $icon Icon colour.
	 * @param bool   $inline Inline layout.
	 * @return array
	 */
	private static function contact_list( $text, $icon, $inline = false ) {
		$phone = self::opt( 'contact_phone', '۰۲۱-۱۲۳۴۵۶۷۸' );
		return self::w(
			'icon-list',
			array(
				'view'                     => $inline ? 'inline' : 'traditional',
				'icon_list'                => self::icon_items(
					array(
						array( $phone, 'fas fa-phone-alt', self::tel( $phone ) ),
						array( self::opt( 'contact_email', 'info@example.com' ), 'fas fa-envelope', 'mailto:' . self::opt( 'contact_email', 'info@example.com' ) ),
						array( self::opt( 'contact_address', 'تهران، خیابان لاله‌زار' ), 'fas fa-map-marker-alt' ),
						array( self::opt( 'contact_hours', 'شنبه تا پنجشنبه ۹ تا ۱۸' ), 'fas fa-clock' ),
					)
				),
				'icon_color'               => $icon,
				'text_color'               => $text,
				'icon_size'                => array( 'unit' => 'px', 'size' => 14 ),
				'space_between'            => array( 'unit' => 'px', 'size' => 10 ),
				'icon_typography_typography' => 'custom',
				'text_indent'              => array( 'unit' => 'px', 'size' => 10 ),
			)
		);
	}

	/**
	 * Social icons.
	 *
	 * @param string $color  Icon colour.
	 * @param string $bg     Background.
	 * @param string $align  Alignment.
	 * @return array
	 */
	private static function social( $color, $bg, $align = 'right' ) {
		$links = array(
			'instagram' => array( 'fab fa-instagram', self::opt( 'social_instagram', '#' ) ),
			'telegram'  => array( 'fab fa-telegram-plane', self::opt( 'social_telegram', '#' ) ),
			'whatsapp'  => array( 'fab fa-whatsapp', self::opt( 'social_whatsapp', '#' ) ),
			'linkedin'  => array( 'fab fa-linkedin-in', self::opt( 'social_linkedin', '#' ) ),
		);
		$list = array();
		foreach ( $links as $link ) {
			$list[] = array(
				'_id'         => self::eid(),
				'social_icon' => array( 'value' => $link[0], 'library' => 'fa-brands' ),
				'link'        => array( 'url' => $link[1], 'is_external' => 'on', 'nofollow' => '' ),
			);
		}
		return self::w(
			'social-icons',
			array(
				'social_icon_list'       => $list,
				'shape'                  => 'rounded',
				'icon_color'             => 'custom',
				'icon_primary_color'     => $bg,
				'icon_secondary_color'   => $color,
				'icon_size'              => array( 'unit' => 'px', 'size' => 16 ),
				'icon_padding'           => array( 'unit' => 'em', 'size' => 0.7 ),
				'icon_spacing'           => array( 'unit' => 'px', 'size' => 8 ),
				'border_radius'          => self::box4( 12 ),
				'align'                  => $align,
			)
		);
	}

	/**
	 * Heading.
	 *
	 * @param string $text  Text.
	 * @param string $color Colour.
	 * @param int    $size  Font size.
	 * @param string $tag   Tag.
	 * @param array  $extra Extra.
	 * @return array
	 */
	private static function heading( $text, $color, $size = 16, $tag = 'p', $extra = array() ) {
		return self::w(
			'heading',
			array_merge(
				array(
					'title'                  => $text,
					'header_size'            => $tag,
					'title_color'            => $color,
					'typography_typography'  => 'custom',
					'typography_font_size'   => array( 'unit' => 'px', 'size' => $size ),
					'typography_font_weight' => '800',
				),
				$extra
			)
		);
	}

	/**
	 * Text editor.
	 *
	 * @param string $html  HTML.
	 * @param string $color Colour.
	 * @param int    $size  Font size.
	 * @param array  $extra Extra.
	 * @return array
	 */
	private static function text( $html, $color, $size = 14, $extra = array() ) {
		return self::w(
			'text-editor',
			array_merge(
				array(
					'editor'                 => $html,
					'text_color'             => $color,
					'typography_typography'  => 'custom',
					'typography_font_size'   => array( 'unit' => 'px', 'size' => $size ),
					'typography_line_height' => array( 'unit' => 'em', 'size' => 2 ),
				),
				$extra
			)
		);
	}

	/**
	 * Button.
	 *
	 * @param string $text  Text.
	 * @param string $url   URL.
	 * @param string $bg    Background.
	 * @param string $color Text colour.
	 * @param array  $extra Extra.
	 * @return array
	 */
	private static function button( $text, $url, $bg, $color, $extra = array() ) {
		return self::w(
			'button',
			array_merge(
				array(
					'text'                   => $text,
					'link'                   => array( 'url' => $url, 'is_external' => '', 'nofollow' => '' ),
					'background_color'       => $bg,
					'button_text_color'      => $color,
					'border_radius'          => self::box4( 10 ),
					'typography_typography'  => 'custom',
					'typography_font_size'   => array( 'unit' => 'px', 'size' => 14 ),
					'typography_font_weight' => '800',
					'text_padding'           => self::box4( 12, 22 ),
				),
				$extra
			)
		);
	}

	/**
	 * Footer link list.
	 *
	 * @param string $title    Title.
	 * @param string $location Menu location.
	 * @param string $source   Fallback source.
	 * @param string $title_c  Title colour.
	 * @param string $link_c   Link colour.
	 * @param string $hover    Hover colour.
	 * @param string $bar      Title underline colour.
	 * @return array
	 */
	private static function links( $title, $location, $source, $title_c, $link_c, $hover, $bar ) {
		return self::w(
			'shf-menu-list',
			array(
				'title'       => $title,
				'menu'        => '',
				'location'    => $location,
				'source'      => $source,
				'layout'      => 'vertical',
				'bullet'      => 'yes',
				'title_color' => $title_c,
				'title_bar'   => $bar,
				'link_color'  => $link_c,
				'link_hover'  => $hover,
			)
		);
	}

	/* ===================================================================== headers */

	/**
	 * Header 1 — classic store (two rows).
	 *
	 * @return array
	 */
	private static function header_1() {
		$phone     = self::opt( 'contact_phone', '۰۲۱-۱۲۳۴۵۶۷۸' );
		$wholesale = self::wholesale_url();

		return array(
			self::con(
				array(
					self::w( 'shf-nav-menu', array_merge( array( 'location' => 'primary', 'breakpoint' => 'tablet', 'toggle_color' => self::TEXT ), self::hide( 'desktop' ) ) ),
					self::w( 'shf-logo', array( 'source' => 'site', 'show_name' => 'yes', 'show_tagline' => 'yes', 'height' => array( 'unit' => 'px', 'size' => 46 ), 'name_color' => self::GREEN ) ),
					self::w(
						'shf-search',
						array_merge(
							self::grow(),
							array(
								'mode'                         => 'bar',
								'bg'                           => '#F2F4F1',
								'radius'                       => array( 'unit' => 'px', 'size' => 14 ),
								'height'                       => array( 'unit' => 'px', 'size' => 48 ),
								'_element_width_mobile'        => 'initial',
								'_element_custom_width_mobile' => array( 'unit' => '%', 'size' => 100 ),
								'_flex_order_mobile'           => 'end',
								'_flex_order_tablet'           => 'end',
								'_element_width_tablet'        => 'initial',
								'_element_custom_width_tablet' => array( 'unit' => '%', 'size' => 100 ),
							)
						)
					),
					self::w(
						'shf-icons',
						array(
							'cart'         => 'yes',
							'account'      => 'yes',
							'wishlist'     => 'yes',
							'extra_text'   => $wholesale ? 'خرید عمده' : '',
							'extra_link'   => array( 'url' => $wholesale ),
							'extra_bg'     => self::GREEN,
							'extra_color'  => '#FFFFFF',
							'count_bg'     => self::LIME,
							'_css_classes' => 'shf-push-end',
						)
					),
				),
				array(
					'background_background' => 'classic',
					'background_color'      => '#FFFFFF',
					'flex_wrap_tablet'      => 'wrap',
					'flex_wrap_mobile'      => 'wrap',
					'flex_gap'              => self::gap( 24 ),
					'flex_gap_mobile'       => self::gap( 10 ),
					'padding'               => self::box4( 14, 16 ),
					'css_classes'           => 'shf-header shf-header--1',
				)
			),
			self::con(
				array(
					self::w( 'shf-categories', array( 'label' => 'همه دسته‌بندی‌ها', 'btn_bg' => '#EEF4EF', 'btn_color' => self::GREEN ) ),
					self::w( 'shf-nav-menu', array_merge( self::grow(), array( 'location' => 'primary', 'breakpoint' => 'none', 'item_color' => self::TEXT, 'item_hover' => self::BRAND, 'active_color' => self::GREEN, 'active_bg' => '#EEF4EF' ) ) ),
					self::w(
						'icon-list',
						array(
							'view'       => 'inline',
							'icon_list'  => self::icon_items( array( array( $phone, 'fas fa-phone-alt', self::tel( $phone ) ) ) ),
							'icon_color' => self::BRAND,
							'text_color' => self::TEXT,
							'icon_size'  => array( 'unit' => 'px', 'size' => 14 ),
						)
					),
				),
				array_merge(
					array(
						'background_background' => 'classic',
						'background_color'      => '#FFFFFF',
						'border_border'         => 'solid',
						'border_width'          => array( 'unit' => 'px', 'top' => '1', 'right' => '0', 'bottom' => '1', 'left' => '0', 'isLinked' => false ),
						'border_color'          => self::BORDER,
						'padding'               => self::box4( 6, 16 ),
					),
					self::hide( 'tablet', 'mobile' )
				)
			),
		);
	}

	/**
	 * Header 2 — minimal single row.
	 *
	 * @return array
	 */
	private static function header_2() {
		return array(
			self::con(
				array(
					self::w( 'shf-logo', array( 'source' => 'site', 'show_name' => 'yes', 'show_tagline' => '', 'height' => array( 'unit' => 'px', 'size' => 40 ), 'name_color' => self::TEXT ) ),
					self::w( 'shf-nav-menu', array_merge( self::grow(), array( 'location' => 'primary', 'breakpoint' => 'tablet', 'align' => 'center', 'gap' => array( 'unit' => 'px', 'size' => 6 ), 'item_color' => self::TEXT, 'item_hover' => self::GREEN, 'active_color' => self::GREEN, 'toggle_color' => self::TEXT, '_flex_order_tablet' => 'start' ) ) ),
					self::w( 'shf-search', array( 'mode' => 'icon', 'bg' => '#F2F4F1' ) ),
					self::w( 'shf-icons', array( 'cart' => 'yes', 'account' => 'yes', 'account_style' => 'button', 'login_label' => 'ورود / ثبت‌نام', 'wishlist' => 'yes', 'btn_color' => self::GREEN, 'count_bg' => self::GREEN ) ),
				),
				array(
					'background_background' => 'classic',
					'background_color'      => '#FFFFFF',
					'border_border'         => 'solid',
					'border_width'          => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '1', 'left' => '0', 'isLinked' => false ),
					'border_color'          => self::BORDER,
					'padding'               => self::box4( 14, 16 ),
					'flex_gap'              => self::gap( 16 ),
					'flex_gap_mobile'       => self::gap( 4 ),
					'flex_wrap_tablet'      => 'nowrap',
					'flex_wrap_mobile'      => 'nowrap',
					'css_classes'           => 'shf-header shf-header--2',
				)
			),
		);
	}

	/**
	 * Header 3 — dark with announcement bar.
	 *
	 * @return array
	 */
	private static function header_3() {
		$phone = self::opt( 'contact_phone', '۰۲۱-۱۲۳۴۵۶۷۸' );
		return array(
			self::con(
				array(
					self::text( '<p>ارسال سریع به سراسر کشور | ضمانت اصالت کالا</p>', '#E6F2EC', 13, self::grow() ),
					self::w(
						'icon-list',
						array_merge(
							array(
								'view'       => 'inline',
								'icon_list'  => self::icon_items( array( array( $phone, 'fas fa-phone-alt', self::tel( $phone ) ), array( 'پیگیری سفارش', 'fas fa-box', function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : '' ) ) ),
								'icon_color' => self::LIME,
								'text_color' => '#FFFFFF',
								'icon_size'  => array( 'unit' => 'px', 'size' => 12 ),
								'space_between' => array( 'unit' => 'px', 'size' => 20 ),
							),
							self::hide( 'mobile' )
						)
					),
				),
				array(
					'background_background' => 'classic',
					'background_color'      => '#043428',
					'padding'               => self::box4( 4, 16 ),
					'css_classes'           => 'shf-header shf-header--3',
				)
			),
			self::con(
				array(
					self::w( 'shf-nav-menu', array_merge( array( 'location' => 'primary', 'breakpoint' => 'tablet', 'toggle_color' => '#FFFFFF' ), self::hide( 'desktop' ) ) ),
					self::w( 'shf-logo', array( 'source' => 'site', 'show_name' => 'yes', 'show_tagline' => 'yes', 'height' => array( 'unit' => 'px', 'size' => 46 ), 'name_color' => '#FFFFFF', 'tagline_color' => '#B9D3C8' ) ),
					self::w(
						'shf-search',
						array_merge(
							self::grow(),
							array(
								'mode'                         => 'bar',
								'bg'                           => 'rgba(255,255,255,0.1)',
								'border'                       => 'rgba(255,255,255,0.18)',
								'text'                         => '#FFFFFF',
								'radius'                       => array( 'unit' => 'px', 'size' => 999 ),
								'height'                       => array( 'unit' => 'px', 'size' => 46 ),
								'_element_width_mobile'        => 'initial',
								'_element_custom_width_mobile' => array( 'unit' => '%', 'size' => 100 ),
								'_flex_order_mobile'           => 'end',
								'_flex_order_tablet'           => 'end',
								'_element_width_tablet'        => 'initial',
								'_element_custom_width_tablet' => array( 'unit' => '%', 'size' => 100 ),
							)
						)
					),
					self::w( 'shf-icons', array( 'cart' => 'yes', 'account' => 'yes', 'account_style' => 'button', 'wishlist' => 'yes', 'color' => '#FFFFFF', 'icon_bg' => 'rgba(255,255,255,0.1)', 'btn_color' => '#FFFFFF', 'count_bg' => self::LIME, '_css_classes' => 'shf-push-end shf-icons--dark' ) ),
				),
				array(
					'background_background' => 'classic',
					'background_color'      => self::GREEN,
					'flex_wrap_tablet'      => 'wrap',
					'flex_wrap_mobile'      => 'wrap',
					'flex_gap'              => self::gap( 24 ),
					'flex_gap_mobile'       => self::gap( 10 ),
					'padding'               => self::box4( 16, 16 ),
				)
			),
			self::con(
				array(
					self::w( 'shf-categories', array( 'label' => 'دسته‌بندی محصولات', 'btn_bg' => self::LIME, 'btn_color' => self::GREEN ) ),
					self::w( 'shf-nav-menu', array_merge( self::grow(), array( 'location' => 'primary', 'breakpoint' => 'none', 'item_color' => '#FFFFFF', 'item_hover' => self::LIME, 'active_color' => self::LIME, 'active_bg' => 'rgba(255,255,255,0.08)', 'dropdown_bg' => '#FFFFFF', 'dropdown_color' => self::TEXT ) ) ),
				),
				array_merge(
					array(
						'background_background' => 'classic',
						'background_color'      => self::GREEN,
						'border_border'         => 'solid',
						'border_width'          => array( 'unit' => 'px', 'top' => '1', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ),
						'border_color'          => 'rgba(255,255,255,0.12)',
						'padding'               => self::box4( 8, 16 ),
					),
					self::hide( 'tablet', 'mobile' )
				)
			),
		);
	}

	/* ===================================================================== footers */

	/**
	 * Footer 1 — dark, complete.
	 *
	 * @return array
	 */
	private static function footer_1() {
		$phone     = self::opt( 'contact_phone', '۰۲۱-۱۲۳۴۵۶۷۸' );
		$wholesale = self::wholesale_url();
		$about     = self::opt( 'footer_about', 'فروشگاه تخصصی تجهیزات برق ساختمان و صنعت؛ عرضه کلید و پریز، روشنایی، سیم و کابل و تجهیزات حفاظتی از برندهای معتبر.' );

		$cta_buttons = array( self::button( $phone, self::tel( $phone ), self::LIME, self::GREEN, array( 'selected_icon' => array( 'value' => 'fas fa-phone-alt', 'library' => 'fa-solid' ), 'icon_indent' => array( 'unit' => 'px', 'size' => 8 ) ) ) );
		if ( $wholesale ) {
			$cta_buttons[] = self::button( 'خرید عمده', $wholesale, 'rgba(255,255,255,0.08)', '#FFFFFF', array( 'border_border' => 'solid', 'border_width' => self::box4( 1 ), 'border_color' => 'rgba(255,255,255,0.2)' ) );
		}

		return array(
			self::con(
				array(
					self::con(
						array(
							self::col(
								array(
									self::heading( 'برای انتخاب تجهیزات مشاوره می‌خواهید؟', '#FFFFFF', 18 ),
									self::text( '<p>کارشناسان ما برای استعلام قیمت، خرید عمده و انتخاب کالای مناسب پاسخگوی شما هستند.</p>', 'rgba(255,255,255,0.75)', 14 ),
								),
								60,
								array( 'flex_gap' => self::gap( 4 ), 'width_tablet' => array( 'unit' => '%', 'size' => 100 ) )
							),
							self::con( $cta_buttons, array( 'flex_gap' => self::gap( 10 ), 'flex_justify_content' => 'flex-end', 'flex_justify_content_mobile' => 'flex-start', 'flex_wrap' => 'wrap' ), true ),
						),
						array(
							'background_background' => 'classic',
							'background_color'      => 'rgba(255,255,255,0.07)',
							'border_border'         => 'solid',
							'border_width'          => self::box4( 1 ),
							'border_color'          => 'rgba(255,255,255,0.12)',
							'border_radius'         => self::box4( 22 ),
							'padding'               => self::box4( 22, 26 ),
							'padding_mobile'        => self::box4( 18 ),
							'flex_justify_content'  => 'space-between',
							'flex_direction_tablet' => 'column',
							'flex_align_items_tablet' => 'flex-start',
						),
						true
					),
					self::con(
						array(
							self::col(
								array(
									self::w( 'shf-logo', array( 'source' => 'site', 'show_name' => 'yes', 'show_tagline' => 'yes', 'name_color' => '#FFFFFF', 'tagline_color' => 'rgba(255,255,255,0.7)', '_css_classes' => 'shf-logo--on-dark' ) ),
									self::text( '<p>' . esc_html( $about ) . '</p>', 'rgba(255,255,255,0.75)', 14 ),
									self::social( '#FFFFFF', 'rgba(255,255,255,0.1)' ),
								),
								30,
								array( 'width_tablet' => array( 'unit' => '%', 'size' => 100 ) )
							),
							self::col( array( self::links( 'دسته‌بندی‌ها', 'footer_shop', 'categories', '#FFFFFF', 'rgba(255,255,255,0.75)', self::LIME, self::LIME ) ), 17 ),
							self::col( array( self::links( 'راهنما و قوانین', 'footer_help', 'pages', '#FFFFFF', 'rgba(255,255,255,0.75)', self::LIME, self::LIME ) ), 17 ),
							self::col( array( self::heading( 'ارتباط با ما', '#FFFFFF', 16 ), self::contact_list( 'rgba(255,255,255,0.85)', self::LIME ) ), 24, array( 'width_tablet' => array( 'unit' => '%', 'size' => 100 ) ) ),
						),
						array( 'flex_align_items' => 'flex-start', 'flex_wrap' => 'wrap', 'flex_gap' => self::gap( 28 ), 'padding' => self::box4( 40, 0, 24, 0 ) ),
						true
					),
					self::con(
						array( self::w( 'shf-copyright', array( 'text_color' => 'rgba(255,255,255,0.7)', 'align' => 'center' ) ) ),
						array(
							'border_border'        => 'solid',
							'border_width'         => array( 'unit' => 'px', 'top' => '1', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ),
							'border_color'         => 'rgba(255,255,255,0.12)',
							'padding'              => self::box4( 16, 0 ),
							'flex_justify_content' => 'center',
						),
						true
					),
				),
				array(
					'flex_direction'        => 'column',
					'flex_align_items'      => 'stretch',
					'flex_gap'              => self::gap( 0 ),
					'background_background' => 'classic',
					'background_color'      => self::GREEN,
					'padding'               => self::box4( 40, 16, 0, 16 ),
					'margin'                => array( 'unit' => 'px', 'top' => '32', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ),
					'border_radius'         => array( 'unit' => 'px', 'top' => '32', 'right' => '32', 'bottom' => '0', 'left' => '0', 'isLinked' => false ),
					'css_classes'           => 'shf-footer shf-footer--1',
				)
			),
		);
	}

	/**
	 * Footer 2 — light columns.
	 *
	 * @return array
	 */
	private static function footer_2() {
		$about = self::opt( 'footer_about', 'فروشگاه تخصصی تجهیزات برق ساختمان و صنعت با ضمانت اصالت کالا و ارسال سریع.' );
		return array(
			self::con(
				array(
					self::con(
						array(
							self::col(
								array(
									self::w( 'shf-logo', array( 'source' => 'site', 'show_name' => 'yes', 'show_tagline' => 'yes', 'name_color' => self::GREEN ) ),
									self::text( '<p>' . esc_html( $about ) . '</p>', self::MUTED, 14 ),
									self::contact_list( self::TEXT, self::BRAND ),
								),
								32,
								array( 'width_tablet' => array( 'unit' => '%', 'size' => 100 ) )
							),
							self::col( array( self::links( 'فروشگاه', 'footer_shop', 'categories', self::TEXT, self::MUTED, self::BRAND, self::BRAND ) ), 18 ),
							self::col( array( self::links( 'خدمات مشتریان', 'footer_help', 'pages', self::TEXT, self::MUTED, self::BRAND, self::BRAND ) ), 18 ),
							self::col(
								array(
									self::heading( 'ساعات پاسخگویی', self::TEXT, 16 ),
									self::text( '<p>' . esc_html( self::opt( 'contact_hours', 'شنبه تا چهارشنبه ۹ تا ۱۸ — پنجشنبه ۹ تا ۱۴' ) ) . '</p>', self::MUTED, 14 ),
									self::button( 'تماس با پشتیبانی', self::tel( self::opt( 'contact_phone', '۰۲۱-۱۲۳۴۵۶۷۸' ) ), self::GREEN, '#FFFFFF' ),
								),
								22
							),
						),
						array( 'flex_align_items' => 'flex-start', 'flex_wrap' => 'wrap', 'flex_gap' => self::gap( 28 ), 'padding' => self::box4( 40, 0, 28, 0 ) ),
						true
					),
					self::con(
						array(
							self::w( 'shf-copyright', array_merge( self::grow(), array( 'text_color' => self::MUTED, 'align_mobile' => 'center' ) ) ),
							self::social( self::GREEN, '#FFFFFF', 'left' ),
						),
						array(
							'border_border'         => 'solid',
							'border_width'          => array( 'unit' => 'px', 'top' => '1', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ),
							'border_color'          => self::BORDER,
							'padding'               => self::box4( 14, 0 ),
							'flex_direction_mobile' => 'column',
						),
						true
					),
				),
				array(
					'flex_direction'        => 'column',
					'flex_align_items'      => 'stretch',
					'flex_gap'              => self::gap( 0 ),
					'background_background' => 'classic',
					'background_color'      => '#FFFFFF',
					'border_border'         => 'solid',
					'border_width'          => array( 'unit' => 'px', 'top' => '1', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ),
					'border_color'          => self::BORDER,
					'padding'               => self::box4( 0, 16 ),
					'margin'                => array( 'unit' => 'px', 'top' => '32', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ),
					'css_classes'           => 'shf-footer shf-footer--2',
				)
			),
		);
	}

	/**
	 * Footer 3 — minimal centered.
	 *
	 * @return array
	 */
	private static function footer_3() {
		return array(
			self::con(
				array(
					self::w( 'shf-logo', array( 'source' => 'site', 'show_name' => 'yes', 'show_tagline' => '', 'name_color' => self::GREEN ) ),
					self::w( 'shf-menu-list', array( 'location' => 'footer_help', 'source' => 'pages', 'layout' => 'horizontal', 'bullet' => '', 'link_color' => self::TEXT, 'link_hover' => self::BRAND, 'gap' => array( 'unit' => 'px', 'size' => 22 ) ) ),
					self::social( self::GREEN, '#EEF4EF', 'center' ),
					self::w( 'shf-copyright', array( 'text_color' => self::MUTED, 'align' => 'center' ) ),
				),
				array(
					'flex_direction'        => 'column',
					'flex_align_items'      => 'center',
					'flex_gap'              => self::gap( 18 ),
					'background_background' => 'classic',
					'background_color'      => self::BG,
					'border_border'         => 'solid',
					'border_width'          => array( 'unit' => 'px', 'top' => '1', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ),
					'border_color'          => self::BORDER,
					'padding'               => self::box4( 36, 16, 24, 16 ),
					'margin'                => array( 'unit' => 'px', 'top' => '32', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ),
					'css_classes'           => 'shf-footer shf-footer--3',
				)
			),
		);
	}
}
