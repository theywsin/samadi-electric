<?php
/**
 * Plugin Name:       Samadi Elementor Widgets
 * Plugin URI:        https://samadielectric.ir/
 * Description:       افزونه همراه قالب صمدی الکتریک: ویجت‌های اختصاصی Elementor، فیلتر محصولات، جستجوی زنده، خرید عمده و استعلام قیمت، محصولات اخیراً مشاهده‌شده. Companion plugin for the Samadi Electric theme (works with other themes too).
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * Author:            Samadi Electric
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       samadi-elementor-widgets
 * Domain Path:       /languages
 * WC requires at least: 9.0
 * WC tested up to:   10.9
 * Elementor tested up to: 3.35
 * Elementor Pro tested up to: 3.35
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

define( 'SAMADI_EW_VERSION', '1.0.0' );
define( 'SAMADI_EW_FILE', __FILE__ );
define( 'SAMADI_EW_DIR', plugin_dir_path( __FILE__ ) );
define( 'SAMADI_EW_URL', plugin_dir_url( __FILE__ ) );

require_once SAMADI_EW_DIR . 'includes/helpers.php';
require_once SAMADI_EW_DIR . 'includes/class-plugin.php';

/**
 * Declare compatibility with WooCommerce High-Performance Order Storage and Cart/Checkout blocks.
 */
add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', SAMADI_EW_FILE, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', SAMADI_EW_FILE, true );
		}
	}
);

add_action( 'plugins_loaded', array( 'Samadi_EW_Plugin', 'instance' ), 20 );
