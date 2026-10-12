<?php
/**
 * Widget: Samadi Product Carousel (grid widget with horizontal slider layout by default).
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/class-product-grid.php';

/**
 * Product carousel.
 */
class Samadi_EW_Widget_Product_Carousel extends Samadi_EW_Widget_Product_Grid {

	/**
	 * Default layout.
	 *
	 * @var string
	 */
	protected $default_layout = 'carousel';

	/** @return string */
	public function get_name() {
		return 'samadi-product-carousel';
	}

	/** @return string */
	public function get_title() {
		return __( 'اسلایدر محصولات صمدی', 'samadi-elementor-widgets' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-posts-carousel';
	}
}
