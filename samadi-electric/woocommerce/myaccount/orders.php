<?php
/**
 * Orders endpoint: order rows in a panel instead of a table.
 *
 * Based on WooCommerce's myaccount/orders.php. All hooks, the orders column filter, per-column
 * callbacks, order actions and pagination are kept (see template-parts/account/order-row.php).
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Samadi_Electric
 * @version 9.5.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders ); ?>

<section class="se-panel">
	<?php if ( $has_orders ) : ?>

		<ul class="se-order-list woocommerce-orders-table woocommerce-MyAccount-orders account-orders-table">
			<?php
			foreach ( $customer_orders->orders as $customer_order ) {
				$samadi_order = wc_get_order( $customer_order );
				if ( $samadi_order ) {
					get_template_part( 'template-parts/account/order-row', null, array( 'order' => $samadi_order ) );
				}
			}
			?>
		</ul>

		<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

		<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
			<div class="se-account-pagination woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
				<?php if ( 1 !== $current_page ) : ?>
					<a class="se-btn se-btn--outline woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button<?php echo esc_attr( $wp_button_class ); ?>" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>">
						<?php samadi_icon( 'chevron-right', array( 'size' => 18 ) ); ?>
						<span><?php esc_html_e( 'سفارش‌های جدیدتر', 'samadi-electric' ); ?></span>
					</a>
				<?php endif; ?>

				<?php if ( intval( $customer_orders->max_num_pages ) !== $current_page ) : ?>
					<a class="se-btn se-btn--outline woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button<?php echo esc_attr( $wp_button_class ); ?>" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>">
						<span><?php esc_html_e( 'سفارش‌های قدیمی‌تر', 'samadi-electric' ); ?></span>
						<?php samadi_icon( 'chevron-left', array( 'size' => 18 ) ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

	<?php else : ?>

		<?php get_template_part( 'template-parts/account/empty' ); ?>

	<?php endif; ?>
</section>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
