<?php
/**
 * Demo page definitions: Elementor layouts (home, about, contact, wholesale) and block pages.
 *
 * @package Samadi_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Demo pages.
 */
class Samadi_Core_Demo_Pages {

	/**
	 * Page definitions keyed by page key.
	 *
	 * @return array<string, array>
	 */
	public static function definitions() {
		return array(
			'home'      => array( 'title' => 'صفحه اصلی', 'slug' => 'home', 'elementor' => self::home() ),
			'wholesale' => array( 'title' => 'خرید عمده و استعلام قیمت', 'slug' => 'wholesale', 'elementor' => self::wholesale(), 'order' => 2 ),
			'about'     => array( 'title' => 'درباره ما', 'slug' => 'about', 'elementor' => self::about(), 'order' => 3 ),
			'contact'   => array( 'title' => 'تماس با ما', 'slug' => 'contact', 'elementor' => self::contact(), 'order' => 4 ),
			'faq'       => array( 'title' => 'سؤالات متداول', 'slug' => 'faq', 'content' => self::faq(), 'order' => 5 ),
			'shipping'  => array( 'title' => 'رویه ارسال و مرجوعی', 'slug' => 'shipping-returns', 'content' => self::shipping(), 'order' => 6 ),
			'terms'     => array( 'title' => 'قوانین و مقررات', 'slug' => 'terms', 'content' => self::terms(), 'order' => 7 ),
			'privacy'   => array( 'title' => 'حریم خصوصی', 'slug' => 'privacy-policy', 'content' => self::privacy_content(), 'order' => 8 ),
			'blog'      => array( 'title' => 'مجله برق', 'slug' => 'blog', 'content' => '', 'order' => 9 ),
		);
	}

	/* ------------------------------------------------------------------ Elementor helpers */

	/**
	 * Unique Elementor element ID.
	 *
	 * @return string
	 */
	private static function eid() {
		return substr( md5( uniqid( '', true ) . wp_rand() ), 0, 7 );
	}

	/**
	 * Section container (boxed, theme gutter).
	 *
	 * @param array $elements Children.
	 * @param array $settings Extra settings.
	 * @return array
	 */
	private static function section( $elements, $settings = array() ) {
		return array(
			'id'       => self::eid(),
			'elType'   => 'container',
			'isInner'  => false,
			'settings' => array_merge(
				array(
					'content_width'  => 'boxed',
					'flex_direction' => 'column',
					'flex_gap'       => array( 'unit' => 'px', 'size' => 16, 'column' => '16', 'row' => '16' ),
					'padding'        => array( 'unit' => 'px', 'top' => '24', 'right' => '16', 'bottom' => '24', 'left' => '16', 'isLinked' => false ),
				),
				$settings
			),
			'elements' => $elements,
		);
	}

	/**
	 * Inner container (column / row).
	 *
	 * @param array $elements Children.
	 * @param array $settings Settings.
	 * @return array
	 */
	private static function box( $elements, $settings = array() ) {
		return array(
			'id'       => self::eid(),
			'elType'   => 'container',
			'isInner'  => true,
			'settings' => array_merge(
				array(
					'content_width'  => 'full',
					'flex_direction' => 'column',
					'padding'        => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true ),
				),
				$settings
			),
			'elements' => $elements,
		);
	}

	/**
	 * Card-style inner container.
	 *
	 * @param array $elements Children.
	 * @param array $settings Settings.
	 * @return array
	 */
	private static function card( $elements, $settings = array() ) {
		return self::box(
			$elements,
			array_merge(
				array(
					'background_background' => 'classic',
					'background_color'      => '#FFFFFF',
					'border_border'         => 'solid',
					'border_width'          => array( 'unit' => 'px', 'top' => '1', 'right' => '1', 'bottom' => '1', 'left' => '1', 'isLinked' => true ),
					'border_color'          => '#E8ECE8',
					'border_radius'         => array( 'unit' => 'px', 'top' => '24', 'right' => '24', 'bottom' => '24', 'left' => '24', 'isLinked' => true ),
					'padding'               => array( 'unit' => 'px', 'top' => '32', 'right' => '32', 'bottom' => '32', 'left' => '32', 'isLinked' => true ),
					'padding_mobile'        => array( 'unit' => 'px', 'top' => '20', 'right' => '20', 'bottom' => '20', 'left' => '20', 'isLinked' => true ),
				),
				$settings
			)
		);
	}

	/**
	 * Widget element.
	 *
	 * @param string $type     Widget type.
	 * @param array  $settings Settings.
	 * @return array
	 */
	private static function widget( $type, $settings ) {
		return array(
			'id'         => self::eid(),
			'elType'     => 'widget',
			'widgetType' => $type,
			'settings'   => $settings,
			'elements'   => array(),
		);
	}

	/**
	 * Heading widget.
	 *
	 * @param string $text Text.
	 * @param string $tag  Tag.
	 * @param array  $extra Extra settings.
	 * @return array
	 */
	private static function heading( $text, $tag = 'h2', $extra = array() ) {
		return self::widget( 'heading', array_merge( array( 'title' => $text, 'header_size' => $tag ), $extra ) );
	}

	/**
	 * Text editor widget.
	 *
	 * @param string $html HTML.
	 * @return array
	 */
	private static function text( $html ) {
		return self::widget( 'text-editor', array( 'editor' => $html, 'text_color' => '#4A5A54' ) );
	}

	/**
	 * Image widget for a demo image.
	 *
	 * @param string $key Image key.
	 * @return array
	 */
	private static function image( $key ) {
		$id = Samadi_Core_Demo_Importer::id( 'attachments', $key );
		return self::widget(
			'image',
			array(
				'image'                => array( 'id' => $id, 'url' => $id ? (string) wp_get_attachment_url( $id ) : '' ),
				'image_size'           => 'large',
				'image_border_radius'  => array( 'unit' => 'px', 'top' => '24', 'right' => '24', 'bottom' => '24', 'left' => '24', 'isLinked' => true ),
			)
		);
	}

	/**
	 * Button widget.
	 *
	 * @param string $text  Text.
	 * @param string $url   URL.
	 * @param bool   $light Light style.
	 * @return array
	 */
	private static function button( $text, $url, $light = false ) {
		return self::widget(
			'button',
			array(
				'text'             => $text,
				'link'             => array( 'url' => $url, 'is_external' => '', 'nofollow' => '' ),
				'background_color' => $light ? '#FFFFFF' : '#064E3B',
				'button_text_color' => $light ? '#064E3B' : '#FFFFFF',
				'border_radius'    => array( 'unit' => 'px', 'top' => '10', 'right' => '10', 'bottom' => '10', 'left' => '10', 'isLinked' => true ),
			)
		);
	}

	/**
	 * Page URL helper.
	 *
	 * @param string $key Page key.
	 * @return string
	 */
	private static function url( $key ) {
		$id = Samadi_Core_Demo_Importer::id( 'posts', 'page:' . $key );
		return $id ? (string) get_permalink( $id ) : home_url( '/' . $key . '/' );
	}

	/* ------------------------------------------------------------------ Elementor pages */

	/**
	 * Home page: companion plugin template + demo hero slides.
	 *
	 * @return array
	 */
	private static function home() {
		$file = defined( 'SAMADI_EW_DIR' ) ? SAMADI_EW_DIR . 'templates/elementor/home.json' : '';
		if ( ! $file || ! file_exists( $file ) ) {
			return array();
		}
		$template = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Bundled file.
		if ( empty( $template['content'] ) ) {
			return array();
		}

		$shop      = wc_get_page_permalink( 'shop' );
		$wholesale = self::url( 'wholesale' );
		$replace   = array(
			'{{shop_url}}'      => $shop,
			'{{wholesale_url}}' => $wholesale,
			'{{blog_url}}'      => self::url( 'blog' ),
		);
		$content   = self::replace( $template['content'], $replace );

		$slides = array(
			array( 'hero-1', 'تجهیزات الکتریکی باکیفیت، برای زندگی بهتر', 'صمدی الکتریک، همراه مطمئن شما در تأمین کلیه لوازم برق ساختمان و صنعت.', 'مشاهده محصولات', $shop, 'خرید عمده', $wholesale ),
			array( 'hero-2', 'سیم، کابل و تابلو برق برای پروژه‌ها', 'قیمت ویژه پیمانکاران و ارسال سریع به سراسر کشور با پیش‌فاکتور رسمی.', 'استعلام قیمت', $wholesale, 'سیم و کابل', self::term_url( 'cables' ) ),
			array( 'hero-3', 'روشنایی LED، مصرف کمتر و نور بیشتر', 'پنل، اسپات، لامپ و پروژکتور با گارانتی معتبر.', 'محصولات روشنایی', self::term_url( 'lighting' ), 'لامپ‌ها', self::term_url( 'lamps' ) ),
		);

		$items = array();
		foreach ( $slides as $slide ) {
			$id      = Samadi_Core_Demo_Importer::id( 'attachments', $slide[0] );
			$items[] = array(
				'_id'       => self::eid(),
				'image'     => array( 'id' => $id, 'url' => $id ? (string) wp_get_attachment_url( $id ) : '' ),
				'title'     => $slide[1],
				'text'      => $slide[2],
				'btn1_text' => $slide[3],
				'btn1_url'  => array( 'url' => $slide[4], 'is_external' => '', 'nofollow' => '' ),
				'btn2_text' => $slide[5],
				'btn2_url'  => array( 'url' => $slide[6], 'is_external' => '', 'nofollow' => '' ),
				'align'     => 'end',
			);
		}

		foreach ( $content as &$section ) {
			foreach ( $section['elements'] as &$element ) {
				if ( isset( $element['widgetType'] ) && 'samadi-hero' === $element['widgetType'] ) {
					$element['settings']['slides'] = $items;
				}
			}
		}
		unset( $section, $element );

		return $content;
	}

	/**
	 * Product category URL by demo slug.
	 *
	 * @param string $slug Slug.
	 * @return string
	 */
	private static function term_url( $slug ) {
		$id   = Samadi_Core_Demo_Importer::id( 'terms', 'product_cat:' . $slug );
		$link = $id ? get_term_link( $id, 'product_cat' ) : '';
		return is_string( $link ) ? $link : wc_get_page_permalink( 'shop' );
	}

	/**
	 * Wholesale / quote page.
	 *
	 * @return array
	 */
	private static function wholesale() {
		return array(
			self::section(
				array(
					self::box(
						array(
							self::card(
								array(
									self::heading( 'خرید عمده و استعلام قیمت', 'h1' ),
									self::text( '<p>پیمانکاران، فروشگاه‌ها و کارفرمایان پروژه‌ها می‌توانند فهرست کالای مورد نیاز را ثبت کنند تا کارشناسان فروش، پیش‌فاکتور رسمی با قیمت همکاری را ارسال کنند.</p><ul><li>قیمت ویژه بر اساس حجم سفارش</li><li>ارسال مستقیم به محل پروژه</li><li>صدور فاکتور رسمی</li><li>مشاوره فنی رایگان در انتخاب تجهیزات</li></ul>' ),
								)
							),
							self::card( array( self::widget( 'samadi-quote-form', array( 'title' => 'فرم درخواست پیش‌فاکتور', 'intro' => 'نام کالا یا کد آن، تعداد و توضیحات را وارد کنید.', 'button' => 'ارسال درخواست' ) ) ) ),
						),
						array(
							'flex_direction'        => 'row',
							'flex_direction_tablet' => 'column',
							'flex_gap'              => array( 'unit' => 'px', 'size' => 24, 'column' => '24', 'row' => '24' ),
							'flex_align_items'      => 'stretch',
						)
					),
				)
			),
		);
	}

	/**
	 * About page.
	 *
	 * @return array
	 */
	private static function about() {
		$stat = static function ( $number, $label ) {
			return self::card(
				array(
					self::heading( $number, 'p', array( 'align' => 'center', 'title_color' => '#064E3B', 'typography_typography' => 'custom', 'typography_font_size' => array( 'unit' => 'px', 'size' => 34 ), 'typography_font_weight' => '900' ) ),
					self::widget( 'text-editor', array( 'editor' => '<p>' . $label . '</p>', 'align' => 'center', 'text_color' => '#718078' ) ),
				),
				array(
					'padding'      => array( 'unit' => 'px', 'top' => '24', 'right' => '16', 'bottom' => '16', 'left' => '16', 'isLinked' => false ),
					'width'        => array( 'unit' => '%', 'size' => 23 ),
					'width_tablet' => array( 'unit' => '%', 'size' => 47 ),
					'width_mobile' => array( 'unit' => '%', 'size' => 100 ),
					'_flex_grow'   => 1,
				)
			);
		};

		return array(
			self::section(
				array(
					self::box(
						array(
							self::box(
								array(
									self::heading( 'درباره صمدی الکتریک', 'h1' ),
									self::text( '<p>صمدی الکتریک از دل بازار لاله‌زار تهران شروع کرد؛ با یک هدف ساده: تأمین تجهیزات برق اصل، با قیمت منصفانه و مشاوره صادقانه. امروز با فروشگاه آنلاین، کالاهای ما به پیمانکاران، فروشگاه‌ها و خانواده‌ها در سراسر ایران می‌رسد.</p><p>ما فقط فروشنده نیستیم؛ کارشناسان ما پیش از خرید کمک می‌کنند مناسب‌ترین کالا را برای پروژه یا منزل خود انتخاب کنید.</p>' ),
									self::button( 'مشاهده محصولات', wc_get_page_permalink( 'shop' ) ),
								),
								array( 'width' => array( 'unit' => '%', 'size' => 50 ), 'width_tablet' => array( 'unit' => '%', 'size' => 100 ), 'flex_justify_content' => 'center', 'flex_gap' => array( 'unit' => 'px', 'size' => 12, 'column' => '12', 'row' => '12' ) )
							),
							self::box( array( self::image( 'about' ) ), array( 'width' => array( 'unit' => '%', 'size' => 50 ), 'width_tablet' => array( 'unit' => '%', 'size' => 100 ) ) ),
						),
						array( 'flex_direction' => 'row', 'flex_direction_tablet' => 'column', 'flex_gap' => array( 'unit' => 'px', 'size' => 32, 'column' => '32', 'row' => '32' ), 'flex_align_items' => 'center' )
					),
				),
				array( 'padding' => array( 'unit' => 'px', 'top' => '40', 'right' => '16', 'bottom' => '24', 'left' => '16', 'isLinked' => false ) )
			),
			self::section(
				array(
					self::box(
						array( $stat( '+۱۵', 'سال تجربه' ), $stat( '+۲۰۰۰', 'کالای فعال' ), $stat( '+۴۰۰', 'پروژه تأمین‌شده' ), $stat( '۳۱', 'استان تحت پوشش ارسال' ) ),
						array( 'flex_direction' => 'row', 'flex_wrap' => 'wrap', 'flex_gap' => array( 'unit' => 'px', 'size' => 16, 'column' => '16', 'row' => '16' ) )
					),
				)
			),
			self::section(
				array(
					self::heading( 'برندهای همکار', 'h2' ),
					self::widget( 'samadi-brands', array( 'title' => '', 'limit' => 10, 'layout' => 'grid', 'columns' => 5, 'columns_tablet' => 3, 'columns_mobile' => 2 ) ),
				)
			),
			self::section(
				array(
					self::card(
						array(
							self::heading( 'پروژه دارید؟ از ما پیش‌فاکتور بگیرید', 'h2', array( 'title_color' => '#FFFFFF' ) ),
							self::widget( 'text-editor', array( 'editor' => '<p>فهرست کالا را ارسال کنید؛ کارشناسان ما قیمت همکاری را اعلام می‌کنند.</p>', 'text_color' => '#D9E7E1' ) ),
							self::button( 'درخواست پیش‌فاکتور', self::url( 'wholesale' ), true ),
						),
						array( 'background_color' => '#064E3B', 'border_border' => 'none', 'flex_gap' => array( 'unit' => 'px', 'size' => 8, 'column' => '8', 'row' => '8' ) )
					),
				),
				array( 'padding' => array( 'unit' => 'px', 'top' => '8', 'right' => '16', 'bottom' => '40', 'left' => '16', 'isLinked' => false ) )
			),
		);
	}

	/**
	 * Contact page.
	 *
	 * @return array
	 */
	private static function contact() {
		return array(
			self::section(
				array(
					self::heading( 'تماس با ما', 'h1' ),
					self::text( '<p>برای مشاوره پیش از خرید، پیگیری سفارش یا خرید عمده، از راه‌های زیر با ما در ارتباط باشید. پاسخگویی در ساعات کاری انجام می‌شود.</p>' ),
					self::box(
						array(
							self::card( array( self::heading( 'اطلاعات تماس', 'h2' ), self::widget( 'samadi-contact', array() ) ), array( 'width' => array( 'unit' => '%', 'size' => 50 ), 'width_tablet' => array( 'unit' => '%', 'size' => 100 ) ) ),
							self::card(
								array(
									self::heading( 'خرید عمده و پروژه‌ها', 'h2' ),
									self::text( '<p>اگر فهرست کالا یا نقشه پروژه دارید، آن را از طریق فرم استعلام قیمت ارسال کنید تا پیش‌فاکتور رسمی برایتان صادر شود.</p>' ),
									self::button( 'فرم استعلام قیمت', self::url( 'wholesale' ) ),
									self::heading( 'پیگیری سفارش', 'h3' ),
									self::text( '<p>وضعیت سفارش‌های خود را از بخش «حساب کاربری ← سفارش‌ها» ببینید.</p>' ),
									self::button( 'ورود به حساب کاربری', wc_get_page_permalink( 'myaccount' ), true ),
								),
								array( 'width' => array( 'unit' => '%', 'size' => 50 ), 'width_tablet' => array( 'unit' => '%', 'size' => 100 ), 'flex_gap' => array( 'unit' => 'px', 'size' => 10, 'column' => '10', 'row' => '10' ) )
							),
						),
						array( 'flex_direction' => 'row', 'flex_direction_tablet' => 'column', 'flex_gap' => array( 'unit' => 'px', 'size' => 24, 'column' => '24', 'row' => '24' ), 'flex_align_items' => 'stretch' )
					),
				),
				array( 'padding' => array( 'unit' => 'px', 'top' => '32', 'right' => '16', 'bottom' => '40', 'left' => '16', 'isLinked' => false ) )
			),
		);
	}

	/**
	 * Recursive placeholder replacement.
	 *
	 * @param mixed $data Data.
	 * @param array $map  Map.
	 * @return mixed
	 */
	private static function replace( $data, $map ) {
		if ( is_array( $data ) ) {
			foreach ( $data as $k => $v ) {
				$data[ $k ] = self::replace( $v, $map );
			}
			return $data;
		}
		return is_string( $data ) ? strtr( $data, $map ) : $data;
	}

	/* ------------------------------------------------------------------ block pages */

	/**
	 * Paragraph/heading blocks from a [heading => text] map.
	 *
	 * @param string $intro    Intro paragraph.
	 * @param array  $sections Sections.
	 * @return string
	 */
	private static function blocks( $intro, $sections ) {
		$html = '<!-- wp:paragraph --><p>' . $intro . '</p><!-- /wp:paragraph -->';
		foreach ( $sections as $heading => $text ) {
			$html .= '<!-- wp:heading --><h2 class="wp-block-heading">' . $heading . '</h2><!-- /wp:heading -->';
			$html .= '<!-- wp:paragraph --><p>' . $text . '</p><!-- /wp:paragraph -->';
		}
		return $html;
	}

	/**
	 * FAQ page (details blocks).
	 *
	 * @return string
	 */
	private static function faq() {
		$data = require SAMADI_CORE_DIR . 'demo/content.php';
		$html = '<!-- wp:paragraph --><p>پاسخ پرتکرارترین پرسش‌های مشتریان را این‌جا ببینید. اگر پاسخ خود را پیدا نکردید، با ما تماس بگیرید.</p><!-- /wp:paragraph -->';
		foreach ( $data['faq'] as $question => $answer ) {
			$html .= '<!-- wp:details --><details class="wp-block-details"><summary>' . $question . '</summary><!-- wp:paragraph {"placeholder":"Type / to add a hidden block"} --><p>' . $answer . '</p><!-- /wp:paragraph --></details><!-- /wp:details -->';
		}
		return $html;
	}

	/**
	 * Shipping and returns page.
	 *
	 * @return string
	 */
	private static function shipping() {
		return self::blocks(
			'سفارش‌ها پس از تأیید و آماده‌سازی، از طریق پست یا باربری به سراسر کشور ارسال می‌شوند.',
			array(
				'زمان آماده‌سازی و ارسال' => 'سفارش‌های ثبت‌شده تا ساعت ۱۴ روزهای کاری، در همان روز آماده و تحویل مأمور ارسال می‌شوند. زمان تحویل در تهران ۱ تا ۲ روز کاری و در سایر شهرها ۲ تا ۵ روز کاری است.',
				'هزینه ارسال'              => 'هزینه ارسال در صفحه تسویه‌حساب بر اساس روش ارسال محاسبه می‌شود. برای سفارش‌های بالای ۵ میلیون تومان، ارسال رایگان است.',
				'شرایط مرجوعی'             => 'کالای سالم، بدون استفاده و در بسته‌بندی اصلی تا ۷ روز پس از تحویل قابل مرجوع است. سیم و کابل برش‌خورده و کالاهای سفارشی قابل مرجوع نیستند.',
				'کالای معیوب'              => 'در صورت دریافت کالای معیوب یا مغایر، حداکثر ۴۸ ساعت پس از تحویل با پشتیبانی تماس بگیرید تا کالا بدون هزینه تعویض شود.',
			)
		);
	}

	/**
	 * Terms page.
	 *
	 * @return string
	 */
	private static function terms() {
		return self::blocks(
			'استفاده از فروشگاه اینترنتی صمدی الکتریک به معنای پذیرش قوانین زیر است.',
			array(
				'ثبت سفارش'            => 'سفارش پس از پرداخت یا تأیید پرداخت در محل قطعی می‌شود. در صورت ناموجود شدن کالا پس از ثبت سفارش، مبلغ آن به‌طور کامل بازگردانده می‌شود.',
				'قیمت‌ها'              => 'قیمت‌ها به تومان و شامل مالیات بر ارزش افزوده است. قیمت کالاهای استعلامی پس از بررسی درخواست اعلام می‌شود.',
				'گارانتی'              => 'گارانتی کالاها مطابق شرایط شرکت سازنده است و برگه گارانتی همراه کالا ارسال می‌شود.',
				'مسئولیت نصب'          => 'نصب تجهیزات برقی باید توسط افراد متخصص و دارای صلاحیت انجام شود. فروشگاه مسئولیتی در قبال خسارت ناشی از نصب نادرست ندارد.',
			)
		);
	}

	/**
	 * Privacy page content.
	 *
	 * @return string
	 */
	public static function privacy_content() {
		return self::blocks(
			'صمدی الکتریک به حریم خصوصی کاربران احترام می‌گذارد و اطلاعات شما را فقط برای پردازش سفارش و ارتباط با شما استفاده می‌کند.',
			array(
				'اطلاعاتی که جمع‌آوری می‌کنیم' => 'نام، شماره تماس، ایمیل و آدرس برای ارسال سفارش؛ و اطلاعات فنی مرورگر برای بهبود عملکرد سایت.',
				'نحوه استفاده'                  => 'اطلاعات شما فقط برای پردازش سفارش، ارسال کالا، پاسخ به درخواست‌ها و اطلاع‌رسانی درباره وضعیت سفارش استفاده می‌شود.',
				'اشتراک‌گذاری'                  => 'اطلاعات شما به هیچ شخص ثالثی فروخته نمی‌شود و فقط در حد لازم در اختیار شرکت ارسال‌کننده قرار می‌گیرد.',
				'دسترسی و حذف'                  => 'برای مشاهده، اصلاح یا حذف اطلاعات حساب کاربری خود با پشتیبانی تماس بگیرید.',
			)
		);
	}
}
