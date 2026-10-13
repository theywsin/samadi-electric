<?php
/**
 * Address book: several labelled addresses per customer (home, work, …).
 *
 * The default address is synced to WooCommerce's billing and shipping fields, so checkout,
 * emails and admin keep working unchanged. At checkout a picker fills the fields from any
 * saved address.
 *
 * @package Samadi_Account
 */

defined( 'ABSPATH' ) || exit;

/**
 * Address book.
 */
class Samadi_Account_Addresses {

	const META    = '_sad_addresses';
	const DEFAULT = '_sad_default_address';
	const SEEDED  = '_sad_addresses_seeded';
	const ACTION  = 'sad_address';

	/**
	 * Fields (key => label, required).
	 *
	 * @return array<string, array{0:string,1:bool}>
	 */
	public static function fields() {
		return array(
			'label'      => array( 'عنوان آدرس', true ),
			'first_name' => array( 'نام گیرنده', true ),
			'last_name'  => array( 'نام خانوادگی گیرنده', true ),
			'phone'      => array( 'شماره موبایل', true ),
			'state'      => array( 'استان', true ),
			'city'       => array( 'شهر', true ),
			'address_1'  => array( 'نشانی کامل', true ),
			'postcode'   => array( 'کد پستی', false ),
		);
	}

	/**
	 * Address types (icon per type).
	 *
	 * @return array<string, array{0:string,1:string}>
	 */
	public static function types() {
		return array(
			'home'  => array( 'خانه', 'house' ),
			'work'  => array( 'محل کار', 'briefcase' ),
			'other' => array( 'سایر', 'map-pin' ),
		);
	}

	/**
	 * Hooks.
	 */
	public static function init() {
		if ( ! sad_option( 'address_book' ) ) {
			return;
		}
		add_action( 'template_redirect', array( __CLASS__, 'handle' ) );
		if ( sad_option( 'checkout_picker' ) ) {
			add_action( 'woocommerce_before_checkout_billing_form', array( __CLASS__, 'checkout_picker' ) );
			add_filter( 'render_block_woocommerce/checkout', array( __CLASS__, 'block_checkout_picker' ) );
			add_action( 'wp_ajax_sad_pick_address', array( __CLASS__, 'ajax_pick' ) );
		}
		// Keep the book's default entry in step when the customer edits the WooCommerce billing form.
		add_action( 'woocommerce_customer_save_address', array( __CLASS__, 'from_billing' ), 10, 2 );
	}

	/**
	 * Country used for addresses (store base country).
	 *
	 * @return string
	 */
	public static function country() {
		return WC()->countries ? WC()->countries->get_base_country() : 'IR';
	}

	/**
	 * All addresses of a user (seeded once from the billing/shipping address).
	 *
	 * @param int $user_id User.
	 * @return array<string, array>
	 */
	public static function all( $user_id ) {
		if ( ! $user_id ) {
			return array();
		}
		if ( ! sad_option( 'address_book' ) ) {
			return self::from_customer( $user_id );
		}
		if ( ! get_user_meta( $user_id, self::SEEDED, true ) ) {
			self::seed( $user_id );
		}
		$list = get_user_meta( $user_id, self::META, true );
		return is_array( $list ) ? $list : array();
	}

	/**
	 * Default address ID.
	 *
	 * @param int $user_id User.
	 * @return string
	 */
	public static function default_id( $user_id ) {
		$id   = (string) get_user_meta( $user_id, self::DEFAULT, true );
		$list = self::all( $user_id );
		if ( ! isset( $list[ $id ] ) ) {
			$keys = array_keys( $list );
			$id   = $keys ? (string) $keys[0] : '';
		}
		return $id;
	}

	/**
	 * Billing/shipping addresses as read-only entries (when the address book is off).
	 *
	 * @param int $user_id User.
	 * @return array
	 */
	private static function from_customer( $user_id ) {
		$out = array();
		foreach ( array( 'billing' => array( 'آدرس صورتحساب', 'home' ), 'shipping' => array( 'آدرس ارسال', 'work' ) ) as $type => $meta ) {
			$address_1 = (string) get_user_meta( $user_id, $type . '_address_1', true );
			if ( '' === $address_1 ) {
				continue;
			}
			$out[ $type ] = array(
				'label'      => $meta[0],
				'type'       => $meta[1],
				'first_name' => (string) get_user_meta( $user_id, $type . '_first_name', true ),
				'last_name'  => (string) get_user_meta( $user_id, $type . '_last_name', true ),
				'phone'      => (string) get_user_meta( $user_id, $type . '_phone', true ),
				'state'      => (string) get_user_meta( $user_id, $type . '_state', true ),
				'city'       => (string) get_user_meta( $user_id, $type . '_city', true ),
				'address_1'  => $address_1 . ' ' . (string) get_user_meta( $user_id, $type . '_address_2', true ),
				'postcode'   => (string) get_user_meta( $user_id, $type . '_postcode', true ),
			);
		}
		return $out;
	}

	/**
	 * First-time seed from the WooCommerce billing and shipping addresses.
	 *
	 * @param int $user_id User.
	 */
	private static function seed( $user_id ) {
		update_user_meta( $user_id, self::SEEDED, 1 );
		$list = array();
		foreach ( self::from_customer( $user_id ) as $type => $address ) {
			$address['label'] = 'billing' === $type ? 'خانه' : 'محل کار';
			$address['phone'] = $address['phone'] ? $address['phone'] : (string) get_user_meta( $user_id, 'billing_phone', true );
			$address['address_1'] = trim( $address['address_1'] );
			if ( $list && self::same( reset( $list ), $address ) ) {
				continue;
			}
			$list[ self::new_id() ] = $address;
		}
		if ( $list ) {
			update_user_meta( $user_id, self::META, $list );
			update_user_meta( $user_id, self::DEFAULT, (string) array_key_first( $list ) );
		}
	}

	/**
	 * Two addresses point to the same place.
	 *
	 * @param array $a A.
	 * @param array $b B.
	 * @return bool
	 */
	private static function same( $a, $b ) {
		return $a['address_1'] === $b['address_1'] && $a['city'] === $b['city'];
	}

	/**
	 * New address ID.
	 *
	 * @return string
	 */
	private static function new_id() {
		return 'a' . substr( md5( uniqid( '', true ) ), 0, 8 );
	}

	/**
	 * State name for display.
	 *
	 * @param string $state State code.
	 * @return string
	 */
	public static function state_name( $state ) {
		$states = WC()->countries ? WC()->countries->get_states( self::country() ) : array();
		$name   = is_array( $states ) && isset( $states[ $state ] ) ? html_entity_decode( $states[ $state ] ) : $state;
		// WooCommerce's English list is "Mazandaran (مازندران)": keep the Persian name.
		if ( preg_match( '/\(([^)]*\p{Arabic}[^)]*)\)/u', $name, $m ) ) {
			$name = trim( $m[1] );
		}
		return $name;
	}

	/**
	 * One-line address for display.
	 *
	 * @param array $address Address.
	 * @return string
	 */
	public static function line( $address ) {
		$parts = array_filter( array( self::state_name( $address['state'] ), $address['city'], $address['address_1'] ) );
		return implode( '، ', $parts );
	}

	/**
	 * URL of the address book, the add form, or an action on an address.
	 *
	 * @param string $do   '' | new | edit | delete | default.
	 * @param string $id   Address ID.
	 * @return string
	 */
	public static function url( $do = '', $id = '' ) {
		$base = wc_get_account_endpoint_url( 'edit-address' );
		if ( '' === $do ) {
			return $base;
		}
		if ( in_array( $do, array( 'new', 'edit' ), true ) ) {
			return add_query_arg( array( 'sad-address' => 'new' === $do ? 'new' : $id ), $base );
		}
		return wp_nonce_url( add_query_arg( array( 'sad-do' => $do, 'sad-id' => $id ), $base ), self::ACTION . '_' . $do . '_' . $id );
	}

	/**
	 * Handle form submissions and link actions.
	 */
	public static function handle() {
		if ( ! is_user_logged_in() || ! is_account_page() ) {
			return;
		}
		$user_id = get_current_user_id();

		// Delete / make default (nonce-protected links).
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Verified below.
		if ( isset( $_GET['sad-do'], $_GET['sad-id'] ) ) {
			$do = sanitize_key( wp_unslash( $_GET['sad-do'] ) );
			$id = sanitize_key( wp_unslash( $_GET['sad-id'] ) );
			check_admin_referer( self::ACTION . '_' . $do . '_' . $id );
			$list = self::all( $user_id );
			if ( isset( $list[ $id ] ) ) {
				if ( 'delete' === $do ) {
					$was_default = (string) get_user_meta( $user_id, self::DEFAULT, true ) === $id;
					unset( $list[ $id ] );
					update_user_meta( $user_id, self::META, $list );
					if ( $was_default && $list ) {
						self::make_default( $user_id, (string) array_key_first( $list ) );
					}
					wc_add_notice( 'آدرس حذف شد.' );
				} elseif ( 'default' === $do ) {
					self::make_default( $user_id, $id );
					wc_add_notice( 'آدرس پیش‌فرض تغییر کرد.' );
				}
			}
			wp_safe_redirect( self::url() );
			exit;
		}
		// phpcs:enable

		if ( empty( $_POST['sad_address_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['sad_address_nonce'] ) ), self::ACTION . '_save' ) ) {
			wc_add_notice( 'نشست شما منقضی شده است. دوباره تلاش کنید.', 'error' );
			return;
		}

		$id      = isset( $_POST['sad_id'] ) ? sanitize_key( wp_unslash( $_POST['sad_id'] ) ) : '';
		$list    = self::all( $user_id );
		$address = array();
		$errors  = 0;
		foreach ( self::fields() as $key => $field ) {
			$value           = isset( $_POST[ 'sad_' . $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'sad_' . $key ] ) ) : '';
			$address[ $key ] = $value;
			if ( $field[1] && '' === $value ) {
				/* translators: %s: field label. */
				wc_add_notice( sprintf( '«%s» را وارد کنید.', $field[0] ), 'error' );
				++$errors;
			}
		}
		$type            = isset( $_POST['sad_type'] ) ? sanitize_key( wp_unslash( $_POST['sad_type'] ) ) : 'home';
		$address['type'] = array_key_exists( $type, self::types() ) ? $type : 'other';
		$address['phone']    = wc_sanitize_phone_number( $address['phone'] );
		$address['postcode'] = wc_format_postcode( $address['postcode'], self::country() );

		if ( $address['phone'] && ! WC_Validation::is_phone( $address['phone'] ) ) {
			wc_add_notice( 'شماره موبایل معتبر نیست.', 'error' );
			++$errors;
		}
		if ( $errors ) {
			return;
		}

		$is_new = ! isset( $list[ $id ] );
		$id     = $is_new ? self::new_id() : $id;
		$list[ $id ] = $address;
		update_user_meta( $user_id, self::META, $list );

		if ( ! empty( $_POST['sad_default'] ) || 1 === count( $list ) || self::default_id( $user_id ) === $id ) {
			self::make_default( $user_id, $id );
		}

		wc_add_notice( $is_new ? 'آدرس جدید ذخیره شد.' : 'آدرس به‌روزرسانی شد.' );
		wp_safe_redirect( self::url() );
		exit;
	}

	/**
	 * Set the default address and copy it to the WooCommerce billing and shipping fields.
	 *
	 * @param int    $user_id User.
	 * @param string $id      Address ID.
	 */
	public static function make_default( $user_id, $id ) {
		$list = self::all( $user_id );
		if ( ! isset( $list[ $id ] ) ) {
			return;
		}
		update_user_meta( $user_id, self::DEFAULT, $id );
		$a        = $list[ $id ];
		$customer = new WC_Customer( $user_id );
		foreach ( array( 'billing', 'shipping' ) as $type ) {
			$customer->{"set_{$type}_first_name"}( $a['first_name'] );
			$customer->{"set_{$type}_last_name"}( $a['last_name'] );
			$customer->{"set_{$type}_country"}( self::country() );
			$customer->{"set_{$type}_state"}( $a['state'] );
			$customer->{"set_{$type}_city"}( $a['city'] );
			$customer->{"set_{$type}_address_1"}( $a['address_1'] );
			$customer->{"set_{$type}_address_2"}( '' );
			$customer->{"set_{$type}_postcode"}( $a['postcode'] );
			$customer->{"set_{$type}_phone"}( $a['phone'] );
		}
		$customer->save();
	}

	/**
	 * When the WooCommerce billing form is saved, update the default book entry.
	 *
	 * @param int    $user_id User.
	 * @param string $type    billing|shipping.
	 */
	public static function from_billing( $user_id, $type ) {
		if ( 'billing' !== $type ) {
			return;
		}
		$list = self::all( $user_id );
		$id   = self::default_id( $user_id );
		if ( ! $id || ! isset( $list[ $id ] ) ) {
			return;
		}
		foreach ( array( 'first_name', 'last_name', 'phone', 'state', 'city', 'address_1', 'postcode' ) as $key ) {
			$list[ $id ][ $key ] = (string) get_user_meta( $user_id, 'billing_' . $key, true );
		}
		update_user_meta( $user_id, self::META, $list );
	}

	/**
	 * Address picker above the classic checkout billing form (fills the fields in place).
	 */
	public static function checkout_picker() {
		echo self::picker_html( 'classic' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in picker_html().
	}

	/**
	 * Address picker above the block checkout (saves the choice, then reloads the checkout).
	 *
	 * @param string $content Block content.
	 * @return string
	 */
	public static function block_checkout_picker( $content ) {
		return self::picker_html( 'block' ) . $content;
	}

	/**
	 * Use a saved address for checkout: it becomes the default and fills billing/shipping.
	 */
	public static function ajax_pick() {
		check_ajax_referer( 'sad_pick', 'nonce' );
		$id   = isset( $_POST['id'] ) ? sanitize_key( wp_unslash( $_POST['id'] ) ) : '';
		$list = self::all( get_current_user_id() );
		if ( ! isset( $list[ $id ] ) ) {
			wp_send_json_error( null, 400 );
		}
		self::make_default( get_current_user_id(), $id );
		if ( WC()->customer ) {
			// Refresh the session customer so the checkout reads the new address.
			WC()->customer = new WC_Customer( get_current_user_id(), true );
			WC()->customer->save();
		}
		wp_send_json_success();
	}

	/**
	 * Picker markup.
	 *
	 * @param string $mode classic|block.
	 * @return string
	 */
	private static function picker_html( $mode ) {
		if ( ! is_user_logged_in() ) {
			return '';
		}
		$user_id = get_current_user_id();
		$list    = self::all( $user_id );
		if ( count( $list ) < 2 ) {
			return '';
		}
		$default = self::default_id( $user_id );
		$data    = array();
		ob_start();
		printf(
			'<div class="sad-picker" data-mode="%1$s" data-nonce="%2$s"><p class="sad-picker__title">ارسال به کدام آدرس؟</p><div class="sad-picker__list">',
			esc_attr( $mode ),
			esc_attr( wp_create_nonce( 'sad_pick' ) )
		);
		foreach ( $list as $id => $a ) {
			$data[ $id ] = array(
				'billing_first_name' => $a['first_name'],
				'billing_last_name'  => $a['last_name'],
				'billing_phone'      => $a['phone'],
				'billing_state'      => $a['state'],
				'billing_city'       => $a['city'],
				'billing_address_1'  => $a['address_1'],
				'billing_address_2'  => '',
				'billing_postcode'   => $a['postcode'],
			);
			printf(
				'<label class="sad-picker__item"><input type="radio" name="sad_pick" value="%1$s"%2$s><span><strong>%3$s</strong><small>%4$s</small></span></label>',
				esc_attr( $id ),
				checked( $id, $default, false ),
				esc_html( $a['label'] ),
				esc_html( self::line( $a ) )
			);
		}
		echo '</div><script type="application/json" class="sad-picker__data">' . wp_json_encode( $data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE ) . '</script></div>';
		return (string) ob_get_clean();
	}
}
