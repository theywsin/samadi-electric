<?php
/**
 * Bootstrap: Elementor widgets, assets, WooCommerce cart fragments.
 *
 * @package Samadi_Header_Footer
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugin.
 */
class SHF_Plugin {

	/**
	 * Hooks.
	 */
	public static function init() {
		load_plugin_textdomain( 'samadi-header-footer', false, dirname( plugin_basename( SHF_FILE ) ) . '/languages' );

		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', array( __CLASS__, 'notice_elementor' ) );
			return;
		}

		SHF_Admin::init();
		SHF_Renderer::init();

		add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'category' ) );
		add_action( 'elementor/widgets/register', array( __CLASS__, 'widgets' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ), 5 );
		add_action( 'elementor/editor/after_enqueue_styles', array( __CLASS__, 'editor_styles' ) );
		add_filter( 'woocommerce_add_to_cart_fragments', array( __CLASS__, 'cart_fragment' ) );
	}

	/**
	 * Admin notice when Elementor is missing.
	 */
	public static function notice_elementor() {
		if ( current_user_can( 'activate_plugins' ) ) {
			echo '<div class="notice notice-error"><p>افزونه «Samadi Header & Footer» برای کار به Elementor نیاز دارد. لطفاً Elementor را نصب و فعال کنید.</p></div>';
		}
	}

	/**
	 * Widget category.
	 *
	 * @param \Elementor\Elements_Manager $manager Manager.
	 */
	public static function category( $manager ) {
		$manager->add_category( 'samadi-hf', array( 'title' => 'هدر و فوتر صمدی', 'icon' => 'eicon-header' ) );
	}

	/**
	 * Register widgets.
	 *
	 * @param \Elementor\Widgets_Manager $manager Manager.
	 */
	public static function widgets( $manager ) {
		require_once SHF_DIR . 'includes/widgets/class-base.php';
		$widgets = array(
			'logo'       => 'SHF_Widget_Logo',
			'nav-menu'   => 'SHF_Widget_Nav_Menu',
			'search'     => 'SHF_Widget_Search',
			'icons'      => 'SHF_Widget_Icons',
			'categories' => 'SHF_Widget_Categories',
			'menu-list'  => 'SHF_Widget_Menu_List',
			'copyright'  => 'SHF_Widget_Copyright',
		);
		foreach ( $widgets as $file => $class ) {
			require_once SHF_DIR . 'includes/widgets/class-' . $file . '.php';
			$manager->register( new $class() );
		}
	}

	/**
	 * Front-end assets.
	 */
	public static function register_assets() {
		wp_register_style( 'shf', SHF_URL . 'assets/css/frontend.css', array(), SHF_VERSION );
		wp_register_script( 'shf', SHF_URL . 'assets/js/frontend.js', array(), SHF_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
		wp_localize_script( 'shf', 'shfConfig', array( 'close' => 'بستن منو', 'open' => 'باز کردن منو' ) );
	}

	/**
	 * Editor styles (widget previews inside the editor).
	 */
	public static function editor_styles() {
		wp_enqueue_style( 'shf', SHF_URL . 'assets/css/frontend.css', array(), SHF_VERSION );
	}

	/**
	 * Cart count markup.
	 *
	 * @return string
	 */
	public static function cart_count() {
		$count = ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
		return sprintf( '<span class="shf-cart-count%s">%s</span>', $count ? '' : ' is-empty', esc_html( self::fa( $count ) ) );
	}

	/**
	 * Keep the cart count fresh after AJAX add-to-cart.
	 *
	 * @param array $fragments Fragments.
	 * @return array
	 */
	public static function cart_fragment( $fragments ) {
		$fragments['span.shf-cart-count'] = self::cart_count();
		return $fragments;
	}

	/**
	 * Persian digits.
	 *
	 * @param string|int $text Text.
	 * @return string
	 */
	public static function fa( $text ) {
		return strtr( (string) $text, array( '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹' ) );
	}

	/**
	 * Inline SVG icon (Lucide subset, ISC).
	 *
	 * @param string $name Icon.
	 * @param int    $size Size.
	 * @return string
	 */
	public static function icon( $name, $size = 22 ) {
		$icons = array(
			'search'        => '<path d="m21 21-4.34-4.34"/><circle cx="11" cy="11" r="8"/>',
			'user'          => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
			'heart'         => '<path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/>',
			'cart'          => '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>',
			'menu'          => '<path d="M4 5h16"/><path d="M4 12h16"/><path d="M4 19h16"/>',
			'x'             => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
			'chevron-down'  => '<path d="m6 9 6 6 6-6"/>',
			'chevron-left'  => '<path d="m15 18-6-6 6-6"/>',
			'grid'          => '<rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/>',
			'phone'         => '<path d="M13.832 16.568a1 1 0 0 0 1.213-.303l.355-.465A2 2 0 0 1 17 15h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2A18 18 0 0 1 2 4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-.8 1.6l-.468.351a1 1 0 0 0-.292 1.233 14 14 0 0 0 6.392 6.384"/>',
		);
		if ( empty( $icons[ $name ] ) ) {
			return '';
		}
		return sprintf( '<svg class="shf-icon" width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%2$s</svg>', absint( $size ), $icons[ $name ] );
	}
}
