<?php
/**
 * Base class for Samadi widgets: shared control groups and helpers.
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

/**
 * Widget base.
 */
abstract class Samadi_EW_Widget_Base extends Widget_Base {

	/**
	 * Category.
	 *
	 * @return string[]
	 */
	public function get_categories() {
		return array( 'samadi' );
	}

	/**
	 * Styles.
	 *
	 * @return string[]
	 */
	public function get_style_depends(): array {
		return array( 'samadi-ew' );
	}

	/**
	 * Scripts.
	 *
	 * @return string[]
	 */
	public function get_script_depends(): array {
		return array( 'samadi-ew' );
	}

	/**
	 * Lean DOM (no inner wrapper div).
	 *
	 * @return bool
	 */
	public function has_widget_inner_wrapper(): bool {
		return false;
	}

	/**
	 * Keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array( 'samadi', 'صمدی', 'shop', 'woocommerce' );
	}

	/**
	 * Section title + "view all" link controls.
	 *
	 * @param string $default_title Default title.
	 */
	protected function add_heading_controls( $default_title = '' ) {
		$this->add_control(
			'title',
			array(
				'label'       => __( 'عنوان بخش', 'samadi-elementor-widgets' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => $default_title,
				'label_block' => true,
				'dynamic'     => array( 'active' => true ),
			)
		);
		$this->add_control(
			'title_tag',
			array(
				'label'   => __( 'تگ عنوان', 'samadi-elementor-widgets' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h2',
				'options' => array(
					'h2'  => 'H2',
					'h3'  => 'H3',
					'h4'  => 'H4',
					'p'   => 'p',
					'div' => 'div',
				),
			)
		);
		$this->add_control(
			'link',
			array(
				'label'       => __( 'لینک «مشاهده همه»', 'samadi-elementor-widgets' ),
				'type'        => Controls_Manager::URL,
				'dynamic'     => array( 'active' => true ),
				'label_block' => true,
			)
		);
		$this->add_control(
			'link_text',
			array(
				'label'   => __( 'متن لینک', 'samadi-elementor-widgets' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'مشاهده همه', 'samadi-elementor-widgets' ),
			)
		);
	}

	/**
	 * Heading style section.
	 */
	protected function add_heading_style() {
		$this->start_controls_section(
			'section_style_heading',
			array(
				'label' => __( 'عنوان بخش', 'samadi-elementor-widgets' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'heading_color',
			array(
				'label'     => __( 'رنگ عنوان', 'samadi-elementor-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .se-section-head__title' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'heading_typography',
				'selector' => '{{WRAPPER}} .se-section-head__title',
			)
		);
		$this->add_control(
			'heading_link_color',
			array(
				'label'     => __( 'رنگ لینک', 'samadi-elementor-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .se-section-head__link' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'heading_spacing',
			array(
				'label'      => __( 'فاصله تا محتوا', 'samadi-elementor-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 80 ) ),
				'selectors'  => array( '{{WRAPPER}} .se-section-head' => 'margin-bottom: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Box (card) style section.
	 *
	 * @param string $id       Section ID.
	 * @param string $label    Label.
	 * @param string $selector CSS selector inside {{WRAPPER}}.
	 */
	protected function add_box_style( $id, $label, $selector ) {
		$this->start_controls_section(
			$id,
			array(
				'label' => $label,
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			$id . '_bg',
			array(
				'label'     => __( 'پس‌زمینه', 'samadi-elementor-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} ' . $selector => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => $id . '_border',
				'selector' => '{{WRAPPER}} ' . $selector,
			)
		);
		$this->add_responsive_control(
			$id . '_radius',
			array(
				'label'      => __( 'گردی گوشه‌ها', 'samadi-elementor-widgets' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array( '{{WRAPPER}} ' . $selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			$id . '_padding',
			array(
				'label'      => __( 'فاصله داخلی', 'samadi-elementor-widgets' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( '{{WRAPPER}} ' . $selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => $id . '_shadow',
				'selector' => '{{WRAPPER}} ' . $selector,
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Text style controls (color + typography) inside the current section.
	 *
	 * @param string $id       ID prefix.
	 * @param string $label    Label.
	 * @param string $selector Selector.
	 */
	protected function add_text_style( $id, $label, $selector ) {
		$this->add_control(
			$id . '_heading',
			array(
				'label'     => $label,
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);
		$this->add_control(
			$id . '_color',
			array(
				'label'     => __( 'رنگ', 'samadi-elementor-widgets' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} ' . $selector => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => $id . '_typography',
				'selector' => '{{WRAPPER}} ' . $selector,
			)
		);
	}

	/**
	 * Responsive columns control writing a CSS variable.
	 *
	 * @param string $selector Selector.
	 * @param int    $desktop  Desktop default.
	 * @param int    $tablet   Tablet default.
	 * @param int    $mobile   Mobile default.
	 */
	protected function add_columns_control( $selector, $desktop = 4, $tablet = 3, $mobile = 2 ) {
		$options = array_combine( range( 1, 6 ), range( 1, 6 ) );

		$this->add_responsive_control(
			'columns',
			array(
				'label'           => __( 'تعداد ستون', 'samadi-elementor-widgets' ),
				'type'            => Controls_Manager::SELECT,
				'options'         => $options,
				'default'         => (string) $desktop,
				'tablet_default'  => (string) $tablet,
				'mobile_default'  => (string) $mobile,
				'selectors'       => array( '{{WRAPPER}} ' . $selector => '--se-cols: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'gap',
			array(
				'label'      => __( 'فاصله بین آیتم‌ها', 'samadi-elementor-widgets' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors'  => array( '{{WRAPPER}} ' . $selector => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);
	}

	/**
	 * Product category options for SELECT2.
	 *
	 * @return array<int, string>
	 */
	protected function category_options() {
		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'number'     => 300,
			)
		);

		return is_wp_error( $terms ) ? array() : wp_list_pluck( $terms, 'name', 'term_id' );
	}

	/**
	 * Brand options for SELECT2.
	 *
	 * @return array<int, string>
	 */
	protected function brand_options() {
		$taxonomy = samadi_ew_brand_taxonomy();
		if ( ! $taxonomy ) {
			return array();
		}
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
				'number'     => 300,
			)
		);

		return is_wp_error( $terms ) ? array() : wp_list_pluck( $terms, 'name', 'term_id' );
	}

	/**
	 * URL from an Elementor URL control value.
	 *
	 * @param mixed $value Value.
	 * @return string
	 */
	protected function url( $value ) {
		return is_array( $value ) && ! empty( $value['url'] ) ? (string) $value['url'] : '';
	}

	/**
	 * Wrap output for consistent scoping.
	 *
	 * @param string $html  HTML.
	 * @param string $class Extra class.
	 */
	protected function output( $html, $class = '' ) {
		if ( '' === $html ) {
			return;
		}
		echo '<div class="se-ew ' . esc_attr( $class ) . '">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Components escape their output.
	}
}
