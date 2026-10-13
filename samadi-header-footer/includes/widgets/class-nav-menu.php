<?php
/**
 * Navigation menu widget with dropdowns and an off-canvas mobile drawer.
 *
 * @package Samadi_Header_Footer
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Nav menu.
 */
class SHF_Widget_Nav_Menu extends SHF_Widget_Base {

	/** @return string */
	public function get_name() {
		return 'shf-nav-menu';
	}

	/** @return string */
	public function get_title() {
		return 'منوی ناوبری (صمدی)';
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-nav-menu';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => 'منو' ) );
		$this->add_control( 'menu', array( 'label' => 'منو', 'type' => Controls_Manager::SELECT, 'options' => $this->menu_choices(), 'default' => '', 'description' => 'منوها را از «نمایش ← فهرست‌ها» بسازید.' ) );
		$this->add_control( 'location', array( 'label' => 'یا جایگاه منو', 'type' => Controls_Manager::SELECT, 'options' => $this->location_choices(), 'default' => 'primary', 'condition' => array( 'menu' => '' ) ) );
		$this->add_control(
			'breakpoint',
			array(
				'label'   => 'منوی کشویی از',
				'type'    => Controls_Manager::SELECT,
				'default' => 'tablet',
				'options' => array(
					'tablet' => 'تبلت و موبایل',
					'mobile' => 'فقط موبایل',
					'none'   => 'هرگز (همیشه افقی)',
				),
			)
		);
		$this->add_control( 'drawer_title', array( 'label' => 'عنوان منوی کشویی', 'type' => Controls_Manager::TEXT, 'default' => 'منو' ) );
		$this->add_responsive_control(
			'align',
			array(
				'label'     => 'چینش',
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array( 'title' => 'راست', 'icon' => 'eicon-h-align-right' ),
					'center'     => array( 'title' => 'وسط', 'icon' => 'eicon-h-align-center' ),
					'flex-end'   => array( 'title' => 'چپ', 'icon' => 'eicon-h-align-left' ),
				),
				'selectors' => array( '{{WRAPPER}} .shf-nav__list' => 'justify-content: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => 'آیتم‌ها', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->text_style( 'item', '.shf-nav__list > li > a', 'آیتم‌های اصلی', true );
		$this->add_responsive_control(
			'gap',
			array(
				'label'     => 'فاصله آیتم‌ها',
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'selectors' => array( '{{WRAPPER}} .shf-nav__list' => 'gap: {{SIZE}}px;' ),
			)
		);
		$this->add_control( 'active_color', array( 'label' => 'رنگ آیتم فعال', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .shf-nav__list > .current-menu-item > a, {{WRAPPER}} .shf-nav__list > .current-menu-ancestor > a' => 'color: {{VALUE}};' ) ) );
		$this->add_control( 'active_bg', array( 'label' => 'پس‌زمینه آیتم فعال', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .shf-nav__list > .current-menu-item > a, {{WRAPPER}} .shf-nav__list > li > a:hover' => 'background-color: {{VALUE}};' ) ) );
		$this->add_control( 'dropdown_heading', array( 'label' => 'زیرمنو', 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_control( 'dropdown_bg', array( 'label' => 'پس‌زمینه زیرمنو', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .shf-nav__list .sub-menu' => 'background-color: {{VALUE}};' ) ) );
		$this->add_control( 'dropdown_color', array( 'label' => 'رنگ متن زیرمنو', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .shf-nav__list .sub-menu a' => 'color: {{VALUE}};' ) ) );
		$this->add_control( 'toggle_color', array( 'label' => 'رنگ دکمه منوی موبایل', 'type' => Controls_Manager::COLOR, 'separator' => 'before', 'selectors' => array( '{{WRAPPER}} .shf-nav__toggle' => 'color: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s    = $this->get_settings_for_display();
		$menu = $this->resolve_menu( $s['menu'], $s['location'] );
		$id   = 'shf-drawer-' . $this->get_id();
		$args = array(
			'container'   => false,
			'echo'        => false,
			'fallback_cb' => false,
			'depth'       => 3,
		);
		if ( $menu ) {
			$args['menu'] = $menu;
		} elseif ( $s['location'] && has_nav_menu( $s['location'] ) ) {
			$args['theme_location'] = $s['location'];
		}

		$list = ( $menu || isset( $args['theme_location'] ) ) ? wp_nav_menu( $args + array( 'menu_class' => 'shf-nav__list' ) ) : '';
		if ( ! $list ) {
			// No menu yet: list published pages so the header is never empty.
			$list = '<ul class="shf-nav__list">' . wp_list_pages( array( 'title_li' => '', 'echo' => false, 'depth' => 1, 'number' => 7, 'sort_column' => 'menu_order' ) ) . '</ul>';
		}
		$drawer_list = str_replace( 'class="shf-nav__list', 'class="shf-drawer__list', $list );
		?>
		<nav class="shf-nav shf-nav--bp-<?php echo esc_attr( $s['breakpoint'] ); ?>" aria-label="<?php echo esc_attr( $s['drawer_title'] ); ?>">
			<?php echo $list; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output. ?>
			<button type="button" class="shf-nav__toggle" aria-expanded="false" aria-controls="<?php echo esc_attr( $id ); ?>" data-shf-drawer-open>
				<?php echo SHF_Plugin::icon( 'menu', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="shf-sr">باز کردن منو</span>
			</button>
			<div class="shf-drawer" id="<?php echo esc_attr( $id ); ?>" hidden>
				<div class="shf-drawer__backdrop" data-shf-drawer-close></div>
				<div class="shf-drawer__panel" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( $s['drawer_title'] ); ?>">
					<div class="shf-drawer__head">
						<span class="shf-drawer__title"><?php echo esc_html( $s['drawer_title'] ); ?></span>
						<button type="button" class="shf-drawer__close" data-shf-drawer-close><?php echo SHF_Plugin::icon( 'x', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="shf-sr">بستن منو</span></button>
					</div>
					<?php echo $drawer_list; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu output. ?>
				</div>
			</div>
		</nav>
		<?php
	}
}
