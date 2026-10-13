<?php
/**
 * Plugin Name:       Samadi Core
 * Plugin URI:        https://samadielectric.ir/
 * Description:       هسته قالب صمدی الکتریک: علاقه‌مندی‌ها، مقایسه محصولات، خبرنامه پیامکی، تاریخ شمسی، فیلدها و اعتبارسنجی تسویه‌حساب ایرانی، اسکیمای فروشگاه و مقاوم‌سازی امنیتی. Core features of the Samadi Electric theme, kept in a plugin so they survive a theme switch.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Samadi Electric
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       samadi-core
 * Domain Path:       /languages
 * WC requires at least: 9.0
 * WC tested up to:   10.9
 *
 * @package Samadi_Core
 */

defined( 'ABSPATH' ) || exit;

define( 'SAMADI_CORE_VERSION', '1.0.0' );
define( 'SAMADI_CORE_DB_VERSION', '1' );
define( 'SAMADI_CORE_FILE', __FILE__ );
define( 'SAMADI_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'SAMADI_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once SAMADI_CORE_DIR . 'includes/helpers.php';
require_once SAMADI_CORE_DIR . 'includes/class-jalali.php';
require_once SAMADI_CORE_DIR . 'includes/class-plugin.php';

register_activation_hook( __FILE__, array( 'Samadi_Core_Plugin', 'activate' ) );

/**
 * Declare compatibility with WooCommerce High-Performance Order Storage and Cart/Checkout blocks.
 */
add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', SAMADI_CORE_FILE, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', SAMADI_CORE_FILE, true );
		}
	}
);

add_action( 'plugins_loaded', array( 'Samadi_Core_Plugin', 'instance' ), 15 );
