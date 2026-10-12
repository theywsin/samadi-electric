<?php
/**
 * Mobile bottom navigation bar.
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_items = array(
	'home' => array(
		'url'    => home_url( '/' ),
		'label'  => __( 'خانه', 'samadi-electric' ),
		'icon'   => 'house',
		'active' => is_front_page(),
	),
);

if ( samadi_is_wc() ) {
	$samadi_items['shop'] = array(
		'url'    => samadi_shop_url(),
		'label'  => __( 'دسته‌بندی‌ها', 'samadi-electric' ),
		'icon'   => 'layout-grid',
		'active' => is_shop() || is_product_taxonomy(),
	);
	$samadi_items['cart'] = array(
		'url'    => wc_get_cart_url(),
		'label'  => __( 'سبد خرید', 'samadi-electric' ),
		'icon'   => 'shopping-cart',
		'active' => is_cart(),
		'count'  => true,
	);

	$samadi_wishlist = samadi_wishlist_url();
	if ( $samadi_wishlist && samadi_option( 'mobile_nav_wishlist' ) ) {
		$samadi_items['wishlist'] = array(
			'url'    => $samadi_wishlist,
			'label'  => __( 'علاقه‌مندی‌ها', 'samadi-electric' ),
			'icon'   => 'heart',
			'active' => false,
		);
	}

	$samadi_items['account'] = array(
		'url'    => wc_get_page_permalink( 'myaccount' ),
		'label'  => __( 'حساب کاربری', 'samadi-electric' ),
		'icon'   => 'user',
		'active' => is_account_page(),
	);
}

/**
 * Filter bottom navigation items.
 *
 * @param array $samadi_items Items.
 */
$samadi_items = apply_filters( 'samadi_bottom_nav_items', $samadi_items );
?>
<nav class="se-bottom-nav" aria-label="<?php esc_attr_e( 'ناوبری سریع', 'samadi-electric' ); ?>">
	<ul class="se-bottom-nav__list">
		<?php foreach ( $samadi_items as $samadi_key => $samadi_item ) : ?>
			<li class="se-bottom-nav__item">
				<a class="se-bottom-nav__link<?php echo ! empty( $samadi_item['active'] ) ? ' is-active' : ''; ?>" href="<?php echo esc_url( $samadi_item['url'] ); ?>"<?php echo ! empty( $samadi_item['active'] ) ? ' aria-current="page"' : ''; ?>>
					<span class="se-bottom-nav__icon">
						<?php samadi_icon( $samadi_item['icon'] ); ?>
						<?php
						if ( ! empty( $samadi_item['count'] ) && function_exists( 'samadi_wc_cart_count_html' ) ) {
							echo samadi_wc_cart_count_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
						?>
					</span>
					<span class="se-bottom-nav__label"><?php echo esc_html( $samadi_item['label'] ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
