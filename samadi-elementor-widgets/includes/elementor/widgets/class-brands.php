<?php
/**
 * Widget: Samadi Brand Carousel.
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Brands.
 */
class Samadi_EW_Widget_Brands extends Samadi_EW_Widget_Base {

	/** @return string */
	public function get_name() {
		return 'samadi-brands';
	}

	/** @return string */
	public function get_title() {
		return __( 'برندهای صمدی', 'samadi-elementor-widgets' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-logo';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'برندها', 'samadi-elementor-widgets' ) ) );
		$this->add_heading_controls( __( 'برندهای معتبر', 'samadi-elementor-widgets' ) );
		$this->add_control(
			'ids',
			array(
				'label'       => __( 'برندهای منتخب (خالی = پرکالاترین برندها)', 'samadi-elementor-widgets' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->brand_options(),
				'separator'   => 'before',
			)
		);
		$this->add_control( 'limit', array( 'label' => __( 'حداکثر تعداد', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::NUMBER, 'default' => 12, 'min' => 1, 'max' => 60 ) );
		$this->add_control( 'show_name', array( 'label' => __( 'نمایش نام زیر لوگو', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SWITCHER ) );
		$this->add_control(
			'layout',
			array(
				'label'   => __( 'چیدمان', 'samadi-elementor-widgets' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => array(
					'grid'     => __( 'شبکه‌ای', 'samadi-elementor-widgets' ),
					'carousel' => __( 'اسلایدر افقی', 'samadi-elementor-widgets' ),
				),
			)
		);
		$this->add_columns_control( '.se-brand-strip', 7, 4, 3 );
		$this->end_controls_section();

		$this->add_heading_style();
		$this->add_box_style( 'section_style_logo', __( 'کادر لوگو', 'samadi-elementor-widgets' ), '.se-brand-strip__link' );
		$this->start_controls_section( 'section_style_img', array( 'label' => __( 'لوگو', 'samadi-elementor-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'logo_height', array( 'label' => __( 'حداکثر ارتفاع لوگو', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 20, 'max' => 160 ) ), 'selectors' => array( '{{WRAPPER}} .se-brand-strip__link img' => 'max-height: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'grayscale', array( 'label' => __( 'خاکستری (رنگی هنگام هاور)', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'grayscale(1)', 'selectors' => array( '{{WRAPPER}} .se-brand-strip__link img' => 'filter: {{VALUE}};', '{{WRAPPER}} .se-brand-strip__link:hover img' => 'filter: none;' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();
		$this->output(
			Samadi_EW_Components::brands(
				array(
					'ids'       => isset( $s['ids'] ) ? (array) $s['ids'] : array(),
					'limit'     => (int) $s['limit'],
					'layout'    => $s['layout'],
					'title'     => $s['title'],
					'title_tag' => $s['title_tag'],
					'link'      => $this->url( $s['link'] ),
					'link_text' => $s['link_text'],
					'show_name' => 'yes' === $s['show_name'],
				)
			)
		);
	}
}
