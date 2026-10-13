<?php
/**
 * Samadi Electric theme bootstrap.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

define( 'SAMADI_VERSION', '1.1.0' );
define( 'SAMADI_DIR', get_template_directory() );
define( 'SAMADI_URI', get_template_directory_uri() );

/**
 * Theme modules. Order matters: helpers and defaults are used by everything else.
 */
$samadi_modules = array(
	'helpers',
	'setup',
	'customizer',
	'enqueue',
	'template-tags',
	'navigation',
	'accessibility',
	'performance',
	'seo',
	'elementor',
);

foreach ( $samadi_modules as $samadi_module ) {
	require_once SAMADI_DIR . '/inc/' . $samadi_module . '.php';
}

if ( class_exists( 'WooCommerce' ) ) {
	require_once SAMADI_DIR . '/inc/woocommerce.php';
	require_once SAMADI_DIR . '/inc/account.php';
}

unset( $samadi_modules, $samadi_module );
