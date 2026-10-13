<?php
/**
 * Iranian checkout: digit normalization, mobile / postcode / national code validation and
 * field tweaks for the classic (shortcode) checkout and the My Account address forms.
 *
 * Validation runs only for addresses in Iran, so international orders are untouched.
 *
 * @package Samadi_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Checkout module.
 */
final class Samadi_Core_Checkout {

	/**
	 * Fields whose values are normalized before WooCommerce validates and saves them.
	 */
	const NUMERIC_FIELDS = array( 'billing_phone', 'shipping_phone', 'billing_postcode', 'shipping_postcode', 'billing_national_code' );

	/**
	 * Hooks.
	 */
	public static function init() {
		add_filter( 'woocommerce_default_address_fields', array( __CLASS__, 'address_fields' ), 20 );
		add_filter( 'woocommerce_billing_fields', array( __CLASS__, 'billing_fields' ), 20 );
		add_filter( 'woocommerce_shipping_fields', array( __CLASS__, 'shipping_fields' ), 20 );
		add_filter( 'woocommerce_admin_billing_fields', array( __CLASS__, 'admin_billing_fields' ) );
		add_filter( 'woocommerce_email_customer_details_fields', array( __CLASS__, 'email_fields' ), 10, 3 );
		add_filter( 'default_checkout_billing_country', array( __CLASS__, 'default_country' ) );
		add_filter( 'default_checkout_shipping_country', array( __CLASS__, 'default_country' ) );

		foreach ( self::NUMERIC_FIELDS as $key ) {
			add_filter( 'woocommerce_process_checkout_field_' . $key, array( __CLASS__, 'normalize_' . ( false !== strpos( $key, 'phone' ) ? 'phone' : 'number' ) ) );
			add_filter( 'woocommerce_process_myaccount_field_' . $key, array( __CLASS__, 'normalize_' . ( false !== strpos( $key, 'phone' ) ? 'phone' : 'number' ) ) );
		}

		add_action( 'woocommerce_after_checkout_validation', array( __CLASS__, 'validate_checkout' ), 10, 2 );
		add_action( 'woocommerce_after_save_address_validation', array( __CLASS__, 'validate_account_address' ), 10, 2 );
	}

	/**
	 * Whether the store is in Iran.
	 *
	 * @return bool
	 */
	private static function store_in_iran() {
		return function_exists( 'WC' ) && WC()->countries && 'IR' === WC()->countries->get_base_country();
	}

	/**
	 * Whether an address country (possibly empty) means Iran.
	 *
	 * @param string $country Country code.
	 * @return bool
	 */
	private static function is_iran( $country ) {
		return 'IR' === $country || ( '' === (string) $country && self::store_in_iran() );
	}

	/**
	 * Phone: Latin digits; Iranian mobiles in the 09XXXXXXXXX form.
	 *
	 * @param string $value Value.
	 * @return string
	 */
	public static function normalize_phone( $value ) {
		$value  = trim( samadi_core_en_digits( (string) $value ) );
		$mobile = samadi_core_normalize_mobile( $value );

		return samadi_core_is_mobile( $mobile ) ? $mobile : $value;
	}

	/**
	 * Postcode / national code: Latin digits without spaces or dashes.
	 *
	 * @param string $value Value.
	 * @return string
	 */
	public static function normalize_number( $value ) {
		$value = samadi_core_en_digits( (string) $value );

		return preg_match( '/^[\d\s\-]+$/', $value ) ? (string) preg_replace( '/[\s\-]+/', '', $value ) : trim( $value );
	}

	/**
	 * Fields shared by billing and shipping.
	 *
	 * @param array $fields Fields.
	 * @return array
	 */
	public static function address_fields( $fields ) {
		if ( samadi_core_setting( 'checkout_hide_company' ) ) {
			unset( $fields['company'] );
		}
		if ( samadi_core_setting( 'checkout_hide_address2' ) ) {
			unset( $fields['address_2'] );
		}

		if ( isset( $fields['postcode'] ) ) {
			$fields['postcode']['custom_attributes'] = array_merge(
				isset( $fields['postcode']['custom_attributes'] ) ? (array) $fields['postcode']['custom_attributes'] : array(),
				array(
					'inputmode' => 'numeric',
					'dir'       => 'ltr',
				)
			);
			if ( self::store_in_iran() ) {
				$fields['postcode']['placeholder'] = __( 'کدپستی ۱۰ رقمی', 'samadi-core' );
			}
		}

		return $fields;
	}

	/**
	 * Billing fields: mobile phone, optional national code.
	 *
	 * @param array $fields Fields.
	 * @return array
	 */
	public static function billing_fields( $fields ) {
		if ( isset( $fields['billing_phone'] ) && samadi_core_setting( 'checkout_mobile' ) && self::store_in_iran() ) {
			$fields['billing_phone']['label']             = __( 'تلفن همراه', 'samadi-core' );
			$fields['billing_phone']['placeholder']       = '09123456789';
			$fields['billing_phone']['required']          = true;
			$fields['billing_phone']['custom_attributes'] = array_merge(
				isset( $fields['billing_phone']['custom_attributes'] ) ? (array) $fields['billing_phone']['custom_attributes'] : array(),
				array(
					'inputmode' => 'numeric',
					'dir'       => 'ltr',
				)
			);
		}

		$mode = (string) samadi_core_setting( 'checkout_national_code' );
		if ( in_array( $mode, array( 'optional', 'required' ), true ) ) {
			$fields['billing_national_code'] = array(
				'label'             => __( 'کد ملی', 'samadi-core' ),
				'required'          => 'required' === $mode,
				'type'              => 'text',
				'class'             => array( 'form-row-wide' ),
				'priority'          => 25,
				'maxlength'         => 12,
				'autocomplete'      => 'off',
				'custom_attributes' => array(
					'inputmode' => 'numeric',
					'dir'       => 'ltr',
				),
			);
		}

		return $fields;
	}

	/**
	 * Shipping fields: optional shipping phone (WooCommerce setting) gets the same input hints.
	 *
	 * @param array $fields Fields.
	 * @return array
	 */
	public static function shipping_fields( $fields ) {
		if ( isset( $fields['shipping_phone'] ) ) {
			$fields['shipping_phone']['custom_attributes'] = array_merge(
				isset( $fields['shipping_phone']['custom_attributes'] ) ? (array) $fields['shipping_phone']['custom_attributes'] : array(),
				array(
					'inputmode' => 'numeric',
					'dir'       => 'ltr',
				)
			);
		}

		return $fields;
	}

	/**
	 * Show and edit the national code in the order screen's billing block.
	 *
	 * @param array $fields Fields.
	 * @return array
	 */
	public static function admin_billing_fields( $fields ) {
		if ( 'off' !== samadi_core_setting( 'checkout_national_code' ) ) {
			$fields['national_code'] = array(
				'label' => __( 'کد ملی', 'samadi-core' ),
				'show'  => true,
			);
		}

		return $fields;
	}

	/**
	 * National code in order emails.
	 *
	 * @param array    $fields        Fields.
	 * @param bool     $sent_to_admin Whether the email goes to the store.
	 * @param WC_Order $order         Order.
	 * @return array
	 */
	public static function email_fields( $fields, $sent_to_admin, $order ) {
		$code = $order instanceof WC_Order ? (string) $order->get_meta( '_billing_national_code' ) : '';

		if ( '' !== $code ) {
			$fields['national_code'] = array(
				'label' => __( 'کد ملی', 'samadi-core' ),
				'value' => $code,
			);
		}

		return $fields;
	}

	/**
	 * Iran as the default checkout country for stores based in Iran.
	 *
	 * @param string|null $country Country.
	 * @return string|null
	 */
	public static function default_country( $country ) {
		if ( ! $country && samadi_core_setting( 'checkout_default_iran' ) && self::store_in_iran() ) {
			return 'IR';
		}

		return $country;
	}

	/**
	 * Errors for one address group.
	 *
	 * @param array  $data   Field key => value (already normalized).
	 * @param string $prefix billing|shipping.
	 * @return string[] Field key => message.
	 */
	private static function address_errors( $data, $prefix ) {
		$errors  = array();
		$country = isset( $data[ $prefix . '_country' ] ) ? (string) $data[ $prefix . '_country' ] : '';

		if ( ! self::is_iran( $country ) ) {
			return $errors;
		}

		$phone = isset( $data[ $prefix . '_phone' ] ) ? (string) $data[ $prefix . '_phone' ] : '';
		if ( '' !== $phone && samadi_core_setting( 'checkout_mobile' ) && ! samadi_core_is_mobile( $phone ) ) {
			$errors[ $prefix . '_phone' ] = __( 'شماره موبایل معتبر نیست. شماره را به شکل ۰۹۱۲۳۴۵۶۷۸۹ وارد کنید.', 'samadi-core' );
		}

		$postcode = isset( $data[ $prefix . '_postcode' ] ) ? (string) $data[ $prefix . '_postcode' ] : '';
		// WooCommerce reports postcodes it rejects itself; only add what it lets through.
		if ( '' !== $postcode && samadi_core_setting( 'checkout_postcode' ) && ! samadi_core_is_postcode( $postcode ) && ( ! class_exists( 'WC_Validation' ) || WC_Validation::is_postcode( $postcode, 'IR' ) ) ) {
			$errors[ $prefix . '_postcode' ] = __( 'کدپستی باید ۱۰ رقم باشد.', 'samadi-core' );
		}

		if ( 'billing' === $prefix && isset( $data['billing_national_code'] ) && '' !== (string) $data['billing_national_code'] && ! samadi_core_is_national_code( (string) $data['billing_national_code'] ) ) {
			$errors['billing_national_code'] = __( 'کد ملی وارد شده معتبر نیست.', 'samadi-core' );
		}

		return $errors;
	}

	/**
	 * Classic checkout validation.
	 *
	 * @param array    $data   Posted (normalized) data.
	 * @param WP_Error $errors Errors.
	 */
	public static function validate_checkout( $data, $errors ) {
		$groups = array( 'billing' );
		if ( ! empty( $data['ship_to_different_address'] ) ) {
			$groups[] = 'shipping';
		}

		foreach ( $groups as $prefix ) {
			foreach ( self::address_errors( $data, $prefix ) as $key => $message ) {
				$errors->add( 'validation', $message, array( 'id' => $key ) );
			}
		}
	}

	/**
	 * My Account → Addresses validation.
	 *
	 * @param int    $user_id      User ID.
	 * @param string $address_type billing|shipping.
	 */
	public static function validate_account_address( $user_id, $address_type ) {
		if ( ! in_array( $address_type, array( 'billing', 'shipping' ), true ) ) {
			return;
		}

		// WooCommerce verified the nonce before this action.
		$data = array();
		foreach ( array( 'country', 'phone', 'postcode', 'national_code' ) as $field ) {
			$key = $address_type . '_' . $field;
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$raw          = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
			$data[ $key ] = 'phone' === $field ? self::normalize_phone( $raw ) : ( 'country' === $field ? $raw : self::normalize_number( $raw ) );
		}

		foreach ( self::address_errors( $data, $address_type ) as $message ) {
			wc_add_notice( $message, 'error' );
		}
	}
}
