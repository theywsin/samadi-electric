<?php
/**
 * Settings page (WooCommerce → Samadi) using the WordPress Settings API.
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

/**
 * Settings.
 */
class Samadi_EW_Settings {

	const OPTION = 'samadi_ew_settings';
	const PAGE   = 'samadi-ew-settings';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 60 );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( SAMADI_EW_FILE ), array( __CLASS__, 'action_links' ) );
		add_filter( 'option_page_capability_samadi_ew', array( __CLASS__, 'capability' ) );

		// Keep the header cart count live when the Samadi theme (which registers it) is not active.
		add_filter( 'woocommerce_add_to_cart_fragments', array( __CLASS__, 'cart_fragment' ), 20 );
	}

	/**
	 * Cart count fragment fallback.
	 *
	 * @param array $fragments Fragments.
	 * @return array
	 */
	public static function cart_fragment( $fragments ) {
		if ( ! isset( $fragments['span.se-cart-count'] ) ) {
			$fragments['span.se-cart-count'] = Samadi_EW_Components::cart_count_html();
		}

		return $fragments;
	}

	/**
	 * Shop managers may save these settings.
	 *
	 * @return string
	 */
	public static function capability() {
		return 'manage_woocommerce';
	}

	/**
	 * Admin menu.
	 */
	public static function menu() {
		add_submenu_page(
			'woocommerce',
			__( 'تنظیمات صمدی', 'samadi-elementor-widgets' ),
			__( 'تنظیمات صمدی', 'samadi-elementor-widgets' ),
			'manage_woocommerce',
			self::PAGE,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Settings link on the plugins screen.
	 *
	 * @param array $links Links.
	 * @return array
	 */
	public static function action_links( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=' . self::PAGE ) ) . '">' . esc_html__( 'تنظیمات', 'samadi-elementor-widgets' ) . '</a>' );

		return $links;
	}

	/**
	 * Register setting + fields.
	 */
	public static function register() {
		register_setting(
			'samadi_ew',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => samadi_ew_setting_defaults(),
			)
		);

		add_settings_section( 'samadi_ew_quote', __( 'خرید عمده و استعلام قیمت', 'samadi-elementor-widgets' ), '__return_false', self::PAGE );
		add_settings_section( 'samadi_ew_shop', __( 'جستجو، فیلتر و محصولات اخیر', 'samadi-elementor-widgets' ), '__return_false', self::PAGE );

		$fields = array(
			'quote_page'             => array( 'samadi_ew_quote', __( 'صفحه فرم خرید عمده / استعلام', 'samadi-elementor-widgets' ), 'page' ),
			'quote_email'            => array( 'samadi_ew_quote', __( 'ایمیل دریافت اعلان', 'samadi-elementor-widgets' ), 'email' ),
			'quote_button'           => array( 'samadi_ew_quote', __( 'دکمه استعلام قیمت در صفحه محصول', 'samadi-elementor-widgets' ), 'quote_button' ),
			'quote_button_label'     => array( 'samadi_ew_quote', __( 'متن دکمه استعلام', 'samadi-elementor-widgets' ), 'text' ),
			'price_on_request_empty' => array( 'samadi_ew_quote', __( 'محصولات بدون قیمت', 'samadi-elementor-widgets' ), 'checkbox', __( 'به‌جای قیمت خالی، «استعلام قیمت» نمایش داده شود (این محصولات قابل افزودن به سبد نیستند).', 'samadi-elementor-widgets' ) ),
			'live_search'            => array( 'samadi_ew_shop', __( 'جستجوی زنده', 'samadi-elementor-widgets' ), 'checkbox', __( 'پیشنهاد لحظه‌ای محصولات، دسته‌ها و برندها هنگام تایپ', 'samadi-elementor-widgets' ) ),
			'search_sku'             => array( 'samadi_ew_shop', __( 'جستجوی کد کالا', 'samadi-elementor-widgets' ), 'checkbox', __( 'نتایج جستجوی فروشگاه شامل تطابق SKU (کد کالا) هم باشد', 'samadi-elementor-widgets' ) ),
			'filter_noindex'         => array( 'samadi_ew_shop', __( 'ایندکس صفحات فیلترشده', 'samadi-elementor-widgets' ), 'checkbox', __( 'آدرس‌های دارای فیلتر با noindex,follow علامت‌گذاری شوند (پیشنهادی برای جلوگیری از محتوای تکراری)', 'samadi-elementor-widgets' ) ),
			'recently_viewed'        => array( 'samadi_ew_shop', __( 'محصولات اخیراً مشاهده‌شده', 'samadi-elementor-widgets' ), 'checkbox', __( 'نمایش خودکار در انتهای صفحه محصول', 'samadi-elementor-widgets' ) ),
			'recently_viewed_limit'  => array( 'samadi_ew_shop', __( 'تعداد محصولات اخیر', 'samadi-elementor-widgets' ), 'number' ),
		);

		foreach ( $fields as $key => $field ) {
			add_settings_field(
				$key,
				$field[1],
				array( __CLASS__, 'field' ),
				self::PAGE,
				$field[0],
				array(
					'key'         => $key,
					'type'        => $field[2],
					'description' => isset( $field[3] ) ? $field[3] : '',
					'label_for'   => 'samadi-ew-' . $key,
				)
			);
		}
	}

	/**
	 * Sanitize settings.
	 *
	 * @param mixed $input Raw input.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$input    = is_array( $input ) ? $input : array();
		$defaults = samadi_ew_setting_defaults();

		$email = isset( $input['quote_email'] ) ? sanitize_email( $input['quote_email'] ) : '';

		return array(
			'quote_page'             => isset( $input['quote_page'] ) ? absint( $input['quote_page'] ) : 0,
			'quote_email'            => is_email( $email ) ? $email : '',
			'quote_button'           => isset( $input['quote_button'] ) && in_array( $input['quote_button'], array( 'all', 'request', 'none' ), true ) ? $input['quote_button'] : $defaults['quote_button'],
			'quote_button_label'     => isset( $input['quote_button_label'] ) && '' !== trim( $input['quote_button_label'] ) ? sanitize_text_field( $input['quote_button_label'] ) : $defaults['quote_button_label'],
			'price_on_request_empty' => ! empty( $input['price_on_request_empty'] ),
			'live_search'            => ! empty( $input['live_search'] ),
			'search_sku'             => ! empty( $input['search_sku'] ),
			'filter_noindex'         => ! empty( $input['filter_noindex'] ),
			'recently_viewed'        => ! empty( $input['recently_viewed'] ),
			'recently_viewed_limit'  => isset( $input['recently_viewed_limit'] ) ? max( 1, min( 20, absint( $input['recently_viewed_limit'] ) ) ) : $defaults['recently_viewed_limit'],
		);
	}

	/**
	 * Render a field.
	 *
	 * @param array $args Args.
	 */
	public static function field( $args ) {
		$key   = $args['key'];
		$value = samadi_ew_setting( $key );
		$name  = self::OPTION . '[' . $key . ']';
		$id    = $args['label_for'];

		switch ( $args['type'] ) {
			case 'page':
				wp_dropdown_pages(
					array(
						'name'              => esc_attr( $name ),
						'id'                => esc_attr( $id ),
						'selected'          => absint( $value ),
						'show_option_none'  => esc_html__( '— انتخاب صفحه —', 'samadi-elementor-widgets' ),
						'option_none_value' => '0',
					)
				);
				echo '<p class="description">' . esc_html__( 'شورت‌کد [samadi_quote_form] یا ویجت «فرم استعلام قیمت صمدی» باید در این صفحه قرار داشته باشد.', 'samadi-elementor-widgets' ) . '</p>';
				break;
			case 'email':
				printf( '<input type="email" class="regular-text" id="%s" name="%s" value="%s" placeholder="%s">', esc_attr( $id ), esc_attr( $name ), esc_attr( $value ), esc_attr( get_option( 'admin_email' ) ) );
				echo '<p class="description">' . esc_html__( 'خالی = ایمیل مدیر سایت', 'samadi-elementor-widgets' ) . '</p>';
				break;
			case 'quote_button':
				$choices = array(
					'all'     => __( 'برای همه محصولات', 'samadi-elementor-widgets' ),
					'request' => __( 'فقط محصولات «قیمت با استعلام»', 'samadi-elementor-widgets' ),
					'none'    => __( 'نمایش داده نشود', 'samadi-elementor-widgets' ),
				);
				printf( '<select id="%s" name="%s">', esc_attr( $id ), esc_attr( $name ) );
				foreach ( $choices as $choice => $label ) {
					printf( '<option value="%s" %s>%s</option>', esc_attr( $choice ), selected( $value, $choice, false ), esc_html( $label ) );
				}
				echo '</select>';
				break;
			case 'checkbox':
				printf( '<label><input type="checkbox" id="%s" name="%s" value="1" %s> %s</label>', esc_attr( $id ), esc_attr( $name ), checked( (bool) $value, true, false ), esc_html( $args['description'] ) );
				break;
			case 'number':
				printf( '<input type="number" min="1" max="20" class="small-text" id="%s" name="%s" value="%s">', esc_attr( $id ), esc_attr( $name ), esc_attr( $value ) );
				break;
			default:
				printf( '<input type="text" class="regular-text" id="%s" name="%s" value="%s">', esc_attr( $id ), esc_attr( $name ), esc_attr( $value ) );
		}
	}

	/**
	 * Render settings page.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'تنظیمات صمدی الکتریک', 'samadi-elementor-widgets' ); ?></h1>
			<?php Samadi_EW_Setup::render_notices(); ?>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'samadi_ew' );
				do_settings_sections( self::PAGE );
				submit_button();
				?>
			</form>
			<hr>
			<?php Samadi_EW_Setup::render_tools(); ?>
		</div>
		<?php
	}
}
