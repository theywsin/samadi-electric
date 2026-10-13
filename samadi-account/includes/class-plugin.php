<?php
/**
 * Core: template overrides, account menu, assets and body classes.
 *
 * @package Samadi_Account
 */

defined( 'ABSPATH' ) || exit;

/**
 * Account dashboard.
 */
class Samadi_Account {

	/**
	 * WooCommerce templates replaced by the plugin (they take precedence over theme overrides).
	 *
	 * @var string[]
	 */
	const TEMPLATES = array(
		'myaccount/my-account.php',
		'myaccount/navigation.php',
		'myaccount/dashboard.php',
		'myaccount/orders.php',
		'myaccount/my-address.php',
	);

	/**
	 * Hooks.
	 */
	public static function init() {
		add_filter( 'wc_get_template', array( __CLASS__, 'template' ), 20, 2 );
		add_filter( 'woocommerce_account_menu_items', array( __CLASS__, 'menu_items' ), 20 );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ), 20 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ), 30 );
	}

	/**
	 * Serve the plugin's templates.
	 *
	 * @param string $template      Located template.
	 * @param string $template_name Template name.
	 * @return string
	 */
	public static function template( $template, $template_name ) {
		if ( in_array( $template_name, self::TEMPLATES, true ) && apply_filters( 'sad_use_template', true, $template_name ) ) {
			$file = SAD_DIR . 'templates/' . $template_name;
			if ( file_exists( $file ) ) {
				return $file;
			}
		}
		return $template;
	}

	/**
	 * Menu labels and order matching the design.
	 *
	 * @param array $items Items.
	 * @return array
	 */
	public static function menu_items( $items ) {
		$labels = array(
			'dashboard'       => 'پیشخوان',
			'orders'          => 'سفارش‌های من',
			'downloads'       => 'دانلودهای من',
			'edit-address'    => 'آدرس‌های من',
			'payment-methods' => 'روش‌های پرداخت',
			'edit-account'    => 'اطلاعات حساب',
			'customer-logout' => 'خروج از حساب',
		);

		$out = array();
		foreach ( $items as $key => $label ) {
			if ( 'edit-account' === $key && sad_option( 'wishlist' ) && ! isset( $items[ Samadi_Account_Wishlist::ENDPOINT ] ) ) {
				$out[ Samadi_Account_Wishlist::ENDPOINT ] = 'علاقه‌مندی‌ها';
			}
			$out[ $key ] = isset( $labels[ $key ] ) ? $labels[ $key ] : $label;
		}
		// Keep logout last.
		if ( isset( $out['customer-logout'] ) ) {
			$logout = $out['customer-logout'];
			unset( $out['customer-logout'] );
			$out['customer-logout'] = $logout;
		}
		return $out;
	}

	/**
	 * Icon for a menu endpoint.
	 *
	 * @param string $endpoint Endpoint.
	 * @return string
	 */
	public static function menu_icon( $endpoint ) {
		$map = array(
			'dashboard'       => 'house',
			'orders'          => 'shopping-bag',
			'downloads'       => 'download',
			'edit-address'    => 'map-pin',
			'wishlist'        => 'heart',
			'payment-methods' => 'credit-card',
			'edit-account'    => 'user',
			'customer-logout' => 'log-out',
		);
		return (string) apply_filters( 'sad_menu_icon', isset( $map[ $endpoint ] ) ? $map[ $endpoint ] : 'chevron-left', $endpoint );
	}

	/**
	 * Current account endpoint key.
	 *
	 * @return string
	 */
	public static function endpoint() {
		$endpoint = WC()->query ? WC()->query->get_current_endpoint() : '';
		return $endpoint ? $endpoint : 'dashboard';
	}

	/**
	 * Body classes.
	 *
	 * @param string[] $classes Classes.
	 * @return string[]
	 */
	public static function body_class( $classes ) {
		if ( is_account_page() ) {
			// The plugin owns the account layout: drop the Samadi theme's own account modifiers.
			$classes   = array_values(
				array_filter(
					$classes,
					static function ( $class ) {
						return 0 !== strpos( $class, 'se-account' );
					}
				)
			);
			$classes[] = 'sad-page';
			if ( is_user_logged_in() ) {
				$classes[] = 'sad-page--' . sanitize_html_class( self::endpoint() );
				if ( sad_option( 'hide_title' ) ) {
					$classes[] = 'sad-hide-title';
				}
			}
		}
		return $classes;
	}

	/**
	 * Stylesheet, script and colour tokens.
	 */
	public static function assets() {
		$load = is_account_page() || is_checkout() || ( sad_option( 'wishlist' ) && ( is_woocommerce() || is_front_page() || is_page() ) );
		if ( ! apply_filters( 'sad_load_assets', $load ) ) {
			return;
		}

		wp_enqueue_style( 'sad-account', SAD_URL . 'assets/css/account.css', array(), SAD_VERSION );
		$primary = sanitize_hex_color( (string) sad_option( 'color_primary' ) );
		$accent  = sanitize_hex_color( (string) sad_option( 'color_accent' ) );
		$css     = ':root{' . ( $primary ? '--sad-primary:' . $primary . ';' : '' ) . ( $accent ? '--sad-accent:' . $accent . ';' : '' ) . '}';
		if ( sad_option( 'font' ) ) {
			$css .= '@font-face{font-family:"SAD Vazirmatn";src:url(' . esc_url( SAD_URL . 'assets/fonts/vazirmatn-arabic-wght-normal.woff2' ) . ') format("woff2");font-weight:100 900;font-display:swap;unicode-range:U+0600-06FF,U+0750-077F,U+200C-200E,U+FB50-FDFF,U+FE70-FEFF}'
				. '@font-face{font-family:"SAD Vazirmatn";src:url(' . esc_url( SAD_URL . 'assets/fonts/vazirmatn-latin-wght-normal.woff2' ) . ') format("woff2");font-weight:100 900;font-display:swap;unicode-range:U+0000-00FF}'
				. '.sad-account,.sad-auth{font-family:"Vazirmatn","SAD Vazirmatn",Tahoma,sans-serif}';
		}
		wp_add_inline_style( 'sad-account', $css );

		wp_enqueue_script( 'sad-account', SAD_URL . 'assets/js/account.js', array(), SAD_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
		wp_localize_script(
			'sad-account',
			'sadAccount',
			array(
				'ajax'     => admin_url( 'admin-ajax.php' ),
				'nonce'    => is_user_logged_in() ? wp_create_nonce( 'sad_wishlist' ) : '',
				'loggedIn' => is_user_logged_in(),
				'loginUrl' => wc_get_page_permalink( 'myaccount' ),
				'i18n'     => array(
					'added'   => 'به علاقه‌مندی‌ها اضافه شد.',
					'removed' => 'از علاقه‌مندی‌ها حذف شد.',
					'confirm' => 'این آدرس حذف شود؟',
				),
			)
		);
	}

	/**
	 * Dashboard statistics (real data only).
	 *
	 * @param int $user_id User.
	 * @return array{orders:int,spent:float,wishlist:int,addresses:int}
	 */
	public static function stats( $user_id ) {
		return array(
			'orders'    => (int) wc_get_customer_order_count( $user_id ),
			'spent'     => (float) wc_get_customer_total_spent( $user_id ),
			'wishlist'  => count( Samadi_Account_Wishlist::items( $user_id ) ),
			'addresses' => count( Samadi_Account_Addresses::all( $user_id ) ),
		);
	}

	/**
	 * Recent orders.
	 *
	 * @param int $user_id User.
	 * @param int $limit   Limit.
	 * @return WC_Order[]
	 */
	public static function recent_orders( $user_id, $limit = 4 ) {
		$orders = wc_get_orders(
			array(
				'customer_id' => $user_id,
				'limit'       => $limit,
				'orderby'     => 'date',
				'order'       => 'DESC',
				'status'      => array_keys( wc_get_order_statuses() ),
			)
		);
		return array_filter(
			$orders,
			static function ( $order ) {
				return $order instanceof WC_Order;
			}
		);
	}

	/**
	 * Thumbnail of an order's first product.
	 *
	 * @param WC_Order $order Order.
	 * @return string
	 */
	public static function order_thumb( $order ) {
		foreach ( $order->get_items() as $item ) {
			$product = is_callable( array( $item, 'get_product' ) ) ? $item->get_product() : null;
			if ( $product && $product->get_image_id() ) {
				return wp_get_attachment_image( $product->get_image_id(), 'woocommerce_gallery_thumbnail', false, array( 'alt' => '' ) );
			}
		}
		return sad_icon( 'package', 22 );
	}
}
