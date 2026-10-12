<?php
/**
 * Product card used in every WooCommerce loop (shop, categories, shortcodes, related, Elementor widgets).
 *
 * Based on WooCommerce's content-product.php. All standard loop hooks still fire, but WooCommerce's
 * default callbacks are moved into the theme's card structure (see inc/woocommerce.php).
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Samadi_Electric
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}

$samadi_link     = apply_filters( 'woocommerce_loop_product_link', get_the_permalink(), $product );
$samadi_style    = sanitize_html_class( (string) samadi_option( 'card_style' ) );
$samadi_brand    = samadi_option( 'card_show_brand' ) ? samadi_product_brand( $product->get_id() ) : null;
$samadi_wishlist = samadi_wishlist_provider() ? samadi_wishlist_button( $product->get_id() ) : '';
?>
<li <?php wc_product_class( 'se-pcard se-pcard--' . $samadi_style, $product ); ?>>
	<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>

	<div class="se-pcard__media">
		<a href="<?php echo esc_url( $samadi_link ); ?>" class="se-pcard__image woocommerce-LoopProduct-link woocommerce-loop-product__link" tabindex="-1" aria-hidden="true">
			<?php echo woocommerce_get_product_thumbnail(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
		<div class="se-pcard__badges">
			<?php echo samadi_wc_sale_badge( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php if ( $product->is_featured() ) : ?>
				<span class="se-badge se-badge--featured"><?php esc_html_e( 'ویژه', 'samadi-electric' ); ?></span>
			<?php endif; ?>
		</div>
		<?php if ( $samadi_wishlist ) : ?>
			<div class="se-pcard__wishlist"><?php echo $samadi_wishlist; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Wishlist plugin output. ?></div>
		<?php endif; ?>
		<?php do_action( 'woocommerce_before_shop_loop_item_title' ); ?>
	</div>

	<div class="se-pcard__body">
		<?php if ( $samadi_brand ) : ?>
			<span class="se-pcard__brand"><?php echo esc_html( $samadi_brand->name ); ?></span>
		<?php endif; ?>

		<h3 class="se-pcard__title woocommerce-loop-product__title">
			<a href="<?php echo esc_url( $samadi_link ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
		</h3>

		<?php do_action( 'woocommerce_shop_loop_item_title' ); ?>

		<?php if ( samadi_option( 'card_show_sku' ) && wc_product_sku_enabled() && $product->get_sku() ) : ?>
			<span class="se-pcard__sku"><?php esc_html_e( 'کد:', 'samadi-electric' ); ?> <span dir="ltr"><?php echo esc_html( $product->get_sku() ); ?></span></span>
		<?php endif; ?>

		<?php if ( samadi_option( 'card_show_rating' ) && wc_review_ratings_enabled() ) : ?>
			<?php woocommerce_template_loop_rating(); ?>
		<?php endif; ?>

		<?php echo samadi_wc_card_stock( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<?php do_action( 'woocommerce_after_shop_loop_item_title' ); ?>
	</div>

	<div class="se-pcard__footer">
		<div class="se-pcard__price"><?php woocommerce_template_loop_price(); ?></div>
		<div class="se-pcard__action"><?php woocommerce_template_loop_add_to_cart(); ?></div>
	</div>

	<?php do_action( 'woocommerce_after_shop_loop_item' ); ?>
</li>
