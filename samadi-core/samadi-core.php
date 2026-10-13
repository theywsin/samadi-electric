<?php
/**
 * Plugin Name:       Samadi Core
 * Plugin URI:        https://samadielectric.ir/
 * Description:       هسته قالب صمدی الکتریک: راه‌انداز یک‌کلیکی (نصب WooCommerce، Elementor و افزونه همراه) و درون‌ریزی کامل دموی فروشگاه با همه صفحات، محصولات، منوها و تنظیمات. Core plugin for the Samadi Electric theme: setup wizard and full demo import.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Samadi Electric
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       samadi-core
 * Domain Path:       /languages
 *
 * @package Samadi_Core
 */

defined( 'ABSPATH' ) || exit;

define( 'SAMADI_CORE_VERSION', '1.0.0' );
define( 'SAMADI_CORE_FILE', __FILE__ );
define( 'SAMADI_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'SAMADI_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once SAMADI_CORE_DIR . 'includes/class-plugins.php';
require_once SAMADI_CORE_DIR . 'includes/class-demo-importer.php';
require_once SAMADI_CORE_DIR . 'includes/class-demo-pages.php';
require_once SAMADI_CORE_DIR . 'includes/class-wizard.php';

add_action(
	'plugins_loaded',
	static function () {
		load_plugin_textdomain( 'samadi-core', false, dirname( plugin_basename( SAMADI_CORE_FILE ) ) . '/languages' );
		Samadi_Core_Wizard::init();
	}
);

add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', SAMADI_CORE_FILE, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', SAMADI_CORE_FILE, true );
		}
	}
);

// Open the setup wizard right after activation (not during bulk or network activation).
register_activation_hook(
	__FILE__,
	static function () {
		if ( ! get_option( Samadi_Core_Demo_Importer::DONE ) ) {
			set_transient( 'samadi_core_redirect', 1, 60 );
		}
	}
);

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once SAMADI_CORE_DIR . 'includes/class-cli.php';
	WP_CLI::add_command( 'samadi', 'Samadi_Core_CLI' );
}
