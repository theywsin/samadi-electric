<?php
/**
 * Jalali (Solar Hijri) dates across the site.
 *
 * Hooks `wp_date`, which WordPress uses for post, comment and archive dates and WooCommerce uses
 * for order and review dates. Machine-readable formats (ISO 8601, RFC 2822, Unix time, Y-m-d…) are
 * left in the Gregorian calendar so feeds, sitemaps, schema and `<time datetime>` stay valid.
 *
 * @package Samadi_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Jalali dates module.
 */
final class Samadi_Core_Jalali_Dates {

	/**
	 * Hooks.
	 */
	public static function init() {
		if ( self::other_plugin() ) {
			return;
		}

		add_filter( 'wp_date', array( __CLASS__, 'filter_date' ), 10, 4 );
	}

	/**
	 * Whether another Jalali date plugin is active.
	 *
	 * @return bool
	 */
	public static function other_plugin() {
		$active = defined( 'WP_PARSI_VER' ) || function_exists( 'parsidate' ) || defined( 'WPP_VERSION' ) || class_exists( 'WPP_Jdate' );

		/**
		 * Whether another plugin already converts dates to Jalali.
		 *
		 * @param bool $active Detected.
		 */
		return (bool) apply_filters( 'samadi_core_other_jalali_plugin', $active );
	}

	/**
	 * Whether a PHP date format is meant for machines rather than people.
	 *
	 * @param string $format Format.
	 * @return bool
	 */
	public static function is_machine_format( $format ) {
		$plain = preg_replace( '/\\\\./', '', (string) $format );

		if ( preg_match( '/[crUOPe]/', (string) $plain ) ) {
			return true;
		}

		return 0 === strpos( ltrim( (string) $plain ), 'Y-m-d' ) || in_array( $plain, array( 'Ymd', 'YmdHis', 'Y', 'G', 'H', 'i', 's', 'n', 'j', 'm', 'd', 'w', 'N', 'z', 't', 'L' ), true );
	}

	/**
	 * Whether this request should show Jalali dates.
	 *
	 * @return bool
	 */
	private static function applies() {
		if ( is_admin() && ! wp_doing_ajax() && ! samadi_core_setting( 'jalali_admin' ) ) {
			return false;
		}

		if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return false;
		}

		if ( did_action( 'parse_query' ) && ( is_feed() || ( function_exists( 'is_robots' ) && is_robots() ) ) ) {
			return false;
		}

		return (bool) apply_filters( 'samadi_core_jalali_applies', true );
	}

	/**
	 * `wp_date` filter.
	 *
	 * @param string            $date      Formatted date.
	 * @param string            $format    Format.
	 * @param int               $timestamp Unix timestamp.
	 * @param DateTimeZone|null $timezone  Time zone.
	 * @return string
	 */
	public static function filter_date( $date, $format, $timestamp, $timezone ) {
		if ( ! is_string( $format ) || '' === $format || self::is_machine_format( $format ) || ! self::applies() ) {
			return $date;
		}

		$out = Samadi_Core_Jalali::format( $format, (int) $timestamp, $timezone );

		return samadi_core_setting( 'jalali_digits' ) ? samadi_core_fa_digits( $out ) : $out;
	}
}
