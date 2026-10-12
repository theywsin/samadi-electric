<?php
/**
 * Widget: Samadi Product Filters (for Elementor Pro product archive templates).
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Filters.
 */
class Samadi_EW_Widget_Product_Filters extends Samadi_EW_Widget_Base {

	/** @return string */
	public function get_name() {
		return 'samadi-product-filters';
	}

	/** @return string */
	public function get_title() {
		return __( 'فیلتر محصولات صمدی', 'samadi-elementor-widgets' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-filter';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$attributes = array();
		foreach ( wc_get_attribute_taxonomies() as $attribute ) {
			$attributes[ wc_attribute_taxonomy_name( $attribute->attribute_name ) ] = $attribute->attribute_label;
		}

		$this->start_controls_section( 'section_content', array( 'label' => __( 'فیلترها', 'samadi-elementor-widgets' ) ) );
		$this->add_control( 'note', array( 'type' => Controls_Manager::RAW_HTML, 'raw' => esc_html__( 'در قالب آرشیو محصولات استفاده کنید. فقط فیلترهایی نمایش داده می‌شوند که برای محصولات دسته جاری کاربرد دارند.', 'samadi-elementor-widgets' ), 'content_classes' => 'elementor-descriptor' ) );
		$this->add_control( 'price', array( 'label' => __( 'قیمت', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'stock', array( 'label' => __( 'موجودی', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'brand', array( 'label' => __( 'برند', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control(
			'attributes',
			array(
				'label'       => __( 'ویژگی‌ها (خالی = همه ویژگی‌های مرتبط)', 'samadi-elementor-widgets' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $attributes,
			)
		);
		$this->add_control( 'collapse', array( 'label' => __( 'نمایش «بیشتر» پس از چند گزینه', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::NUMBER, 'default' => 8, 'min' => 0, 'max' => 50 ) );
		$this->end_controls_section();

		$this->add_box_style( 'section_style_box', __( 'کادر', 'samadi-elementor-widgets' ), '.se-filter-form' );
		$this->start_controls_section( 'section_style_text', array( 'label' => __( 'متن', 'samadi-elementor-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_text_style( 'group_title', __( 'عنوان گروه', 'samadi-elementor-widgets' ), '.se-filter-group__title' );
		$this->add_text_style( 'option', __( 'گزینه‌ها', 'samadi-elementor-widgets' ), '.se-filter-option' );
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();
		$this->output(
			Samadi_EW_Filters::render_form(
				array(
					'price'      => 'yes' === $s['price'],
					'stock'      => 'yes' === $s['stock'],
					'brand'      => 'yes' === $s['brand'],
					'attributes' => isset( $s['attributes'] ) ? (array) $s['attributes'] : array(),
					'collapse'   => (int) $s['collapse'],
				)
			)
		);
	}
}
