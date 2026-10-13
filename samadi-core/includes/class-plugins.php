<?php
/**
 * Required plugins: status, installation (wordpress.org or bundled package) and activation.
 *
 * @package Samadi_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugin dependency manager.
 */
class Samadi_Core_Plugins {

	/**
	 * Required plugins.
	 *
	 * @return array<string, array{name:string,file:string,source:string}>
	 */
	public static function required() {
		return array(
			'woocommerce'              => array(
				'name'   => 'WooCommerce',
				'file'   => 'woocommerce/woocommerce.php',
				'source' => 'wporg',
			),
			'elementor'                => array(
				'name'   => 'Elementor',
				'file'   => 'elementor/elementor.php',
				'source' => 'wporg',
			),
			'samadi-elementor-widgets' => array(
				'name'   => 'Samadi Elementor Widgets',
				'file'   => 'samadi-elementor-widgets/samadi-elementor-widgets.php',
				'source' => SAMADI_CORE_DIR . 'bundled/samadi-elementor-widgets.zip',
			),
		);
	}

	/**
	 * Status of a plugin: active | installed | missing.
	 *
	 * @param string $slug Slug.
	 * @return string
	 */
	public static function status( $slug ) {
		$plugin = self::required()[ $slug ];
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( is_plugin_active( $plugin['file'] ) ) {
			return 'active';
		}
		return array_key_exists( $plugin['file'], get_plugins() ) ? 'installed' : 'missing';
	}

	/**
	 * Whether every required plugin is active.
	 *
	 * @return bool
	 */
	public static function all_active() {
		foreach ( array_keys( self::required() ) as $slug ) {
			if ( 'active' !== self::status( $slug ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Install (if needed) and activate a required plugin.
	 *
	 * @param string $slug Slug.
	 * @return true|WP_Error
	 */
	public static function install_and_activate( $slug ) {
		$required = self::required();
		if ( ! isset( $required[ $slug ] ) ) {
			return new WP_Error( 'samadi_unknown_plugin', __( 'افزونه ناشناخته است.', 'samadi-core' ) );
		}
		$plugin = $required[ $slug ];

		if ( 'missing' === self::status( $slug ) ) {
			if ( ! current_user_can( 'install_plugins' ) ) {
				return new WP_Error( 'samadi_cap', __( 'اجازه نصب افزونه را ندارید.', 'samadi-core' ) );
			}

			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/misc.php';
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

			if ( 'wporg' === $plugin['source'] ) {
				$api = plugins_api( 'plugin_information', array( 'slug' => $slug, 'fields' => array( 'sections' => false ) ) );
				if ( is_wp_error( $api ) ) {
					return $api;
				}
				$package = $api->download_link;
			} else {
				$package = $plugin['source'];
				if ( ! file_exists( $package ) ) {
					return new WP_Error( 'samadi_package', __( 'بسته افزونه همراه در Samadi Core پیدا نشد.', 'samadi-core' ) );
				}
			}

			$skin     = new WP_Ajax_Upgrader_Skin();
			$upgrader = new Plugin_Upgrader( $skin );
			$result   = $upgrader->install( $package );

			if ( is_wp_error( $result ) ) {
				return $result;
			}
			if ( is_wp_error( $skin->result ) ) {
				return $skin->result;
			}
			if ( ! $result ) {
				$errors = $skin->get_errors();
				return is_wp_error( $errors ) && $errors->has_errors() ? $errors : new WP_Error( 'samadi_install', __( 'نصب افزونه انجام نشد. دسترسی نوشتن پوشه افزونه‌ها را بررسی کنید.', 'samadi-core' ) );
			}
			wp_clean_plugins_cache();
		}

		if ( 'active' !== self::status( $slug ) ) {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return new WP_Error( 'samadi_cap', __( 'اجازه فعال‌سازی افزونه را ندارید.', 'samadi-core' ) );
			}
			// Plugins redirect to their own onboarding after activation; the wizard keeps control.
			delete_transient( '_wc_activation_redirect' );
			$activated = activate_plugin( $plugin['file'] );
			delete_transient( '_wc_activation_redirect' );
			delete_transient( 'elementor_activation_redirect' );
			if ( is_wp_error( $activated ) ) {
				return $activated;
			}
		}

		return true;
	}
}
