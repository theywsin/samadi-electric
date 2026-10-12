<?php
/**
 * Widget: Samadi Promotional Banner (hero slider).
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Hero / promotional banner.
 */
class Samadi_EW_Widget_Hero extends Samadi_EW_Widget_Base {

	/** @return string */
	public function get_name() {
		return 'samadi-hero';
	}

	/** @return string */
	public function get_title() {
		return __( 'بنر تبلیغاتی صمدی', 'samadi-elementor-widgets' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-slider-push';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'section_slides', array( 'label' => __( 'اسلایدها', 'samadi-elementor-widgets' ) ) );

		$repeater = new Repeater();
		$repeater->add_control( 'image', array( 'label' => __( 'تصویر', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::MEDIA, 'dynamic' => array( 'active' => true ) ) );
		$repeater->add_control( 'title', array( 'label' => __( 'عنوان', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2, 'dynamic' => array( 'active' => true ) ) );
		$repeater->add_control( 'text', array( 'label' => __( 'متن', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3, 'dynamic' => array( 'active' => true ) ) );
		$repeater->add_control( 'btn1_text', array( 'label' => __( 'متن دکمه اصلی', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::TEXT ) );
		$repeater->add_control( 'btn1_url', array( 'label' => __( 'لینک دکمه اصلی', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::URL, 'dynamic' => array( 'active' => true ) ) );
		$repeater->add_control( 'btn2_text', array( 'label' => __( 'متن دکمه دوم', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::TEXT ) );
		$repeater->add_control( 'btn2_url', array( 'label' => __( 'لینک دکمه دوم', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::URL, 'dynamic' => array( 'active' => true ) ) );
		$repeater->add_control(
			'align',
			array(
				'label'   => __( 'موقعیت محتوا', 'samadi-elementor-widgets' ),
				'type'    => Controls_Manager::CHOOSE,
				'default' => 'start',
				'options' => array(
					'start'  => array( 'title' => __( 'راست', 'samadi-elementor-widgets' ), 'icon' => 'eicon-h-align-right' ),
					'center' => array( 'title' => __( 'وسط', 'samadi-elementor-widgets' ), 'icon' => 'eicon-h-align-center' ),
					'end'    => array( 'title' => __( 'چپ', 'samadi-elementor-widgets' ), 'icon' => 'eicon-h-align-left' ),
				),
			)
		);

		$this->add_control(
			'slides',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array(
						'title'     => __( 'تجهیزات الکتریکی باکیفیت، برای زندگی بهتر', 'samadi-elementor-widgets' ),
						'text'      => __( 'صمدی الکتریک، همراه مطمئن شما در تأمین کلیه لوازم برق ساختمان و صنعت.', 'samadi-elementor-widgets' ),
						'btn1_text' => __( 'مشاهده محصولات', 'samadi-elementor-widgets' ),
						'btn1_url'  => array( 'url' => wc_get_page_permalink( 'shop' ) ),
						'btn2_text' => __( 'خرید عمده', 'samadi-elementor-widgets' ),
						'btn2_url'  => array( 'url' => preg_replace( '/#.*$/', '', samadi_ew_quote_url() ) ),
						'align'     => 'start',
					),
				),
			)
		);
		$this->add_control( 'autoplay', array( 'label' => __( 'پخش خودکار', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'interval', array( 'label' => __( 'فاصله زمانی (ثانیه)', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::NUMBER, 'default' => 6, 'min' => 3, 'max' => 20, 'condition' => array( 'autoplay' => 'yes' ) ) );
		$this->add_control(
			'title_tag',
			array(
				'label'   => __( 'تگ عنوان', 'samadi-elementor-widgets' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h2',
				'options' => array( 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'p' => 'p' ),
			)
		);
		$this->add_control( 'eager', array( 'label' => __( 'تصویر اول در بالای صفحه است (LCP)', 'samadi-elementor-widgets' ), 'description' => __( 'تصویر اسلاید اول بدون تأخیر و با اولویت بالا بارگذاری می‌شود.', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_style_hero', array( 'label' => __( 'بنر', 'samadi-elementor-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control(
			'height',
			array(
				'label'      => __( 'حداقل ارتفاع', 'samadi-elementor-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array( 'px' => array( 'min' => 160, 'max' => 900 ) ),
				'selectors'  => array( '{{WRAPPER}} .se-hero' => 'min-height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control( 'hero_bg', array( 'label' => __( 'رنگ پس‌زمینه', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .se-hero' => 'background: {{VALUE}};' ) ) );
		$this->add_control( 'hide_overlay', array( 'label' => __( 'حذف لایه محوکننده روی تصویر', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'none', 'selectors' => array( '{{WRAPPER}} .se-hero.has-image::before' => 'display: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'radius', array( 'label' => __( 'گردی گوشه‌ها', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 60 ) ), 'selectors' => array( '{{WRAPPER}} .se-hero' => 'border-radius: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'content_width', array( 'label' => __( 'عرض محتوا', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px', '%' ), 'range' => array( 'px' => array( 'min' => 200, 'max' => 900 ) ), 'selectors' => array( '{{WRAPPER}} .se-hero__content' => 'max-width: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_text_style( 'hero_title', __( 'عنوان', 'samadi-elementor-widgets' ), '.se-hero__title' );
		$this->add_text_style( 'hero_text', __( 'متن', 'samadi-elementor-widgets' ), '.se-hero__text' );
		$this->add_control( 'btn_bg', array( 'label' => __( 'پس‌زمینه دکمه اصلی', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'separator' => 'before', 'selectors' => array( '{{WRAPPER}} .se-hero .se-btn--primary' => 'background-color: {{VALUE}};' ) ) );
		$this->add_control( 'btn_color', array( 'label' => __( 'رنگ متن دکمه اصلی', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .se-hero .se-btn--primary' => 'color: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s      = $this->get_settings_for_display();
		$slides = array();

		foreach ( (array) $s['slides'] as $slide ) {
			$slides[] = array(
				'image_id'  => isset( $slide['image']['id'] ) ? absint( $slide['image']['id'] ) : 0,
				'image_url' => isset( $slide['image']['url'] ) ? $slide['image']['url'] : '',
				'title'     => isset( $slide['title'] ) ? $slide['title'] : '',
				'text'      => isset( $slide['text'] ) ? $slide['text'] : '',
				'btn1_text' => isset( $slide['btn1_text'] ) ? $slide['btn1_text'] : '',
				'btn1_url'  => $this->url( isset( $slide['btn1_url'] ) ? $slide['btn1_url'] : '' ),
				'btn2_text' => isset( $slide['btn2_text'] ) ? $slide['btn2_text'] : '',
				'btn2_url'  => $this->url( isset( $slide['btn2_url'] ) ? $slide['btn2_url'] : '' ),
				'align'     => isset( $slide['align'] ) ? $slide['align'] : 'start',
			);
		}

		$this->output(
			Samadi_EW_Components::hero(
				array(
					'slides'    => $slides,
					'autoplay'  => 'yes' === $s['autoplay'],
					'interval'  => isset( $s['interval'] ) ? (int) $s['interval'] : 6,
					'title_tag' => $s['title_tag'],
					'eager'     => 'yes' === $s['eager'],
				)
			)
		);
	}
}
