<?php
/**
 * Widget: Samadi Recently Viewed Products.
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Recently viewed.
 */
class Samadi_EW_Widget_Recently_Viewed extends Samadi_EW_Widget_Base {

	/** @return string */
	public function get_name() {
		return 'samadi-recently-viewed';
	}

	/** @return string */
	public function get_title() {
		return __( 'محصولات اخیراً مشاهده‌شده صمدی', 'samadi-elementor-widgets' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-clock-o';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'محصولات اخیر', 'samadi-elementor-widgets' ) ) );
		$this->add_control( 'note', array( 'type' => Controls_Manager::RAW_HTML, 'raw' => esc_html__( 'فهرست در مرورگر بازدیدکننده نگهداری می‌شود و برای بازدیدکننده‌ای که محصولی ندیده است نمایش داده نمی‌شود (در ویرایشگر هم خالی است).', 'samadi-elementor-widgets' ), 'content_classes' => 'elementor-descriptor' ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'بازدیدهای اخیر شما', 'samadi-elementor-widgets' ) ) );
		$this->add_control( 'limit', array( 'label' => __( 'تعداد', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::NUMBER, 'default' => 8, 'min' => 1, 'max' => 20 ) );
		$this->add_control(
			'layout',
			array(
				'label'   => __( 'چیدمان', 'samadi-elementor-widgets' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'carousel',
				'options' => array(
					'grid'     => __( 'شبکه‌ای', 'samadi-elementor-widgets' ),
					'carousel' => __( 'اسلایدر افقی', 'samadi-elementor-widgets' ),
				),
			)
		);
		$this->add_columns_control( '.se-products ul.products', 4, 3, 2 );
		$this->end_controls_section();
		$this->add_heading_style();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();
		$this->output(
			Samadi_EW_Recently_Viewed::render(
				array(
					'title'   => $s['title'],
					'limit'   => (int) $s['limit'],
					'columns' => isset( $s['columns'] ) ? (int) $s['columns'] : 4,
					'layout'  => $s['layout'],
				)
			)
		);
	}
}
