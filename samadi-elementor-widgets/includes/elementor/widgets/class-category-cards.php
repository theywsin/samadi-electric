<?php
/**
 * Widget: Samadi Category Cards.
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Product category cards.
 */
class Samadi_EW_Widget_Category_Cards extends Samadi_EW_Widget_Base {

	/** @return string */
	public function get_name() {
		return 'samadi-category-cards';
	}

	/** @return string */
	public function get_title() {
		return __( 'کارت دسته‌بندی‌های صمدی', 'samadi-elementor-widgets' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-product-categories';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'دسته‌بندی‌ها', 'samadi-elementor-widgets' ) ) );
		$this->add_heading_controls( __( 'دسته‌بندی‌های محبوب', 'samadi-elementor-widgets' ) );
		$this->add_control(
			'source',
			array(
				'label'     => __( 'منبع', 'samadi-elementor-widgets' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'top',
				'separator' => 'before',
				'options'   => array(
					'top'      => __( 'دسته‌های اصلی', 'samadi-elementor-widgets' ),
					'manual'   => __( 'انتخاب دستی (به همین ترتیب)', 'samadi-elementor-widgets' ),
					'children' => __( 'زیردسته‌های دسته جاری', 'samadi-elementor-widgets' ),
				),
			)
		);
		$this->add_control(
			'ids',
			array(
				'label'       => __( 'دسته‌ها', 'samadi-elementor-widgets' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->category_options(),
				'condition'   => array( 'source' => 'manual' ),
			)
		);
		$this->add_control(
			'orderby',
			array(
				'label'     => __( 'ترتیب', 'samadi-elementor-widgets' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'menu_order',
				'options'   => array(
					'menu_order' => __( 'ترتیب ووکامرس (قابل جابه‌جایی)', 'samadi-elementor-widgets' ),
					'name'       => __( 'نام', 'samadi-elementor-widgets' ),
					'count'      => __( 'تعداد محصول', 'samadi-elementor-widgets' ),
				),
				'condition' => array( 'source!' => 'manual' ),
			)
		);
		$this->add_control( 'limit', array( 'label' => __( 'حداکثر تعداد', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::NUMBER, 'default' => 8, 'min' => 1, 'max' => 48 ) );
		$this->add_control( 'hide_empty', array( 'label' => __( 'پنهان کردن دسته‌های خالی', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'show_count', array( 'label' => __( 'نمایش تعداد کالا', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SWITCHER ) );
		$this->add_control( 'show_image', array( 'label' => __( 'نمایش تصویر دسته', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
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
		$this->add_control( 'mobile_grid', array( 'label' => __( 'در موبایل به‌صورت شبکه‌ای', 'samadi-elementor-widgets' ), 'description' => __( 'اسلایدر در دسکتاپ، شبکه فشرده در موبایل (مطابق طرح).', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'condition' => array( 'layout' => 'carousel' ) ) );
		$this->add_control( 'show_desc', array( 'label' => __( 'نمایش توضیح کوتاه دسته (دسکتاپ)', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'highlight', array( 'label' => __( 'برجسته کردن کارت اول (دسکتاپ)', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SWITCHER ) );
		$this->add_control( 'brands_url', array( 'label' => __( 'کاشی «برندها» (لینک صفحه برندها)', 'samadi-elementor-widgets' ), 'description' => __( 'خالی = نمایش داده نمی‌شود.', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::URL, 'dynamic' => array( 'active' => true ) ) );
		$this->add_columns_control( '.se-cat-grid', 8, 4, 4 );
		$this->end_controls_section();

		$this->add_heading_style();
		$this->add_box_style( 'section_style_card', __( 'کارت', 'samadi-elementor-widgets' ), '.se-cat-card__link' );

		$this->start_controls_section( 'section_style_card_text', array( 'label' => __( 'متن و تصویر کارت', 'samadi-elementor-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'image_height', array( 'label' => __( 'ارتفاع تصویر', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 40, 'max' => 300 ) ), 'selectors' => array( '{{WRAPPER}} .se-cat-card__media' => 'aspect-ratio: auto; height: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_text_style( 'cat_name', __( 'نام دسته', 'samadi-elementor-widgets' ), '.se-cat-card__name' );
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		$this->output(
			Samadi_EW_Components::categories(
				array(
					'source'     => $s['source'],
					'ids'        => isset( $s['ids'] ) ? (array) $s['ids'] : array(),
					'limit'      => (int) $s['limit'],
					'orderby'    => isset( $s['orderby'] ) ? $s['orderby'] : 'menu_order',
					'hide_empty' => 'yes' === $s['hide_empty'],
					'show_count' => 'yes' === $s['show_count'],
					'show_image' => 'yes' === $s['show_image'],
					'title'      => $s['title'],
					'title_tag'  => $s['title_tag'],
					'link'       => $this->url( $s['link'] ),
					'link_text'  => $s['link_text'],
					'layout'      => $s['layout'],
					'mobile_grid' => isset( $s['mobile_grid'] ) && 'yes' === $s['mobile_grid'],
					'show_desc'   => 'yes' === $s['show_desc'],
					'highlight'   => 'yes' === $s['highlight'],
					'brands_url'  => $this->url( isset( $s['brands_url'] ) ? $s['brands_url'] : '' ),
				)
			)
		);
	}
}
