<?php
/**
 * Widget: Samadi Quote Button (price inquiry for the current product).
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Quote button.
 */
class Samadi_EW_Widget_Quote_Button extends Samadi_EW_Widget_Base {

	/** @return string */
	public function get_name() {
		return 'samadi-quote-button';
	}

	/** @return string */
	public function get_title() {
		return __( 'دکمه استعلام قیمت صمدی', 'samadi-elementor-widgets' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-button';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'دکمه', 'samadi-elementor-widgets' ) ) );
		$this->add_control( 'label', array( 'label' => __( 'متن', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::TEXT, 'default' => (string) samadi_ew_setting( 'quote_button_label' ) ) );
		$this->end_controls_section();

		$this->add_box_style( 'section_style_btn', __( 'دکمه', 'samadi-elementor-widgets' ), '.se-quote-btn' );
		$this->start_controls_section( 'section_style_text', array( 'label' => __( 'متن', 'samadi-elementor-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_text_style( 'btn_text', __( 'متن دکمه', 'samadi-elementor-widgets' ), '.se-quote-btn' );
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();
		$this->output( Samadi_EW_Components::quote_button( array( 'label' => $s['label'] ) ) );
	}
}
