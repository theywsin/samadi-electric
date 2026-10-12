<?php
/**
 * Widget: Samadi Floating Mobile Navigation.
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Mobile bottom navigation.
 */
class Samadi_EW_Widget_Mobile_Nav extends Samadi_EW_Widget_Base {

	/** @return string */
	public function get_name() {
		return 'samadi-mobile-nav';
	}

	/** @return string */
	public function get_title() {
		return __( 'نوار ناوبری موبایل صمدی', 'samadi-elementor-widgets' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-nav-menu';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'آیتم‌ها', 'samadi-elementor-widgets' ) ) );
		$this->add_control( 'note', array( 'type' => Controls_Manager::RAW_HTML, 'raw' => esc_html__( 'قالب صمدی الکتریک این نوار را خودش نمایش می‌دهد؛ این ویجت برای زمانی است که آن را در «سفارشی‌سازی ← موبایل» خاموش کرده‌اید یا از قالب دیگری استفاده می‌کنید. فقط در عرض موبایل/تبلت دیده می‌شود.', 'samadi-elementor-widgets' ), 'content_classes' => 'elementor-descriptor' ) );
		$this->add_control(
			'items',
			array(
				'label'    => __( 'آیتم‌ها', 'samadi-elementor-widgets' ),
				'type'     => Controls_Manager::SELECT2,
				'multiple' => true,
				'default'  => array( 'home', 'shop', 'cart', 'wishlist', 'account' ),
				'options'  => array(
					'home'     => __( 'خانه', 'samadi-elementor-widgets' ),
					'shop'     => __( 'دسته‌بندی‌ها / فروشگاه', 'samadi-elementor-widgets' ),
					'cart'     => __( 'سبد خرید', 'samadi-elementor-widgets' ),
					'wishlist' => __( 'علاقه‌مندی‌ها (در صورت نصب افزونه)', 'samadi-elementor-widgets' ),
					'account'  => __( 'حساب کاربری', 'samadi-elementor-widgets' ),
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'section_style', array( 'label' => __( 'نوار', 'samadi-elementor-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'bg', array( 'label' => __( 'پس‌زمینه', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .se-bottom-nav' => 'background-color: {{VALUE}};' ) ) );
		$this->add_control( 'color', array( 'label' => __( 'رنگ آیتم‌ها', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .se-bottom-nav__link' => 'color: {{VALUE}};' ) ) );
		$this->add_control( 'active', array( 'label' => __( 'رنگ آیتم فعال', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .se-bottom-nav__link.is-active' => 'color: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();
		$this->output( Samadi_EW_Components::mobile_nav( array( 'items' => (array) $s['items'] ) ), 'se-ew-mobile-nav' );
	}
}
