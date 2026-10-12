<?php
/**
 * Widget: Samadi Quote Form (wholesale purchase / price inquiry).
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Quote form.
 */
class Samadi_EW_Widget_Quote_Form extends Samadi_EW_Widget_Base {

	/** @return string */
	public function get_name() {
		return 'samadi-quote-form';
	}

	/** @return string */
	public function get_title() {
		return __( 'فرم استعلام قیمت صمدی', 'samadi-elementor-widgets' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'فرم', 'samadi-elementor-widgets' ) ) );
		$this->add_control( 'note', array( 'type' => Controls_Manager::RAW_HTML, 'raw' => esc_html__( 'درخواست‌ها در «ووکامرس ← درخواست‌های استعلام» ذخیره و به ایمیل تنظیم‌شده ارسال می‌شوند. این صفحه را در تنظیمات صمدی به‌عنوان صفحه خرید عمده انتخاب کنید و از کش صفحه مستثنا کنید.', 'samadi-elementor-widgets' ), 'content_classes' => 'elementor-descriptor' ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'فرم درخواست خرید عمده و استعلام قیمت', 'samadi-elementor-widgets' ), 'label_block' => true ) );
		$this->add_control( 'intro', array( 'label' => __( 'توضیح', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::TEXTAREA, 'default' => __( 'فهرست کالاهای موردنیاز را وارد کنید؛ کارشناسان فروش در اولین فرصت با شما تماس می‌گیرند.', 'samadi-elementor-widgets' ) ) );
		$this->add_control( 'button', array( 'label' => __( 'متن دکمه ارسال', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'ثبت درخواست', 'samadi-elementor-widgets' ) ) );
		$this->end_controls_section();

		$this->add_box_style( 'section_style_box', __( 'کادر فرم', 'samadi-elementor-widgets' ), '.se-quote' );
		$this->start_controls_section( 'section_style_btn', array( 'label' => __( 'دکمه', 'samadi-elementor-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'btn_bg', array( 'label' => __( 'پس‌زمینه', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .se-quote__form button[type=submit]' => 'background-color: {{VALUE}};' ) ) );
		$this->add_control( 'btn_color', array( 'label' => __( 'رنگ متن', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .se-quote__form button[type=submit]' => 'color: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();
		$this->output(
			Samadi_EW_Quote::render_form(
				array(
					'title'  => $s['title'],
					'intro'  => $s['intro'],
					'button' => $s['button'],
				)
			)
		);
	}
}
