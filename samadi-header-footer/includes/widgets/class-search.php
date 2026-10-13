<?php
/**
 * Product search widget (live suggestions when Samadi Elementor Widgets is active).
 *
 * @package Samadi_Header_Footer
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Search.
 */
class SHF_Widget_Search extends SHF_Widget_Base {

	/** @return string */
	public function get_name() {
		return 'shf-search';
	}

	/** @return string */
	public function get_title() {
		return 'جستجو (صمدی)';
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-search';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => 'جستجو' ) );
		$this->add_control( 'placeholder', array( 'label' => 'متن راهنما', 'type' => Controls_Manager::TEXT, 'default' => 'جستجوی محصول، برند یا کد کالا…' ) );
		$this->add_control( 'products', array( 'label' => 'فقط در محصولات', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control(
			'mode',
			array(
				'label'   => 'نمایش',
				'type'    => Controls_Manager::SELECT,
				'default' => 'bar',
				'options' => array(
					'bar'  => 'نوار کامل',
					'icon' => 'آیکون (باز شدن با کلیک)',
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => 'جستجو', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'bg', array( 'label' => 'پس‌زمینه', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .shf-search__field' => 'background-color: {{VALUE}};' ) ) );
		$this->add_control( 'border', array( 'label' => 'رنگ حاشیه', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .shf-search__field' => 'border-color: {{VALUE}};' ) ) );
		$this->add_control( 'text', array( 'label' => 'رنگ متن', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .shf-search__input, {{WRAPPER}} .shf-search__submit, {{WRAPPER}} .shf-search__toggle' => 'color: {{VALUE}};' ) ) );
		$this->add_responsive_control(
			'radius',
			array(
				'label'     => 'گردی گوشه',
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
				'selectors' => array( '{{WRAPPER}} .shf-search__field' => 'border-radius: {{SIZE}}px;' ),
			)
		);
		$this->add_responsive_control(
			'height',
			array(
				'label'     => 'ارتفاع',
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 32, 'max' => 70 ) ),
				'selectors' => array( '{{WRAPPER}} .shf-search__field' => 'min-height: {{SIZE}}px;' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s   = $this->get_settings_for_display();
		$id  = 'shf-s-' . $this->get_id();
		$wc  = 'yes' === $s['products'] && post_type_exists( 'product' );
		$cls = 'shf-search shf-search--' . ( 'icon' === $s['mode'] ? 'icon' : 'bar' );
		?>
		<div class="<?php echo esc_attr( $cls ); ?>" <?php echo $wc ? 'data-se-live-search' : ''; ?>>
			<?php if ( 'icon' === $s['mode'] ) : ?>
				<button type="button" class="shf-search__toggle" aria-expanded="false" aria-controls="<?php echo esc_attr( $id ); ?>" data-shf-search-toggle><?php echo SHF_Plugin::icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="shf-sr">جستجو</span></button>
			<?php endif; ?>
			<form role="search" method="get" class="shf-search__form se-search-form" id="<?php echo esc_attr( $id ); ?>" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="shf-sr" for="<?php echo esc_attr( $id ); ?>-input">جستجو</label>
				<div class="shf-search__field">
					<input type="search" id="<?php echo esc_attr( $id ); ?>-input" class="shf-search__input se-search-form__input" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr( $s['placeholder'] ); ?>" autocomplete="off">
					<?php if ( $wc ) : ?>
						<input type="hidden" name="post_type" value="product">
					<?php endif; ?>
					<button type="submit" class="shf-search__submit"><?php echo SHF_Plugin::icon( 'search', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="shf-sr">جستجو</span></button>
				</div>
			</form>
		</div>
		<?php
	}
}
