<?php
/**
 * Footer link list (a WordPress menu rendered as a simple vertical or horizontal list).
 *
 * @package Samadi_Header_Footer
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Menu list.
 */
class SHF_Widget_Menu_List extends SHF_Widget_Base {

	/** @return string */
	public function get_name() {
		return 'shf-menu-list';
	}

	/** @return string */
	public function get_title() {
		return 'فهرست لینک‌ها از منو (صمدی)';
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-bullet-list';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => 'فهرست' ) );
		$this->add_control( 'title', array( 'label' => 'عنوان', 'type' => Controls_Manager::TEXT, 'default' => '' ) );
		$this->add_control( 'menu', array( 'label' => 'منو', 'type' => Controls_Manager::SELECT, 'options' => $this->menu_choices(), 'default' => '' ) );
		$this->add_control( 'location', array( 'label' => 'یا جایگاه منو', 'type' => Controls_Manager::SELECT, 'options' => $this->location_choices(), 'default' => '', 'condition' => array( 'menu' => '' ) ) );
		$this->add_control(
			'source',
			array(
				'label'       => 'اگر منو خالی بود',
				'type'        => Controls_Manager::SELECT,
				'default'     => 'pages',
				'options'     => array(
					'pages'      => 'برگه‌های سایت',
					'categories' => 'دسته‌بندی محصولات',
					'none'       => 'هیچ',
				),
			)
		);
		$this->add_control(
			'layout',
			array(
				'label'   => 'چیدمان',
				'type'    => Controls_Manager::SELECT,
				'default' => 'vertical',
				'options' => array(
					'vertical'   => 'عمودی',
					'horizontal' => 'افقی',
				),
			)
		);
		$this->add_control( 'bullet', array( 'label' => 'نقطه کنار لینک', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => 'فهرست', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->text_style( 'title', '.shf-list__title', 'عنوان' );
		$this->add_control( 'title_bar', array( 'label' => 'رنگ خط زیر عنوان', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .shf-list__title::after' => 'background-color: {{VALUE}};' ) ) );
		$this->text_style( 'link', '.shf-list a', 'لینک‌ها', true );
		$this->add_responsive_control(
			'gap',
			array(
				'label'     => 'فاصله لینک‌ها',
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'selectors' => array( '{{WRAPPER}} .shf-list__items' => 'gap: {{SIZE}}px;' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s     = $this->get_settings_for_display();
		$menu  = $this->resolve_menu( $s['menu'], $s['location'] );
		$items = array();

		if ( $menu ) {
			foreach ( (array) wp_get_nav_menu_items( $menu ) as $item ) {
				if ( ! $item->menu_item_parent ) {
					$items[] = array( $item->url, $item->title );
				}
			}
		} elseif ( 'categories' === $s['source'] && taxonomy_exists( 'product_cat' ) ) {
			$terms = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => 0, 'hide_empty' => true, 'number' => 6, 'exclude' => array( (int) get_option( 'default_product_cat' ) ) ) );
			foreach ( is_wp_error( $terms ) ? array() : $terms as $term ) {
				$items[] = array( get_term_link( $term ), $term->name );
			}
		} elseif ( 'pages' === $s['source'] ) {
			foreach ( get_pages( array( 'number' => 6, 'sort_column' => 'menu_order', 'parent' => 0 ) ) as $page ) {
				$items[] = array( get_permalink( $page ), $page->post_title );
			}
		}

		$classes = 'shf-list shf-list--' . ( 'horizontal' === $s['layout'] ? 'h' : 'v' ) . ( 'yes' === $s['bullet'] ? ' shf-list--bullet' : '' );
		echo '<div class="' . esc_attr( $classes ) . '">';
		if ( $s['title'] ) {
			echo '<p class="shf-list__title">' . esc_html( $s['title'] ) . '</p>';
		}
		echo '<ul class="shf-list__items">';
		foreach ( $items as $item ) {
			echo '<li><a href="' . esc_url( $item[0] ) . '">' . esc_html( $item[1] ) . '</a></li>';
		}
		echo '</ul></div>';
	}
}
