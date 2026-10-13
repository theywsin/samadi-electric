<?php
/**
 * Built-in wishlist: heart buttons, AJAX toggle, "wishlist" account endpoint.
 *
 * Stored per user in user meta. Guests are sent to the login page. When YITH or TI
 * Wishlist is active the plugin steps aside and links to their page instead.
 *
 * @package Samadi_Account
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wishlist.
 */
class Samadi_Account_Wishlist {

	const ENDPOINT = 'wishlist';
	const META     = '_sad_wishlist';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'endpoint' ) );
		add_filter( 'woocommerce_get_query_vars', array( __CLASS__, 'query_vars' ) );
		add_filter( 'woocommerce_endpoint_' . self::ENDPOINT . '_title', array( __CLASS__, 'title' ) );
		add_action( 'woocommerce_account_' . self::ENDPOINT . '_endpoint', array( __CLASS__, 'render' ) );
		add_filter( 'woocommerce_get_endpoint_url', array( __CLASS__, 'external_url' ), 10, 2 );

		if ( ! self::enabled() ) {
			return;
		}

		add_action( 'wp_ajax_sad_wishlist_toggle', array( __CLASS__, 'ajax_toggle' ) );

		// Samadi Electric theme: register as its wishlist provider (cards + bottom nav + header icon).
		add_filter( 'samadi_wishlist_provider', array( __CLASS__, 'theme_provider' ) );
		add_filter( 'samadi_wishlist_url', array( __CLASS__, 'url' ) );
		add_filter( 'samadi_wishlist_button', array( __CLASS__, 'theme_button' ), 10, 2 );

		// Other themes: heart on product cards and the product page.
		if ( ! self::is_samadi_theme() ) {
			add_action( 'woocommerce_before_shop_loop_item', array( __CLASS__, 'loop_button' ), 5 );
			add_action( 'woocommerce_after_add_to_cart_form', array( __CLASS__, 'single_button' ) );
		}
	}

	/**
	 * Whether the built-in wishlist is active (setting on and no other wishlist plugin).
	 *
	 * @return bool
	 */
	public static function enabled() {
		return sad_option( 'wishlist' ) && ! self::third_party();
	}

	/**
	 * Third-party wishlist plugin present.
	 *
	 * @return bool
	 */
	public static function third_party() {
		return ( defined( 'YITH_WCWL' ) && function_exists( 'yith_wcwl_get_wishlist_url' ) ) || defined( 'TINVWL_FVERSION' ) || defined( 'TINVWL_VERSION' );
	}

	/**
	 * Samadi Electric theme active.
	 *
	 * @return bool
	 */
	private static function is_samadi_theme() {
		return function_exists( 'samadi_wishlist_provider' );
	}

	/**
	 * Register the endpoint.
	 */
	public static function endpoint() {
		add_rewrite_endpoint( self::ENDPOINT, EP_ROOT | EP_PAGES );
	}

	/**
	 * WooCommerce query vars.
	 *
	 * @param array $vars Vars.
	 * @return array
	 */
	public static function query_vars( $vars ) {
		$vars[ self::ENDPOINT ] = self::ENDPOINT;
		return $vars;
	}

	/**
	 * Endpoint title.
	 *
	 * @return string
	 */
	public static function title() {
		return 'علاقه‌مندی‌های من';
	}

	/**
	 * Menu link goes to the third-party wishlist page when one is active.
	 *
	 * @param string $url      URL.
	 * @param string $endpoint Endpoint.
	 * @return string
	 */
	public static function external_url( $url, $endpoint ) {
		if ( self::ENDPOINT === $endpoint && self::third_party() ) {
			if ( function_exists( 'yith_wcwl_get_wishlist_url' ) ) {
				return (string) yith_wcwl_get_wishlist_url();
			}
			if ( function_exists( 'tinv_url_wishlist_default' ) ) {
				return (string) tinv_url_wishlist_default();
			}
		}
		return $url;
	}

	/**
	 * Wishlist URL.
	 *
	 * @return string
	 */
	public static function url() {
		return wc_get_account_endpoint_url( self::ENDPOINT );
	}

	/**
	 * Product IDs in a user's wishlist (published products only, newest first).
	 *
	 * @param int $user_id User.
	 * @return int[]
	 */
	public static function items( $user_id ) {
		if ( ! $user_id || ! self::enabled() ) {
			return array();
		}
		$ids = get_user_meta( $user_id, self::META, true );
		$ids = is_array( $ids ) ? array_map( 'absint', $ids ) : array();

		return array_values(
			array_filter(
				$ids,
				static function ( $id ) {
					return 'publish' === get_post_status( $id );
				}
			)
		);
	}

	/**
	 * Whether a product is in the current user's wishlist.
	 *
	 * @param int $product_id Product.
	 * @return bool
	 */
	public static function has( $product_id ) {
		return in_array( (int) $product_id, self::items( get_current_user_id() ), true );
	}

	/**
	 * Toggle a product for a user.
	 *
	 * @param int $user_id    User.
	 * @param int $product_id Product.
	 * @return bool New state (true = in wishlist).
	 */
	public static function toggle( $user_id, $product_id ) {
		$ids = self::items( $user_id );
		if ( in_array( $product_id, $ids, true ) ) {
			$ids   = array_values( array_diff( $ids, array( $product_id ) ) );
			$state = false;
		} else {
			array_unshift( $ids, $product_id );
			$ids   = array_slice( $ids, 0, 200 );
			$state = true;
		}
		update_user_meta( $user_id, self::META, $ids );
		return $state;
	}

	/**
	 * AJAX toggle.
	 */
	public static function ajax_toggle() {
		check_ajax_referer( 'sad_wishlist', 'nonce' );
		$product_id = isset( $_POST['product'] ) ? absint( $_POST['product'] ) : 0;
		$product    = $product_id ? wc_get_product( $product_id ) : null;
		if ( ! $product || 'publish' !== get_post_status( $product_id ) ) {
			wp_send_json_error( array( 'message' => 'محصول نامعتبر است.' ), 400 );
		}
		$state = self::toggle( get_current_user_id(), $product_id );
		wp_send_json_success(
			array(
				'active' => $state,
				'count'  => count( self::items( get_current_user_id() ) ),
			)
		);
	}

	/**
	 * Heart button markup.
	 *
	 * @param int    $product_id Product.
	 * @param string $context    loop|single|card.
	 * @return string
	 */
	public static function button( $product_id, $context = 'loop' ) {
		$active = is_user_logged_in() && self::has( $product_id );
		$label  = $active ? 'حذف از علاقه‌مندی‌ها' : 'افزودن به علاقه‌مندی‌ها';

		return sprintf(
			'<button type="button" class="sad-wish sad-wish--%1$s%2$s" data-sad-wish="%3$d" aria-pressed="%4$s" aria-label="%5$s" title="%5$s">%6$s</button>',
			esc_attr( $context ),
			$active ? ' is-active' : '',
			absint( $product_id ),
			$active ? 'true' : 'false',
			esc_attr( $label ),
			sad_icon( 'heart', 'single' === $context ? 22 : 18 )
		);
	}

	/**
	 * Theme provider slug.
	 *
	 * @param string $provider Provider.
	 * @return string
	 */
	public static function theme_provider( $provider ) {
		return $provider ? $provider : 'samadi-account';
	}

	/**
	 * Theme wishlist button.
	 *
	 * @param string $html       Existing markup.
	 * @param int    $product_id Product.
	 * @return string
	 */
	public static function theme_button( $html, $product_id ) {
		return '' !== $html ? $html : self::button( $product_id, 'card' );
	}

	/**
	 * Loop heart (non-Samadi themes).
	 */
	public static function loop_button() {
		global $product;
		if ( $product instanceof WC_Product ) {
			echo self::button( $product->get_id(), 'loop' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in button().
		}
	}

	/**
	 * Product page heart (non-Samadi themes).
	 */
	public static function single_button() {
		global $product;
		if ( $product instanceof WC_Product ) {
			echo '<div class="sad-wish-single">' . self::button( $product->get_id(), 'single' ) . '<span>افزودن به علاقه‌مندی‌ها</span></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in button().
		}
	}

	/**
	 * Account endpoint.
	 */
	public static function render() {
		$ids = self::items( get_current_user_id() );
		sad_template( 'myaccount/wishlist', array( 'ids' => $ids ) );
	}
}
