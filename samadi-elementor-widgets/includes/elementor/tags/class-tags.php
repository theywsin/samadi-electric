<?php
/**
 * Elementor dynamic tags (usable where Elementor Pro exposes the dynamic tags UI).
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module as TagsModule;

/**
 * Store contact info from the theme Customizer (phone, mobile, email, address, hours).
 */
class Samadi_EW_Tag_Store_Info extends Tag {

	/** @return string */
	public function get_name() {
		return 'samadi-store-info';
	}

	/** @return string */
	public function get_title() {
		return __( 'اطلاعات فروشگاه', 'samadi-elementor-widgets' );
	}

	/** @return string */
	public function get_group() {
		return 'samadi';
	}

	/** @return string[] */
	public function get_categories() {
		return array( TagsModule::TEXT_CATEGORY );
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->add_control(
			'field',
			array(
				'label'   => __( 'فیلد', 'samadi-elementor-widgets' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'contact_phone',
				'options' => array(
					'contact_phone'   => __( 'تلفن ثابت', 'samadi-elementor-widgets' ),
					'contact_mobile'  => __( 'تلفن همراه', 'samadi-elementor-widgets' ),
					'contact_email'   => __( 'ایمیل', 'samadi-elementor-widgets' ),
					'contact_address' => __( 'آدرس', 'samadi-elementor-widgets' ),
					'contact_hours'   => __( 'ساعات کاری', 'samadi-elementor-widgets' ),
				),
			)
		);
	}

	/**
	 * Render.
	 */
	public function render() {
		$field = (string) $this->get_settings( 'field' );
		$value = function_exists( 'samadi_option' ) ? (string) samadi_option( $field ) : '';
		echo esc_html( $value );
	}
}

/**
 * Current product brand name.
 */
class Samadi_EW_Tag_Product_Brand extends Tag {

	/** @return string */
	public function get_name() {
		return 'samadi-product-brand';
	}

	/** @return string */
	public function get_title() {
		return __( 'برند محصول', 'samadi-elementor-widgets' );
	}

	/** @return string */
	public function get_group() {
		return 'samadi';
	}

	/** @return string[] */
	public function get_categories() {
		return array( TagsModule::TEXT_CATEGORY );
	}

	/**
	 * Render.
	 */
	public function render() {
		$taxonomy = samadi_ew_brand_taxonomy();
		if ( ! $taxonomy ) {
			return;
		}
		$terms = get_the_terms( get_the_ID(), $taxonomy );
		if ( $terms && ! is_wp_error( $terms ) ) {
			echo esc_html( reset( $terms )->name );
		}
	}
}

/**
 * Current product SKU.
 */
class Samadi_EW_Tag_Product_Sku extends Tag {

	/** @return string */
	public function get_name() {
		return 'samadi-product-sku';
	}

	/** @return string */
	public function get_title() {
		return __( 'کد کالا (SKU)', 'samadi-elementor-widgets' );
	}

	/** @return string */
	public function get_group() {
		return 'samadi';
	}

	/** @return string[] */
	public function get_categories() {
		return array( TagsModule::TEXT_CATEGORY );
	}

	/**
	 * Render.
	 */
	public function render() {
		$product = wc_get_product( get_the_ID() );
		if ( $product && $product->get_sku() ) {
			echo esc_html( $product->get_sku() );
		}
	}
}

/**
 * Price inquiry URL for the current product.
 */
class Samadi_EW_Tag_Quote_Url extends Data_Tag {

	/** @return string */
	public function get_name() {
		return 'samadi-quote-url';
	}

	/** @return string */
	public function get_title() {
		return __( 'لینک استعلام قیمت محصول', 'samadi-elementor-widgets' );
	}

	/** @return string */
	public function get_group() {
		return 'samadi';
	}

	/** @return string[] */
	public function get_categories() {
		return array( TagsModule::URL_CATEGORY );
	}

	/**
	 * Value.
	 *
	 * @param array $options Options.
	 * @return string
	 */
	public function get_value( array $options = array() ) {
		return samadi_ew_quote_url( 'product' === get_post_type() ? get_the_ID() : 0 );
	}
}

/**
 * Wholesale page URL.
 */
class Samadi_EW_Tag_Wholesale_Url extends Data_Tag {

	/** @return string */
	public function get_name() {
		return 'samadi-wholesale-url';
	}

	/** @return string */
	public function get_title() {
		return __( 'لینک صفحه خرید عمده', 'samadi-elementor-widgets' );
	}

	/** @return string */
	public function get_group() {
		return 'samadi';
	}

	/** @return string[] */
	public function get_categories() {
		return array( TagsModule::URL_CATEGORY );
	}

	/**
	 * Value.
	 *
	 * @param array $options Options.
	 * @return string
	 */
	public function get_value( array $options = array() ) {
		return preg_replace( '/#.*$/', '', samadi_ew_quote_url() );
	}
}
