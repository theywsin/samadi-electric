<?php
/**
 * Plugin Name:       Samadi Header & Footer
 * Plugin URI:        https://samadielectric.ir/
 * Description:       سه طرح آماده هدر و سه طرح آماده فوتر با پیش‌نمایش؛ هر کدام را جداگانه نصب کنید و به‌طور کامل با Elementor ویرایش کنید. ویجت‌های لوگو، منو (با منوی کشویی موبایل)، جستجو، آیکون‌های سبد/حساب، دسته‌بندی‌ها، فهرست لینک‌ها و کپی‌رایت. Three header and three footer designs, installable separately and fully editable with Elementor.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Requires Plugins:  elementor
 * Author:            Samadi Electric
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       samadi-header-footer
 * Elementor tested up to: 4.3
 *
 * @package Samadi_Header_Footer
 */

defined( 'ABSPATH' ) || exit;

define( 'SHF_VERSION', '1.0.0' );
define( 'SHF_FILE', __FILE__ );
define( 'SHF_DIR', plugin_dir_path( __FILE__ ) );
define( 'SHF_URL', plugin_dir_url( __FILE__ ) );

require_once SHF_DIR . 'includes/class-designs.php';
require_once SHF_DIR . 'includes/class-library.php';
require_once SHF_DIR . 'includes/class-renderer.php';
require_once SHF_DIR . 'includes/class-admin.php';
require_once SHF_DIR . 'includes/class-plugin.php';

add_action( 'plugins_loaded', array( 'SHF_Plugin', 'init' ) );
