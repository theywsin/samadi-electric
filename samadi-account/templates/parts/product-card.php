<?php
/**
 * Wishlist product card.
 *
 * @package Samadi_Account
 *
 * @var WC_Product $product Product.
 */

defined( 'ABSPATH' ) || exit;

$sad_stock = $product->get_stock_status();
$sad_stock = 'instock' === $sad_stock ? array( 'موجود در انبار', 'success' ) : ( 'onbackorder' === $sad_stock ? array( 'پیش‌سفارش', 'warning' ) : array( 'ناموجود', 'danger' ) );
?>
<div class="sad-product">
	<div class="sad-product__top">
		<?php echo Samadi_Account_Wishlist::button( $product->get_id(), 'card' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in button(). ?>
		<span class="sad-pill sad-pill--<?php echo esc_attr( $sad_stock[1] ); ?> sad-pill--xs"><?php echo esc_html( $sad_stock[0] ); ?></span>
	</div>
	<a class="sad-product__image" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo $product->get_image( 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
	<a class="sad-product__name" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
	<div class="sad-product__bottom">
		<span class="sad-product__price"><?php echo wp_kses_post( sad_fa( $product->get_price_html() ) ); ?></span>
		<?php
		$sad_ajax = $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock();
		printf(
			'<a href="%1$s" data-quantity="1" data-product_id="%2$d" data-product_sku="%3$s" class="sad-product__cart%4$s" aria-label="%5$s" rel="nofollow">%6$s</a>',
			esc_url( $product->add_to_cart_url() ),
			absint( $product->get_id() ),
			esc_attr( $product->get_sku() ),
			$sad_ajax ? ' add_to_cart_button ajax_add_to_cart' : '',
			esc_attr( $product->add_to_cart_description() ),
			sad_icon( 'shopping-cart', 18 ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
		?>
	</div>
</div>
