<?php
/**
 * Widget: Samadi Product Badges (sale %, featured, out of stock) for the current product.
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Badges.
 */
class Samadi_EW_Widget_Product_Badges extends Samadi_EW_Widget_Base {

	/** @return string */
	public function get_name() {
		return 'samadi-product-badges';
	}

	/** @return string */
	public function get_title() {
		return __( 'نشان‌های محصول صمدی', 'samadi-elementor-widgets' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-price-list';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'section_style', array( 'label' => __( 'نشان‌ها', 'samadi-elementor-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'sale_bg', array( 'label' => __( 'پس‌زمینه تخفیف', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .se-badge--sale' => 'background-color: {{VALUE}};' ) ) );
		$this->add_control( 'featured_bg', array( 'label' => __( 'پس‌زمینه ویژه', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .se-badge--featured' => 'background-color: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$this->output( Samadi_EW_Components::badges() );
	}
}
