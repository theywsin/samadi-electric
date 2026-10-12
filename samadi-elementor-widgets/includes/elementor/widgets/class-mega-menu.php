<?php
/**
 * Widget: Samadi Mega Menu (product categories).
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Mega menu.
 */
class Samadi_EW_Widget_Mega_Menu extends Samadi_EW_Widget_Base {

	/** @return string */
	public function get_name() {
		return 'samadi-mega-menu';
	}

	/** @return string */
	public function get_title() {
		return __( 'مگامنوی دسته‌بندی صمدی', 'samadi-elementor-widgets' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-mega-menu';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'مگامنو', 'samadi-elementor-widgets' ) ) );
		$this->add_control( 'label', array( 'label' => __( 'عنوان دکمه', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'همه دسته‌بندی‌ها', 'samadi-elementor-widgets' ) ) );
		$this->add_control( 'limit', array( 'label' => __( 'تعداد دسته‌های اصلی', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::NUMBER, 'default' => 12, 'min' => 1, 'max' => 40 ) );
		$this->add_control( 'children', array( 'label' => __( 'تعداد زیردسته برای هر دسته (۰ = بدون زیردسته)', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::NUMBER, 'default' => 8, 'min' => 0, 'max' => 30 ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_style', array( 'label' => __( 'دکمه', 'samadi-elementor-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'btn_bg', array( 'label' => __( 'پس‌زمینه', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .se-mega__toggle' => 'background-color: {{VALUE}};' ) ) );
		$this->add_control( 'btn_color', array( 'label' => __( 'رنگ متن', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .se-mega__toggle' => 'color: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();
		$this->output(
			Samadi_EW_Components::mega_menu(
				array(
					'label'    => $s['label'],
					'limit'    => (int) $s['limit'],
					'children' => (int) $s['children'],
				)
			),
			'se-ew-mega'
		);
	}
}
