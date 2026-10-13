<?php
/**
 * Wishlist panel on the account dashboard (YITH / TI WooCommerce Wishlist, or the
 * samadi_account_wishlist_ids filter).
 *
 * @package Samadi_Electric
 */

defined( 'ABSPATH' ) || exit;

$samadi_ids = samadi_account_wishlist_ids( 4 );
$samadi_url = samadi_wishlist_url();

if ( ! $samadi_url ) {
	return;
}
?>
<section class="se-panel se-panel--wishlist">
	<header class="se-panel__head">
		<h2 class="se-panel__title"><?php esc_html_e( 'علاقه‌مندی‌های من', 'samadi-electric' ); ?></h2>
		<a class="se-panel__more" href="<?php echo esc_url( $samadi_url ); ?>">
			<span><?php esc_html_e( 'مشاهده همه', 'samadi-electric' ); ?></span>
			<?php samadi_icon( 'chevron-left', array( 'size' => 16 ) ); ?>
		</a>
	</header>

	<?php if ( $samadi_ids ) : ?>
		<ul class="se-wish-grid">
			<?php foreach ( $samadi_ids as $samadi_id ) : ?>
				<?php
				$samadi_product = wc_get_product( $samadi_id );
				if ( ! $samadi_product || ! $samadi_product->is_visible() ) {
					continue;
				}
				$samadi_link = $samadi_product->get_permalink();
				?>
				<li class="se-wish">
					<div class="se-wish__top">
						<?php if ( $samadi_product->is_in_stock() ) : ?>
							<span class="se-wish__stock"><?php esc_html_e( 'موجود در انبار', 'samadi-electric' ); ?></span>
						<?php else : ?>
							<span class="se-wish__stock is-out"><?php esc_html_e( 'ناموجود', 'samadi-electric' ); ?></span>
						<?php endif; ?>
						<span class="se-wish__heart"><?php samadi_icon( 'heart', array( 'size' => 18 ) ); ?></span>
					</div>
					<a class="se-wish__image" href="<?php echo esc_url( $samadi_link ); ?>" tabindex="-1" aria-hidden="true">
						<?php echo $samadi_product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
					<h3 class="se-wish__name"><a href="<?php echo esc_url( $samadi_link ); ?>"><?php echo esc_html( $samadi_product->get_name() ); ?></a></h3>
					<div class="se-wish__foot">
						<span class="se-wish__price"><?php echo wp_kses_post( $samadi_product->get_price_html() ); ?></span>
						<?php if ( $samadi_product->is_purchasable() && $samadi_product->is_in_stock() ) : ?>
							<a href="<?php echo esc_url( $samadi_product->add_to_cart_url() ); ?>" class="se-wish__cart button add_to_cart_button<?php echo $samadi_product->supports( 'ajax_add_to_cart' ) ? ' ajax_add_to_cart' : ''; ?>" data-product_id="<?php echo esc_attr( $samadi_product->get_id() ); ?>" data-quantity="1" rel="nofollow" aria-label="<?php echo esc_attr( $samadi_product->add_to_cart_description() ); ?>">
								<?php samadi_icon( 'shopping-cart', array( 'size' => 18 ) ); ?>
							</a>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php else : ?>
		<p class="se-panel__empty"><?php esc_html_e( 'هنوز محصولی به علاقه‌مندی‌ها اضافه نکرده‌اید.', 'samadi-electric' ); ?></p>
	<?php endif; ?>
</section>
