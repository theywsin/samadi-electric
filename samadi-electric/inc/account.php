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
		'orders'          => __( 'سفارش‌های من', 'samadi-electric' ),
		'downloads'       => __( 'دانلودهای من', 'samadi-electric' ),
		'edit-address'    => __( 'آدرس‌های من', 'samadi-electric' ),
		'payment-methods' => __( 'روش‌های پرداخت', 'samadi-electric' ),
		'edit-account'    => __( 'اطلاعات حساب', 'samadi-electric' ),
		'customer-logout' => __( 'خروج از حساب', 'samadi-electric' ),
	);

	foreach ( $labels as $endpoint => $label ) {
		if ( isset( $items[ $endpoint ] ) ) {
			$items[ $endpoint ] = $label;
		}
	}

	// Wishlist (YITH / TI / filter) gets a menu item before the account details.
	if ( samadi_wishlist_url() && ! isset( $items['se-wishlist'] ) ) {
		$with = array();
		foreach ( $items as $endpoint => $label ) {
			if ( 'edit-account' === $endpoint || 'customer-logout' === $endpoint ) {
				$with['se-wishlist'] = __( 'علاقه‌مندی‌ها', 'samadi-electric' );
			}
			$with[ $endpoint ] = $label;
		}
		$items = $with + array( 'se-wishlist' => __( 'علاقه‌مندی‌ها', 'samadi-electric' ) );
	}

	return $items;
}
add_filter( 'woocommerce_account_menu_items', 'samadi_account_menu_items', 5 );

/**
 * The wishlist menu item links to the wishlist plugin's page.
 *
 * @param string $url      Endpoint URL.
 * @param string $endpoint Endpoint.
 * @return string
 */
function samadi_account_wishlist_endpoint_url( $url, $endpoint ) {
	if ( 'se-wishlist' === $endpoint ) {
		$wishlist = samadi_wishlist_url();
		return $wishlist ? $wishlist : $url;
	}

	return $url;
}
add_filter( 'woocommerce_get_endpoint_url', 'samadi_account_wishlist_endpoint_url', 10, 2 );

/**
 * Icon for an account menu endpoint.
 *
 * @param string $endpoint Endpoint key.
 * @return string Icon name.
 */
function samadi_account_menu_icon( $endpoint ) {
	$icons = array(
		'dashboard'       => 'house',
		'orders'          => 'shopping-bag',
		'downloads'       => 'download',
		'edit-address'    => 'map-pin',
		'payment-methods' => 'credit-card',
		'se-wishlist'     => 'heart',
		'edit-account'    => 'user',
		'customer-logout' => 'log-out',
	);

	$icon = isset( $icons[ $endpoint ] ) ? $icons[ $endpoint ] : 'file-text';

	return (string) apply_filters( 'samadi_account_menu_icon', $icon, $endpoint );
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
		'cancelled'      => 'danger',
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
		'<span class="se-status se-status--%1$s">%2$s</span>',
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
 * Product IDs in the current customer's default wishlist (YITH or TI WooCommerce Wishlist).
 *
 * @param int $limit Maximum number of IDs; 0 for all.
 * @return int[]
 */
function samadi_account_wishlist_ids( $limit = 0 ) {
	$ids = array();

	try {
		if ( 'yith' === samadi_wishlist_provider() && class_exists( 'YITH_WCWL_Wishlist_Factory' ) ) {
			$wishlist = YITH_WCWL_Wishlist_Factory::get_default_wishlist();
			if ( $wishlist && is_callable( array( $wishlist, 'get_items' ) ) ) {
				foreach ( $wishlist->get_items() as $item ) {
					$ids[] = (int) $item->get_product_id();
				}
			}
		} elseif ( 'ti' === samadi_wishlist_provider() && class_exists( 'TInvWL_Wishlist' ) && class_exists( 'TInvWL_Product' ) ) {
			$wishlist = ( new TInvWL_Wishlist() )->get_by_user_default();
			$wishlist = is_array( $wishlist ) && isset( $wishlist[0] ) ? $wishlist[0] : $wishlist;
			if ( ! empty( $wishlist['ID'] ) ) {
				foreach ( (array) ( new TInvWL_Product() )->get_wishlist( array( 'wishlist_id' => $wishlist['ID'], 'count' => 999 ) ) as $item ) {
					$ids[] = isset( $item['product_id'] ) ? (int) $item['product_id'] : 0;
				}
			}
		}
	} catch ( Throwable $e ) {
		$ids = array();
	}

	/**
	 * Product IDs shown in the account wishlist panel and counted in the dashboard stats.
	 *
	 * @param int[] $ids Product IDs.
	 */
	$ids = array_values( array_unique( array_filter( array_map( 'absint', (array) apply_filters( 'samadi_account_wishlist_ids', $ids ) ) ) ) );

	return $limit ? array_slice( $ids, 0, $limit ) : $ids;
}

/**
 * Address types shown in the account (same filter WooCommerce's addresses page uses).
 *
 * @return array<string, string> Type => title.
 */
function samadi_account_address_types() {
	$types = array( 'billing' => __( 'صورتحساب', 'samadi-electric' ) );

	if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) {
		$types['shipping'] = __( 'محل ارسال', 'samadi-electric' );
	}

	return apply_filters( 'woocommerce_my_account_get_addresses', $types, get_current_user_id() ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
}

/**
 * One saved address, split into the parts the address cards show.
 *
 * @param string $type Address type (billing, shipping, or a custom type).
 * @return array{lines: string, name: string, phone: string}|null Null when the address is empty.
 */
function samadi_account_address( $type ) {
	$customer = new WC_Customer( get_current_user_id() );
	$get      = static function ( $field ) use ( $customer, $type ) {
		$method = 'get_' . $type . '_' . $field;
		return is_callable( array( $customer, $method ) ) ? (string) $customer->$method() : '';
	};

	if ( ! in_array( $type, array( 'billing', 'shipping' ), true ) ) {
		$formatted = wc_get_account_formatted_address( $type );
		return $formatted ? array(
			'lines' => $formatted,
			'name'  => '',
			'phone' => '',
		) : null;
	}

	$lines = WC()->countries->get_formatted_address(
		array(
			'address_1' => $get( 'address_1' ),
			'address_2' => $get( 'address_2' ),
			'city'      => $get( 'city' ),
			'state'     => $get( 'state' ),
			'postcode'  => $get( 'postcode' ),
			'country'   => $get( 'country' ),
		)
	);
	$name  = trim( $get( 'first_name' ) . ' ' . $get( 'last_name' ) );
	$phone = $get( 'phone' );

	if ( '' === trim( wp_strip_all_tags( $lines ) ) && ! $get( 'address_1' ) ) {
		return null;
	}

	return array(
		'lines' => $lines,
		'name'  => $name,
		'phone' => samadi_option( 'persian_digits' ) ? samadi_fa_digits( $phone ) : $phone,
	);
}

/**
 * Dashboard stat cards.
 *
 * @return array<int, array{key: string, label: string, value: string, icon: string, link: string, url: string}>
 */
function samadi_account_stats() {
	$menu  = wc_get_account_menu_items();
	$stats = array();

	if ( isset( $menu['orders'] ) ) {
		$stats[] = array(
			'key'   => 'orders',
			'label' => __( 'تعداد سفارش‌ها', 'samadi-electric' ),
			'value' => esc_html( samadi_number( samadi_account_order_count() ) ),
			'icon'  => 'shopping-bag',
			'link'  => __( 'مشاهده سفارش‌ها', 'samadi-electric' ),
			'url'   => wc_get_account_endpoint_url( 'orders' ),
		);
		$stats[] = array(
			'key'   => 'spent',
			'label' => __( 'مجموع خریدها', 'samadi-electric' ),
			'value' => wc_price( wc_get_customer_total_spent( get_current_user_id() ) ),
			'icon'  => 'wallet',
			'link'  => __( 'جزئیات مالی', 'samadi-electric' ),
			'url'   => wc_get_account_endpoint_url( 'orders' ),
		);
	}

	if ( samadi_wishlist_url() ) {
		$stats[] = array(
			'key'   => 'wishlist',
			'label' => __( 'علاقه‌مندی‌ها', 'samadi-electric' ),
			'value' => esc_html( samadi_number( count( samadi_account_wishlist_ids() ) ) ),
			'icon'  => 'heart',
			'link'  => __( 'مشاهده موارد محبوب', 'samadi-electric' ),
			'url'   => samadi_wishlist_url(),
		);
	}

	if ( isset( $menu['edit-address'] ) ) {
		$saved = 0;
		foreach ( array_keys( samadi_account_address_types() ) as $type ) {
			$saved += samadi_account_address( $type ) ? 1 : 0;
		}
		$stats[] = array(
			'key'   => 'addresses',
			'label' => __( 'آدرس‌های ثبت شده', 'samadi-electric' ),
			'value' => esc_html( samadi_number( $saved ) ),
			'icon'  => 'map-pin',
			'link'  => __( 'مدیریت آدرس‌ها', 'samadi-electric' ),
			'url'   => wc_get_account_endpoint_url( 'edit-address' ),
		);
	}

	/**
	 * Dashboard stat cards. `value` is HTML (already escaped).
	 *
	 * @param array $stats Stat cards.
	 */
	return apply_filters( 'samadi_account_stats', $stats );
}

/**
 * Banner image behind the dashboard greeting.
 *
 * @return string Image URL; empty for a plain banner.
 */
function samadi_account_banner_image() {
	return (string) apply_filters( 'samadi_account_banner_image', SAMADI_URI . '/assets/images/hero.jpg' );
}

/**
 * "Contact support" link in the account sidebar: a page with the slug contact / contact-us, else a tel: link.
 *
 * @return string Empty when nothing is configured.
 */
function samadi_account_support_url() {
	$url = '';

	foreach ( array( 'contact', 'contact-us', 'تماس-با-ما' ) as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page && 'publish' === $page->post_status ) {
			$url = get_permalink( $page );
			break;
		}
	}

	if ( ! $url ) {
		$phone = samadi_tel( (string) samadi_option( 'contact_phone' ) );
		$url   = $phone ? 'tel:' . $phone : '';
	}

	return (string) apply_filters( 'samadi_account_support_url', $url );
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
		if ( is_user_logged_in() && ! is_wc_endpoint_url() ) {
			$classes[] = 'se-account-dashboard';
		}
	}

	return $classes;
}
add_filter( 'body_class', 'samadi_account_body_class' );
