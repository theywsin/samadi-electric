<?php
/**
 * Widget: Samadi Blog Posts (educational articles / buying guides).
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Blog posts.
 */
class Samadi_EW_Widget_Blog_Posts extends Samadi_EW_Widget_Base {

	/** @return string */
	public function get_name() {
		return 'samadi-blog-posts';
	}

	/** @return string */
	public function get_title() {
		return __( 'مقالات آموزشی صمدی', 'samadi-elementor-widgets' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-post-list';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$categories = get_categories( array( 'hide_empty' => false ) );

		$this->start_controls_section( 'section_content', array( 'label' => __( 'مقالات', 'samadi-elementor-widgets' ) ) );
		$this->add_heading_controls( __( 'راهنمای خرید و مقالات آموزشی', 'samadi-elementor-widgets' ) );
		$this->add_control( 'count', array( 'label' => __( 'تعداد', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::NUMBER, 'default' => 3, 'min' => 1, 'max' => 12, 'separator' => 'before' ) );
		$this->add_control(
			'category',
			array(
				'label'   => __( 'دسته‌بندی', 'samadi-elementor-widgets' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '0',
				'options' => array( '0' => __( 'همه', 'samadi-elementor-widgets' ) ) + wp_list_pluck( $categories, 'name', 'term_id' ),
			)
		);
		$this->add_control( 'excerpt', array( 'label' => __( 'تعداد کلمات خلاصه (۰ = بدون خلاصه)', 'samadi-elementor-widgets' ), 'type' => Controls_Manager::NUMBER, 'default' => 20, 'min' => 0, 'max' => 60 ) );
		$this->add_responsive_control(
			'columns',
			array(
				'label'          => __( 'تعداد ستون', 'samadi-elementor-widgets' ),
				'type'           => Controls_Manager::SELECT,
				'options'        => array_combine( range( 1, 4 ), range( 1, 4 ) ),
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'selectors'      => array( '{{WRAPPER}} .se-post-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));' ),
			)
		);
		$this->end_controls_section();

		$this->add_heading_style();
		$this->add_box_style( 'section_style_card', __( 'کارت مقاله', 'samadi-elementor-widgets' ), '.se-post-card' );
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();
		$this->output(
			Samadi_EW_Components::posts(
				array(
					'count'     => (int) $s['count'],
					'category'  => (int) $s['category'],
					'excerpt'   => (int) $s['excerpt'],
					'title'     => $s['title'],
					'title_tag' => $s['title_tag'],
					'link'      => $this->url( $s['link'] ),
					'link_text' => $s['link_text'],
				)
			)
		);
	}
}
