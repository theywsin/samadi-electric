<?php
/**
 * Widget: Samadi Header (account, wishlist, cart count, wholesale button).
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Header tools.
 */
class Samadi_EW_Widget_Header_Tools extends Samadi_EW_Widget_Base {

	/** @return string */
	public function get_name() {
		return 'samadi-header-tools';
	}

	/** @return string */
	public function get_title() {
		return __( 'ابزارهای هدر صمدی', 'samadi-elementor-widgets' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-cart-medium';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'آیتم‌ها', 'samadi-elementor-widgets' ) ) );
		$this->add_control( 'wholesale', array( 'label' => __( 'دکمه خرید عمده', 'samadi-elementor-widgets' ), 'description' => __( 'به صفحه خرید عمده تعریف‌شده در تنظیمات لینک می‌شود.', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'wholesale_label', array( 'label' => __( 'متن دکمه', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'خرید عمده', 'samadi-elementor-widgets' ), 'condition' => array( 'wholesale' => 'yes' ) ) );
		$this->add_control( 'wishlist', array( 'label' => __( 'علاقه‌مندی‌ها (در صورت نصب افزونه)', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'account', array( 'label' => __( 'حساب کاربری', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'cart', array( 'label' => __( 'سبد خرید', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_responsive_control(
			'align',
			array(
				'label'     => __( 'تراز', 'samadi-elementor-widgets' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array( 'title' => __( 'راست', 'samadi-elementor-widgets' ), 'icon' => 'eicon-h-align-right' ),
					'center'     => array( 'title' => __( 'وسط', 'samadi-elementor-widgets' ), 'icon' => 'eicon-h-align-center' ),
					'flex-end'   => array( 'title' => __( 'چپ', 'samadi-elementor-widgets' ), 'icon' => 'eicon-h-align-left' ),
				),
				'selectors' => array( '{{WRAPPER}} .se-ew-header-tools' => 'justify-content: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'section_style', array( 'label' => __( 'آیکون‌ها', 'samadi-elementor-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'icon_color', array( 'label' => __( 'رنگ آیکون', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .se-icon-btn' => 'color: {{VALUE}};' ) ) );
		$this->add_control( 'count_bg', array( 'label' => __( 'پس‌زمینه شمارنده سبد', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .se-cart-count' => 'background-color: {{VALUE}};' ) ) );
		$this->add_control( 'btn_bg', array( 'label' => __( 'پس‌زمینه دکمه خرید عمده', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .se-header__wholesale' => 'background-color: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();
		$this->output(
			Samadi_EW_Components::header_tools(
				array(
					'wholesale'       => 'yes' === $s['wholesale'],
					'wholesale_label' => isset( $s['wholesale_label'] ) ? $s['wholesale_label'] : '',
					'wishlist'        => 'yes' === $s['wishlist'],
					'account'         => 'yes' === $s['account'],
					'cart'            => 'yes' === $s['cart'],
				)
			)
		);
	}
}
