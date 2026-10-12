<?php
/**
 * Widget: Samadi Benefits Bar.
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Benefits bar.
 */
class Samadi_EW_Widget_Benefits extends Samadi_EW_Widget_Base {

	/** @return string */
	public function get_name() {
		return 'samadi-benefits';
	}

	/** @return string */
	public function get_title() {
		return __( 'نوار مزایای صمدی', 'samadi-elementor-widgets' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-icon-box';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'section_items', array( 'label' => __( 'مزایا', 'samadi-elementor-widgets' ) ) );

		$repeater = new Repeater();
		$repeater->add_control( 'icon', array( 'label' => __( 'آیکون', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SELECT, 'options' => samadi_ew_icon_choices(), 'default' => 'check' ) );
		$repeater->add_control( 'title', array( 'label' => __( 'عنوان', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::TEXT, 'dynamic' => array( 'active' => true ) ) );
		$repeater->add_control( 'text', array( 'label' => __( 'توضیح', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::TEXT, 'dynamic' => array( 'active' => true ) ) );

		$defaults = array(
			array( 'icon' => 'truck', 'title' => __( 'ارسال سریع', 'samadi-elementor-widgets' ), 'text' => __( 'به سراسر ایران', 'samadi-elementor-widgets' ) ),
			array( 'icon' => 'shield-check', 'title' => __( 'ضمانت اصالت کالا', 'samadi-elementor-widgets' ), 'text' => __( 'از برندهای معتبر', 'samadi-elementor-widgets' ) ),
			array( 'icon' => 'headset', 'title' => __( 'مشاوره تخصصی', 'samadi-elementor-widgets' ), 'text' => __( 'پیش از خرید', 'samadi-elementor-widgets' ) ),
			array( 'icon' => 'badge-percent', 'title' => __( 'قیمت مناسب', 'samadi-elementor-widgets' ), 'text' => __( 'خرید مستقیم', 'samadi-elementor-widgets' ) ),
			array( 'icon' => 'message-circle', 'title' => __( 'پشتیبانی واقعی', 'samadi-elementor-widgets' ), 'text' => __( 'همیشه کنار شما', 'samadi-elementor-widgets' ) ),
		);

		$this->add_control(
			'items',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => $defaults,
			)
		);
		$this->end_controls_section();

		$this->add_box_style( 'section_style_bar', __( 'نوار', 'samadi-elementor-widgets' ), '.se-benefits' );

		$this->start_controls_section( 'section_style_items', array( 'label' => __( 'آیتم‌ها', 'samadi-elementor-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'icon_color', array( 'label' => __( 'رنگ آیکون', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .se-benefits__icon' => 'color: {{VALUE}};' ) ) );
		$this->add_control( 'icon_bg', array( 'label' => __( 'پس‌زمینه آیکون', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .se-benefits__icon' => 'background-color: {{VALUE}};' ) ) );
		$this->add_control( 'hide_divider', array( 'label' => __( 'حذف جداکننده بین آیتم‌ها', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'none', 'selectors' => array( '{{WRAPPER}} .se-benefits__item + .se-benefits__item' => 'border-inline-start-style: {{VALUE}};' ) ) );
		$this->add_text_style( 'b_title', __( 'عنوان', 'samadi-elementor-widgets' ), '.se-benefits__title' );
		$this->add_text_style( 'b_text', __( 'توضیح', 'samadi-elementor-widgets' ), '.se-benefits__text' );
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s     = $this->get_settings_for_display();
		$items = array();
		foreach ( (array) $s['items'] as $item ) {
			$items[] = array(
				'icon'  => isset( $item['icon'] ) ? $item['icon'] : 'check',
				'title' => isset( $item['title'] ) ? $item['title'] : '',
				'text'  => isset( $item['text'] ) ? $item['text'] : '',
			);
		}
		$this->output( Samadi_EW_Components::benefits( array( 'items' => $items ) ) );
	}
}
