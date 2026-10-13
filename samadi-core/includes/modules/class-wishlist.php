<?php
/**
 * Wishlist (علاقه‌مندی‌ها).
 *
 * Plugs into the theme's wishlist hooks (samadi_wishlist_provider / _url / _button and
 * samadi_account_wishlist_ids), so the card heart, header icon, bottom-nav item and the My Account
 * wishlist panel all work without YITH or TI. When one of those plugins is active, the theme
 * uses it and this module stays out of the way.
 *
 * Storage: user meta for signed-in customers, a cookie for guests. The cookie always mirrors
 * the current list so the front-end script can mark buttons on cached pages; on login the guest
 * list is merged into the account.
 *
 * @package Samadi_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wishlist module.
 */
final class Samadi_Core_Wishlist {

	/**
	 * User meta key.
	 */
	const META = 'samadi_wishlist';

	/**
	 * Cookie name.
	 */
	const COOKIE = 'samadi_wishlist';

	/**
	 * Maximum number of products in a list.
	 */
	const MAX = 100;

	/**
	 * Hooks.
	 */
	public static function init() {
		if ( self::other_plugin() ) {
			return;
		}

		add_filter( 'samadi_wishlist_provider', array( __CLASS__, 'provider' ) );
		add_filter( 'samadi_wishlist_url', array( __CLASS__, 'filter_url' ) );
		add_filter( 'samadi_wishlist_button', array( __CLASS__, 'filter_button' ), 10, 2 );
		add_filter( 'samadi_account_wishlist_ids', array( __CLASS__, 'filter_account_ids' ) );
		add_filter( 'samadi_bottom_nav_items', array( __CLASS__, 'bottom_nav_active' ) );
		add_filter( 'samadi_core_single_buttons', array( __CLASS__, 'single_button' ), 10, 2 );
		add_filter( 'samadi_core_card_buttons', array( __CLASS__, 'card_button' ), 10, 2 );
		add_filter( 'samadi_core_script_data', array( __CLASS__, 'script_data' ) );

		add_shortcode( 'samadi_wishlist', array( __CLASS__, 'shortcode' ) );

		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
		add_action( 'wp_login', array( __CLASS__, 'merge_on_login' ), 10, 2 );
		add_action( 'template_redirect', array( __CLASS__, 'template_redirect' ) );
	}

	/**
	 * Whether YITH or TI WooCommerce Wishlist is active.
	 *
	 * @return bool
	 */
	public static function other_plugin() {
		return defined( 'YITH_WCWL' ) || defined( 'TINVWL_FVERSION' ) || defined( 'TINVWL_VERSION' ) || function_exists( 'tinv_url_wishlist_default' );
	}

	/**
	 * Product IDs in the current visitor's list.
	 *
	 * @return int[]
	 */
	public static function ids() {
		$user_id = get_current_user_id();

		if ( $user_id ) {
			$ids = get_user_meta( $user_id, self::META, true );
			$ids = is_array( $ids ) ? $ids : array();
		} else {
			$ids = samadi_core_cookie_ids( self::COOKIE, self::MAX );
		}

		return array_slice( array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) ), 0, self::MAX );
	}

	/**
	 * Save the current visitor's list (and mirror it in the cookie).
	 *
	 * @param int[] $ids Product IDs, newest first.
	 */
	public static function save( $ids ) {
		$ids     = array_slice( array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) ), 0, self::MAX );
		$user_id = get_current_user_id();

		if ( $user_id ) {
			update_user_meta( $user_id, self::META, $ids );
		}

		samadi_core_set_cookie_ids( self::COOKIE, $ids );

		/**
		 * The visitor's wishlist changed.
		 *
		 * @param int[] $ids     Product IDs.
		 * @param int   $user_id User ID (0 for guests).
		 */
		do_action( 'samadi_core_wishlist_saved', $ids, $user_id );
	}

	/**
	 * Whether a product is in the current visitor's list.
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public static function has( $product_id ) {
		return in_array( absint( $product_id ), self::ids(), true );
	}

	/**
	 * Wishlist page URL.
	 *
	 * @return string
	 */
	public static function url() {
		$page = (int) samadi_core_setting( 'wishlist_page' );

		return $page && 'publish' === get_post_status( $page ) ? (string) get_permalink( $page ) : '';
	}

	/**
	 * Theme hook: provider name.
	 *
	 * @param string $provider Provider found so far.
	 * @return string
	 */
	public static function provider( $provider ) {
		return $provider ? $provider : 'samadi';
	}

	/**
	 * Theme hook: wishlist page URL.
	 *
	 * @param string $url URL found so far.
	 * @return string
	 */
	public static function filter_url( $url ) {
		return $url ? $url : self::url();
	}

	/**
	 * Theme hook: card button. The compare module appends its own button on a later priority.
	 *
	 * @param string $html       Markup from another provider.
	 * @param int    $product_id Product ID.
	 * @return string
	 */
	public static function filter_button( $html, $product_id ) {
		return 'samadi' === samadi_wishlist_provider() ? self::button( $product_id ) : $html;
	}

	/**
	 * Card buttons for themes without the Samadi card slot.
	 *
	 * @param string[] $buttons    Buttons.
	 * @param int      $product_id Product ID.
	 * @return string[]
	 */
	public static function card_button( $buttons, $product_id ) {
		$buttons['wishlist'] = self::button( $product_id );

		return $buttons;
	}

	/**
	 * Theme hook: product IDs for the My Account wishlist panel and stats.
	 *
	 * @param int[] $ids IDs from another provider.
	 * @return int[]
	 */
	public static function filter_account_ids( $ids ) {
		return $ids ? $ids : self::ids();
	}

	/**
	 * Mark the bottom-nav wishlist item as current on the wishlist page.
	 *
	 * @param array $items Items.
	 * @return array
	 */
	public static function bottom_nav_active( $items ) {
		$page = (int) samadi_core_setting( 'wishlist_page' );

		if ( isset( $items['wishlist'] ) && $page && is_page( $page ) ) {
			$items['wishlist']['active'] = true;
		}

		return $items;
	}

	/**
	 * Icon button (product cards).
	 *
	 * @param int $product_id Product ID.
	 * @return string
	 */
	public static function button( $product_id ) {
		$product_id = absint( $product_id );
		$active     = self::has( $product_id );

		return sprintf(
			'<button type="button" class="se-core-wish%1$s" data-se-wish="%2$d" aria-pressed="%3$s" aria-label="%4$s" title="%4$s" data-label-add="%5$s" data-label-remove="%6$s">%7$s</button>',
			$active ? ' is-active' : '',
			$product_id,
			$active ? 'true' : 'false',
			esc_attr( $active ? __( 'حذف از علاقه‌مندی‌ها', 'samadi-core' ) : __( 'افزودن به علاقه‌مندی‌ها', 'samadi-core' ) ),
			esc_attr__( 'افزودن به علاقه‌مندی‌ها', 'samadi-core' ),
			esc_attr__( 'حذف از علاقه‌مندی‌ها', 'samadi-core' ),
			samadi_core_icon( 'heart', 20 )
		);
	}

	/**
	 * Wide button on the single product page.
	 *
	 * @param string[]   $buttons Buttons.
	 * @param WC_Product $product Product.
	 * @return string[]
	 */
	public static function single_button( $buttons, $product ) {
		if ( ! samadi_core_setting( 'wishlist_single' ) ) {
			return $buttons;
		}

		$id     = $product->get_id();
		$active = self::has( $id );

		$buttons['wishlist'] = sprintf(
			'<button type="button" class="se-core-action se-core-wish%1$s" data-se-wish="%2$d" aria-pressed="%3$s" data-label-add="%4$s" data-label-remove="%5$s">%6$s<span class="se-core-action__label">%7$s</span></button>',
			$active ? ' is-active' : '',
			$id,
			$active ? 'true' : 'false',
			esc_attr__( 'افزودن به علاقه‌مندی‌ها', 'samadi-core' ),
			esc_attr__( 'در علاقه‌مندی‌ها', 'samadi-core' ),
			samadi_core_icon( 'heart', 20 ),
			esc_html( $active ? __( 'در علاقه‌مندی‌ها', 'samadi-core' ) : __( 'افزودن به علاقه‌مندی‌ها', 'samadi-core' ) )
		);

		return $buttons;
	}

	/**
	 * Front-end data.
	 *
	 * @param array $data Data.
	 * @return array
	 */
	public static function script_data( $data ) {
		$data['wishlist'] = array(
			'cookie' => self::COOKIE,
			'url'    => self::url(),
			'i18n'   => array(
				'added'   => __( 'به علاقه‌مندی‌ها اضافه شد.', 'samadi-core' ),
				'removed' => __( 'از علاقه‌مندی‌ها حذف شد.', 'samadi-core' ),
				'view'    => __( 'مشاهده', 'samadi-core' ),
			),
		);

		return $data;
	}

	/**
	 * Wishlist / signed-in housekeeping on front-end page loads.
	 */
	public static function template_redirect() {
		$page = (int) samadi_core_setting( 'wishlist_page' );

		if ( $page && is_page( $page ) ) {
			samadi_core_no_page_cache();
		}

		// Keep the cookie in step with the account list (another device may have changed it).
		if ( is_user_logged_in() ) {
			$ids    = self::ids();
			$cookie = samadi_core_cookie_ids( self::COOKIE, self::MAX );
			if ( $ids !== $cookie ) {
				samadi_core_set_cookie_ids( self::COOKIE, $ids );
			}
		}
	}

	/**
	 * Merge the guest list into the account on login.
	 *
	 * @param string  $login User login.
	 * @param WP_User $user  User.
	 */
	public static function merge_on_login( $login, $user ) {
		$guest = samadi_core_cookie_ids( self::COOKIE, self::MAX );

		if ( ! $guest || ! $user instanceof WP_User ) {
			return;
		}

		$saved = get_user_meta( $user->ID, self::META, true );
		$saved = is_array( $saved ) ? array_map( 'absint', $saved ) : array();
		$ids   = array_slice( array_values( array_unique( array_merge( $guest, $saved ) ) ), 0, self::MAX );

		update_user_meta( $user->ID, self::META, $ids );
		samadi_core_set_cookie_ids( self::COOKIE, $ids );
	}

	/**
	 * REST routes.
	 */
	public static function routes() {
		register_rest_route(
			'samadi-core/v1',
			'/wishlist',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( __CLASS__, 'rest_get' ),
					'permission_callback' => '__return_true',
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( __CLASS__, 'rest_update' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'product_id' => array(
							'type'              => 'integer',
							'required'          => true,
							'sanitize_callback' => 'absint',
						),
						'action'     => array(
							'type'    => 'string',
							'enum'    => array( 'toggle', 'add', 'remove' ),
							'default' => 'toggle',
						),
					),
				),
			)
		);
	}

	/**
	 * GET /wishlist.
	 *
	 * @return WP_REST_Response
	 */
	public static function rest_get() {
		$response = rest_ensure_response( self::response_data( false ) );
		$response->header( 'Cache-Control', 'no-store' );

		return $response;
	}

	/**
	 * POST /wishlist — add, remove or toggle one product.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function rest_update( $request ) {
		$product_id = (int) $request['product_id'];
		$action     = (string) $request['action'];
		$ids        = self::ids();
		$in_list    = in_array( $product_id, $ids, true );
		$add        = 'add' === $action || ( 'toggle' === $action && ! $in_list );

		if ( $add && ! samadi_core_visible_product( $product_id ) ) {
			return new WP_Error( 'samadi_core_invalid_product', __( 'این محصول در دسترس نیست.', 'samadi-core' ), array( 'status' => 404 ) );
		}

		if ( $add && ! $in_list ) {
			array_unshift( $ids, $product_id );
		} elseif ( ! $add ) {
			$ids = array_values( array_diff( $ids, array( $product_id ) ) );
		}

		self::save( $ids );

		$response = rest_ensure_response( self::response_data( $add ) );
		$response->header( 'Cache-Control', 'no-store' );

		return $response;
	}

	/**
	 * REST payload.
	 *
	 * @param bool $added Whether the last change added a product.
	 * @return array
	 */
	private static function response_data( $added ) {
		$ids = self::ids();

		return array(
			'ids'   => $ids,
			'count' => count( $ids ),
			'added' => (bool) $added,
		);
	}

	/**
	 * [samadi_wishlist] — the visitor's saved products as a product grid.
	 *
	 * @return string
	 */
	public static function shortcode() {
		$products = array_values( array_filter( array_map( 'samadi_core_visible_product', self::ids() ) ) );

		return samadi_core_template(
			'wishlist',
			array(
				'products' => $products,
			)
		);
	}
}
