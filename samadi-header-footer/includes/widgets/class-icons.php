<?php
/**
 * Header icons: wishlist, account (icon or "login / register" button), cart with live count.
 *
 * @package Samadi_Header_Footer
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Icons.
 */
class SHF_Widget_Icons extends SHF_Widget_Base {

	/** @return string */
	public function get_name() {
		return 'shf-icons';
	}

	/** @return string */
	public function get_title() {
		return 'آیکون‌های هدر: سبد، حساب، علاقه‌مندی (صمدی)';
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-cart-medium';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => 'آیکون‌ها' ) );
		$this->add_control( 'cart', array( 'label' => 'سبد خرید', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'account', array( 'label' => 'حساب کاربری', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control(
			'account_style',
			array(
				'label'     => 'نمایش حساب',
				'type'      => Controls_Manager::SELECT,
				'default'   => 'icon',
				'options'   => array(
					'icon'   => 'آیکون',
					'button' => 'دکمه «ورود / ثبت‌نام»',
				),
				'condition' => array( 'account' => 'yes' ),
			)
		);
		$this->add_control( 'login_label', array( 'label' => 'متن دکمه ورود', 'type' => Controls_Manager::TEXT, 'default' => 'ورود / ثبت‌نام', 'condition' => array( 'account_style' => 'button' ) ) );
		$this->add_control( 'wishlist', array( 'label' => 'علاقه‌مندی‌ها (در صورت نصب)', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'wishlist_mobile', array( 'label' => 'علاقه‌مندی در موبایل', 'type' => Controls_Manager::SWITCHER, 'default' => '', 'condition' => array( 'wishlist' => 'yes' ) ) );
		$this->add_control( 'extra_text', array( 'label' => 'دکمه اضافه — متن', 'type' => Controls_Manager::TEXT, 'default' => '', 'separator' => 'before', 'description' => 'مثلاً «خرید عمده». خالی = بدون دکمه.' ) );
		$this->add_control( 'extra_link', array( 'label' => 'دکمه اضافه — لینک', 'type' => Controls_Manager::URL ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => 'آیکون‌ها', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'color', array( 'label' => 'رنگ آیکون', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .shf-icons__icon' => 'color: {{VALUE}};' ) ) );
		$this->add_control( 'icon_bg', array( 'label' => 'پس‌زمینه آیکون', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .shf-icons__icon' => 'background-color: {{VALUE}};' ) ) );
		$this->add_control( 'count_bg', array( 'label' => 'پس‌زمینه شمارنده سبد', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .shf-cart-count' => 'background-color: {{VALUE}};' ) ) );
		$this->add_control( 'btn_color', array( 'label' => 'رنگ دکمه‌ها', 'type' => Controls_Manager::COLOR, 'separator' => 'before', 'selectors' => array( '{{WRAPPER}} .shf-icons__btn' => 'color: {{VALUE}}; border-color: {{VALUE}};' ) ) );
		$this->add_control( 'extra_bg', array( 'label' => 'پس‌زمینه دکمه اضافه', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .shf-icons__btn--extra' => 'background-color: {{VALUE}}; border-color: {{VALUE}};' ) ) );
		$this->add_control( 'extra_color', array( 'label' => 'رنگ متن دکمه اضافه', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .shf-icons__btn--extra' => 'color: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Wishlist URL from the Samadi theme, Samadi Account, YITH or TI.
	 *
	 * @return string
	 */
	private function wishlist_url() {
		if ( function_exists( 'samadi_wishlist_url' ) ) {
			return (string) samadi_wishlist_url();
		}
		if ( class_exists( 'Samadi_Account_Wishlist' ) && Samadi_Account_Wishlist::enabled() ) {
			return Samadi_Account_Wishlist::url();
		}
		if ( function_exists( 'yith_wcwl_get_wishlist_url' ) ) {
			return (string) yith_wcwl_get_wishlist_url();
		}
		if ( function_exists( 'tinv_url_wishlist_default' ) ) {
			return (string) tinv_url_wishlist_default();
		}
		return '';
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s  = $this->get_settings_for_display();
		$wc = function_exists( 'wc_get_page_permalink' );

		echo '<div class="shf-icons' . ( 'yes' === $s['wishlist_mobile'] ? ' shf-icons--wish-mobile' : '' ) . '">';

		if ( '' !== trim( (string) $s['extra_text'] ) && ! empty( $s['extra_link']['url'] ) ) {
			printf( '<a class="shf-icons__btn shf-icons__btn--extra" href="%s">%s</a>', esc_url( $s['extra_link']['url'] ), esc_html( $s['extra_text'] ) );
		}

		if ( 'yes' === $s['wishlist'] && $this->wishlist_url() ) {
			printf( '<a class="shf-icons__icon shf-icons__icon--wishlist" href="%s">%s<span class="shf-sr">علاقه‌مندی‌ها</span></a>', esc_url( $this->wishlist_url() ), SHF_Plugin::icon( 'heart' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		if ( 'yes' === $s['account'] ) {
			$url = $wc ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
			if ( 'button' === $s['account_style'] && ! is_user_logged_in() ) {
				printf( '<a class="shf-icons__btn shf-icons__btn--login" href="%s">%s</a>', esc_url( $url ), esc_html( $s['login_label'] ) );
				// Compact icon instead of the text button on phones.
				printf( '<a class="shf-icons__icon shf-icons__icon--login" href="%s">%s<span class="shf-sr">%s</span></a>', esc_url( $url ), SHF_Plugin::icon( 'user' ), esc_html( $s['login_label'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				printf( '<a class="shf-icons__icon" href="%s">%s<span class="shf-sr">حساب کاربری</span></a>', esc_url( $url ), SHF_Plugin::icon( 'user' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}

		if ( 'yes' === $s['cart'] && $wc ) {
			printf( '<a class="shf-icons__icon shf-icons__cart" href="%s">%s<span class="shf-sr">سبد خرید</span>%s</a>', esc_url( wc_get_cart_url() ), SHF_Plugin::icon( 'cart' ), SHF_Plugin::cart_count() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		echo '</div>';
	}
}
