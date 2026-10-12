<?php
/**
 * Widget: Samadi Contact Info (from the theme's Customizer contact settings).
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Contact info.
 */
class Samadi_EW_Widget_Contact extends Samadi_EW_Widget_Base {

	/** @return string */
	public function get_name() {
		return 'samadi-contact';
	}

	/** @return string */
	public function get_title() {
		return __( 'اطلاعات تماس صمدی', 'samadi-elementor-widgets' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-call-to-action';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'اطلاعات تماس', 'samadi-elementor-widgets' ) ) );
		$this->add_control( 'note', array( 'type' => Controls_Manager::RAW_HTML, 'raw' => esc_html__( 'تلفن، ایمیل، آدرس و ساعات کاری از تنظیمات قالب (سفارشی‌سازی ← اطلاعات تماس) خوانده می‌شوند. موارد خالی نمایش داده نمی‌شوند.', 'samadi-elementor-widgets' ), 'content_classes' => 'elementor-descriptor' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_style', array( 'label' => __( 'متن', 'samadi-elementor-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'icon_color', array( 'label' => __( 'رنگ آیکون', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .se-contact__item svg' => 'color: {{VALUE}};' ) ) );
		$this->add_text_style( 'contact_text', __( 'متن', 'samadi-elementor-widgets' ), '.se-contact__item, {{WRAPPER}} .se-contact__item a' );
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$this->output( Samadi_EW_Components::contact() );
	}
}
