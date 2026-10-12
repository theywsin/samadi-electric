<?php
/**
 * Widget: Samadi Product Specifications (single product templates).
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Specs table.
 */
class Samadi_EW_Widget_Product_Specs extends Samadi_EW_Widget_Base {

	/** @return string */
	public function get_name() {
		return 'samadi-product-specs';
	}

	/** @return string */
	public function get_title() {
		return __( 'مشخصات فنی محصول صمدی', 'samadi-elementor-widgets' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-product-info';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'مشخصات فنی', 'samadi-elementor-widgets' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'مشخصات فنی', 'samadi-elementor-widgets' ) ) );
		$this->add_control( 'note', array( 'type' => Controls_Manager::RAW_HTML, 'raw' => esc_html__( 'از ویژگی‌های محصول در ووکامرس (به همراه برند، کد کالا، وزن و ابعاد) ساخته می‌شود.', 'samadi-elementor-widgets' ), 'content_classes' => 'elementor-descriptor' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_style', array( 'label' => __( 'جدول', 'samadi-elementor-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'label_bg', array( 'label' => __( 'پس‌زمینه ستون عنوان', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} table.shop_attributes th' => 'background-color: {{VALUE}};' ) ) );
		$this->add_text_style( 'spec_label', __( 'عنوان ویژگی', 'samadi-elementor-widgets' ), 'table.shop_attributes th' );
		$this->add_text_style( 'spec_value', __( 'مقدار', 'samadi-elementor-widgets' ), 'table.shop_attributes td' );
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();
		$this->output( Samadi_EW_Components::specs( array( 'title' => $s['title'] ) ) );
	}
}
