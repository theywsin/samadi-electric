<?php
/**
 * Widget: Samadi Product Grid (also base for the carousel widget).
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Product grid backed by WooCommerce product queries.
 */
class Samadi_EW_Widget_Product_Grid extends Samadi_EW_Widget_Base {

	/**
	 * Default layout.
	 *
	 * @var string
	 */
	protected $default_layout = 'grid';

	/** @return string */
	public function get_name() {
		return 'samadi-product-grid';
	}

	/** @return string */
	public function get_title() {
		return __( 'شبکه محصولات صمدی', 'samadi-elementor-widgets' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-products';
	}

	/**
	 * Product source controls (shared with flash sale).
	 *
	 * @param string $default_source Default source.
	 */
	protected function add_query_controls( $default_source = 'recent' ) {
		$this->add_control(
			'source',
			array(
				'label'     => __( 'منبع محصولات', 'samadi-elementor-widgets' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => $default_source,
				'separator' => 'before',
				'options'   => array(
					'recent'           => __( 'جدیدترین', 'samadi-elementor-widgets' ),
					'sale'             => __( 'تخفیف‌دار (پیشنهاد ویژه)', 'samadi-elementor-widgets' ),
					'best_selling'     => __( 'پرفروش‌ترین', 'samadi-elementor-widgets' ),
					'featured'         => __( 'محصولات ویژه (ستاره‌دار)', 'samadi-elementor-widgets' ),
					'top_rated'        => __( 'بیشترین امتیاز', 'samadi-elementor-widgets' ),
					'category'         => __( 'از دسته‌بندی‌های انتخابی', 'samadi-elementor-widgets' ),
					'current_category' => __( 'دسته‌بندی جاری (در آرشیو)', 'samadi-elementor-widgets' ),
					'manual'           => __( 'انتخاب دستی محصولات', 'samadi-elementor-widgets' ),
					'related'          => __( 'محصولات مرتبط (صفحه محصول)', 'samadi-elementor-widgets' ),
					'upsells'          => __( 'محصولات پیشنهادی/جایگزین (صفحه محصول)', 'samadi-elementor-widgets' ),
					'cross_sells'      => __( 'محصولات مکمل (سبد خرید)', 'samadi-elementor-widgets' ),
				),
			)
		);
		$this->add_control(
			'categories',
			array(
				'label'       => __( 'دسته‌بندی‌ها', 'samadi-elementor-widgets' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->category_options(),
				'condition'   => array( 'source' => 'category' ),
			)
		);
		$this->add_control(
			'ids',
			array(
				'label'       => __( 'شناسه محصولات', 'samadi-elementor-widgets' ),
				'description' => __( 'شناسه‌ها (ID) را با کاما جدا کنید؛ به همین ترتیب نمایش داده می‌شوند.', 'samadi-elementor-widgets' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'condition'   => array( 'source' => 'manual' ),
			)
		);
		$this->add_control( 'limit', array( 'label' => __( 'تعداد محصولات', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::NUMBER, 'default' => 8, 'min' => 1, 'max' => 48 ) );
		$this->add_control(
			'orderby',
			array(
				'label'   => __( 'مرتب‌سازی', 'samadi-elementor-widgets' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''           => __( 'پیش‌فرض منبع', 'samadi-elementor-widgets' ),
					'date'       => __( 'تاریخ', 'samadi-elementor-widgets' ),
					'title'      => __( 'نام', 'samadi-elementor-widgets' ),
					'price'      => __( 'قیمت', 'samadi-elementor-widgets' ),
					'popularity' => __( 'محبوبیت (فروش)', 'samadi-elementor-widgets' ),
					'rating'     => __( 'امتیاز', 'samadi-elementor-widgets' ),
					'menu_order' => __( 'ترتیب دستی', 'samadi-elementor-widgets' ),
					'rand'       => __( 'تصادفی', 'samadi-elementor-widgets' ),
				),
			)
		);
		$this->add_control(
			'order',
			array(
				'label'     => __( 'جهت', 'samadi-elementor-widgets' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'DESC',
				'options'   => array(
					'DESC' => __( 'نزولی', 'samadi-elementor-widgets' ),
					'ASC'  => __( 'صعودی', 'samadi-elementor-widgets' ),
				),
				'condition' => array( 'orderby!' => array( '', 'rand' ) ),
			)
		);
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'محصولات', 'samadi-elementor-widgets' ) ) );
		$this->add_heading_controls( '' );
		$this->add_query_controls();
		$this->add_control(
			'layout',
			array(
				'label'   => __( 'چیدمان', 'samadi-elementor-widgets' ),
				'type'    => Controls_Manager::SELECT,
				'default' => $this->default_layout,
				'options' => array(
					'grid'     => __( 'شبکه‌ای', 'samadi-elementor-widgets' ),
					'carousel' => __( 'اسلایدر افقی', 'samadi-elementor-widgets' ),
				),
			)
		);
		$this->add_control( 'paginate', array( 'label' => __( 'صفحه‌بندی', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::SWITCHER, 'condition' => array( 'layout' => 'grid' ) ) );
		$this->add_columns_control( '.se-products ul.products', 4, 3, 2 );
		$this->end_controls_section();

		$this->add_heading_style();
		$this->add_card_style();
	}

	/**
	 * Card style controls (applies to the theme's product card).
	 */
	protected function add_card_style() {
		$this->add_box_style( 'section_style_card', __( 'کارت محصول', 'samadi-elementor-widgets' ), '.se-products li.product' );

		$this->start_controls_section( 'section_style_card_content', array( 'label' => __( 'محتوای کارت', 'samadi-elementor-widgets' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_text_style( 'p_title', __( 'نام محصول', 'samadi-elementor-widgets' ), '.se-products .woocommerce-loop-product__title, {{WRAPPER}} .se-products .woocommerce-loop-product__title a' );
		$this->add_text_style( 'p_price', __( 'قیمت', 'samadi-elementor-widgets' ), '.se-products .price' );
		$this->add_control( 'p_badge_bg', array( 'label' => __( 'پس‌زمینه نشان تخفیف', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'separator' => 'before', 'selectors' => array( '{{WRAPPER}} .se-products .se-badge--sale, {{WRAPPER}} .se-products .onsale' => 'background-color: {{VALUE}};' ) ) );
		$this->add_control( 'p_btn_bg', array( 'label' => __( 'پس‌زمینه دکمه سبد', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .se-products .button' => 'background-color: {{VALUE}};' ) ) );
		$this->add_control( 'p_btn_color', array( 'label' => __( 'رنگ دکمه سبد', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .se-products .button' => 'color: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Query args from settings.
	 *
	 * @param array $s Settings.
	 * @return array
	 */
	protected function query_args( $s ) {
		return array(
			'source'     => $s['source'],
			'categories' => isset( $s['categories'] ) ? (array) $s['categories'] : array(),
			'ids'        => isset( $s['ids'] ) ? wp_parse_id_list( $s['ids'] ) : array(),
			'limit'      => (int) $s['limit'],
			'columns'    => isset( $s['columns'] ) ? (int) $s['columns'] : 4,
			'orderby'    => isset( $s['orderby'] ) ? $s['orderby'] : '',
			'order'      => isset( $s['order'] ) ? $s['order'] : 'DESC',
		);
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s    = $this->get_settings_for_display();
		$args = array_merge(
			$this->query_args( $s ),
			array(
				'layout'    => $s['layout'],
				'title'     => $s['title'],
				'title_tag' => $s['title_tag'],
				'link'      => $this->url( $s['link'] ),
				'link_text' => $s['link_text'],
				'paginate'  => isset( $s['paginate'] ) && 'yes' === $s['paginate'],
			)
		);

		$html = Samadi_EW_Components::products( $args );

		if ( '' === $html && Samadi_EW_Components::is_editor() ) {
			$html = '<p class="se-notice">' . esc_html__( 'محصولی برای این تنظیمات پیدا نشد. منبع یا دسته‌بندی را تغییر دهید.', 'samadi-elementor-widgets' ) . '</p>';
		}

		$this->output( $html );
	}
}
