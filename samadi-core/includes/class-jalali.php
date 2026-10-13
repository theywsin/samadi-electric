<?php
/**
 * Solar Hijri (Jalali) calendar: conversion and date formatting.
 *
 * The formatter understands PHP date() format characters. Calendar-dependent characters
 * (day, month, year, week-day names, leap year, day of year) are rendered in the Jalali
 * calendar; time characters are delegated to PHP.
 *
 * @package Samadi_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Jalali calendar helpers.
 */
final class Samadi_Core_Jalali {

	/**
	 * Month names.
	 *
	 * @var string[]
	 */
	const MONTHS = array( 1 => 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند' );

	/**
	 * Week-day names, indexed like PHP's "w" (0 = Sunday).
	 *
	 * @var string[]
	 */
	const DAYS = array( 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه' );

	/**
	 * One-letter week-day abbreviations, indexed like PHP's "w".
	 *
	 * @var string[]
	 */
	const DAYS_SHORT = array( 'ی', 'د', 'س', 'چ', 'پ', 'ج', 'ش' );

	/**
	 * Gregorian date to Jalali.
	 *
	 * @param int $gy Year.
	 * @param int $gm Month (1-12).
	 * @param int $gd Day.
	 * @return int[] [ year, month, day ]
	 */
	public static function from_gregorian( $gy, $gm, $gd ) {
		$g_d_m = array( 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334 );
		$gy2   = $gm > 2 ? $gy + 1 : $gy;
		$days  = 355666 + ( 365 * $gy ) + intdiv( $gy2 + 3, 4 ) - intdiv( $gy2 + 99, 100 ) + intdiv( $gy2 + 399, 400 ) + $gd + $g_d_m[ $gm - 1 ];
		$jy    = -1595 + ( 33 * intdiv( $days, 12053 ) );
		$days %= 12053;
		$jy   += 4 * intdiv( $days, 1461 );
		$days %= 1461;

		if ( $days > 365 ) {
			$jy  += intdiv( $days - 1, 365 );
			$days = ( $days - 1 ) % 365;
		}

		if ( $days < 186 ) {
			$jm = 1 + intdiv( $days, 31 );
			$jd = 1 + ( $days % 31 );
		} else {
			$jm = 7 + intdiv( $days - 186, 30 );
			$jd = 1 + ( ( $days - 186 ) % 30 );
		}

		return array( $jy, $jm, $jd );
	}

	/**
	 * Jalali date to Gregorian.
	 *
	 * @param int $jy Year.
	 * @param int $jm Month (1-12).
	 * @param int $jd Day.
	 * @return int[] [ year, month, day ]
	 */
	public static function to_gregorian( $jy, $jm, $jd ) {
		$jy  += 1595;
		$days = -355668 + ( 365 * $jy ) + ( intdiv( $jy, 33 ) * 8 ) + intdiv( ( $jy % 33 ) + 3, 4 ) + $jd + ( $jm < 7 ? ( $jm - 1 ) * 31 : ( ( $jm - 7 ) * 30 ) + 186 );
		$gy   = 400 * intdiv( $days, 146097 );
		$days %= 146097;

		if ( $days > 36524 ) {
			$gy  += 100 * intdiv( --$days, 36524 );
			$days %= 36524;
			if ( $days >= 365 ) {
				++$days;
			}
		}

		$gy  += 4 * intdiv( $days, 1461 );
		$days %= 1461;

		if ( $days > 365 ) {
			$gy  += intdiv( $days - 1, 365 );
			$days = ( $days - 1 ) % 365;
		}

		$gd    = $days + 1;
		$leap  = ( 0 === $gy % 4 && 0 !== $gy % 100 ) || 0 === $gy % 400;
		$sal_a = array( 0, 31, $leap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );

		for ( $gm = 0; $gm < 13 && $gd > $sal_a[ $gm ]; $gm++ ) {
			$gd -= $sal_a[ $gm ];
		}

		return array( $gy, $gm, $gd );
	}

	/**
	 * Whether a Jalali year is a leap year (33-year cycle, matches the conversion above).
	 *
	 * @param int $jy Year.
	 * @return bool
	 */
	public static function is_leap( $jy ) {
		return in_array( ( ( $jy % 33 ) + 33 ) % 33, array( 1, 5, 9, 13, 17, 22, 26, 30 ), true );
	}

	/**
	 * Number of days in a Jalali month.
	 *
	 * @param int $jy Year.
	 * @param int $jm Month.
	 * @return int
	 */
	public static function month_days( $jy, $jm ) {
		if ( $jm <= 6 ) {
			return 31;
		}
		if ( $jm <= 11 ) {
			return 30;
		}

		return self::is_leap( $jy ) ? 30 : 29;
	}

	/**
	 * Format a timestamp in the Jalali calendar.
	 *
	 * @param string            $format   PHP date() format.
	 * @param int|null          $timestamp Unix timestamp; null for now.
	 * @param DateTimeZone|null $timezone Time zone; null for the site's.
	 * @return string
	 */
	public static function format( $format, $timestamp = null, $timezone = null ) {
		$timezone = $timezone instanceof DateTimeZone ? $timezone : wp_timezone();
		$date     = new DateTimeImmutable( '@' . ( null === $timestamp ? time() : (int) $timestamp ) );
		$date     = $date->setTimezone( $timezone );

		list( $jy, $jm, $jd ) = self::from_gregorian( (int) $date->format( 'Y' ), (int) $date->format( 'n' ), (int) $date->format( 'j' ) );

		$w   = (int) $date->format( 'w' );
		$out = '';
		$len = strlen( $format );

		for ( $i = 0; $i < $len; $i++ ) {
			$char = $format[ $i ];

			if ( '\\' === $char ) {
				++$i;
				$out .= $i < $len ? $format[ $i ] : '';
				continue;
			}

			switch ( $char ) {
				case 'd':
					$out .= sprintf( '%02d', $jd );
					break;
				case 'j':
					$out .= $jd;
					break;
				case 'D':
					$out .= self::DAYS_SHORT[ $w ];
					break;
				case 'l':
					$out .= self::DAYS[ $w ];
					break;
				case 'N':
					// Persian week starts on Saturday: Saturday = 1 … Friday = 7.
					$out .= ( ( $w + 1 ) % 7 ) + 1;
					break;
				case 'w':
					$out .= ( $w + 1 ) % 7;
					break;
				case 'z':
					$out .= ( $jm <= 7 ? ( $jm - 1 ) * 31 : 186 + ( $jm - 7 ) * 30 ) + $jd - 1;
					break;
				case 'S':
					$out .= 'ام';
					break;
				case 'F':
				case 'M':
					$out .= self::MONTHS[ $jm ];
					break;
				case 'm':
					$out .= sprintf( '%02d', $jm );
					break;
				case 'n':
					$out .= $jm;
					break;
				case 't':
					$out .= self::month_days( $jy, $jm );
					break;
				case 'L':
					$out .= self::is_leap( $jy ) ? '1' : '0';
					break;
				case 'o':
				case 'Y':
					$out .= $jy;
					break;
				case 'y':
					$out .= sprintf( '%02d', $jy % 100 );
					break;
				case 'a':
				case 'A':
					$out .= (int) $date->format( 'G' ) < 12 ? 'ق.ظ' : 'ب.ظ';
					break;
				default:
					$out .= ctype_alpha( $char ) ? $date->format( $char ) : $char;
			}
		}

		return $out;
	}
}

/**
 * Format a timestamp in the Jalali calendar (Persian digits follow the site setting).
 *
 * @param string   $format    PHP date() format.
 * @param int|null $timestamp Unix timestamp; null for now.
 * @return string
 */
function samadi_core_jdate( $format, $timestamp = null ) {
	$out = Samadi_Core_Jalali::format( $format, $timestamp );

	return samadi_core_setting( 'jalali_digits' ) ? samadi_core_fa_digits( $out ) : $out;
}
