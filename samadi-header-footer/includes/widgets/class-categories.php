<?php
/**
 * Product categories dropdown ("all categories" button).
 *
 * @package Samadi_Header_Footer
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Categories.
 */
class SHF_Widget_Categories extends SHF_Widget_Base {

	/** @return string */
	public function get_name() {
		return 'shf-categories';
	}

	/** @return string */
	public function get_title() {
		return 'دسته‌بندی محصولات (صمدی)';
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-menu-toggle';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => 'دسته‌بندی‌ها' ) );
		$this->add_control( 'label', array( 'label' => 'عنوان دکمه', 'type' => Controls_Manager::TEXT, 'default' => 'دسته‌بندی محصولات' ) );
		$this->add_control( 'limit', array( 'label' => 'تعداد دسته اصلی', 'type' => Controls_Manager::NUMBER, 'default' => 10, 'min' => 1, 'max' => 30 ) );
		$this->add_control( 'children', array( 'label' => 'تعداد زیردسته', 'type' => Controls_Manager::NUMBER, 'default' => 6, 'min' => 0, 'max' => 20 ) );
		$this->end_controls_section();

		$this->start_controls_section( 'style', array( 'label' => 'دکمه', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'btn_bg', array( 'label' => 'پس‌زمینه دکمه', 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .shf-cats__btn' => 'background-color: {{VALUE}};' ) ) );
		$this->text_style( 'btn', '.shf-cats__btn', 'متن دکمه' );
		$this->end_controls_section();
	}

	/**
	 * Render.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();
		if ( ! taxonomy_exists( 'product_cat' ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<p>برای این ویجت WooCommerce لازم است.</p>';
			}
			return;
		}
		$exclude = (int) get_option( 'default_product_cat' );
		$parents = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => 0,
				'hide_empty' => true,
				'number'     => max( 1, (int) $s['limit'] ),
				'exclude'    => $exclude ? array( $exclude ) : array(),
				'orderby'    => 'menu_order',
			)
		);
		if ( is_wp_error( $parents ) ) {
			$parents = array();
		}
		$id = 'shf-cats-' . $this->get_id();
		?>
		<div class="shf-cats" data-shf-dropdown>
			<button type="button" class="shf-cats__btn" aria-expanded="false" aria-controls="<?php echo esc_attr( $id ); ?>">
				<?php echo SHF_Plugin::icon( 'grid', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo esc_html( $s['label'] ); ?></span>
				<?php echo SHF_Plugin::icon( 'chevron-down', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
			<div class="shf-cats__panel" id="<?php echo esc_attr( $id ); ?>" hidden>
				<ul class="shf-cats__list">
					<?php foreach ( $parents as $parent ) : ?>
						<li class="shf-cats__item">
							<a class="shf-cats__parent" href="<?php echo esc_url( get_term_link( $parent ) ); ?>"><?php echo esc_html( $parent->name ); ?></a>
							<?php
							$children = (int) $s['children'] > 0 ? get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $parent->term_id, 'hide_empty' => true, 'number' => (int) $s['children'] ) ) : array();
							if ( $children && ! is_wp_error( $children ) ) :
								?>
								<ul class="shf-cats__children">
									<?php foreach ( $children as $child ) : ?>
										<li><a href="<?php echo esc_url( get_term_link( $child ) ); ?>"><?php echo esc_html( $child->name ); ?></a></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<?php
	}
}
