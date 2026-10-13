<?php
/**
 * Plugin Name:       Samadi Account Dashboard
 * Plugin URI:        https://samadielectric.ir/
 * Description:       طراحی کامل و اختصاصی «حساب کاربری من» ووکامرس: پیشخوان با بنر خوشامد و آمار، آخرین سفارش‌ها، دفترچه آدرس چندگانه، علاقه‌مندی‌ها، اطلاعات حساب، عکس پروفایل، تاریخ شمسی و منوی آیکون‌دار. A complete, custom WooCommerce My Account design (RTL / Persian).
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * Author:            Samadi Electric
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       samadi-account
 * Domain Path:       /languages
 * WC requires at least: 9.0
 * WC tested up to:   11.2
 *
 * @package Samadi_Account
 */

defined( 'ABSPATH' ) || exit;

define( 'SAD_VERSION', '1.0.0' );
define( 'SAD_FILE', __FILE__ );
define( 'SAD_DIR', plugin_dir_path( __FILE__ ) );
define( 'SAD_URL', plugin_dir_url( __FILE__ ) );

require_once SAD_DIR . 'includes/functions.php';
require_once SAD_DIR . 'includes/class-plugin.php';
require_once SAD_DIR . 'includes/class-wishlist.php';
require_once SAD_DIR . 'includes/class-addresses.php';
require_once SAD_DIR . 'includes/class-profile.php';
require_once SAD_DIR . 'includes/class-settings.php';

add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', SAD_FILE, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', SAD_FILE, true );
		}
	}
);

add_action(
	'plugins_loaded',
	static function () {
		load_plugin_textdomain( 'samadi-account', false, dirname( plugin_basename( SAD_FILE ) ) . '/languages' );
		Samadi_Account_Settings::init();
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		Samadi_Account::init();
		Samadi_Account_Wishlist::init();
		Samadi_Account_Addresses::init();
		Samadi_Account_Profile::init();
	}
);

register_activation_hook(
	__FILE__,
	static function () {
		add_rewrite_endpoint( Samadi_Account_Wishlist::ENDPOINT, EP_ROOT | EP_PAGES );
		flush_rewrite_rules();
	}
);

register_deactivation_hook(
	__FILE__,
	static function () {
		flush_rewrite_rules();
	}
);
