<?php
/**
 * Elementor integration: widget category, widgets and dynamic tags.
 *
 * @package Samadi_Elementor_Widgets
 */

defined( 'ABSPATH' ) || exit;

/**
 * Elementor loader.
 */
class Samadi_EW_Elementor {

	/**
	 * Widget class files (file => class).
	 *
	 * @var array<string, string>
	 */
	private static $widgets = array(
		'hero'             => 'Samadi_EW_Widget_Hero',
		'benefits'         => 'Samadi_EW_Widget_Benefits',
		'category-cards'   => 'Samadi_EW_Widget_Category_Cards',
		'product-grid'     => 'Samadi_EW_Widget_Product_Grid',
		'product-carousel' => 'Samadi_EW_Widget_Product_Carousel',
		'flash-sale'       => 'Samadi_EW_Widget_Flash_Sale',
		'brands'           => 'Samadi_EW_Widget_Brands',
		'blog-posts'       => 'Samadi_EW_Widget_Blog_Posts',
		'search'           => 'Samadi_EW_Widget_Search',
		'header-tools'     => 'Samadi_EW_Widget_Header_Tools',
		'mega-menu'        => 'Samadi_EW_Widget_Mega_Menu',
		'product-filters'  => 'Samadi_EW_Widget_Product_Filters',
		'product-specs'    => 'Samadi_EW_Widget_Product_Specs',
		'product-badges'   => 'Samadi_EW_Widget_Product_Badges',
		'quote-button'     => 'Samadi_EW_Widget_Quote_Button',
		'quote-form'       => 'Samadi_EW_Widget_Quote_Form',
		'recently-viewed'  => 'Samadi_EW_Widget_Recently_Viewed',
		'mobile-nav'       => 'Samadi_EW_Widget_Mobile_Nav',
		'contact'          => 'Samadi_EW_Widget_Contact',
	);

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'category' ) );
		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widgets' ) );
		add_action( 'elementor/dynamic_tags/register', array( __CLASS__, 'register_tags' ) );
		add_action( 'elementor/frontend/after_register_styles', array( __CLASS__, 'ensure_assets' ) );
	}

	/**
	 * Make sure our assets are registered for widget dependencies (also in the editor preview).
	 */
	public static function ensure_assets() {
		if ( ! wp_style_is( 'samadi-ew', 'registered' ) ) {
			Samadi_EW_Plugin::instance()->register_assets();
		}
	}

	/**
	 * Widget category.
	 *
	 * @param \Elementor\Elements_Manager $manager Manager.
	 */
	public static function category( $manager ) {
		$manager->add_category(
			'samadi',
			array(
				'title' => __( 'صمدی الکتریک', 'samadi-elementor-widgets' ),
				'icon'  => 'eicon-flash',
			)
		);
	}

	/**
	 * Register widgets.
	 *
	 * @param \Elementor\Widgets_Manager $manager Manager.
	 */
	public static function register_widgets( $manager ) {
		require_once SAMADI_EW_DIR . 'includes/elementor/class-widget-base.php';

		foreach ( self::$widgets as $file => $class ) {
			require_once SAMADI_EW_DIR . 'includes/elementor/widgets/class-' . $file . '.php';
			$manager->register( new $class() );
		}
	}

	/**
	 * Register dynamic tags (Elementor Pro exposes the dynamic tags UI).
	 *
	 * @param \Elementor\Core\DynamicTags\Manager $manager Manager.
	 */
	public static function register_tags( $manager ) {
		$manager->register_group(
			'samadi',
			array( 'title' => __( 'صمدی الکتریک', 'samadi-elementor-widgets' ) )
		);

		require_once SAMADI_EW_DIR . 'includes/elementor/tags/class-tags.php';

		foreach ( array( 'Samadi_EW_Tag_Store_Info', 'Samadi_EW_Tag_Product_Brand', 'Samadi_EW_Tag_Product_Sku', 'Samadi_EW_Tag_Quote_Url', 'Samadi_EW_Tag_Wholesale_Url' ) as $tag ) {
			$manager->register( new $tag() );
		}
	}
}
