<?php
/**
 * Widget base.
 *
 * @package Samadi_Header_Footer
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

/**
 * Shared helpers for the header/footer widgets.
 */
abstract class SHF_Widget_Base extends Widget_Base {

	/**
	 * Category.
	 *
	 * @return string[]
	 */
	public function get_categories() {
		return array( 'samadi-hf' );
	}

	/**
	 * Styles.
	 *
	 * @return string[]
	 */
	public function get_style_depends(): array {
		return array( 'shf' );
	}

	/**
	 * Scripts.
	 *
	 * @return string[]
	 */
	public function get_script_depends(): array {
		return array( 'shf' );
	}

	/**
	 * Lean DOM.
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
		return array( 'samadi', 'header', 'footer', 'هدر', 'فوتر', 'صمدی' );
	}

	/**
	 * WordPress menus as choices.
	 *
	 * @return array<string, string>
	 */
	protected function menu_choices() {
		$choices = array( '' => '— منوی جایگاه / خودکار —' );
		foreach ( wp_get_nav_menus() as $menu ) {
			$choices[ (string) $menu->term_id ] = $menu->name;
		}
		return $choices;
	}

	/**
	 * Theme menu locations as choices.
	 *
	 * @return array<string, string>
	 */
	protected function location_choices() {
		$choices = array( '' => '—' );
		foreach ( get_registered_nav_menus() as $key => $label ) {
			$choices[ $key ] = $label;
		}
		return $choices;
	}

	/**
	 * Resolve the menu to render: explicit menu → menu at a location → first menu.
	 *
	 * @param string $menu     Menu term ID.
	 * @param string $location Location.
	 * @return int|string Menu ID or ''.
	 */
	protected function resolve_menu( $menu, $location ) {
		if ( $menu && wp_get_nav_menu_object( (int) $menu ) ) {
			return (int) $menu;
		}
		$locations = get_nav_menu_locations();
		if ( $location && ! empty( $locations[ $location ] ) && wp_get_nav_menu_object( $locations[ $location ] ) ) {
			return (int) $locations[ $location ];
		}
		return '';
	}

	/**
	 * Color + typography controls for a selector.
	 *
	 * @param string $prefix   Control prefix.
	 * @param string $selector Selector.
	 * @param string $label    Label.
	 * @param bool   $hover    Add hover color.
	 */
	protected function text_style( $prefix, $selector, $label, $hover = false ) {
		$this->add_control( $prefix . '_heading', array( 'label' => $label, 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_control( $prefix . '_color', array( 'label' => 'رنگ', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} ' . $selector => 'color: {{VALUE}};' ) ) );
		if ( $hover ) {
			$this->add_control( $prefix . '_hover', array( 'label' => 'رنگ هاور', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} ' . $selector . ':hover' => 'color: {{VALUE}};' ) ) );
		}
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => $prefix . '_typo', 'selector' => '{{WRAPPER}} ' . $selector ) );
	}
}
