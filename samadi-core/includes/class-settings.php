<?php
/**
 * Admin: «صمدی» menu with the core settings page.
 *
 * @package Samadi_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Settings page.
 */
final class Samadi_Core_Settings {

	/**
	 * Menu / page slug.
	 */
	const PAGE = 'samadi-core';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( SAMADI_CORE_FILE ), array( __CLASS__, 'action_links' ) );
		add_filter( 'option_page_capability_samadi_core', array( __CLASS__, 'capability' ) );
	}

	/**
	 * Capability needed to change the settings.
	 *
	 * @return string
	 */
	public static function capability() {
		return (string) apply_filters( 'samadi_core_capability', 'manage_options' );
	}

	/**
	 * Admin menu.
	 */
	public static function menu() {
		add_menu_page(
			__( 'صمدی', 'samadi-core' ),
			__( 'صمدی', 'samadi-core' ),
			self::capability(),
			self::PAGE,
			array( __CLASS__, 'render' ),
			'dashicons-lightbulb',
			56
		);

		add_submenu_page( self::PAGE, __( 'تنظیمات هسته صمدی', 'samadi-core' ), __( 'تنظیمات', 'samadi-core' ), self::capability(), self::PAGE, array( __CLASS__, 'render' ) );
	}

	/**
	 * Settings link on the plugins screen.
	 *
	 * @param array $links Links.
	 * @return array
	 */
	public static function action_links( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=' . self::PAGE ) ) . '">' . esc_html__( 'تنظیمات', 'samadi-core' ) . '</a>' );

		return $links;
	}

	/**
	 * Sections: key => [ title, description ].
	 *
	 * @return array<string, array{0: string, 1: string}>
	 */
	public static function sections() {
		return array(
			'modules'    => array( __( 'ماژول‌ها', 'samadi-core' ), __( 'هر بخش را می‌توانید جداگانه خاموش کنید. ماژول‌های فروشگاهی فقط با ووکامرس فعال اجرا می‌شوند.', 'samadi-core' ) ),
			'wishlist'   => array( __( 'علاقه‌مندی‌ها', 'samadi-core' ), __( 'دکمه قلب روی کارت محصول، آیکون هدر، نوار موبایل و بخش علاقه‌مندی حساب کاربری قالب خودکار به این ماژول وصل می‌شوند. فهرست مهمان در کوکی و فهرست کاربر واردشده در حسابش ذخیره می‌شود و هنگام ورود ادغام می‌شود.', 'samadi-core' ) ),
			'compare'    => array( __( 'مقایسه محصولات', 'samadi-core' ), __( 'محصولات انتخاب‌شده در مرورگر بازدیدکننده نگه داشته می‌شوند و در برگه مقایسه کنار هم نمایش داده می‌شوند.', 'samadi-core' ) ),
			'newsletter' => array( __( 'خبرنامه', 'samadi-core' ), __( 'فرم عضویت با شماره موبایل (شورت‌کد [samadi_newsletter]). بخش خبرنامه صفحه اصلی قالب از همین فرم استفاده می‌کند.', 'samadi-core' ) ),
			'jalali'     => array( __( 'تاریخ شمسی', 'samadi-core' ), __( 'تاریخ نوشته‌ها، دیدگاه‌ها و سفارش‌ها به شمسی نمایش داده می‌شود. اگر افزونه تاریخ شمسی دیگری (مثل Parsi Date) فعال باشد، این ماژول کاری نمی‌کند. قالب‌های ماشینی مثل Y-m-d و c میلادی می‌مانند.', 'samadi-core' ) ),
			'checkout'   => array( __( 'تسویه‌حساب ایرانی', 'samadi-core' ), __( 'ارقام فارسی و عربی فرم‌ها به لاتین تبدیل و شماره موبایل، کدپستی و کد ملی اعتبارسنجی می‌شوند (فقط برای آدرس‌های ایران).', 'samadi-core' ) ),
			'schema'     => array( __( 'اسکیمای فروشگاه', 'samadi-core' ), __( 'اطلاعات ساختاریافته فروشگاه (نام، لوگو، تلفن، آدرس، شبکه‌های اجتماعی) در صفحه اصلی. اطلاعات تماس از سفارشی‌ساز قالب خوانده می‌شود. با فعال بودن Yoast، Rank Math، SEOPress یا AIOSEO خروجی نمی‌دهد.', 'samadi-core' ) ),
			'security'   => array( __( 'امنیت', 'samadi-core' ), __( 'مقاوم‌سازی‌های کم‌خطر که روی کارکرد فروشگاه اثری ندارند.', 'samadi-core' ) ),
			'advanced'   => array( __( 'پیشرفته', 'samadi-core' ), '' ),
		);
	}

	/**
	 * Fields: key => [ section, label, type, description, choices ].
	 *
	 * @return array<string, array>
	 */
	public static function fields() {
		return array(
			'module_wishlist'        => array( 'modules', __( 'علاقه‌مندی‌ها', 'samadi-core' ), 'checkbox', self::conflict_note( 'wishlist' ) ),
			'module_compare'         => array( 'modules', __( 'مقایسه محصولات', 'samadi-core' ), 'checkbox' ),
			'module_newsletter'      => array( 'modules', __( 'خبرنامه پیامکی', 'samadi-core' ), 'checkbox' ),
			'module_jalali'          => array( 'modules', __( 'تاریخ شمسی', 'samadi-core' ), 'checkbox', self::conflict_note( 'jalali' ) ),
			'module_checkout'        => array( 'modules', __( 'تسویه‌حساب ایرانی', 'samadi-core' ), 'checkbox' ),
			'module_schema'          => array( 'modules', __( 'اسکیمای فروشگاه', 'samadi-core' ), 'checkbox' ),
			'module_security'        => array( 'modules', __( 'مقاوم‌سازی امنیتی', 'samadi-core' ), 'checkbox' ),

			'wishlist_page'          => array( 'wishlist', __( 'برگه علاقه‌مندی‌ها', 'samadi-core' ), 'page', __( 'برگه‌ای که شورت‌کد [samadi_wishlist] در آن است. هنگام فعال‌سازی افزونه خودکار ساخته می‌شود.', 'samadi-core' ) ),
			'wishlist_single'        => array( 'wishlist', __( 'دکمه در صفحه محصول', 'samadi-core' ), 'checkbox', __( 'دکمه «افزودن به علاقه‌مندی‌ها» زیر دکمه خرید', 'samadi-core' ) ),

			'compare_page'           => array( 'compare', __( 'برگه مقایسه', 'samadi-core' ), 'page', __( 'برگه‌ای که شورت‌کد [samadi_compare] در آن است. هنگام فعال‌سازی افزونه خودکار ساخته می‌شود.', 'samadi-core' ) ),
			'compare_max'            => array( 'compare', __( 'حداکثر تعداد محصولات', 'samadi-core' ), 'number', __( 'بین ۲ تا ۶', 'samadi-core' ) ),
			'compare_card'           => array( 'compare', __( 'دکمه روی کارت محصول', 'samadi-core' ), 'checkbox' ),
			'compare_single'         => array( 'compare', __( 'دکمه در صفحه محصول', 'samadi-core' ), 'checkbox' ),

			'newsletter_placeholder' => array( 'newsletter', __( 'متن راهنمای فیلد', 'samadi-core' ), 'text' ),
			'newsletter_button'      => array( 'newsletter', __( 'متن دکمه', 'samadi-core' ), 'text' ),
			'newsletter_success'     => array( 'newsletter', __( 'پیام موفقیت', 'samadi-core' ), 'text' ),
			'newsletter_notify'      => array( 'newsletter', __( 'ایمیل اعلان عضو جدید', 'samadi-core' ), 'email', __( 'خالی = بدون اعلان', 'samadi-core' ) ),

			'jalali_admin'           => array( 'jalali', __( 'در پیشخوان وردپرس', 'samadi-core' ), 'checkbox', __( 'تاریخ‌ها در بخش مدیریت هم شمسی نمایش داده شوند', 'samadi-core' ) ),
			'jalali_digits'          => array( 'jalali', __( 'ارقام فارسی', 'samadi-core' ), 'checkbox', __( 'اعداد تاریخ با ارقام فارسی نوشته شوند', 'samadi-core' ) ),

			'checkout_mobile'        => array( 'checkout', __( 'اعتبارسنجی موبایل', 'samadi-core' ), 'checkbox', __( 'فیلد تلفن باید شماره موبایل ایران باشد (۰۹xxxxxxxxx)', 'samadi-core' ) ),
			'checkout_postcode'      => array( 'checkout', __( 'اعتبارسنجی کدپستی', 'samadi-core' ), 'checkbox', __( 'کدپستی باید ۱۰ رقمی باشد', 'samadi-core' ) ),
			'checkout_national_code' => array(
				'checkout',
				__( 'فیلد کد ملی', 'samadi-core' ),
				'select',
				__( 'برای صدور فاکتور رسمی یا ارسال با پست', 'samadi-core' ),
				array(
					'off'      => __( 'نمایش داده نشود', 'samadi-core' ),
					'optional' => __( 'اختیاری', 'samadi-core' ),
					'required' => __( 'الزامی', 'samadi-core' ),
				),
			),
			'checkout_hide_company'  => array( 'checkout', __( 'پنهان کردن نام شرکت', 'samadi-core' ), 'checkbox' ),
			'checkout_hide_address2' => array( 'checkout', __( 'پنهان کردن خط دوم آدرس', 'samadi-core' ), 'checkbox' ),
			'checkout_default_iran'  => array( 'checkout', __( 'کشور پیش‌فرض ایران', 'samadi-core' ), 'checkbox', __( 'وقتی کشور فروشگاه ایران است، فرم با ایران و ترتیب «استان، شهر، آدرس» شروع شود', 'samadi-core' ) ),

			'schema_store_type'      => array(
				'schema',
				__( 'نوع کسب‌وکار', 'samadi-core' ),
				'select',
				'',
				array(
					'Store'                 => __( 'فروشگاه (Store)', 'samadi-core' ),
					'ElectronicsStore'      => __( 'فروشگاه لوازم الکترونیکی (ElectronicsStore)', 'samadi-core' ),
					'HardwareStore'         => __( 'فروشگاه ابزار و یراق (HardwareStore)', 'samadi-core' ),
					'HomeGoodsStore'        => __( 'فروشگاه لوازم خانه (HomeGoodsStore)', 'samadi-core' ),
					'OnlineStore'           => __( 'فقط فروشگاه اینترنتی (OnlineStore)', 'samadi-core' ),
				),
			),
			'schema_search'          => array( 'schema', __( 'جعبه جستجوی گوگل', 'samadi-core' ), 'checkbox', __( 'اعلام جستجوی محصولات سایت به موتورهای جستجو (WebSite + SearchAction)', 'samadi-core' ) ),

			'security_xmlrpc'        => array( 'security', __( 'غیرفعال کردن XML-RPC', 'samadi-core' ), 'checkbox', __( 'اگر از اپلیکیشن موبایل وردپرس یا Jetpack استفاده می‌کنید، خاموش کنید.', 'samadi-core' ) ),
			'security_version'       => array( 'security', __( 'پنهان کردن نسخه وردپرس', 'samadi-core' ), 'checkbox' ),
			'security_users'         => array( 'security', __( 'جلوگیری از شناسایی نام کاربری‌ها', 'samadi-core' ), 'checkbox', __( 'آدرس ?author= و فهرست کاربران REST API برای بازدیدکنندگان بسته می‌شود', 'samadi-core' ) ),
			'security_login_errors'  => array( 'security', __( 'پیام خطای یکسان ورود', 'samadi-core' ), 'checkbox', __( 'خطای ورود نشان نمی‌دهد نام کاربری درست بوده یا رمز عبور', 'samadi-core' ) ),

			'delete_data'            => array( 'advanced', __( 'حذف داده‌ها هنگام پاک کردن افزونه', 'samadi-core' ), 'checkbox', __( 'تنظیمات، فهرست اعضای خبرنامه و علاقه‌مندی‌های کاربران پاک می‌شوند', 'samadi-core' ) ),
		);
	}

	/**
	 * Note shown when another plugin already provides a feature.
	 *
	 * @param string $module Module.
	 * @return string
	 */
	private static function conflict_note( $module ) {
		if ( 'wishlist' === $module && ( defined( 'YITH_WCWL' ) || defined( 'TINVWL_FVERSION' ) || defined( 'TINVWL_VERSION' ) ) ) {
			return __( 'افزونه علاقه‌مندی دیگری فعال است؛ قالب از آن استفاده می‌کند و این ماژول کنار می‌رود.', 'samadi-core' );
		}
		if ( 'jalali' === $module && class_exists( 'Samadi_Core_Jalali_Dates' ) && Samadi_Core_Jalali_Dates::other_plugin() ) {
			return __( 'افزونه تاریخ شمسی دیگری فعال است؛ این ماژول کاری نمی‌کند.', 'samadi-core' );
		}

		return '';
	}

	/**
	 * Register setting + fields.
	 */
	public static function register() {
		register_setting(
			'samadi_core',
			SAMADI_CORE_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => samadi_core_setting_defaults(),
			)
		);

		foreach ( self::sections() as $key => $section ) {
			add_settings_section(
				'samadi_core_' . $key,
				$section[0],
				static function () use ( $section, $key ) {
					echo '<span id="samadi-core-' . esc_attr( $key ) . '" class="samadi-core-anchor"></span>';
					if ( $section[1] ) {
						echo '<p class="description">' . esc_html( $section[1] ) . '</p>';
					}
				},
				self::PAGE
			);
		}

		foreach ( self::fields() as $key => $field ) {
			add_settings_field(
				$key,
				$field[1],
				array( __CLASS__, 'field' ),
				self::PAGE,
				'samadi_core_' . $field[0],
				array(
					'key'         => $key,
					'type'        => $field[2],
					'description' => isset( $field[3] ) ? $field[3] : '',
					'choices'     => isset( $field[4] ) ? $field[4] : array(),
					'label_for'   => 'checkbox' === $field[2] ? null : 'samadi-core-' . $key,
				)
			);
		}
	}

	/**
	 * Render one field.
	 *
	 * @param array $args Field args.
	 */
	public static function field( $args ) {
		$key   = $args['key'];
		$value = samadi_core_setting( $key );
		$name  = SAMADI_CORE_OPTION . '[' . $key . ']';
		$id    = 'samadi-core-' . $key;

		switch ( $args['type'] ) {
			case 'checkbox':
				printf(
					'<label for="%1$s"><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s> %4$s</label>',
					esc_attr( $id ),
					esc_attr( $name ),
					checked( (bool) $value, true, false ),
					esc_html( $args['description'] ? $args['description'] : __( 'فعال', 'samadi-core' ) )
				);
				return;

			case 'page':
				wp_dropdown_pages(
					array(
						'name'              => esc_attr( $name ),
						'id'                => esc_attr( $id ),
						'selected'          => absint( $value ),
						'show_option_none'  => esc_html__( '— انتخاب برگه —', 'samadi-core' ),
						'option_none_value' => '0',
					)
				);
				if ( $value && get_permalink( (int) $value ) ) {
					printf( ' <a href="%s" target="_blank" rel="noopener">%s</a>', esc_url( get_permalink( (int) $value ) ), esc_html__( 'مشاهده', 'samadi-core' ) );
				}
				break;

			case 'select':
				printf( '<select id="%1$s" name="%2$s">', esc_attr( $id ), esc_attr( $name ) );
				foreach ( $args['choices'] as $choice => $label ) {
					printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $choice ), selected( (string) $value, (string) $choice, false ), esc_html( $label ) );
				}
				echo '</select>';
				break;

			case 'number':
				printf( '<input type="number" class="small-text" id="%1$s" name="%2$s" value="%3$d" min="2" max="6">', esc_attr( $id ), esc_attr( $name ), absint( $value ) );
				break;

			case 'email':
				printf( '<input type="email" class="regular-text" dir="ltr" id="%1$s" name="%2$s" value="%3$s">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ) );
				break;

			default:
				printf( '<input type="text" class="regular-text" id="%1$s" name="%2$s" value="%3$s">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ) );
		}

		if ( $args['description'] ) {
			echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
		}
	}

	/**
	 * Sanitize the whole settings array.
	 *
	 * @param mixed $input Raw input.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$input    = is_array( $input ) ? $input : array();
		$defaults = samadi_core_setting_defaults();
		$fields   = self::fields();
		$clean    = array();

		foreach ( $defaults as $key => $default ) {
			$type  = isset( $fields[ $key ] ) ? $fields[ $key ][2] : 'text';
			$value = isset( $input[ $key ] ) ? $input[ $key ] : null;

			switch ( $type ) {
				case 'checkbox':
					$clean[ $key ] = ! empty( $value );
					break;
				case 'page':
					$clean[ $key ] = absint( $value );
					break;
				case 'number':
					$clean[ $key ] = min( 6, max( 2, absint( $value ) ) );
					break;
				case 'email':
					$clean[ $key ] = sanitize_email( (string) $value );
					break;
				case 'select':
					$choices       = isset( $fields[ $key ][4] ) ? $fields[ $key ][4] : array();
					$clean[ $key ] = isset( $choices[ (string) $value ] ) ? (string) $value : $default;
					break;
				default:
					$clean[ $key ] = null === $value ? $default : sanitize_text_field( (string) $value );
			}
		}

		return $clean;
	}

	/**
	 * Settings page.
	 */
	public static function render() {
		if ( ! current_user_can( self::capability() ) ) {
			return;
		}
		?>
		<div class="wrap samadi-core-settings">
			<h1><?php esc_html_e( 'تنظیمات هسته صمدی', 'samadi-core' ); ?></h1>
			<nav class="samadi-core-toc" aria-label="<?php esc_attr_e( 'بخش‌های تنظیمات', 'samadi-core' ); ?>">
				<?php foreach ( self::sections() as $key => $section ) : ?>
					<a href="#samadi-core-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $section[0] ); ?></a>
				<?php endforeach; ?>
			</nav>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'samadi_core' );
				do_settings_sections( self::PAGE );
				submit_button();
				?>
			</form>
		</div>
		<style>
			.samadi-core-toc { display: flex; flex-wrap: wrap; gap: 6px; margin: 16px 0 8px; }
			.samadi-core-toc a { padding: 6px 12px; border: 1px solid #c3d0c9; border-radius: 999px; background: #fff; color: #123f35; text-decoration: none; font-weight: 600; }
			.samadi-core-toc a:hover, .samadi-core-toc a:focus { background: #123f35; color: #d9ee8c; }
			.samadi-core-settings h2 { margin-top: 32px; padding-top: 16px; border-top: 1px solid #dcdcde; color: #123f35; }
			.samadi-core-anchor { position: relative; top: -48px; display: block; }
		</style>
		<?php
	}
}
