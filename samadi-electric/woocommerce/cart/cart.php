<?php
/**
 * Cart Page — card layout from the Samadi Electric design.
 *
 * Based on WooCommerce's cart/cart.php: every hook, filter, field name and class that WooCommerce's
 * cart script relies on (.woocommerce-cart-form, .cart_item, .product-remove > a, update_cart,
 * apply_coupon, nonce) is kept; only the table markup is replaced by cards.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Samadi_Electric
 * @version 11.2.0
 */

defined( 'ABSPATH' ) || exit;

$samadi_count = WC()->cart->get_cart_contents_count();

do_action( 'woocommerce_before_cart' ); ?>

<form class="woocommerce-cart-form se-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
	<?php // Inside the form so WooCommerce's AJAX cart refresh also updates the item count. ?>
	<div class="se-cart-head">
		<h1 class="se-cart-head__title"><?php esc_html_e( 'سبد خرید شما', 'samadi-electric' ); ?></h1>
		<span class="se-cart-head__count">
			<?php
			/* translators: %s: number of items. */
			echo esc_html( sprintf( __( '%s کالا', 'samadi-electric' ), samadi_number( $samadi_count ) ) );
			?>
		</span>
	</div>

	<?php do_action( 'woocommerce_before_cart_table' ); ?>

	<div class="shop_table cart woocommerce-cart-form__contents se-cart-items">
		<?php do_action( 'woocommerce_before_cart_contents' ); ?>

		<?php
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			/** This filter is documented in WooCommerce's cart/cart.php. */
			$visible = apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key );

			if ( $_product instanceof WC_Product && $_product->exists() && $cart_item['quantity'] > 0 && $visible ) {
				$cart_item_name = WC()->cart->get_item_product_name( $cart_item, $_product );

				/** This filter is documented in WooCommerce's cart/cart.php. */
				$product_name      = apply_filters( 'woocommerce_cart_item_name', $cart_item_name, $cart_item, $cart_item_key );
				$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
				$samadi_brand      = samadi_product_brand( $product_id );
				?>
				<div class="se-cart-item woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
					<div class="se-cart-item__info">
						<div class="product-thumbnail se-cart-item__thumb">
							<?php
							/** This filter is documented in WooCommerce's cart/cart.php. */
							$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

							if ( ! $product_permalink ) {
								echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							} else {
								printf( '<a href="%s" tabindex="-1" aria-hidden="true">%s</a>', esc_url( $product_permalink ), $thumbnail ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							?>
						</div>

						<div class="se-cart-item__details">
							<div class="product-name se-cart-item__name">
								<?php
								if ( ! $product_permalink ) {
									echo wp_kses_post( $product_name . '&nbsp;' );
								} else {
									/** This filter is documented in WooCommerce's cart/cart.php. */
									echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $cart_item_name ), $cart_item, $cart_item_key ) );
								}

								do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );
								?>
							</div>

							<?php if ( $samadi_brand ) : ?>
								<span class="se-cart-item__brand"><?php echo esc_html( $samadi_brand->name ); ?></span>
							<?php endif; ?>

							<?php
							// Meta data (variation attributes etc.).
							echo wc_get_formatted_cart_item_data( $cart_item, false, $cart_item_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

							// Backorder notification.
							if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
								echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) );
							}
							?>

							<div class="product-price se-cart-item__price">
								<span class="se-cart-item__label"><?php esc_html_e( 'قیمت واحد:', 'samadi-electric' ); ?></span>
								<?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
						</div>
					</div>

					<div class="se-cart-item__controls">
						<div class="product-quantity se-cart-item__qty">
							<?php
							if ( $_product->is_sold_individually() ) {
								$min_quantity = 1;
								$max_quantity = 1;
							} else {
								$min_quantity = 0;
								$max_quantity = $_product->get_max_purchase_quantity();
							}

							$product_quantity = woocommerce_quantity_input(
								array(
									'input_name'   => "cart[{$cart_item_key}][qty]",
									'input_value'  => $cart_item['quantity'],
									'max_value'    => $max_quantity,
									'min_value'    => $min_quantity,
									'product_name' => $product_name,
								),
								$_product,
								false
							);

							echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						</div>

						<div class="product-subtotal se-cart-item__subtotal">
							<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>

						<div class="product-remove se-cart-item__remove">
							<?php
							echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								'woocommerce_cart_item_remove_link',
								sprintf(
									'<a role="button" href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">%s</a>',
									esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
									/* translators: %s is the product name */
									esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ),
									esc_attr( $product_id ),
									esc_attr( $_product->get_sku() ),
									samadi_get_icon( 'trash-2', array( 'size' => 19 ) )
								),
								$cart_item_key
							);
							?>
						</div>
					</div>
				</div>
				<?php
			}
		}
		?>

		<?php do_action( 'woocommerce_cart_contents' ); ?>
	</div>

	<?php samadi_wc_free_shipping_notice(); ?>

	<div class="actions se-cart-actions">
		<?php if ( wc_coupons_enabled() ) { ?>
			<div class="coupon se-coupon">
				<label for="coupon_code" class="se-coupon__label"><?php esc_html_e( 'کد تخفیف دارید؟', 'samadi-electric' ); ?></label>
				<div class="se-coupon__row">
					<input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'کد تخفیف را وارد کنید', 'samadi-electric' ); ?>" />
					<button type="submit" class="button se-btn se-btn--primary" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'اعمال', 'samadi-electric' ); ?></button>
				</div>
				<?php do_action( 'woocommerce_cart_coupon' ); ?>
			</div>
		<?php } ?>

		<button type="submit" class="button se-btn se-btn--outline se-cart-actions__update" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>"><?php esc_html_e( 'به‌روزرسانی سبد', 'samadi-electric' ); ?></button>

		<?php do_action( 'woocommerce_cart_actions' ); ?>

		<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
	</div>

	<?php do_action( 'woocommerce_after_cart_contents' ); ?>
	<?php do_action( 'woocommerce_after_cart_table' ); ?>
</form>

<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>

<div class="cart-collaterals">
	<?php
		/**
		 * Cart collaterals hook.
		 *
		 * @hooked woocommerce_cross_sell_display
		 * @hooked woocommerce_cart_totals - 10
		 */
		do_action( 'woocommerce_cart_collaterals' );
	?>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
