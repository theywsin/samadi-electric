<?php
/**
 * Copyright line with {year} (Jalali or Gregorian) and {site} placeholders.
 *
 * @package Samadi_Header_Footer
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Copyright.
 */
class SHF_Widget_Copyright extends SHF_Widget_Base {

	/** @return string */
	public function get_name() {
		return 'shf-copyright';
	}

	/** @return string */
	public function get_title() {
		return 'کپی‌رایت (صمدی)';
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-text';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => 'متن' ) );
		$this->add_control( 'text', array( 'label' => 'متن', 'type' => Controls_Manager::TEXTAREA, 'default' => '© {year} {site} — تمامی حقوق محفوظ است.', 'description' => '{year} = سال جاری، {site} = نام سایت' ) );
		$this->add_control( 'jalali', array( 'label' => 'سال شمسی', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_responsive_control(
			'align',
			array(
				'label'     => 'چینش',
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'right'  => array( 'title' => 'راست', 'icon' => 'eicon-text-align-right' ),
					'center' => array( 'title' => 'وسط', 'icon' => 'eicon-text-align-center' ),
					'left'   => array( 'title' => 'چپ', 'icon' => 'eicon-text-align-left' ),
				),
				'selectors' => array( '{{WRAPPER}} .shf-copyright' => 'text-align: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => 'متن', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->text_style( 'text', '.shf-copyright', 'متن' );
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s    = $this->get_settings_for_display();
		$year = (int) wp_date( 'Y' );
		if ( 'yes' === $s['jalali'] ) {
			// Jalali year starts at Nowruz (~21 March).
			$year = $year - ( (int) wp_date( 'nd' ) >= 321 ? 621 : 622 );
		}
		$text = strtr( (string) $s['text'], array( '{year}' => SHF_Plugin::fa( $year ), '{site}' => get_bloginfo( 'name' ) ) );
		echo '<p class="shf-copyright">' . wp_kses_post( $text ) . '</p>';
	}
}
