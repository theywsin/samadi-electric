<?php
/**
 * Widget: Samadi Flash Sale (countdown + on-sale products).
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

require_once __DIR__ . '/class-product-grid.php';

/**
 * Flash sale.
 */
class Samadi_EW_Widget_Flash_Sale extends Samadi_EW_Widget_Product_Grid {

	/** @return string */
	public function get_name() {
		return 'samadi-flash-sale';
	}

	/** @return string */
	public function get_title() {
		return __( 'فروش ویژه زمان‌دار صمدی', 'samadi-elementor-widgets' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-countdown';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'فروش ویژه', 'samadi-elementor-widgets' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'پیشنهاد شگفت‌انگیز', 'samadi-elementor-widgets' ), 'label_block' => true ) );
		$this->add_control(
			'end',
			array(
				'label'       => __( 'زمان پایان', 'samadi-elementor-widgets' ),
				'description' => __( 'به وقت تنظیمات سایت. شمارش معکوس تا این زمان نمایش داده می‌شود.', 'samadi-elementor-widgets' ),
				'type'        => Controls_Manager::DATE_TIME,
			)
		);
		$this->add_control(
			'expired',
			array(
				'label'   => __( 'پس از پایان زمان', 'samadi-elementor-widgets' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'hide',
				'options' => array(
					'hide' => __( 'بخش پنهان شود', 'samadi-elementor-widgets' ),
					'show' => __( 'محصولات نمایش داده شوند', 'samadi-elementor-widgets' ),
				),
			)
		);
		$this->add_query_controls( 'sale' );
		$this->add_columns_control( '.se-products ul.products', 4, 3, 2 );
		$this->end_controls_section();

		$this->add_box_style( 'section_style_flash', __( 'کادر', 'samadi-elementor-widgets' ), '.se-flash' );
		$this->start_controls_section( 'section_style_timer', array( 'label' => __( 'شمارنده', 'samadi-elementor-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'timer_bg', array( 'label' => __( 'پس‌زمینه', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .se-countdown__unit' => 'background-color: {{VALUE}};' ) ) );
		$this->add_control( 'timer_color', array( 'label' => __( 'رنگ', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .se-countdown__unit' => 'color: {{VALUE}};' ) ) );
		$this->add_text_style( 'flash_title', __( 'عنوان', 'samadi-elementor-widgets' ), '.se-flash__title' );
		$this->end_controls_section();
		$this->add_card_style();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s        = $this->get_settings_for_display();
		$products = $this->query_args( $s );

		$products['layout'] = 'carousel';

		$this->output(
			Samadi_EW_Components::flash_sale(
				array(
					'title'    => $s['title'],
					'end'      => $s['end'],
					'expired'  => $s['expired'],
					'products' => $products,
				)
			)
		);
	}
}
