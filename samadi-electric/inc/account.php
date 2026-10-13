<?php
/**
 * WooCommerce My Account: menu labels/icons, order status tones, dashboard data.
 *
 * Templates live in woocommerce/myaccount/ and template-parts/account/. They keep every
 * WooCommerce hook and filter; this file only supplies presentation helpers.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

/**
 * Persian labels for WooCommerce's core account endpoints. Custom endpoints keep their own label.
 *
 * @param array $items Endpoint => label.
 * @return array
 */
function samadi_account_menu_items( $items ) {
	$labels = array(
		'dashboard'       => __( 'پیشخوان', 'samadi-electric' ),
		'orders'          => __( 'سفارش‌ها', 'samadi-electric' ),
		'downloads'       => __( 'دانلودها', 'samadi-electric' ),
		'edit-address'    => __( 'آدرس‌ها', 'samadi-electric' ),
		'payment-methods' => __( 'روش‌های پرداخت', 'samadi-electric' ),
		'edit-account'    => __( 'جزئیات حساب', 'samadi-electric' ),
		'customer-logout' => __( 'خروج از حساب', 'samadi-electric' ),
	);

	foreach ( $labels as $endpoint => $label ) {
		if ( isset( $items[ $endpoint ] ) ) {
			$items[ $endpoint ] = $label;
		}
	}

	return $items;
}
add_filter( 'woocommerce_account_menu_items', 'samadi_account_menu_items', 5 );

/**
 * Icon for an account menu endpoint.
 *
 * @param string $endpoint Endpoint key.
 * @return string Icon name.
 */
function samadi_account_menu_icon( $endpoint ) {
	$icons = array(
		'dashboard'       => 'layout-dashboard',
		'orders'          => 'package',
		'downloads'       => 'download',
		'edit-address'    => 'map-pin',
		'payment-methods' => 'credit-card',
		'edit-account'    => 'user-pen',
		'customer-logout' => 'log-out',
		'wishlist'        => 'heart',
	);

	$icon = isset( $icons[ $endpoint ] ) ? $icons[ $endpoint ] : 'file-text';

	return (string) apply_filters( 'samadi_account_menu_icon', $icon, $endpoint );
}

/**
 * Short description for an endpoint, used by the dashboard shortcut tiles.
 *
 * @param string $endpoint Endpoint key.
 * @return string
 */
function samadi_account_menu_description( $endpoint ) {
	$descriptions = array(
		'orders'          => __( 'پیگیری وضعیت و جزئیات سفارش‌ها', 'samadi-electric' ),
		'downloads'       => __( 'فایل‌های قابل دانلود خریدهای شما', 'samadi-electric' ),
		'edit-address'    => __( 'آدرس صورتحساب و ارسال', 'samadi-electric' ),
		'payment-methods' => __( 'کارت‌ها و روش‌های پرداخت ذخیره‌شده', 'samadi-electric' ),
		'edit-account'    => __( 'نام، ایمیل و تغییر رمز عبور', 'samadi-electric' ),
	);

	return isset( $descriptions[ $endpoint ] ) ? $descriptions[ $endpoint ] : '';
}

/**
 * Visual tone of an order status (drives the badge color).
 *
 * @param string $status Order status without the wc- prefix.
 * @return string success|info|warning|danger|muted
 */
function samadi_account_status_tone( $status ) {
	$tones = array(
		'completed'      => 'success',
		'processing'     => 'info',
		'on-hold'        => 'warning',
		'pending'        => 'warning',
		'checkout-draft' => 'muted',
		'cancelled'      => 'muted',
		'refunded'       => 'muted',
		'failed'         => 'danger',
	);

	$tone = isset( $tones[ $status ] ) ? $tones[ $status ] : 'info';

	return (string) apply_filters( 'samadi_account_status_tone', $tone, $status );
}

/**
 * Order status badge markup.
 *
 * @param WC_Order $order Order.
 * @return string
 */
function samadi_account_status_badge( $order ) {
	$status = $order->get_status();

	return sprintf(
		'<span class="se-status se-status--%1$s"><span class="se-status__dot" aria-hidden="true"></span>%2$s</span>',
		esc_attr( samadi_account_status_tone( $status ) ),
		esc_html( wc_get_order_status_name( $status ) )
	);
}

/**
 * Number of orders of the current customer, optionally limited to some statuses.
 *
 * @param string[] $statuses Statuses without the wc- prefix; empty for all visible statuses.
 * @return int
 */
function samadi_account_order_count( $statuses = array() ) {
	$customer_id = get_current_user_id();

	if ( ! $customer_id ) {
		return 0;
	}

	if ( ! $statuses ) {
		$statuses = array_map(
			static function ( $status ) {
				return 'wc-' === substr( $status, 0, 3 ) ? substr( $status, 3 ) : $status;
			},
			array_keys( wc_get_order_statuses() )
		);
		$statuses = array_values( array_diff( $statuses, array( 'checkout-draft' ) ) );
	}

	$result = wc_get_orders(
		array(
			'customer_id' => $customer_id,
			'status'      => $statuses,
			'type'        => 'shop_order',
			'limit'       => 1,
			'return'      => 'ids',
			'paginate'    => true,
		)
	);

	return is_object( $result ) && isset( $result->total ) ? (int) $result->total : 0;
}

/**
 * Dashboard stat tiles.
 *
 * @return array<int, array{label: string, value: int, icon: string, tone: string}>
 */
function samadi_account_stats() {
	$stats = array(
		array(
			'label' => __( 'همه سفارش‌ها', 'samadi-electric' ),
			'value' => samadi_account_order_count(),
			'icon'  => 'package',
			'tone'  => 'brand',
		),
		array(
			'label' => __( 'در حال انجام', 'samadi-electric' ),
			'value' => samadi_account_order_count( array( 'pending', 'on-hold', 'processing' ) ),
			'icon'  => 'clock',
			'tone'  => 'warning',
		),
		array(
			'label' => __( 'تحویل‌شده', 'samadi-electric' ),
			'value' => samadi_account_order_count( array( 'completed' ) ),
			'icon'  => 'check',
			'tone'  => 'success',
		),
	);

	return apply_filters( 'samadi_account_stats', $stats );
}

/**
 * Most recent orders of the current customer (same query args WooCommerce's orders endpoint uses).
 *
 * @param int $limit Number of orders.
 * @return WC_Order[]
 */
function samadi_account_recent_orders( $limit = 3 ) {
	$customer_id = get_current_user_id();

	if ( ! $customer_id ) {
		return array();
	}

	$orders = wc_get_orders(
		apply_filters(
			'woocommerce_my_account_my_orders_query',
			array(
				'customer' => $customer_id,
				'page'     => 1,
				'paginate' => false,
				'limit'    => $limit,
			)
		)
	);

	if ( ! is_array( $orders ) ) {
		return array();
	}

	return array_values(
		array_filter(
			$orders,
			static function ( $order ) {
				return $order instanceof WC_Order;
			}
		)
	);
}

/**
 * Account page body class (lets the page header adapt when signed in).
 *
 * @param string[] $classes Classes.
 * @return string[]
 */
function samadi_account_body_class( $classes ) {
	if ( is_account_page() ) {
		$classes[] = is_user_logged_in() ? 'se-account' : 'se-account-guest';
	}

	return $classes;
}
add_filter( 'body_class', 'samadi_account_body_class' );
