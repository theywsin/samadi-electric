<?php
/**
 * Helper functions: options, icons, Persian digits, Jalali dates, order status labels.
 *
 * @package Samadi_Account
 */

defined( 'ABSPATH' ) || exit;

/**
 * Option defaults.
 *
 * @return array<string, mixed>
 */
function sad_defaults() {
	return array(
		'hero_image'     => 0,
		'hero_title'     => 'سلام، %s جان 👋',
		'hero_text'      => "به حساب کاربری خود در %s خوش آمدید.\nاز اینجا می‌توانید سفارش‌ها، آدرس‌ها و اطلاعات حساب خود را مدیریت کنید.",
		'support_show'   => true,
		'support_title'  => 'نیاز به کمک دارید؟',
		'support_text'   => 'کارشناسان ما آماده پاسخگویی هستند.',
		'support_label'  => 'تماس با پشتیبانی',
		'support_url'    => '',
		'wishlist'       => true,
		'address_book'   => true,
		'checkout_picker' => true,
		'hide_title'     => true,
		'persian_digits' => true,
		'jalali'         => true,
		'font'           => true,
		'color_primary'  => '#0F4D3A',
		'color_accent'   => '#1E7A52',
	);
}

/**
 * Read an option.
 *
 * @param string $key Key.
 * @return mixed
 */
function sad_option( $key ) {
	$saved    = get_option( 'sad_settings', array() );
	$defaults = sad_defaults();
	$saved    = is_array( $saved ) ? array_merge( $defaults, $saved ) : $defaults;

	return array_key_exists( $key, $saved ) ? $saved[ $key ] : null;
}

/**
 * Inline SVG icon.
 *
 * @param string $name Icon.
 * @param int    $size Size.
 * @param string $class Extra class.
 * @return string
 */
function sad_icon( $name, $size = 20, $class = '' ) {
	static $icons = null;
	if ( null === $icons ) {
		$icons = require SAD_DIR . 'includes/icons.php';
	}
	if ( empty( $icons[ $name ] ) ) {
		return '';
	}
	return sprintf(
		'<svg class="sad-icon sad-icon--%1$s %2$s" width="%3$d" height="%3$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%4$s</svg>',
		esc_attr( $name ),
		esc_attr( $class ),
		absint( $size ),
		$icons[ $name ]
	);
}

/**
 * Persian digits in text nodes (never inside tags or entities).
 *
 * @param string $html HTML or text.
 * @return string
 */
function sad_fa( $html ) {
	$html = (string) $html;
	if ( ! sad_option( 'persian_digits' ) || ! preg_match( '/\d/', $html ) ) {
		return $html;
	}
	$parts = preg_split( '/(<[^>]*>|&#?[a-z0-9]+;)/i', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
	foreach ( $parts as $i => $part ) {
		if ( '' !== $part && '<' !== $part[0] && '&' !== $part[0] ) {
			$parts[ $i ] = strtr( $part, array( '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹' ) );
		}
	}
	return implode( '', $parts );
}

/**
 * Gregorian → Jalali.
 *
 * @param int $gy Year.
 * @param int $gm Month.
 * @param int $gd Day.
 * @return int[] [ year, month, day ]
 */
function sad_gregorian_to_jalali( $gy, $gm, $gd ) {
	$g_d_m = array( 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334 );
	$gy2   = ( $gm > 2 ) ? ( $gy + 1 ) : $gy;
	$days  = 355666 + ( 365 * $gy ) + (int) ( ( $gy2 + 3 ) / 4 ) - (int) ( ( $gy2 + 99 ) / 100 ) + (int) ( ( $gy2 + 399 ) / 400 ) + $gd + $g_d_m[ $gm - 1 ];
	$jy    = -1595 + ( 33 * (int) ( $days / 12053 ) );
	$days %= 12053;
	$jy   += 4 * (int) ( $days / 1461 );
	$days %= 1461;
	if ( $days > 365 ) {
		$jy  += (int) ( ( $days - 1 ) / 365 );
		$days = ( $days - 1 ) % 365;
	}
	if ( $days < 186 ) {
		$jm = 1 + (int) ( $days / 31 );
		$jd = 1 + ( $days % 31 );
	} else {
		$jm = 7 + (int) ( ( $days - 186 ) / 30 );
		$jd = 1 + ( ( $days - 186 ) % 30 );
	}
	return array( $jy, $jm, $jd );
}

/**
 * Format a date for display: Jalali ("۲۲ مهر ۱۴۰۳") or the site's date format.
 *
 * @param WC_DateTime|int|string|null $date Date.
 * @return string
 */
function sad_date( $date ) {
	if ( ! $date ) {
		return '';
	}
	if ( $date instanceof DateTimeInterface ) {
		$timestamp = $date->getTimestamp() + $date->getOffset();
	} else {
		$timestamp = is_numeric( $date ) ? (int) $date : strtotime( (string) $date );
		$timestamp = $timestamp + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
	}

	// A Persian calendar plugin may already localize date_i18n(); only convert here when asked.
	if ( ! sad_option( 'jalali' ) ) {
		return sad_fa( date_i18n( get_option( 'date_format' ), $timestamp ) );
	}

	$months = array( 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند' );
	list( $y, $m, $d ) = sad_gregorian_to_jalali( (int) gmdate( 'Y', $timestamp ), (int) gmdate( 'n', $timestamp ), (int) gmdate( 'j', $timestamp ) );

	return sad_fa( $d . ' ' . $months[ $m - 1 ] . ' ' . $y );
}

/**
 * Display label and tone for an order status.
 *
 * @param string $status Status without wc- prefix.
 * @return array{label:string,tone:string}
 */
function sad_status( $status ) {
	$map = array(
		'pending'    => array( 'در انتظار پرداخت', 'warning' ),
		'processing' => array( 'در حال ارسال', 'info' ),
		'on-hold'    => array( 'در انتظار بررسی', 'warning' ),
		'completed'  => array( 'تحویل شده', 'success' ),
		'cancelled'  => array( 'لغو شده', 'danger' ),
		'refunded'   => array( 'مسترد شده', 'danger' ),
		'failed'     => array( 'ناموفق', 'danger' ),
	);
	$row = isset( $map[ $status ] ) ? $map[ $status ] : array( wc_get_order_status_name( $status ), 'neutral' );

	/**
	 * Filter an order status label/tone (tone: info|success|warning|danger|neutral).
	 *
	 * @param array  $status { label, tone }.
	 * @param string $key    Status.
	 */
	return apply_filters(
		'sad_order_status',
		array(
			'label' => $row[0],
			'tone'  => $row[1],
		),
		$status
	);
}

/**
 * Iranian mobile numbers grouped like "0911 822 0943" (other numbers unchanged).
 *
 * @param string $phone Phone.
 * @return string
 */
function sad_phone( $phone ) {
	$digits = preg_replace( '/\D/', '', strtr( (string) $phone, array( '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9' ) ) );
	if ( 11 === strlen( $digits ) && 0 === strpos( $digits, '09' ) ) {
		$phone = substr( $digits, 0, 4 ) . ' ' . substr( $digits, 4, 3 ) . ' ' . substr( $digits, 7 );
	}
	return sad_fa( $phone );
}

/**
 * Price with Persian digits.
 *
 * @param float|string $amount Amount.
 * @return string
 */
function sad_price( $amount ) {
	return sad_fa( wc_price( $amount ) );
}

/**
 * Current user's avatar (uploaded photo, otherwise initials — no external requests).
 *
 * @param int $user_id User ID.
 * @param int $size    Size.
 * @return string
 */
function sad_avatar( $user_id, $size = 72 ) {
	$attachment = (int) get_user_meta( $user_id, '_sad_avatar', true );
	if ( $attachment && wp_attachment_is_image( $attachment ) ) {
		return wp_get_attachment_image( $attachment, array( $size, $size ), false, array( 'class' => 'sad-avatar__img', 'alt' => '' ) );
	}

	$user  = get_userdata( $user_id );
	$name  = $user ? trim( $user->first_name . ' ' . $user->last_name ) : '';
	$name  = '' !== $name ? $name : ( $user ? $user->display_name : '' );
	$first = function_exists( 'mb_substr' ) ? mb_substr( $name, 0, 1 ) : substr( $name, 0, 1 );

	return '<span class="sad-avatar__initial" aria-hidden="true">' . esc_html( $first ) . '</span>';
}

/**
 * Display name: first + last name, falling back to the display name.
 *
 * @param WP_User $user User.
 * @return string
 */
function sad_user_name( $user ) {
	$name = trim( $user->first_name . ' ' . $user->last_name );
	return '' !== $name ? $name : $user->display_name;
}

/**
 * Short greeting name (first name or display name).
 *
 * @param WP_User $user User.
 * @return string
 */
function sad_first_name( $user ) {
	return '' !== trim( $user->first_name ) ? $user->first_name : $user->display_name;
}

/**
 * Support link URL (setting, contact page, or the store's phone).
 *
 * @return string
 */
function sad_support_url() {
	$url = (string) sad_option( 'support_url' );
	if ( $url ) {
		return $url;
	}
	foreach ( array( 'contact', 'contact-us', 'تماس-با-ما' ) as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page && 'publish' === $page->post_status ) {
			return get_permalink( $page );
		}
	}
	$phone = function_exists( 'samadi_option' ) ? (string) samadi_option( 'contact_phone' ) : '';
	if ( $phone && function_exists( 'samadi_tel' ) ) {
		return 'tel:' . samadi_tel( $phone );
	}
	return home_url( '/' );
}

/**
 * Load a plugin template part.
 *
 * @param string $name Template name (relative to templates/).
 * @param array  $args Variables.
 */
function sad_template( $name, $args = array() ) {
	$file = SAD_DIR . 'templates/' . $name . '.php';
	if ( file_exists( $file ) ) {
		extract( $args, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Local template variables.
		include $file;
	}
}
