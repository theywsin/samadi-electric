<?php
/**
 * Low-risk hardening: XML-RPC off, no WordPress version in the markup, no user-name discovery
 * through ?author= / the REST users list / the users sitemap, and one login error for both a wrong
 * user name and a wrong password.
 *
 * @package Samadi_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Security module.
 */
final class Samadi_Core_Security {

	/**
	 * Hooks.
	 */
	public static function init() {
		if ( samadi_core_setting( 'security_xmlrpc' ) ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
			add_filter( 'xmlrpc_methods', '__return_empty_array' );
			add_filter( 'wp_headers', array( __CLASS__, 'remove_pingback_header' ) );
			add_filter( 'pings_open', '__return_false', 20 );
			remove_action( 'wp_head', 'rsd_link' );
		}

		if ( samadi_core_setting( 'security_version' ) ) {
			remove_action( 'wp_head', 'wp_generator' );
			add_filter( 'the_generator', '__return_empty_string' );
		}

		if ( samadi_core_setting( 'security_users' ) ) {
			add_action( 'template_redirect', array( __CLASS__, 'block_author_scan' ), 1 );
			add_filter( 'rest_endpoints', array( __CLASS__, 'hide_users_endpoints' ) );
			add_filter( 'wp_sitemaps_add_provider', array( __CLASS__, 'remove_users_sitemap' ), 10, 2 );
			add_filter( 'oembed_response_data', array( __CLASS__, 'hide_oembed_author' ) );
		}

		if ( samadi_core_setting( 'security_login_errors' ) ) {
			add_filter( 'authenticate', array( __CLASS__, 'generic_login_error' ), 99 );
		}
	}

	/**
	 * Drop the X-Pingback header.
	 *
	 * @param array $headers Headers.
	 * @return array
	 */
	public static function remove_pingback_header( $headers ) {
		unset( $headers['X-Pingback'] );

		return $headers;
	}

	/**
	 * ?author=1 redirects to the user's archive and reveals the login slug: send visitors home instead.
	 */
	public static function block_author_scan() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only check.
		if ( is_admin() || ! isset( $_GET['author'] ) || current_user_can( 'list_users' ) ) {
			return;
		}

		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}

	/**
	 * Hide /wp/v2/users from visitors (the block editor still gets it).
	 *
	 * @param array $endpoints Endpoints.
	 * @return array
	 */
	public static function hide_users_endpoints( $endpoints ) {
		if ( is_user_logged_in() ) {
			return $endpoints;
		}

		foreach ( array_keys( $endpoints ) as $route ) {
			if ( 0 === strpos( $route, '/wp/v2/users' ) ) {
				unset( $endpoints[ $route ] );
			}
		}

		return $endpoints;
	}

	/**
	 * No users sitemap.
	 *
	 * @param WP_Sitemaps_Provider|false $provider Provider.
	 * @param string                     $name     Provider name.
	 * @return WP_Sitemaps_Provider|false
	 */
	public static function remove_users_sitemap( $provider, $name ) {
		return 'users' === $name ? false : $provider;
	}

	/**
	 * No author name / URL in oEmbed responses.
	 *
	 * @param array $data Data.
	 * @return array
	 */
	public static function hide_oembed_author( $data ) {
		unset( $data['author_name'], $data['author_url'] );

		return $data;
	}

	/**
	 * Same message for an unknown user and a wrong password.
	 *
	 * @param WP_User|WP_Error|null $user Result so far.
	 * @return WP_User|WP_Error|null
	 */
	public static function generic_login_error( $user ) {
		if ( ! is_wp_error( $user ) ) {
			return $user;
		}

		if ( array_intersect( $user->get_error_codes(), array( 'invalid_username', 'invalid_email', 'incorrect_password' ) ) ) {
			return new WP_Error( 'authentication_failed', __( '<strong>خطا:</strong> نام کاربری یا رمز عبور نادرست است.', 'samadi-core' ) );
		}

		return $user;
	}
}
