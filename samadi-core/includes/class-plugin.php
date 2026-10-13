<?php
/**
 * Plugin loader.
 *
 * @package Samadi_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 */
final class Samadi_Core_Plugin {

	/**
	 * Module key => class file / class name.
	 *
	 * @var array<string, string>
	 */
	const MODULES = array(
		'wishlist'   => 'Samadi_Core_Wishlist',
		'compare'    => 'Samadi_Core_Compare',
		'newsletter' => 'Samadi_Core_Newsletter',
		'jalali'     => 'Samadi_Core_Jalali_Dates',
		'checkout'   => 'Samadi_Core_Checkout',
		'schema'     => 'Samadi_Core_Schema',
		'security'   => 'Samadi_Core_Security',
	);

	/**
	 * Instance.
	 *
	 * @var Samadi_Core_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Singleton.
	 *
	 * @return Samadi_Core_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Boot.
	 */
	private function __construct() {
		load_plugin_textdomain( 'samadi-core', false, dirname( plugin_basename( SAMADI_CORE_FILE ) ) . '/languages' );

		require_once SAMADI_CORE_DIR . 'includes/class-settings.php';
		Samadi_Core_Settings::init();

		// The newsletter table is needed even when the module is off (admin list, uninstall).
		require_once SAMADI_CORE_DIR . 'includes/modules/class-newsletter.php';
		add_action( 'init', array( 'Samadi_Core_Newsletter', 'maybe_upgrade' ), 5 );
		if ( is_admin() ) {
			Samadi_Core_Newsletter::admin_init();
		}

		foreach ( self::MODULES as $module => $class ) {
			if ( ! samadi_core_module_enabled( $module ) ) {
				continue;
			}
			require_once SAMADI_CORE_DIR . 'includes/modules/class-' . $module . '.php';
			call_user_func( array( $class, 'init' ) );
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ), 5 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 20 );

		if ( class_exists( 'WooCommerce' ) ) {
			add_action( 'woocommerce_single_product_summary', array( $this, 'single_actions' ), 31 );
			// The Samadi theme renders card buttons in its own slot (samadi_wishlist_button); other themes get them after the card.
			add_action( 'woocommerce_after_shop_loop_item', array( $this, 'card_actions' ), 15 );
		}
	}

	/**
	 * Wishlist / compare buttons under the add-to-cart form.
	 */
	public function single_actions() {
		global $product;

		if ( ! $product instanceof WC_Product ) {
			return;
		}

		/**
		 * Buttons shown under the add-to-cart form (key => HTML).
		 *
		 * @param string[]   $buttons Buttons.
		 * @param WC_Product $product Product.
		 */
		$buttons = array_filter( (array) apply_filters( 'samadi_core_single_buttons', array(), $product ) );

		if ( $buttons ) {
			echo '<div class="se-core-actions">' . implode( '', $buttons ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built with escaped parts.
		}
	}

	/**
	 * Card buttons for themes without the Samadi card slot.
	 */
	public function card_actions() {
		global $product;

		if ( function_exists( 'samadi_wishlist_button' ) || ! $product instanceof WC_Product ) {
			return;
		}

		/**
		 * Icon buttons on product cards (key => HTML), for themes other than Samadi Electric.
		 *
		 * @param string[] $buttons    Buttons.
		 * @param int      $product_id Product ID.
		 */
		$buttons = array_filter( (array) apply_filters( 'samadi_core_card_buttons', array(), $product->get_id() ) );

		if ( $buttons ) {
			echo '<div class="se-core-card-actions se-core-card-actions--loop">' . implode( '', $buttons ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built with escaped parts.
		}
	}

	/**
	 * Front-end assets. Modules add their data to `samadiCore` through the samadi_core_script_data filter.
	 */
	public function register_assets() {
		wp_register_style( 'samadi-core', SAMADI_CORE_URL . 'assets/css/core.css', array(), SAMADI_CORE_VERSION );
		wp_register_script( 'samadi-core', SAMADI_CORE_URL . 'assets/js/core.js', array(), SAMADI_CORE_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}

	/**
	 * Wishlist and compare buttons appear on product cards on any page, so the (small) bundle loads site-wide
	 * whenever a module that needs it is on.
	 */
	public function enqueue_assets() {
		$needed = samadi_core_module_enabled( 'wishlist' ) || samadi_core_module_enabled( 'compare' ) || samadi_core_module_enabled( 'newsletter' );

		if ( ! $needed ) {
			return;
		}

		wp_enqueue_style( 'samadi-core' );
		wp_enqueue_script( 'samadi-core' );

		/**
		 * Data passed to the front-end script.
		 *
		 * @param array $data Data.
		 */
		$data = apply_filters(
			'samadi_core_script_data',
			array(
				'rest'          => esc_url_raw( rest_url( 'samadi-core/v1/' ) ),
				'nonce'         => is_user_logged_in() ? wp_create_nonce( 'wp_rest' ) : '',
				'persianDigits' => samadi_core_persian_digits(),
				'cookiePath'    => COOKIEPATH ? COOKIEPATH : '/',
				'secure'        => is_ssl(),
				'i18n'          => array(
					'error' => __( 'خطایی رخ داد. لطفاً دوباره تلاش کنید.', 'samadi-core' ),
				),
			)
		);

		wp_localize_script( 'samadi-core', 'samadiCore', $data );
	}

	/**
	 * Activation: create the subscribers table and the wishlist / compare pages.
	 */
	public static function activate() {
		require_once SAMADI_CORE_DIR . 'includes/modules/class-newsletter.php';
		Samadi_Core_Newsletter::install();

		$settings = get_option( SAMADI_CORE_OPTION, array() );
		$settings = is_array( $settings ) ? $settings : array();

		$pages = array(
			'wishlist_page' => array( __( 'علاقه‌مندی‌ها', 'samadi-core' ), 'wishlist', '[samadi_wishlist]' ),
			'compare_page'  => array( __( 'مقایسه محصولات', 'samadi-core' ), 'compare', '[samadi_compare]' ),
		);

		foreach ( $pages as $key => $page ) {
			if ( ! empty( $settings[ $key ] ) && 'page' === get_post_type( (int) $settings[ $key ] ) && 'trash' !== get_post_status( (int) $settings[ $key ] ) ) {
				continue;
			}

			$existing = get_page_by_path( $page[1] );
			if ( $existing && false !== strpos( (string) $existing->post_content, $page[2] ) ) {
				$settings[ $key ] = (int) $existing->ID;
				continue;
			}

			$id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $page[0],
					'post_name'    => $page[1],
					'post_content' => '<!-- wp:shortcode -->' . $page[2] . '<!-- /wp:shortcode -->',
				)
			);

			if ( $id && ! is_wp_error( $id ) ) {
				$settings[ $key ] = (int) $id;
			}
		}

		update_option( SAMADI_CORE_OPTION, $settings );
	}
}
