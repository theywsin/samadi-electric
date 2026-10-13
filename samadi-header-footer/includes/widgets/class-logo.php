<?php
/**
 * Site logo widget.
 *
 * @package Samadi_Header_Footer
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Logo: the site's custom logo, an uploaded image, or the site name (+ tagline).
 */
class SHF_Widget_Logo extends SHF_Widget_Base {

	/** @return string */
	public function get_name() {
		return 'shf-logo';
	}

	/** @return string */
	public function get_title() {
		return 'لوگوی سایت (صمدی)';
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-site-logo';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => 'لوگو' ) );
		$this->add_control(
			'source',
			array(
				'label'   => 'منبع',
				'type'    => Controls_Manager::SELECT,
				'default' => 'site',
				'options' => array(
					'site'  => 'لوگوی سایت (سفارشی‌سازی)',
					'image' => 'تصویر دلخواه',
					'text'  => 'فقط نام سایت',
				),
			)
		);
		$this->add_control( 'image', array( 'label' => 'تصویر', 'type' => Controls_Manager::MEDIA, 'condition' => array( 'source' => 'image' ) ) );
		$this->add_control( 'show_name', array( 'label' => 'نمایش نام سایت کنار لوگو', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'show_tagline', array( 'label' => 'نمایش توضیح کوتاه سایت', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'condition' => array( 'show_name' => 'yes' ), 'description' => 'در موبایل برای صرفه‌جویی در فضا پنهان می‌شود.' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => 'لوگو', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control(
			'height',
			array(
				'label'      => 'ارتفاع تصویر',
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 20, 'max' => 160 ) ),
				'selectors'  => array( '{{WRAPPER}} .shf-logo__img' => 'height: {{SIZE}}{{UNIT}}; width: auto;' ),
			)
		);
		$this->text_style( 'name', '.shf-logo__name', 'نام سایت' );
		$this->text_style( 'tagline', '.shf-logo__tagline', 'توضیح کوتاه' );
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s       = $this->get_settings_for_display();
		$name    = get_bloginfo( 'name' );
		$tagline = get_bloginfo( 'description' );
		$img     = '';

		if ( 'image' === $s['source'] && ! empty( $s['image']['id'] ) ) {
			$img = wp_get_attachment_image( (int) $s['image']['id'], 'medium', false, array( 'class' => 'shf-logo__img', 'alt' => $name ) );
		} elseif ( 'image' === $s['source'] && ! empty( $s['image']['url'] ) ) {
			$img = '<img class="shf-logo__img" src="' . esc_url( $s['image']['url'] ) . '" alt="' . esc_attr( $name ) . '">';
		} elseif ( 'site' === $s['source'] && get_theme_mod( 'custom_logo' ) ) {
			$img = wp_get_attachment_image( (int) get_theme_mod( 'custom_logo' ), 'medium', false, array( 'class' => 'shf-logo__img', 'alt' => $name ) );
		} elseif ( 'site' === $s['source'] && defined( 'SAMADI_URI' ) ) {
			$img = '<img class="shf-logo__img shf-logo__img--mark" src="' . esc_url( SAMADI_URI . '/assets/images/logo-mark.svg' ) . '" width="44" height="44" alt="" aria-hidden="true">';
		}

		echo '<a class="shf-logo" href="' . esc_url( home_url( '/' ) ) . '" rel="home">';
		echo $img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
		if ( 'yes' === $s['show_name'] || ! $img ) {
			echo '<span class="shf-logo__text"><span class="shf-logo__name">' . esc_html( $name ) . '</span>';
			if ( 'yes' === $s['show_tagline'] && $tagline ) {
				echo '<span class="shf-logo__tagline">' . esc_html( $tagline ) . '</span>';
			}
			echo '</span>';
		}
		echo '</a>';
	}
}
