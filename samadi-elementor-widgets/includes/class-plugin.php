<?php
/**
 * Plugin loader.
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 */
final class Samadi_EW_Plugin {

	/**
	 * Instance.
	 *
	 * @var Samadi_EW_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Singleton.
	 *
	 * @return Samadi_EW_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Boot modules.
	 */
	private function __construct() {
		load_plugin_textdomain( 'samadi-elementor-widgets', false, dirname( plugin_basename( SAMADI_EW_FILE ) ) . '/languages' );

		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_missing_woocommerce' ) );
			return;
		}

		$modules = array(
			'class-components',
			'class-settings',
			'class-term-colors',
			'class-search',
			'class-filters',
			'class-quote',
			'class-recently-viewed',
			'class-shortcodes',
			'class-setup',
		);

		foreach ( $modules as $module ) {
			require_once SAMADI_EW_DIR . 'includes/' . $module . '.php';
		}

		Samadi_EW_Settings::init();
		Samadi_EW_Term_Colors::init();
		Samadi_EW_Search::init();
		Samadi_EW_Filters::init();
		Samadi_EW_Quote::init();
		Samadi_EW_Recently_Viewed::init();
		Samadi_EW_Shortcodes::init();
		Samadi_EW_Setup::init();

		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ), 5 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 20 );

		if ( did_action( 'elementor/loaded' ) ) {
			require_once SAMADI_EW_DIR . 'includes/elementor/class-elementor.php';
			Samadi_EW_Elementor::init();
		}
	}

	/**
	 * Register front-end assets (enqueued on demand by widgets/shortcodes, and globally where needed).
	 */
	public function register_assets() {
		// Without the Samadi theme, shared component styles come from the plugin's standalone stylesheet.
		$standalone = ! function_exists( 'samadi_option' );
		if ( $standalone ) {
			wp_register_style( 'samadi-ew-standalone', SAMADI_EW_URL . 'assets/css/standalone.css', array(), SAMADI_EW_VERSION );
		}
		wp_register_style( 'samadi-ew', SAMADI_EW_URL . 'assets/css/widgets.css', $standalone ? array( 'samadi-ew-standalone' ) : array(), SAMADI_EW_VERSION );
		wp_register_script( 'samadi-ew', SAMADI_EW_URL . 'assets/js/frontend.js', array(), SAMADI_EW_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );

		wp_localize_script(
			'samadi-ew',
			'samadiEW',
			array(
				'rest'       => esc_url_raw( rest_url( 'samadi/v1/' ) ),
				'searchUrl'  => esc_url_raw( add_query_arg( 'post_type', 'product', home_url( '/' ) ) ),
				'liveSearch' => (bool) samadi_ew_setting( 'live_search' ),
				'productId'  => 0,
				'i18n'       => array(
					'noResults'   => __( 'محصولی با این عبارت پیدا نشد.', 'samadi-elementor-widgets' ),
					'viewAll'     => __( 'مشاهده همه نتایج', 'samadi-elementor-widgets' ),
					'searching'   => __( 'در حال جستجو…', 'samadi-elementor-widgets' ),
					'products'    => __( 'محصولات', 'samadi-elementor-widgets' ),
					'categories'  => __( 'دسته‌بندی‌ها', 'samadi-elementor-widgets' ),
					'brands'      => __( 'برندها', 'samadi-elementor-widgets' ),
					'sku'         => __( 'کد:', 'samadi-elementor-widgets' ),
					'results'     => __( 'نتایج به‌روزرسانی شد.', 'samadi-elementor-widgets' ),
					'loadError'   => __( 'بارگذاری نتایج با خطا مواجه شد. صفحه دوباره بارگذاری می‌شود.', 'samadi-elementor-widgets' ),
					'prev'        => __( 'قبلی', 'samadi-elementor-widgets' ),
					'next'        => __( 'بعدی', 'samadi-elementor-widgets' ),
					'days'        => __( 'روز', 'samadi-elementor-widgets' ),
					'hours'       => __( 'ساعت', 'samadi-elementor-widgets' ),
					'minutes'     => __( 'دقیقه', 'samadi-elementor-widgets' ),
					'seconds'     => __( 'ثانیه', 'samadi-elementor-widgets' ),
					'removeRow'   => __( 'حذف ردیف', 'samadi-elementor-widgets' ),
				),
			)
		);
	}

	/**
	 * Global assets: live search runs in the header on every page; filters/recently viewed on shop pages.
	 */
	public function enqueue_assets() {
		wp_enqueue_style( 'samadi-ew' );
		wp_enqueue_script( 'samadi-ew' );

		if ( is_product() ) {
			wp_add_inline_script( 'samadi-ew', 'window.samadiEW && ( window.samadiEW.productId = ' . absint( get_queried_object_id() ) . ' );', 'before' );
		}
	}

	/**
	 * Admin notice when WooCommerce is missing.
	 */
	public function notice_missing_woocommerce() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		echo '<div class="notice notice-error"><p>' . esc_html__( 'افزونه Samadi Elementor Widgets برای کار به WooCommerce نیاز دارد. لطفاً WooCommerce را نصب و فعال کنید.', 'samadi-elementor-widgets' ) . '</p></div>';
	}
}
