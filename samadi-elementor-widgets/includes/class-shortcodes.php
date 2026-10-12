<?php
/**
 * Shortcodes — every component is usable without Elementor too.
 *
 * [samadi_products source="sale|recent|featured|best_selling|top_rated|category|manual|related" limit="8" columns="4" layout="grid|carousel" title="" link="" category="12,14" ids="1,2"]
 * [samadi_categories source="top|manual|children" ids="" limit="8" title="" show_count="no" layout="grid|carousel"]
 * [samadi_brands limit="12" title="" layout="grid|carousel"]
 * [samadi_benefits]  (uses the theme's benefits settings when the Samadi theme is active)
 * [samadi_search placeholder=""]
 * [samadi_filters price="yes" stock="yes" brand="yes"]
 * [samadi_quote_form title="" intro="" button=""]
 * [samadi_quote_button label=""]
 * [samadi_recently_viewed title="" limit="8" columns="4"]
 * [samadi_flash_sale title="" end="2026-12-31 23:59" limit="8"]
 * [samadi_specs]
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

/**
 * Shortcodes.
 */
class Samadi_EW_Shortcodes {

	/**
	 * Register.
	 */
	public static function init() {
		$map = array(
			'samadi_products'        => 'products',
			'samadi_categories'      => 'categories',
			'samadi_brands'          => 'brands',
			'samadi_benefits'        => 'benefits',
			'samadi_search'          => 'search',
			'samadi_filters'         => 'filters',
			'samadi_quote_form'      => 'quote_form',
			'samadi_quote_button'    => 'quote_button',
			'samadi_recently_viewed' => 'recently_viewed',
			'samadi_flash_sale'      => 'flash_sale',
			'samadi_specs'           => 'specs',
		);

		foreach ( $map as $tag => $method ) {
			add_shortcode( $tag, array( __CLASS__, $method ) );
		}
	}

	/**
	 * "yes"/"true"/"1" → bool.
	 *
	 * @param mixed $value Value.
	 * @return bool
	 */
	private static function yes( $value ) {
		return in_array( strtolower( (string) $value ), array( '1', 'yes', 'true', 'on' ), true );
	}

	/**
	 * [samadi_products].
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public static function products( $atts ) {
		$atts = shortcode_atts(
			array(
				'source'    => 'recent',
				'limit'     => 8,
				'columns'   => 4,
				'layout'    => 'grid',
				'title'     => '',
				'link'      => '',
				'link_text' => '',
				'category'  => '',
				'ids'       => '',
				'orderby'   => '',
				'order'     => 'DESC',
			),
			$atts,
			'samadi_products'
		);

		return '<div class="se-ew">' . Samadi_EW_Components::products(
			array(
				'source'     => sanitize_key( $atts['source'] ),
				'limit'      => absint( $atts['limit'] ),
				'columns'    => absint( $atts['columns'] ),
				'layout'     => sanitize_key( $atts['layout'] ),
				'title'      => sanitize_text_field( $atts['title'] ),
				'link'       => esc_url_raw( $atts['link'] ),
				'link_text'  => sanitize_text_field( $atts['link_text'] ),
				'categories' => wp_parse_id_list( $atts['category'] ),
				'ids'        => wp_parse_id_list( $atts['ids'] ),
				'orderby'    => sanitize_key( $atts['orderby'] ),
				'order'      => sanitize_key( $atts['order'] ),
			)
		) . '</div>';
	}

	/**
	 * [samadi_categories].
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public static function categories( $atts ) {
		$atts = shortcode_atts(
			array(
				'source'     => 'top',
				'ids'        => '',
				'limit'      => 8,
				'title'      => '',
				'link'       => '',
				'show_count' => 'no',
				'layout'     => 'grid',
			),
			$atts,
			'samadi_categories'
		);

		return '<div class="se-ew">' . Samadi_EW_Components::categories(
			array(
				'source'     => sanitize_key( $atts['source'] ),
				'ids'        => wp_parse_id_list( $atts['ids'] ),
				'limit'      => absint( $atts['limit'] ),
				'title'      => sanitize_text_field( $atts['title'] ),
				'link'       => esc_url_raw( $atts['link'] ),
				'show_count' => self::yes( $atts['show_count'] ),
				'layout'     => sanitize_key( $atts['layout'] ),
			)
		) . '</div>';
	}

	/**
	 * [samadi_brands].
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public static function brands( $atts ) {
		$atts = shortcode_atts(
			array(
				'limit'  => 12,
				'title'  => '',
				'layout' => 'grid',
				'ids'    => '',
			),
			$atts,
			'samadi_brands'
		);

		return '<div class="se-ew">' . Samadi_EW_Components::brands(
			array(
				'limit'  => absint( $atts['limit'] ),
				'title'  => sanitize_text_field( $atts['title'] ),
				'layout' => sanitize_key( $atts['layout'] ),
				'ids'    => wp_parse_id_list( $atts['ids'] ),
			)
		) . '</div>';
	}

	/**
	 * [samadi_benefits] — theme settings when available.
	 *
	 * @return string
	 */
	public static function benefits() {
		$items = function_exists( 'samadi_benefit_items' ) ? samadi_benefit_items() : array();

		return '<div class="se-ew">' . Samadi_EW_Components::benefits( array( 'items' => $items ) ) . '</div>';
	}

	/**
	 * [samadi_search].
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public static function search( $atts ) {
		$atts = shortcode_atts( array( 'placeholder' => __( 'جستجوی محصول، برند یا کد کالا…', 'samadi-elementor-widgets' ) ), $atts, 'samadi_search' );

		return Samadi_EW_Components::search( array( 'placeholder' => sanitize_text_field( $atts['placeholder'] ) ) );
	}

	/**
	 * [samadi_filters].
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public static function filters( $atts ) {
		$atts = shortcode_atts(
			array(
				'price' => 'yes',
				'stock' => 'yes',
				'brand' => 'yes',
			),
			$atts,
			'samadi_filters'
		);

		return Samadi_EW_Filters::render_form(
			array(
				'price' => self::yes( $atts['price'] ),
				'stock' => self::yes( $atts['stock'] ),
				'brand' => self::yes( $atts['brand'] ),
			)
		);
	}

	/**
	 * [samadi_quote_form].
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public static function quote_form( $atts ) {
		$atts = shortcode_atts(
			array(
				'title'  => __( 'فرم درخواست خرید عمده و استعلام قیمت', 'samadi-elementor-widgets' ),
				'intro'  => __( 'فهرست کالاهای موردنیاز را وارد کنید؛ کارشناسان فروش در اولین فرصت با شما تماس می‌گیرند.', 'samadi-elementor-widgets' ),
				'button' => __( 'ثبت درخواست', 'samadi-elementor-widgets' ),
			),
			$atts,
			'samadi_quote_form'
		);

		return Samadi_EW_Quote::render_form( array_map( 'sanitize_text_field', $atts ) );
	}

	/**
	 * [samadi_quote_button].
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public static function quote_button( $atts ) {
		$atts = shortcode_atts( array( 'label' => samadi_ew_setting( 'quote_button_label' ) ), $atts, 'samadi_quote_button' );

		return Samadi_EW_Components::quote_button( array( 'label' => sanitize_text_field( $atts['label'] ) ) );
	}

	/**
	 * [samadi_recently_viewed].
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public static function recently_viewed( $atts ) {
		$atts = shortcode_atts(
			array(
				'title'   => __( 'بازدیدهای اخیر شما', 'samadi-elementor-widgets' ),
				'limit'   => samadi_ew_setting( 'recently_viewed_limit' ),
				'columns' => 4,
				'layout'  => 'carousel',
			),
			$atts,
			'samadi_recently_viewed'
		);

		return Samadi_EW_Recently_Viewed::render(
			array(
				'title'   => sanitize_text_field( $atts['title'] ),
				'limit'   => absint( $atts['limit'] ),
				'columns' => absint( $atts['columns'] ),
				'layout'  => sanitize_key( $atts['layout'] ),
			)
		);
	}

	/**
	 * [samadi_flash_sale].
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public static function flash_sale( $atts ) {
		$atts = shortcode_atts(
			array(
				'title' => __( 'پیشنهاد شگفت‌انگیز', 'samadi-elementor-widgets' ),
				'end'   => '',
				'limit' => 8,
			),
			$atts,
			'samadi_flash_sale'
		);

		return '<div class="se-ew">' . Samadi_EW_Components::flash_sale(
			array(
				'title'    => sanitize_text_field( $atts['title'] ),
				'end'      => sanitize_text_field( $atts['end'] ),
				'products' => array( 'limit' => absint( $atts['limit'] ) ),
			)
		) . '</div>';
	}

	/**
	 * [samadi_specs].
	 *
	 * @return string
	 */
	public static function specs() {
		return Samadi_EW_Components::specs();
	}
}
