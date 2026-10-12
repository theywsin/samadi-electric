<?php
/**
 * Widget: Samadi Search (product search with live suggestions).
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Search.
 */
class Samadi_EW_Widget_Search extends Samadi_EW_Widget_Base {

	/** @return string */
	public function get_name() {
		return 'samadi-search';
	}

	/** @return string */
	public function get_title() {
		return __( 'جستجوی محصولات صمدی', 'samadi-elementor-widgets' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-search';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'جستجو', 'samadi-elementor-widgets' ) ) );
		$this->add_control( 'placeholder', array( 'label' => __( 'متن راهنما', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'جستجوی محصول، برند یا کد کالا…', 'samadi-elementor-widgets' ), 'label_block' => true ) );
		$this->add_control( 'live_note', array( 'type' => Controls_Manager::RAW_HTML, 'raw' => esc_html__( 'پیشنهادهای لحظه‌ای از «ووکامرس ← تنظیمات صمدی» فعال/غیرفعال می‌شوند.', 'samadi-elementor-widgets' ), 'content_classes' => 'elementor-descriptor' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_style', array( 'label' => __( 'کادر جستجو', 'samadi-elementor-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'height', array( 'label' => __( 'ارتفاع', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 36, 'max' => 72 ) ), 'selectors' => array( '{{WRAPPER}} .se-search-form__input' => 'height: {{SIZE}}{{UNIT}} !important;' ) ) );
		$this->add_control( 'bg', array( 'label' => __( 'پس‌زمینه', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .se-search-form__input' => 'background-color: {{VALUE}} !important;' ) ) );
		$this->add_control( 'border_color', array( 'label' => __( 'رنگ حاشیه', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .se-search-form__input' => 'border-color: {{VALUE}} !important;' ) ) );
		$this->add_responsive_control( 'radius', array( 'label' => __( 'گردی گوشه‌ها', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 40 ) ), 'selectors' => array( '{{WRAPPER}} .se-search-form__input' => 'border-radius: {{SIZE}}{{UNIT}} !important;' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();
		$this->output( Samadi_EW_Components::search( array( 'placeholder' => $s['placeholder'] ) ) );
	}
}
