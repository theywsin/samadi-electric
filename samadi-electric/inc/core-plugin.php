<?php
/**
 * Samadi Core companion: one-click install/activation from the package bundled with the theme.
 *
 * The theme works on its own; Samadi Core adds the setup wizard and the full demo import.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugin file of Samadi Core.
 */
const SAMADI_CORE_PLUGIN = 'samadi-core/samadi-core.php';

/**
 * Admin notice offering to install/activate Samadi Core.
 */
function samadi_core_plugin_notice() {
	if ( defined( 'SAMADI_CORE_VERSION' ) || ! current_user_can( 'install_plugins' ) ) {
		return;
	}
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$installed = array_key_exists( SAMADI_CORE_PLUGIN, get_plugins() );
	if ( ! $installed && ! file_exists( SAMADI_DIR . '/bundled/samadi-core.zip' ) ) {
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=samadi_install_core' ), 'samadi_install_core' );
	printf(
		'<div class="notice notice-info"><p><strong>%1$s</strong> %2$s <a class="button button-primary" style="margin-inline-start:8px" href="%3$s">%4$s</a></p></div>',
		esc_html__( 'قالب صمدی الکتریک:', 'samadi-electric' ),
		esc_html__( 'برای راه‌اندازی خودکار و درون‌ریزی کامل دمو (همه صفحات، محصولات، منوها)، افزونه Samadi Core را فعال کنید.', 'samadi-electric' ),
		esc_url( $url ),
		$installed ? esc_html__( 'فعال‌سازی Samadi Core', 'samadi-electric' ) : esc_html__( 'نصب و فعال‌سازی Samadi Core', 'samadi-electric' )
	);
}
add_action( 'admin_notices', 'samadi_core_plugin_notice' );

/**
 * Install (from the bundled zip) and activate Samadi Core, then open its wizard.
 */
function samadi_install_core_plugin() {
	check_admin_referer( 'samadi_install_core' );
	if ( ! current_user_can( 'install_plugins' ) || ! current_user_can( 'activate_plugins' ) ) {
		wp_die( esc_html__( 'دسترسی ندارید.', 'samadi-electric' ) );
	}

	require_once ABSPATH . 'wp-admin/includes/plugin.php';

	if ( ! array_key_exists( SAMADI_CORE_PLUGIN, get_plugins() ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
		$result   = $upgrader->install( SAMADI_DIR . '/bundled/samadi-core.zip' );
		if ( ! $result || is_wp_error( $result ) ) {
			wp_die( esc_html__( 'نصب Samadi Core انجام نشد. فایل samadi-core.zip را از «افزونه‌ها ← افزودن ← بارگذاری» نصب کنید.', 'samadi-electric' ) );
		}
		wp_clean_plugins_cache();
	}

	$activated = activate_plugin( SAMADI_CORE_PLUGIN );
	if ( is_wp_error( $activated ) ) {
		wp_die( esc_html( $activated->get_error_message() ) );
	}

	wp_safe_redirect( admin_url( 'admin.php?page=samadi-setup' ) );
	exit;
}
add_action( 'admin_post_samadi_install_core', 'samadi_install_core_plugin' );
